<?php
namespace HomegrownMVC\Behaviors;

/*
 * Defines a behavior which allows an update to occur. This is useful for
 * fixture model which may need to call update when data is changed.
 *
 * Author: Bremen Braun
 */
interface Updateable {
	function update();
}
?>
