<?php
namespace BurningMoth\Stratus;
/**
 * True if numeric array.
 * @param mixed	$arr
 * @return bool
 */
function is_array_num( $arr ) {
	if ( !is_array($arr) ) return false;
	for ( reset($arr); is_int(key($arr)); next($arr) );
	return is_null(key($arr));
}