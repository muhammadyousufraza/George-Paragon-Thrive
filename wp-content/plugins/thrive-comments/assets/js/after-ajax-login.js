(function ( $ ) {
	if ( jQuery( 'body.interim-login-success' ).length ) {
		window.parent.ThriveComments.util.after_ajax_login_actions();
	}
})( jQuery );
