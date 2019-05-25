<?php
namespace BurningMoth\Stratus;
/**
 * Generate a cryptographically random string.
 * @syntax $Rstr = ( new RandomString(24) )->alpha()->numeric()->special()->chars("additional characters");
 * @syntax echo $Rstr;
 * @syntax $token = $Rstr(32);
 */
class RandomString {

	/**
	 * Length of the generated random string.
	 * @var int
	 */
	private $length = 24;

	/**
	 * Character set to generate string from.
	 * @var string
	 */
	private $chars = '';

	/**
	 * Constructor.
	 * @param int $length
	 */
	public function __construct( $length = null ) {
		if ( is_numeric($length) ) $this->length = (integer) $length;
	}

	/**
	 * Add to character set.
	 * @param string $str
	 *	- tokens alpha, numeric, hex and special will add associated character sets.
	 *	- token empty will reset the character set to an empty string.
	 */
	public function chars( $str ) {

		switch ( $str ) {
			case 'alpha':
				$this->chars .= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				break;

			case 'numeric':
				$this->chars .= '0123456789';
				break;

			case 'hex':
				$this->chars .= '0123456789abcdef';
				break;

			case 'special':
				$this->chars .= '.,=+-_!@#$%^&*?';
				break;

			case 'empty':
				$this->chars = '';
				break;

			default:
				$this->chars .= $str;
				break;

		}

		return $this;
	}

	/**
	 * Caller. Associated char sets.
	 * ->alpha()
	 * ->numeric()
	 * ->hex()
	 * ->special()
	 * ->empty()
	 */
	public function __call( $name, $args ) {
		if ( in_array($name, [ 'alpha', 'numeric', 'hex', 'special', 'empty' ]) ) return $this->chars($name);
		return $this;
	}

	/**
	 * Invoked as function.
	 * @param int $length
	 * @return str
	 */
	public function __invoke( $length = null ) {
		if ( is_numeric($length) ) $this->length = (integer) $length;
		return $this->__toString();
	}

	/**
	 * Stringify into random string.
	 */
	public function __toString() {

		global $tratus;

		// ensure a default alphanumeric character set ...
		if ( empty($this->chars) ) $this->chars('alpha')->chars('numeric');

		// ensure unique characters ...
		else $this->chars = implode('', array_unique(str_split($this->chars)));

		// random string to generate ...
		$str = '';

		// random range max ...
		$max = strlen($this->chars) - 1;

		// generate random string ...
		for ( $i=0; $i < $this->length; $i++ ) $str .= $this->chars[ $tratus->random_int(0, $max) ];

		return $str;
	}

}

