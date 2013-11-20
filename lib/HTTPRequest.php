<?php
/*
 * Sample HTTPRequest to be passed to Homegrown's context
 *
 * Author: Bremen Braun
 */
class HTTPRequest {
	private $requestInfo;
	private $fields;
	private $routeName;
	
	/* CONSTRUCTOR */
	function __construct() {
		$this->requestInfo = $_REQUEST;
		$this->fields = array_keys($this->requestInfo);
		$this->routeName = $this->formatRoute($_SERVER['REQUEST_URI']);
	}

	function routeName() {
		return $this->routeName;
	}
	
	/*
	 * Return whether or not a field exists in the request
	 */
	function hasField($fieldName) {
		return array_key_exists($fieldName, $this->requestInfo);
	}
	
	/*
	 * Return the value for a given field
	 */
	function fieldValue($fieldName) {
		$value = "";
		if ($this->hasField($fieldName)) {
			$value = $this->requestInfo[$fieldName];
		}
		
		return $value;
	}
	
	/*
	 * Return all values for fields as an array ordered the same as the fields
	 * which were passed in
	 */
	function listFieldValues($fields) {
		$values = array();
		foreach ($fields as $field) {
			array_push($values, $this->fieldValue($field));		
		}

		return $values;
	}
	
	/*
	 * Check that all fields exist
	 */
	function validateFields($fields) {
		foreach ($fields as $field) {
			if (!$this->hasField($field)) {
				return false;
			}
		}
		
		return true;
	}
	
	/*
	 * Check that at least one field exists
	 */
	function hasAtLeastOne($fields) {
		foreach ($fields as $field) {
			if ($this->hasField($field)) {
				return true;
			}
		}
		
		return false;
	}
	
	private function formatRoute($uri) {
		$route = $uri;
		$pstart = strrpos($uri, '?');
		if ($pstart !== false) {
			$route = substr($route, 0, $pstart);
		}
		
		return $route;
	}
}

?>
