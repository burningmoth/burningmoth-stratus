<?php
namespace BurningMoth\Stratus;
/**
 * Return post arg or alternative.
 * @param string $key
 * @param mixed $alt
 * @return mixed
 */
function post_arg( $key, $alt = null ) {
	global $tratus;
	return $tratus->array_value($_POST, $key, $alt);
}