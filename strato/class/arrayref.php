<?php
namespace BurningMoth\Stratus;
/**
 * Interface with an variable array via reference as though it were an object.
 * @since 1.0
 * @since 2.1
 * 	- implements ArrayAccess, Countable and Iterator interfaces to behave more like an array if necessary
 */
class ArrayRef implements \ArrayAccess, \Countable, \Iterator {

	/**
	 * Array data reference.
	 * @var array
	 */
	public $___data;

	/**
	 * __CONSTRUCT
	 * @param array $data
	 */
	public function __construct( array &$data ){
		$this->___data =& $data;
	}

	/**
	 * __SET
	 */
	public function __set( $key, $value ) {
		$this->set($key, $value);
	}

	/**
	 * Set a value or values.
	 * @param array|string $key
	 * @param mixed $value
	 * @return self
	 */
	public function set( $key, $value = null ) {

		// update multiple values ...
		if ( is_array($key) ) $this->___data = array_replace($this->___data, $key);

		// update single value ...
		else $this->___data[ $key ] = $value;

		return $this;
	}

	/**
	 * __ISSET
	 */
	public function __isset( $key ) {
		return $this->exists($key);
	}

	/**
	 * Whether a key / value exists or not.
	 * @param string $key
	 * @return bool
	 */
	public function exists( $key ) {
		return (
			array_key_exists($key, $this->___data)
			&& ! is_null( $this->___data[ $key ] )
		);
	}

	/**
	 * __UNSET
	 */
	public function __unset( $key ) {
		$this->delete($key);
	}

	/**
	 * Delete a keyed value or values.
	 * @param array|string $key
	 * @return self
	 */
	public function delete( $key ) {
		$keys = ( is_array($key) ? $key : func_get_args() );
		$this->___data = array_diff_key($this->___data, array_flip($keys));
		return $this;
	}

	/**
	 * __GET
	 */
	public function __get( $key ) {
		return $this->get($key);
	}

	/**
	 * Retrieve a keyed value or alternate.
	 */
	public function get( $key = null, $alt = null ) {
		return (
			empty($key)
			? $this->___data
			: (
				$this->exists($key)
				? $this->___data[ $key ]
				: $alt
			)
		);
	}

	/**
	 * Return a keyed reference.
	 * @param string $key (optional)
	 *	- omitting a key will return the entire array reference
	 * @param mixed $initial
	 *	- initial value to set if key does not exist
	 * @return reference
	 */
	public function &ref( $key = null, $initial = '' ) {
		if ( empty($key) ) return $this->___data;
		if ( !$this->exists($key) ) $this->set($key, $initial);
		return $this->___data[ $key ];
	}

	/**
	 * __TOSTRING
	 * @return a query string from the array
	 */
	public function __toString() {
		return http_build_query($this->___data);
	}

	/**
	 * __INVOKE
	 * @param string $str
	 *	- query string to parse / merge into array
	 * @return self|array
	 *	- returns self if passing query string
	 * 	- returns array data
	 */
	public function __invoke( $str = '' ) {

		if ( !empty($str) ) {
			parse_str($str, $arr);
			return $this->set($arr);
		}

		return $this->___data;
	}


	/**
	 * Implementation of ArrayAccess interface methods.
	 * @since 2.1
	 */
	public function offsetExists( $offset ){
		return $this->exists($offset);
	}

	public function offsetGet( $offset ){
		return $this->get($offset);
	}

	public function offsetSet( $offset, $value ){
		$this->set($offset, $value);
	}

	public function offsetUnset( $offset ){
		$this->delete($offset);
	}

	/**
	 * Implementation of Countable interface methods.
	 * @since 2.1
	 */
	public function count(){
		return count($this->___data);
	}

	/**
	 * Implementation of Interator interface methods.
	 * @since 2.1
	 */
	public function current(){
		return current($this->___data);
	}

	public function key(){
		return key($this->___data);
	}

	public function next(){
		next($this->___data);
	}

	public function rewind(){
		reset($this->___data);
	}

	public function valid(){
		return $this->key() !== null;
	}

}


