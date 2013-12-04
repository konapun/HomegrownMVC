<?php
include_once('errors/BuildException.php');

/*
 * A singular model is the return type of its plural model queries
 * 
 * Author: Bremen Braun
 */
abstract class SingularModel {
	private $fields;
	
	/*
	 * Create a singular model either from properties or by querying on a field
	 */
	final function __construct($dbh, $fields) {
		$this->fields = array();
		foreach ($this->listProperties() as $property) {
			$this->fields[$property] = null;
		}
		
		if (count($fields) > 1) {
			$this->constructFromProperties($fields);
		}
		else {
			$builders = $this->setupBuilders();
			if (!$builders[$field]) {
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
			$this->cloneIntoThis($builder($dbh));
		}
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
	 * Returns a map of properties to its builder
	 */
	protected function setupBuilders($property);
	
	/*
	 * This is the function called when the singular model is being constructed
	 * from its plural. The default behavior is to clone the properties into
	 * this verbatim, but you can override it if you need.
	 */
	protected function constructFromProperties($properties) {
		$nprops = count($properties);
		$nfields = count($this->fields);
		
		$fieldstr = ""; // string of fields used for error reporting
		foreach ($this->fields as $field) {
			if ($fieldstr) {
				$fieldstr .= ' ';
			}
			$fieldstr .= $field;
		}
		
		$errPrefix = "";
		if ($nprops > $nfields) {
			$errPrefix = "Too many properties given.";
		}
		else if ($nprops < $nfields) {
			$errPrefix = "Too few properties given.";
		}
		if ($errPrefix) {
			throw new BuildException("$errPrefix Requires: $fieldstr");
		}
		
		foreach ($properties as $pkey => $pval) {
			if (!$this->setValue($pkey, $pval)) {
				throw new BuildException("Model has no property '$pkey'. Requires: $fieldstr");
			}
		}
	}
	
	/*
	 * Return an array of all the fields this model has
	 */
	protected function listProperties();
	
	/*
	 * Attempt to set the value of a field, returning false if there is no field
	 * with that key for this model
	 */
	private function setValue($field, $val) {
		if (!array_key_exists($field, $this->fields)) {
			return false;
		}
		
		this->fields[$pkey] = $pval;
		return true;
	}
	
	/*
	 * Clone a model of the same type into this model
	 */
	private function cloneIntoThis($plural) {
		foreach ($this->fields as $field) {
			$this->fields[$field] = $plural->getValue($field);
		}
	}
}
?>
