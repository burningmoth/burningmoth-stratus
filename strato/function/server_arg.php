<?php
namespace BurningMoth\Stratus;
/**
 * Fetch value from $_SERVER array.
 *
 * @param string $key
 * @param mixed $alt
 * @return mixed
 */
function server_arg( $key, $alt = null ) {
	global $tratus;
	return $tratus->array_value($_SERVER, $key, $alt);
}
