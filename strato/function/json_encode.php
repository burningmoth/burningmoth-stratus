<?php
namespace BurningMoth\Stratus;
/**
 * JSON encode + error reporting.
 * @param mixed $value
 * @param array $args
 * @return string|mixed
 */
function json_encode( $value, $args = array() ) {

	// parameters passed as though for \json_encode() ? process into expected arguments ...
	if ( ! is_array($args) ) {
		list($value, $options, $depth) = array_pad(func_get_args(), 3, null);
		$args = array_filter(compact('options', 'depth'));
	}

	// merge args w/defaults and extract vars ...
	extract(array_replace([
		'depth' => 512,
		'options' => 0,
		'default' => ''
	], $args));

	try {

		// depth not integer ? throw ...
		if ( ! is_int($depth) ) throw new \Exception(sprintf('$depth parameter should be integer! %s passed!', gettype($depth)));

		// depth not greater than zero ? throw ...
		elseif ( $depth < 1 ) throw new \Exception('$depth parameter must be greater than zero!');

		// options not integer ? throw ...
		elseif ( ! is_int($options) ) throw new \Exception(sprintf('$options parameter should be bitmask! %s passed!', gettype($options)));

		$json = \json_encode($value, $options, $depth);

		// no error ? return value as-is !
		if ( json_last_error() !== \JSON_ERROR_NONE ) throw new \Exception(json_last_error_msg());

	} catch ( \Exception $e ) {

		// trigger error message ...
		trigger_error(__FUNCTION__ . '(): ' . $e->getMessage(), \E_USER_WARNING);

		// return default value ...
		$json = $default;

	}

	return $json;
}

