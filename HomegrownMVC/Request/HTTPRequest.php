<?php
namespace HomegrownMVC\Request;

/*
 * Sample HTTPRequest to be passed to Homegrown's context
 *
 * Author: Bremen Braun
 */
class HTTPRequest {
	private $requestInfo;
	private $method;
	private $fields;
	private $routeName;

	function __construct() {
		$this->requestInfo = $_REQUEST;
		$this->method = $_SERVER['REQUEST_METHOD'];
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
	 * Return whether or not all fields exist in the request
	 */
	function hasFields($fieldNames) {
		foreach ($fieldNames as $name) {
			if (!$this->hasField($name)) {
				return false;
			}
		}

		return true;
	}

	/*
	 * Setting a field's value might be useful when sharing data between routes
	 * within a controller with forwarding
	 */
	function setFieldValue($fieldName, $fieldValue) {
		$this->requestInfo[$fieldName] = $fieldValue;
		array_push($this->fields, $fieldName);
	}

	/*
	 * Return the value for a given field
	 */
	function getFieldValue($fieldName) {
		$value = "";
		if ($this->hasField($fieldName)) {
			$value = $this->requestInfo[$fieldName];
		}

		return $value;
	}

	/*
	 * The method for this request; i.e. 'GET', 'HEAD', 'POST', 'PUT'
	 */
	function getMethod() {
		return $this->method;
	}

	/*
	 * In some cases, a route may accept two mutually exclusive fields, such as
	 * 'name' or 'id'. This function simply returns the value for the first
	 * field encountered which has one. If $mapped is set to true, returns a map
	 * of the found value with its field as field => field_val. Else, only the
	 * value is returned
	 */
	function getFirstValue($fields, $mapped=false) {
		foreach ($fields as $field) {
			$value = $this->getFieldValue($field);
			if ($value) {
				if ($mapped) {
					return array(
						$field => $value
					);
				}

				return $value;
			}
		}

		return "";
	}

	/*
	 * Return all values for fields as an array ordered the same as the fields
	 * which were passed in
	 */
	function listFieldValues($fields) {
		$values = array();
		foreach ($fields as $field) {
			array_push($values, $this->getFieldValue($field));
		}

		return $values;
	}

	/*
	 * Dump request parameters to a hash
	 */
	function getFieldDump() {
		return $this->requestInfo;
	}

	/*
	 * Check that all fields exist and, optionally, that they all have values
	 */
	function validateFields($fields, $withValues=false) {
		foreach ($fields as $field) {
			if (!$this->fieldExists($field)) {
				return false;
			}
			if ($withValues) {
				if (!$this->getFieldValue($field)) {
					return false;
				}
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

	/*
   * Converts the request into a string for a GET request
	 */
	function toGetString($includeQuestionmark=true) {
		$params = array();
		foreach ($this->fields as $key) {
			$val = $this->getFieldValue($key);

			$str = !!$val ? "$key=$val" : $key;
			array_push($params, $str);
		}
		
		$start = ($includeQuestionmark && $params) ? '?' : '';
		return $start . implode('&', $params);
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
