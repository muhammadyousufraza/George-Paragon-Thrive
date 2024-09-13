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
<a id="thrive-ab-create-test" class="sidebar-item" data-position="left" data-tooltip="<?php echo __( 'Test Dashboard', 'thrive-ab-page-testing' ); ?>" title="<?php echo $this->_post->post_title ?>" href="<?php echo $this->get_dashboard_url(); ?>">
	<?php tcb_icon( 'test' ); ?>
</a>
