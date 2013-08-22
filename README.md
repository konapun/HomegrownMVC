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
$context = new Context($httpRequest, $dbh, $viewEngine);
$router = new Router($context);

$router->redirect('/', '/home'); // example.com and example.com/ will redirect to example.com/home (without altering the URL)
$router->handleRoute(); // If no route is given, the current URL is used
```

## Defining controllers
A HomegrownMVC controller implements the abstract BaseController class.
A controller only has to define actions for routes it accepts. Arguments
to the route are provided through the context
```php
class SearchController extends BaseController {
	protected function setupRoutes() {
		$this->controllerBase('/search');
		
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