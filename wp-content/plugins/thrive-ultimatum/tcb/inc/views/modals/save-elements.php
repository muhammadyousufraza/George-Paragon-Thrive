<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="tcb-modal-save-content">
	<span class="tcb-modal-title"><?php echo esc_html__( 'Save Element for Later Use', 'thrive-cb' ) ?></span>

	<div class="error-container"></div>

	<div class="tcb-modal-save-container">
		<div class="tcb-preview-container">
			<div class="tcb-preview-image lazy-loading">
				<img class="tve-lazy-img" src="" data-loading-src="<?php echo esc_url( tve_editor_css( 'images/loading-spinner-large.gif' ) ); ?>" alt="">
			</div>
		</div>

		<div style="flex: 0 0 100px;"></div>

		<div class="save-container">
			<span class="tcb-save-as-label"><?php echo esc_html__( 'Save As', 'thrive-cb' ) ?></span>
			<div class="tcb-center-checkbox">

				<label class="modal-switch">
					<span class="sp template-text"><?php echo esc_html__( 'Template', 'thrive-cb' ) ?></span>
					<input type="checkbox" name="change_element" id="change_element">
					<span class="slider round"></span>
					<span class="sp symbol-text"><?php echo esc_html__( 'Symbol', 'thrive-cb' ) ?></span>
				</label>

			</div>

			<p class="element-description"><?php echo esc_html__( 'Content templates can be used across your website but they are edited only locally.', 'thrive-cb' ) ?></p>

			<div class="tvd-input-field">
				<label class="tcb-save-as-label tcb-input-name"><?php echo esc_html__( 'Template Name', 'thrive-cb' ); ?></label>
				<input class="keydown tvd-active content-title" type="text" name="title" data-source="search" data-fn="filter">
				<img class="tve-lazy-img tcb-input-loader" src="<?php echo esc_url( tve_editor_css( 'images/loading-spinner-large.gif' ) ); ?>" alt="">
				<label class="tvd-active tcb-ct-symbol-message"></label>
			</div>

			<div class="tvd-input-field category_selection category-container mt-20">
				<label class="tcb-save-as-label"><?php echo esc_html__( 'Category', 'thrive-cb' ); ?></label>
				<select id="tcb-save-template-categ-suggest"></select>
			</div>
		</div>
	</div>
</div>
<div class="tcb-modal-footer">
	<span class="tcb-show-templates">
		<a class="click" data-fn="show_templates">
			<span><?php tcb_icon( 'save' ); ?></span>
			<span class="tcb-show-templates-label"><?php echo esc_html__( 'Show all Saved Templates', 'thrive-cb' ) ?></span>
			<span class="tcb-hide-templates-label" style="color: #0c81bf"><?php echo esc_html__( 'Hide all Saved Templates', 'thrive-cb' ) ?></span>
		</a>
	</span>

	<div class="tvd-input-field">
		<label class="tcb-save-as-label tcb-input-name"><?php echo esc_html__( 'Template Name', 'thrive-cb' ); ?></label>
		<input class="keydown tvd-active content-title" type="text" name="title" data-source="search" data-fn="filter">
	</div>

	<div class="tcb-relative">
		<button type="button" class="tcb-right tve-button medium green tcb-modal-save">
			<?php echo esc_html__( 'Save New Template', 'thrive-cb' ) ?>
		</button>
		<div class="update-template-tooltip" style="position:absolute">
			<div class="drop-panel tooltip-panel panel-light ">
				<div class="popup-content mb-40">
					<p><?php echo sprintf( esc_html__( '"%s" already exists' ), '<span class="tcb-template-name"></span>' ); ?></p>
					<p class="tooltip-text mb-20"><?php echo esc_html__( 'Do you want to update it?', 'thrive-cb' ); ?></p>
					<span class="tooltip-text light"><?php echo esc_html__( 'Updating it will overwrite its current contents', 'thrive-cb' ); ?></span>
				</div>
				<div class="action-buttons control-grid">
					<div data-fn="onCancel" class="click tve-button drop-panel-action btn-cancel"><?php echo esc_html__( 'Cancel', 'thrive-cb' ); ?></div>
					<div data-fn="onApply" class="click tve-button drop-panel-action btn-apply"><?php echo esc_html__( 'Update Existing Template', 'thrive-cb' ); ?></div>
				</div>
				<div class="tcb-panel-arrow"></div>
			</div>
		</div>
	</div>
</div>
<div class="tcb-templates-wrapper click" data-fn="clearSelected">
	<div id="tcb-templates-number-container">
		<span id="tcb-templates-number"></span>
		<span> <?php echo esc_html__( ' existing files', 'thrive-cb' ) ?></span>
	</div>
	<div id="cb-pack-content"></div>
</div>
