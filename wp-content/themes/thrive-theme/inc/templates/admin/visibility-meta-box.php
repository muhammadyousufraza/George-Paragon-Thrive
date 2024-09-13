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

<div class="thrive-sidebar-label">
	<span><?php echo __( 'Section Visibility', 'thrive-theme' ); ?></span>
</div>

<input type="hidden" name="thrive_visibility_settings_enabled" value="1"/>

<?php foreach ( Thrive_Post::get_visibility_config( 'sections' ) as $key => $data ) : ?>
	<?php $selected = thrive_post()->get_visibility( $key ); ?>

	<p class="editor-post-format__content">
		<label for="thrive_<?php echo $key; ?>_visibility"><?php echo __( $data['label'], 'thrive-theme' ); ?></label>
		<select id="thrive_<?php echo $key; ?>_visibility" name="thrive_<?php echo $key; ?>_visibility">
			<option value="inherit"><?php echo __( 'Inherit', 'thrive-theme' ); ?></option>
			<option <?php selected( 'show', $selected ); ?> value="show"><?php echo __( 'Show', 'thrive-theme' ); ?></option>
			<option <?php selected( 'hide', $selected ); ?> value="hide"><?php echo __( 'Hide', 'thrive-theme' ); ?></option>
		</select>
	</p>
<?php endforeach; ?>

<hr/>

<div class="thrive-sidebar-label">
	<span><?php echo __( 'Element Visibility', 'thrive-theme' ); ?></span>
</div>
<?php foreach ( Thrive_Post::get_visibility_config( 'elements', true ) as $key => $data ) : ?>
	<input class="thrive-checkbox" type="checkbox" id="thrive_<?php echo $key; ?>_visibility" name="thrive_<?php echo $key; ?>_visibility"
		   value="show" <?php checked( thrive_post()->is_visible( $key ) ); ?>/>
	<label for="thrive_<?php echo $key; ?>_visibility" class="thrive-checkbox-label"><?php echo __( $data['label'], 'thrive-theme' ); ?></label>
	<br/>
<?php endforeach ?>
