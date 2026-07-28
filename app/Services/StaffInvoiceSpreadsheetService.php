<?php

namespace App\Services;

use App\Models\InvoiceSite;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class StaffInvoiceSpreadsheetService
{
    /**
     * @param  Collection<int, InvoiceSite>  $sites
     */
    public function createTemplate(Collection $sites, string $path): void
    {
        File::ensureDirectoryExists(dirname($path));

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Could not create work log template.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook($sites));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->guideSheet());
        $zip->addFromString('xl/worksheets/sheet2.xml', $this->workLogSheet($sites));
        $zip->addFromString('xl/worksheets/sheet3.xml', $this->adminSetupSheet($sites));
        $zip->close();
    }

    /**
     * @return array<int, array{row:int,date:?Carbon,site:string,shift:?string,service_m8_job_code:?string,work_type:string,hours:?float,hourly_rate:?float,amount:?float,notes:?string,shift_template:bool}>
     */
    public function readWorkLogs(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Could not open the Excel work log.');
        }

        $sharedStrings = $this->sharedStrings($zip);
        $sheetXml = $this->worksheetXmlByName($zip, 'Work Log')
            ?? $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('The Excel work log must include a Work Log sheet.');
        }

        $xml = simplexml_load_string($sheetXml);
        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('The Excel work log could not be read.');
        }

        $rows = [];
        $headers = [];
        $columns = [];
        $sheetRows = $xml->xpath('/*[local-name()="worksheet"]/*[local-name()="sheetData"]/*[local-name()="row"]') ?: [];

        foreach ($sheetRows as $row) {
            $rowNumber = (int) $row['r'];

            $cells = [];
            foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                $column = preg_replace('/\d+/', '', (string) $cell['r']);
                $cells[$column] = $this->cellValue($cell, $sharedStrings);
            }

            if ($rowNumber === 1) {
                $headers = $cells;
                foreach ($headers as $column => $header) {
                    $columns[str((string) $header)->lower()->squish()->toString()] = $column;
                }
                continue;
            }

            // Amount cells contain formulas in otherwise empty template rows. Only
            // treat a row as submitted when the staff-editable cells have content.
            $enteredValues = collect(array_values(array_filter([
                $columns['date'] ?? 'A',
                $columns['work type'] ?? 'B',
                $columns['site & shift'] ?? null,
                $columns['site code'] ?? 'C',
                $columns['shift'] ?? null,
                $columns['work reference'] ?? 'D',
                $columns['hours'] ?? 'E',
                $columns['hourly rate'] ?? 'F',
                $columns['notes'] ?? 'H',
            ])))
                ->map(fn (string $column) => $cells[$column] ?? null);

            if ($enteredValues->filter(fn ($value) => filled($value))->isEmpty()) {
                continue;
            }

            if (! isset($columns['work type'], $columns['hours'], $columns['hourly rate'], $columns['amount'])
                || (! isset($columns['site code']) && ! isset($columns['site & shift']))) {
                throw new RuntimeException('Please upload the current Hydrox Facility Management monthly work log template.');
            }

            $workType = $this->normaliseWorkType($cells[$columns['work type']] ?? null);
            $hours = $this->parseNumber($cells[$columns['hours']] ?? null);
            $hourlyRate = $this->parseNumber($cells[$columns['hourly rate']] ?? null);
            $amount = $this->parseNumber($cells[$columns['amount']] ?? null);

            if ($amount === null && $hours !== null && $hourlyRate !== null) {
                $amount = round($hours * $hourlyRate, 2);
            }

            $site = isset($columns['site code']) ? trim((string) ($cells[$columns['site code']] ?? '')) : '';
            $shift = isset($columns['shift']) && filled($cells[$columns['shift']] ?? null)
                ? trim((string) $cells[$columns['shift']])
                : null;
            $combined = isset($columns['site & shift']) ? trim((string) ($cells[$columns['site & shift']] ?? '')) : '';
            if (($site === '' || blank($shift)) && str_contains($combined, ' | ')) {
                [$combinedSite, $combinedShift] = array_map('trim', explode(' | ', $combined, 2));
                $site = $site !== '' ? $site : $combinedSite;
                $shift = filled($shift) ? $shift : $combinedShift;
            }

            $rows[] = [
                'row' => $rowNumber,
                'date' => $this->parseDate($cells[$columns['date'] ?? 'A'] ?? null),
                'work_type' => $workType,
                'site' => $site,
                'shift' => $shift,
                'service_m8_job_code' => isset($columns['work reference']) && filled($cells[$columns['work reference']] ?? null)
                    ? trim((string) $cells[$columns['work reference']])
                    : null,
                'hours' => $hours,
                'hourly_rate' => $hourlyRate,
                'amount' => $amount,
                'notes' => isset($columns['notes']) && filled($cells[$columns['notes']] ?? null)
                    ? trim((string) $cells[$columns['notes']])
                    : null,
                'shift_template' => isset($columns['shift']) || isset($columns['site & shift']),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array{
     *     site_code:string,
     *     name:string,
     *     recurring_pattern:string,
     *     validation_mode:string,
     *     monday_hours:float,
     *     tuesday_hours:float,
     *     wednesday_hours:float,
     *     thursday_hours:float,
     *     friday_hours:float,
     *     saturday_hours:float,
     *     sunday_hours:float,
     *     notes:?string,
     *     shifts:array<int, array{weekday:string,label:string,contract_hours:float,hours:float}>
     * }>
     */
    public function readRecurringSites(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Could not open the recurring jobs spreadsheet.');
        }

        $sharedStrings = $this->sharedStrings($zip);
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new RuntimeException('The recurring jobs spreadsheet could not be read.');
        }

        $xml = simplexml_load_string($sheetXml);
        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('The recurring jobs spreadsheet is not valid.');
        }

        $rows = [];
        $weekdayByColumn = [];
        $shiftByColumn = [];
        $shiftColumns = [];

        $sheetRows = $xml->xpath('/*[local-name()="worksheet"]/*[local-name()="sheetData"]/*[local-name()="row"]') ?: [];

        foreach ($sheetRows as $row) {
            $rowNumber = (int) $row['r'];

            $cells = [];
            foreach ($row->xpath('./*[local-name()="c"]') ?: [] as $cell) {
                $column = preg_replace('/\d+/', '', (string) $cell['r']);
                $cells[$column] = $this->cellValue($cell, $sharedStrings);
            }

            if ($rowNumber === 1) {
                $currentWeekday = null;
                for ($columnNumber = $this->columnNumber('E'); $columnNumber <= $this->columnNumber('Y'); $columnNumber++) {
                    $column = $this->columnName($columnNumber);
                    $weekday = $this->normaliseWeekday((string) ($cells[$column] ?? ''));
                    if ($weekday) {
                        $currentWeekday = $weekday;
                    }
                    if ($currentWeekday) {
                        $weekdayByColumn[$column] = $currentWeekday;
                    }
                }
                continue;
            }

            if ($rowNumber === 2) {
                foreach ($weekdayByColumn as $column => $weekday) {
                    $label = trim((string) ($cells[$column] ?? ''));
                    if ($label !== '') {
                        $shiftByColumn[$column] = $label;
                        $shiftColumns[$column] = [
                            'weekday' => $weekday,
                            'label' => $label,
                        ];
                    }
                }
                continue;
            }

            if ($rowNumber < 3) {
                continue;
            }

            $siteCode = trim((string) ($cells['A'] ?? ''));
            $name = trim((string) ($cells['B'] ?? ''));
            $patternText = trim((string) ($cells['C'] ?? ''));

            if ($siteCode === '' && $name === '') {
                continue;
            }

            $pattern = $this->normaliseRecurringPattern($patternText);
            $rows[] = [
                'site_code' => $siteCode,
                'name' => $name,
                'recurring_pattern' => $pattern,
                'validation_mode' => $pattern === 'fortnightly' ? 'manual' : 'auto',
                'monday_hours' => $this->sumShiftColumns($cells, $shiftColumns, 'monday'),
                'tuesday_hours' => $this->sumShiftColumns($cells, $shiftColumns, 'tuesday'),
                'wednesday_hours' => $this->sumShiftColumns($cells, $shiftColumns, 'wednesday'),
                'thursday_hours' => $this->sumShiftColumns($cells, $shiftColumns, 'thursday'),
                'friday_hours' => $this->sumShiftColumns($cells, $shiftColumns, 'friday'),
                'saturday_hours' => $this->sumShiftColumns($cells, $shiftColumns, 'saturday'),
                'sunday_hours' => $this->sumShiftColumns($cells, $shiftColumns, 'sunday'),
                'notes' => $patternText !== '' ? 'Imported recurring pattern: '.$patternText : null,
                'shifts' => $this->recurringShiftRows($cells, $shiftColumns),
            ];
        }

        if ($rows === []) {
            throw new RuntimeException('No recurring site rows were found in the spreadsheet.');
        }

        return $rows;
    }

    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = simplexml_load_string($xml);
        if (! $strings instanceof SimpleXMLElement) {
            return [];
        }

        $values = [];
        foreach ($strings->xpath('/*[local-name()="sst"]/*[local-name()="si"]') ?: [] as $item) {
            $textNodes = $item->xpath('.//*[local-name()="t"]') ?: [];
            $values[] = collect($textNodes)->map(fn ($text) => (string) $text)->implode('');
        }

        return $values;
    }

    private function worksheetXmlByName(ZipArchive $zip, string $sheetName): ?string
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relsXml === false) {
            return null;
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if (! $workbook instanceof SimpleXMLElement || ! $rels instanceof SimpleXMLElement) {
            return null;
        }

        $relationshipId = null;
        foreach ($workbook->xpath('/*[local-name()="workbook"]/*[local-name()="sheets"]/*[local-name()="sheet"]') ?: [] as $sheet) {
            if ((string) $sheet['name'] !== $sheetName) {
                continue;
            }
            $attributes = $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $relationshipId = (string) ($attributes['id'] ?? '');
            break;
        }

        if (! $relationshipId) {
            return null;
        }

        foreach ($rels->xpath('/*[local-name()="Relationships"]/*[local-name()="Relationship"]') ?: [] as $relationship) {
            if ((string) $relationship['Id'] === $relationshipId) {
                $target = ltrim((string) $relationship['Target'], '/');
                $path = str_starts_with($target, 'xl/') ? $target : 'xl/'.$target;
                $xml = $zip->getFromName($path);

                return $xml === false ? null : $xml;
            }
        }

        return null;
    }

    private function cellValue(SimpleXMLElement $cell, array $sharedStrings): ?string
    {
        $type = (string) $cell['t'];
        $valueNodes = $cell->xpath('./*[local-name()="v"]') ?: [];
        $value = isset($valueNodes[0]) ? (string) $valueNodes[0] : null;

        if ($type === 's') {
            return $value !== null ? ($sharedStrings[(int) $value] ?? null) : null;
        }

        if ($type === 'inlineStr') {
            $textNodes = $cell->xpath('./*[local-name()="is"]//*[local-name()="t"]') ?: [];

            return collect($textNodes)->map(fn ($text) => (string) $text)->implode('') ?: null;
        }

        return $value;
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (blank($value)) {
            return null;
        }

        $value = trim($value);

        if (is_numeric($value)) {
            return Carbon::create(1899, 12, 30)->addDays((int) $value)->startOfDay();
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseNumber(?string $value): ?float
    {
        if (blank($value)) {
            return null;
        }

        $number = str_replace([',', '$', ' '], '', trim((string) $value));

        return is_numeric($number) ? round((float) $number, 2) : null;
    }

    private function sumColumns(array $cells, array $columns): float
    {
        return round(collect($columns)
            ->map(fn (string $column): float => (float) ($this->parseNumber($cells[$column] ?? null) ?? 0))
            ->sum(), 2);
    }

    private function sumShiftColumns(array $cells, array $shiftColumns, string $weekday): float
    {
        return round(collect($shiftColumns)
            ->filter(fn (array $shift): bool => $shift['weekday'] === $weekday)
            ->keys()
            ->map(fn (string $column): float => (float) ($this->parseNumber($cells[$column] ?? null) ?? 0))
            ->sum(), 2);
    }

    private function normaliseWorkType(?string $value): string
    {
        $value = str($value ?: 'Regular Site')->lower();

        return $value->contains('other') || $value->contains('job') ? 'other_work' : 'regular';
    }

    private function recurringShiftRows(array $cells, array $shiftColumns): array
    {
        $rows = [];

        foreach ($shiftColumns as $column => $shift) {
            $hours = $this->parseNumber($cells[$column] ?? null) ?? 0;
            if ($hours > 0) {
                $rows[] = [
                    'weekday' => $shift['weekday'],
                    'label' => $shift['label'],
                    'contract_hours' => round($hours, 2),
                    'hours' => round($hours, 2),
                ];
            }
        }

        return $rows;
    }

    private function normaliseRecurringPattern(string $value): string
    {
        $value = str($value)->lower();

        if ($value->contains('2 week')) {
            return 'fortnightly';
        }

        if ($value->contains('week') || $value->contains('7 days')) {
            return 'weekly';
        }

        return 'manual';
    }

    private function normaliseWeekday(string $value): ?string
    {
        return match (strtolower(trim($value))) {
            'monday' => 'monday',
            'tuesday' => 'tuesday',
            'wednesday' => 'wednesday',
            'thursday' => 'thursday',
            'friday' => 'friday',
            'saturday' => 'saturday',
            'sunday' => 'sunday',
            default => null,
        };
    }

    private function columnNumber(string $column): int
    {
        $number = 0;
        foreach (str_split(strtoupper($column)) as $letter) {
            $number = $number * 26 + (ord($letter) - 64);
        }

        return $number;
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    /**
     * @param  Collection<int, InvoiceSite>  $sites
     */
    private function workbook(Collection $sites): string
    {
        $passwordHash = $this->legacyProtectionHash((string) config('company-portals.invoice_workbook_admin_password', 'CTCAdminOnly'));
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<fileVersion appName="xl"/>'
            .'<workbookPr/>'
            .'<bookViews><workbookView activeTab="0" firstSheet="0"/></bookViews>'
            .'<workbookProtection workbookPassword="'.$passwordHash.'" lockStructure="1"/>'
            .'<sheets>'
            .'<sheet name="How To Fill" sheetId="1" r:id="rId1"/>'
            .'<sheet name="Work Log" sheetId="2" r:id="rId2"/>'
            .'<sheet name="Admin Setup" sheetId="3" state="hidden" r:id="rId3"/>'
            .'</sheets>'
            .'<calcPr calcMode="auto" fullCalcOnLoad="1" forceFullCalc="1"/>'
            .'</workbook>';
    }

    private function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/>'
            .'<Relationship Id="rId4" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="3"><numFmt numFmtId="164" formatCode="dd mmm yyyy"/><numFmt numFmtId="165" formatCode="0.00"/><numFmt numFmtId="166" formatCode="$#,##0.00"/></numFmts>'
            .'<fonts count="7">'
            .'<font><sz val="11"/><name val="Aptos"/><family val="2"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Aptos Display"/></font>'
            .'<font><b/><sz val="22"/><color rgb="FFFFFFFF"/><name val="Aptos Display"/></font>'
            .'<font><b/><sz val="14"/><color rgb="FF0F7893"/><name val="Aptos Display"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FF0F172A"/><name val="Aptos"/></font>'
            .'<font><sz val="10"/><color rgb="FF64748B"/><name val="Aptos"/></font>'
            .'<font><b/><sz val="14"/><color rgb="FFFFFFFF"/><name val="Aptos Display"/></font>'
            .'</fonts>'
            .'<fills count="9">'
            .'<fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF0F172A"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE8F7FB"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFEFFCF6"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFFF7ED"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFEE2E2"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFC62828"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="3">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left style="thin"><color rgb="FFDDE7EF"/></left><right style="thin"><color rgb="FFDDE7EF"/></right><top style="thin"><color rgb="FFDDE7EF"/></top><bottom style="thin"><color rgb="FFDDE7EF"/></bottom><diagonal/></border>'
            .'<border><left/><right/><top/><bottom style="thin"><color rgb="FFDDE7EF"/></bottom><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="14">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="2" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1" applyProtection="1"><alignment vertical="center"/><protection locked="0"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="2" xfId="0" applyBorder="1" applyAlignment="1" applyProtection="1"><alignment vertical="center"/><protection locked="0"/></xf>'
            .'<xf numFmtId="165" fontId="0" fillId="5" borderId="2" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1" applyProtection="1"><alignment vertical="center"/><protection locked="0"/></xf>'
            .'<xf numFmtId="166" fontId="0" fillId="0" borderId="2" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1" applyProtection="1"><alignment vertical="center"/><protection locked="0"/></xf>'
            .'<xf numFmtId="166" fontId="4" fillId="5" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="4" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="4" fillId="7" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1" applyProtection="1"><alignment vertical="center"/><protection locked="0"/></xf>'
            .'<xf numFmtId="0" fontId="6" fillId="8" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function guideSheet(): string
    {
        $rows = '';
        $rows .= '<row r="1" ht="38" customHeight="1">'.$this->cell('A1', 'Hydrox Facility Management — Monthly Subcontractor Work Log', 2).'</row>';
        $rows .= '<row r="2" ht="24" customHeight="1">'.$this->cell('A2', 'Official completion guide • Microsoft Excel and Apple Numbers', 2).'</row>';

        $rows .= '<row r="4" ht="28" customHeight="1">'.$this->cell('A4', 'MUST CHECK BEFORE YOU START', 13).'</row>';
        $mustChecks = [
            'Use one workbook for one work log month only.',
            'Regular rostered cleaning must use Regular Site and an approved Site Code.',
            'Casual work must use Additional Work and include the correct Work Reference.',
            'If a site, hours or job code is missing, stop and contact administration.',
        ];
        foreach ($mustChecks as $index => $check) {
            $row = 5 + $index;
            $rows .= '<row r="'.$row.'" ht="28" customHeight="1">'.$this->cell('A'.$row, '!', 11).$this->cell('B'.$row, $check, 4).'</row>';
        }

        $rows .= '<row r="10" ht="28" customHeight="1">'.$this->cell('A10', 'HOW TO COMPLETE THE WORK LOG', 3).'</row>';
        $steps = [
            11 => ['1  DATE', 'Enter the actual date the work was completed.'],
            12 => ['2  WORK TYPE', 'Choose Regular Site for rostered cleaning or Additional Work for casual work.'],
            13 => ['3  SITE CODE', 'For Regular Site, choose one approved Site Code from the dropdown.'],
            14 => ['4  HOURS & RATE', 'Enter Hours and Hourly Rate manually. Amount calculates automatically.'],
        ];
        foreach ($steps as $row => [$title, $description]) {
            $rows .= '<row r="'.$row.'" ht="34" customHeight="1">'.$this->cell('A'.$row, $title, 10).$this->cell('C'.$row, $description, 4).'</row>';
        }

        $rows .= '<row r="16" ht="28" customHeight="1">'.$this->cell('A16', 'LIVE WORK LOG SUMMARY', 3).'</row>';
        $summaryRows = [
            17 => ['Regular Hours', 'SUMIF(\'Work Log\'!$B$2:$B$101,"Regular Site",\'Work Log\'!$E$2:$E$101)', 12],
            18 => ['Additional Work Hours', 'SUMIF(\'Work Log\'!$B$2:$B$101,"Additional Work",\'Work Log\'!$E$2:$E$101)', 12],
            19 => ['Regular Amount', 'SUMIF(\'Work Log\'!$B$2:$B$101,"Regular Site",\'Work Log\'!$G$2:$G$101)', 9],
            20 => ['Additional Work Amount', 'SUMIF(\'Work Log\'!$B$2:$B$101,"Additional Work",\'Work Log\'!$G$2:$G$101)', 9],
            21 => ['WORK LOG TOTAL', 'SUM(\'Work Log\'!$G$2:$G$101)', 9],
        ];
        foreach ($summaryRows as $row => [$label, $formula, $style]) {
            $rows .= '<row r="'.$row.'" ht="26" customHeight="1">'.$this->cell('A'.$row, $label, $row === 21 ? 10 : 4).$this->formulaCell('D'.$row, $formula, $style).'</row>';
        }

        $rows .= '<row r="23" ht="28" customHeight="1">'.$this->cell('A23', 'QUICK EXAMPLES', 3).'</row>';
        $rows .= '<row r="24" ht="25" customHeight="1">'.$this->cell('A24', 'Work Type', 1).$this->cell('C24', 'Example Date', 1).$this->cell('E24', 'Site Code / Job Code', 1).$this->cell('G24', 'What this means', 1).'</row>';
        $examples = [
            25 => ['Regular Site', '01 Jul 2026', 'CTC001', 'Normal rostered site cleaning'],
            26 => ['Additional Work', '05 Jul 2026', 'Job #2304', 'Bond clean, gardening or event work'],
            27 => ['Regular Site', '10 Jul 2026', 'CTC014', 'Approved commercial site'],
            28 => ['Additional Work', '12 Jul 2026', 'Job #474', 'Casual work reference'],
        ];
        foreach ($examples as $row => [$type, $date, $code, $meaning]) {
            $rows .= '<row r="'.$row.'" ht="27" customHeight="1">'.$this->cell('A'.$row, $type, 4).$this->cell('C'.$row, $date, 4).$this->cell('E'.$row, $code, 4).$this->cell('G'.$row, $meaning, 4).'</row>';
        }

        $rows .= '<row r="30" ht="28" customHeight="1">'.$this->cell('A30', 'APPLE NUMBERS — REQUIRED EXPORT STEP', 13).'</row>';
        $rows .= '<row r="31" ht="38" customHeight="1">'.$this->cell('A31', 'Before uploading from Apple Numbers: choose File → Export To → Excel, then upload the exported .xlsx file.', 11).'</row>';

        $rows .= '<row r="33" ht="28" customHeight="1">'.$this->cell('A33', 'MUST DO BEFORE UPLOAD', 13).'</row>';
        $uploadChecks = [
            'Confirm every Date is inside the correct work log month.',
            'Confirm every Regular Site row uses the Site Code dropdown.',
            'Confirm every Additional Work row has the correct Work Reference and manual Hours.',
            'Confirm Hourly Rate, calculated Amounts and the Work Log Total are correct.',
            'Enter Hours manually and do not type over Amount formula cells.',
            'Save or export as .xlsx—do not upload PDF, CSV or .numbers files.',
        ];
        foreach ($uploadChecks as $index => $check) {
            $row = 34 + $index;
            $rows .= '<row r="'.$row.'" ht="28" customHeight="1">'.$this->cell('A'.$row, '✓', 11).$this->cell('B'.$row, $check, 4).'</row>';
        }
        $rows .= '<row r="41" ht="42" customHeight="1">'.$this->cell('A41', 'NEED HELP?', 13).$this->cell('C41', 'Contact administration before submitting if any Site Code, Hours or Work Reference is unclear.', 4).'</row>';

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView showGridLines="0" tabSelected="1" workbookViewId="0"><selection activeCell="A1" sqref="A1"/></sheetView></sheetViews>'
            .'<cols><col min="1" max="1" width="16" customWidth="1"/><col min="2" max="2" width="4" customWidth="1"/><col min="3" max="3" width="16" customWidth="1"/><col min="4" max="4" width="14" customWidth="1"/><col min="5" max="6" width="18" customWidth="1"/><col min="7" max="8" width="20" customWidth="1"/></cols>'
            .'<sheetData>'.$rows.'</sheetData>'
            .'<mergeCells count="59">'
            .'<mergeCell ref="A1:H1"/><mergeCell ref="A2:H2"/><mergeCell ref="A4:H4"/>'
            .'<mergeCell ref="B5:H5"/><mergeCell ref="B6:H6"/><mergeCell ref="B7:H7"/><mergeCell ref="B8:H8"/>'
            .'<mergeCell ref="A10:H10"/>'
            .'<mergeCell ref="A11:B11"/><mergeCell ref="C11:H11"/><mergeCell ref="A12:B12"/><mergeCell ref="C12:H12"/><mergeCell ref="A13:B13"/><mergeCell ref="C13:H13"/><mergeCell ref="A14:B14"/><mergeCell ref="C14:H14"/>'
            .'<mergeCell ref="A16:H16"/>'
            .'<mergeCell ref="A17:C17"/><mergeCell ref="D17:E17"/><mergeCell ref="A18:C18"/><mergeCell ref="D18:E18"/><mergeCell ref="A19:C19"/><mergeCell ref="D19:E19"/><mergeCell ref="A20:C20"/><mergeCell ref="D20:E20"/><mergeCell ref="A21:C21"/><mergeCell ref="D21:E21"/>'
            .'<mergeCell ref="A23:H23"/>'
            .'<mergeCell ref="A24:B24"/><mergeCell ref="C24:D24"/><mergeCell ref="E24:F24"/><mergeCell ref="G24:H24"/>'
            .'<mergeCell ref="A25:B25"/><mergeCell ref="C25:D25"/><mergeCell ref="E25:F25"/><mergeCell ref="G25:H25"/>'
            .'<mergeCell ref="A26:B26"/><mergeCell ref="C26:D26"/><mergeCell ref="E26:F26"/><mergeCell ref="G26:H26"/>'
            .'<mergeCell ref="A27:B27"/><mergeCell ref="C27:D27"/><mergeCell ref="E27:F27"/><mergeCell ref="G27:H27"/>'
            .'<mergeCell ref="A28:B28"/><mergeCell ref="C28:D28"/><mergeCell ref="E28:F28"/><mergeCell ref="G28:H28"/>'
            .'<mergeCell ref="A30:H30"/><mergeCell ref="A31:H31"/><mergeCell ref="A33:H33"/>'
            .'<mergeCell ref="B34:H34"/><mergeCell ref="B35:H35"/><mergeCell ref="B36:H36"/><mergeCell ref="B37:H37"/><mergeCell ref="B38:H38"/><mergeCell ref="B39:H39"/>'
            .'<mergeCell ref="A41:B41"/><mergeCell ref="C41:H41"/>'
            .'</mergeCells>'
            .'<sheetProtection sheet="1" objects="1" scenarios="1" password="'.$this->legacyProtectionHash('guide').'"/>'
            .'<pageMargins left="0.35" right="0.35" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'</worksheet>';
    }

    /**
     * @param  Collection<int, InvoiceSite>  $sites
     */
    private function workLogSheet(Collection $sites): string
    {
        $rows = '<row r="1">'
            .$this->cell('A1', 'Date', 1).$this->cell('B1', 'Work Type', 1).$this->cell('C1', 'Site Code', 1)
            .$this->cell('D1', 'Work Reference', 1).$this->cell('E1', 'Hours', 1).$this->cell('F1', 'Hourly Rate', 1)
            .$this->cell('G1', 'Amount', 1).$this->cell('H1', 'Notes', 1)
            .$this->cell('X1', 'Valid Site Codes', 1)
            .'</row>';

        $siteCodes = [];
        foreach ($sites as $site) {
            $siteCodes[] = $site->site_code ?: $site->name;
        }

        $lastSiteRow = max(2, count($siteCodes) + 1);
        for ($row = 2; $row <= 101; $row++) {
            $amountFormula = 'IF(OR(E'.$row.'="",F'.$row.'=""),"",E'.$row.'*F'.$row.')';
            $rows .= '<row r="'.$row.'" ht="24" customHeight="1">'
                .$this->blankCell('A'.$row, 5).$this->blankCell('B'.$row, 6).$this->blankCell('C'.$row, 6).$this->blankCell('D'.$row, 6)
                .$this->blankCell('E'.$row, 7).$this->blankCell('F'.$row, 8)
                .$this->formulaCell('G'.$row, $amountFormula, 9).$this->blankCell('H'.$row, 6)
                .(isset($siteCodes[$row - 2]) ? $this->cell('X'.$row, $siteCodes[$row - 2], 6) : '')
                .'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView showGridLines="0" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols><col min="1" max="1" width="15" customWidth="1"/><col min="2" max="2" width="17" customWidth="1"/><col min="3" max="3" width="16" customWidth="1"/><col min="4" max="4" width="22" customWidth="1"/><col min="5" max="5" width="11" customWidth="1"/><col min="6" max="7" width="14" customWidth="1"/><col min="8" max="8" width="38" customWidth="1"/><col min="24" max="24" hidden="1" width="2" customWidth="1"/></cols>'
            .'<sheetData>'.$rows.'</sheetData>'
            .'<dataValidations count="4">'
            .'<dataValidation type="list" errorStyle="stop" allowBlank="0" showErrorMessage="1" showInputMessage="1" promptTitle="Choose a work type" prompt="Select Regular Site or Additional Work." errorTitle="Invalid work type" error="Please use the dropdown list." sqref="B2:B101"><formula1>"Regular Site,Additional Work"</formula1></dataValidation>'
            .'<dataValidation type="list" errorStyle="stop" allowBlank="1" showErrorMessage="1" showInputMessage="1" promptTitle="Choose a site" prompt="Select one approved Site Code." errorTitle="Invalid site" error="Please select a Site Code from the dropdown." sqref="C2:C101"><formula1>$X$2:$X$'.$lastSiteRow.'</formula1></dataValidation>'
            .'<dataValidation type="decimal" operator="between" errorStyle="stop" allowBlank="1" showErrorMessage="1" errorTitle="Invalid hours" error="Hours must be between 0.25 and 24." sqref="E2:E101"><formula1>0.25</formula1><formula2>24</formula2></dataValidation>'
            .'<dataValidation type="decimal" operator="between" errorStyle="stop" allowBlank="1" showErrorMessage="1" errorTitle="Invalid hourly rate" error="Hourly Rate must be between 0 and 1000." sqref="F2:F101"><formula1>0</formula1><formula2>1000</formula2></dataValidation>'
            .'</dataValidations>'
            .'<sheetProtection sheet="1" objects="1" scenarios="1" selectLockedCells="1" selectUnlockedCells="0" password="'.$this->legacyProtectionHash('worklog').'"/>'
            .'<autoFilter ref="A1:H101"/>'
            .'<pageMargins left="0.35" right="0.35" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'</worksheet>';
    }

    /**
     * @param  Collection<int, InvoiceSite>  $sites
     */
    private function adminSetupSheet(Collection $sites): string
    {
        $rows = '<row r="1">'
            .$this->cell('A1', 'Site Code', 1).$this->cell('B1', 'Site Name', 1).$this->cell('C1', 'Shift', 1)
            .$this->cell('D1', 'Monday', 1).$this->cell('E1', 'Tuesday', 1).$this->cell('F1', 'Wednesday', 1)
            .$this->cell('G1', 'Thursday', 1).$this->cell('H1', 'Friday', 1).$this->cell('I1', 'Saturday', 1).$this->cell('J1', 'Sunday', 1)
            .$this->cell('O1', 'ADMIN ONLY — How to update', 1)
            .'</row>';
        $rosterCells = [];
        $siteCodeCells = [];
        $rowNumber = 2;

        foreach ($sites->values() as $siteIndex => $site) {
            $code = $site->site_code ?: $site->name;
            $siteListRow = $siteIndex + 2;
            $siteCodeCells[$siteListRow] = $this->cell('X'.$siteListRow, $code, 6);

            $shifts = $site->relationLoaded('shifts') ? $site->shifts : $site->shifts()->get();
            $grouped = $shifts->where('active', true)->groupBy(fn ($shift) => str($shift->label)->lower()->toString());
            foreach ($grouped->values() as $siteShifts) {
                $first = $siteShifts->first();
                $hoursByDay = $siteShifts->keyBy('weekday');
                $rosterCells[$rowNumber] =
                    $this->cell('A'.$rowNumber, $code, 6).$this->cell('B'.$rowNumber, $site->name, 6).$this->cell('C'.$rowNumber, $first->label, 6)
                    .$this->numberCell('D'.$rowNumber, (float) ($hoursByDay->get('monday')?->hours ?? 0), 12)
                    .$this->numberCell('E'.$rowNumber, (float) ($hoursByDay->get('tuesday')?->hours ?? 0), 12)
                    .$this->numberCell('F'.$rowNumber, (float) ($hoursByDay->get('wednesday')?->hours ?? 0), 12)
                    .$this->numberCell('G'.$rowNumber, (float) ($hoursByDay->get('thursday')?->hours ?? 0), 12)
                    .$this->numberCell('H'.$rowNumber, (float) ($hoursByDay->get('friday')?->hours ?? 0), 12)
                    .$this->numberCell('I'.$rowNumber, (float) ($hoursByDay->get('saturday')?->hours ?? 0), 12)
                    .$this->numberCell('J'.$rowNumber, (float) ($hoursByDay->get('sunday')?->hours ?? 0), 12);
                $rowNumber++;
            }
        }

        $instructions = [
            2 => '1. Update the site and roster in the Hydrox Portal first.',
            3 => '2. For an urgent existing-roster edit, unhide and unprotect this sheet.',
            4 => '3. Edit Site Code, Site Name, Shift or weekday hours in A:J.',
            5 => '4. Keep all shift rows for one site together; use 0 when not rostered.',
            6 => '5. Add new sites in the Hydrox Portal, then download a fresh workbook.',
            7 => '6. Re-protect and hide this sheet before sending the workbook.',
            9 => 'Manual Excel edits do not add the site to the portal and may be flagged on upload.',
        ];
        $lastRow = max(9, $rowNumber - 1, $sites->count() + 1);
        for ($currentRow = 2; $currentRow <= $lastRow; $currentRow++) {
            $instruction = isset($instructions[$currentRow])
                ? $this->cell('O'.$currentRow, $instructions[$currentRow], $currentRow === 9 ? 13 : 4)
                : '';
            $rows .= '<row r="'.$currentRow.'">'.($rosterCells[$currentRow] ?? '').$instruction.($siteCodeCells[$currentRow] ?? '').'</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView showGridLines="0" workbookViewId="0"/></sheetViews>'
            .'<cols><col min="1" max="1" width="14" customWidth="1"/><col min="2" max="2" width="48" customWidth="1"/><col min="3" max="3" width="16" customWidth="1"/><col min="4" max="10" width="12" customWidth="1"/><col min="11" max="14" width="3" customWidth="1"/><col min="15" max="15" width="72" customWidth="1"/><col min="24" max="24" hidden="1" width="2" customWidth="1"/></cols>'
            .'<sheetData>'.$rows.'</sheetData>'
            .'<sheetProtection sheet="1" objects="1" scenarios="1" password="'.$this->legacyProtectionHash((string) config('company-portals.invoice_workbook_admin_password', 'CTCAdminOnly')).'"/>'
            .'<autoFilter ref="A1:J501"/>'
            .'</worksheet>';
    }

    private function cell(string $ref, string $value, int $style = 0): string
    {
        return '<c r="'.$ref.'" t="inlineStr"'.($style ? ' s="'.$style.'"' : '').'><is><t>'.e($value).'</t></is></c>';
    }

    private function blankCell(string $ref, int $style = 0): string
    {
        return '<c r="'.$ref.'"'.($style ? ' s="'.$style.'"' : '').'/>';
    }

    private function numberCell(string $ref, float $value, int $style = 0): string
    {
        return '<c r="'.$ref.'" t="n"'.($style ? ' s="'.$style.'"' : '').'><v>'.$value.'</v></c>';
    }

    private function formulaCell(string $ref, string $formula, int $style = 0): string
    {
        return '<c r="'.$ref.'"'.($style ? ' s="'.$style.'"' : '').'><f>'.e($formula).'</f></c>';
    }

    private function legacyProtectionHash(string $password): string
    {
        $hash = 0;
        $password = substr($password, 0, 15);

        for ($index = strlen($password) - 1; $index >= 0; $index--) {
            $hash = (($hash >> 14) & 0x01) | (($hash << 1) & 0x7fff);
            $hash ^= ord($password[$index]);
        }

        $hash = (($hash >> 14) & 0x01) | (($hash << 1) & 0x7fff);
        $hash ^= strlen($password);
        $hash ^= 0xCE4B;

        return strtoupper(str_pad(dechex($hash), 4, '0', STR_PAD_LEFT));
    }
}
