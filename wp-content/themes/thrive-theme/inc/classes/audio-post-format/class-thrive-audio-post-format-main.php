<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Audio_Post_Format_Main
 */
class Thrive_Audio_Post_Format_Main {

	const AUDIO_META_PREFIX      = 'thrive_meta_postformat_audio';
	const AUDIO_META_TYPE_PREFIX = self::AUDIO_META_PREFIX . '_type';
	const AUDIO_META_OPTION      = 'thrive_theme_audio_format_meta';

	const CUSTOM     = 'custom';
	const SPOTIFY    = 'spotify';
	const SOUNDCLOUD = 'soundcloud';

	const ALL_AUDIO_TYPES = [ self::SOUNDCLOUD, self::SPOTIFY, self::CUSTOM ];

	/**
	 * Get the audio type.
	 *
	 * @return array|mixed
	 */
	public static function get_type() {
		$post_id = get_the_ID();
		$options = get_post_meta( $post_id, static::AUDIO_META_OPTION, true );

		if ( empty( $options ) ) {
			/* if the new options are empty, look for options from the old themes */
			$old_theme_type_key = '_' . static::AUDIO_META_TYPE_PREFIX;
			$type               = get_post_meta( $post_id, $old_theme_type_key, true );
		} elseif ( ! empty( $options['type'] ) ) {
			$type = $options['type'];
		}

		/* if nothing is found, use custom as a default value */
		$type = empty( $type ) || $type === 'file' ? static::CUSTOM : $type;

		return $type;
	}

	/*
	 * Called by the 'save_post' action. Saves the audio post meta settings.
	 */
	public static function save_audio_meta_fields() {
		if ( empty( get_the_ID() ) ) {
			return;
		}

		/* if type is not set, something is terribly wrong */
		if ( isset( $_POST[ static::AUDIO_META_TYPE_PREFIX ] ) ) {
			$post_format = thrive_audio_post_format( $_POST[ static::AUDIO_META_TYPE_PREFIX ] );
			if ( $post_format ) {
				$post_format->save_options( $_POST );
			}
		}
	}

	/**
	 * Wrapper for the audio render.
	 *
	 * @param $type
	 * @param $post_id
	 *
	 * @return mixed
	 *
	 */
	public static function render( $type = null, $post_id = null ) {
		if ( ! $type ) {
			$type = static::get_type();
		}
		$renderer = thrive_audio_post_format( $type, $post_id );

		return empty( $renderer ) ? '' : $renderer->render();
	}


	public static function render_placeholder() {
		if ( is_editor_page_raw( true ) ) {
			$content = Thrive_Utils::return_part( '/inc/templates/parts/dynamic-audio-placeholder.php' );
		} else {
			$content = '';
		}

		return $content;
	}
}
