import fs from "node:fs/promises";
import path from "node:path";
import { SpreadsheetFile, Workbook } from "@oai/artifact-tool";

const outputDir = path.resolve("outputs/staff-invoice-template");
const outputPath = path.join(outputDir, "Hydrox - Monthly Subcontractor Work Log Template.xlsx");

const workbook = Workbook.create();
const workLog = workbook.worksheets.add("Work Log");
const guide = workbook.worksheets.add("How To Fill");

const brand = {
  blue: "#168CAB",
  blueDark: "#0F7893",
  navy: "#0F172A",
  muted: "#64748B",
  bg: "#F8FAFC",
  line: "#DDE7EF",
  green: "#16A34A",
  amber: "#F59E0B",
  red: "#EF4444",
  redDark: "#991B1B",
  softRed: "#FEE2E2",
  softBlue: "#E8F7FB",
  softAmber: "#FEF3C7",
  softGreen: "#DCFCE7",
};

function setWidths(sheet, widths) {
  widths.forEach((width, index) => {
    sheet.getRangeByIndexes(0, index, 120, 1).format.columnWidth = width;
  });
}

function titleBlock(sheet, range, title, subtitle) {
  sheet.getRange(range).merge();
  sheet.getRange(range).values = [[title]];
  sheet.getRange(range).format.fill.color = brand.navy;
  sheet.getRange(range).format.font.color = "#FFFFFF";
  sheet.getRange(range).format.font.bold = true;
  sheet.getRange(range).format.font.size = 18;
  sheet.getRange(range).format.horizontalAlignment = "left";
  sheet.getRange(range).format.verticalAlignment = "center";

  const subtitleRange = range.replace(/\d+$/, (n) => String(Number(n) + 1));
  sheet.getRange(subtitleRange).merge();
  sheet.getRange(subtitleRange).values = [[subtitle]];
  sheet.getRange(subtitleRange).format.fill.color = brand.softBlue;
  sheet.getRange(subtitleRange).format.font.color = brand.blueDark;
  sheet.getRange(subtitleRange).format.font.bold = true;
}

function sectionHeader(range, title) {
  range.merge();
  range.values = [[title]];
  range.format.fill.color = brand.softBlue;
  range.format.font.color = brand.blueDark;
  range.format.font.bold = true;
  range.format.borders = { preset: "outside", style: "thin", color: brand.line };
}

function lightBox(range) {
  range.format.fill.color = brand.bg;
  range.format.borders = { preset: "outside", style: "thin", color: brand.line };
}

function formatHeader(range) {
  range.format.fill.color = brand.navy;
  range.format.font.color = "#FFFFFF";
  range.format.font.bold = true;
  range.format.horizontalAlignment = "center";
  range.format.verticalAlignment = "center";
  range.format.borders = { preset: "outside", style: "thin", color: brand.navy };
}

for (const sheet of [workLog, guide]) {
  sheet.showGridLines = false;
}

setWidths(workLog, [14, 18, 16, 20, 12, 14, 14, 34, 3, 22, 20, 20, 34]);
setWidths(guide, [5, 30, 82, 24]);

workLog.freezePanes.freezeRows(1);
workLog.getRange("A1:H1").values = [["Date", "Work Type", "Site Code", "Work Reference", "Hours", "Hourly Rate", "Amount", "Notes"]];
formatHeader(workLog.getRange("A1:H1"));
workLog.getRange("A2:H101").format.borders = {
  insideHorizontal: { style: "thin", color: brand.line },
  bottom: { style: "thin", color: brand.line },
};
workLog.getRange("A2:A101").setNumberFormat("dd mmm yyyy");
workLog.getRange("E2:E101").setNumberFormat("0.00");
workLog.getRange("F2:G101").setNumberFormat("$#,##0.00");
workLog.getRange("G2:G101").formulas = Array.from({ length: 100 }, (_, i) => [`=IF(OR(E${i + 2}="",F${i + 2}=""),"",E${i + 2}*F${i + 2})`]);
workLog.getRange("A2:H101").format.verticalAlignment = "center";
workLog.getRange("H2:H101").format.wrapText = true;
workLog.getRange("A2:H101").format.rowHeight = 26;

