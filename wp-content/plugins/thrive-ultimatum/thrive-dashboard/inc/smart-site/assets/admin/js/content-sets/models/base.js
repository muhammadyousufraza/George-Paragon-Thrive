module.exports = Backbone.Model.extend( {
	/**
	 * Append WP Nonce to all requests
	 *
	 * @param {string} method
	 * @param {*} collection
	 * @param {*} options
	 */
	sync( method, collection, options ) {
		const beforeSend = options.beforeSend;
		options.beforeSend = function ( xhr ) {
			xhr.setRequestHeader( 'X-WP-Nonce', TD_SETS.nonce );
			if ( typeof beforeSend === 'function' ) {
				beforeSend.apply( this, arguments );
			}
		};
		return Backbone.Model.prototype.sync.apply( this, arguments );
	},
	/**
	 * Builds an correctly-formatted URL from baseUri (which can already contain a query string) and (optionally) a map of query string parameters
	 *
	 * @param {string} baseUri
	 * @param {Object} [params]
	 * @param {string} [pathAppend] string to be appended to the path of the url object
	 *
	 * @return {string} url
	 */
	buildUrl( baseUri, params = {}, pathAppend = '' ) {
		const url = new URL( baseUri );
		_.each( params, ( value, key ) => {
			url.searchParams.append( key, value );
		} );

		if ( pathAppend ) {
			if ( baseUri.includes( '?rest_route' ) ) { //Fixed issue with different type of permalinks
				url.searchParams.set( 'rest_route', url.searchParams.get( 'rest_route' ) + pathAppend );
			} else {
				url.pathname += pathAppend;
			}
		}

		return url.toString();
	},
	/**
	 * Wraps up an error object for later use
	 *
	 * @param {string} field
	 * @param {string} message
	 * @return {{field: *, message: *}} error object
	 */
	validation_error( field, message ) {
		return {
			field,
			message
		};
	},
	/**
	 * Gets the first error message from list if any defined
	 *
	 * @return {string} the error message
	 */
	getValidationError() {
		return this.validationError && this.validationError[ 0 ] ? this.validationError[ 0 ].message : '';
	}
} );
