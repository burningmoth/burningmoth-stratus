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
	if ( ! is_array($headers) ) {

		$values = explode("\r\n", $headers);

		$headers = array();

		foreach(
			$values as $value
		) if (
			preg_match('/^([^:]+):(.*)$/', $value, $match)
		) $headers[ trim(next($match)) ] = trim(next($match));

	}

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

	// add subject to headers ...
	$headers['subject'] = $subject;

	/**
	 * Filter to addresses.
	 * @filter mail_to
	 * @value array $to
	 * @param array $headers
	 */
	$to = $tratus->filter('mail_to', $to, $headers);

	/**
	 * Filter mail headers.
	 * @filter mail_headers
	 * @value array $headers
	 * @param array $to
	 */
	$headers = $tratus->filter('mail_headers', $headers, $to);

	/**
	 * Filter mail body.
	 * @filter mail_body
	 * @value string $body
	 * @param array $to
	 * @param array $headers
	 */
	$body = $tratus->filter('mail_body', $body, $to, $headers);

	// combine to into string ...
	$to = implode(', ', $to);

	// remove to and subject headers or they will be doubled ...
	unset($headers['subject'], $headers['to']);

	// ensure a from header exists ...
	if ( ! array_key_exists('from', $headers) ) $headers['from'] = 'no-reply@' . $tratus->array_value($_SERVER, 'HTTP_HOST', 'localhost');

	// combine headers into string ...
	foreach ( $headers as $name => &$value ) $value = str_replace(' ', '-', ucwords(str_replace('-', ' ', $name))) . ': ' . $value;
	$headers = implode("\r\n", $headers);

	// send mail ...
	return \mail($to, $subject, $body, $headers, $params);
}

