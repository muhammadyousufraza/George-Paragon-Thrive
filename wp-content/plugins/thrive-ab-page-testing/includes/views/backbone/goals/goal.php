<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$icon  = isset( $icon ) ? $icon : '';
$name  = isset( $name ) ? $name : '';
$goal  = isset( $goal ) ? $goal : '';
?>
<div class="tvd-col tvd-s4 thrive-ab-goal" data-goal="<?php echo $goal ?>">
	<div class="thrive-ab-goal-card">
		<div class="thrive-ab-goal-icon">
			<span>
				<?php echo $icon ?>
			</span>
		</div>
		<p><?php echo $name; ?></p>
	</div>
</div>
