class Utils {
	/**
	 * Toggle the loading animation
	 *
	 * @param  $element
	 * @param  loading
	 */
	static toggleLoading( $element, loading = true ) {
		$element.toggleClass( 'loading', loading );
	}

	/**
	 * Toggle the loading animation + text
	 *
	 * @param  $container
	 * @param  toToggle
	 * @param  toHide
	 * @param  loading
	 */
	static toggleLoadingSpinner( $container, toToggle, toHide, loading = false ) {
		this[ ( loading ? 'show' : 'hide' ) + 'LoadingSpinner' ]( $container, toToggle, toHide );
	}
	static showLoadingSpinner( $container, toToggle, toHide ) {
		$container.find( '.loading-spinner-container' ).remove(); /* make sure there are no previous loaders displayed */
		$container.append( TVO_Front.tpl( 'loading-spinner' )( {
			text: 'Looking for matching testimonials',
		} ) );

		if ( toHide.length > 0 ) {
			toToggle = toToggle + ', ' + toHide;
		}

		$container.find( toToggle ).hide();
	}

	static hideLoadingSpinner( $container, toToggle ) {
		$container.find( '.loading-spinner-container' ).remove();
		$container.find( toToggle ).show();
	}

	/**
	 * @param {Array} array
	 * @return {string} json
	 */
	static deepCopy( array ) {
		return JSON.parse( JSON.stringify( array ) );
	}

	static getDefaultOrderingOptions( isStatic ) {
		return {
			type: isStatic ? 'manual' : 'date',
			direction: 'DESC',
			number_of_items: 3,
			offset: 0,
		};
	}
}

module.exports = Utils;
