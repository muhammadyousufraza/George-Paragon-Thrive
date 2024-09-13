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

<div id="tve-sidebar-settings-component" class="tve-component" data-view="SidebarSettings">
	<div class="dropdown-header" data-prop="docked">
		<?php echo __( 'Sidebar Display', 'thrive-theme' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">

		<div class="tve-control" data-view="SidebarDisplay"></div>

		<div data-sidebar-display="normal">
			<div class="tve-control" data-view="Sticky"></div>
			<div class="sticky-options">
				<div class="tve-control" data-view="StickyDelta"></div>
				<div class="tve-control" data-view="StickyUntil"></div>

				<div class="sticky-element-id">
					<div class="tve-control" data-view="StickyElementId"></div>
					<div class="info-text if-until-element"><?php echo __( 'You can set the ID of an element from the "HTML Attributes" section', 'thrive-theme' ); ?></div>
					<div class="orange info-text sticky-until-warning" style="display: none"><?php echo __( 'Sidebar will be sticky until end of main container when the element set is located above or inside the sidebar.', 'thrive-theme' ); ?></div>
				</div>
			</div>
		</div>

		<div class="mb-5" data-sidebar-display="off-screen">
			<div class="tve-control" data-view="OffscreenDefaultState"></div>
			<div class="tve-control" data-view="ShowOffscreenInEditor"></div>
			<div class="tve-control sep-bottom" data-view="OffscreenDisplay"></div>
			<div class="tve-control mt-5" data-view="OffscreenOverlayColorSwitch"></div>
			<div class="tve-control" data-view="OffscreenOverlayColor"></div>
			<div class="tve-control sep-top" data-view="OffscreenDefaultTrigger"></div>
			<div class="tve-control" data-view="OffscreenTriggerPosition"></div>
			<div class="tve-control sep-top" data-view="OffscreenCloseIcon"></div>
		</div>
	</div>
</div>
