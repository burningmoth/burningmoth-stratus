<?php
namespace BurningMoth\Stratus;
/**
 * Formats array of headers from an associative [ $name => $value ] array.
 * Like http_build_query() but for headers.
 *
 * @param array $headers
 * @param bool $return_string
 * @return array|string
 *	- returns a numeric array of headers by default, CRLF delimited string if $return_string is true
 */
function http_build_headers( array $headers, $return_string = false ) {

	global $tratus;

	// format an associative array ...
	if (
		$tratus->is_array_assoc($headers)
	) foreach (
		$headers as $name => &$value
	) $value = sprintf(
		'%s: %s',
		$tratus->http_header_name($name),
		strval($value)
	);

	return (
		$return_string
		? implode("\r\n", $headers)
		: array_values($headers)
	);
}
