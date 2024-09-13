<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$vimeo_options = thrive_video_post_format( Thrive_Video_Post_Format_Main::VIMEO )->get_video_options_meta();
$url_options   = $vimeo_options['url'];
$prefix        = Thrive_Video_Post_Format_Main::VIDEO_META_PREFIX . '_' . Thrive_Video_Post_Format_Main::VIMEO . '_';

?>

<div class="thrive-video-container" data-type="vimeo" style="display:none">
	<div class="thrive-row">
		<div class="thrive-cell">
			<label for="<?php echo $prefix . 'url'; ?>"><?php echo $url_options['label']; ?></label>
		</div>

		<div class="thrive-cell">
			<input type="text" id="<?php echo $prefix . 'url'; ?>" name="<?php echo $prefix . 'url'; ?>" class="thrive-url-input"
				   value="<?php echo $url_options['value']; ?>" placeholder="<?php echo $url_options['placeholder']; ?>"/>
			<div class="thrive-url-validate"></div>
		</div>
	</div>

	<div class="thrive-row">
		<div class="thrive-cell">
			<?php echo __( 'Options', 'thrive-theme' ); ?>
		</div>

		<div class="thrive-cell">
			<?php foreach ( $vimeo_options as $key => $option ) : ?>

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

	<div class="thrive-row">
		<div class="thrive-cell">
			<?php echo __( 'Video Start Time', 'thrive-theme' ); ?>
		</div>
		<div class="thrive-cell thrive-video-start">
			<label for="<?php echo $prefix . 'start_time_minutes'; ?>"> <?php echo __( 'Minutes', 'thrive-theme' ); ?></label>
			<input type="text" maxlength="2" id="<?php echo $prefix . 'start_time_minutes'; ?>" name="<?php echo $prefix . 'start_time_minutes'; ?>"
				   class="thrive-video-time-input" value="<?php echo $vimeo_options['start_time_minutes']['value']; ?>" placeholder="0"/>

			<label for="<?php echo $prefix . 'start_time_seconds'; ?>"> <?php echo __( 'Seconds', 'thrive-theme' ); ?></label>
			<input type="text" maxlength="2" id="<?php echo $prefix . 'start_time_seconds'; ?>" name="<?php echo $prefix . 'start_time_seconds'; ?>"
				   class="thrive-video-time-input" value="<?php echo $vimeo_options['start_time_seconds']['value']; ?>" placeholder="0"/>
		</div>
	</div>
</div>
