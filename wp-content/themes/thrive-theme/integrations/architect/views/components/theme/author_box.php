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
<div id="tve-thrive_author_box-component" class="tve-component" data-view="AuthorBox">
	<div class="dropdown-header component-name" data-prop="docked">
		<?php echo __( 'Main Options', 'thrive-theme' ); ?>
	</div>
	<div class="dropdown-content">
		<div class="mb-10 row tcb-text-center">
			<div class="col-xs-12">
				<button class="tve-button author-box-edit-mode orange click" data-fn="editMode">
					<?php echo __( 'Edit Design', 'thrive-theme' ); ?>
				</button>
			</div>
		</div>

		<div class="pb-5 tcb-text-center">
			<?php
			echo sprintf(
				__( 'Author details can be edited from the %s screen.', 'thrive-theme' ),
				'<a class="blue-text" target="_blank" href="' . admin_url( 'users.php' ) . '">WordPress user settings</a>'
			);
			?>
		</div>
	</div>
</div>
