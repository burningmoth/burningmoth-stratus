<?php
/**
 * Stratus class/library.
 */
namespace BurningMoth;


/**
 * Load any registered modules.
 * Modules may include traits or callbacks.
 * @see strato/module/README.md
 */
if (
	isset($_ENV['STRATUS_MODULES'])
	&& is_array($_ENV['STRATUS_MODULES'])
) array_walk($_ENV['STRATUS_MODULES'], function( $path ){

	// path is a file name only ? assume it refers to a module in this repo ...
	if ( preg_match('/^[\w-]+$/', $path) ) $path = 'strato/module/' . $path . '.php';

	// include module ...
	include $path;

});


/**
 * Stratus overloaded abstract single birth layer or "mother cloud"
 * Acts as a hub connecting and through which all things flow.
 * @see README.md
 */
if ( ! class_exists(__NAMESPACE__.'\Mutatus') ) { abstract class Mutatus {

	/**
	 * Stratus version
	 * @var string|int|float
	 */
	const VERSION = 2;

	/**
	 * Stratus directory
	 * @var string
	 */
	const DIR = __DIR__;

	/**
	 * Static method returning the $stratus global.
	 * @syntax use BuringMoth\Stratus as Strat; Strat::us()->{...}
	 * @param mixed $alt
	 * @return self
	 */
	public static function us( $alt = null ) {

		global $tratus;

		// set Stratus global if ...
		if (
			// ... not set ?
			! isset($tratus)
			// ... not Stratus ?
			|| ! ( $tratus instanceof namespace\Stratus )
		) $tratus = new namespace\Stratus;

		// return stratus via invoke ...
		return $tratus->__invoke($alt);

	}

	/**
	 * __invoke()
	 * @param mixed $alt
	 * @return self
	 */
	public function __invoke( $alt = null ) {
		$this->___default = $alt;
		return $this;
	}

	/**
	 * __construct()
	 * @note Private so that only ::us() can create the $stratus instance.
	 */
	private function __construct() {

		/**
		 * Register autoloader ...
		 * @note MUST not be a static method called with namespaced classname! Such is auto-unregistered when unserializing objects!
		 * @see http://stackoverflow.com/questions/16733789/php-autoloader-not-working#16733947
		 */
		spl_autoload_register([ $this->extend(__DIR__), '___autoLoadClass' ]);

	}

	/**
	 * __clone()
	 * @return self / singleton pattern
	 */
	public function __clone() {
		return $this;
	}


### EXTENSIONS ###

	/**
	 * Original include path.
	 * @var string
	 */
	public $___include_path = null;

	/**
	 * Paths (strata) to extended include path by.
	 * @var array
	 */
	public $___extension_paths = array();

	/**
	 * Extended paths w/o original include path.
	 * @var string
	 */
	public $___extension_path = '';

	/**
	 * Extend the Stratus pattern with another directory containing components.
	 * @param string $dir
	 *	- directory to extend, recommend passing __DIR__ here whenever possible.
	 * @param bool $prepend (optional, true)
	 *	- whether to load components before (true) or after (false) other extended directories.
	 * @return self
	 */
	public function extend( $dir, $prepend = true ) {

		// save original include path ...
		if ( is_null($this->___include_path) ) $this->___include_path = get_include_path();

		// load before others ...
		if ( $prepend ) array_unshift( $this->___extension_paths, $dir );
		// load after others ...
		else array_push( $this->___extension_paths, $dir );

		// process extension paths ...
		$this->___extension_paths = array_unique( array_filter( $this->___extension_paths ) );

		// generate extension path ...
		$this->___extension_path = implode( \PATH_SEPARATOR, $this->___extension_paths );

		// update include path ...
		set_include_path( $this->___include_path . \PATH_SEPARATOR . $this->___extension_path );

		return $this;

	}

	/**
	 * Return the path to a resource in the extended Stratus framework.
	 *
	 * @param string $relpath
	 * @param mixed $alt
	 * @return string
	 */
	public function path( $relpath, $alt = false ) {

		// save current include path ...
		$include_path = get_include_path();

		// set extension path ...
		set_include_path( $this->___extension_path );

		// determine path // @note $relpath should NEVER begin with a forward slash ('/') !!!
		$abspath = stream_resolve_include_path( ltrim($relpath, '/\\') );

		// restore include path ...
		set_include_path( $include_path );

		// return path or alternate ...
		return ( $abspath ? $abspath : $alt );

	}

	/**
	 * Project url.
	 * @var string
	 */
	public $___url = null;

	/**
	 * Fetch extension url.
	 *
	 * @param string $relpath
	 * @param mixed $alt
	 * @return mixed
	 */
	public function url( $relpath = '', $alt = false ) {

		// url not created yet ? create now ...
		if ( empty($this->___url) ) {

			$this->___url
			= 'http'
			. ( $this->array_value($_SERVER, 'HTTPS') ? 's' : '' )
			. '://'
			. $this->array_value($_SERVER, 'HTTP_HOST', 'localhost');

		}

		// no relative extension path passed ? return project url ...
		if ( empty($relpath) ) return $this->___url;

		try {

			// determine absolute path or exception ...
			$abspath = $this->path($relpath);
			if ( ! $abspath ) throw new \Exception;

			// determine document root path or exception ...
			$root = $this->array_value($_SERVER, 'DOCUMENT_ROOT', false);
			if ( ! $root ) throw new \Exception;

			// mash together url from paths ...
			$url = str_replace(
				str_replace('\\', '/', $root),
				$this->___url,
				str_replace('\\', '/', $abspath)
			);

		} catch ( \Exception $e ) {

			$url = $alt;

		}

		return $url;
	}

####################################
### CLASS & FUNCTION AUTOLOADERS ###
####################################

	/**
	 * Class autoloader.
	 *
	 * @param string $namespaced
	 *	- this is the namespace prepended class name.
	 */
	public function ___autoLoadClass( $namespaced ) {

		// break names out of namespaces ...
		$names = explode('\\', strtolower($namespaced));

		/**
		 * Default autoload path relative to include path.
		 * @note leave off .php extension, spl_autoload() takes care of this
		 *
		 * @since 1.0
		 *
		 * @filter stratus:autoload_class_path
		 * @value string $path
		 * @param string $namespaced
		 * @param array $names
		 */
		$path = $this->filter(
			'stratus:autoload_class_path',
			'strato/class/' . end($names),
			$namespaced,
			$names
		);

		/**
		 * Manage multiple paths to try autoloading from.
		 *
		 * @since 2.0
		 *
		 * @filter stratus:autoload_class_paths
		 * @value array $paths
		 * @param string $namespaced
		 * @param array $names
		 */
		$paths = $this->filter(
			'stratus:autoload_class_paths',
			array( $path ),
			$namespaced,
			$names
		);

		// autoload path string of extensions w/preceding period, separated by commas ...
		$exts = implode(',', array_map(
			function( $ext ){
				return '.' . $ext;
			},
			/**
			 * Manage autoload path extensions to check for.
			 *
			 * @since 2.0
			 *
			 * @filter stratus:autoload_class_extensions
			 * @value array $extensions
			 * @param string $namespaced
			 * @param array $names
			 * @param array $paths
			 */
			$this->filter(
				'stratus:autoload_class_extensions',
				array('php'),
				$namespaced,
				$names,
				$paths
			)
		) );

		// attempt to autoload from one of array of paths ...
		do {

			$path = current($paths);

			spl_autoload( $path, $exts );

			// @note ensure class_exists( autoload = false ) or infinity loop will result !
			if ( $success = class_exists($namespaced, false) ) break;

		} while ( next($paths) );

		/**
		 * After class has been successfully loaded (or not).
		 *
		 * @since 1.0
		 * @since 2.0
		 *	- added $path, $success parameters
		 *
		 * @action 'stratus:autoloaded_class'
		 * @param string $namespaced
		 * @param array $names
		 * @param string $path
		 * @param bool $success
		 */
		$this->action('stratus:autoloaded_class', $namespaced, $names, $path, $success);

	}

	/**
	 * Array of callables (and filters) ...
	 * @var array
	 */
	public $___callbacks = array();

	/**
	 * Retrieve a callback.
	 * @param string $key
	 * @return callable|Stratus
	 */
	public function getCallback( $key ) {
		return (
			$this->hasCallback($key)
			? $this->___callbacks[ $key ]
			: false
		);
	}

	/**
	 * Set a callback.
	 * @param string $key
	 * @param callable $callback
	 * @return Stratus
	 */
	public function setCallback( $key, $callback ) {
		$this->___callbacks[ $key ] = $callback;
		return $this;
	}

	/**
	 * Callback exists?
	 * @param string $key
	 * @return bool
	 */
	public function hasCallback( $key ) {
		return array_key_exists($key, $this->___callbacks);
	}

	/**
	 * Delete a callback.
	 * @param string $key
	 * @return Stratus
	 */
	public function deleteCallback( $key ) {
		unset( $this->___callbacks[ $key ] );
		return $this;
	}

	/**
	 * __callStatic()
	 * Loads dynamic or loaded functions from a static context w/o throwing pesky errors.
	 * @syntax use BurningMoth\Stratus as Strat; Strat::us_[name of a dynamic or loaded function](...);
	 */
	public static function __callStatic( $name, $args ) {

		global $tratus;

		// prefixed with ::us_ ? calling a dynamic method in static context ...
		if ( stripos($name, 'us_') === 0 ) {

			// static callback key ...
			$key = 'strat::' . strtolower($name);

			// has callback ? return from that now ...
			if ( $callback = $tratus->getCallback($key) ) {
				return call_user_func_array($callback, $args);
			}

			// parse out function name ...
			$name = substr($name, 3);

			// native method exists in object instance ? call it !
			if ( method_exists($tratus, $name) ) {
				return call_user_func_array( $tratus->setCallback($key, [ $tratus, $name ])->getCallback($key), $args );
			}

		}

		// throw to function autoloader ...
		return $tratus->__call( $name, $args );

	}

	/**
	 * __call()
	 * Loads dynamic or loaded functions on $tratus instance.
	 */
	public function __call( $name, $args ) {

		// function name ...
		$name = strtolower($name);

		// recorded callback ? ...
		if ( $callback = $this->getCallback($name) ) {

			// cached callable ? call now ...
			if (
				is_callable($callback)
			) return call_user_func_array($callback, $args);

			// cached function filter ? filter it now ...
			elseif(
				is_string($callback)
				&& strpos($callback, 'stratus:function') === 0
			) return (
				$callback == 'stratus:function'
				? $this->filter($callback, null, $name, $args)
				: $this->filter($callback, null, $args)
			);

			// bad ! kill it !!! ... move on ...
			else {
				trigger_error(sprintf('Stratus::%s() is NOT a valid callback!', $name), \E_USER_WARNING);
				$this->deleteCallback( $name );
			}

		}

		// namespaced function loaded ? call it now ...
		elseif (
			function_exists( $namespaced = '\\BurningMoth\\Stratus\\' . $name )
		) return call_user_func_array( $this->setCallback($name, $namespaced)->getCallback($name), $args );

		/**
		 * Filter for dynamic function.
		 * @filter stratus:function-[function_name]
		 * @param mixed $value
		 * @param array $args
		 * @return mixed
		 *	- success if not null
		 */
		elseif (
			! is_null( $value = $this->filter('stratus:function-'.$name, null, $args) )
		) {
			$this->setCallback($name, 'stratus:function-'.$name);
			return $value;
		}

		// load function, success ? then call it ...
		elseif (
			( include 'strato/function/' . $name . '.php' )
			&& function_exists($namespaced)
		) return call_user_func_array( $this->setCallback($name, $namespaced)->getCallback($name), $args );

		/**
		 * Filter for dynamic function.
		 * @filter stratus:function
		 * @param mixed $value
		 * @param string $name
		 * @param array $args
		 * @return mixed
		 *	- success if not null
		 */
		elseif (
			! is_null( $value = $this->filter('stratus:function', null, $name, $args) )
		) {
			$this->setCallback($name, 'stratus:function');
			return $value;
		}

		// fail !
		trigger_error(sprintf('Stratus::%s() does not exist!', $name), \E_USER_ERROR);
		return null;

	}

### PROPERTIES / DATA ###

	/**
	 * Default value to fallback to if get fails.
	 * @var mixed
	 */
	public $___default = null;

	/**
	 * Variable data.
	 * @var array
	 */
	public $___data = array();

	/**
	 * MAGIC __get()
	 */
	public function __get( $key = null ) {

		// get set value or default fallback ...
		$value = (
			is_null($key)
			? $this->___data
			: (
				$this->__isset($key)
				? $this->___data[ $key ]
				: $this->___default
			)
		);

		// reset default ...
		$this->___default = null;

		// return value ...
		return $value;
	}

	/**
	 * Return a property or alternate value.
	 * @syntax $stratus($alt)->key == $stratus->get('key', $alt);
	 * @param string $key
	 * @param mixed $alt
	 * @return mixed
	 */
	public function get( $key = null, $alt = null ) {
		$this->___default = $alt;
		return $this->__get($key);
	}

	/**
	 * MAGIC __isset()
	 */
	public function __isset( $key ) {
		return (
			array_key_exists($key, $this->___data)
			&& !is_null( $this->___data[ $key ] )
		);
	}

	// alias of __isset()
	public function exists( $key ) {
		return $this->__isset($key);
	}

	/**
	 * MAGIC __unset()
	 */
	public function __unset( $key ) {
		unset( $this->___data[ $key ] );
	}

	/**
	 * Delete one or more properties.
	 * @syntax $stratus->delete('key1'[, 'key2', ...]);
	 * @syntax $stratus->delete(array('key1', 'key2', ...));
	 * @param string $key
	 * @param ...
	 * @return self
	 */
	public function delete( $key ) {

		// single key passed ? simple unset ...
		if ( func_num_args() == 1 && is_string($key) ) $this->__unset($key);

		// multiple keys passed ? ...
		else $this->___data = array_diff_key($this->___data, array_flip( is_array($key) ? $key : func_get_args() ));

		return $this;
	}

	/**
	 * MAGIC __set()
	 */
	public function __set( $key, $value ) {
		$this->___data[ $key ] = $value;
	}

	/**
	 * Set one or more properties.
	 * @syntax $stratus->set('key', [value]);
	 * @syntax $stratus->set(array('key' => 'value', ...));
	 * @param string|array $key
	 * @param mixed $value (optional, null)
	 * @return self
	 */
	public function set( $key, $value = null ) {

		// update properties data en mass w/an array ...
		if ( is_array($key) ) $this->___data = array_replace( $this->___data, $key );

		// unset a value ...
		elseif ( is_null($value) ) $this->__unset( $key );

		// set a value ...
		else $this->__set( $key, $value );

		return $this;
	}

	/**
	 * Returns a property reference.
	 * @syntax $ref =& {stratus}->ref('property', 'init value');
	 *
	 * @param string $key (optional, default null)
	 *	- if omitted, the entire properties data array will be returned
	 * @param mixed $initial (optional, default empty string)
	 *	- initial property value if it doesn't yet exist and needs to be set first
	 * @return mixed
	 */
	public function &ref( $key = null, $initial = '' ) {

		// no key ? return properties data array ...
		if ( !is_string($key) ) return $this->___data;

		// not set ? set property w/initial value ...
		if ( !$this->__isset($key) ) $this->__set($key, $initial);

		// return the property value ...
		return $this->___data[ $key ];

	}

#############################
### FILTER & ACTION HOOKS ###
#############################

	/**
	 * Action hooks.
	 * @see strato/class/action.php
	 * @var array
	 */
	public $___actions = array();

	/**
	 * Trigger an action hook.
	 * @param string $action
	 * @return self
	 */
	public function action( $action ) {
		if ( $this->hasAction($action) ) return $this->___actions[ $action ]( func_get_args() );
		return $this;
	}

	/**
	 * Trigger an action hook only once and never again.
	 * @param string $action
	 * @return self
	 */
	public function actionOnce( $action ) {
		if ( $this->didAction($action) == 0 ) return $this->action( $action );
		return $this;
	}

	/**
	 * Number of times an action hook has been triggered.
	 * @param string $action
	 * @return integer
	 */
	public function didAction( $action ) {
		return (
			$this->hasAction($action)
			? $this->getAction($action)->calls
			: 0
		);
	}

	/**
	 * Return an action hook.
	 * @param string $action
	 * @return Stratus\Action
	 */
	public function getAction( $action ) {
		return (
			$this->hasAction($action)
			? $this->___actions[ $action ]
			: ( $this->___actions[ $action ] = new Stratus\Action( $action ) )
		);
	}

	/**
	 * Whether an action hook or an action hook callback has been set.
	 * @param string $action
	 * @param callable $callback (optional, null)
	 * @return bool
	 */
	public function hasAction( $action, $callback = null ) {
		return (
			array_key_exists($action, $this->___actions)
			&& $this->___actions[ $action ] instanceof Stratus\Action
			&& (
				empty($callback)
				? true
				: $this->___actions[ $action ]->has( $callback )
			)
		);
	}

	/**
	 * Add a callback to an action hook.
	 * @param string $action
	 * @param callable $callback
	 * @param integer $order (optional, 0)
	 * @return self
	 */
	public function addAction( $action, $callback, $order = 0 ) {
		$this->getAction( $action )->add( $callback, $order );
		return $this;
	}

	/**
	 * Remove an action hook or action hook callback.
	 * @param string $action
	 * @param callable $callback (optional, null)
	 * @return self
	 */
	public function removeAction( $action, $callback = null ) {
		if ( $this->hasAction($action, $callback) ) {
			if ( $callback ) $this->___actions[ $action ]->remove( $callback );
			else unset( $this->___actions[ $action ] );
		}
		return $this;
	}


	/**
	 * Array of filter hooks.
	 * @see strato/class/filter.php
	 * @var array
	 */
	public $___filters = array();

	/**
	 * Calls a filter hook and returns the value.
	 * @param string $filter
	 * @param mixed $value
	 *	- the initial value before return filtered
	 * @params [...]
	 *	- any number of additional parameters to pass to the filter callbacks.
	 * @return mixed
	 */
	public function filter( $filter, $value ) {
		if ( $this->hasFilter($filter) ) return $this->___filters[ $filter ]( func_get_args() );
		return $value;
	}

	/**
	 * Whether a filter hook or filter hook callback has been set.
	 * @param string $filter
	 * @param callable $callback (optional, null)
	 * @return bool
	 */
	public function hasFilter( $filter, $callback = null ) {
		return (
			array_key_exists($filter, $this->___filters)
			&& $this->___filters[ $filter ] instanceof Stratus\Filter
			&& (
				empty($callback)
				? true
				: $this->___filters[ $filter ]->has( $callback )
			)
		);
	}

	/**
	 * Number of times a filter hook has been triggered.
	 * @param string $filter
	 * @return integer
	 */
	public function didFilter( $filter ) {
		return (
			$this->hasFilter($filter)
			? $this->getFilter($filter)->calls
			: 0
		);
	}

	/**
	 * Return a filter hook object.
	 * @param string $filter
	 * @return Stratus\Filter
	 */
	public function getFilter( $filter ) {
		return (
			$this->hasFilter($filter)
			? $this->___filters[ $filter ]
			: ( $this->___filters[ $filter ] = new Stratus\Filter( $filter ) )
		);
	}

	/**
	 * Add a filter hook callback.
	 * @param string $filter
	 * @param string|array $callback
	 * @param integer $order (optional, 0)
	 * @return self
	 */
	public function addFilter( $filter, $callback, $order = 0 ) {
		$this->getFilter( $filter )->add( $callback, $order );
		return $this;
	}

	/**
	 * Remove a filter hook or filter hook callback.
	 * @param string $filter
	 * @param string|array $callback (optional, null)
	 * @return self
	 */
	public function removeFilter( $filter, $callback = null ) {
		if ( $this->hasFilter($filter, $callback) ) {
			if ( $callback ) $this->___filters[ $filter ]->remove($callback);
			else unset( $this->___filters[ $filter ] );
		}
		return $this;
	}


} }


