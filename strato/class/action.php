<?php
namespace BurningMoth\Stratus;
/**
 * Action hook.
 * Invokes each callback in the stack. No values are returned.
 */
class Action extends Hook {

	/**
	 * @see array_walk()
	 */
	public function array_walk_callbacks( $callback, $key, $args ) {
		call_user_func_array( $callback['callback'], $args );
	}

	/**
	 * Execute the callback stack.
	 * @param array $args
	 * return bool true
	 */
	public function __invoke( $args ) {
		$args = parent::__invoke($args);
		return array_walk( $this->callbacks, [ $this, 'array_walk_callbacks' ], $args );
	}

}
