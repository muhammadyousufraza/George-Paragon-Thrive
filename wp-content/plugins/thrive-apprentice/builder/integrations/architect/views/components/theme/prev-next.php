<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>
<div id="tve-thrive_prev_next-component" class="tve-component" data-view="PrevNext" data-ct="Default Template">
	<div class="dropdown-header component-name" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-theme' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="mb-10 row tcb-text-center">
			<div class="col-xs-12">
				<button class="tve-button author-box-edit-mode orange click" data-fn="editMode">
					<?php echo __( 'Edit Design', 'thrive-theme' ); ?>
				</button>
			</div>
		</div>
		<div class="control-grid">
			<div class="label"><?php echo __( 'Alignment', 'thrive-theme' ); ?></div>
		</div>
		<div class="tve-control pt-5 gl-st-button-toggle-2" data-key="Align" data-extends="ButtonGroup"></div>
		<div class="tve-control" data-view="BoxWidth"></div>
		<div class="tve-control" data-view="Size"></div>
		<div class="tve-control" data-view="NewTab"></div>
		<div class="tve-control" data-view="SameCategory"></div>
	</div>
</div>

