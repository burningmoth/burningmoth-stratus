<?php
namespace BurningMoth\Stratus;
/**
 * Is a valid email address?
 * @param string $email
 * @return bool
 */
function is_email( $email ) {
	return filter_var($email, \FILTER_VALIDATE_EMAIL, \FILTER_FLAG_EMAIL_UNICODE);
}
