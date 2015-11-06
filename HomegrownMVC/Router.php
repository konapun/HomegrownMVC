<?php
namespace HomegrownMVC;

/*
 * Manage routes by finding the correct controller
 *
 * Author: Bremen Braun
 */
class Router {
	private $debug;
	private $exceptionHandler;
	private $baseRoute;
	private $controllers;
	private $forwards;

	function __construct($debug=false) {
		$this->debug = $debug;
		$this->baseRoute = "";
		$this->controllers = array();
		$this->forwards = array();
		$this->events = array( // events triggered on handleRoute
			'before' => array(),
			'after'  => array()
		);
		$this->exceptionHandler = function($e) {
			echo $e->getMessage() . "<br>\n";
		};
	}

	/*
   * When debug is set to true, a custom error handler will be used for
   * exceptions which are not of type `RouteNotDefinedException`
	 */
	function debug($debug=true) {
		$this->debug = $debug;
	}

	/*
   * Set a custom error handler. The default action is to echo the error
   * message to the page. $fn should be a function which takes a single argument
   * which is the Exception object being thrown.
	 */
	function handleException($fn) {
		$this->exceptionHandler = $fn;
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
	 * Automatically load all controllers in a directory, given there is
	 * a single controller per file and the controller class has the same
	 * name as the file, minus the .php extension
	 */
	function autoloadControllers($context, $directory='controllers') {
		$context->stash('router', $this);
		if (is_dir($directory)) {
			foreach (glob("$directory/*.php") as $phpFile) {
				include_once($phpFile);

				$className = $this->getClassnameFromFile(basename($phpFile));
				if ($className) {
					$class = new \ReflectionClass($className);
					if ($class->isInstantiable()) {
						$this->addController($class->newInstance($context));
					}
				}
			}
		}
	}

	/*
   * Setting a base route is useful if this HomegrownMVC app is being hosted as
   * a subdirectory of a larger project. Setting the base route will allow your
   * controllers to specify their routes without the base route so that you app
   * can be deployed more portably.
	 */
	function setBaseRoute($route) {
		if (substr($route, -1) != '/') $route .= '/';
		$this->baseRoute = $route;
	}

	/*
   * Give an action to run before a route gets handled. FrontControllers define
   * an entrypoint for this hook.
	 */
	function beforeHandleRoute($callback) {
		array_push($this->events['before'], $callback);
	}

	/*
   * Give an action to run after a route gets handled. FrontControllers define
   * an entrypoint for this hook.
	 */
	function afterHandleRoute($callback) {
		array_push($this->events['after'], $callback);
	}

	/*
	 * Invoke an action from a controller which provides the specified route.
	 * If no route is given, the current URL is used.
	 * Returns true or false depending on whether or not the route was handled
	 */
	function handleRoute($route=null) {
		if ($route == null) $route = $_SERVER['REQUEST_URI'];

		/* See if any controller can handle the URI */
		foreach  ($this->events['before'] as $event) {
			$event();
		}
		$handled = $this->forceFindRoute($route);
		foreach ($this->events['after'] as $event) {
			$event($handled);
		}

		return $handled;
	}

	/*
	 * Try to locate a route both with and without a trailing /
	 * Returns true or false depending on whether or not the route was handled
	 */
	private function forceFindRoute($route) {
		$route = $this->getBaseRoute($route);
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
	 * If a base route is defined, this method returns the part of the route after
	 * the base route.
	 */
	private function getBaseRoute($route) {
		if ($this->baseRoute) {
			if (strpos($route, $this->baseRoute) !== false) {
				$len = strlen($this->baseRoute);
				$route = substr($route, $len);
			}
		}

		return $route;
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
				$controller->invokeRoute($route);
				$foundRoute = true;
				break;
			}
			catch (\Exception $e) {
				if ($this->debug && !($e instanceof \HomegrownMVC\Error\RouteNotDefinedException)) {
					$handler = $this->exceptionHandler;
					$handler($e);
				}
			}
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

	private function getClassnameFromFile($file) {
		if (preg_match('/(.*)\.php/', $file, $matches)) {
			return $matches[1];
		}
	}
}
?>
