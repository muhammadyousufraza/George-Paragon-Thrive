<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
$attributes = tcb_editor()->lcns_attributes();
?>
<div class="tve-license-modal-content grace-period">
	<img class="tve-license-icon" src="<?php echo TVE_DASH_URL ?>/css/images/licensing/licensing-<?= $attributes['source'] ?>-grace-period@2x.webp" alt="License icon"/>
	<div>
		<h3>
			<?php echo sprintf( esc_html( __( 'Heads up! Your %s license has expired', 'thrive-cb' ) ), $attributes['product'] ); ?>
		</h3>
		<p><?php _e( "An active license is needed to access your software or edit your content. You’ll also get access to new features, updates, security improvements, templates and support. Your visitors will still be able to access any content you've created", 'thrive-cb' ); ?></p>

		<p>
			<?php
			echo sprintf(
				esc_html__( 'You may close this lightbox and continue using your software during your grace period for another %s.', 'thrive-cb' ),
				 '<strong>' . TD_TTW_User_Licenses::get_instance()->get_grace_period_left( $attributes['product'] ) . __( ' days', 'thrive-cb' ) . '</strong>'
			)
			?>
		</p>

		<p>
			<?php echo __( "Doesn't sound right? Your license might need to be refreshed.", 'thrive-cb' ); ?>
			<a href="<?php echo TD_TTW_User_Licenses::get_instance()->get_recheck_url(); ?>">
				<?php echo __( 'Click here to refresh your license now.', 'thrive-cb' ) ?>
			</a>
		</p>

		<div class="tve-license-buttons">
			<button class="tve-button-empty" onclick="window.open('https://help.thrivethemes.com/en/articles/8223498-what-happens-when-your-thrive-product-license-expires', '_blank')"><?php _e( 'Learn more', 'thrive-cb' ); ?></button>
			<button class="tve-button-action" onclick="window.open('<?php echo $attributes['link'] ?>', '_blank')"><?php _e( 'Renew now', 'thrive-cb' ); ?></button>
		</div>
	</div>
</div>
