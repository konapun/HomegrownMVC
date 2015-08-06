<?php
/*
 * Import everything needed for a HomegrownMVC project so they're available via
 * `use` statements
 *
 * Author: Bremen Braun
 */

$homegrownBase = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'HomegrownMVC' . DIRECTORY_SEPARATOR;
$controllerBase = $homegrownBase . 'Controller' . DIRECTORY_SEPARATOR;
$modelBase = $homegrownBase . 'Model' . DIRECTORY_SEPARATOR;
$importerBase = $modelBase . DIRECTORY_SEPARATOR . 'DataImporter' . DIRECTORY_SEPARATOR;
$behaviorsBase = $homegrownBase . 'Behaviors' . DIRECTORY_SEPARATOR;
$errorBase = $homegrownBase . 'Error' . DIRECTORY_SEPARATOR;

include_once($homegrownBase . 'Context.php');
include_once($homegrownBase . 'Router.php');
include_once($homegrownBase . 'Request' . DIRECTORY_SEPARATOR . 'HTTPRequest.php');
include_once($behaviorsBase . 'Hashable.php');
include_once($controllerBase . 'IController.php');
include_once($controllerBase . 'BaseController.php');
include_once($controllerBase . 'FrontController.php');
include_once($controllerBase . 'WildcardController.php');
include_once($importerBase . 'IDataImporter.php');
include_once($importerBase . 'CSVDataImporter.php');
include_once($importerBase . 'ExtendedCSVDataImporter.php');
include_once($modelBase . 'PluralModel.php');
include_once($modelBase . 'SingularModel.php');
include_once($modelBase . 'FixtureModel.php');
include_once($errorBase . 'BuildException.php');
include_once($errorBase . 'MalformedUrlException.php');
include_once($errorBase . 'PDOException.php');
include_once($errorBase . 'ResultNotFoundException.php');
include_once($errorBase . 'RouteNotDefinedException.php');
include_once($errorBase . 'CSVFormatException.php');
include_once($errorBase . 'IOException.php');
?>
