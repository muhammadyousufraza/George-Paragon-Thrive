<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$admin_base_url = admin_url( '/', is_ssl() ? 'https' : 'admin' );
// for some reason, the above line does not work in some instances
if ( is_ssl() ) {
	$admin_base_url = str_replace( 'http://', 'https://', $admin_base_url );
}

?>
<div id="tve-theme-menu-component" class="tve-component" data-view="ThemeMenu">
	<div class="action-group">
		<div class="dropdown-header" data-prop="docked">
			<div class="group-description">
				<?php echo __( 'Theme Menu Options', 'thrive-theme' ); ?>
			</div>
			<i></i>
		</div>
		<div class="dropdown-content">
			<div class="tve-control tve-menu-direction mt-10" data-key="MenuDirection"></div>
			<div class="mt-20">
				<div class="row">
					<div class="col-xs-12">
						<a class="tve-button blue tve-edit-menu" href="<?php echo $admin_base_url; ?>nav-menus.php?action=edit&menu=0" target="_blank">
							<?php echo __( 'Edit Menu', 'thrive-theme' ) ?>
						</a>
					</div>
				</div>
			</div>
			<hr>
			<div class="tve-control" data-view="MainColor"></div>
			<div class="tve-control mt-10" data-view="ChildColor"></div>
			<div class="tve-control mt-10" data-view="ChildBackground"></div>
			<div class="tve-advanced-controls">
				<div class="dropdown-header" data-prop="advanced">
				<span>
					<?php echo __( 'Hover Colors', 'thrive-theme' ); ?>
				</span>
					<i></i>
				</div>

				<div class="dropdown-content clear-top">
					<div class="tve-control mt-20" data-view="HoverMainColor"></div>
					<div class="tve-control mt-10" data-view="HoverMainBackground"></div>
					<div class="tve-control mt-10" data-view="HoverChildColor"></div>
					<div class="tve-control mt-10" data-view="HoverChildBackground"></div>
				</div>
			</div>
		</div>
	</div>
</div>
