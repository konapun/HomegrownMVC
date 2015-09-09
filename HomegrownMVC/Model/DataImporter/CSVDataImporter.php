<?php
namespace HomegrownMVC\Model\DataImporter;

use HomegrownMVC\Model\DataImporter\IDataImporter as IDataImporter;
use HomegrownMVC\Error\CSVFormatException as CSVException;
use HomegrownMVC\Error\IOException as IOException;

/*
 * Import data from a CSV file
 */
class CSVDataImporter implements IDataImporter {
  private $file;
  private $fields;
  private $delimiter;
  private $enclosure;
  private $escape;

  /*
   * Construct a new CSV importer from a given file, using the first line of the
   * CSV file as the field names unless those fields are provided
   */
  function __construct($file, $fields=array(), $delimiter=",", $enclosure='"', $escape="\\") {
    $this->file = $file;
    $this->fields = $fields;
    $this->delimiter = $delimiter;
    $this->enclosure = $enclosure;
    $this->escape = $escape;
  }

  function setDelimiter($delimiter) {
    $this->delimiter = $delimiter;
  }

  function setEnclosure($enclosure) {
    $this->enclosure = $enclosure;
  }

  function setEscape($escape) {
    $this->escape = $escape;
  }

  /*
   * Manually set column values if it wasn't done upon construction.
   */
  function setFields($fields) {
    $this->fields = $fields;
  }

  function importData() {
    $lines = @file($this->file, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
      throw new IOException("Can't open file " . $this->file . " for reading");
    }

    $fields = $this->fields;
    $delimiter = $this->delimiter;
    $enclosure = $this->enclosure;
    $escape = $this->escape;

    $rowNumber = 0;
    $fieldCount = count($fields);
    $data = array();
    foreach ($lines as $line) {
      $rowNumber++;

      $columns = str_getcsv($line, $delimiter, $enclosure, $escape);
      if ($fieldCount == 0) { // first time through, get fields from file
        $fields = $columns;
        $fieldCount = count($fields);
        continue;
      }
      else if (count($columns) != $fieldCount) {
        throw new CSVException("Unexpected number of columns in row $rowNumber (got " . count($columns) . ", expected $fieldCount)");
      }

      $columnIndex = 0;
      $namedHash = array();
      foreach ($columns as $column) {
        $namedHash[$fields[$columnIndex]] = $column;
        $columnIndex++;
      }
      array_push($data, $namedHash);
    }

    return $data;
  }

  function exportData($rows, $file=null) {
    if (!$file) $file = $this->file;

    $fh = @fopen($file, 'w+');
    if ($fh === false) {
      throw new IOException("Can't open file '$file' for writing");
    }
    if ($includeHeaders) {
      $headers = array();
      foreach ($this->fields as $field) {
        $headers[$field] = $field;
      }
      array_push($rows, $headers);
    }
    foreach ($rows as $row) {
      if (fputcsv($fh, $row) === false) {
        throw new IOException("Error adding line to CSV file");
      }
    }
    fclose($fh);
  }
}
?>
