<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-filter_dynamic_list-component" class="tve-component" data-view="FilterList">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Dynamic Styled List', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mb-5" data-view="EnableIcons"></div>
		<div class="tve-control tcb-text-center mb-5" style="display: none;" data-view="ModalPicker"></div>
	</div>
</div>
