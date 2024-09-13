class Element {

	/**
	 * Set an element as selected and in edit mode
	 * @param selector
	 * @param asynchronous
	 * @returns {*}
	 */
	static select( selector, asynchronous = false ) {
		const $element = Element.getBySelector( selector );

		if ( asynchronous ) {
			const promise = TVE.Editor_Page.selection_manager.select_element( $element );

			if ( $element.hasClass( 'theme-section' ) ) {
				$element.find( '.section-content' ).trigger( 'click', {emulate: true} );
			}
			/* csf, ncsf :( ... important e ca merge. */
			if ( $element.is( '#content' ) ) {
				$element.find( '.main-content-background' ).trigger( 'click', {emulate: true} );
			}

			return promise;
		}

		TVE.Editor_Page.focus_element( $element );

		return $element;
	}

	/**
	 * Get an element by selector.
	 * @param selector
	 * @returns {{jquery}}
	 */
	static getBySelector( selector ) {

		let $element;

		if ( selector.jquery ) {
			$element = selector;
		} else {
			const map = {};

			_.each( TVE.Elements, function ( element, key ) {
				map[ key ] = element.identifier;
			} );

			if ( map[ selector ] ) {
				$element = TVE.inner_$( map[ selector ] ).first();
			} else {
				$element = TVE.inner_$( selector ).first();
			}
		}

		return $element;
	}

	/**
	 * Insert a type of element from sidebar on a specific target
	 * @param type
	 * @param $target
	 * @param attr
	 * @returns {*}
	 */
	static insert( type = '', $target = null, attr = {} ) {

		if ( ! type || typeof TVE.Elements[ type ] === 'undefined' ) {
			throw new Error( `Unknown element type: ${type}` )
		}

		if ( ! $target || ! $target.length ) {
			$target = TVE.Theme.$sections.top.find( '.section-content' );
		}

		let $element;

		if ( typeof TVE.renderers[ type ] === 'undefined' ) {
			$element = TVE.main.static_element( type ).children().first();
			$target.append( $element );
		} else {
			$element = TVE.inner_$( TVE.renderers[ type ].render_default( $target ) )
		}

		for ( const key in attr ) {
			$element.attr( key, attr[ key ] );
		}

		TVE.Theme.content.init_selectors( $target );

		return $element
	}

	/**
	 * Check if an element is visible
	 * @param selector
	 * @returns {null|boolean}
	 */
	static isVisible( selector ) {

		if ( selector.jQuery ) {
			selector = $( selector );
		}

		if ( selector.length !== 1 ) {
			return null;
		}

		let isVisible = true;

		if ( selector.is( '.control-hide' ) ) {
			isVisible = false;
		}

		isVisible = isVisible && [ 'block', 'inline', 'inline-block', 'flex', 'inline-flex' ].indexOf( selector.css( 'display' ) ) !== - 1;

		return isVisible;
	}

	/**
	 *
	 * @param {HTMLElement|jquery} element
	 * @param {String} direction
	 */
	static offset( element, direction = '' ) {
		if ( element.jquery ) {
			element = element[ 0 ]
		}

		let offset;

		switch ( direction ) {
			case 'left':
				offset = element.offsetLeft;
				break;
			case 'top':
				offset = element.offsetTop;
				break;
			case 'right':
				offset = element.offsetLeft + element.offsetWidth;
				break;
			case 'bottom':
				offset = element.offsetTop + element.offsetHeight;
				break;
			default:
				offset = false;
		}

		return offset;
	}
}

module.exports = Element;
