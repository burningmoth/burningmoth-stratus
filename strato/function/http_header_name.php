<?php
namespace BurningMoth\Stratus;
/**
 * Formats a qualified Http-Header-Name.
 *
 * @param string $name
 * @return string
 */
function http_header_name( $name ) {
	return str_replace(' ', '-', ucwords(trim(preg_replace('/[^a-zA-Z0-9]+/', ' ', strval($name)))));
}
