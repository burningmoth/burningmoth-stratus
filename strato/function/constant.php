<?php
namespace BurningMoth\Stratus;
/**
 * Get and/or set a constant value (without throwing an error for undefined constants!).
 * @param string $const
 * @param mixed $alt
 * @param bool $define
 *	- define undefined constant from alt value?
 * @return mixed
 */
function constant( $const, $alt = null, $define = false ) {

	// constant defined ? return value now ...
	if ( defined($const) ) return \constant($const);

	// define constant now from alt ? ...
	if (
		$define
		&& (
			is_scalar($alt)
			|| is_array($alt)
		)
	) define($const, $alt);

	// return alternate value ...
	return $alt;
}

