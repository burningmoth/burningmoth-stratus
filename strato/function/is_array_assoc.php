<?php
namespace BurningMoth\Stratus;
/**
 * True if associative array.
 * @param mixed	$arr
 * @return bool
 */
function is_array_assoc( $arr ) {
	global $tratus;
	if ( ! is_array($arr) ) return false;
	return ! $tratus->is_array_num($arr);
}
