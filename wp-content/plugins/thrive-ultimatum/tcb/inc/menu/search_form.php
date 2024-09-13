<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-search_form-component" class="tve-component" data-view="SearchForm">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="mb-5 tcb-text-center">
			<button class="tve-button orange click tve-sf-edit-mode" data-fn="editFormElements">
				<?php echo esc_html__( 'Edit form elements', 'thrive-cb' ); ?>
			</button>
		</div>
		<div class="tve-control" data-view="SearchPalette"></div>
		<div class="mb-5"><?php echo esc_html__( 'Format', 'thrive-cb' ); ?></div>
		<div class="tve-control" data-view="FormType"></div>
		<hr>

		<div class="tve-search-with-btn">

			<div class="tve-control label-hidden" data-extends="Switch" data-view="EnableTwoStep" data-label="<?php _e( 'Enable two-step search', 'thrive-cb' ); ?>"></div>
			<div class="tve-control tcb-hidden" data-view="DisplayOptions"></div>
			<div class="tve-control tcb-hidden" data-view="EditorPreview"></div>

			<div class="tve-search-input-position mt-10">
				<div class="mb-5"><?php echo __( 'Search input position', 'thrive-cb' ); ?></div>
				<div class="tve-control" data-view="InputPosition"></div>
			</div>

			<hr>
			<div class="mb-5 hide-tablet hide-mobile sf-button-layout-ctrl"><?php echo esc_html__( 'Button layout', 'thrive-cb' ); ?></div>
			<div class="tve-control hide-tablet hide-mobile sf-button-layout-ctrl" data-view="ButtonLayout"></div>
			<hr class="hide-tablet hide-mobile sf-button-layout-ctrl">
		</div>

		<div class="control-grid add-post-type-control">
			<div class="label"><?php echo esc_html__( 'Search the following content', 'thrive-cb' ); ?></div>
			<div class="full">
				<button class="tcb-right tve-button blue click" data-fn="addPostType">
					<?php echo esc_html__( 'Manage', 'thrive-cb' ) ?>
				</button>
			</div>
		</div>
		<div class="tve-control" data-key="PostTypes" data-initializer="getPostTypesControl"></div>
		<div class="tve-sf-width-control">
			<hr>
			<div class="tve-control" data-view="ContentWidth"></div>
		</div>
		<hr>
		<div class="tve-control" data-view="Size"></div>
	</div>
</div>

