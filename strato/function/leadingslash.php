<?php
namespace BurningMoth\Stratus;
/**
 * Prepend a leading slash to path.
 * @param string $path
 * @return string
 */
function leadingslash( $path ) {
	return (
		strlen($path)
		&& $path[0] != '/'
		? '/' . $path
		: $path
	);
}

