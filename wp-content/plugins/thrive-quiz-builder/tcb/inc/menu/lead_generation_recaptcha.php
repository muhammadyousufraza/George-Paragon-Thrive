<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package TCB2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tve-lead_generation_recaptcha-component" class="tve-component" data-view="LeadGenerationRecaptcha">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="CaptchaTheme"></div>
		<div class="tve-control" data-view="CaptchaType"></div>
		<div class="tve-control" data-view="CaptchaSize"></div>
	</div>
</div>
