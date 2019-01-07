<?php
namespace BurningMoth\Stratus;
/**
 * Validate an IP address.
 * @param string $ip
 * @param int $flags (optional)
 *	- bitmask of filter_var() flags: FILTER_FLAG_IPV4, FILTER_FLAG_IPV6, FILTER_FLAG_NO_PRIV_RANGE, FILTER_FLAG_NO_RES_RANGE (default)
 * @return bool
 */
function valid_ip( $ip, $flags = null ){
	return \filter_var($ip, \FILTER_VALIDATE_IP, (
		empty($flags)
		? \FILTER_FLAG_NO_RES_RANGE
		: $flags
	));
}

