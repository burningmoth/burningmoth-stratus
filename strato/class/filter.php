<?php
namespace BurningMoth\Stratus;
/**
 * Filter hook.
 * Reduces callbacks stack to a single value.
 */
class Filter extends Hook {

	/**
	 * @see array_reduce()
	 */
	public function array_reduce_callbacks( $args, $callback ) {
		$args[0] = call_user_func_array($callback['callback'], $args);
		return $args;
	}

	/**
	 * Pass arguments to callback stack, reducing to a single value in the first argument.
	 * @param array $args
	 * @return mixed
	 */
	public function __invoke( $args ) {
		$args = parent::__invoke( $args );
		if ( !count($args) ) $args = [ null ];
		$args = array_reduce( $this->callbacks, [ $this, 'array_reduce_callbacks' ], $args );
		return $args[0];
	}

}
