<script type="text/javascript">
	( function ( $ ) {
		var $window = $( window ),
			trigger_elements = function ( elements ) {
				elements.each( function () {
					var $elem = $( this ),
						lb_content = $elem.parents( '.tve_p_lb_content' ),
						ajax_content = $elem.parents( '.ajax-content' ),
						inViewport = TCB_Front.isInViewport( $elem ) || isOutsideBody( $elem ) || isAtTheBottomOfThePage( $elem );

					if ( lb_content.length ) {
						lb_content.on( 'tve.lightbox-open', function () {
							if ( ! $elem.hasClass( 'tve-viewport-triggered' ) ) {
								$elem.trigger( 'tve-viewport' ).addClass( 'tve-viewport-triggered' );
							}
						} );
						return;
					}
					if ( ajax_content.length ) {
						ajax_content.on( 'content-inserted.tcb', function () {
							if ( inViewport && ! $elem.hasClass( 'tve-viewport-triggered' ) ) {
								$elem.trigger( 'tve-viewport' ).addClass( 'tve-viewport-triggered' );
							}
						} );
						return;
					}

					if ( inViewport ) {
						$elem.trigger( 'tve-viewport' ).addClass( 'tve-viewport-triggered' );
					}
				} );
			},
			trigger_exit = function ( elements ) {
				elements.each( function () {
					var $elem = $( this );

					if ( ! ( TCB_Front.isInViewport( $elem ) || isOutsideBody( $elem ) ) ) {
						$elem.trigger( 'tve-viewport-leave' ).removeClass( 'tve-viewport-triggered' );
					}
				} );
			},
			/**
			 * Returns true if the element is located at the bottom of the page and the element is in viewport
			 */
			isAtTheBottomOfThePage = function ( $elem ) {
				return TCB_Front.isInViewport( $elem, 0 ) && $window.scrollTop() >= parseInt( $elem.offset().top + $elem.outerHeight() - window.innerHeight );
			},
			/**
			 * Check if element is always outside of the viewport, is above the top scroll
			 * @param element
			 * @returns {boolean}
			 */
			isOutsideBody = function ( element ) {
				if ( element.jquery ) {
					element = element[ 0 ];
				}

				var rect = element.getBoundingClientRect();

				/* we've scrolled maximum to the top, but the element is above */
				return window.scrollY + rect.bottom < 0;

				/* leaving this commented, can be added if more bugs appear. it checks for bottom elements
				var $window = ThriveGlobal.$j( window ),
					scrolledToBottom = $window.scrollTop() + $window.height() === ThriveGlobal.$j( document ).height();

				return ( scrolledToBottom && rect.top > ( window.innerHeight - delta ) );

				 */
			};
		$( document ).ready( function () {
			window.tar_trigger_viewport = trigger_elements;
			window.tar_trigger_exit_viewport = trigger_exit;

			let $to_test = $( '.tve_et_tve-viewport' ).removeClass('tve-viewport-triggered');
			$window.scroll( function () {
				$to_test = $( '.tve_et_tve-viewport' );
				trigger_elements( $to_test.filter( ':not(.tve-viewport-triggered)' ) );
				trigger_exit( $to_test.filter( '.tve-viewport-triggered' ) );

			} );
			setTimeout( function () {
				trigger_elements( $to_test.filter( ':not(.tve-viewport-triggered)' ) );
			}, 200 );
		} );
	} )
	( jQuery );
</script>
