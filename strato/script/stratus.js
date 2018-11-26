/**
 * Stratus base framework.
 * @requires proto.js
 * @note Shouldn't be dependent on any other framework.
 * @note Should be nominally supportive of IE 9-ish+ browsers, no fancy goodness yet.
 */

// $tratus namespace if not already declared.
if ( ! $tratus ) var $tratus = {};

/* ############# *
 * ### TESTS ### *
 * ############# */

/**
 * Test whether a value is string type or not.
 * @param mixed value
 * @return bool
 */
$tratus.isString = function( value ){
	return (
		value instanceof String
		|| typeof value == 'string'
	);
}

/**
 * Test whether a value is number type or not.
 * @param mixed value
 * @return bool
 */
$tratus.isNumber = function( value ){
	return (
		value instanceof Number
		|| typeof value == 'number'
	);
}

/**
 * Test whether a value is an object
 * @param mixed value
 * @return bool
 */
$tratus.isObject = function( value ){
	return (
		(
			value instanceof Object
		 	|| typeof value == 'object'
		)
		&& value !== null
	);
}


/* ################## *
 * ### FORMATTING ### *
 * ################## */

/**
 * Unicode-safe versions of btoa() and atob() ( base64 encoding and decoding respectively ).
 * @author Johan Sundström
 * @see http://ecmanaut.blogspot.com/2006/07/encoding-decoding-utf8-in-javascript.html
 */
$tratus.base64encode = function( str ) {
	return window.btoa(unescape(encodeURIComponent(str)));
}

$tratus.base64decode = function( str ) {
	return decodeURIComponent(escape(window.atob(str)));
}


/* ############################### *
 * ### FILTER AND ACTION HOOKS ### *
 * ############################### */

/**
 * Hook object.
 *
 * @syntax hook = new $tratus.Hook( String hook )
 *	.add( Function callback[,String key, Number order]] )
 *	.remove( String key | Function callback )
 *	.exec( mixed[, param, param, ...]);
 *
 * @syntax Hook.Callback callback = hook.get( String key | Function callback );
 * @syntax integer hook.calls;
 * @syntax bool hook.has( String key | Function callback );
 *
 * @param string hook
 * @return object
 */
