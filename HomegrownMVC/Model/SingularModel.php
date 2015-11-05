<?php
namespace HomegrownMVC\Model;

use HomegrownMVC\Behaviors\Hashable;
use HomegrownMVC\Error\BuildException as BuildException;
use HomegrownMVC\Error\MultipleResultsException as MultipleResultsException;
use HomegrownMVC\Error\ResultNotFoundException as ResultNotFoundException;
use HomegrownMVC\Error\VisibilityException as VisibilityException;
use HomegrownMVC\Error\MethodCallException as MethodCallException;

/*
 * A singular model is the return type of its plural model queries
 *
 * Author: Bremen Braun
 */
abstract class SingularModel implements \HomegrownMVC\Behaviors\Hashable {
	private $fields;
	private $anomalies;
	private $reflection;
	private $cache;
	private $dbh;

	/*
	 * Create a singular model either from properties or by querying on a field
	 */
	final function __construct($dbh, $fields) {
		$this->dbh = $dbh;
		$this->reflection = null;
		$this->fields = array();
		$this->cache = array();
		$this->anomalies = $this->handlePropertyConstructionAnomalies();
		foreach ($this->listProperties() as $maybeKey => $maybeDefault) {
			if (is_int($maybeKey)) { // Probably the property index since we require keys be strings, the real key will be in $maybeDefaul
				$this->fields[$maybeDefault] = null;
			}
			else { // Property with default value
				$this->fields[$maybeKey] = $maybeDefault;
			}
		}

		$fieldCount = count($fields);
		$fields = $this->giveDefaults($fields);
		if ($fieldCount > 1) {
			$this->constructFromProperties($fields);
		}
		else {
			$this->constructFromBuilder($fields);
		}

		$this->configure();
	}

	final function getDatabaseHandle() {
		return $this->dbh;
	}

	/*
	 * Return whether or not a field is set for this model
	 */
	function hasField($field) {
		return array_key_exists($field, $this->fields);
	}

	/*
	 * Generic way of getting a model's field value
	 */
	function getValue($field) {
		if (!$this->hasField($field)) {
			throw new \InvalidArgumentException("Model " . get_class($this) . " has no field '$field'");
		}

		return $this->fields[$field];
	}

	/*
	 * Attempt to set the value of a field, returning false if there is no field
	 * with that key for this model
	 */
	function setValue($field, $val) {
		if (!array_key_exists($field, $this->fields)) {
			return false;
		}

		if (isset($this->anomalies[$field])) { // custom handling for special cases
			$convertFn = $this->anomalies[$field];
			$this->fields[$field] = $convertFn($val);
		}
		else { // value is a primitive (default)
			$this->fields[$field] = $val;
		}
		return true;
	}

	/*
	 * Return a hashed version of this model for easy consumption by the view
	 * engine. If the model contains cacheable methods and a cache key was set,
	 * cached values will automatically be exported into the hash.
	 */
	function hashify() {
		$hash = $this->fields;
		foreach ($this->cache as $method => $props) {
			if ($props['key']) { // not falsey - include cached value in output
				$hash[$props['key']] = $props['value'];
			}
		}

		return $hash;
	}

	/*
	 * Return whether or not this is equal to another Singular Model. You can
	 * override this to change the behavior, for instance to check equality based
	 * on an ID
	 */
	function equals($singular) {
		foreach ($singular->getSchema() as $column) {
			if ($this->hasField($column) && $this->getValue($column) == $singular->getValue($column)) continue;
			return false;
		}

		return true;
	}

	/*
	 * Return a clone of this object with the same field values
	 */
	function cloneObject() {
		$fields = array();
		foreach ($this->getSchema() as $prop) {
			$fields[$prop] = $this->getValue($prop);
		}
		return new static($this->getDatabaseHandle(), $fields);
	}

	/*
   * Cache a function `$methodName`'s output so potentially time-consuming
   * operations are run only once. `$hashKey` is the hash key the value will be
   * associated with when this object is hashified. If $hashKey is falsey, the
   * cached value will not be placed into the hashified version of this model.
   * Configure cache settings in `SingularModel::configure`.
	 */
	function cache($methodName, $hashKey=false) {
		$reflection = $this->reflect();
		if (!$reflection->hasMethod($methodName)) {
			throw new \InvalidArgumentException("Can't cache for method '$methodName' - no such method");
		}
		$method = $reflection->getMethod($methodName);
		if ($method->getNumberOfRequiredParameters() > 0) {
			throw new \InvalidArgumentException("Can't cache for method '$methodName' - caching is currently only available for methods which take no arguments");
		}

		$method->setAccessible(false); // this way, the original method can't be called by the user which forces the call to go through __call, which uses the cacheable version
		if ($method->isPublic()) {
			throw new VisibilityException("Error caching method '$methodName' - Cacheable methods must be declared as 'protected' or 'private'");
		}

		$this->cache[$methodName] = array(
			'key'   => $hashKey,
			'value' => null
		);
	}

	/*
	 * Return the reference ID for this object
	 */
	function __toString() {
		return spl_object_hash($this);
	}

