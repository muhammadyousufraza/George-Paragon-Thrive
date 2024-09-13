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
<div id="tve-main-container-component" class="tve-component" data-view="MainContainer">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Content Wrapper', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tve-control" data-view="PageMap"></div>

		<div class="tve-control" data-view="LayoutVisibility"></div>

		<hr class="mt-5">

		<div class="tve-control mb-5" data-view="SidebarVisibility"></div>

		<div class="tve-control mb-5" data-view="Position"></div>

		<div class="tve-control mt-5" data-view="Gutter"></div>

		<div class="tve-control mb-5" data-view="Wrap"></div>
	</div>
</div>
