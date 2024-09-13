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
<div id="thrive_video_post_format_options" class="thrive-post-format-options">
	<button type="button" class="handlediv" aria-expanded="true">
		<span class="screen-reader-text"><?php echo __( 'Toggle panel: Thrive Video Post Format Options', 'thrive-theme' ); ?></span>
		<span class="toggle-indicator" aria-hidden="true"></span></button>
	<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Thrive Video Post Format Options', 'thrive-theme' ); ?></span></h2>
	<div class="inside">
		<?php $type = Thrive_Video_Post_Format_Main::get_type(); ?>
		<?php $video_post_main = thrive_video_post_format( $type ); ?>

		<div class="thrive-video-post-meta" id="thrive-video-post-meta">

			<div class="thrive-video-type-picker">
				<?php $name = Thrive_Video_Post_Format_Main::VIDEO_META_PREFIX . '_type'; ?>

				<?php foreach ( Thrive_Video_Post_Format_Main::ALL_VIDEO_TYPES as $key ) : ?>
					<?php $identifier = $name . '_' . $key; ?>
					<input type="radio"
						   id="<?php echo $identifier; ?>"
						   name="<?php echo $name; ?>"
						   class="<?php echo $name; ?>"
						   value="<?php echo $key; ?>"
						<?php echo $type === $key ? 'checked' : ''; ?>/>
					<label for="<?php echo $identifier; ?>"><?php echo __( ucfirst( $key ), 'thrive-theme' ); ?></label>
				<?php endforeach; ?>
			</div>

			<?php foreach ( Thrive_Video_Post_Format_Main::ALL_VIDEO_TYPES as $key ) : ?>
				<?php thrive_video_post_format( $key )->render_options(); ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>

<div id="thrive_audio_post_format_options" class="thrive-post-format-options">
	<button type="button" class="handlediv" aria-expanded="true">
		<span class="screen-reader-text"><?php echo __( 'Toggle panel: Thrive Audio Post Format Options', 'thrive-theme' ); ?></span>
		<span class="toggle-indicator" aria-hidden="true"></span></button>
	<h2 class="hndle ui-sortable-handle"><span><?php echo __( 'Thrive Audio Post Format Options', 'thrive-theme' ); ?></span></h2>
	<div class="inside">
		<?php $type = Thrive_Audio_Post_Format_Main::get_type(); ?>
		<?php $audio_post_main = thrive_audio_post_format( $type ); ?>

		<div class="thrive-audio-post-meta" id="thrive-audio-post-meta">

			<div class="thrive-audio-type-picker">
				<?php $name = Thrive_Audio_Post_Format_Main::AUDIO_META_PREFIX . '_type'; ?>

				<?php foreach ( Thrive_Audio_Post_Format_Main::ALL_AUDIO_TYPES as $key ) : ?>
					<?php $identifier = $name . '_' . $key; ?>
					<input type="radio"
						   id="<?php echo $identifier; ?>"
						   name="<?php echo $name; ?>"
						   class="<?php echo $name; ?>"
						   value="<?php echo $key; ?>"
						<?php echo $type === $key ? 'checked' : ''; ?>/>
					<label for="<?php echo $identifier; ?>"><?php echo __( ucfirst( $key ), 'thrive-theme' ); ?></label>
				<?php endforeach; ?>
			</div>

			<?php foreach ( Thrive_Audio_Post_Format_Main::ALL_AUDIO_TYPES as $key ) : ?>
				<?php thrive_audio_post_format( $key )->render_options(); ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>
