<?php
namespace BurningMoth\Stratus;
/**
 * Formats a cookie domain w/preceding dot.
 *
 * @param string $domain (default current host)
 *	- if omitted or empty, current host name is used
 * @param int $levels (default 2)
 *	- number of levels from end of domain name to include ( ex. 'www.domain.com', 2 levels = 'domain.com' )
 *	- if empty (0,false,null), will return domain as-is
 *	- positive or negative ints can be passed, positive ints will be converted to negative
 * @return string
 */
function cookie_domain( $domain = null, $levels = -2 ) {

	global $tratus;

	// no domain ? get from headers ...
	if ( empty($domain) ) $domain = $tratus->server_arg('HTTP_HOST', '');

	// no levels ? return domain as is ...
	if ( empty($levels) ) return $domain;

	// levels not negative ? make negative ...
	if ( $levels > 0 ) $levels *= -1;

	// return cookie domain ...
	return '.' . implode('.', array_slice(explode('.', $domain), $levels));
}
