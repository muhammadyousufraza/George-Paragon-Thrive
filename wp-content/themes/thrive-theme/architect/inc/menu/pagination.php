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
<div id="tve-pagination-component" class="tve-component" data-view="Pagination">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Pagination', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="tve-control hide-tablet hide-mobile" data-view="Type"></div>
		<div class="tve-control" data-view="PageNumbersToggle"></div>
		<div class="tve-control hide-tablet hide-mobile" data-view="PagesNearCurrent"></div>
		<div class="tve-control" data-view="PageSpacing"></div>
		<hr class="tve-pagination-separator">
		<div class="tve-control" data-view="NextPreviousToggle"></div>
		<div class="tve-control" data-view="FirstLastToggle"></div>
		<div class="tve-control" data-view="LabelToggle"></div>
		<div class="tve-control mb-10" data-view="Alignment"></div>
	</div>
</div>
