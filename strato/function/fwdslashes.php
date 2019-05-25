<?php
namespace BurningMoth\Stratus;
/**
 * Normalize path slashes forward if not already.
 * @param string $path
 * @return string
 */
function fwdslashes( $path ) {
	return (
		\DIRECTORY_SEPARATOR === '\\'
		? str_replace(\DIRECTORY_SEPARATOR, '/', $path)
		: $path
	);
}

