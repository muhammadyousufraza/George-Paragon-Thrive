( function ( $, tcb ) {
	module.exports = {
		init () {
			if ( ! tve_frontend_options.is_editor_page ) {
				this.frontendInit();
			}
		},
		/**
		 * Init logic for the frontend
		 *
		 * @param {HTMLElement} $root of content
		 */
		frontendInit ( $root = null ) {
			$root = $root || tcb.$document;

			$root.find( '.tva-certificate-verification-element' ).each( ( index, element ) => {
				const $element = $( element );

				this.bindEvents( $element, index );
				this.displayState( 'form', $element );

				if ( tve_frontend_options.query_vars.certificate_u ) {
					$element.find( '.tva-certificate-form-input input' ).val( tve_frontend_options.query_vars.certificate_u );
					$element.find( '.tva-certificate-form .thrv-button' ).trigger( 'click' );
				}
			} );
		},
		/**
		 * Bind events for current $element
		 *
		 * @param {jQuery} $element to bind events to
		 * @param {number} index of element in page
		 */
		bindEvents ( $element, index ) {
			/**
			 * On button click send an ajax request to the server to verify the certificate
			 * - display the HTML from response on the element at index
			 */
			$element.find( '.tva-certificate-form .thrv-button' ).off( 'click' ).on( 'click', event => {

				event.preventDefault();

				const $button = $( event.currentTarget );
				const $input = $element.find( '.tva-certificate-form-input input' );

				$button.addClass( 'tar-disabled tve-loading' );

				this.searchForCertificate( $input.val() )
					.then( response => {
						this.certificate = response.certificate;
						const $validHtml = $( response.html ).find( '.tva-certificate-state[data-state="valid"]' );
						const $validState = $element.find( '.tva-certificate-state[data-state="valid"]' );
						$validState.html( $validHtml[ index ] );
						this.bindEvents( $element );
						this.displayState( 'valid', $element );
					} )
					.catch( response => {
						let errorType = 'error';
						let errorMessage = response.message;

						//for this specific error code we want to display the message set in TAr
						if ( response.code === 'no_certificate_found' ) {
							errorMessage = $element.attr( 'data-message' ) || response.message;
						}

						//for this specific error code and custom notifications
						if ( response.code === 'no_certificate_found' && TCB_Front.notificationElement.isCustomNotification ) {
							errorType = 'warning';
						}

						TCB_Front.notificationElement.toggle( errorMessage, errorType );
					} )
					.finally( () => {
						$button.removeClass( 'tar-disabled tve-loading' );
					} );
			} );
			/**
			 * On back to form click display the form state
			 */
			$element.find( '.tve-dynamic-link[data-shortcode-id="form"]' ).off( 'click' ).on( 'click', e => {
				e.preventDefault();
				this.displayState( 'form', $element );
			} );
			/**
			 * On download button click download the certificate
			 */
			$element.find( '.tve-dynamic-link[data-shortcode-id="download_certificate"]' ).off( 'click' ).on( 'click', e => {
				e.preventDefault();
				const $button = $( e.currentTarget ).parent( '.thrv-button' );
				$button.addClass( 'tar-disabled tve-loading' );
				this.downloadCertificate( $button );
			} );
		},
		downloadCertificate ( $button ) {
			const certificateTitle = this.certificate.course.name,
				$body = $( 'body' );

			$.ajax( {
				url: `${ ThriveAppFront.routes.certificate }/download`,
				type: 'POST',
				cache: false,
				data: {
					course_id: this.certificate.course.id,
					user_id: this.certificate.recipient.ID,
				},
				beforeSend ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', ThriveAppFront.nonce );
				},
				xhr () {
					const xhr = new XMLHttpRequest();
					xhr.onreadystatechange = () => {
						if ( xhr.readyState === 2 ) {
							xhr.responseType = xhr.status === 200 ? 'blob' : 'text';
						}
					};
					return xhr;
				},
				success ( data ) {
					//Convert the Byte Data to BLOB object.
					const blob = new Blob( [ data ], { type: 'application/octetstream' } ),
						url = window.URL || window.webkitURL;

					const $a = $( '<a />' ).attr( {
						'href': url.createObjectURL( blob ),
						'download': `${ certificateTitle }.pdf`
					} );

					$body.append( $a );
					$a[ 0 ].click();
					$body.remove( $a );

					$button.removeClass( 'tar-disabled tve-loading' );
				},
				error ( data ) {
					let errorType = 'error';
					let errorMessage = data.responseJSON.message;

					$button.removeClass( 'tar-disabled tve-loading' );

					if ( TCB_Front.notificationElement.isCustomNotification ) {
						errorType = 'warning';
					}

					if ( data.status === 401 ) {
						errorMessage = ThriveAppFront.t.login_required;
					}

					TCB_Front.notificationElement.toggle( errorMessage, errorType );
				}
			} );
		},
		displayState ( state, $element ) {

			if ( [ 'valid', 'form' ].indexOf( state ) === - 1 ) {
				throw new Error( `Invalid state: ${ state }` );
			}

			const $states = $element.find( '.tva-certificate-state' );

			$states
				.removeClass( 'tcb-permanently-hidden' )
				.hide();

			$element.find( `.tva-certificate-state[data-state="${ state }"]` ).show();
		},
		searchForCertificate ( number ) {
			return new Promise( ( resolve, reject ) => {
				TCB_Front.Utils.restAjax( {
					type: 'GET',
					route: tve_frontend_options.routes.certificate_search,
					data: {
						number
					},
				} ).done( response => {
					resolve( response );
				} ).fail( response => {
					reject( response.responseJSON );
				} );
			} );
		}
	}
} )( ThriveGlobal.$j, TCB_Front );
