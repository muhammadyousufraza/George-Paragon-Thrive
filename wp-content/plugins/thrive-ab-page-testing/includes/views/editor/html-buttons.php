<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<a class="sidebar-item" id="thrive-ab-create-test"
   href="<?php echo $this->get_dashboard_url(); ?>"
   data-position="left"
   data-tooltip="<?php echo ( ! thrive_ab()->license_activated() ) ? __( 'Click here to activate Thrive Optimize before creating a new A/B test.', 'thrive-ab-page-testing' ) : __( 'Create New Test', 'thrive-ab-page-testing' ); ?>">
	<?php tcb_icon( 'test' ); ?>
</a>