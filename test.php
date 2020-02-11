<?php 
/* 
function autoVersionning(string $filepath)
{
    if(file_exists($filepath))
    {
        return "{$filepath}?ver=" . filemtime($_SERVER['DOCUMENT_ROOT'] . $filepath);
    }
    else
    {
        throw new \Exception("File {$filepath} doesn't exist.");
    }
} 
*/

/*
(<link rel="stylesheet" href=")(.*)(" ?/?>) 
$1<?= auto_version("$2"); ?>$3
*.php
lib/PhpSpreadsheet
*/

?>