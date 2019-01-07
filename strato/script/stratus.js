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
 * Test whether a value can be parsed as a number.
 * @param mixed value
 * @return bool
 */
$tratus.isNumeric = function( value ){
	return !(
		Number.isNaN
		? Number.isNaN(value)
		: isNaN(value)
	);
}

/**
 * Test whether a value is boolean or not.
 * @param mixed value
 * @return bool
 */
$tratus.isBoolean = function(value){
	return ( typeof value == 'boolean' );
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

/**
 * Test whether a value is a function or not.
 * @param mixed value
 * @return bool
 */
$tratus.isFunction = function( value ){
	return ( typeof value == 'function' );
}

/**
 * Test whether a value has been defined
 * @param mixed value
 * @return bool
 */
$tratus.isDefined = function( value ){
	return (
		value !== null
		&& typeof value != 'undefined'
	);
}

/**
 * Test whether value is scalar.
 * @param mixed value
 * @return bool
 */
$tratus.isScalar = function( value ){
	return (
		this.isString(value)
		|| this.isNumber(value)
		|| this.isBoolean(value)
	);
}

/**
 * Test if a value is "binary", aka. a string containing nonprintable characters that blow up base64 and other string functions.
 * @see https://stackoverflow.com/a/1677660
 * @param mixed value
 * @return bool
 */
$tratus.isBinary = function( value ){
	return (
		this.isString(value)
		&& /[\x00-\x1F]/.test(value)
	);
}

/**
 * Test if a value is "bytes" (ArrayBuffer)
 * @param mixed value
 * @return bool
 */
$tratus.isBytes = function( value){
	return ( value instanceof ArrayBuffer );
}



/* ################## *
 * ### FORMATTING ### *
 * ################## */

/**
 * String base64 [En|De]code methods. Unicode safe.
 * @note Check for binary string, these can blow up the *URIComponent functions.
 * @see http://ecmanaut.blogspot.com/2006/07/encoding-decoding-utf8-in-javascript.html
 */
$tratus.base64Encode = function( str ) {
	if ( ! this.isBinary(str) ) str = unescape(encodeURIComponent(str));
	return window.btoa(str);
}

$tratus.base64Decode = function( str ) {
	str = window.atob(str);
	if ( ! this.isBinary(str) ) str = decodeURIComponent(escape(str));
	return str;
}

/**
 * Converting between String and 'bytes' (BufferSource)
 * @note TextEncode and TextDecode worked in some cases but not others (crypto namely)!
 * @see https://stackoverflow.com/a/21797381
 * @see https://stackoverflow.com/questions/6965107/converting-between-strings-and-arraybuffers
 */
$tratus.bytesToString = function( buf ){
	return String.fromCharCode.apply(null, new Uint8Array(buf));
}

$tratus.stringToBytes = function( str ){
    var bytes = new Uint8Array( str.length );
    for ( var i = 0; i < str.length; i++ ) bytes[i] = str.charCodeAt(i);
    return bytes;
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
					: $tratus.base64Encode(key)
				);
			}

			// base64 encode ...
			else key = $tratus.base64Encode(key);

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

		// default at least one undefined argument ...
		if ( args.length < 1 ) args.push(undefined);

		// put through ordered callbacks ...
		if ( callbacks.length ) callbacks.sort(function( a, b ){
			return (
				a.order == b.order
				? 0
				: (
					a.order > b.order
					? 1
					: -1
				)
			);
		}).forEach(function( callback ){
			args[0] = callback.exec( args );
		});

		// return first argument ...
		return args[0];

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
