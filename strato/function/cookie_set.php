<?php
namespace BurningMoth\Stratus;
/**
 * Set (or delete) a cookie.
 * @param string $key
 * @param mixed $value
 *	- if null or false then the cookie is set to delete
 * @param array $opts
 *	- expire (unix timestamp|DateTime|int seconds), 0
 *	- domain (string), $_SERVER['HTTP_HOST']
 *	- path (string), "/"
 *	- secure (bool), $_SERVER['HTTPS']
 *	- httponly (bool), false
 * 	- global (bool|int), false, modifies domain into global
 *		- ex. true w/"www.sub.domain.com" = ".domain.com"
 *		- ex. 3 w/"www.sub.domain.com" = ".sub.domain.com"
 *	- raw (bool), whether to set with setrawcookie() or setcookie()
 *	@return bool
 */
function cookie_set( $key, $value = null, array $params = [] ) {

	// headers sent ? exit ... no way this can work ...
	if ( headers_sent() ) {
		trigger_error(__FUNCTION__.'(): failed! Headers already sent!', \E_USER_WARNING);
		return false;
	}

	global $tratus;

	// parse params against defaults ...
	$params = array_replace([

		// standard setcookie() parameters ...
		'expire'	=> 0,
		'domain'	=> $tratus->server_arg('HTTP_HOST', ''),
		'path'		=> '/',
		'secure'	=> $tratus->server_arg('HTTPS', false),
		'httponly'	=> false,

		// set global domain w/preceding period ?
		'global'	=> false,

		// set raw cookie value (not url encoded) ...
		'raw'		=> false,

	], $params);

	// extract into variables ...
	extract($params);

	// deleting cookie ? set expire way in the past ...
	if ( is_null($value) || $value === false ) {
		$expire = time() - 31536000;
		unset($_COOKIE[ $key ]);
	}

	else {

		// expire is datetime object ? output timestamp ...
		if ( $expire instanceof \DateTime ) $expire = $expire->getTimestamp();

		// ensure expire is an integer ...
		elseif ( ! is_integer($expire) ) $expire = 0;

		// expire is less than time ? assume we're adding to current seconds ...
		elseif (
			$expire > 0
			&& $expire < time()
		) $expire += time();

		// update cookie variable ...
		$_COOKIE[ $key ] = $value;

	}

	// global domain ? ...
	if ( $global ) {

		// not integer ? convert to default integer value ...
		if ( !is_integer($global) ) $global = -2;

		// positive integer ? convert to negative ...
		elseif ( $global > 0 ) $global *= -1;

		// modify domain ...
		$domain = $tratus->cookie_domain($domain, $global);

	}

	// save cookie ...
	return call_user_func(
		( $raw ? 'setrawcookie' : 'setcookie' ),
		$key,
		(string) $value,
		$expire,
		$path,
		$domain,
		$secure,
		$httponly
	);

}
