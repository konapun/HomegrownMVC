# Homegrown(MV)C
A very small MVC framework minus the M and the V

### Rationale
This is a micro MVC framework (well, barely even a framework) born out of necessity as I tried to make a legacy project
I inherited more manageable. It only defines a router and base controller since the project already had models and views.
As a result, it should work with any templating system and DBAL.

## Contexts
A context is just an object that bundles together the HTTP request, view engine, and database handle to be passed
to a controller. Since the context is an object you instantiate, you can use any class for any of these three parameters.

## Using routes
HomegrownMVC uses a router to locate controllers.
```php
/*
 * This could be a snippet from your index.php file
 */

$context = new Context($httpRequest, $dbh, $viewEngine);
$router = new Router();

// Redirect example.com and example.com/ to example.com/home (without altering the URL)
$router->redirect('/', '/home');

// Associate routes with controllers. Controllers are custom custrollers you define which extend BaseController
$router->addController(new IndexController($context));
$router->addController(new SearchController($context));
$router->addController(new ErrorController($context));

// Handle the route. If no route is given, the current URL is used
if (!$router->handleRoute()) {
	$router->handleRoute('404'); // manually reroute to 404 defined in the error controller
}
```


Alternatively, the router can automatically locate and add controllers for you, given that
there is a single controller class per file and the controller class name is the same as
the file name, minus the .php extension.

```php
// Let the router locate, instantiate, and add the controllers for you
$router->autoloadControllers($context, 'controllers'); // the 2nd arg is the directory containing the controllers (default: 'controllers')

// Handle the route. If no route is given, the current URL is used
if (!$router->handleRoute()) {
	$router->handleRoute('404'); // manually reroute to 404 defined in the error controller
}
```

## Defining controllers
A HomegrownMVC controller extends the abstract BaseController class.
A controller only has to define actions for routes it accepts. Arguments
to the route are provided through the context
```php
class SearchController extends BaseController {
	protected function setupRoutes() {
		$this->controllerBase('/search/');
		
		return array(
			'person' => function($context) { //maps to www.example.com/search/person
				$request = $context->getRequest();
				$view = $context->getViewEngine();
				
				// This will depend on the object you're using to do HTTP requests
				$name = $request->getParam('name');
				
				// This will depend on your view engine. You may use any, as this tiny framework doesn't provide one
				$view->replaceVar('name', "Searching for $name");
				$view->render();
			},
		);
	}
```

A controller may conditionally reroute one route to another route defined by the same controller
```php
class RerouteController extends BaseController {
	protected function setupRoutes() {
		$that = $this;
		
		return array(
			'route1' => function($context) {
				echo "Doing route1";
			},
			'test_reroute' => function($context) use ($that) {
				$that->invokeRoute('route1');
			}
		);
	}
}
```

You can also reroute to a different controller
```php
class RerouteController extends BaseController {
	protected function setupRoutes() {
		return array(
			'reroute' => function($context) {
				$searchController = new SearchController($context);
				$searchController->invokeRoute('/search/person');
			}
		);
	}
}
```
