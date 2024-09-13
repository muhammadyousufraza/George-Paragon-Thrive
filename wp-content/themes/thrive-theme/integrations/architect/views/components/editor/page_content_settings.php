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

<div id="tve-page_content_settings-component" class="tve-component" data-view="PageContentSettings">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo Thrive_Utils::get_post_type_name() . ' ' . __( 'Content', 'thrive-theme' ) ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mb-5 full-width" data-view="PostTitle"></div>
		<div class="tve-control mb-5" data-view="FeaturedImage"></div>
	</div>
</div>
