<?php
session_start();
if (!isset($_SESSION['OwnerID'])) {
    header('Location: ../all/login');
    exit();
}

require_once('../classes/database.php');
$con = new database();

$days = isset($_GET['days']) ? intval($_GET['days']) : 30;
if ($days <= 0 || $days > 365) { $days = 30; }

// Fetch sales data (labels = dates, data = amounts)
$salesData = $con->getSystemSalesData($days);
$labels = $salesData['labels'] ?? [];
$data = $salesData['data'] ?? [];

$rows = [];
$total = 0;
for ($i = 0; $i < count($labels); $i++) {
    $amount = isset($data[$i]) ? (float)$data[$i] : 0.0;
    $rows[] = [ $labels[$i], $amount ];
    $total += $amount;
}

// Build a minimal XLSX (OpenXML) without external library to avoid dependencies.
// Structure: [Content_Types].xml, _rels/.rels, xl/workbook.xml, xl/worksheets/sheet1.xml, xl/_rels/workbook.xml.rels
// We'll write the sheet rows with shared strings not used (inline strings & numeric values).

$sheetRowsXml = '';
$rowIndex = 1;
// Header row
$sheetRowsXml .= '<row r="'.$rowIndex.'">'
               . '<c r="A'.$rowIndex.'" t="inlineStr"><is><t>Date</t></is></c>'
               . '<c r="B'.$rowIndex.'" t="inlineStr"><is><t>Sales</t></is></c>'
               . '</row>';
$rowIndex++;
foreach ($rows as $r) {
    $date = htmlspecialchars($r[0]);
    $amt = $r[1];
    $sheetRowsXml .= '<row r="'.$rowIndex.'">'
                   . '<c r="A'.$rowIndex.'" t="inlineStr"><is><t>'.$date.'</t></is></c>'
                   . '<c r="B'.$rowIndex.'"><v>'.number_format($amt,2,'.','').'</v></c>'
                   . '</row>';
    $rowIndex++;
}
// Total row
$sheetRowsXml .= '<row r="'.$rowIndex.'">'
               . '<c r="A'.$rowIndex.'" t="inlineStr"><is><t>Total</t></is></c>'
               . '<c r="B'.$rowIndex.'"><v>'.number_format($total,2,'.','').'</v></c>'
               . '</row>';

$sheetXml = '<?xml version="1.0" encoding="UTF-8"?>'
    .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
    .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    .'<sheetData>'.$sheetRowsXml.'</sheetData>'
    .'</worksheet>';

$workbookXml = '<?xml version="1.0" encoding="UTF-8"?>'
    .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
    .' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
    .'<sheets><sheet name="Sales" sheetId="1" r:id="rId1"/></sheets>'
    .'</workbook>';

$relsXml = '<?xml version="1.0" encoding="UTF-8"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
    .'</Relationships>';

$workbookRelsXml = '<?xml version="1.0" encoding="UTF-8"?>'
    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
    .'</Relationships>';

$contentTypesXml = '<?xml version="1.0" encoding="UTF-8"?>'
    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    .'<Default Extension="xml" ContentType="application/xml"/>'
    .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
    .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
    .'</Types>';

// Create zip (xlsx) in memory
$zip = new ZipArchive();
$tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
$zip->open($tmpFile, ZipArchive::OVERWRITE);
$zip->addFromString('[Content_Types].xml', $contentTypesXml);
$zip->addFromString('_rels/.rels', $relsXml);
$zip->addFromString('xl/workbook.xml', $workbookXml);
$zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
$zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
$zip->close();

$filename = 'sales_report_last_'.$days.'_days_'.date('Ymd_His').'.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Length: '.filesize($tmpFile));
readfile($tmpFile);
unlink($tmpFile);
exit();
