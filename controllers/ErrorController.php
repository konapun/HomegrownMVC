<?php 
include_once('controllers/BaseController.php');

/*
 * Connect to error views which the router will manually redirect to
 */
class ErrorController extends BaseController {
	protected function setupRoutes() {
		$this->controllerBase('/');
		
		return array(
			'404' => function($context) { 
				echo "404: Page not found";
			}
		);
	}
}
?>