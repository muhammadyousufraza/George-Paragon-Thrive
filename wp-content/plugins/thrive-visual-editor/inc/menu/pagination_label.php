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

<div id="tve-pagination_label-component" class="tve-component" data-view="PaginationLabel">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="tve-control hide-tablet hide-mobile" data-view="Format"></div>
	</div>
</div>