workLog.getRange("J1:M2").merge();
workLog.getRange("J1:M2").values = [["Hydrox Facility Management\nMonthly Subcontractor Work Log Template"]];
workLog.getRange("J1:M2").format.fill.color = brand.navy;
workLog.getRange("J1:M2").format.font.color = "#FFFFFF";
workLog.getRange("J1:M2").format.font.bold = true;
workLog.getRange("J1:M2").format.font.size = 16;
workLog.getRange("J1:M2").format.wrapText = true;
workLog.getRange("J1:M2").format.verticalAlignment = "center";
workLog.getRange("J1:M2").format.rowHeight = 34;

sectionHeader(workLog.getRange("J4:M4"), "Before You Upload");
workLog.getRange("J5:M9").merge();
workLog.getRange("J5:M9").values = [[
  "1. Use one workbook for one month only.\n" +
  "2. Roster-based cleaning sites must use Regular Site and a Hydrox site code.\n" +
  "3. Casual jobs must use Other Job and the supplied work reference.\n" +
  "4. Enter hours and hourly rate; amount calculates automatically.\n" +
  "5. If unsure whether the work is a site or job, ask admin before uploading."
]];
lightBox(workLog.getRange("J5:M9"));
workLog.getRange("J5:M9").format.wrapText = true;
workLog.getRange("J5:M9").format.font.color = brand.navy;
workLog.getRange("J5:M9").format.verticalAlignment = "top";

sectionHeader(workLog.getRange("J11:M11"), "Work Log Summary");
workLog.getRange("J12:J16").values = [
  ["Regular Hours"],
  ["Other Job Hours"],
  ["Regular Amount"],
  ["Other Job Amount"],
  ["Work Log Total"],
];
workLog.getRange("K12:K16").formulas = [
  ['=SUMIF(B2:B101,"Regular Site",E2:E101)'],
  ['=SUMIF(B2:B101,"Other Job",E2:E101)'],
  ['=SUMIF(B2:B101,"Regular Site",G2:G101)'],
  ['=SUMIF(B2:B101,"Other Job",G2:G101)'],
  ['=SUM(G2:G101)'],
];
lightBox(workLog.getRange("J12:M16"));
workLog.getRange("J12:J16").format.font.bold = true;
workLog.getRange("K12:K13").setNumberFormat("0.00");
workLog.getRange("K14:K16").setNumberFormat("$#,##0.00");
workLog.getRange("K16").format.font.bold = true;
workLog.getRange("K16").format.fill.color = brand.softGreen;
workLog.getRange("K16").format.font.color = brand.green;

sectionHeader(workLog.getRange("J18:M18"), "Quick Examples");
workLog.getRange("J19:M22").values = [
  ["Regular Site", "2026-07-01", "HYD001", "Roster-based cleaning site"],
  ["Other Job", "2026-07-05", "Job #2304", "Bond cleaning / gardening / event job"],
  ["Regular Site", "2026-07-10", "HYD014", "Regular school, office or commercial site"],
  ["Other Job", "2026-07-12", "Job #474", "Casual job reference"],
];
lightBox(workLog.getRange("J19:M22"));
workLog.getRange("J19:M22").format.font.color = brand.navy;

workLog.getRange("B2:B101").dataValidation = { rule: { type: "list", values: ["Regular Site", "Other Job"] } };

workLog.getRange("A2:H101").format.fill.color = "#FFFFFF";

guide.getRange("A1:D2").merge();
guide.getRange("A1:D2").values = [["Hydrox Facility Management Subcontractor Work Log Guide"]];
guide.getRange("A1:D2").format.fill.color = brand.navy;
guide.getRange("A1:D2").format.font.color = "#FFFFFF";
guide.getRange("A1:D2").format.font.bold = true;
guide.getRange("A1:D2").format.font.size = 20;
guide.getRange("A1:D2").format.verticalAlignment = "center";

