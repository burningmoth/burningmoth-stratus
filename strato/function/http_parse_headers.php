<?php
namespace BurningMoth\Stratus;
/**
 * Parse a string or array of headers into associative array.
 * @param array|string $headers
 * @return array
 */
function http_parse_headers( $headers ) {

	global $tratus;

	// string ? break into lines ...
	if ( is_string($headers) ) $headers = array_filter(explode("\r\n", $headers));

	// already numeric array ? ...
	elseif ( $tratus->is_array_num($headers) );

	// cast whatever this is as array and give it back ...
	else return (array) $headers;

	// separate name and values ...
	$headers = array_filter(array_map(function( $header ){
		return (
			preg_match('/^([\w-]+): (.*)$/', $header, $matches)
			? [ strtolower(next($matches)), trim(next($matches)) ]
			: false
		);
	}, $headers));

	// return associative array of [ name => value ] ...
	return \array_column($headers, 1, 0);
}
