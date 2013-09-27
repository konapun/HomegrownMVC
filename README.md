# Homegrown(MV)C
A very small MVC framework minus the M and the V

### Rationale
This is a router/controller framework (well, barely even a framework) born out of necessity as I tried to make a legacy project
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

## Controllers
A HomegrownMVC controller extends the abstract BaseController class.
A controller only has to define actions for routes it accepts. Arguments
to the route are provided through the context

### Defining controllers
Currently, two types of controllers are defined:
  * **BaseController**: Routes are literal paths 
```php
/*
 * Sample controller which is a regular BaseController
 */
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

  * **WildcardController**: Routes can define wildcards to match
```php
/*
 * Sample WildcardController demonstrating the use of wildcards
 * in the routes
 */
class UserController extends WildcardController {
	protected function setupWildcardRoutes() {
		$this->setWildcardCharacter(':'); // this is the default character, but you can change it to any single character
		$this->controllerBase('/user/');
		
		return array(
			':uid/profile' => function($context, $params) {
				echo "Showing profile for user with ID " . $params['uid'];
			},
			':uid/pictures/:pid' => function($context, $params) {
				echo "Showing picture with ID " . $params['pid'] . " for user with ID " . $params['uid'];
			}
		);
	}
}
```

### Controller rerouting
A controller may conditionally reroute from one route to another
```php
class RerouteController extends BaseController {
	protected function setupRoutes() {
		$that = $this;
		
		return array(
			'route1' => function($context) {
				echo "Doing route1";
			},
			'reroute_same_controller' => function($context) use ($that) {
				$that->invokeRoute('route1');
			},
			'reroute_different_controller' => function($context) {
				$searchController = new SearchController($context);
				$searchController->invokeRoute('/search/person');
			}
		);
	}
}
```

### Pre-route hooks
You may specify callbacks to run before a route is invoked using `eachRoute`. This is useful for when, for example, you have a navigation controller
and want to set an active class depending on which nav route is invoked:
```php
class NavigationController extends BaseController {
	$this->eachRoute(function($context) {
		$view = $context->getViewEngine();
		$route = substr($context->getRequest()->routeName(), 1); // just remove the leading slash; since Homegrown(MV)C doesn't provide a Request class, your exact way of doing this will vary
		
		$view->replaceVar("$route-active", 'active'); // since Homegrown(MV)C doesn't provide a view engine, your exact way of doing this will vary
	});
}
```
