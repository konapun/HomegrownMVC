<?php
namespace HomegrownMVC\Controller;

/*
 * Allow route variables of the form :var in the route name
 *
 * author: Bremen Braun
 */
abstract class WildcardController extends BaseController {
	private $url;
	private $wcChar = ':';
	
	/*
	 * The concrete controller will return a map of routes to actions
	 * the same as before, but this time the route name can include
	 * wildcards which are expanded to their values and passed to the
	 * matched controller action
	 */
	abstract protected function setupWildcardRoutes();
	
	/*
	 * Expand wildcards before passing control to the base controller
	 */
	final protected function setupRoutes() {
		return $this->expandRoutes($this->url, $this->setupWildcardRoutes());
	}
	
	/*
	 * By default, wildcards are preceded by a colon, but you can change it
	 * to match a different character if you'd like
	 */
	protected function setWildcardCharacter($char) {
		if (strlen($char) > 1) {
			throw new InvalidArgumentException("Wildcard must be a single character, not a string");
		}
		$this->wcChar = $char;
	}
	
	/*
	 * Attempt to match a route in this controller
	 */
	function invokeRoute($url) {
		$this->url = $url;
		parent::invokeRoute($url);
	}
	
	/*
	 * Replace variable fields in the user-defined routes
	 * with specific versions the BaseController can understand
	 *
	 * Since the concrete controller will want to know what the
	 * actual value of the parameter passed was but the base
	 * controller's invokeRoute only passes the context, we'll
	 * need to replace the original callback with a currying
	 * version which passes a params array
	 * 
	 * Ex:
	 * - url: www.example.com/user/1234/profile
	 * - routes:
	 *   - '/user/:id/profile'
	 *   - '/user/:id/photos'
	 * => Creates routes
	 *   - '/user/1234/profile'
	 *   - '/user/1234/photos'
	 */
	private function expandRoutes($url, $routes) {
		$expandedRoutes = array();
		$urlFields = explode('/', strtok($url, '?'));
		$urlFieldCount = count($urlFields);
		foreach ($routes as $route => $action) {
			$fields = explode('/', $route);
			$fieldIndex = 0;
			$params = array(); // wildcard params
			if (count($fields) !== $urlFieldCount) continue;
			
			$expandedRoute = array();
			foreach ($fields as $field) {
				if (strlen($field) > 0 && $field[0] == $this->wcChar) { // it's a wildcard
					$val = $urlFields[$fieldIndex];
					
					$params[substr($field, 1)] = urldecode($val);
					$field = $val;
				}
				
				array_push($expandedRoute, $field);
				$fieldIndex++;
			}
			
			$expandedRoute = implode('/', $expandedRoute); // rejoin the route string
			$expandedRoutes[$expandedRoute] = function($context) use ($params, $action) { // curry to match the signature that parent::invokeRoute understands
				$action($context, $params);
			};
		}
		
		return $expandedRoutes;
	}
}
?>
