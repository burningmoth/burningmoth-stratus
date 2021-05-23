<?php
namespace BurningMoth\Stratus;
/**
 * Set (or delete) a cookie.
 *
 * @param string $key
 * @param mixed $value
 *	- if null or false then the cookie is set to delete
 * @param array $opts
 *	- expires (unix timestamp|DateTime|int seconds), 0
 *	- domain (string), $_SERVER['HTTP_HOST']
 *	- path (string), "/"
 *	- secure (bool), $_SERVER['HTTPS']
 *	- httponly (bool), false
 *	- samesite (string), "Lax" (one of "Lax", "Strict" or "None"; "None" requires secure=true)
 * 	- global (bool|int), false, modifies domain into global
 *		- if resolves true, passed as $levels param to cookie_domain()
 *		- true defaults to 2 levels, ex. true w/"www.sub.domain.com" = ".domain.com"
 *		- ex. 3 w/"www.sub.domain.com" = ".sub.domain.com"
 *		- appears to default to TRUE in PHP 7.3+
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
		'expires'	=> 0,
		'domain'	=> $tratus->array_value($_SERVER, 'HTTP_HOST', ''),
		'path'		=> '/',
		'secure'	=> $tratus->array_value($_SERVER, 'HTTPS', false),
		'httponly'	=> false,
		'samesite'	=> 'Lax',

		// set global domain w/preceding period ?
		'global'	=> false,

		// set raw cookie value (not url encoded) ...
		'raw'		=> false,

	], $params);

	// extract into variables ...
	extract($params);

	// deleting cookie ? set expire way in the past ...
	if ( is_null($value) || $value === false ) {
		$expires = time() - 31536000;
		unset($_COOKIE[ $key ]);
	}

	else {

		// deprecated "expire" value set ? indicate and update to proper "expires" value ...
		if ( isset($expire) ) {

			// update to new ...
			$expires = $expire;

			// remove old ...
			unset($expire);

			// indicate deprecated ...
			trigger_error('cookie_set() "expire" parameter has been deprecated. Use "expires" instead.', \E_USER_DEPRECATED);

		}

		// expire is datetime object ? output timestamp ...
		if ( $expires instanceof \DateTime ) $expires = $expires->getTimestamp();

		// ensure expire is an integer ...
		elseif ( ! is_integer($expires) ) $expires = 0;

		// expire is less than time ? assume we're adding to current seconds ...
		elseif (
			$expires > 0
			&& $expires < time()
		) $expires += time();

		// update cookie variable ...
		$_COOKIE[ $key ] = $value;

	}

	// global domain ? ...
	if ( $global ) {

		// not integer ? convert to default integer value ...
		if ( ! is_integer($global) ) $global = -2;

		// modify domain ...
		$domain = $tratus->cookie_domain($domain, $global);

	}

	// ensure samesite first letter is capitalized ...
	$samesite = ucfirst($samesite);

	// start cookie function parameters ...
	$params = [ $key, strval($value) ];

	// PHP 7.3+ ? append options array inc/samesite ...
	if ( version_compare(\PHP_VERSION, '7.3.0', '>=') ) $params[] = compact('expires', 'path', 'domain', 'secure', 'httponly', 'samesite');

	// otherwise append standard legacy params ...
	// SameSite hack @see https://stackoverflow.com/questions/39750906/php-setcookie-samesite-strict#46971326
	else $params = array_merge($params, [ $expires, sprintf('%s; samesite=%s', $path, $samesite), $domain, $secure, $httponly ]);

	// call cookie function, return bool ...
	return call_user_func_array(
		( $raw ? 'setrawcookie' : 'setcookie' ),
		$params
	);

}
