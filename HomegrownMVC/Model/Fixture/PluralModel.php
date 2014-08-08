<?php
namespace HomegrownMVC\Model\Fixture;

/*
 * Similar to HomegrownMVC\Model\PluralModel, but made especially for handling
 * collections of data without a database
 *
 * Author: Bremen Braun
 */
abstract class PluralModel {
  private static $data;
  private $singularClass;

  function __construct($singularClass="") {
    if (!$singularClass) $singularClass = $this->inferSingularClassName();
    $this->singularClass = $singularClass;
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
  final function filter($callback) {
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
    $pluralName = get_class();
    echo "Inferring singular classname from $pluralName";
    // TODO
    return $pluralName;
  }

  private function instantiateData($arrayOfHashes) {
    $objects = array();
    $class = $this->singularClass;
    foreach ($arrayOfHashes as $hash) {
      array_push($objects, new $class($hash))
    }

    return $objects;
  }
}
?>
