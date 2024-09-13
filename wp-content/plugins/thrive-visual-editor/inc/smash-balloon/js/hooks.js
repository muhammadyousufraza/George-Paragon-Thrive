( $ => {
	$( window ).on( 'tcb.register-hooks', function () {
		TVE.add_action( 'tcb-ready', () => {
			/* tell the editor what html it should use for our element */
			TVE.renderers[ 'smash-balloon' ] = {
				render_default: () => TVE.inner_$( '#tve-smash-balloon-element' ).html()
			};
		} );

		TVE.add_filter( 'tcb_filter_html_before_save', $filter => {
			const sbElements = $filter.find( '.tcb-smash-balloon' );

			if ( 0 < sbElements.length ) {
				_.each( sbElements, sbElement => {
					const element = TVE.inner_$( sbElement );
					const elType = element.attr( 'data-type' );
					const elFeed = element.attr( 'data-feed' );

					if ( '' !== elType && '' !== elFeed ) {
						element.find( '.tve-smash-balloon' ).html( `[${ elType } feed='${ elFeed }']` );
					}
				} );
			}

			return $filter;
		} );
	} );
} )( jQuery );
