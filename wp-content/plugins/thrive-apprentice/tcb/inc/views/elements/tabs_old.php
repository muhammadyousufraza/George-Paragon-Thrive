<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="thrv_wrapper thrv_tabs_shortcode thrv-tabbed-content">
	<div class="tve_scT tve_red">
		<ul class="tve_clearfix">
			<li class="tve_tS"><span class="tve_scTC1 thrv-inline-text tve_editable"><?php echo esc_html__( 'First tab', 'thrive-cb' ) ?></span></li>
			<li><span class="tve_scTC2 thrv-inline-text tve_editable"><?php echo esc_html__( 'Second tab', 'thrive-cb' ) ?></span></li>
			<li><span class="tve_scTC3 thrv-inline-text tve_editable"><?php echo esc_html__( 'Third tab', 'thrive-cb' ) ?></span></li>
		</ul>
		<div class="tve_scTC tve_scTC1" style="display: block"></div>
		<div class="tve_scTC tve_scTC2"></div>
		<div class="tve_scTC tve_scTC3"></div>
	</div>
</div>
