<?php

namespace App\Services;

use App\Models\AreaPhoto;
use App\Models\CompletionReport;
use App\Models\IssuePhoto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ReportPdfService
{
    private const PDF_IMAGE_MAX_WIDTH = 1200;
    private const PDF_IMAGE_MAX_HEIGHT = 900;
    private const PDF_IMAGE_QUALITY = 78;

    public function generate(CompletionReport $report): string
    {
        $report->load([
            'job.customer',
            'areas.beforePhotos',
            'areas.afterPhotos',
            'products',
            'issues.photos',
        ]);

        $this->prepareImagesForPdf($report);
        $report->status = 'Generated';

        $pdf = Pdf::loadView('pdf.completion-report', [
            'report' => $report,
            'company' => [
                'name' => config('app.company_name', env('COMPANY_NAME', 'Hydrox Facility Management')),
                'phone' => config('app.company_phone', '0418 222 477'),
                'phone_secondary' => '',
                'email' => env('COMPANY_EMAIL', 'admin@hydrox.au'),
                'address' => config('app.company_address', ''),
                'logo' => public_path(env('COMPANY_LOGO', 'images/hydrox-logo.svg')),
            ],
        ])->setPaper('a4', 'portrait');

        $path = 'reports/'.$report->report_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());
        $report->update(['pdf_path' => $path, 'status' => 'Generated']);

        return $path;
    }

    private function prepareImagesForPdf(CompletionReport $report): void
    {
        $report->areas->each(function ($area): void {
            $this->preparePhotoCollection($area->beforePhotos);
            $this->preparePhotoCollection($area->afterPhotos);
        });

        $report->issues->each(function ($issue): void {
            $this->preparePhotoCollection($issue->photos);
        });
    }

    /**
     * @param  EloquentCollection<int, AreaPhoto|IssuePhoto>|Collection<int, AreaPhoto|IssuePhoto>  $photos
     */
    private function preparePhotoCollection(EloquentCollection|Collection $photos): void
    {
        $photos->each(function (AreaPhoto|IssuePhoto $photo): void {
            $photo->setAttribute('pdf_src', $this->optimizedImagePath($photo->path));
        });
    }

    private function optimizedImagePath(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $source = Storage::disk('public')->path($path);

        if (! $this->canOptimizeImages()) {
            return $source;
        }

        $info = @getimagesize($source);

        if ($info === false || empty($info[0]) || empty($info[1])) {
            return null;
        }

        [$width, $height, $type] = $info;
        $modifiedAt = @filemtime($source) ?: time();
        $cachePath = storage_path('app/pdf-cache/'.md5($path.$modifiedAt).'.jpg');

        if (is_file($cachePath)) {
            return $cachePath;
        }

        if (! $this->ensureCacheDirectory(dirname($cachePath))) {
            return $source;
        }

        $sourceImage = $this->createImageResource($source, $type);

        if (! $sourceImage) {
            return $source;
        }

        $scale = min(
            1,
            self::PDF_IMAGE_MAX_WIDTH / $width,
            self::PDF_IMAGE_MAX_HEIGHT / $height
        );

        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if (! $targetImage) {
            return $source;
        }

        $white = imagecolorallocate($targetImage, 255, 255, 255);
        imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $white);
        imagecopyresampled($targetImage, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        if (! @imagejpeg($targetImage, $cachePath, self::PDF_IMAGE_QUALITY)) {
            return $source;
        }

        return is_file($cachePath) ? $cachePath : $source;
    }

    private function canOptimizeImages(): bool
    {
        return function_exists('imagecreatetruecolor')
            && function_exists('imagecopyresampled')
            && function_exists('imagejpeg')
            && function_exists('imagefilledrectangle')
            && function_exists('imagecolorallocate');
    }

    private function ensureCacheDirectory(string $directory): bool
    {
        if (is_dir($directory)) {
            return is_writable($directory);
        }

        return @mkdir($directory, 0755, true) || is_dir($directory);
    }

    /**
     * @return \GdImage|false
     */
    private function createImageResource(string $path, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : false,
            IMAGETYPE_PNG => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
            default => false,
        };
    }
}
