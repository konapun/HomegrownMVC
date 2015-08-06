<?php
namespace HomegrownMVC\Controller;

use HomegrownMVC\Error\MalformedUrlException as MalformedUrlException;
use HomegrownMVC\Error\RouteNotDefinedException as RouteNotDefinedException;

/*
 * A simple, lightweight controller
 *
 * Author: Bremen Braun
 */
abstract class BaseController implements IController {
	private $context;
	private $controllerBase;
	private $before;
	private $after;

	function __construct($context) {
		$this->context = $context;
		$this->controllerBase = "";
		$this->before = array();
		$this->after = array();
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
	protected function beforeRoutes($cb) {
		array_push($this->before, $cb);
	}

	/*
	 * Give a callback to run after invoking a route
	 *
	 * This can be useful for displaying views that are common between all routes
	 */
	protected function afterRoutes($cb) {
		array_push($this->after, $cb);
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
		if ($action && array_key_exists($action, $routes)) {
			$controllerAction = $routes[$action];

			$workflow = array_merge($this->before, array_merge(array($controllerAction), $this->after)); // run beforeRoutes, route action, and afterRoutes, halting prematurely if any function returns false
			foreach ($workflow as $cb) {
				if ($cb($context) === false) {
					break;
				}
			}
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
