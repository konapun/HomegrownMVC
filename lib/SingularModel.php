<?php
include_once('errors/BuildException.php');
include_once('errors/ResultNotFoundException.php');

/*
 * A singular model is the return type of its plural model queries
 * 
 * Author: Bremen Braun
 */
abstract class SingularModel {
	private $fields;
	private $anomalies;
	private $dbh;
	
	/*
	 * Create a singular model either from properties or by querying on a field
	 */
	final function __construct($dbh, $fields) {
		$this->dbh = $dbh;
		$this->fields = array();
		$this->anomalies = $this->handlePropertyConstructionAnomalies();
		foreach ($this->listProperties() as $property) {
			$this->fields[$property] = null;
		}
		
		if (count($fields) > 1) {
			$this->constructFromProperties($fields);
		}
		else {
			$fkeys = array_keys($fields);
			$field = $fkeys[0];
			$builders = $this->setupBuilders($dbh);
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
			$found = $builder($fields[$field]);
			if (!$found) {
				throw new ResultNotFoundException("Couldn't locate a result for $field '" . $fields[$field] . "'");
			}
			$this->cloneIntoThis($found[0]);
		}
		
		$this->configure();
	}
	
	final function getDatabaseHandle() {
		return $this->dbh;
	}
	
	/*
	 * Generic way of getting a model's field value
	 */
	function getValue($field) {
		if (!isset($this->fields[$field])) {
			throw new InvalidArgumentException("Model has no field '$field'");
		}
		
		return $this->fields[$field];
	}
	
	/*
	 * Return a hashed version of this model for easy consumption by the view
	 * engine
	 */
	function hashify() {
		return $this->fields;
	}
	
	/*
	 * Define a function to run after this object is created
	 */
	function configure() {}
	
	/*
	 * This is the function called when the singular model is being constructed
	 * from its plural. The default behavior is to clone the properties into
	 * this verbatim. However, if you require special handling of particular
	 * fields (converting a primitive from the database return to an object),
	 * you can handle these in `handlePropertyConstructionAnomalies`.
	 */
	final protected function constructFromProperties($properties) {
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
			if (!$this->setValue($pkey, $pval)) {
				throw new BuildException("Model has no property '$pkey'. Requires: $fieldstr");
			}
		}
	}
	
	/*
	 * Returns a map of properties to its builder
	 */
	abstract protected function setupBuilders($property);
	
	/*
	 * Return an array of all the fields this model has
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
	 * Attempt to set the value of a field, returning false if there is no field
	 * with that key for this model
	 */
	private function setValue($field, $val) {
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
	 * Clone a model of the same type into this model
	 */
	private function cloneIntoThis($plural) {
		foreach ($this->fields as $fkey => $fval) {
			$this->fields[$fkey] = $plural->getValue($fkey);
		}
	}
}
?>
