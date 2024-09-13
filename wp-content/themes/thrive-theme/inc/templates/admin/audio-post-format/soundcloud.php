<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$soundcloud_options = thrive_audio_post_format( Thrive_Audio_Post_Format_Main::SOUNDCLOUD )->get_audio_options_meta();
$url_options        = $soundcloud_options['url'];
$prefix             = Thrive_Audio_Post_Format_Main::AUDIO_META_PREFIX . '_' . Thrive_Audio_Post_Format_Main::SOUNDCLOUD . '_';

?>

<div class="thrive-audio-container" data-type="soundcloud" style="display:none">
	<div class="thrive-row">
		<div class="thrive-cell">
			<label for="<?php echo $prefix . 'url'; ?>"><?php echo $url_options['label']; ?></label>
		</div>

		<div class="thrive-cell">
			<textarea id="<?php echo $prefix . 'url'; ?>" name="<?php echo $prefix . 'url'; ?>" class="thrive-url-input"
					  placeholder="<?php echo $url_options['placeholder']; ?>"><?php echo $url_options['value']; ?></textarea>
		</div>
	</div>

	<div class="thrive-row">
		<div class="thrive-cell">
			<?php echo __( 'Options', 'thrive-theme' ); ?>
		</div>

		<div class="thrive-cell">
			<?php foreach ( $soundcloud_options as $key => $option ) : ?>

				<?php if ( $option['type'] === 'checkbox' ) : ?>
					<input type="checkbox" name="<?php echo $prefix . $key; ?>" id="<?php echo $prefix . $key; ?>"
						   value="1" <?php checked( $option['value'] ); ?> class="<?php echo empty( $option['class'] ) ? '' : $option['class']; ?>"/>
					<label for="<?php echo $prefix . $key; ?>"><?php echo $option['label']; ?>
						<?php if ( ! empty( $option['notice'] ) ) : ?>
							<span class="thrive-notice" style="display:none"> - <?php echo $option['notice']; ?> </span>
						<?php endif; ?>
					</label>
					<br/>
				<?php endif; ?>

			<?php endforeach; ?>
		</div>
	</div>
</div>
