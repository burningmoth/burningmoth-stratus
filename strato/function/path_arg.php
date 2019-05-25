<?php
namespace BurningMoth\Stratus;
/**
 * Get a page url path argument or arguments.
 * @param integer|string|null $key
 * @param mixed $alt
 * @return mixed
 */
function path_arg( $key = null, $alt = null ) {

	global $tratus;

	// run through array_values() to reset numeric indexes ...
	static $args; if ( !isset($args) ) $args = array_values(array_filter(explode('/', parse_url($tratus->array_value($_SERVER, 'REQUEST_URI', ''), \PHP_URL_PATH))));

	// null ? return args array : value or alt ...
	return (
		is_null($key)
		? $args
		$tratus->array_arg($args, $key, $alt)
	);
}
