<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-leads
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div class="error-container"></div>
<div class="modal-sidebar">
	<div class="lp-search">
		<?php tcb_icon( 'search-regular' ); ?>
		<input class="tve-c-modal-search-input input" type="text" data-source="search" data-fn="onSearch"
		       placeholder="<?php echo esc_html__( 'Search', 'thrive-leads' ); ?>"/>
		<?php tcb_icon( 'close2', false, 'sidebar', 'click', array( 'data-fn' => 'clearSearch' ) ); ?>
	</div>

	<div class="lp-menu-wrapper">
		<div class="mt-30">
			<div class="sidebar-title">
				<p><?php echo __( 'Type', 'thrive-leads' ); ?></p>
				<span class="tcb-hl"></span>
			</div>
			<div id="tl-default-filters">
				<a href="javascript:void(0);" class="click tl-category-filter active" data-content="default" data-fn="filterClick">
					<span class="tl-filter-label"><?php echo __( 'Opt-in templates', 'thrive-leads' ); ?></span>
					<span class="tl-filter-counter"></span>
				</a>
			</div>
		</div>
		<div class="mt-30">
			<div class="sidebar-title">
				<p><?php echo __( 'My Templates', 'thrive-leads' ); ?></p>
				<span class="tcb-hl"></span>
			</div>
			<div id="tl-saved-filters">
				<a href="javascript:void(0);" class="click tl-category-filter" data-content="saved" data-fn="filterClick">
					<span class="tl-filter-label"><?php echo __( 'Saved templates', 'thrive-leads' ); ?></span>
					<span class="tl-filter-counter"></span>
				</a>
			</div>
			<label class="saved-templates-checkbox">
				<input id="tl-filter-current-templates" class="click" type="checkbox" data-fn="renderTemplates"/>
				<span>Show only saved versions of the current template</span>
			</label>
		</div>
	</div>
</div>
<div class="modal-content">
	<span class="tcb-modal-title ml-30"><?php echo __( 'Thrive Leads Library', 'thrive-leads' ) ?></span>
	<div class="warning-ct-change mr-30">
		<div class="tcb-notification info-text">
			<div class="tcb-notification-content">
				<?php echo __( 'Any changes you’ve made to the current form will be lost when you select a new template. We recommend you to save your current template first.', 'thrive-leads' ) ?>
			</div>
		</div>
	</div>
	<div class="tl-set-list"></div>
</div>
<div class="tcb-modal-footer clearfix padding-top-20 flex-end">
	<button type="button" class="tcb-right tve-button medium green tcb-modal-save click" data-fn="applyTemplate">
		<?php echo __( 'Choose Template', 'thrive-leads' ) ?>
	</button>
</div>
