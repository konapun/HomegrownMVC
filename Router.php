<?php 
/*
 * Manage routes by finding the correct controller
 * 
 * Author: Bremen Braun
 */

class Router {
	private $controllers;
	private $forwards;
	
	function __construct() {
		$this->controllers = array();
		$this->forwards = array();
	}
	
	/*
	 * Redirect one route to another without altering the URL
	 */
	function redirect($from, $to) {
		$this->forwards[$from] = $to;
	}
	
	/*
	 * Add a controller to search for a route
	 */
	function addController($controller) {
		array_push($this->controllers, $controller);
	}
	
	/*
	 * Invoke an action from a controller which provides the specified route.
	 * If no route is given, the current URL is used.
	 * Returns true or false depending on whether or not the route was handled
	 */
	function handleRoute($route=null) {
		if ($route == null) $route = $_SERVER['REQUEST_URI'];
		
		/* See if any controller can handle the URI */
		return $this->forceFindRoute($route);
	}
	
	/*
	 * Try to locate a route both with and without a trailing /
	 * Returns true or false depending on whether or not the route was handled
	 */
	private function forceFindRoute($route) {
		$foundRoute = $this->findRoute($route);
		if (!$foundRoute) { // try finding route with or without a trailing /, depending on whether or not the original had it
			if (substr($route, -1) == '/') {
				$route = substr($route, 0, strlen($route)-1);
			}
			else {
				$route .= '/';
			}
			
			$foundRoute = $this->findRoute($route);
		}
		
		return $foundRoute;
	}
	
	/*
	 * Attempt to invoke a controller action for a given route
	 * Returns true or false depending on whether or not the route was handled
	 */
	private function findRoute($route) {
		$route = $this->getForwardedRoute($route);
		$foundRoute = false;
		foreach ($this->controllers as $controller) {
			try {
				$controller->findRoute($route); 
				$foundRoute = true;
				break;
			}
			catch (Exception $e) {}
		}
		
		return $foundRoute;
	}
	
	/*
	 * Check if a route maps to a redirect.
	 * If so, return the forwarded route, else the route as passed in
	 */
	private function getForwardedRoute($route) {
		$forwarded = $route;
		foreach ($this->forwards as $from => $to) {
			if ($route == $from) {
				$forwarded = $to;
				break;
			}
		}
		
		return $forwarded;
	}
}

?>