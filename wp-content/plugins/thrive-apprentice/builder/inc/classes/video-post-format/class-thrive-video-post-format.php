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
 * Class Thrive_Video_Post_Format
 */
abstract class Thrive_Video_Post_Format {

	public $type;
	public $post_id;

	/**
	 * Factory for video classes.
	 *
	 * @param string $type
	 *
	 * @return Thrive_Video_Post_Format
	 */
	public static function factory( $type, $post_id = null ) {
		$video_class = "Thrive_Video_Post_{$type}";

		/* only do something if the class exists */
		if ( ! empty( $type ) && class_exists( $video_class, false ) ) {
			return new $video_class( $type, $post_id );
		}

		return null;
	}

	/**
	 * Thrive_Video_Post_Format constructor.
	 *
	 * @param $type
	 * @param $post_id
	 */
	public function __construct( $type, $post_id = null ) {
		$this->type    = $type;
		$this->post_id = $post_id ? $post_id : get_the_ID();
	}

	public function get_general_defaults() {
		$defaults = [
			'aspect-ratio'         => [
				'value'   => '',
				'type'    => '',
				'default' => '16:9',
			],
			'aspect-ratio-default' => [
				'value'   => '',
				'type'    => '',
				'default' => '0',
			],
			'float'                => [
				'value'   => '',
				'type'    => '',
				'default' => 'false',
			],
			'float-padding1-d'     => [
				'value'   => '',
				'type'    => '',
				'default' => '25px',
			],
			'float-padding2-d'     => [
				'value'   => '',
				'type'    => '',
				'default' => '25px',
			],
			'float-position'       => [
				'value'   => '',
				'type'    => '',
				'default' => 'top-left',
			],
			'float-visibility'     => [
				'value'   => '',
				'type'    => '',
				'default' => 'mobile',
			],
			'float-width-d'        => [
				'value'   => '',
				'type'    => '',
				'default' => '300px',
			],
			'is-dynamic'           => [
				'value'   => true,
				'type'    => '',
				'default' => true,
			],
			'is-floating'          => [
				'value'   => '',
				'type'    => '',
				'default' => '0',
			],
			'float-close'          => [
				'value'   => '',
				'type'    => '',
				'default' => false,
			],
		];

		return $defaults;
	}

	/**
	 * Get the meta for a video type. If a key is provided, return only the value for that key.
	 *
	 * @param $key
	 *
	 * @return array|mixed
	 */
	public function get_video_options_meta( $key = '' ) {
		$options = get_post_meta( $this->post_id, Thrive_Video_Post_Format_Main::VIDEO_META_OPTION, true );

		/* if the options are empty, try looking for the settings from the old themes */
		if ( empty( $options ) ) {
			$options = $this->compat_get_video_options_meta();
		} else {
			$defaults = $this->get_defaults();
			/* if the type from the options does not match the current video type, return the defaults */
			if ( empty( $options['type'] ) || ( $options['type'] !== $this->type ) ) {
				return $defaults;
			}

			$options = $options['video_options'];

			/* merge the values with the other fields from the defaults */
			foreach ( $defaults as $option_key => $sub_options ) {
				/* if the saved value is empty or is not set, use the default value */
				$defaults[ $option_key ]['value'] = empty( $options[ $option_key ]['value'] ) ? $sub_options['default'] : $options[ $option_key ]['value'];
			}
			$options = $defaults;
		}

		/* if a key is set, return only that option */
		if ( isset( $options[ $key ] ) ) {
			$options = $options[ $key ];
		}

		return $options;
	}

