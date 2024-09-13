<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
 if ( $this->messages ) : ?>
	<?php $this->render( 'messages' ); ?>
<?php endif ?>
<?php if ( empty( $this->messages['redirect'] ) ) : ?>
	<?php include TVE_DASH_PATH . '/templates/header.phtml'; ?>
	<div class="tvd-v-spacer vs-2"></div>
	<div class="dash-icon-manager-settings">
		<h3><?php echo esc_html__( "Thrive Icon Manager", 'thrive-dash' ); ?></h3>
		<p><?php echo esc_html__( "Thrive Themes integrate with IcoMoon. Here's how it works:", 'thrive-dash' ) ?></p>
		<ol>
			<li><?php echo wp_kses_post( sprintf( __( "%s to go to the IcoMoon web app and select the icons you want to use in your site", 'thrive-dash' ), '<a target="_blank" href="//icomoon.io/app/#/select">' . __( "Click here", 'thrive-dash' ) . '</a>' ) ); ?></li>
			<li><?php echo esc_html__( "Download the font file from IcoMoon to your computer", 'thrive-dash' ) ?></li>
			<li><?php echo esc_html__( "Upload the font file through the upload button below", 'thrive-dash' ) ?></li>
			<li><?php echo esc_html__( "Your icons will be available for use!", 'thrive-dash' ) ?></li>
		</ol>
		<div class="clear"></div>
		<p>&nbsp;</p>
		<h3><?php echo esc_html__( "Import Icons", 'thrive-dash' ) ?></h3>

		<?php if ( ! $this->icons ) : ?>
			<p><?php echo esc_html__( "You don't have any icons yet, use the Upload button to import a custom icon pack.", 'thrive-dash' ) ?></p>
		<?php else: ?>
			<p><?php echo esc_html__( "Your custom icon pack has been loaded. To modify your icon pack, simply upload a new file.", 'thrive-dash' ) ?></p>
		<?php endif ?>

		<?php $this->render( 'form' ) ?>

		<div class="clear"></div>
		<p>&nbsp;</p>

		<?php if ( $this->icons ) : ?>
			<?php $this->render( 'icons' ) ?>
		<?php endif ?>

		<div class="tvd-row" style="margin-top: 10px;">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=tve_dash_section' ) ); ?>" class="tvd-waves-effect tvd-waves-light tvd-btn-small tvd-btn-gray">
				<?php echo esc_html__( "Back To Dashboard", 'thrive-dash' ); ?>
			</a>
		</div>
	</div>
<?php endif ?>
