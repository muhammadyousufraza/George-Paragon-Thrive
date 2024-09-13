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
<div id="tve-thrive_dynamic_list-component" class="tve-component" data-view="DynamicList">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Dynamic Styled List', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center mb-10 mr-5 ml-5">
			<button class="tve-button orange click" data-fn="editListElements"><?php echo __( 'Edit List Items', 'thrive-theme' ); ?></button>
			<button class="tve-button grey click margin-left-20" data-fn="filterList"><?php echo __( 'Filter List', 'thrive-theme' ); ?></button>
		</div>
		<div class="tve-control mb-5" data-view="Limit"></div>
		<div class="tve-control mb-5" data-view="ListLayout"></div>
		<div class="tve-control mb-5" data-view="EnableIcons"></div>
		<div class="tve-control tcb-text-center mb-5" style="display: none;" data-view="ModalPicker"></div>
		<div class="tve-control" data-view="ListSpacing"></div>
	</div>
</div>
