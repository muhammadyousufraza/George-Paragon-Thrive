<?php

namespace TCB\inc\helpers;

/**
 * Class FormSettings
 *
 * @property-read array $apis        configuration for the api connections
 * @property-read int   $captcha     whether or not captcha is enabled (1 / 0)
 * @property-read array $extra       extra configuration for each api connection
 * @property-read array $custom_tags array of custom tags configuration (from radio, checkbox, dropdown)
 *
 * @package TCB\inc\helpers
 */
class FormSettings {
	public $ID;

	public $post_title;

	const POST_TYPE = '_tcb_form_settings';

	const SEP = '__TCB_FORM__';

	protected $config = [];

	/**
	 * Default configuration for forms
	 *
	 * @var array
	 */
	public static $defaults = [
		'apis'            => [],
		'captcha'         => 0,
		'extra'           => [],
		'custom_tags'     => [],
		'form_identifier' => '',
		'inputs'          => [],
	];

	public function __construct( $config ) {
		$this->set_config( $config );
	}

	/**
	 * Setter for config
	 *
	 * @param array|string $config
	 *
	 * @return $this
	 */
	public function set_config( $config ) {
		if ( is_string( $config ) ) {
			$config = json_decode( $config, true );
		}
		$this->config = wp_parse_args( $config, static::$defaults );

		return $this;
	}

	/**
	 * Get the configuration
	 *
	 * @param bool $json get it as JSON
	 *
	 * @return array|false|string
	 */
	public function get_config( $json = true ) {
		return $json ? json_encode( $this->config ) : $this->config;
	}

	/**
	 * Magic config getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$default = isset( static::$defaults[ $name ] ) ? static::$defaults[ $name ] : null;

		return isset( $this->config[ $name ] ) ? $this->config[ $name ] : $default;
	}

	/**
	 * Loads a file config from an ID, or directly with a configuration array
	 *
	 * @param string|int|null|array $id          if array, it will act as a config. If empty, return a new instance with default settings
	 * @param int                   $post_parent if sent, it will also make sure the instance has the same post parent as this
	 *
	 * @return static
	 */
	public static function get_one( $id = null, $post_parent = null ) {
		if ( is_array( $id ) ) {
			$config = $id;
			$id     = null;
		} elseif ( $id && is_numeric( $id ) ) {
			$id                  = (int) $id;
			$post                = get_post( $id );
			$post_parent_matches = true;
			if ( $post && $post_parent ) {
				$post_parent_matches = $post_parent && ( (int) $post_parent === (int) $post->post_parent );
			}
			if ( $post && $post->post_type === static::POST_TYPE && $post_parent_matches ) {
				$config = json_decode( $post->post_content, true );
			}
		}

		if ( empty( $config ) ) {
			$id     = null;
			$config = [];
		}

		$instance     = new static( $config );
		$instance->ID = $id;

		return $instance;
	}

	/**
	 * Save a File config to db
	 *
	 * @param string $post_title name to give to the post that's being saved
	 * @param array  $post_data  extra post data to save
	 *
	 * @return static|\WP_Error
	 */
	public function save( $post_title, $post_data = null ) {
		/* preserve new lines for email fields */
		array_walk_recursive( $this->config, function ( &$val, $key ) {
			if ( is_string( $val ) && strpos( $key, 'email' ) !== false ) {
				$val = nl2br( $val );
				$val = preg_replace( "/[\n]+/", "", $val );
			}
		} );

		$content = wp_json_encode( $this->config );
		$content = wp_slash( $content );

		$save_data = [
			'post_type'    => static::POST_TYPE,
			'post_title'   => $post_title,
			'post_content' => $content,
		];
		if ( is_array( $post_data ) ) {
			$save_data += $post_data;
		}
		remove_all_filters( 'wp_insert_post_data' );
		remove_all_actions( 'edit_post' );
		remove_all_actions( 'save_post' );
		remove_all_actions( 'wp_insert_post' );
		if ( $this->ID ) {
			$save_data['ID'] = $this->ID;
			$post_id         = wp_update_post( $save_data );
		} else {
			$post_id = wp_insert_post( $save_data );
		}
		$this->ID = $post_id;

		return is_wp_error( $post_id ) ? $post_id : $this;
	}

	/**
	 * Delete the current instance
	 */
	public function delete() {
		if ( $this->ID ) {
			wp_delete_post( $this->ID );
		}

		return $this;
	}

	/**
	 * Build the regex pattern for matching form json configuration
	 *
	 * @param bool $with_attribute whether or not to also match the `data-form-settings` attribute
	 *
	 * @return string
	 */
	public static function pattern( $with_attribute = false ) {
		$regex = static::SEP . '(.+?)' . static::SEP;

		if ( $with_attribute ) {
			$regex = ' data-form-settings="' . $regex . '"';
		}

		return "#{$regex}#s";
	}

