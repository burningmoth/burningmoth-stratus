<?php
namespace BurningMoth\Stratus;
/**
 * Append a trailing slash to path.
 * @param string $path
 * @return string
 */
function trailingslash( $path ) {
	return $path . (
		( $len = strlen($path) )
		&& $path[ $len - 1 ] != '/'
		? '/'
		: ''
	);
}

