<?php
namespace BurningMoth\Stratus;
/**
 * Return a header value or alternative.
 * @param string $key
 * @param mixed $alt
 * @return mixed
 */
function head_arg( $key = null, $alt = null ) {

	global $tratus;

	static $headers, $func_process_key;
	if ( !isset($headers) ) {

		$func_process_key = function( $key ){
			return str_replace('-', '_', strtolower(strval($key)));
		};

		// get headers ...
		$headers = apache_request_headers();

		// process into consistent variable-safe header keys ...
		$headers = array_combine(
			array_map(
				$func_process_key,
				array_keys($headers)
			),
			array_values($headers)
		);

	}

	return (
		empty($key)
		? $headers
		: $tratus->array_value($headers, $func_process_key($key), $alt)
	);
}
