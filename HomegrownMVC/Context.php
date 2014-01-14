<?php
namespace HomegrownMVC;

/*
 * Gather disparate objects to be passed as a single object
 */ 
class Context {
	private $request;
	private $dbHandle;
	private $viewEngine;
	private $stash;
	
	function __construct($request, $databaseHandle, $viewEngine) {
		$this->request = $request;
		$this->dbHandle = $databaseHandle;
		$this->viewEngine = $viewEngine;
		$this->stash = array();
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
	
	function stash($key, $val=null) {
		if ($val != null) {
			$this->stash[$key] = $val;
		}
		
		if (array_key_exists($key, $this->stash)) {
			return $this->stash[$key];
		}
	}
}
?>
