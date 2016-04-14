<?php
namespace HomegrownMVC\Model\DataImporter;

use HomegrownMVC\Model\DataImporter\IDataImporter as IDataImporter;
use HomegrownMVC\Error\CSVFormatException as CSVException;
use HomegrownMVC\Error\IOException as IOException;

/*
 * Import data from a JSON file
 */
class JSONDataImporter implements IDataImporter {
  private $file;
  private $fields;
  private $delimiter;
  private $enclosure;
  private $escape;

  /*
   * Construct a new JSON importer from a given file.
   */
  function __construct($file) {
    $this->file = $file;
  }

  /*
   * Manually set column values if it wasn't done upon construction.
   */
  function setFields($fields) {
    $this->fields = $fields;
  }

  function importData() {
    $contents = file_get_contents($this->file);
    if ($contents === false) {
      throw new IOException("Can't open file " . $this->file . " for  reading");
    }
    $json = json_decode($contents, true);
    if (is_null($json)) {
      throw new IOException("Can't parse JSON file " . $this->file);
    }

    return $json;
  }

  function exportData($rows, $file=null) {
    // TODO
  }
}
?>
