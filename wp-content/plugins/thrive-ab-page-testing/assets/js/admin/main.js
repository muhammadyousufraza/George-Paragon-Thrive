(function ( $ ) {

	//DOM ready
	$( function () {
		var ThriveAbAdmin = window.ThriveAbAdmin || {},
			router = require( './router' );


		Backbone.emulateHTTP = true;

		ThriveAbAdmin.router = new router();
		ThriveAbAdmin.router.init_breadcrumbs();

		Backbone.history.stop();
		Backbone.history.start();

		if ( ! Backbone.history.fragment ) {
			ThriveAbAdmin.router.navigate( '#dashboard', {trigger: true} );
		}
	} );
})( jQuery );
