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
 * Class Thrive_Audio_Post_Format
 */
abstract class Thrive_Audio_Post_Format {

	public $type;
	public $post_id;

	const AUDIO_CONTAINER_CLS = 'tve_audio_container';

	/**
	 * Factory for audio classes.
	 *
	 * @param string $type
	 * @param        $post_id
	 *
	 * @return Thrive_Audio_Post_Format
	 */
	public static function factory( $type, $post_id = null ) {
		$audio_class = "Thrive_Audio_Post_{$type}";

		/* only do something if the class exists */
		if ( ! empty( $type ) && class_exists( $audio_class, false ) ) {
			return new $audio_class( $type, $post_id );
		}

		return null;
	}

	/**
	 * Thrive_Audio_Post_Format constructor.
	 *
	 * @param $type
	 * @param $post_id
	 */
	public function __construct( $type, $post_id = null ) {
		$this->type    = $type;
		$this->post_id = $post_id ? $post_id : get_the_ID();
	}

	/**
	 * Get the meta for a audio type. If a key is provided, return only the value for that key.
	 *
	 * @param $key
	 *
	 * @return array|mixed
	 */
	public function get_audio_options_meta( $key = '' ) {
		$options = get_post_meta( $this->post_id, Thrive_Audio_Post_Format_Main::AUDIO_META_OPTION, true );

		/* if the options are empty, try looking for the settings from the old themes */
		if ( empty( $options ) ) {
			$options = $this->compat_get_audio_options_meta();
		} else {
			$defaults = $this->get_defaults();
			/* if the type from the options does not match the current audio type, return the defaults */
			if ( empty( $options['type'] ) || ( $options['type'] !== $this->type ) || empty( $options['audio_options'] ) ) {
				return $defaults;
			} else {
				$options = $options['audio_options'];

				/* merge the values with the other fields from the defaults */
				foreach ( $defaults as $option_key => $sub_options ) {
					/* if the saved value is empty or is not set, use the default value */
					$defaults[ $option_key ]['value'] = empty( $options[ $option_key ]['value'] ) ? $sub_options['default'] : $options[ $option_key ]['value'];
				}
				$options = $defaults;
			}
		}

		/* if a key is set, return only that option */
		if ( isset( $options[ $key ] ) ) {
			$options = $options[ $key ];
		}

		return $options;
	}

	/**
	 * Get the audio settings saved from the old themes. If there are no settings saved, use the default values.
	 *
	 * @return mixed
	 */
	public function compat_get_audio_options_meta() {
		$options = $this->get_defaults();

		/* generate the keys for the specific options of the audio type, and get their values from the post meta */
		foreach ( $options as $key => $sub_options ) {
			if ( $this->type === Thrive_Audio_Post_Format_Main::CUSTOM && $key === 'url' ) {
				$prefixed_name = '_' . Thrive_Audio_Post_Format_Main::AUDIO_META_PREFIX . '_file';
			} else {
				$prefixed_name = '_' . Thrive_Audio_Post_Format_Main::AUDIO_META_PREFIX . '_' . $this->type . '_' . $key;
			}

			$value = get_post_meta( $this->post_id, $prefixed_name, true );

			/* if the post meta value is empty, use the default value */
			$options[ $key ]['value'] = empty( $value ) ? $sub_options['default'] : $value;
		}

		return $options;
	}

	/**
	 * Save the options from the meta page.
	 *
	 * @param $post_data
	 */
	public function save_options( $post_data ) {
		$audio_options = [];

		foreach ( $this->get_defaults() as $key => $options ) {
			$audio_options[ $key ] = [];

			$prefixed_name                  = Thrive_Audio_Post_Format_Main::AUDIO_META_PREFIX . '_' . $this->type . '_' . $key;
			$audio_options[ $key ]['value'] = isset( $post_data[ $prefixed_name ] ) ? $post_data[ $prefixed_name ] : '';
		}
		$options = [
			'type'          => $this->type,
			'audio_options' => $audio_options,
		];

		update_post_meta( $this->post_id, Thrive_Audio_Post_Format_Main::AUDIO_META_OPTION, $options );
	}

	/**
	 * Get backend formated audio otpions to be localized in page
	 *
	 * @return array
	 */
	public function get_audio_options() {
		$video_options = [];
		$meta_options  = $this->get_audio_options_meta();
		foreach ( $this->get_defaults() as $key => $options ) {
			$video_options[ $key ] = $meta_options[ $key ]['value'];
		}

		return $video_options;
	}

	/**
	 * Renders the audio of the given type by delegating the render to that class.
	 *
	 * @return mixed
	 */
	public abstract function render();

	/**
	 * Get default values for settings, labels, input types, placeholders, etc.
	 *
	 * @return mixed
	 */
	public abstract function get_defaults();

	/**
	 * Render meta settings on the add new post / edit post page.
	 *
	 * @return mixed
	 */
	public abstract function render_options();

	/**
	 * Process data before save to match backwards comaptible format
	 *
	 * @param $params
	 * @param $type
	 *
	 * @return array
	 */
	public function process_options( $params, $type ) {
		$audio_options = [];
		foreach ( $this->get_defaults() as $key => $options ) {
			$param_name                      = empty( $options['alias'] ) ? strtolower( $key ) : $options['alias'];
			$processed_key                   = Thrive_Audio_Post_Format_Main::AUDIO_META_PREFIX . '_' . $type . '_' . $key;
			$audio_options[ $processed_key ] = isset( $params[ $param_name ] ) ? $params[ $param_name ] : $options['default'];
			if ( ! empty( $options['inverted'] ) ) {
				$audio_options[ $processed_key ] = empty( $audio_options[ $processed_key ] ) ? 1 : 0;
			}
		}

		return $audio_options;
	}
}

/**
 * @param $type
 * @param $post_id
 *
 * @return Thrive_Audio_Post_Format
 */
function thrive_audio_post_format( $type, $post_id = null ) {
	return Thrive_Audio_Post_Format::factory( $type, $post_id );
}
