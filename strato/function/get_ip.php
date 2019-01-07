<?php
namespace BurningMoth\Stratus;
/**
 * Get a ip address.
 * @param bool $remote
 *	- true (default), gets remote ip of requesting server
 *	- false, gets local ip of responding server
 * @return string|bool
 */
function get_ip( $remote = true ) {

	global $tratus;

	if ( ! $remote ) $ip = $tratus->array_value($_SERVER, 'SERVER_ADDR', false);

	// this can be a comma-delimited list of proxy addresses, get the last valid ip ...
	elseif( $ip = $tratus->array_value($_SERVER, 'HTTP_X_FORWARDED_FOR', false) ) {
		$ips = explode(',', $ip);
		$ips = array_map('trim', $ips);
		$ips = array_filter($ips, [ $tratus, 'valid_ip' ]);
		$ip = end($ips);
	}

	elseif( $ip = $tratus->array_value($_SERVER, 'HTTP_CLIENT_IP', false) );

	else $ip = $tratus->array_value($_SERVER, 'REMOTE_ADDR', false);

	return $ip;
}

