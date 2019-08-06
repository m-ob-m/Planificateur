<?php 
require_once __DIR__ . "/lib/mpr/mprExpressionEvaluator.php";
require_once __DIR__ . "/lib/fileFunctions/fileFunctions.php";
require_once __DIR__ . "/lib/numberFunctions/numberFunctions.php";

//echo \FileFunctions\PathSanitizer::sanitize("C:\allo\moo.mpr") . "<br>";
//echo \FileFunctions\PathSanitizer::sanitize("\\\\192.168.0.1\allo\moo.mpr") . "<br>";
//echo \FileFunctions\PathSanitizer::sanitize("\\\\srvbackup\allo\moo.mpr") . "<br>";
//echo \FileFunctions\PathSanitizer::sanitize("/media/allo/moo.mpr") . "<br>";
echo \MprExpression\Evaluator::evaluate("IF thermo=1 THEN 2 ELSE 0", null, ["thermo" => "1"]) . "<br>";
//echo \MprExpression\Evaluator::evaluate("IF 1 + 0 THEN 0 ELSE 1", null, ["moo" => "30"]) . "<br>";

// require_once __DIR__ . '/lib/PhpSpreadsheet/autoload.php';

// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// $spreadsheet = new Spreadsheet();
// $sheet = $spreadsheet->getActiveSheet();
// $sheet->setCellValue('A1', 'Hello World !');

// $writer = new Xlsx($spreadsheet);
// $writer->save('hello world.xlsx');

// is_positive_integer("");
// echo "</br>";
// is_positive_integer(true);
// echo "</br>";
// is_positive_integer(false);
// echo "</br>";
// is_positive_integer(null);
// echo "</br>";
// is_positive_integer(NAN);
// echo "</br>";
// is_positive_integer("42.5");
// echo "</br>";
// is_positive_integer(42.5);
// echo "</br>";
// is_positive_integer("-1");
// echo "</br>";
// is_positive_integer(-1);
// echo "</br>";
// is_positive_integer(" 1");
// echo "</br>";
// is_positive_integer("1");
// echo "</br>";
// is_positive_integer(1);
// echo "</br>";