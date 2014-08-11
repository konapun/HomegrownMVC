<?php
namespace HomegrownMVC\Model;

/*
 * A model which is instantiated with data without needing a database
 *
 * Author: Bremen Braun
 */
abstract class FixtureModel {
  private static $data;
  private $dbh;
  private $singularClassName;
  
  /*
   * Instantiate all data for this model, passing each SingularModel a database
   * handle $dbh (or null) and the name of the singular class to be created. If
   * no singular class name is given, this class will try to infer it by finding
   * the singular form of this class name
   */
  function __construct($dbh=null, $singularClassName="") {
    if (!$singularClassName) $singularClassName = $this->inferSingularClassName();
    $this->singularClassName = $singularClassName;
    self::$data = $this->instantiateData($this->setupData());
  }
  
  /*
   * Return an array of hashes containing data to use in creating the singular
   * version of this model
   */
  abstract protected function setupData();
  
  /*
   * Return all data contained within this fixture as an array of instantiated
   * objects
   */
  final function getAll() {
    return self::$data;
  }
  
  /*
   * Convenience method for filtering the data using a callback that takes an
   * object from the collection and returns true or false depending on whether
   * or not to keep the object in the filtered collection
   */
  final function find($callback) {
    $found = array();
    foreach (self::$data as $object) {
      if ($callback($object)) {
        array_push($found, $object);
      }
    }
    
    return $found;
  }

  /*
   * Cast an array of singulars to a hash type that can be consumed by Smarty
   * - ex: $plural::hashify($singulars)
   */
  static function hashify($singulars) {
    $hashedSingulars = array();
    foreach ($singulars as $singular) {
      array_push($hashedSingulars, $singular->hashify());
    }
    
    return $hashedSingulars;
  }
  
  private function inferSingularClassName() {
    die("This functionality is not yet implemented. For now, pass the name of the singular class to create manually");
  }
  
  private function instantiateData($arrayOfHashes) {
    $objects = array();
    $dbh = $this->dbh;
    $class = $this->singularClassName;
    foreach ($arrayOfHashes as $hash) {
      array_push($objects, new $class($dbh, $hash));
    }
    
    return $objects;
  }
}
?>
