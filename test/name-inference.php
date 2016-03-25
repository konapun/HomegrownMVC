<?php
include_once('HomegrownMVC.php');

use HomegrownMVC\Util\NameInferer as Inferer;

$inferer = new Inferer(false);
$tests = array(
  array(
    'Tests',
    'Test'
  ),
  array(
    'Aliases',
    'Alias'
  )
);

$pass = 0;
foreach ($tests as $test) {
  list($plural, $control) = $test;

  $experiment = "";
  try {
    $experiment = $inferer->inferSingularClassName($plural);
  }
  catch (Exception $e) {}
  if ($experiment !== $control) {
    echo "Error inferring name from $plural: expected $control but got $experiment\n";
    $pass--;
  }
  else {
    echo "PASS: $plural -> $experiment\n";
  }
  $pass++;
}
echo "Passed $pass out of " . count($tests) . " tests\n";
?>
