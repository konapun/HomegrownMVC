<?php
namespace HomegrownMVC\Controller;

use HomegrownMVC\Controller\WildcardController as WildcardController;

/*
 * The route controller is a special controller that can be optionally used by
 * the router for more traditional router -> controller bindings, where the
 * router uses the route controller to locate end controllers and invoke methods
 * based on the route.
 * 	Example:
 *
 * 		RouteController has route /people/create/:id/:name
 * 		- locate PeopleController
 * 		- instantiate it
 * 		- invoke $peopleController->create($id, $name)
 */
abstract class RouteController extends WildcardController {
  private $resource;
  private $initial;
  private $maxDepth; // The maximum number of params that can be passed via the route

  function __construct($context) {
    parent::__construct($context);
    $this->resource = "";
    $this->initial = 'index';
    $this->maxDepth = 8;
    $this->configure();
  }

  /*
   * Configure the controller with `setResource`, `setInitialRoute`,
   * `setMaxArgDepth`, etc.
   */
  protected function configure() {}

  /*
   * Setting the resource allows handing of nested routes while still invoking
   * the correct controller. For instance, if you have a route
   *   /nested/people/1
   * You can set the resource to 'nested' to still allow the People controller
   * to be invoked
   */
  final protected function setResource($resource) {
    $this->resource = "$resource/";
  }

  /*
   * Set the method name automatically invoked when this controller's base route
   * is matched (the default is 'index')
   */
  final protected function setInitialRoute($route) {
    $this->initial = $route;
  }

  /*
   * Set the maximum number of arguments that can be passed to methods in this
   * controller via URL segments (default 8). Note that traditional arguments
   * passed through the request are unaffected.
   */
  final protected function setMaxArgDepth($depth) {
    $this->maxDepth = $depth;
  }

  /*
   * Return the maximum number of arguments that can be passed via URL segments
   * to methods in this controller.
   */
  final protected function getMaxArgDepth() {
    return $this->maxDepth;
  }

  /*
   * Dynamically build routes based on methods defined by the subclass
   */
  final protected function setupWildcardRoutes() {
    $routes = array();
    $base = $this->resource . strtolower(get_class($this));

    $wcChar = $this->getWildcardCharacter();
    foreach ($this->getRouteMethods() as $method) {
      $action = $method == 'index' ? $base : "$base/$method";

      $routes[$action] = function($context) use ($method) { // set route action with no params
        $this->$method($context, array());
      };

      $params = array();
      for ($i = 0; $i < $this->getMaxArgDepth(); $i++) { // set route ation with params
        array_push($params, $wcChar . $i);

        $routes[$action . '/' . join('/', $params)] = function($context, $params) use ($method) {
          $this->$method($context, $params);
        };
      }
    }

    return $routes;
  }

  /*
   * Get all the methods defined by the subclass, excluding those from this
   * abstract class. Methods from the subclass will be used to match routes.
   */
  private function getRouteMethods() {
    $baseMethods = get_class_methods(__CLASS__); // only methods defined in this abstract class
    $subMethods = get_class_methods($this); // all methods available in the subclass

    return array_diff($subMethods, $baseMethods);
  }
}
?>
