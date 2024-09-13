<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

?>
<div id="tve-post_content-component" class="tve-component" data-view="PostContent">
	<div class="dropdown-header component-name" data-prop="docked">
		<?php echo esc_html__( 'Post Content', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control mt-10" data-view="ContentSize"></div>

		<div class="tve-control mb-10" data-view="WordsTrim"></div>

		<div class="tve-control mb-10" data-view="ReadMoreText"></div>
	</div>
</div>
