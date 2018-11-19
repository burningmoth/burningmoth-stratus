<?php
namespace BurningMoth\Stratus;
/**
 * Return an argument from a url path.
 * @param string|integer $key
 * @param mixed $alt
 * @return mixed
 */
function url_arg( $key, $alt = null ) {

	global $tratus;

	static $args;
	if ( !isset($args) ) {

		if ( $args = $tratus->array_value($_SERVER, 'REQUEST_URI', array()) ) {

			$args = \parse_url($args, \PHP_URL_PATH);
			$args = \explode('/', $args);
			$args = \array_filter($args, '\strlen');
			$args = \array_map('\urldecode', $args);

		}

	}

	return $tratus->array_arg($args, $key, $alt);

}