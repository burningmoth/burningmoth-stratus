/**
 * Prototypes.
 * Ammends gaps in and extends javascript functionality for $tratus methods.
 * @note should have no dependencies.
 */

/**
 * Returns arguments as an array.
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
};


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
};

