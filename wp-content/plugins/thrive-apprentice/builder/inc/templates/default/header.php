<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$config = [
	'menu_id'       => tve_get_default_menu(),
	'dropdown_icon' => 'style_1',
	'mobile_icon'   => 'style_1',
	'dir'           => 'tve_horizontal',
	'uuid'          => 'm-16b1c7f9087',
	'template'      => - 1,
	'template_name' => 'Basic',
];
?>

<div class="symbol-section-out"></div>
<div class="symbol-section-in" style="z-index: 9;">
	<div class="thrv_wrapper thrv-columns">
		<div class="tcb-flex-row tcb--cols--2 v-2 tcb-mobile-no-wrap m-edit tcb-resized" data-css="tve-u-164d292c1b3">
			<div class="tcb-flex-col c-33" data-css="tve-u-164d294a532">
				<div class="tcb-col">[tcb_logo]</div>
			</div>
			<div class="tcb-flex-col c-66" data-css="tve-u-164d294a535">
				<div class="tcb-col" style="z-index: 9;">
					<div class="tcb-clear" data-css="tve-u-164d29337f5">
						<div class="thrv_wrapper thrv_widget_menu tve-custom-menu-upgrade tve-mobile-dropdown tve-menu-template-light-tmp-first tve-regular" data-tve-switch-icon="tablet,mobile" data-css="tve-u-16b1c7f9088">
							<div class="thrive-shortcode-config" style="display:none!important;">__CONFIG_widget_menu__<?php echo json_encode( $config ); ?>__CONFIG_widget_menu__</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