/**
 * Extended Stratus super class inc/traits.
 * @see strato/module/README.md
 */
if ( ! class_exists(__NAMESPACE__.'\Stratus') ) {

	// presume unsuccessful ...
	$success = false;

	// has traits ? process ...
	if (
		isset($_ENV['STRATUS_TRAITS'])
		&& is_array($_ENV['STRATUS_TRAITS'])
	) {

		// unique path to generated code to include ...
		$path = sprintf(
			'%s%sburningmoth-stratus-%s-%u.tmp',
			sys_get_temp_dir(),
			\DIRECTORY_SEPARATOR,
			Mutatus::VERSION,
			crc32( __FILE__ . print_r($_ENV['STRATUS_TRAITS'], true) )
		);

		if (
			// unable to include pre-generated code ...
			!( $success = @include $path )

			&& ( $traits = $_ENV['STRATUS_TRAITS'] )

			// verfied trait names ...
			&& ( $trait_names = array_filter( array_keys($traits), 'trait_exists' ) )

			// verified traits ...
			&& ( $traits = array_intersect_key( $traits, array_flip($trait_names) ) )
		) {

			// begin generated code ...
			$code = 'namespace BurningMoth; class Stratus extends Mutatus { ';

			// ensure leading slash for trait names ...
			$leadingslashit = function( $name ){ if ( $name[0] != '\\' ) $name = '\\' . $name; return $name;  };

			// true if valid method name ...
			$is_method = function( $name ){ return preg_match('/^[a-zA-Z0-9_]+$/', $name); };

			// append traits ...
			$code .= 'use ' . implode(', ', array_map($leadingslashit, $trait_names) );

			// trait conflict resolutions ...
			$resolves = array();

			// process trait rules into resolutions ...
			foreach ( $traits as $trait_a => $rules ) {

				// ensure leading slash ...
				$trait_a = $leadingslashit($trait_a);

				// has rules to resolve conflicts ? process ...
				if ( is_array($rules) ) {

					foreach ( $rules as $method => $trait_b ) {

						// check method ...
						if ( !$is_method($method) ) {
							trigger_error(sprintf('"%s" is not a valid method name!', $method), \E_USER_WARNING);
							continue;
						}

						// valid trait ? override ...
						if ( trait_exists($trait_b) ) {
							$op = 'insteadof';
							$trait_b = $leadingslashit($trait_b);
						}

						// valid method name ? alias ...
						elseif ( $is_method($trait_b) ) $op = 'as';

						// report error ...
						else {
							$op = false;
							trigger_error(sprintf('"%s" is neither a valid trait nor method name!', $trait_b), \E_USER_WARNING);
						}

						// format conflict resolution ...
						if ( $op ) $resolves[] = sprintf('%s::%s %s %s;', $trait_a, $method, $op, $trait_b);

					}

				}

			}

			// append resolves if any ...
			$code .= ( $resolves ? ' { ' . implode(' ', $resolves) . ' } ' : '; ' );

			// close code ...
			$code .= '} return ' . time() . ';';

			// code evaluates ? write to tmpfile ...
			if ( $success = eval($code) ) file_put_contents($path, '<?php ' . $code);

		}

	}

	// no generation or otherwise unsuccessful ? establish class normally ...
	if ( empty($success) ) {
		class Stratus extends Mutatus {}
	}

}

/**
 * Set global $tratus variable.
 * @var Stratus
 */
$GLOBALS['tratus'] = Stratus::us();

/**
 * Trigger any initialization callbacks.
 * @see strato/module/README.md
 */
if (
	isset($_ENV['STRATUS_CALLBACKS'])
	&& is_array($_ENV['STRATUS_CALLBACKS'])
) foreach ( $_ENV['STRATUS_CALLBACKS'] as $callback ) call_user_func($callback, $GLOBALS['tratus']);

// scrub Stratus environmental vars ...
unset($_ENV['STRATUS_MODULES'], $_ENV['STRATUS_TRAITS'], $_ENV['STRATUS_CALLBACKS']);

/**
 * return global $tratus instance.
 * @syntax $tratus = require 'stratus.php';
 */
return $GLOBALS['tratus'];
