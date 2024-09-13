<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>
<div id="tve-certificate_qr_code-component" class="tve-component" data-view="CertificateQRCode">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-apprentice' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="ExternalFields"></div>
		<div class="tve-control custom-fields-state" data-state="static" data-view="StaticSource"></div>
		<div class="tve-control" data-view="ForegroundColor"></div>
		<div class="tve-control" data-view="BackgroundColor"></div>
		<div class="info-text orange mb-10">
			<?php echo __( 'Be aware that not all color combinations will be able to be scanned successfully. We encourage you to test your QR code by scanning it with your phone.', 'thrive-apprentice' ); ?>
		</div>
		<div class="tve-control" data-view="Size"></div>
	</div>
</div>