	/**
	 * Populate the $data that's sent to autoresponder based on stored settings for the form
	 *
	 * @param array $data
	 */
	public function populate_request( &$data ) {
		/* mark the current request data as trusted */
		$data['$$trusted'] = true;

		/* captcha */
		$data['_use_captcha'] = (int) $this->captcha;

		/* add form identifier to keep track from which form the data is coming */
		$data['form_identifier'] = ! empty( trim( $this->form_identifier ) ) ? trim( $this->form_identifier ) : '';

		/* build custom tags list based on user-submitted values and form settings - these are set for radio, checkbox and dropdown form elements */
		$taglist = [];
		foreach ( $this->custom_tags as $field_name => $all_tags ) {
			if ( ! isset( $data[ $field_name ] ) ) {
				/* no POST data has been sent in $field_name - no tag associated*/
				continue;
			}
			$value_as_array = is_array( $data[ $field_name ] ) ? $data[ $field_name ] : [ $data[ $field_name ] ];
			foreach ( $value_as_array as $submitted_value ) {
				if ( isset( $all_tags[ $submitted_value ] ) ) {
					$taglist[] = str_replace( [ '"', "'" ], '', trim( $all_tags[ $submitted_value ] ) );
				}
			}
		}
		$taglist         = implode( ',', array_filter( $taglist ) );
		$has_custom_tags = ! empty( $taglist );

		/* extra data for each api */
		foreach ( $this->extra as $api_key => $data_array ) {
			foreach ( $data_array as $field => $value ) {
				parse_str( $field, $parsed_field );
				/* parse array fields that are stored flat "field[custom_field]":"value"  */
				if ( is_array( $parsed_field ) && ! empty( $parsed_field ) ) {
					$key = array_keys( $parsed_field )[0];

					if ( is_array( $parsed_field[ $key ] ) && ! empty( $parsed_field[ $key ] ) ) {
						$second_key = array_keys( $parsed_field[ $key ] )[0];

						$data[ $api_key . '_' . $key ][ $second_key ] = $value;
					} else {
						$data[ $api_key . '_' . $key ] = $value;
					}
				} else {
					$data[ $api_key . '_' . $field ] = $value;
				}
			}
			$tags_key = $api_key . '_tags';
			if ( isset( $data[ $tags_key ] ) ) {
				/* append any tags from radio/checkboxes/dropdowns */
				if ( $has_custom_tags ) {
					$data[ $tags_key ] = trim( $data[ $tags_key ] . ',' . $taglist, ',' );
				}

				/**
				 * Filter the final list of tags that gets sent to the API
				 *
				 * @param string $tags    list of tags, separated by comma
				 * @param string $api_key API connection identifier
				 *
				 * @return array
				 */
				$data[ $tags_key ] = apply_filters( 'tcb_form_api_tags', $data[ $tags_key ], $api_key );
			}
		}
	}

	/**
	 * On duplicate we need to re save the form settings on a new entry
	 */
	public static function save_form_settings_from_duplicated_content( $content, $post_parent ) {
		/* pattern used to find the settings id from the content */
		$pattern = '/data-settings-id="(.+?)"/';

		/* Find if we have in content a form by searching for it's form settings id */
		preg_match_all( $pattern, $content, $matches );

		/* If we find a match we need to generate another entry for post settings and replace the new id in the content */
		if ( ! empty( $matches[1] ) ) {

			$forms = [];

			if ( is_array( $matches[1] ) ) {
				foreach ( $matches[1] as $form_settings_id ) {
					$form_settings_instance = self::get_one( $form_settings_id, $post_parent );
					$forms[]                = $form_settings_instance->get_form_settings_array();
				}
			} else {
				$form_settings_id       = $matches[1];
				$form_settings_instance = self::get_one( $form_settings_id, $post_parent );
				$forms[]                = $form_settings_instance->get_form_settings_array();
			}

			$replaced = tve_save_form_settings( $forms, $post_parent );

			foreach ( $replaced as $old_id => $new_id ) {
				$old_id = (int) $old_id;

				$content = preg_replace( "/data-settings-id=\"$old_id\"/", "data-settings-id=\"$new_id\"", $content );
			}

		}

		return $content;
	}

	/**
	 * Returns an array with the settings of a form, but without it's original ID so we can use this array to regenerate and save the settings for a cloned form
	 *
	 * @return array
	 */
	public function get_form_settings_array() {
		/* We need to change the original ID so we won't find the initial instance of the settings, but to  create a new one */
		$temporary_id = $this->ID . '_temporary';

		return [
			'id'       => $temporary_id,
			'settings' => $this->get_config(),
		];
	}
}
