<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="thrv_wrapper thrv-contact-form" data-ct="contact_form-21969">
	<form action="" method="post" novalidate>
		<div class="tve-cf-item-wrapper">
			<div class="tve-cf-item">
				<div class="thrv-cf-input-wrapper" data-type="first_name">
					<label><?php echo esc_html__( 'First Name', 'thrive-cb' ) ?></label>
					<div class="tve-cf-input">
						<input placeholder="John" data-placeholder="John" type="text" name="first_name" required="required">
					</div>
				</div>
			</div>
			<div class="tve-cf-item">
				<div class="thrv-cf-input-wrapper" data-type="email">
					<label><?php echo esc_html__( 'Email Address', 'thrive-cb' ) ?></label>
					<div class="tve-cf-input">
						<input placeholder="j.doe@inbox.com" data-placeholder="j.doe@inbox.com" type="email" name="email" required="required">
					</div>
				</div>
			</div>
			<div class="tve-cf-item">
				<div class="thrv-cf-input-wrapper" data-type="message">
					<label><?php echo esc_html__( 'Message', 'thrive-cb' ) ?></label>
					<div class="tve-cf-input">
						<textarea placeholder="Type your message here..." data-placeholder="Type your message here..." name="message" required="required"></textarea>
					</div>
				</div>
			</div>
		</div>
		<div class="thrv_wrapper tve-form-button tcb-local-vars-root">
			<div class="thrive-colors-palette-config" style="display: none !important">__CONFIG_colors_palette__{"active_palette":0,"config":{"colors":{"cf6ff":{"name":"Main Color","parent":-1},"73c8d":{"name":"Dark Accent","parent":"cf6ff"}},"gradients":[]},"palettes":[{"name":"Default","value":{"colors":{"cf6ff":{"val":"rgb(20, 115, 210)","hsl":{"h":210,"s":0.82,"l":0.45}},"73c8d":{"val":"rgb(21, 89, 162)","hsl_parent_dependency":{"h":211,"s":0.77,"l":0.35}}},"gradients":[]}}]}__CONFIG_colors_palette__</div>
			<a href="#" class="tcb-button-link tve-form-button-submit">
				<span class="tcb-button-texts tcb-plain-text"><span class="tcb-button-text thrv-inline-text"><?php echo esc_html__( 'Send Message', 'thrive-cb' ); ?></span></span>
			</a>
			<input type="submit" style="display: none !important;">
		</div>
	</form>
</div>
