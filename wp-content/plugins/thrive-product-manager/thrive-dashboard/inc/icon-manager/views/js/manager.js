( function ( $ ) {

	$( function () {
		const $upload = $( '#tve_icon_pack_upload' ),
			$remove = $( '#tve_icon_pack_remove' ),
			$input = $( '#tve_icon_pack_file' ),
			$inputId = $( '#tve_icon_pack_file_id' );
		let wpFileFrame;

		$input.on( 'click', () => {
			$upload.click();
		} );

		$upload.on( 'click', e => {
			e.preventDefault();
			if ( ! wpFileFrame ) {

				wpFileFrame = wp.media.frames.file_frame = wp.media( {
					title: 'Upload IcoMoon Font Pack',
					button: {
						text: 'Use file'
					},
					multiple: false
				} );

				/* Add class so we can add custom CSS */
				wpFileFrame.on( 'open', function () {
					wpFileFrame.$el.addClass( 'retina-icon-picker' )
				} );

				wpFileFrame.on( 'select', function () {
					const attachment = wpFileFrame.state().get( 'selection' ).first().toJSON();
					$inputId.val( attachment.id );
					$input.val( attachment.filename );
				} );
			}

			wpFileFrame.open();

			return false;
		} );

		$remove.on( 'click', function () {
			$input.val( '' );
			$inputId.val( '' );
		} );

		const $redirectTo = $( '#tve-redirect-to' ),
			$redirectIn = $( '#tve-redirect-count' );

		if ( $redirectIn.length && $redirectTo.length ) {
			const interval = setInterval( function () {
				let _current = parseInt( $redirectIn.text() );
				_current --;
				$redirectIn.html( _current + '' );
				if ( _current === 0 ) {
					clearInterval( interval );
					location.href = $redirectTo.val();
				}
			}, 1000 );
		}

	} );

} )( jQuery );
