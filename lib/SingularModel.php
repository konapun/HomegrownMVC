<?php
include_once('errors/BuildException.php');

/*
 * A singular model is the return type of its plural model queries
 * 
 * Author: Bremen Braun
 */
abstract class SingularModel {
	private $fields;
	private $dbh;
	
	/*
	 * Create a singular model either from properties or by querying on a field
	 */
	final function __construct($dbh, $fields) {
		$this->dbh = $dbh;
		$this->fields = array();
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
			$this->cloneIntoThis($found[0]);
		}
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
	 * This is the function called when the singular model is being constructed
	 * from its plural. The default behavior is to clone the properties into
	 * this verbatim, but you can override it if you need.
	 */
	protected function constructFromProperties($properties) {
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
	 * Attempt to set the value of a field, returning false if there is no field
	 * with that key for this model
	 */
	private function setValue($field, $val) {
		if (!array_key_exists($field, $this->fields)) {
			return false;
		}
		
		$this->fields[$field] = $val;
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
