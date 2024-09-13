<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/** @var $data TCB_Editor */
?>

<!-- used for palette colors in sidebar preview-->
<style id="tve-elem-palettes"></style>
<div id="tve_cpanel" class="state-default">
	<div class="tve-cpanel-top-content">
		<?php do_action( 'tcb_cpanel_top_content' ); ?>
	</div>
	<div class="tve-active-element no-states" data-default="<?php echo esc_html__( 'No Element Selected', 'thrive-cb' ); ?>">
		<div class="element-name-info-wrapper">
			<div class="element-name"><?php echo esc_html__( 'No Element Selected', 'thrive-cb' ); ?></div>
			<span class="element-info click"></span>
		</div>
		<div class="element-states"></div>
		<div id="multiple-selection-settings" style="display:none;">
			<button class="tve-button active click" data-type="move" data-fn="toggleMultipleSelectMode">
				<?php tcb_icon( 'move' ); ?>
				<span><?php echo esc_html__( 'Moving', 'thrive-cb' ); ?></span>
			</button>
			<button class="tve-button click" data-type="style" data-fn="toggleMultipleSelectMode">
				<?php tcb_icon( 'brush' ); ?>
				<span><?php echo esc_html__( 'Styling', 'thrive-cb' ); ?></span>
			</button>
		</div>
	</div>

	<div id="tve-scroll-panel" class="trigger">
		<div class="tve-panel">
			<?php /* information at the top of components that we are currently in Edit Mode */ ?>
			<div id="component-edit-mode" class="info-text grey-text orange border white-bg bigger m-5 tcb-hide">
				<?php echo sprintf( esc_html__( 'You are now in Edit Mode. When finished, press "DONE" in the bottom bar', 'thrive-cb' ) ); ?>
			</div>
			<div id="multiple-selection-notice" class="tcb-hide multi-select-sidebar-message">
				<?php echo sprintf( esc_html__( 'Multiple selection mode activated.', 'thrive-cb' ) ); ?>
				<br>
				<?php echo sprintf( esc_html__( 'You can now %s the selected elements across the page', 'thrive-cb' ), '<span class="selection-action">move</span>' ); ?>
			</div>

			<button id="group-moving-exit" data-fn="exit_multiple_selected_mode" class="tcb-hide multi-select-sidebar-message click">
				<?php echo sprintf( esc_html__( 'Exit group moving', 'thrive-cb' ) ); ?>
			</button>

			<div id="component-jump-mode" class="tcb-hide tcb-flex">
				<p class="info-text grey-text orange border white-bg bigger m-5"><?php echo esc_html__( 'Select an element as your target and click "Add jumplink" when you are done.', 'thrive-cb' ); ?></p>
				<img id="tve-jumplink-helper" src="<?php echo esc_url( tve_editor_css( 'images/jumplink-helper.png' ) ); ?>" width="179" height="135" srcset="<?php echo esc_url( tve_editor_css( 'images/jumplink-helper@2x.png' ) ); ?> 2x">
			</div>
			<div class="sidebar-block default-text default-visible">
				<?php $file = apply_filters( 'tcb_editor_default_sidebar', TVE_TCB_ROOT_PATH . '/inc/views/sidebar-default.php' ); ?>
				<?php if ( is_file( $file ) ) : ?>
					<?php require_once $file; ?>
				<?php endif; ?>
			</div>
			<div id="tve-components" class="tcb-flex sidebar-block" style="display: none">
				<div id="tcb-drop-panels"></div>
				<?php $data->elements->output_components(); ?>
			</div>

			<?php /* custom sidebar states for elements */ ?>
			<?php foreach ( $data->elements->custom_sidebars() as $key => $element_sidebar ) : ?>
				<div class="sidebar-block" style="display: none" id="sidebar-<?php echo esc_attr( $key ); ?>"
					 data-title="<?php echo esc_attr( $element_sidebar['title'] ); ?>"><?php echo $element_sidebar['template']; ?></div> <?php // phpcs:ignore ?>
			<?php endforeach ?>
		</div>
	</div>

	<div class="tve-settings tcb-relative" id="tcb-editor-settings"><?php tcb_template( 'editor-settings', $data ); ?></div>

	<a href="javascript:void(0)" class="panel-extend click" data-fn="togglePanel" data-title-collapsed="<?php echo esc_attr__( 'Expand panel', 'thrive-cb' ); ?>"
	   data-title-expanded="<?php echo esc_attr__( 'Collapse panel', 'thrive-cb' ); ?>" title="<?php echo esc_attr__( 'Collapse panel', 'thrive-cb' ); ?>">
		<?php tcb_icon( 'caret-left-solid' ); ?>
	</a>
</div>
