<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ${NAMESPACE}
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
?>
<div class="tva-certificate-state tve-form-state tcb-permanently-hidden" data-state="form">
	<div class="thrv_wrapper thrv_text_element">
		<h2 style="text-align: center; padding: 0;">Verify a Certificate</h2>
	</div>
	<div class="thrv_wrapper tva-certificate-form tcb-no-save tcb-no-clone tcb-no-delete">
		<form action="" method="post" novalidate>
			<div class="tve-form-drop-zone thrv-columns">
				<div class="tcb-flex-row v-2 tcb--cols--2 tcb-resized">
					<div class="tcb-flex-col">
						<div class="tcb-col">
							<div class="thrv_wrapper tva-certificate-form-item tcb-no-save tcb-no-clone tcb-no-delete">
								<div class="thrv_wrapper tva-certificate-form-input tve_no_drag tcb-no-save tcb-no-clone tcb-no-delete">
									<input type="text" name="number" placeholder="Enter a certificate number">
								</div>
							</div>
						</div>
					</div>
					<div class="tcb-flex-col">
						<div class="tcb-col">
							<div class="thrv_wrapper thrv-button tcb-no-delete tcb-no-save tcb-no-scroll tcb-no-clone tcb-local-vars-root tve-verification-button" data-css="tve-u-18442397057" data-button-style="btn-tpl-58268">
								<div class="thrive-colors-palette-config" style="display: none !important">__CONFIG_colors_palette__{"active_palette":0,"config":{"colors":{"b04af":{"name":"Main Accent","parent":-1}},"gradients":[]},"palettes":[{"name":"Default Palette","value":{"colors":{"b04af":{"val":"var(--tva-skin-color-4)","hsl":{"h":210,"s":0.78,"l":0.54}}},"gradients":[]},"original":{"colors":{"b04af":{"val":"rgb(19, 114,
									211)","hsl":{"h":210,"s":0.83,"l":0.45,"a":1}}},"gradients":[]}}]}__CONFIG_colors_palette__
								</div>
								<a href="#" class="tcb-button-link tcb-plain-text" draggable="false">
									<span class="tcb-button-texts tcb-no-clone tve_no_drag tcb-no-save tcb-no-delete">
										<span class="tcb-button-text thrv-inline-text tcb-no-clone tve_no_drag tcb-no-save tcb-no-delete">
											<?php esc_html_e( 'Verify', 'thrive-apprentice' ); ?>
										</span>
									</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
