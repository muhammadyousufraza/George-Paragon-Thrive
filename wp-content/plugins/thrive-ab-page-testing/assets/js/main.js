(function ( $ ) {

	//DOM ready
	$( function () {

		var ThriveAB = window.ThriveAB || {},
			util = require( './util' ),
			router = require( './router' );

		_.extendOwn( ThriveAB.ajax, util.ajax );

		Backbone.emulateHTTP = true;

		ThriveAB.router = new router();
		Backbone.history.stop();
		Backbone.history.start();

		if ( ! Backbone.history.fragment ) {

			if ( ThriveAB.current_test ) {
				return ThriveAB.router.navigate( '#test', {trigger: true} );
			}

			if ( ThriveAB.running_test ) {
				ThriveAB.router.navigate( '#test/' + ThriveAB.running_test.id, {trigger: true} );
			} else {
				ThriveAB.router.navigate( '#dashboard', {trigger: true} );
			}
		}
	} );

})( jQuery );
