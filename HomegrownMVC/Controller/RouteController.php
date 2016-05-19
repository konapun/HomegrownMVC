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
  private $MAX_DEPTH = 12; // The maximum number of params that can be passed via the route

  function __construct($context) {
    parent::__construct($context);
    $this->resource = "";
    $this->configure();
  }

  /*
   * Configure the controller with `setResource`, etc
   */
  protected function configure() {}

  /*
   * Setting the resource allows handing of nested routes while still invoking
   * the correct controller. For instance, if you have a route
   *   /nested/people/1
   * You can set the resource to 'nested' to still allow the People controller
   * to be invoked
   */
  final function setResource($resource) {
    $this->resource = "$resource/";
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
      for ($i = 1; $i <= $this->MAX_DEPTH; $i++) { // set route ation with params
        array_push($params, $wcChar . '$' . $i);

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