$tratus.Hook = function( hook ){

	/**
	 * hook this object is called on
	 * @var string
	 */
	this.hook = hook;

	/**
	 * list of key: this.Callback objects
	 * @var object
	 */
	this.callbacks = {};

	/**
	 * Callback wrapper object.
	 *
	 * @syntax callback = new this.Callback( Function )
	 * 	.exec( Array args );
	 *
	 * @param function callback
	 * @return Callback
	 */
	this.Callback = function( callback ){

		/**
		 * @var function
		 */
		this.callback = callback;

		/**
		 * key this callback is indexed on.
		 * @var string
		 */
		this.key = '';

		/**
		 * ascending order this callback is called in relative to other callbacks
		 * @var integer
		 */
		this.order = 0;

		/**
		 * Execute this callback against passed arguments.
		 * @param array args
		 * @return mixed
		 */
		this.exec = function( args ){
			return this.callback.apply(this, args);
		};

		return this;
	};

	/**
	 * number of times this filter has been called
	 * @var integer
	 */
	this.calls = 0;

	/**
	 * Cast a key string from function or non-string
	 * @param mixed callback
	 * @return string
	 */
	this.key = function( callback ){

		var key;

		// already string ...
		if ( $tratus.isString(callback) ) key = callback;

		// something else ! turn into string ...
		else {

			key = callback.toString();

			if ( callback instanceof Function ) {
				// look for function name ...
				var matches = key.match(/^function (\w+)/);
				key = (
					// named function ? use name as key ...
					matches
					? matches.pop()

					// anon function, base64 encode ...
					: $tratus.base64encode( key )
				);
			}

			// base64 encode ...
			else key = $tratus.base64encode( key );

		}

		return key;

	};

	/**
	 * Add a callback function to the stack.
	 * @param function callback
	 * @param string key
	 * @param integer order
	 * @return self
	 */
	this.add = function( callback ){

		if ( callback instanceof Function ) {

			// wrap callback in object ...
			callback = new this.Callback( callback );

			// set key ...
			callback.key = this.key( callback.callback );

			// passed key or order ...
			if (
				arguments.length > 1
			) {

				if (
					$tratus.isNumber(arguments[1])
				) callback.order = arguments[1];

				else if (
					arguments[1]
					&& $tratus.isString(arguments[1])
				) callback.key = arguments[1];

			}

			// passed order ...
			if (
				arguments.length > 2
				&& $tratus.isNumber(arguments[2])
			) callback.order = arguments[2];

			// set callback ...
			this.callbacks[ callback.key ] = callback;

		}

		else console.error(callback, 'is not a valid callback function!');

		return this;
	};

	/**
	 * remove a callback from the stack.
	 * @param string|function callback
	 * @return self
	 */
	this.remove = function( callback ){
		delete this.callbacks[ this.key(callback) ];
		return this;
	};

	/**
	 * stack contain a callback
	 * @param string|function callback
	 * @return bool
	 */
	this.has = function( callback ){
		return ( this.callbacks[ this.key(callback) ] instanceof this.Callback );
	};

	/**
	 * get a callback object from the stack
	 * @param string|function callback
	 * @return this.Callback
	 */
	this.get = function( callback ){

		// callback doesn't exist ? ...
		if ( ! this.has(callback) ) {

			// create from function ...
			if ( callback instanceof Function ) this.add(callback);

			// create from key w/generic function ...
			else this.add(function( value ){ return value; }, callback);

		}

		return this.callbacks[ this.key(callback) ];
	};

	/**
	 * Execute a value against the callback stack ...
	 * @param value
	 *	- if this parameter does not exist it will be considered null
	 * @param ...
	 *	- any number of additional arguments
	 * @return mixed
	 */
	this.exec = function(){

		// count the call ...
		this.calls++;

		var
		// get callback objects ...
		callbacks = Object.values(this.callbacks),

		// get arguments ...
		args = Function.args(arguments);

		// default at least one null argument ...
		if ( args.length < 1 ) args.push(null);

		return (
			// has callbacks ? ...
			callbacks.length
			// sort, reduce and return value from callbacks ...
			? callbacks.sort(function( a, b ){
				return (
					a.order == b.order
					? 0
					: (
						a.order > b.order
						? 1
						: -1
					)
				);
			}).reduce(function( callback ){
				try {
					return args[0] = callback.exec( args );
				} catch ( err ) {
					console.error(err);
					return args[0];
				}
			}, callbacks[0] )
			// return value from first argument ...
			: args[0]
		);

	};

	return this;

};

/**
 * hook: $tratus.Hook, ...
 * @var object
 */
$tratus.___hooks = [];

/**
 * Retrieve hook object.
 * @param string hook
 * return Hook
 */
$tratus.hook = function( hook ){
	return (
		this.___hooks[ hook ] instanceof this.Hook
		? this.___hooks[ hook ]
		: ( this.___hooks[ hook ] = new this.Hook(hook) )
	);
}

/**
 * Retrieve or apply a filter hook.
 * @param string hook
 * @param mixed value
 *	- this or additional parameters present then the hook will execute against them
 * @return Hook|mixed
 */
$tratus.filter = function( hook ) {
	hook = this.hook( 'filter:'.concat(hook) );
	return (
		arguments.length > 1
		? hook.exec.apply(hook, Function.args(arguments).slice(1))
		: hook
	);
}

/**
 * Retrieve an action hook.
 * @param hook
 * @return Hook
 */
$tratus.action = function( hook ){
	return this.hook( 'action:'.concat(hook) );
}

/**
 * Trigger an action hook.
 * @param hook
 * @param ...
 *	- additional parameters to pass the hook callbacks
 * @return int
 * 	- number of times the action hook has been called
 */
$tratus.trigger = function( hook ){
	hook = this.action( hook );
	void (
		arguments.length == 1
		? hook.exec()
		: hook.exec.apply(hook, Function.args(arguments).slice(1))
	);
	return hook.calls;
}
