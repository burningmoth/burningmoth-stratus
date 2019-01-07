<?php
namespace BurningMoth\Stratus;
/**
 * Returns validated email or false.
 * @param string $email
 * @param bool|string
 */
function valid_email( $email ) {
	return filter_var($email, \FILTER_VALIDATE_EMAIL);
}

