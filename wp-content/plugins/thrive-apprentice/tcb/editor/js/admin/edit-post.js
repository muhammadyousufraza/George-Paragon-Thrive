( $ => {
	const TCBYoastPlugin = require( './classes/tcb-yoast-plugin' ),
		TCBRankMathPlugin = require( './classes/tcb-rankmath-plugin' ),
		RankMathInstance = new TCBRankMathPlugin(),
		YoastInstance = new TCBYoastPlugin();

	window.TCBYoastPlugin = TCBYoastPlugin;

	/**
	 * YoastSEO content analysis integration
	 */
	$( window ).on( 'YoastSEO:ready', () => {
		YoastInstance.init();
	} );

	/**
	 * RankMath content analysis integration
	 */
	$( document ).ready( function () {
		if ( typeof window.rankMath !== 'undefined' ) {
			RankMathInstance.init();
		}
	} );

	/**
	 */
	function show_loader() {
		$( '#tcb-admin-page-loader' ).show();
	}

	$( function () {
		const $document = $( document );

		$document.on( 'click.tcb', '#tcb2-migrate-post', ( index, element ) => {
			show_loader();

			$.ajax( {
				type: 'post',
				url: ajaxurl,
				dataType: 'json',
				data: {
					_nonce: TCB_Post_Edit_Data.admin_nonce,
					post_id: TCB_Post_Edit_Data.post_id,
					action: 'tcb_admin_ajax_controller',
					route: 'migrate_post_content'
				}
			} ).done( function () {
				location.href = element.getAttribute( 'data-edit' );
			} ).fail( function ( jqXHR ) {
				alert( 'ERROR: ' + jqXHR.responseText );
			} );
		} )
		         .on( 'click', '#tcb2-show-wp-editor', function () {
			         /**
												 * Enable the hidden input that will disable TCB editor when saving the post
												 */
			         const $editlink = $document.find( '.tcb-enable-editor' ),
				         $postbox = $editlink.closest( '.postbox' );

			         $postbox.next( '.tcb-flags' ).find( 'input' ).prop( 'disabled', false );
			         $postbox.before( $editlink );
			         $postbox.remove();
			         $( 'body' ).removeClass( 'tcb-hide-wp-editor' );
		         } )
		         .on( 'click', '.tcb-enable-editor', function () {
			         $( 'body' ).addClass( 'tcb-hide-wp-editor' );

			         $.ajax( {
				         type: 'post',
				         url: ajaxurl,
				         dataType: 'json',
				         data: {
					         _nonce: TCB_Post_Edit_Data.admin_nonce,
					         post_id: this.getAttribute( 'data-id' ),
					         action: 'tcb_admin_ajax_controller',
					         route: 'enable_tcb'
				         }
			         } ).done( function () {
				         $( window ).off( 'beforeunload.edit-post' );
				         $( 'input#save-post' ).trigger( 'click' );
			         } );
		         } );
	} );
} )( jQuery );
