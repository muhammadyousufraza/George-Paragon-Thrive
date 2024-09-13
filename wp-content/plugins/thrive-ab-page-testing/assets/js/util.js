(function ( $ ) {

	module.exports = {
		ajax: {
			get_url: function ( query_string ) {

				var _q = this.url.indexOf( '?' ) !== - 1 ? '&' : '?';

				if ( ! query_string || ! query_string.length ) {
					return this.url + _q + '_nonce=' + this.nonce;
				}

				query_string = query_string.replace( /^(\?|&)/, '' );
				query_string += '&nonce=' + this.nonce;

				return this.url + _q + query_string;
			},
			data: function ( custom_action, type, extra_data, data_type ) {
				return {
					url: this.url,
					dataType: typeof data_type === 'undefined' ? 'json' : data_type,
					type: type || 'get',
					data: _.extend( {
						action: this.action,
						custom: custom_action,
						nonce: this.nonce
					}, extra_data || {} ),
					error: function ( jqXHR, textStatus, errorThrown ) {
						if ( typeof jqXHR.tcb_error === 'function' && jqXHR.tcb_error.apply( jqXHR, arguments ) === false ) {
							return;
						}

						TVE_Dash.hideLoader();

						if ( jqXHR.responseText ) {
							try {
								var response = JSON.parse( jqXHR.responseText );
								TVE_Dash.err( response.message );
							} catch ( e ) {
								TVE_Dash.err( jqXHR.responseText );
							}
							return;
						}
						if ( ! errorThrown ) {
							errorThrown = 'An unexpected error occurred. ' + ( jqXHR.status ? ' (Status code: ' + jqXHR.status + ')' : '' );
						} else {
							errorThrown = 'Unexpected error: ' + ( jqXHR.status ? jqXHR.status + ': ' : '' ) + errorThrown;
						}
						// finally just the error text
						TVE_Dash.err( errorThrown );
					}
				}
			},
			do: function ( action, type, extra_data, data_type ) {
				return $.ajax( this.data( action, type, extra_data, data_type ) );
			}
		}
	};
})( jQuery );
