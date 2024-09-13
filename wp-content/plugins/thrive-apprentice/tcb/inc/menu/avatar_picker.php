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
<div id="tve-avatar_picker-component" class="tve-component" data-view="AvatarPicker">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Main Options', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div>
			<div class="tve-control" data-view="GoogleApi"></div>
			<div class="tve-control" data-view="FacebookApi"></div>
			<div class="tve-control" data-view="Gravatar"></div>
			<div class="tve-control pb-5" data-view="CustomUrl"></div>
		</div>

		<hr>

		<div class="tve-control" data-view="ImageSize"></div>

		<hr>

		<div class="tve-control" data-view="ButtonType"></div>
		<div class="tve-control" data-view="IconPosition"></div>
		<div class="tve-control" data-view="ButtonPosition"></div>

		<hr>

		<div class="tve-control" data-view="ImagePicker"></div>
	</div>
</div>
