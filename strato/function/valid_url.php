<?php
namespace BurningMoth\Stratus;
/**
 * Validate a URL.
 * @param string $url
 * @return bool|string
 */
function valid_url( $url ){
	return \filter_var($url, \FILTER_VALIDATE_URL, \FILTER_FLAG_SCHEME_REQUIRED & \FILTER_FLAG_HOST_REQUIRED);
}
