<?php 
include_once('controllers/BaseController.php');

/*
 * Example controller
 */
class IndexController extends BaseController {
	protected function setupRoutes() {
		$this->controllerBase('/');
		
		return array(
			'home' => function($context) {
				$view = $context->getViewEngine();
				
				// This will depend on your view engine. You may use any, as this tiny framework doesn't provide one
				$view->replaceVar('test_variable', 'my content!');
				$view->render();
			},
			'about' => function($context) {
				$view = $context->getViewEngine();
				
				// This will depend on your view engine
				$view->includeTemplate('content', 'templates/about.html');
				$view->render();
			},
		);
	}
}
?>