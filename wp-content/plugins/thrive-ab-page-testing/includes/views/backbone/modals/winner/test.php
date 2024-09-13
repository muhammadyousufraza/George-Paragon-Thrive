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
<div class="thrive-ab-test-header">
	<div class="tvd-row">
		<div class="tvd-col tvd-s2"><?php echo __( 'Variation Name', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Content Views', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Engagements', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s2"><?php echo __( 'Engagement Rate', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s2"><?php echo __( 'Percentage Improvement', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s2"><?php echo __( 'Chance to beat Original', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s2">&nbsp;</div>
	</div>
</div>
<div class="thrive-ab-test-items"></div>
