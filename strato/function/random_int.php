<?php
namespace BurningMoth\Stratus;
/**
 * Return cryptosecure random integer.
 * @param integer $min
 * @param integer $max
 * @return integer
 */
function random_int( $min = 0, $max = null ) {

	// attempt to use random_int() ...
	try {

		// ensure valid minimum value ...
		if ( ! is_numeric($min) ) $min = 0;
		else $min = (integer) $min;

		// ensure valid maximum value ...
		if ( ! is_numeric($max) ) $max = \PHP_INT_MAX;
		else $max = (integer) $max;

		// equal ? throw error ...
		if ( $max == $min ) throw new \TypeError;

		// parameters passed wrong ? warn and correct ...
		elseif ( $max < $min ) {
			list($max, $min) = array_map('intval', func_get_args());
			trigger_error(__FUNCTION__.'(): $min value should be less than $max!', \E_USER_WARNING);
		}

		// get random integer ...
		$value = \random_int($min, $max);

	}

	// invalid parameters passed, return minimum ...
	catch ( \TypeError $e ) {
		$value = $min;
	}

	// random_int() doesn't exist or randomizing source can't be found ...
	// @see https://stackoverflow.com/a/13733588
	catch ( \Error | \Exception $e ) {

		try {

			// get range of numbers ...
			$range = $max - $min;

			// range of 1 always results in 0, use mt_rand instead ...
			if ( $range == 1 ) $value = mt_rand($min, $max);

			// use openssl_random_pseudo_bytes()
			else {

				// ensure a realistic range ...
				if (
					! is_integer($range)
					|| $range > \PHP_INT_MAX
				) $range = \PHP_INT_MAX;

				// bytes length ...
				$bytes = ceil( log($range, 2) / 8 );

				// get number from random bytes until one in range ...
				do {
					$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
				} while ( $rnd > $range );

				// add random number to minimum and return ...
				$value = $min + $rnd;

			}

		}

		// if for some reason the above doesn't work, resort to NOT-cryptosecure mt_rand()
		catch ( \Error | \Exception $e ) {
			$value = mt_rand($min, $max);
		}

	}

	return $value;
}

