<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/* get the selected page template */
$selected = thrive_post()->get_meta( THRIVE_META_POST_TEMPLATE );

use Thrive\Theme\AMP\Settings as AMP_Settings;

?>

<p class="editor-post-format__content">
	<input type="hidden" name="thrive_template_settings_enabled" value="1"/>

	<label for="<?php echo THRIVE_META_POST_TEMPLATE; ?>"><?php echo __( 'Template', 'thrive-theme' ); ?></label>
	<select id="<?php echo THRIVE_META_POST_TEMPLATE; ?>" name="<?php echo THRIVE_META_POST_TEMPLATE; ?>">
		<option selected="selected" value="0"><?php echo __( 'Default', 'thrive-theme' ); ?></option>
		<?php foreach ( thrive_post()->get_all_templates() as $template ) : ?>
			<option <?php selected( $template['ID'], $selected ); ?> value="<?php echo $template['ID']; ?>">
				<?php echo $template['name']; ?>
			</option>
		<?php endforeach; ?>
	</select>
</p>

<?php if ( AMP_Settings::enabled_on_post_type( get_the_ID() ) ) : ?>
	<hr class="thrive-settings-separator"/>
	<div class="thrive-sidebar-label">
		<h4><?php echo esc_html__( 'AMP Settings', 'thrive-theme' ); ?></h4>
	</div>

	<div class="thrive-setting-row">
		<input class="thrive-checkbox" type="checkbox"
			   id="<?php echo THRIVE_META_POST_AMP_STATUS; ?>"
			   name="<?php echo THRIVE_META_POST_AMP_STATUS; ?>"
			   value="disabled" <?php checked( thrive_post()->is_amp_disabled() ); ?>
		/>
		<label for="<?php echo THRIVE_META_POST_AMP_STATUS; ?>" class="thrive-checkbox-label">
			<?php echo esc_html__( 'Disable AMP for this post', 'thrive-theme' ); ?>
		</label>
	</div>
<?php endif; ?>
