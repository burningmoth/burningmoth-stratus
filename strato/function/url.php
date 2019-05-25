<?php
namespace BurningMoth\Stratus;
/**
 * Fetch extension url.
 *
 * @param string $relpath
 * @return mixed
 */
function url( $relpath = '' ) {

	global $tratus;

	// base url not created yet ? create now ...
	if ( empty($tratus->___url) ) {

		$tratus->___url
		= 'http'
		. ( $tratus->array_value($_SERVER, 'HTTPS') ? 's' : '' )
		. '://'
		. $tratus->array_value($_SERVER, 'HTTP_HOST', '127.0.0.1');

	}

	// no relative extension path passed ? return project url ...
	if ( empty($relpath) ) $url = $tratus->___url;

	// stratus path ? patch in ...
	elseif (
		( $root = $tratus->array_value($_SERVER, 'DOCUMENT_ROOT', false) )
		&& ( $abspath = $tratus->path($relpath) )
	) $url = str_replace(
		$tratus->fwdslashes($root),
		$tratus->___url,
		$tratus->fwdslashes($abspath)
	);

	// simply tack the path onto the base url ...
	else $url = $tratus->___url . $tratus->leadingslash( $tratus->fwdslashes( $relpath ) );

	return $url;
}

