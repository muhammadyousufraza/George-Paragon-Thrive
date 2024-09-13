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
<div class="tvd-switch">
	<label>
			<span class="thrive-ab-switch">
				<?php echo __( 'One page for all variations', 'thrive-ab-page-testing' ) ?>
			</span>
		<input id="multi-page" type="checkbox" class="change" data-fn="switcher_changed">
		<span class="tvd-lever">
				<i></i>
				<i></i>
			</span>
		<span class="thrive-ab-switch thrive-ab-inactive">
				<?php echo __( 'Use separate pages for variations', 'thrive-ab-page-testing' ) ?>
			</span>
	</label>
</div>