guide.getRange("B4:C4").merge();
guide.getRange("B4:C4").values = [["Use this file to claim monthly work completed for Hydrox Facility Management. The portal already knows your name, ABN, work log month and profile details."]];
guide.getRange("B4:C4").format.wrapText = true;
guide.getRange("B4:C4").format.font.color = brand.muted;

guide.getRange("B6:C6").merge();
sectionHeader(guide.getRange("B6:C6"), "How To Fill The Work Log");
guide.getRange("B7:C14").values = [
  ["Date", "The date you completed the work. Use dates inside the work log month only."],
  ["Work Type", "Choose Regular Site for normal site work. Choose Other Job for casual or special jobs."],
  ["Site Code", "For roster-based cleaning sites, select Regular Site and enter the Hydrox site code, for example HYD001."],
  ["Work Reference", "For casual jobs, select Other Job and enter the supplied work reference, for example Job #2304."],
  ["Hours", "Enter the hours you are claiming for that row."],
  ["Hourly Rate", "Enter the hourly rate you are claiming for that row."],
  ["Amount", "This calculates automatically from Hours x Hourly Rate."],
  ["Notes", "Optional. Use notes for special context, especially Other Job rows."],
];
lightBox(guide.getRange("B7:C14"));
guide.getRange("B7:B14").format.font.bold = true;
guide.getRange("B7:C14").format.wrapText = true;

guide.getRange("B17:C17").merge();
sectionHeader(guide.getRange("B17:C17"), "Important Rules");
guide.getRange("B17:C17").format.fill.color = brand.red;
guide.getRange("B17:C17").format.font.color = "#FFFFFF";
guide.getRange("B17:C17").format.borders = { preset: "outside", style: "thin", color: brand.red };
guide.getRange("B18:C27").values = [
  ["Regular Site", "Use this only for roster-based cleaning sites. You must add the Hydrox site code."],
  ["Other Job", "Use this only for casual jobs such as bond cleaning, gardening, events, steam cleaning or one-time jobs."],
  ["Work Reference", "Use the job or work reference supplied with the assignment, for example Job #2304."],
  ["Temporary Jobs", "Write down the job code or take a screenshot before signing out, because one-time job details may disappear from the app after sign-out."],
  ["Do Not Mix Codes", "Do not add a job code for regular cleaning sites. Do not add a site code for casual jobs."],
  ["Ask Admin", "If you are not sure whether the work is a regular site or a casual job, ask admin before submitting."],
  ["Amount", "Do not type over the Amount formulas. Enter Hours and Hourly Rate only."],
  ["Corrections", "If admin requests correction, update the file and upload the corrected version."],
  ["File Type", "Upload .xlsx only."],
  ["Profile Details", "Your name, ABN, work log month and contact details are already saved in the portal."],
];
guide.getRange("B18:C27").format.fill.color = brand.softRed;
guide.getRange("B18:C27").format.font.color = brand.redDark;
guide.getRange("B18:C27").format.borders = { preset: "outside", style: "thin", color: brand.red };
guide.getRange("B18:B27").format.font.bold = true;
guide.getRange("B18:C27").format.wrapText = true;

for (const sheet of [workLog, guide]) {
  sheet.getUsedRange().format.font.name = "Aptos";
  sheet.getUsedRange().format.autofitRows();
}

const errors = await workbook.inspect({
  kind: "match",
  searchTerm: "#REF!|#DIV/0!|#VALUE!|#NAME\\?|#N/A",
  options: { useRegex: true, maxResults: 100 },
  summary: "formula error scan",
});
console.log(errors.ndjson);

const preview = await workbook.render({ sheetName: "Work Log", range: "A1:M24", scale: 1, format: "png" });
await fs.mkdir(outputDir, { recursive: true });
await fs.writeFile(path.join(outputDir, "work-log-preview.png"), new Uint8Array(await preview.arrayBuffer()));

const guidePreview = await workbook.render({ sheetName: "How To Fill", range: "A1:D30", scale: 1, format: "png" });
await fs.writeFile(path.join(outputDir, "how-to-fill-preview.png"), new Uint8Array(await guidePreview.arrayBuffer()));

const exported = await SpreadsheetFile.exportXlsx(workbook);
await exported.save(outputPath);
console.log(outputPath);
