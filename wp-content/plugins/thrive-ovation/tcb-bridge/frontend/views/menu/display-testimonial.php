<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
?>
<div id="tvo-preview-menu" class="tvo-menu-onpage">
	<div class="tvo-menu-onpage-container">

		<div class="tvo-menu-relative tvo-menu-inline-block">
			<span class="tvo_toggle_fields tvo_menu_item tvo_icon_box" data-func="toggle_fields" data-toggle=".tvo_color_box">
				<i class="tvo-f-icon-color-palette"></i>
				<?php echo __( 'Color', 'thrive-ovation' ) ?>
			</span>

			<div class="tvo_toggle_fields_box tvo_color_box tvo-showhide-fields" style="display: none;">
				<?php echo __( 'Template Color', 'thrive-ovation' ) ?>
				<ul class="tvo_template_colors">
					<li class="tve_black"><span></span></li>
					<li class="tve_blue"><span></span></li>
					<li class="tve_green"><span></span></li>
					<li class="tve_orange"><span></span></li>
					<li class="tve_purple"><span></span></li>
					<li class="tve_red"><span></span></li>
					<li class="tve_teal"><span></span></li>
					<li class="tve_white"><span></span></li>
				</ul>
			</div>
		</div>

		<span class="tvo_display_settings tvo_menu_item" data-func="display_settings"><?php echo __( 'Display settings', 'thrive-ovation' ) ?></span>
		<span class="tvo_change_template tvo_menu_item" data-func="change_template"><?php echo __( 'Change template', 'thrive-ovation' ) ?></span>
		<div class="tvo-menu-relative tvo-menu-inline-block">
			<span class="tvo_toggle_fields tvo_menu_item" data-func="toggle_fields" data-toggle=".tvo_fields_box">
				<?php echo __( 'Show/hide fields', 'thrive-ovation' ) ?>
			</span>

			<div class="tvo_toggle_fields_box tvo_fields_box tvo-showhide-fields" style="display: none;">
				<input type="checkbox" class="tvo_config_field" name="show_role" id="show_role">
				<label for="show_role"><?php echo __( 'Role', 'thrive-ovation' ) ?></label>
				<br>
				<input type="checkbox" class="tvo_config_field" name="show_site" id="show_site">
				<label for="show_site"><?php echo __( 'Website', 'thrive-ovation' ) ?></label>
				<br>
				<input type="checkbox" class="tvo_config_field" name="show_title" id="show_title">
				<label for="show_title"><?php echo __( 'Title', 'thrive-ovation' ) ?></label>
			</div>
		</div>
		<span class="tvo_save_changes tvo_menu_item" data-func="save"><?php echo __( 'Save Changes', 'thrive-ovation' ) ?></span>
	</div>
</div>
