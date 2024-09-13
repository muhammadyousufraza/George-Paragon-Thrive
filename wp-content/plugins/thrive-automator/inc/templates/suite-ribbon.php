<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

use Thrive\Automator\Suite\TTW;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="notice notice-warning tap-suite-notice">
	<a class="tap-notice-dismiss notice-dismiss" href="?tap-notice-dismiss=<?php echo esc_attr( TTW::RIBBON ) ?>"></a>
	<img src="<?php echo esc_url( TAP_PLUGIN_URL . 'assets/images/integrations.webp' ) ?>" alt="Integrations">
	<p><?php esc_html_e( 'You haven’t enabled ', 'thrive-automator' ) ?><span><?php esc_html_e( 'Thrive Automator’s free integrations', 'thrive-automator' ) ?></span><?php esc_html_e( ', including 20+ email services, WooCommerce triggers, actions and more.', 'thrive-automator' ) ?></p>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=thrive_automator#/suite' ) ) ?>" class="button button-primary tap-go-suite"><?php esc_html_e( 'Start now', 'thrive-automator' ); ?></a>
</div>
