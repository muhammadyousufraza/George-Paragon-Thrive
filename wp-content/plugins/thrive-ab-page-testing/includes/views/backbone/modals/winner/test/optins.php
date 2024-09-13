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
		<div class="tvd-col tvd-s2"><?php echo __( 'Name', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Visitors', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Unique Visitors', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Subscriptions', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Subscription Rate', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s2"><?php echo __( 'Improvement', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s2"><?php echo __( 'Chance to beat Original', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s2">&nbsp;</div>
	</div>
</div>
<div class="thrive-ab-test-items"></div>
