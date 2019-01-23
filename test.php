<?php 
include_once __DIR__ . "/lib/mpr/mprExpressionEvaluator.php";
include_once __DIR__ . "/lib/fileFunctions/fileFunctions.php";

//echo \FileFunctions\PathSanitizer::sanitize("C:\allo\moo.mpr") . "<br>";
//echo \FileFunctions\PathSanitizer::sanitize("\\\\192.168.0.1\allo\moo.mpr") . "<br>";
//echo \FileFunctions\PathSanitizer::sanitize("\\\\srvbackup\allo\moo.mpr") . "<br>";
//echo \FileFunctions\PathSanitizer::sanitize("/media/allo/moo.mpr") . "<br>";
//echo \MprExpression\Evaluator::evaluate("IF -1= 0 THEN SIN(moo) ELSE 0 OR NOT 0 AND NOT 0", null, ["moo" => "30"]) . "<br>";
//echo \MprExpression\Evaluator::evaluate("IF 1 + 0 THEN 0 ELSE 1", null, ["moo" => "30"]) . "<br>";

include_once __DIR__ . '/lib/PhpSpreadsheet/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Hello World !');

$writer = new Xlsx($spreadsheet);
$writer->save('hello world.xlsx');
?>