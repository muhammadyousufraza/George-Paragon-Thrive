( function ( $ ) {
	const $document = $( document );
	$document.on( 'heartbeat-tick.wp-auth-check', function ( e, data ) {
		if ( 'wp-auth-check' in data && ! data[ 'wp-auth-check' ] ) {
			/* when opening the auth iframe popup, add our user key field so we can identify him */
			setTimeout( function () {
				$( '#wp-auth-check-frame' ).off( 'load.tvd' ).on( 'load.tvd', function () {
					$( '#wp-auth-check-frame' ).contents().find( 'form#loginform' ).append( '<input type="hidden" name="tvd_auth_check_user_key" value="' + tvd_auth_check.userkey + '">' );
				} );
			} )
		}
	} );
	/* after login, trigger and event on the document where other plugins can listen */
	window.tvd_after_auth = function ( data ) {
		$document.trigger( 'tvd_after_auth', data );
	}
} )( jQuery );

