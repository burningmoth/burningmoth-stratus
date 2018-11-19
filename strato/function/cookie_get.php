<?php
namespace BurningMoth\Stratus;
/**
 * Retrieve a cookie value.
 * @param string $key
 * @param mixed $alt
 * @return mixed
 */
function cookie_get( $key, $alt = null ) {
	global $tratus;
	return $tratus->array_value($_COOKIE, $key, $alt);
}

