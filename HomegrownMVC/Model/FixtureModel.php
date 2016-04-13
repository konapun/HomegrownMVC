<?php
namespace HomegrownMVC\Model;

use HomegrownMVC\Util\NameInferer as NameInferer;

/*
 * A model which is instantiated with data without needing a database
 *
 * Author: Bremen Braun
 */
abstract class FixtureModel {
  private $data;
  private $dbh;
  private $ignoreExtraSetupFields;
  private $singularClassName;

  /*
   * Instantiate data collection either by data returned via `setupData` or by
   * passing an array of SingularModels as the second argument
   */
  function __construct($dbh=null, $singularClassName="") {
    if (is_array($singularClassName)) {
      $this->instantiateFromData($singularClassName);
    }
    else {
      $this->instantiateByClassName($singularClassName);
    }

    $this->dbh = $dbh;
    $this->ignoreExtraSetupFields = false;
  }

  /*
   * Return an array of hashes containing data to use in creating the singular
   * version of this model
   */
  abstract protected function setupData();

  /*
   * Get the database handle used to create this object
   */
  final function getDatabaseHandle() {
    return $this->dbh;
  }

  /*
   * Return all data contained within this fixture as an array of instantiated
   * objects
   */
  final function getAll($sortFn=null) {
    $data = $this->data;
    if ($sortFn) usort($data, $sortFn);

    return $data;
  }

  /*
   * Convenience method for filtering the data using a callback that takes an
   * object from the collection and returns true or false depending on whether
   * or not to keep the object in the filtered collection
   */
  final function find($callback) {
    $found = array();
    foreach ($this->data as $object) {
      if ($callback($object)) {
        array_push($found, $object);
      }
    }

    return $found;
  }

  final function filter($filterFn, $asObj=false) {
    $filtered = array_filter($this->getAll(), $filterFn);
    if ($asObj) {
      $filtered = $this->copy($filtered);
    }
    return $filtered;
  }

  final function merge($otherSingulars) {
    $merged = array_merge($this->getAll(), $otherSingulars);
    return $this->copy($merged);
  }

  /*
   * Copy an array of `SingularModel`s into a fixture model
   */
  final function copy($singulars) {
    $clone = new static($this->dbh, $this->singularClassName);
    $clone->data = $singulars;
    return $clone;
  }

  /*
   * Write changes from $singular back to the file, if possible
   */
   /*
  function commit($updatedUser) {
    $rows = array();
    $schema = array();
    foreach ($this->getAll() as $user) {
      if (!$schema) { // get schema and write header
        $schema = $user->getSchema();
        $fields = array();
        foreach ($schema as $column) {
          $fields[$column] = $column;
        }

        array_push($rows, $fields);
      }

      if ($user->equals($updatedUser)) $user = $updatedUser;
      $fields = array();
      foreach ($schema as $column) {
        $fields[$column] = $user->getValue($column);
      }

      array_push($rows, $fields);
    }

    $this->importer->exportData($rows); // FIXME
  }
  */

  /*
   * When building SingularModels from `setupData`, allow passing extra fields
   * other than the required to the model's builder
   */
  final protected function ignoreExtraSetupFields($bool=true) {
    $this->ignoreExtraSetupFields = $bool;
  }

  /*
   * Cast an array of singulars to a hash type that can be consumed by Smarty
   * - ex: $plural::hashify($singulars)
   */
  static function hashify($singulars) {
    if (!is_array($singulars)) $singulars = array($singulars);

    $hashedSingulars = array();
    foreach ($singulars as $singular) {
      array_push($hashedSingulars, $singular->hashify());
    }

    return $hashedSingulars;
  }

  /*
   * Instantiate by manually passing data. This is useful for chained filters
   * (etc. a `find` result is wrapped in a new FixtureModel instance so `find`
   * can be called subsequently on the new collection
   )
   */
  private function instantiateFromData($data) {
    $this->data = $data;
  }

  /*
   * Instantiate all data for this model, passing each SingularModel a database
   * handle $dbh (or null) and the name of the singular class to be created. If
   * no singular class name is given, this class will try to infer it by finding
   * the singular form of this class name
   */
  private function instantiateByClassName($singularClassName) {
    if (!$singularClassName) $singularClassName = $this->inferSingularClassName(self);
    $this->singularClassName = $singularClassName;
    $this->data = $this->instantiateData($this->setupData());
  }

  private function inferSingularClassName($class) {
    $inferer = new NameInferer(true);
		return $inferer->inferSingularClassName($class);
  }

  private function instantiateData($arrayOfHashes) {
    $objects = array();
    $dbh = $this->dbh;
    $class = $this->singularClassName;
    foreach ($arrayOfHashes as $hash) {
      if (is_array($hash)) { // instantiate from data
        array_push($objects, new $class($dbh, $hash, $this->ignoreExtraSetupFields));
      }
      else { // or this could be the object itself
        array_push($object, $hash);
      }
    }

    return $objects;
  }
}
?>
