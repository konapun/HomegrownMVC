<?php
namespace HomegrownMVC\Controller;

use HomegrownMVC\Error\RouteNotDefinedException as RouteNotDefinedException;

/*
 * A front controller is a special type of controller that's unconditionally
 * called prior to handling a route from the router. Node that this won't fire
 * when invoking a route from the controller, and in most cases that's not what
 * you want anyway.
 *
 * Unlike a normal controller, a front controller doesn't handle any routes.
 * This is mostly a design choice to keep controllers from being too hard to
 * track. A front controller is mainly only used for handling logic for a site-
 * wide page wrapper.
 */
abstract class FrontController implements IController {
  private $context;

  function __construct($context) {
		$this->context = $context;
    $this->setupFrontController();
	}

  abstract protected function setupFrontController();

  final protected function beforeFrontRoutes($callback) {
    $context = $this->context;
    $this->context->stash('router')->beforeHandleRoute(function() use (&$context, $callback) {
      $callback($context);
    });
  }

  final protected function afterFrontRoutes($callback) {
    $context = $this->context;
    $this->context->stash('router')->afterHandleRoute(function($found) use (&$context, $callback) {
      $context->stash('handledRoute', $found);
      $callback($context);
    });
  }

  final function invokeRoute($url) {
    throw new RouteNotDefinedException("FrontController does not specify route actions"); // User can throw a 404 or something
  }
}
?>
