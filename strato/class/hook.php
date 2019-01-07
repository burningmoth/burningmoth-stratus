<?php
namespace BurningMoth\Stratus;
/**
 * Base class for Action and Filter classes.
 */
class Hook {

	/**
	 * Callables to call when hook is invoked.
	 * @var array
	 */
	public $callbacks = array();

	/**
	 * Whether or not the callbacks have been sorted or not.
	 * @var bool
	 */
	public $sorted = false;

	/**
	 * Number of times this hook as been invoked.
	 * @var integer
	 */
	public $calls = 0;

	/**
	 * Hook id.
	 * @var string
	 */
	public $id = '';

	/**
	 * ___construct
	 * @param string $id
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * array_sort callback to sort the hook callbacks.
	 * @see uasort()
	 */
	public function array_sort_callbacks( $a, $b ) {
		return (
			$a['order'] == $b['order']
			? 0
			: (
				$a['order'] < $b['order']
				? -1
				: 1
			)
		);
	}

	/**
	 * Return a string value from a callable to use as a key.
	 * @param string|array $callback
	 * @return string
	 */
	public function callbackToString( $callback ) {
		if ( is_array($callback) ) {
			if ( is_object( $callback[0] ) ) $callback[0] = get_class($callback[0]);
			return implode('::', $callback);
		}
		elseif ( is_object($callback) ) {
			ob_start();
			debug_zval_dump($callback);
			if ( preg_match('/#\d+/', ob_get_clean(), $matches) ) return get_class($callback) . '#' . current($matches);
			else return get_class($callback) . '#' . strval( count($this->callbacks) + microtime(true) );
		}
		return (string) $callback;
	}

	/**
	 * Add a callback to the stack.
	 * @param string|array $callback
	 * @param integer $order (optional, default 0)
	 */
	public function add( $callback, $order = 0 ) {

		global $tratus;

		// callback is word ? assume to be an Optimera callback ...
		if (
			is_string($callback)
			&& preg_match('/^\w+$/', $callback)
		) $callback = [ $tratus, $callback ];

		// valid callback ? success ...
		if ( is_callable($callback, true) ) {

			// add callback ...
			$this->callbacks[ $this->callbackToString( $callback ) ] = [
				'callback'	=> $callback,
				'order'		=> $order,
			];

			// reset sorted / will resort when called ...
			$this->sorted = false;

		}

		// invalid ! fail !
		else trigger_error(sprintf('"%s" is not a valid callback!', $callback), E_USER_WARNING);
	}

	/**
	 * Whether the stack contains a particular callback or not.
	 * @param string|array $callback
	 * @return bool
	 */
	public function has( $callback ){
		return array_key_exists( $this->callbackToString($callback), $this->callbacks );
	}

	/**
	 * Remove a callback from the stack.
	 * @param string|array $callback
	 */
	public function remove( $callback ){
		unset( $this->callbacks[ $this->callbackToString( $callback ) ] );
	}

	/**
	 * __invoke
	 * @param array $args
	 * 	- passed by either Optimera->filter() or Optimera->action()
	 * @return array
	 *	- arguments sans the first one (hook name)
	 */
	public function __invoke( $args ) {

		// sort if not sorted ...
		if ( !$this->sorted ) $this->sorted = uasort($this->callbacks, [ $this, 'array_sort_callbacks' ]);

		// increment # of calls ...
		$this->calls++;

		// return arguments less the first one ...
		return array_slice($args, 1);
	}

}
