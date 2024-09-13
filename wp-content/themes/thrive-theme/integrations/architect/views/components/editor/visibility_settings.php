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

<div id="tve-visibility_settings-component" class="tve-component" data-view="VisibilitySettings">
	<div class="dropdown-header" data-prop="docked">
		<div class="group-description">
			<?php echo __( 'Visibility Settings', 'thrive-theme' ); ?>
		</div>
		<i></i>
	</div>
	<div class="dropdown-content mb-10">
		<div class="mb-10">
			<?php echo __( 'Set the visibility for sections and modules for this specific post.', 'thrive-theme' ); ?>
		</div>

		<hr/>

		<?php foreach ( Thrive_Post::get_visibility_config( 'sections' ) as $key => $data ) : ?>
			<div class="tve-control" data-view="<?php echo $data['view']; ?>"></div>
		<?php endforeach ?>

		<hr/>

		<?php foreach ( Thrive_Post::get_visibility_config( 'elements' ) as $key => $data ) : ?>
			<div class="tve-control" data-view="<?php echo $data['view']; ?>"></div>
		<?php endforeach ?>

		<div class="tve-control sep-top" data-view="ShowAllHidden"></div>
	</div>
</div>
