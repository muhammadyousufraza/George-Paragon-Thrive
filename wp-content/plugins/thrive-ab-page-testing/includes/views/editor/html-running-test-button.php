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
<a id="thrive-ab-create-test" class="tab-started-test sidebar-item"
		href="<?php echo $test_url; ?>"
		data-position="left"
		data-tooltip="<?php echo ( ! thrive_ab()->license_activated() ) ? __( 'Click here to activate Thrive Optimize before creating a new A/B test.', 'thrive-ab-page-testing' ) : __( 'Test Dashboard', 'thrive-ab-page-testing' ) ?>">
	<?php tcb_icon( 'test', false, 'sidebar', 'test-running' ); ?>
</a>