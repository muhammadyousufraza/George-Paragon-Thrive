<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>

<div id="tve-image-component" class="tve-component" data-view="Image">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">

		<div class="tve-control" data-view="ExternalFields"></div>
		<div class="tve-control custom-fields-state" data-state="static" data-view="ImagePicker"></div>
		<div class="tve-control" data-view="ImageFullSize"></div>

		<hr>

		<div class="tve-control" data-view="ImageSize"></div>
		<div class="tve-control" data-view="ImageHeight"></div>
		<div class="control-grid center reset-size tcb-hidden">
			<span class="click tcb-text-uppercase" data-fn="resetToDefaultSize"><?php tcb_icon( 'undo' ); ?><?php echo esc_html__( 'Reset to default size', 'thrive-cb' ); ?></span>
		</div>
		<hr>
		<div class="tve-control" data-view="StyleChange"></div>
		<div class="tve-control" data-view="ImageTitle"></div>
		<div class="tve-control" data-view="ImageAltText"></div>
		<div class="tve-control scrolled" data-key="StylePicker" data-initializer="style_picker_control"></div>

		<hr>

		<div class="tve-control no-space mb-5" data-view="ImageLoading"></div>
		<div class="tve-control no-space mb-5" data-view="ImageCaption"></div>
		<div class="tve-control no-space" data-key="ToggleURL" data-extends="Switch" data-label="<?php echo esc_html__( 'Add link to image', 'thrive-cb' ); ?>"></div>

		<div class="image-link mt-10"></div>

	</div>
</div>

<div id="tve-image-effects-component" class="tve-component" data-view="ImageEffects">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Image Effects', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ImageGreyscale"></div>
		<div class="tve-control" data-view="ImageOpacity"></div>
		<div class="tve-control" data-view="ImageBlur"></div>
		<div class="tve-control" data-view="ImageBrightness"></div>
		<div class="tve-control" data-view="ImageContrast"></div>
		<div class="tve-control" data-view="ImageSepia"></div>
		<div class="tve-control" data-view="ImageInvert"></div>
		<div class="tve-control" data-view="ImageSaturate"></div>
		<div class="tve-control" data-view="ImageHueRotate"></div>
		<div class="tve-control" data-view="ImageOverlaySwitch"></div>
		<div class="tve-control" data-view="ImageOverlay"></div>

		<button class="click tve-button" data-fn="set_default">
			<?php echo esc_html__( 'Reset to Default', 'thrive-cb' ); ?>
		</button>
	</div>
</div>
