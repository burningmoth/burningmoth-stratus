<?php
namespace BurningMoth\Stratus;
/**
 * Return get arg or alternative.
 * @param string $key
 * @param mixed $alt
 * @return mixed
 */
function get_arg( $key, $alt = null ) {
	global $tratus;
	return $tratus->array_value($_GET, $key, $alt);
}