( function ( $ ) {

	function triggerResize( width ) {
		if ( parent && parent.TVE_LS_API ) {
			parent.TVE_LS_API.resizeFrame( width );
		}
	}

	document.addEventListener( 'lightspeed_data.tcb', function ( event ) {
		var sizes = {
				m: 600,
				t: 1023,
				d: 1366
			},
			$container = $( '.tve_editor_main_content' ),
			formHeight = {};

		if ( $container.length === 0 ) {
			$container = $( '#tve_editor' );
		}

		/* fox for margin collapse */
		$container.css( 'border', '1px solid transparent' );

		/* save form height so we can prepare a placeholder for it. */
		for ( var device in sizes ) {
			if ( sizes.hasOwnProperty( device ) ) {
				triggerResize( sizes[ device ] );
				formHeight[ device ] = $container.outerHeight( true );
			}
		}

		$container.css( 'border', '' );

		event.detail[ 'form-height' ] = formHeight;
	} );

} )( jQuery )
