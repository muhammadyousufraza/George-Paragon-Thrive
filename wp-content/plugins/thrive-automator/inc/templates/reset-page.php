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
    .tap-container {
        margin: 24px auto;
        width: 680px;
        box-sizing: border-box;
        padding: 25px 90px 35px;
        background: white;
        border: 1px solid #e5e5e5;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
        position: relative;
    }

    .tap-container h1 {
        margin: 0 0 30px;
    }

    .wp-core-ui .tap-container .button {
        color: #fff;
        background-color: #cf2a27;
        border: none;
    }

    .tap-center {
        text-align: center;
    }

    .tap-mb30 {
        margin-bottom: 30px;
    }
</style>

<div class="tap-container theme-overlay">
	<h1 class="tap-center"><?php esc_html_e( 'Reset Thrive Automator', 'thrive-automator' ); ?></h1>
	<p><?php esc_html_e( 'Use the button below to reset Thrive Automator to its default state.', 'thrive-automator' ); ?></p>
	<p class="tap-mb30"><strong><?php esc_html_e( "Warning: Resetting Thrive Automator will remove all automations that you've created and cannot be undone!", 'thrive-automator' ); ?></strong></p>

	<p class="tap-center tap-mb30"><strong><?php esc_html_e( 'Are you sure you want to reset Thrive Automator?', 'thrive-automator' ); ?></strong></p>

	<div class="tap-center">
		<button data-action="thrive_automator_reset" class="button tap-action-button delete-theme">
			<?php esc_html_e( 'Remove all data from Thrive Automator', 'thrive-automator' ); ?>
		</button>
	</div>
</div>

<script type="text/javascript">
    ( function ( $ ) {
        $( '.tap-action-button' ).click( function () {
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
