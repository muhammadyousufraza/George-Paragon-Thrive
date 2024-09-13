<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
} ?>
<div id="tve-social_follow-component" class="tve-component" data-view="SocialFollow">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center mb-10 mr-5 ml-5">
			<button class="tve-button orange click" data-fn="editElement">
				<?php echo esc_html__( 'Edit design', 'thrive-cb' ); ?>
			</button>
		</div>
		<div class="tve-control" data-view="CustomBranding"></div>
		<div class="tve-control" data-view="CssVarChanger"></div>
		<div class="tve-control gl-st-button-toggle-1 hide-states tcb-hidden" data-view="SocialFollowPalettes"></div>
		<div class="tve-control" data-key="type" data-view="ButtonGroup"></div>
		<div class="tve-control" data-key="style" data-initializer="style_control"></div>
		<div class="tve-control" data-key="orientation" data-view="ButtonGroup"></div>
		<hr>
		<div class="tve-control" data-key="size" data-view="Slider"></div>
		<div class="tve-control pt-5 gl-st-button-toggle-2" data-key="Align" data-view="ButtonGroup"></div>
		<div class="tve-control" data-view="CommonButtonWidth"></div>
		<hr>
		<div class="control-grid">
			<span class="input-label"><?php echo esc_html__( 'Social Networks', 'thrive-cb' ) ?></span>
		</div>
		<div class="tve-control" data-key="selector" data-initializer="selector_control"></div>
		<div class="tve-control" data-key="preview" data-initializer="previewListInitializer" ></div>
		<hr>
		<div class="control-grid full-width">
			<span class="input-label"><?php echo esc_html__( 'Custom Networks', 'thrive-cb' ) ?></span>
			<button class="click tcb-create-network p-5 mt-5" data-fn="createNetwork" style="width:100%"> <?php tcb_icon( 'plus-regular' ); ?><?php echo esc_html__( 'Create New', 'thrive-cb' ) ?> </button>
		</div>
		<div class="tve-control no-space" data-key="has_custom_url" data-view="Switch"></div>
		<div class="tve-control no-space pt-5 pb-5 full-width" data-key="custom_url" data-view="LabelInput"></div>
		<div class="tve-control no-space" data-key="total_share" data-view="Switch"></div>
		<div class="tve-control no-space pt-5 input-small" data-key="counts" data-view="LabelInput" data-label="<?php esc_attr_e( 'Greater Than', 'thrive-cb' ); ?>"></div>
	</div>
</div>
