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
<div id="tve-social_follow_item-component" class="tve-component" data-view="SocialFollowItem">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="NetworkColor"></div>
		<div class="hide-states pb-10">
			<div class="tve-control tve-choose-icon gl-st-icon-toggle-2" data-view="ModalPicker"></div>
		</div>
		<div class="tve-control no-space gl-st-icon-toggle-1" data-view="ColorPicker"></div>
		<div class="hide-states pt-10">
			<div class="tve-control gl-st-icon-toggle-1" data-view="Slider"></div>
		</div>
	</div>
</div>
