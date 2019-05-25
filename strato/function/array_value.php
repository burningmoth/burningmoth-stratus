<?php
namespace BurningMoth\Stratus;
/**
 * Return a value from an array or object if exists or alternate value.
 * @param array|object $arr
 * @param string $key
 * @param mixed $alt (optional, null)
 * @return mixed
 */
function array_value( $arr, $key, $alt = null ) {

	if (
		is_array($arr)
		&& array_key_exists($key, $arr)
	) return $arr[ $key ];

	if (
		is_object($arr)
		&& property_exists($arr, $key)
	) return $arr->$key;

	return $alt;
}
