<?php
namespace HomegrownMVC\Model\DataImporter;

use HomegrownMVC\Model\DataImporter\CSVDataImporter as CSVImporter;
use HomegrownMVC\Error\IOException as IOException;

/*
 * A CSV importer that supports pulling data from text files given in columns
 * using a prefix (default "file:")
 */
class ExtendedCSVDataImporter extends CSVImporter {
  private $prefix = 'file:';
  private $globalImportPath = "";
  private $importPaths = array();
  
  /*
   * Set the prefix that will be used to identify a column value as data which
   * is to be loaded from an external file. If this is not set manually, the
   * default prefix is "file:"
   */
  function setPrefix($prefix) {
    $this->prefix = $prefix;
  }
  
  function getPrefix() {
    return $this->prefix;
  }
  
  /*
   * You may want to limit file imports to a specific path for security. Paths
   * are appended to the filename on import. If a column name is not given, the
   * path is used for all columns
   */
  function setFilePath($path, $columnName="") {
    $lastChar = substr($path, -1);
    
    if ($lastChar != DIRECTORY_SEPARATOR) $path .= DIRECTORY_SEPARATOR;
    if ($columnName) {
      $this->importPaths[$columnName] = $path;
    }
    else {
      $this->globalImportPath = $path;
    }
  }
  
  /*
   * Import data as normal, but check each column value for the set prefix
   * before loading data from a file as the field value
   */
  function importData() {
    $rows = parent::importData();
    
    $extendedRows = array();
    $prefix = $this->getPrefix();
    foreach ($rows as $row) {
      foreach ($row as $columnName => $data) {
        if (strpos($data, $prefix) === 0) { // found prefix
          $filename = substr($data, strlen($prefix));
          $fullPath = $this->getFullPath($columnName, $filename);
          
          if (!file_exists($fullPath)) {
            throw new IOException("Can't locate file \"$filename\" for reading");
          }
          
          $content = file_get_contents($fullPath);
          $row[$columnName] = $content;
        }
      }
      
      array_push($extendedRows, $row);
    }
    
    return $extendedRows;
  }
  
  /*
   * Get the actual path for the file, first looking for an override on the
   * specific import path for the column and falling back to the global import
   * path if no specific path exists
   */
  private function getFullPath($column, $path) {
    if (isset($this->importPaths[$column])) {
      return $this->importPaths[$column] . $path;
    }
    
    return $this->globalImportPath . $path;
  }
}
?>
