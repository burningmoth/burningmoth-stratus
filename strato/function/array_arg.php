<?php
namespace BurningMoth\Stratus;
/**
 * Get a page url path argument or arguments.
 * @param array $args
 * @param integer|string|null $key
 * @param mixed $alt
 * @return mixed
 */
function array_arg( $args, $key, $alt = null ) {

	// not array ? return alternate ...
	if ( !is_array($args) ) return $alt;

	// run through array_values() to reset numeric indexes ...
	$args = array_values($args);

	// string ? look for key/value pair ...
	if (
		is_string($key)
		&& ( $index = array_search($key, $args) ) !== false
		&& ++$index < count($args)
	) return $args[ $index ];

	// integer ? return arg element at index ...
	elseif (
		is_integer($key)
		&& $key >= 0
		&& $key < count($args)
	) return $args[ $key ];

	// fail ? return alternate value ...
	return $alt;

}