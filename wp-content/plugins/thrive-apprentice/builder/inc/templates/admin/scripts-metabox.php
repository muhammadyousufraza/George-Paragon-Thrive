<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$scripts = tcb_scripts()->get_all();
?>
<div>
	<div class="components-base-control__field">
		<label class="components-base-control__label">
			<?php echo __( 'Header scripts', 'thrive-theme' ) ?>
			<svg viewBox="0 0 512 512" style="width:12px" data-tooltip="Before the <b>&lthead&gt</b> end tag" data-side="top">
				<path d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path>
			</svg>
		</label>
		<textarea class="components-textarea-control__input" rows="5" title="<?php echo __( 'Header Scripts', 'thrive-theme' ); ?>"
				  name="thrive_head_scripts"><?php echo $scripts[ Tcb_Scripts::HEAD_SCRIPT ] ?></textarea>
	</div>
	<div class="field-section no-border s-setting">
		<label class="components-base-control__label">
			<?php echo __( 'Body (header) scripts', 'thrive-theme' ) ?>
			<svg viewBox="0 0 512 512" style="width:12px" data-tooltip="Immediately after the <b>&ltbody&gt</b> tag" data-side="top">
				<path d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path>
			</svg>
		</label>
		<textarea class="components-textarea-control__input" rows="5" title="<?php echo __( 'Body Scripts', 'thrive-theme' ); ?>"
				  name="thrive_body_scripts"><?php echo $scripts[ Tcb_Scripts::BODY_SCRIPT ] ?></textarea>
	</div>
	<div class="field-section no-border s-setting">
		<label class="components-base-control__label">
			<?php echo __( 'Body (footer) scripts', 'thrive-theme' ) ?>
			<svg viewBox="0 0 512 512" style="width:12px" data-tooltip="Before the <b>&ltbody&gt</b> end tag" data-side="top">
				<path d="M256 8C119.043 8 8 119.083 8 256c0 136.997 111.043 248 248 248s248-111.003 248-248C504 119.083 392.957 8 256 8zm0 110c23.196 0 42 18.804 42 42s-18.804 42-42 42-42-18.804-42-42 18.804-42 42-42zm56 254c0 6.627-5.373 12-12 12h-88c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h12v-64h-12c-6.627 0-12-5.373-12-12v-24c0-6.627 5.373-12 12-12h64c6.627 0 12 5.373 12 12v100h12c6.627 0 12 5.373 12 12v24z"></path>
			</svg>
		</label>
		<textarea class="components-textarea-control__input" rows="5" title="<?php echo __( 'Footer Scripts', 'thrive-theme' ); ?>"
				  name="thrive_footer_scripts"><?php echo $scripts[ Tcb_Scripts::FOOTER_SCRIPT ] ?></textarea>
	</div>
</div>