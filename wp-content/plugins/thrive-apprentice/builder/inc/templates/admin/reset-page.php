<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<style>
    .ttb-container {
        margin: 24px auto;
        width: 680px;
        box-sizing: border-box;
        padding: 25px 90px 35px;
        background: white;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        position: relative;
    }

    .ttb-container h1 {
        margin: 0 0 30px;
    }

    .wp-core-ui .ttb-container .button {
        color: #fff;
        background-color: #cf2a27;
        border: none;
    }

    .ttb-center {
        text-align: center;
    }

    .ttb-mb30 {
        margin-bottom: 30px;
    }
</style>

<div class="ttb-container theme-overlay">
	<h1 class="ttb-center"><?php echo __( 'Reset your Theme', 'thrive-theme' ); ?></h1>
	<p><?php echo __( 'Use the button below to reset your theme to its default state.', 'thrive-theme' ); ?></p>
	<p class="ttb-mb30"><strong><?php echo __( "Warning: Resetting the theme will remove all custom templates that you've created and cannot be undone!", 'thrive-theme' ); ?></strong></p>

	<p class="ttb-center ttb-mb30"><strong><?php echo __( 'Are you sure you want to reset your theme?', 'thrive-theme' ); ?></strong></p>

	<div class="ttb-center">
		<button style="display: none;" data-action="ttb_skin_reset" class="button button-primary button-large ttb-action-button">
			<?php echo __( 'Yes, I want to Reset the Theme', 'thrive-theme' ); ?>
		</button>
		<button data-action="ttb_factory_reset" class="button ttb-action-button delete-theme">
			<?php echo __( 'Remove all data from the theme builder', 'thrive-theme' ); ?>
		</button>
	</div>
</div>

<script type="text/javascript">
	( function ( $ ) {
		$( '.ttb-action-button' ).click( function () {
			$( this ).css( 'opacity', 0.3 );

			$.ajax( {
					url: ajaxurl,
					type: 'post',
					data: {
						action: this.dataset.action
					}
				}
			).success( () => $( this ).css( {'opacity': 1, 'background-color': 'green'} ).text( 'Done - reset again?' )
			).always( response => console.warn( response ) )
		} );
	} )( jQuery )
</script>
