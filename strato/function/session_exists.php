<?php
namespace BurningMoth\Stratus;
/**
* Check for (or begin) active session.
*
* @param bool $start (default, false)
*	- attempt to start session if one not already begun?
*
* @return bool
*/
function session_exists( $start = FALSE ) {

	// assume session has not started until proven otherwise ...
	$started = FALSE;

    // check to make sure we aren't using commmand line version
    if ( php_sapi_name() === 'cli' ) return $started;

	// test for started session ...
	$started = (
		// PHP 5.4+
		function_exists('session_status')
		? ( \PHP_SESSION_ACTIVE == session_status() )
		// PHP -5.4
		: ! empty( session_id() )
	);

	// start session now ? ...
	if (
		! $started
		&& $start
		&& ! headers_sent()
	) $started = session_start();

	// return bool ...
	return $started;
}

