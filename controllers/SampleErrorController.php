<?php 
include_once('controllers/BaseController.php');

/*
 * Sample error controller with routes defined by error codes
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