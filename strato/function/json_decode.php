<?php
namespace BurningMoth\Stratus;
/**
 * JSON decode + error reporting.
 * @param string $json
 * @param array $args
 * @return mixed
 */
function json_decode( $json, $args = array() ) {

	// parameters passed as though for \json_decode() ? process into expected arguments ...
	if ( ! is_array($args) ) {
		list($json, $assoc, $depth, $options) = array_pad(func_get_args(), 4, null);
		$args = array_filter(compact('assoc', 'depth', 'options'));
	}

	// merge args w/defaults and extract vars ...
	extract(array_replace([
		'assoc' => false,
		'depth' => 512,
		'options' => 0,
		'default' => false
	], $args));

	try {

		// json not string ? throw ...
		if ( ! is_string($json) ) throw new \Exception(sprintf('$json parameter should be string! %s passed!', gettype($json)));

		// assoc not bool ? throw ...
		elseif ( ! is_bool($assoc) ) throw new \Exception(sprintf('$assoc parameter should be boolean! %s passed!', gettype($assoc)));

		// depth not integer ? throw ...
		elseif ( ! is_int($depth) ) throw new \Exception(sprintf('$depth parameter should be integer! %s passed!', gettype($depth)));

		// depth not greater than zero ? throw ...
		elseif ( $depth < 1 ) throw new \Exception('$depth parameter must be greater than zero!');

		// options not integer ? throw ...
		elseif ( ! is_int($options) ) throw new \Exception(sprintf('$options parameter should be bitmask! %s passed!', gettype($options)));

		// decode json ...
		$value = \json_decode($json, $assoc, $depth, $options);

		// error ? throw ...
		if ( json_last_error() !== \JSON_ERROR_NONE ) throw new \Exception(json_last_error_msg());

	} catch ( \Exception $e ) {

		// throw error ...
		trigger_error(__FUNCTION__ . '(): ' . $e->getMessage(), \E_USER_WARNING);

		// set return value to default ...
		$value = $default;

	}

	// return json or default value ...
	return $value;
}
