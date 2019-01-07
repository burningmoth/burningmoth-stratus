<?php
namespace BurningMoth\Stratus;
/**
 * Return an unserialized string.
 * @param mixed $value
 * @return mixed
 */
function unserialize( $value ) {
	return (
		is_string($value)
		&& ( $unserialized = @\unserialize($value) ) !== false
		? $unserialized
		: $value
	);
}