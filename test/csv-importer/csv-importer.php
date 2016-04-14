<?php
include_once('HomegrownMVC.php');

use HomegrownMVC\Model\DataImporter\CSVDataImporter as Importer;

$file = 'test/csv-importer/test.csv';
$importer = new Importer($file);

var_dump($importer->importData());
?>