	/**
	 * Get the video settings saved from the old themes. If there are no settings saved, use the default values.
	 *
	 * @return mixed
	 */
	public function compat_get_video_options_meta() {
		$options = $this->get_defaults();

		/* generate the keys for the specific options of the video type, and get their values from the post meta */
		foreach ( $options as $key => $sub_options ) {
			$prefixed_name = '_' . Thrive_Video_Post_Format_Main::VIDEO_META_PREFIX . '_' . $this->type . '_' . $key;
			$value         = get_post_meta( $this->post_id, $prefixed_name, true );

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
		$video_options = [];

		foreach ( $this->get_defaults() as $key => $options ) {
			$video_options[ $key ] = [];

			$prefixed_name = Thrive_Video_Post_Format_Main::VIDEO_META_PREFIX . '_' . $this->type . '_' . $key;

			$value = '';
			if ( isset( $post_data[ $prefixed_name ] ) ) {

				switch ( $key ) {
					case 'url':
						$value = $this->sanitize_url( $post_data[ $prefixed_name ] );
						break;
					case 'start_time_minutes':
					case 'start_time_seconds':
						$value = (int) $post_data[ $prefixed_name ];
						break;
					case 'aspect-ratio':
					case 'aspect-ratio-default':
					case 'float':
					case 'float-padding1-d':
					case 'float-padding2-d':
					case 'float-position':
					case 'float-visibility':
					case 'float-width-d':
					case 'is-dynamic':
					case 'is-floating':
						$value = $post_data[ $prefixed_name ];
						break;
					case 'float-close':
						$value = empty( $post_data[ $prefixed_name ] ) || $post_data[ $prefixed_name ] === 'false' ? 0 : 1;
						break;
					default:
						/* those are just checkboxes */
						$value = empty( $post_data[ $prefixed_name ] ) ? 0 : 1;
						break;
				}
			}

			$video_options[ $key ]['value'] = $value;
		}
		$options = [
			'type'          => $this->type,
			'video_options' => $video_options,
		];

		update_post_meta( $this->post_id, Thrive_Video_Post_Format_Main::VIDEO_META_OPTION, $options );
	}

	/**
	 * Escape the video URL.
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function sanitize_url( $url ) {
		return esc_url( trim( $url ) );
	}

	/**
	 * Return the video start time to be used in a query string.
	 * Depending on the $return_type variable, this can return the time either as an int, in seconds, or as a string, in the '7m21s' format.
	 *
	 * @param        $options
	 * @param string $return_type
	 *
	 * @return int|string
	 */
	public static function get_start_time( $options, $return_type = 'string' ) {
		$minutes = empty( $options['start_time_minutes']['value'] ) ? 0 : (int) $options['start_time_minutes']['value'];
		$seconds = empty( $options['start_time_seconds']['value'] ) ? 0 : (int) $options['start_time_seconds']['value'];

		if ( $return_type === 'int' ) {
			/* calculate the start time ( 60 * min + s ) */
			$time = $minutes * 60 + $seconds;
		} else {
			$time = '';
			/* format: 1m2s */
			if ( ! empty( $minutes ) ) {
				$time = $minutes . 'm';
			}
			if ( ! empty( $seconds ) ) {
				$time .= $seconds . 's';
			}
		}

		return $time;
	}

	/**
	 * Renders the video of the given type by delegating the render to that class.
	 *
	 * @param $has_thumbnail
	 * @param $main_attr
	 *
	 * @return mixed
	 */
	public abstract function render( $has_thumbnail, $main_attr );

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
		$video_options = [];
		foreach ( $this->get_defaults() as $key => $options ) {
			$param_name                      = empty( $options['alias'] ) ? $key : $options['alias'];
			$processed_key                   = Thrive_Video_Post_Format_Main::VIDEO_META_PREFIX . '_' . $type . '_' . $key;
			$video_options[ $processed_key ] = isset( $params[ $param_name ] ) ? $params[ $param_name ] : $options['default'];
			if ( ! empty( $options['inverted'] ) ) {
				$video_options[ $processed_key ] = empty( $video_options[ $processed_key ] ) ? 1 : 0;
			}
		}

		return $video_options;
	}

	/**
	 * Process data in order to be localized on page
	 *
	 * @return array
	 */
	public function get_video_options() {
		$video_options = [];
		$meta_options  = $this->get_video_options_meta();
		foreach ( $this->get_defaults() as $key => $options ) {
			$param_name                   = empty( $options['alias'] ) ? $key : $options['alias'];
			$video_options[ $param_name ] = $meta_options[ $key ]['value'];
			if ( ! empty( $options['inverted'] ) ) {
				$video_options[ $param_name ] = empty( $video_options[ $param_name ] ) ? 1 : 0;
			}

		}

		return $video_options;
	}

	public function parse_video_attributes( $video_attrs ) {
		return $video_attrs;
	}

}

/**
 * @param $type
 * @param $post_id
 *
 * @return Thrive_Video_Post_Format
 */
function thrive_video_post_format( $type, $post_id = null ) {
	return Thrive_Video_Post_Format::factory( $type, $post_id );
}
