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
		$this->headerFunction = [ $this, '___parseHeader' ];
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
	 * Parsed response headers.
	 * @var array
	 */
	public $___response_headers = array();

	/**
	 * CURLOPT_HEADERFUNCTION callback (set in __construct)
	 * @param resource $handle
	 * @param string $header
	 * @return int
	 */
	public function ___parseHeader( $handle, $header ) {

		global $tratus;

		// ammend response headers array ...
		$this->___response_headers = array_replace($this->___response_headers, $tratus->http_parse_headers($header));

		// MUST return length of the header read or explosions !!!!
		return strlen($header);
	}

	/**
	 * Retrieve one or more header values.
	 * @param string $key
	 * @return string|array
	 */
	public function getheader( $key = null ) {

		if ( is_null($key) ) return $this->___response_headers;

		$key = strtolower($key);

		return (
			array_key_exists($key, $this->___response_headers)
			? $this->___response_headers[ $key ]
			: null
		);
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

		global $tratus;

		/**
		 * Trigger before curl executes.
		 * @action curl_before_exec
		 * @param cURL $curl
		 */
		$tratus->action('curl_before_exec', $this);

		// returns false ? report error ...
		if ( ( $value = \curl_exec( $this->handle ) ) === false && $this->error() ) trigger_error($this->error(), \E_USER_WARNING);

		// has error ? report error ...
		elseif ( $errno = $this->errno() ) trigger_error($this->strerror($errno), \E_USER_WARNING);

		/**
		 * Trigger after curl executes.
		 * @action curl_after_exec
		 * @param cURL $curl
		 */
		$tratus->action('curl_after_exec', $this);

		// return any value ...
		return $value;
	}

	/**
	 * Alias for exec().
	 */
	public function __invoke() {
		return $this->exec();
	}

	/**
	 * GET URL
	 * @param string $url
	 * @param array $query
	 * @param array $headers
	 * @param array $opts
	 * @return string
	 */
	static public function get( $url, array $query = [], array $headers = [], array $opts = [] ) {

		global $tratus;

		// has query ? ...
		if ( $query ) {

			// convert to URL object if not already ...
			if ( ! $url instanceof URL ) $url = new URL($url);

			// update query ...
			$url->query = array_replace($url->query(), $query);

		}

		// has headers ? add headers ...
		if ( $headers ) $opts['httpheader'] = $tratus->http_build_headers($headers);

		// set to return value ...
		$opts['returntransfer'] = true;

		// retrieve output ...
		$curl = new self(strval($url));
		$curl->setopt_array($opts);
		$op = $curl->exec();
		$curl->close();

		// return output ...
		return $op;
	}

	/**
	 * POST URL
	 * @param string $url
	 * @param string $data
	 * @param array $headers
	 * @param array $opts
	 * @return string
	 */
	static public function post( $url, $data, array $headers = [], array $opts = [] ) {

		global $tratus;

		// add headers ...
		$opts['httpheader'] = $tratus->http_build_headers(array_replace([ 'content-type' => 'application/octet-stream' ], $headers));

		// add post data ...
		$opts['postfields'] = strval($data);

		// set to return value ...
		$opts['returntransfer'] = true;

		// retrieve output ...
		$curl = new self(strval($url));
		$curl->setopt_array($opts);
		$op = $curl->exec();
		$curl->close();

		// return output ...
		return $op;
	}

}