	/*
   * Intercept non-public method calls in case the cache is to be used
	 */
	final function __call($method, $args) {
		if (array_key_exists($method, $this->cache)) {
			$cache = $this->cache[$method];
			if (!is_null($cache['value'])) {
				return $cache['value'];
			}
			else {
				$ret = call_user_func_array(array($this, $method), $args);
				$this->cache[$method]['value'] = $ret;
				return $ret;
			}
		}

		throw new MethodCallException("Can't locate method '$method' in class " . get_class($this));
	}

	final function getSchema() {
		return $this->listProperties();
	}

	/*
	 * Define a function to run after this object is created
	 */
	protected function configure() {}

	/*
	 * Returns a map of properties to its builder
	 */
	protected function setupBuilders($dbh) {
		return array();
	}

	/*
	 * Return an array of all the fields this model has. You can optionally
   * specify a default value by giving at as the hash value for the key
   *   ex. return array(
   *     'sample_key_without_default',
   *     'sample_key_with_default' => 'default_value' // won't give an error if no value is given when constructing this instance
   *   )
	 */
	abstract protected function listProperties();

	/*
	 * By default, `constructFromProperties` sets the values of its fields by
	 * primitives obtained through database queries (via a PluralModel). If you
	 * need to convert it to an object type, define a mapping of the field to
	 * a function which performs the conversion.
	 */
	protected function handlePropertyConstructionAnomalies() {
		return array();
	}

	/*
	 * This is the function called when the singular model is being constructed
	 * from its plural. The default behavior is to clone the properties into
	 * this verbatim. However, if you require special handling of particular
	 * fields (converting a primitive from the database return to an object),
	 * you can handle these in `handlePropertyConstructionAnomalies`.
	 */
	private function constructFromProperties($properties) {
		$nprops = count($properties);
		$nfields = count($this->fields);

		$fieldstr = ""; // string of fields used for error reporting
		foreach ($this->fields as $fkey => $fval) {
			if ($fieldstr) {
				$fieldstr .= ' ';
			}
			$fieldstr .= $fkey;
		}

		$errPrefix = "";
		if ($nprops > $nfields) {
			$errPrefix = "Too many properties given.";
		}
		else if ($nprops < $nfields) {
			$errPrefix = "Too few properties given.";
		}
		if ($errPrefix) {
			$propstr = "";
			foreach ($properties as $pkey => $pval) {
				if ($propstr) {
					$propstr .= ' ';
				}
				$propstr .= $pkey;
			}

			throw new BuildException("$errPrefix Requires: $fieldstr (Got: $propstr)");
		}

		foreach ($properties as $pkey => $pval) {
			if (is_null($pval)) {
				throw new BuildException("Null value given for property '$pkey'. Requires: $fieldstr");
			}
			else if (!$this->setValue($pkey, $pval)) {
				throw new BuildException("Model has no property '$pkey'. Requires: $fieldstr");
			}
		}
	}

	/*
	 * Construct a full singular model from a single property by calling a
	 * specific builder function (usually by querying the plural form)
	 */
	private function constructFromBuilder($fields) {
		$fkeys = array_keys($fields);
		$field = $fkeys[0];
		$builders = $this->setupBuilders($this->dbh);
		if (!isset($builders[$field])) {
			$keys = array_keys($builders);
			$keystr = "";
			foreach ($keys as $key) {
				if ($keystr) {
					$keystr .= ", ";
				}
				$keystr .= $key;
			}

			throw new BuildException("Requires one of the following fields for automated build: $keystr");
		}

		$builder = $builders[$field];
		$found = $this->callBuilderWithArgs($builder, $fields[$field]);
		if (!$found) {
			throw new ResultNotFoundException("Couldn't locate a result for $field '" . $fields[$field] . "'");
		}

		$result = $found;
		if (is_array($result)) {
      if (count($result) == 1) {
        $result = $result[0];
      }
      else {
        throw new MultipleResultsException($result, "Builder returned multiple results (" . count($result) . ")");
      }
		}

		$this->cloneIntoThis($result);
	}

	/*
	 * Call a builder using either a single argument or by treating an array as
	 * an arguments list
	 */
	private function callBuilderWithArgs($builder, $args) {
		if (is_array($args)) {
			return call_user_func_array($builder, $args);
		}
		else {
			return $builder($args);
		}
	}

  private function giveDefaults($fields) {
    foreach ($this->fields as $fieldKey => $fieldVal) {
      if (!array_key_exists($fieldKey, $fields) || is_null($fields[$fieldKey])) {
        $fields[$fieldKey] = $fieldVal;
      }
    }

    return $fields;
  }

	/*
	 * Clone a model of the same type into this model
	 */
	private function cloneIntoThis($plural) {
		foreach ($this->fields as $fkey => $fval) {
			$this->fields[$fkey] = $plural->getValue($fkey);
		}
	}

	private function reflect() {
		if ($this->reflection == null) {
			$this->reflection = new \ReflectionClass($this);
		}
		return $this->reflection;
	}
}
?>
