<?php
namespace HomegrownMVC\Behaviors;

/*
 * Allow a transactional commit operation. This is useful for Singular models
 * who are allowed to update their data and store the resulting changes.
 *
 * Author: Bremen Braun
 */
interface Commitable {
	function commit();
}
?>
