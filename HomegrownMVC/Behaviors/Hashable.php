<?php
namespace HomegrownMVC\Behaviors;

/*
 * Defines a behavior by which an object can be converted to a hashed. This is
 * useful for custom models which are pseudo-SingularModels
 *
 * Author: Bremen Braun
 */
interface Hashable {
	function hashify();
}
?>
