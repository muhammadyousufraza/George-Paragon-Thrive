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
		<div class="tvd-col tvd-s3"><?php echo __( 'Variation Name', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s3"><?php echo __( 'Traffic', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Visitors', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Unique Visitors', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Conversions', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Conversion Rate', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Improvement', 'thrive-ab-page-testing' ) ?></div>
		<div class="tvd-col tvd-s1"><?php echo __( 'Chance to beat Original', 'thrive-ab-page-testing' ) ?></div>
	</div>
</div>
<?php include dirname( __FILE__ ) . '/table-body.php'; ?>
