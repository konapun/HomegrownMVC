<?php
/*
 * Gather disparate objects to be passed as a single object
 */ 
class Context {
	private $request;
	private $dbHandle;
	private $viewEngine;
	private $route;
	
	function __construct($request, $databaseHandle, $viewEngine) {
		$this->request = $request;
		$this->dbHandle = $databaseHandle;
		$this->viewEngine = $viewEngine;
	}
	
	function getRequest() {
		return $this->request;
	}
	
	function getDatabaseHandle() {
		return $this->dbHandle;
	}
	
	function getViewEngine() {
		return $this->viewEngine;
	}
	
	function getRoute() {
	
	}
}
?>