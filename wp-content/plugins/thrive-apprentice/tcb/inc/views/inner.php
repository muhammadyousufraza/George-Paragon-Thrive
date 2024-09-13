<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/**
 * Stuff to be included in the inner frame
 */
?>
<div id="tcb-inner-actions">
	<div id="tcb-table-panel">
		<div class="tcb-btn-row above-element">
			<button class="tcb-table-btn" data-fn="addColumn"><?php tcb_icon( 'add', false, 'editor' ) ?><?php echo esc_html__( 'Add column', 'thrive-cb' ) ?></button>
			<button class="tcb-table-btn" data-fn="addRow"><?php tcb_icon( 'add', false, 'editor' ) ?><?php echo esc_html__( 'Add row', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one" data-fn="split"><?php tcb_icon( 'split', false, 'editor' ) ?><?php echo esc_html__( 'Split', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-more" data-fn="merge"><?php tcb_icon( 'merge', false, 'editor' ) ?><?php echo esc_html__( 'Merge', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one move-left" data-fn="moveLeft"><?php echo esc_html__( 'Move column left', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one move-right" data-fn="moveRight"><?php echo esc_html__( 'Move column right', 'thrive-cb' ) ?></button>
			<div class="tcb-panel-right">
				<button disabled class="tcb-table-btn tcb-btn-red m-disable m-enable-one"
						data-fn="removeColumn"><?php tcb_icon( 'delete', false, 'editor' ) ?><?php echo esc_html__( 'Remove column', 'thrive-cb' ) ?></button>
				<button disabled class="tcb-table-btn tcb-btn-red m-disable m-enable-one"
						data-fn="removeRow"><?php tcb_icon( 'delete', false, 'editor' ) ?><?php echo esc_html__( 'Remove row', 'thrive-cb' ) ?></button>
			</div>
		</div>
		<div class="tcb-btn-row below-element">
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="insertColumn" data-arg="after"><?php echo esc_html__( 'Insert column after', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="insertColumn" data-arg="before"><?php echo esc_html__( 'Insert column before', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="insertRow" data-arg="after"><?php echo esc_html__( 'Insert row after', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="insertRow" data-arg="before"><?php echo esc_html__( 'Insert row before', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="cloneColumn" data-arg="before"><?php echo esc_html__( 'Clone column', 'thrive-cb' ) ?></button>
			<button disabled class="tcb-table-btn m-disable m-enable-one m-enable-more" data-fn="cloneRow" data-arg="before"><?php echo esc_html__( 'Clone row', 'thrive-cb' ) ?></button>
			<div class="tcb-panel-right">
				<button class="tcb-table-btn tcb-btn-green" data-fn="cancel"><?php echo esc_html__( 'Close', 'thrive-cb' ) ?></button>
			</div>
		</div>
	</div>
	<img src="<?php echo esc_url( tve_editor_css( 'images/drag-img.png' ) ); ?>" width="20" height="20" id="tcb-drag-img">
</div>
