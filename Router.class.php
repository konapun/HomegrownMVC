<?php 
/*
 * Manage routes by finding the correct controller
 * 
 * Author: Bremen Braun
 */
include_once('controllers/ErrorController.php');

class Router {
	private $context;
	private $controllers;
	private $forwards;
	
	function __construct($context) {
		$this->context = $context;
		$this->controllers = array();
		$this->forwards = array();
	}
	
	function redirect($from, $to) {
		$this->forwards[$from] = $to;
	}
	
	function addController($controller) {
		array_push($this->controllers, $controller);
	}
	
	function handleRoute($route=null) {
		if ($route == null) $route = $_SERVER['REQUEST_URI'];
		
		/* See if any controller can handle the URI */
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
		if (!$foundRoute) {
			$errorController = new ErrorController($this->context);
			$errorController->findRoute('404');
		}
	}
	
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