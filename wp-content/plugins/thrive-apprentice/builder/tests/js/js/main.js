'use strict';
/**
 * GLOBAL DEFINITIONS
 */

window.$ = jQuery;
window.media = {};

const Element = require( './util/element' ),
	Tests = {
		init: function () {

			require( './functional/_includes' );


			TVE.add_filter( 'tcb.selection.element', ( $element, event ) => {
				if ( event.target.classList.contains( 'theme-section' ) ) {
					$element = TVE.inner_$( event.target );
				}
				/* small trick to select the correct element */
				if ( event.target.classList.contains( 'section-content' ) ) {
					$element = TVE.inner_$( event.target.parentElement );
				}

				return $element;
			} );

			TVE.$document.off( 'heartbeat-tick.wp-auth-check' );

			_.each( TVE.main.responsive, function ( item, m ) {
				window.media[ m ] = item.media;
			} );

			TVE.UndoManager.setCallback( function () {
				window.onbeforeunload = null;
			} );

			if ( ! this.isCliRunner() ) {
				QUnit.start();
			}

		},
		isCliRunner: function () {
			return navigator.userAgent.includes( 'Puppeteer-CLI-Runner' );
		}
	};

QUnit.testAsync = QUnit.asyncTest = function ( name, callback ) {
	const delay = arguments.length > 2 && arguments[ 2 ] !== undefined ? arguments[ 2 ] : 5;

	return QUnit.test( name, function ( assert ) {
		const done = assert.async(),
			promise = callback( assert );

		if ( promise && typeof promise.then === 'function' ) {
			return promise.then( function () {
				return done();
			} );
		}
		/* default case: run after delay */
		setTimeout( function () {
			return done();
		}, delay );
	} );
};

QUnit.testWithSelectedElement = function ( testName, selector, callback ) {
	const openComponent = arguments.length > 3 && arguments[ 3 ] !== undefined ? arguments[ 3 ] : null;

	return QUnit.testAsync( testName, function ( assert ) {
		const $element = Element.getBySelector( selector );

		if ( $element.length === 0 ) {
			throw new Error( `${selector} element was not found!!!` )
		}

		return Element.select( $element, true ).then( function () {
			if ( openComponent && TVE.Components[ openComponent ] ) {
				const elementType = TVE._type( $element );

				TVE.Components[ openComponent ].open( TVE.Elements[ elementType ].components[ openComponent ], elementType, {docked: true} ).then( () => {
					callback( assert, $element );
				} )
			} else {
				callback( assert, $element );
			}
		} );
	} );
};

/** RUN TESTS **/
QUnit.config.autostart = false;

QUnit.done( function () {
	document.body.classList.remove( 'running' );
} );

jQuery( function () {
	TVE.main.on( 'tcb-ready', function () {
		Tests.init()
	} );
} );
