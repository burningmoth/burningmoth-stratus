<?php
namespace BurningMoth\Stratus;
/**
 * Return a value from an array if exists or alternate value.
 * @param array $arr
 * @param string $key
 * @param mixed $alt (optional, null)
 * @return mixed
 */
function array_value( $arr, $key, $alt = null ) {
	if ( !is_array($arr) ) return $alt;
	if ( array_key_exists($key, $arr) ) return $arr[ $key ];
	return $alt;
}
