<?php
namespace BurningMoth\Stratus;
/**
 * Better mail() function.
 * @param string|array $to
 * @param string $subject
 * @param string $body
 * @param string|array $headers
 * @param string $params
 * @return bool
 */
function mail( $to, $subject, $body, $headers = '', $params = '' ) {

	global $tratus;

	// ensure to is array ...
	if ( ! is_array($to) ) $to = array_map('trim', explode(',', strval($to)));

	// ensure headers is array ...
	if ( ! is_array($headers) ) $headers = $tratus->http_parse_headers($headers);

	// lowercase header names ...
	$headers = array_combine(
		array_map('strtolower', array_keys($headers)),
		array_values($headers)
	);

	/**
	 * Filter mail subject.
	 * @filter mail_subject
	 * @value string $subject
	 * @param array $to
	 * @param array $headers
	 */
	$subject = $tratus->filter('mail_subject', $subject, $to, $headers);

	/**
	 * Filter to addresses.
	 * @filter mail_to
	 * @value array $to
	 * @param array $headers
	 * @param string $subject
	 */
	$to = $tratus->filter('mail_to', $to, $headers, $subject);

	/**
	 * Filter mail headers.
	 * @filter mail_headers
	 * @value array $headers
	 * @param array $to
	 * @param string $subject
	 */
	$headers = $tratus->filter('mail_headers', $headers, $to, $subject);

	/**
	 * Filter mail body.
	 * @filter mail_body
	 * @value string $body
	 * @param array $to
	 * @param array $headers
	 */
	$body = $tratus->filter('mail_body', $body, $to, $headers);

	// remove to and subject headers or they will be doubled ...
	unset($headers['subject'], $headers['to']);

	// ensure a from header exists ...
	if ( ! array_key_exists('from', $headers) ) $headers['from'] = 'no-reply@' . $tratus->array_value($_SERVER, 'HTTP_HOST', 'localhost');

	/**
	 * Filter whether mail was sent or not.
	 * This provides an opportunity to override the PHP's built-in mail() function.
	 * @filter mail_sent
	 * @value bool $sent (default false)
	 * @param array $to
	 * @param string $subject
	 * @param string $body
	 * @param array $headers
	 */
	if ( ! $success = $tratus->filter('mail_sent', false, $to, $subject, $body, $headers) ) {

		// combine to into string ...
		$to = implode(', ', $to);

		// ensure header values are strings ...
		$headers = array_map(function( $value ){
			return (
				is_array($value)
				? implode(', ', $value)
				: strval($value)
			);
		}, $headers);

		// send mail ...
		$success = \mail($to, $subject, $body, $headers, $params);

	}

	return $success;
}

