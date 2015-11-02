<?php
namespace HomegrownMVC\Model\DataImporter;

interface IDataImporter {
  function importData();
  function exportData($rows, $file=null);
}
?>
