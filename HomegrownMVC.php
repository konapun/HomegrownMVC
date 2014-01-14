<?php
/*
 * Import everything needed for a HomegrownMVC project so that they're available
 * via `use` statements
 *
 * Author: Bremen Braun
 */
$homegrownBase = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'HomegrownMVC' . DIRECTORY_SEPARATOR;
$controllerBase = $homegrownBase . 'Controller' . DIRECTORY_SEPARATOR;
$modelBase = $homegrownBase . 'Model' . DIRECTORY_SEPARATOR;
$errorBase = $homegrownBase . 'Error' . DIRECTORY_SEPARATOR;

include_once($homegrownBase . 'Context.php');
include_once($homegrownBase . 'Router.php');
include_once($homegrownBase . 'Request' . DIRECTORY_SEPARATOR . 'HTTPRequest.php');
include_once($controllerBase . 'BaseController.php');
include_once($controllerBase . 'WildcardController.php');
include_once($modelBase . 'PluralModel.php');
include_once($modelBase . 'SingularModel.php');
include_once($errorBase . 'BuildException.php');
include_once($errorBase . 'MalformedUrlException.php');
include_once($errorBase . 'ResultNotFoundException.php');
include_once($errorBase . 'RouteNotDefinedException.php');
?>
