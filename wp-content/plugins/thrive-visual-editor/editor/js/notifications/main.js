( $ => {
	$( window ).on( 'tcb.register-hooks', () => {
		_.each( require( './filters' ), ( callback, tag ) => {
			TVE.add_filter( tag, callback );
		} );

		/* register all actions */
		_.each( require( './actions' ), ( callback, tag ) => {
			TVE.add_action( tag, callback );
		} );
	} );
} )( jQuery );
