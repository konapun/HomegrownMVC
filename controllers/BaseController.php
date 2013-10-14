<?php
include_once('errors/MalformedUrlException.php');
include_once('errors/RouteNotDefinedException.php');

/*
 * A simple, lightweight controller
 * 
 * Author: Bremen Braun
 */
abstract class BaseController {
	private $context;
	private $controllerBase;
	private $callbacks;
	
	function __construct($context) {
		$this->context = $context;
		$this->controllerBase = "";
		$this->callbacks = array();
	}
	
	/*
	 * The concrete controller defines this which returns a map of routes to their action
	 */
	abstract protected function setupRoutes();
	
	/*
	 * Give a callback to run before invoking a route
	 * 
	 * This can be useful for setting active classes in a template based on a route
	 */
	protected function eachRoute($cb) {
		array_push($this->callbacks, $cb);
	}
	
	/*
	 * This function is called by the router
	 */
	function invokeRoute($url) {
		if ($this->controllerBase && substr($this->controllerBase, -1) != '/') {
			$this->controllerBase .= '/';
		}
		
		$routes = $this->setupRoutes();
		$parsed = $this->parseURL($url);
		$action = $parsed['action'];
		$args = $parsed['args'];
		$context = $parsed['context'];
		$matchPath = $this->controllerBase . $action;
		if ($this->controllerBase && (($basepos = strpos($action, $this->controllerBase)) !== false)) {
			$action = substr($action, $basepos + strlen($this->controllerBase));
		}
		if (array_key_exists($action, $routes)) {
			$controllerAction = $routes[$action];
		
			foreach ($this->callbacks as $cb) {
				if ($cb($context) === false) break;
			}
			return $controllerAction($context);
		}
		else {
			throw new RouteNotDefinedException("Controller does not define an action for route $action"); // User can throw a 404 or something
		}
	}
	
	/*
	 * Concrete controllers may call this to shorten paths
	 */
	protected function controllerBase($baseURL) { // where baseURL is something like '/search' or '/user' for route nesting
		$this->controllerBase = $baseURL;
	}
	
	private function parseURL($url) {
		@list($action, $args) = preg_split('/\?/', $url); // args may not exist
		if ($args) {
			$args = $this->parseArguments($args);
		}
		else {
			$args = array();
		}
		
		return array(
			'context' => $this->context,
			'action'  => $action,
			'args'    => $args, // not a big deal since it's available through the context anyway
		);
	}
	
	private function parseArguments($argstr) {
		$args = array();
		$kvs = preg_split('/&/', $argstr);
		if (is_array($kvs)) {
			foreach ($kvs as $kv) {
				$split = preg_split('/=/', $kv);
				$key = $split[0];
				$value = null;
				if (count($split) > 1) { 
					$value = $split[1];
				}
				
				$args[$key] = $value;
			}
		}
		
		return $args;
	}
}
?>
