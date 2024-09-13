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
<div id="tve-scripts_settings-component" class="tve-component" data-view="ScriptsSettings">
	<div class="mouseover" data-fn="hideTooltip">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo esc_html__( 'Custom Scripts', 'thrive-cb' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content mb-10">
			<div class="state-custom-scripts state">
				<section>
					<div class="field-section s-setting">
						<label class="s-name">
							<?php echo esc_html__( 'Header scripts', 'thrive-cb' ) ?>
							<?php tcb_icon( 'info-circle-solid', false, 'sidebar', '', [ "data-tooltip" => "Before the <b>&lthead&gt</b> end tag", "data-side" => "top" ] ); ?>
						</label>
						<textarea class="input" data-fn="setScript" rows="5" title="<?php echo esc_attr__( 'Header Scripts', 'thrive-cb' ); ?>"
								  name="<?php echo esc_attr( Tcb_Scripts::HEAD_SCRIPT ) ?>"><?php echo esc_textarea( $scripts[ Tcb_Scripts::HEAD_SCRIPT ] ); ?></textarea>
					</div>
					<div class="field-section no-border s-setting">
						<label class="s-name">
							<?php echo esc_html__( 'Body (header) scripts', 'thrive-cb' ) ?>
							<?php tcb_icon( 'info-circle-solid', false, 'sidebar', '', [ "data-tooltip" => "Immediately after the <b>&ltbody&gt</b> tag", "data-side" => "top" ] ); ?>
						</label>
						<textarea class="input" data-fn="setScript" rows="5" title="<?php echo esc_attr__( 'Body Scripts', 'thrive-cb' ); ?>"
								  name="<?php echo esc_attr( Tcb_Scripts::BODY_SCRIPT ); ?>"><?php echo esc_textarea( $scripts[ Tcb_Scripts::BODY_SCRIPT ] ); ?></textarea>
					</div>
					<div class="field-section no-border s-setting">
						<label class="s-name">
							<?php echo esc_html__( 'Body (footer) scripts', 'thrive-cb' ); ?>
							<?php tcb_icon( 'info-circle-solid', false, 'sidebar', '', [ "data-tooltip" => "Before the <b>&ltbody&gt</b> end tag", "data-side" => "top" ] ); ?>
						</label>
						<textarea class="input" data-fn="setScript" rows="5" title="<?php echo esc_attr__( 'Footer Scripts', 'thrive-cb' ); ?>"
								  name="<?php echo esc_attr( Tcb_Scripts::FOOTER_SCRIPT ); ?>"><?php echo esc_textarea( $scripts[ Tcb_Scripts::FOOTER_SCRIPT ] ); ?></textarea>
					</div>
				</section>
			</div>
		</div>
	</div>
</div>
