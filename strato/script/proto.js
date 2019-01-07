/**
 * Prototypes.
 * Ammends gaps in and extends javascript functionality for $tratus methods.
 * @note should have no dependencies.
 */

/**
 * Returns arguments array-like object as a proper array.
 * @param Arguments args
 * @return array
 */
Function.prototype.args = function( args ){
	return Array.prototype.slice.call( args );
};

/**
 * Object.keys()
 */
if ( ! Object.keys ) Object.prototype.keys = function( obj ){
	var keys = [];
	try {
		for ( var key in obj ) keys.push( key );
	} catch ( err ) {
		console.err(err);
	}
	return keys;
}


/**
 * Object.values()
 */
if ( ! Object.values ) Object.prototype.values = function( obj ){
	var values = [];
	try {
		for ( var key in obj ) values.push( obj[key] );
	} catch ( err ) {
		console.err(err);
	}
	return values;
}

/**
 * Object.assign()
 */
if ( ! Object.assign ) Object.assign = function( target, source ){
	var
	objs = Function.args(arguments),
	target = objs.shift();
	for ( var i = 0; i < objs.length; i++ ) {
		for ( var k in objs[i] ) {
			target[k] = objs[i][k];
		}
	}
	return target;
}

/**
 * Array.find()
 */
if ( ! Array.find ) Array.prototype.find = function( callback ){
	var
	$elf = ( arguments.length > 1 ? arguments[1] : this ),
	value = undefined;
	for ( var i=0; i < $elf.length; i++ ) {
		if ( callback($elf[ i ], i, $elf) ) {
			value = $elf[i];
			break;
		}
	}
	return value;
}

/**
 * String.includes()
 */
if ( ! String.includes ) String.prototype.includes = function( str ){
	var pos = (
		arguments.length > 1
		? arguments[1]
		: 0
	);
	return (
		pos > 0
		? this.substring(pos)
		: this
	).indexOf(str) !== -1;
}

/**
 * Array.includes()
 */
if ( ! Array.includes ) Array.prototype.includes = function(  val ){
	var pos = (
		arguments.length > 1
		? arguments[1]
		: 0
	);
	return (
		pos > 0
		? this.slice(pos)
		: this
	).indexOf(val) !== -1;
}
