<?php
namespace BurningMoth\Stratus;
/**
 * Generate a random string of characters.
 * @param integer $length (default 24)
 * @param string $chars (default alphanumeric, upper and lower case)
 */
function random_str( $length = null, $chars = null ) {
	$rstr = new RandomString($length);
	if ( is_string($chars) ) $rstr->chars($chars);
	return (string) $rstr;
}
