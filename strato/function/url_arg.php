<?php
namespace BurningMoth\Stratus;
/**
 * Return an argument from a url path.
 * @deprecated 2.0
 * @param string|integer $key
 * @param mixed $alt
 * @return mixed
 */
function url_arg( $key, $alt = null ) {
	trigger_error(sprintf('%s() is deprecated. Use path_arg() instead.', __FUNCTION__), \E_USER_DEPRECATED);
	global $tratus;
	return $tratus->path_arg($key, $alt);
}
