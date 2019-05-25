<?php
namespace BurningMoth\Stratus;
use BurningMoth\Stratus as Strat;
/**
 * Object interface for PHP cURL functionality.
 */
class cURL {

	/**
	 * cURL handle.
	 * @var resource
	 */
	public $handle;

	/**
	 * Initializes a new curl handle as curl_init() would.
	 * @param string $url
	 */
	public function __construct( $url ) {
		$this->handle = curl_init($url);
	}

	/**
	 * Copy handle when cloning otherwise the clone will continue to affect the original.
	 */
	public function __clone() {
		$this->handle = $this->copy_handle();
	}

	/**
	 * Close handle on object destruct.
	 */
	public function __destruct() {
		if ( is_resource($this->handle) ) $this->close();
	}

	/**
	 * Return a CURLOPT_* constant value or false
	 * @param scalar $key
	 * @return bool|int
	 */
	protected function curlopt( $key ) {
		return (
			is_string($key)
			? Strat::us_constant( ( ( $key = strtoupper($key) ) == 'HEADER_OUT' ? 'CURLINFO_' : 'CURLOPT_' ) . $key, false )
			: $key
		);
	}

	/**
	 * Set CURLOPT_* value.
	 * @example $curl->returnTransfer = true eq curl_setopt($ch, CURLOPT_RETURNTRANSFER, true)
	 */
	public function __set( $key, $value ) {
		$opt = $this->curlopt($key);
		if ( $opt === false ) trigger_error(sprintf('CURLOPT_%s is not a valid constant!', strtoupper($key)));
		else $this->setopt($opt, $value);
	}

	/**
	 * Fetch CURLINFO_* value.
	 * @example $curl->effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL)
	 */
	public function __get( $key ) {
		return $this->getinfo( Strat::us_constant('CURLINFO_' . strtoupper($key), 0) );
	}

	/**
	 * Interface with curl_* prefixed function.
	 */
	public function __call( $name, $args ) {

		// automatically prepend the handle for the following functions ...
		if ( in_array($name, [ 'close', 'copy_handle', 'error', 'errno', 'escape', 'exec', 'getinfo', 'pause', 'reset', 'setopt_array', 'setopt', 'unescape' ]) ) array_unshift($args, $this->handle);

		// call curl prefixed function ...
		return call_user_func_array('curl_'.$name, $args);

		/*
		file_create
		init
		strerror
		version
		multi_* and share_* is something else ...
		*/
	}

	/**
	 * Prepare options before calling curl_setopt_array();
	 */
	public function setopt_array( array $options ) {

		// process any string values into their corresponding constants like _set() does.
		$options = array_combine(
			array_map([$this, 'curlopt'], array_keys($options)),
			array_values($options)
		);

		// PHP will have issued warning of array_combine() failure ...
		return (
			is_array($options)
			? \curl_setopt_array($this->handle, $options)
			: false
		);
	}

	/**
	 * Combine curl_exec() and error reporting.
	 */
	public function exec() {
		// returns false ? report error ...
		if ( ( $value = \curl_exec( $this->handle ) ) === false && $this->error() ) trigger_error($this->error(), \E_USER_WARNING);
		// has error ? report error ...
		elseif ( $errno = $this->errno() ) trigger_error($this->strerror($errno), \E_USER_WARNING);
		// return any value ...
		return $value;
	}

}

