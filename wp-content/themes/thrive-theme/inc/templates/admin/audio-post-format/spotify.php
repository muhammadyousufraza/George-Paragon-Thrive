<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$spotify_options = thrive_audio_post_format( Thrive_Audio_Post_Format_Main::SPOTIFY )->get_audio_options_meta();
$url_options     = $spotify_options['url'];
$prefix          = Thrive_Audio_Post_Format_Main::AUDIO_META_PREFIX . '_' . Thrive_Audio_Post_Format_Main::SPOTIFY . '_';

?>

<div class="thrive-audio-container" data-type="spotify" style="display:none">
	<div class="thrive-row">
		<div class="thrive-cell">
			<label for="<?php echo $prefix . 'url'; ?>"><?php echo $url_options['label']; ?></label>
		</div>

		<div class="thrive-cell">
			<textarea id="<?php echo $prefix . 'url'; ?>" name="<?php echo $prefix . 'url'; ?>" class="thrive-url-input"
					  placeholder="<?php echo $url_options['placeholder']; ?>"><?php echo $url_options['value']; ?></textarea>
		</div>
	</div>
</div>
