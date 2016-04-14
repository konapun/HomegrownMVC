<?php
include_once('HomegrownMVC.php');

use HomegrownMVC\Model\DataImporter\JSONDataImporter as Importer;

$file = 'test/json-importer/test.json';
$importer = new Importer($file);

var_dump($importer->importData());
?>
