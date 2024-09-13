( function ( $ ) {

	$( function () {
		var $div = $( '.theme-overlay' ),
			html = $div.html();


		setInterval( function () {
			var newhtml = $div.html();

			if ( html !== newhtml ) {
				checkForThriveTheme();
				html = newhtml;
			}
		}, 500 );

		if ( $( '.theme-overlay .theme-name' ).text().includes( TVD_STABLE_THEME.name ) ) {
			checkForThriveTheme();
		}

		/**
		 * Checks if the active theme is ThriveTheme
		 */
		function checkForThriveTheme() {
			if ( tvdIsThriveTheme() && $( '.tvd-switch-stable-theme' ).length === 0 ) {
				$( '.theme-wrap' ).find( '.theme-actions' ).append( TVD_STABLE_THEME.link_html );
			}
		}

		/**
		 * Checks if the theme in the lightbox is the thrive-theme
		 *
		 * @returns {boolean}
		 */
		function tvdIsThriveTheme() {
			return location.search.includes( 'thrive-theme' );
		}
	} );
} )( jQuery );
