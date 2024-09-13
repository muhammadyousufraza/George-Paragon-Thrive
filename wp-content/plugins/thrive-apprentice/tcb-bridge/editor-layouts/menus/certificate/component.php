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
<div id="tve-certificate_verification-component" class="tve-component" data-view="CertificateVerification">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-apprentice' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center">
			<button class="tve-button orange mb-10 click" data-fn="editCertificate">
				<?php echo __( 'Edit Design', 'thrive-apprentice' ); ?>
			</button>
		</div>
		<hr>
		<div class="tve-control" data-view="LabelText">
			<div class="control-grid">
				<div class="label"><?php echo esc_html__( 'No certificate found message', 'thrive-apprentice' ); ?></div>
				<div class="input">
					<input type="text" class="tve-input-control input change" placeholder="" data-fn="changed">
				</div>
			</div>
		</div>
	</div>
</div>
