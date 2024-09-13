<?php
$screen  = get_current_screen();
$post_id = $post ? $post->ID : ( isset( $_GET['post_id'] ) ? $_GET['post_id'] : 0 );
?>
<html>
<body>
<form name="post" action="post.php" method="post" id="post">
	<?php wp_nonce_field( 'update-post_' . $post->ID ) ?>
	<input type="hidden" name="action" value="editpost"/>
	<input type="hidden" name="originalaction" value="editpost"/>

	<?php the_block_editor_meta_box_post_form_hidden_fields( $post ) ?>

	<div id="poststuff" class="wp-admin wp-core-ui js">

		<input style="display:none;margin-bottom: 10px" type="submit" name="save" id="publish" class="button button-primary button-large" value="Update">

		<?php do_meta_boxes( $screen->id, 'side', $post ); ?>
		<?php do_meta_boxes( $screen->id, 'normal', $post ); ?>
		<?php do_meta_boxes( $screen->id, 'column3', $post ); ?>
		<?php do_meta_boxes( $screen->id, 'column4', $post ); ?>
	</div>
</form>

<script>
	addLoadEvent = function ( func ) {
		if ( typeof jQuery !== 'undefined' ) {
			jQuery( function () {
				func();
			} );
		} else if ( typeof wpOnload !== 'function' ) {
			wpOnload = func;
		} else {
			var oldonload = wpOnload;
			wpOnload = function () {
				oldonload();
				func();
			}
		}
	};
	var ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php', 'relative' ) ); ?>',
		pagenow = '<?php echo esc_js( $screen->id ); ?>',
		typenow = '<?php echo esc_js( $screen->post_type ); ?>',
		isRtl = <?php echo (int) is_rtl(); ?>;

	window.tcbTriggerSave = function () {
		jQuery( '#publish' ).trigger( 'click' );
	};

	window.destroySortable = function () {
		setTimeout( () => {
			const $sortable = jQuery( '.meta-box-sortables' );
			if ( $sortable.length && typeof $sortable.sortable === 'function' ) {
				$sortable.sortable( 'destroy' );
			}
		}, 1000 );
	};

	window.removeExtraButtons = function () {
		jQuery( '.handle-actions' ).children( ':not(.handlediv):not(.handle-order-higher):not(.handle-order-lower)' ).remove();
	};

	window.tcbToggleMetaBoxes = function ( metaBoxIds = [] ) {
		if ( ! Array.isArray( metaBoxIds ) ) {
			metaBoxIds = [];
		}

		jQuery( `.postbox:not('.hide-if-js')` ).toggleClass( 'very-hidden', metaBoxIds.length > 0 ).addClass( 'closed' );

		metaBoxIds.forEach( metaBoxId => {
			jQuery( `#${metaBoxId}` ).removeClass( 'closed very-hidden' )
		} );
	};

	//on page load
	addLoadEvent( () => {
		removeExtraButtons();

		document.documentElement.setAttribute( 'dir', 'ltr' )

		const settingsHeader = document.querySelector( '#submitdiv .postbox-header h2' )
		if ( settingsHeader ) {
			settingsHeader.innerHTML = 'General settings';
		}

		Array.from( document.getElementsByTagName( 'a' ) ).forEach( element => {
			element.setAttribute( 'target', '_blank' )
		} )
	} );
</script>
