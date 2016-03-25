<?php
namespace HomegrownMVC\Util;

use HomegrownMVC\Error\NameInferenceException as NameInferenceException;
use HomegrownMVC\Error\ClassNotFoundException as ClassNotFoundException;

class NameInferer {
  private $requireClassExistence;

  function __construct($requireClassExistence=false) {
    $this->requireClassExistence = $requireClassExistence;
  }

  /*
   * Find the singular class name for a pluralized class
   */
  function inferSingularClassName($class) {
    $singular = "";
    if (preg_match('/(.+?)(es|s)$/', $class, $matches)) {
      $singular = $matches[1];
    }
    else {
      throw new NameInferenceException("Can't infer singular model name from class $class");
    }
    if ($this->requireClassExistence) {
      if (!class_exists($singular)) {
        throw new ClassNotFoundException("Class '$singular' does not exist");
      }
    }
    return $singular;
	}
}
?>
