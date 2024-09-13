<?php

use TVA\Access\Expiry\Base;
use TVA\Product;

/**
 * Class TVA_Access_Restriction
 *
 */
class TVA_Access_Restriction implements JsonSerializable {

	/**
	 * Post type to be used for storing TAr content
	 */
	const POST_TYPE    = 'tva-acc-restriction'; // must be max 20 chars
	const REDIRECT_IDS = 'tva_redirect_ids'; // db key for option that holds redirect IDs for custom redirect pages - used to check TAr editing access

	/**
	 * Key to be used for the global option or for individual course meta
	 */
	const DB_KEY_NAME = 'tva_access_restriction';

	protected static $INSTANCES = [];

	/**
	 * Holds settings data for this instance
	 *
	 * @var array[]
	 */
	protected $data = [
		'not_logged'            => [],
		'not_purchased'         => [],
		'locked'                => [],
		'custom'                => [],
		'action_button_display' => [],
	];

	/**
	 * Stores the original data (the version from the database)
	 *
	 * @var array
	 */
	protected $original_data = [];

	/**
	 * Defaults for a configurable scope ( "not_logged" or "not_purchased" )
	 *
	 * @return array
	 */
	public static $defaults = [
		'option'          => 'content',
		'content'         => [
			'title'   => '__original',
			'post_id' => 0, // ID of post editable with TAr
		],
		'redirect_login'  => [
			'state'            => 'login',
			'content_redirect' => true,
		],
		'redirect_custom' => [
			'post_id' => 0,
		],
	];

	/**
	 * Get the available options for setting up the "Restricted access" behaviour
	 *
	 * @return array
	 */
	public static function get_possible_options() {
		return [
			'inherit'         => __( 'Default site behaviour', 'thrive-apprentice' ),
			'message'         => __( 'Display message (deprecated)', 'thrive-apprentice' ),
			'content'         => __( 'Display custom content', 'thrive-apprentice' ),
			'redirect_login'  => __( 'Redirect to login & registration page', 'thrive-apprentice' ),
			'redirect_custom' => __( 'Redirect to custom page', 'thrive-apprentice' ),
			'show_login_form' => __( 'Display login form (deprecated)', 'thrive-apprentice' ),
			'call_to_action'  => __( 'Display call to action button', 'thrive-apprentice' ),
			'buy_action'      => __( 'Display buy button', 'thrive-apprentice' ),
		];
	}

	/**
	 * Load from db
	 *
	 * @return TVA_Access_Restriction
	 */
	public function load() {
		$this->data = $this->_do_load();

		if ( empty( $this->data ) ) {
			$this->data = [];
		}

		if ( empty( $this->data['not_purchased'] ) ) {
			$this->data['not_purchased'] = static::$defaults;
		}

		if ( empty( $this->data['not_logged'] ) ) {
			$this->data['not_logged'] = static::$defaults;
			$this->ensure_backwards_compatibility();
		}

		if ( empty( $this->data['locked'] ) ) {
			$this->data['locked'] = static::$defaults;
		}

		if ( empty( $this->data['custom'] ) ) {
			$this->data['custom'] = [];
		}

		if ( empty( $this->data['action_button_display'] ) ) {
			$this->data['action_button_display'] = [
				'option' => 'call_to_action',
			];
		}

		$this->original_data = $this->data;

		return $this;
	}

	/**
	 * Load actual data from the database
	 *
	 * @return false|mixed|void
	 */
	protected function _do_load() {
		return get_option( static::DB_KEY_NAME );
	}

	/**
	 * Solve some backwards-compat issues:
	 *
	 * If "Show login form" was checked, make sure the settings reflect that and delete the old option
	 */
	protected function ensure_backwards_compatibility() {
		$old_show_login_form = tva_get_settings_manager()->factory( 'loginform' );

		if ( $old_show_login_form->get_value() ) {
			$this->data['not_logged']['option'] = 'show_login_form';
			$this->save();
			$old_show_login_form->delete();
			/* also delete the previously saved option from TA1 */
			delete_option( 'tva_loginform' );
		}
	}

	/**
	 * @param null $product_id
	 *
	 * @return TVA_Access_Restriction
	 */
	public static function instance_factory( $product_id = null ) {
		$instance_key = empty( $product_id ) ? '_global' : $product_id;

		if ( ! isset( self::$INSTANCES[ $instance_key ] ) ) {
			if ( ! empty( $product_id ) ) {
				$instance = new TVA_Product_Access_Restriction( $product_id );
			} else {
				$instance = new TVA_Access_Restriction();
			}
			$instance->load();

			self::$INSTANCES[ $instance_key ] = $instance;
		}

		return self::$INSTANCES[ $instance_key ];
	}

	/**
	 * "Deep" set a value. $prop can contain "." :: ->set( 'field1.field2', 'value' ) translates into this->data['field1']['field2'] = 'value'
	 *
	 * @param string|array $prop
	 * @param mixed        $value
	 *
	 * @return TVA_Access_Restriction
	 */
	public function set( $prop, $value = null ) {
		if ( is_array( $prop ) ) {
			//full data setter
			$this->data = $prop;

			return $this;
		}

		return $this->handle_deep_set( $prop, $value );
	}

	/**
	 * @param string|array $prop
	 * @param mixed        $value
	 *
	 * @return TVA_Access_Restriction
	 */
	public function handle_deep_set( $prop, $value = null ) {
		$parts  = explode( '.', $prop );
		$target = &$this->data;

		while ( count( $parts ) > 1 ) {
			$field = array_shift( $parts );
			if ( ! is_array( $target ) || ! array_key_exists( $field, $target ) ) {
				$target[ $field ] = [];
			}
			$target = &$target[ $field ];
		}

		$target[ $parts[0] ] = $value;

		return $this;
	}

	/**
	 * Deep-getter - expands "." notation from $prop a.b.c => $this->data['a']['b']['c']
	 *
	 * @param string $prop
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get( $prop = null, $default = null ) {
		if ( ! $prop ) {
			return $this->data;
		}

		$value = $this->data;
		$parts = explode( '.', (string) $prop );

		foreach ( $parts as $field ) {
			if ( ! is_array( $value ) || ! array_key_exists( $field, $value ) ) {
				return $default;
			}
			$value = $value[ $field ];
		}

		return $value;
	}

	/**
	 * Persist settings to the database
	 *
	 * @return TVA_Access_Restriction
	 */
	public function save() {
		$this->_do_save( [
			'not_logged'            => $this->trim_data( $this->data['not_logged'] ),
			'not_purchased'         => $this->trim_data( $this->data['not_purchased'] ),
			'locked'                => $this->trim_data( $this->data['locked'] ),
			'action_button_display' => $this->trim_data( $this->data['action_button_display'] ),
		] );
		$this->ensure_redirect_ids();

		delete_post_meta_by_key( 'tva_temporary_redirect' );

		return $this;
	}

	/**
	 * Makes sure all the pages set as "custom_redirect" are marked correctly, so that we can allow editing those with TAr
	 */
	public function ensure_redirect_ids() {
		/**
		 * array_filter preserves array keys
		 */
		$redirect_ids = array_filter( get_option( static::REDIRECT_IDS, [] ) );

		/* ensure custom redirect post is marked accordingly */
		foreach ( [ 'not_logged', 'not_purchased', 'locked' ] as $scope ) {
			$scope_key = $this->get_scope_key( $scope );
			$data      = $this->data[ $scope ];
			$prev_data = isset( $this->original_data[ $scope ] ) ? $this->original_data[ $scope ] : [];
			if ( empty( $prev_data ) ) {
				$prev_data = [ 'option' => 'content', 'post_id' => 0 ];
			}
			if ( $data['option'] === 'redirect_custom' && ! empty( $data['redirect_custom']['post_id'] ) ) {
				$post_id = $data['redirect_custom']['post_id'];
				if ( empty( $redirect_ids[ $post_id ][ $scope_key ] ) ) {
					$redirect_ids[ $post_id ][ $scope_key ] = true;
					$save_redirect                          = true;
				}

				if ( $prev_data['option'] === 'redirect_custom' && (int) $prev_data['redirect_custom']['post_id'] !== (int) $data['redirect_custom']['post_id'] ) {
					$prev_post_id = $prev_data['redirect_custom']['post_id'];
					unset( $redirect_ids[ $prev_post_id ][ $scope_key ] );
					$save_redirect = true;
				}
			} elseif ( ! empty( $prev_data['redirect_custom']['post_id'] ) ) {
				unset( $redirect_ids[ $prev_data['redirect_custom']['post_id'] ][ $scope_key ] );
				$save_redirect = true;
			}
		}

		if ( isset( $save_redirect ) ) {
			update_option( static::REDIRECT_IDS, array_filter( $redirect_ids ) );
		}
	}

	protected function _do_save( $data ) {
		update_option( static::DB_KEY_NAME, $data );
	}

	/**
	 * Remove any unnecessary fields from data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function trim_data( $data ) {
		$opt     = $data['option'];
		$trimmed = [ 'option' => $opt ];
		if ( ! empty( $data[ $opt ] ) ) {
			$trimmed[ $opt ] = $data[ $opt ];
		}

		// make sure no post details are saved
		if ( isset( $trimmed[ $opt ]['post'] ) ) {
			unset( $trimmed[ $opt ]['post'] );
		}

		return $trimmed;
	}

	/**
	 * Handle admin localization.
	 * For certain options, additional data is needed, such as post titles, edit links etc
	 *
	 * @return array[]
	 */
	public function admin_localize() {

		/**
		 * Include some extra data in the localization array
		 */
		foreach ( [ 'not_logged', 'not_purchased', 'locked', 'action_button_display' ] as $field ) {
			$data = &$this->data[ $field ];
			if ( ! empty( $data['option'] ) && $data['option'] === 'redirect_custom' && ! empty( $data['redirect_custom']['post_id'] ) ) {
				$data['redirect_custom']['post'] = new TVA_Page_Setting( 'custom_redirect', 'general', $data['redirect_custom']['post_id'] );
			}
			if ( $data['option'] === 'content' ) {
				$post_id                 = isset( $data['content']['post_id'] ) ? $data['content']['post_id'] : 0;
				$data['content']['post'] = new TVA_Page_Setting( 'content_post_' . $field, 'general', $post_id );
			}
			if ( $data['option'] === 'redirect_custom' && empty( $data['redirect_custom']['post_id'] ) ) {
				$data['redirect_custom']['post'] = [
					'category'     => 'custom_link',
					'edit_url'     => '',
					'edit_with_wp' => '',
					'name'         => 'custom_redirect',
					'preview_url'  => isset( $data['redirect_custom']['custom_link'] ) ? $data['redirect_custom']['custom_link'] : '',
					'title'        => isset( $data['redirect_custom']['title'] ) ? $data['redirect_custom']['title'] : '',
					'value'        => - 1,
				];
			}
		}

		return $this->data;
	}

	/**
	 * Make sure that every needed piece of data exists
	 *
	 * @param int|null $custom_id
	 *
	 * @return TVA_Access_Restriction
	 */
	public function ensure_data_exists( $custom_id = null ) {
		foreach ( [ 'not_logged', 'not_purchased', 'locked' ] as $field ) {
			$option = empty( $this->data[ $field ]['option'] ) ? '' : $this->data[ $field ]['option'];
			if ( $option === 'content' ) {
				/* make sure a post exists that should hold the content */
				$post_id                                    = empty( $this->data[ $field ]['content']['post_id'] ) ? null : $this->data[ $field ]['content']['post_id'];
				$this->data[ $field ]['content']['post_id'] = $this->ensure_content_post( $this->get_scope_key( $field ), $field, $post_id );
				if ( empty( $post_id ) ) {
					$this->save(); // if this was not previously set, save it now.
				}
			} else {
				$this->data[ $field ]['content']['post_id'] = 0;
			}
			$this->data[ $field ]['content'] += self::$defaults['content'];
		}
		$this->ensure_redirect_ids();

		return $this;
	}

	/**
	 * Get the scope meta key for the post holding custom content
	 *
	 * @param string $scope
	 *
	 * @return string
	 */
	protected function get_scope_key( $scope ) {
		return $scope;
	}

	/**
	 * Ensure a post exists for the $scope
	 *
	 * @param string $scope
	 * @param null   $existing_id
	 *
	 * @return int|WP_Error
	 */
	protected function ensure_content_post( $scope_key, $scope, $existing_id = null ) {
		$post = get_post( $existing_id );

		if ( ! $post || $post->post_type !== static::POST_TYPE ) {
			/* 1. search for an existing post that might have been lost */
			$variants = get_posts( [
				'post_type'   => static::POST_TYPE,
				'meta_key'    => 'tva_content_for',
				'meta_value'  => $scope_key,
				'post_status' => [ 'draft', 'publish' ],
			] );

			if ( ! empty( $variants ) ) {
				$post = $variants[0];
			}
		}

		/* if nothing could be found, need to create a new post */
		if ( empty( $post ) ) {
			$post_id = wp_insert_post( [
				'post_type'  => static::POST_TYPE,
				'post_title' => 'Restriction for ' . $scope_key,
			] );
			update_post_meta( $post_id, 'tva_content_for', $scope_key );
			/* save the default TAr html + css */
			update_post_meta( $post_id, 'tve_updated_post', tva_get_file_contents( "templates/access-restriction/{$scope}/template.php" ) );
			update_post_meta( $post_id, 'tve_custom_css', tva_get_file_contents( "templates/access-restriction/{$scope}/style.php" ) );
		} else {
			$post_id = $post->ID;
		}

		return $post_id;
	}

	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		return $this->data;
	}

	public function delete() {
		// this can never be deleted
	}

	/**
	 * Handles the case when user doesn't have access to the content
	 *
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function output_restricted_access( $echo = true, $scope = 'not_logged' ) {
		$output = $this->handle_output_restricted_access( $scope );

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * outputs or returns the restriction content
	 *
	 * @param string|null $scope
	 *
	 * @return string
	 */
	public function handle_output_restricted_access( $scope = null ) {
		if ( is_null( $scope ) || $scope === 'not_purchased' ) {
			$scope = is_user_logged_in() ? 'not_purchased' : 'not_logged';
		}

		$settings = $this->get_applicable_settings( $scope );

		$option = $settings['option'];
		$output = '';

		switch ( $option ) {
			case 'message':
			case 'show_login_form':
				$output = isset( $settings['message'] ) ? ( '<div>' . $settings['message'] . '</div>' ) : '';
				/* backwards compat:: show login form if the option is enabled */
				$global_option = tva_access_restriction_settings()->get( "{$scope}.option" );

				if ( $global_option === 'show_login_form' || empty( $settings['message'] ) ) {
					$output .= tva_get_file_contents( 'templates/template_1/errors.php' );
					$output .= tva_login_form();
				}
				break;
			case 'content':
				$output = $this->get_tar_content( empty( $settings['content']['post_id'] ) ? 0 : $settings['content']['post_id'] );
				break;
			default:
				break;
		}

		return $output;
	}

	/**
	 * @param mixed $post_id
	 *
	 * @return string
	 */
	public function get_tar_content( $post_id ) {
		if ( empty( $post_id ) ) {
			return '';
		}

		global $post;
		$saved = $post;
		$post  = get_post( $post_id );

		$html = tve_editor_content( '' );
		ob_start();
		tve_load_custom_css( $post_id );
		TCB\Lightspeed\JS::get_instance( $post_id )->load_modules();
		$css = ob_get_contents();
		ob_end_clean();

		/* restore global $post */
		$post = $saved;

		return $css . do_shortcode( $html );
	}

	/**
	 * Checks if a page identified by $page_id has been set as a "Custom redirect" option from global settings or for a course
	 *
	 * @param int|string $page_id
	 *
	 * @return bool
	 */
	public static function is_custom_redirect_page( $page_id ) {
		$redirect_ids = get_option( static::REDIRECT_IDS, [] );

		return ! empty( $redirect_ids[ $page_id ] ) || (int) get_post_meta( $page_id, 'tva_temporary_redirect', true ) === 1;
	}

	/**
	 * Mark a page as a custom redirect option
	 * Sets a post_meta field that will identify $page_id as being used in a "Custom redirect" setting
	 *
	 * @param int|string $page_id
	 */
	public static function mark_temporary_redirect_page( $page_id ) {
		update_post_meta( $page_id, 'tva_temporary_redirect', 1 );
	}

	/**
	 * Remove the meta setup for a custom redirect page
	 *
	 * @param int|string $page_id
	 */
	public static function remove_temporary_redirect_page( $page_id ) {
		delete_post_meta( $page_id, 'tva_temporary_redirect' );
	}

	/**
	 * Called on the 'template_redirect' wp hook when the current user (or visitor) does not have access to a course
	 * Handles redirection, or enqueueing TAr resources when the 'content' option is selected
	 *
	 */
	public function template_redirect( $scope = null ) {
		$settings = $this->get_applicable_settings( $scope );
		$this->handle_redirect( $settings );
	}

	/**
	 * Handles redirection, or enqueueing TAr resources when the 'content' option is selected
	 *
	 * @param $settings
	 */
	public function handle_redirect( $settings ) {
		if ( $settings['option'] === 'redirect_login' ) {
			$state         = isset( $settings['redirect_login']['state'] ) ? $settings['redirect_login']['state'] : 'login';
			$login_page_id = TVA_Page_Setting::get( 'login_page' );

			if ( ! empty( $login_page_id ) ) {
				$redirect_url = get_permalink( $login_page_id );
				if ( ! empty( $settings['redirect_login']['content_redirect'] ) ) {
					if ( ! empty( $_GET['ret'] ) && filter_var( $_GET['ret'], FILTER_VALIDATE_URL ) !== false ) {
						$redirect_url = add_query_arg( [ 'ret' => $_GET['ret'] ], $redirect_url );
					} else {
						$redirect_url = add_query_arg( 'ret', is_tax( TVA_Const::COURSE_TAXONOMY ) ? tva_course()->get_link() : get_permalink(), $redirect_url );
					}
				}
				wp_redirect( $redirect_url . '#tcb-' . $state );
				exit();
			}
		} elseif ( $settings['option'] === 'redirect_custom' ) {
			$post_id = isset( $settings['redirect_custom']['post_id'] ) ? $settings['redirect_custom']['post_id'] : 0;
			$url     = isset( $settings['redirect_custom']['custom_link'] ) ? $settings['redirect_custom']['custom_link'] : '';
			if ( ! empty( $post_id ) ) {
				wp_redirect( get_permalink( $post_id ) );
				exit();
			} else {
				wp_redirect( $url );
				exit();
			}
		} elseif ( $settings['option'] === 'content' ) {
			$post_id = isset( $settings['content']['post_id'] ) ? $settings['content']['post_id'] : 0;
			if ( $post_id ) {

				/**
				 * Special case for MemberMouse. (facepalm)
				 *
				 * If MemberMouse plugin is present it does a very strange redirect if the content is viewed as a guest
				 */
				if ( class_exists( 'MM_MembershipLevel' ) ) {
					remove_all_filters( 'template_redirect' );
				}

				// enqueue styles & scripts
				add_filter( 'tcb_overwrite_scripts_enqueue', '__return_true' );
				add_action( 'wp_head', static function () use ( $post_id ) {
					tve_load_custom_css( $post_id );
				}, 999 );
			}
		}
	}

	/**
	 * Get applicable settings for the current course
	 * If nothing set for the course, or the option is 'inherit', returns the global settings
	 *
	 * @return array
	 */
	public function get_applicable_settings( $scope = null ) {
		if ( is_null( $scope ) || $scope === 'not_purchased' ) {
			$scope = is_user_logged_in() ? 'not_purchased' : 'not_logged';
		}

		$global_settings = tva_access_restriction_settings()->get( $scope );
		$settings        = $this->get( $scope );

		if ( $settings['option'] === 'inherit' ) {
			$settings = $global_settings;
		}

		return $settings;
	}

	/**
	 * Outputs the current title taking into account the settings for this course
	 *
	 * @param string  $before
	 * @param string  $after
	 * @param boolean $echo
	 *
	 * @return string|void
	 */
	public function the_title( $before = '', $after = '', $echo = true, $scope = null ) {
		$title = '';

		if ( tva_access_manager()->is_object_locked( get_queried_object() ) && tva_access_manager()->has_access_to_object( get_queried_object() ) ) {
			$scope = 'locked';
		}

		$settings = $this->get_applicable_settings( $scope );

		if ( ! empty( $settings['option'] ) && $settings['option'] === 'content' && isset( $settings['content']['title'] ) && $settings['content']['title'] !== '__original' ) {
			if ( $settings['content']['title'] !== '__hide' ) {
				$title = $before . $settings['content']['title'] . $after;
			}
		} else {
			$title = the_title( $before, $after, false );
		}

		if ( $echo ) {
			echo $title;
		} else {
			return $title;
		}
	}
}

class TVA_Product_Access_Restriction extends TVA_Access_Restriction {
	/**
	 * Defaults for a configurable scope ( "not_logged", "not_purchased" or "locked" )
	 *
	 * @return array
	 */
	public static $defaults = [
		'option' => 'inherit',
	];

	protected $term_id;

	public function __construct( $product_id ) {
		$this->term_id = $product_id;
	}

	/**
	 * "Deep" set a value. $prop can contain "." :: ->set( 'field1.field2', 'value' ) translates into this->data['field1']['field2'] = 'value'
	 *
	 * @param string|array $prop
	 * @param mixed        $value
	 *
	 * @return TVA_Product_Access_Restriction
	 */
	public function set( $prop, $value = null ) {
		if ( is_array( $prop ) ) {
			foreach ( $prop as $scope => $setting ) {
				if ( $scope !== 'custom' ) {
					$this->data[ $scope ] = $setting;
				} else {
					foreach ( $setting as $index => $custom_rule ) {
						$updated_rule = [
							'option'                   => $custom_rule['option'],
							"{$custom_rule['option']}" => $custom_rule[ $custom_rule['option'] ],
							'conditions'               => $custom_rule['conditions'],
							'title'                    => $custom_rule['title'],
							'order'                    => $custom_rule['order'],
							'custom_id'                => $custom_rule['custom_id'],
						];

						$this->data['custom'][ $index ] = $updated_rule;
					}
				}
			}

			return $this;
		}

		return $this->handle_deep_set( $prop, $value );
	}

	/**
	 * Nothing needed here.
	 */
	protected function ensure_backwards_compatibility() {

	}

	/**
	 * Load actual data from the database
	 *
	 * @return false|mixed|void
	 */
	protected function _do_load() {
		$data = get_term_meta( $this->term_id, static::DB_KEY_NAME, true );
		if ( empty( $data ) ) {
			$data = [];
		}
		/* backwards compat -> get old restricted access message */
		$message = get_term_meta( $this->term_id, 'tva_message', true );
		if ( empty( $data['not_logged'] ) && $message ) {
			$data['not_logged'] = [
				'option'  => 'message',
				'message' => $message,
			];
		}
		if ( empty( $data['not_purchased'] ) && $message ) {
			$data['not_purchased'] = [
				'option'  => 'message',
				'message' => $message,
			];
		}

		return $data;
	}

	/**
	 * @param string $scope
	 *
	 * @return string
	 */
	protected function get_scope_key( $scope, $custom_id = null ) {
		if ( $scope === 'custom' ) {
			$scope = 'not_purchased';
		}

		if ( is_null( $custom_id ) ) {
			return $scope . '_' . $this->term_id;
		} elseif ( $custom_id === - 1 ) {
			return $scope . '_' . $this->term_id . '_tmp';
		} else {
			return $scope . '_' . $this->term_id . '_' . $custom_id;
		}
	}

	/**
	 * Save data to database
	 *
	 * @param $data
	 */
	protected function _do_save( $data ) {
		update_term_meta( $this->term_id, static::DB_KEY_NAME, $data );
	}

	/**
	 * Delete the settings for a course
	 */
	public function delete() {
		/*
		 * Delete the post storing TAr content to be displayed for this course
		 */
		foreach ( [ 'not_logged', 'not_purchased' ] as $scope ) {
			$posts = get_posts( [
				'post_type'   => static::POST_TYPE,
				'meta_key'    => 'tva_content_for',
				'meta_value'  => $this->get_scope_key( $scope ),
				'post_status' => [ 'draft', 'publish' ],
			] );
			if ( ! empty( $posts ) ) {
				wp_delete_post( $posts[0]->ID );
			}
		}

		delete_term_meta( $this->term_id, static::DB_KEY_NAME );
	}

	/**
	 * Called on the 'template_redirect' wp hook when the current user (or visitor) does not have access to a course
	 * Handles redirection, or enqueueing TAr resources when the 'content' option is selected
	 */
	public function template_redirect( $scope = null ) {

		$access_expiry_should_redirect = Base::access_expired_should_redirect( get_current_user_id(), tva_access_manager()->get_product() );

		if ( $access_expiry_should_redirect !== false ) {
			/**
			 * Access expiry should redirect check
			 */
			wp_redirect( $access_expiry_should_redirect );
			exit();
		}

		$settings = $this->get_applicable_settings( $scope );
		$this->handle_redirect( $settings );
	}

	/**
	 * Handles the case when user doesn't have access to the content
	 *
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function output_restricted_access( $echo = true, $scope = 'not_logged' ) {
		$output = $this->handle_output_restricted_access( $scope );

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Get applicable settings for the current course
	 * If nothing set for the course, or the option is 'inherit', returns the global settings
	 *
	 * @return array
	 */
	public function get_applicable_settings( $scope = null ) {
		if ( is_null( $scope ) || $scope === 'not_purchased' ) {
			$scope = is_user_logged_in() ? 'not_purchased' : 'not_logged';
		}

		if ( $scope === 'not_purchased' ) {
			$settings = $this->get_purchase_context_settings();
		} else {
			$settings = $this->get( $scope );
		}

		$global_settings = tva_access_restriction_settings()->get( $scope );

		if ( $settings['option'] === 'inherit' ) {
			$settings = $global_settings;
		}

		return $settings;
	}

	public function get_purchase_context_settings() {
		/* store a reference to the original product so it can be restored later */
		$original_product = tva_access_manager()->get_product();

		$scope             = 'not_purchased';
		$settings          = $this->get( $scope );
		$purchase_settings = $this->get( 'custom' );
		$user              = wp_get_current_user();
		$tva_user          = new TVA_User( get_current_user_id() );

		if ( ! empty( $purchase_settings ) ) {
			foreach ( $purchase_settings as $purchase_setting ) {
				$product_conditions_met = true;
				$role_conditions_met    = false;

				foreach ( $purchase_setting['conditions']['products'] as $product_id ) {
					tva_access_manager()->set_tva_user( $tva_user );
					tva_access_manager()->set_product( new Product( $product_id ) );

					if ( tva_access_manager()->check_rules() === false ) {
						$product_conditions_met = false;
						break;
					}
				}
				if ( ! empty( $purchase_setting['conditions']['roles'] ) ) {
					foreach ( $purchase_setting['conditions']['roles'] as $role ) {
						if ( in_array( $role, (array) $user->roles ) ) {
							$role_conditions_met = true;
							break;
						}
					}
				} else {
					$role_conditions_met = true;
				}

				if ( $product_conditions_met && $role_conditions_met ) {
					$settings = $purchase_setting;
					break;
				}
			}

			tva_access_manager()->set_product( $original_product );
		}

		return $settings;
	}

	/**
	 * Persist settings to the database
	 *
	 * @return TVA_Product_Access_Restriction
	 */
	public function save() {

		// remove data that shouldn't be there
		if ( ! empty( $this->data['custom'][ - 1 ] ) ) {
			unset( $this->data['custom'][ - 1 ] );
		} else if ( ! empty( $this->data['custom']['option'] ) ) {
			unset( $this->data['custom']['option'] );
		}

		foreach ( $this->data['custom'] as $index => $custom_setting ) {
			$this->data['custom'][ $index ] = $this->trim_custom_data( $custom_setting );
		}

		$this->_do_save( [
			'not_logged'            => $this->trim_data( $this->data['not_logged'] ),
			'not_purchased'         => $this->trim_data( $this->data['not_purchased'] ),
			'action_button_display' => $this->trim_data( $this->data['action_button_display'] ),
			'locked'                => $this->trim_data( $this->data['locked'] ),
			'custom'                => $this->data['custom'],
		] );

		$this->ensure_redirect_ids();

		delete_post_meta_by_key( 'tva_temporary_redirect' );

		return $this;
	}

	/**
	 * Remove any unnecessary fields from data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function trim_custom_data( $data ) {
		$opt     = $data['option'];
		$trimmed = [
			'option'     => $opt,
			'conditions' => $data['conditions'],
			'title'      => $data['title'],
			'order'      => $data['order'],
			'custom_id'  => $data['custom_id'],
		];
		if ( ! empty( $data[ $opt ] ) ) {
			$trimmed[ $opt ] = $data[ $opt ];
		}

		// make sure no post details are saved
		if ( isset( $trimmed[ $opt ]['post'] ) ) {
			unset( $trimmed[ $opt ]['post'] );
		}

		return $trimmed;
	}

	/**
	 * Handle admin localization.
	 * For certain options, additional data is needed, such as post titles, edit links etc
	 *
	 * @return array[]
	 */
	public function admin_localize() {
		/**
		 * Include some extra data in the localization array for the custom settings
		 */
		if ( ! empty( $this->data['custom'] ) ) {
			foreach ( $this->data['custom'] as $index => $field ) {
				$data = &$this->data['custom'][ $index ];

				if ( ! empty( $data['option'] ) && $data['option'] === 'redirect_custom' && ! empty( $data['redirect_custom']['post_id'] ) ) {
					$data['redirect_custom']['post'] = new TVA_Page_Setting( 'custom_redirect', 'general', $data['redirect_custom']['post_id'] );
				}

				if ( $data['option'] === 'content' ) {
					$post_id                 = isset( $data['content']['post_id'] ) ? $data['content']['post_id'] : 0;
					$data['content']['post'] = new TVA_Page_Setting( 'content_post_custom_' . $field['custom_id'], 'general', $post_id );
				}

				if ( $data['option'] === 'redirect_custom' && empty( $data['redirect_custom']['post_id'] ) ) {
					$data['redirect_custom']['post'] = [
						'category'     => 'custom_link',
						'edit_url'     => '',
						'edit_with_wp' => '',
						'name'         => 'custom_redirect',
						'preview_url'  => isset( $data['redirect_custom']['custom_link'] ) ? $data['redirect_custom']['custom_link'] : '',
						'title'        => isset( $data['redirect_custom']['title'] ) ? $data['redirect_custom']['title'] : '',
						'value'        => - 1,
					];
				}
			}
		}

		return parent::admin_localize();
	}

	/**
	 * Make sure that every needed piece of data exists
	 *
	 * @param int|null $custom_id
	 *
	 * @return TVA_Access_Restriction
	 */
	public function ensure_data_exists( $custom_id = null ) {

		// custom_id = 0 a valid thus we can't rely on empty() nor on is_null()
		if ( ( $custom_id !== '' && $custom_id !== null ) && ( empty( $this->data['custom'] ) || empty( $this->data['custom'][ - 1 ] ) ) ) {
			$this->data['custom'][ - 1 ] = [
				'custom_id' => - 1,
				'option'    => 'content',
			];
		}
		foreach ( $this->data['custom'] as $index => $field ) {
			$option = empty( $this->data['custom'][ $index ]['option'] ) ? '' : $this->data['custom'][ $index ]['option'];
			if ( $option === 'content' ) {
				/* make sure a post exists that should hold the content */
				$post_id                                              = empty( $this->data['custom'][ $index ]['content']['post_id'] ) ? null : $this->data['custom'][ $index ]['content']['post_id'];
				$this->data['custom'][ $index ]['content']['post_id'] = $this->ensure_content_post( $this->get_scope_key( 'custom', $this->data['custom'][ $index ]['custom_id'] ), 'not_purchased', $post_id );
			} else {
				$this->data['custom'][ $index ]['content']['post_id'] = 0;
			}
		}

		return parent::ensure_data_exists( null );
	}

	/**
	 * Deletes the temporarily created post which is used for display custom content option in the case of custom settings
	 *
	 * @param $scope
	 */
	public function delete_tmp_settings( $scope ) {

		$posts = get_posts( [
			'post_type'   => static::POST_TYPE,
			'meta_key'    => 'tva_content_for',
			'meta_value'  => $this->get_scope_key( $scope, - 1 ),
			'post_status' => [ 'draft', 'publish' ],
		] );

		if ( ! empty( $posts ) ) {
			wp_delete_post( $posts[0]->ID );
		}
	}

	/**
	 * Updates the temporary post to be associated with the correct custom settings
	 *
	 * @param $scope
	 */
	public function update_tmp_post( $post_id, $scope, $custom_id ) {
		$post = get_post( $post_id );

		if ( $post instanceof WP_Post ) {
			$scope_key = $this->get_scope_key( $scope, $custom_id );

			$post->post_title = 'Restriction for ' . $scope_key;
			wp_update_post( $post );
			update_post_meta( $post_id, 'tva_content_for', $scope_key );
		}
	}

	/**
	 * Saves a custom setting for a product
	 *
	 * @param int|null $custom_id
	 * @param array    $updated_settings
	 * @param string   $scope
	 * @param int|null $post_id
	 */
	public function save_custom_settings( $custom_id, $updated_settings, $scope, $post_id ) {
		$custom_settings        = $this->get( $scope );
		$updated_settings_index = 0;

		if ( isset( $custom_id ) && (int) $custom_id !== - 1 ) {
			$updated_settings['custom_id'] = $custom_id;

			foreach ( $custom_settings as $index => $setting ) {
				if ( $setting['custom_id'] === $custom_id ) {
					$custom_settings[ $index ] = $updated_settings;
					$updated_settings_index    = $index;
					break;
				}
			}
		} else {
			$custom_id = 0;
			foreach ( $custom_settings as $index => $custom_setting ) {
				$custom_settings[ $index ]['order'] ++;

				if ( $custom_setting['custom_id'] > $custom_id ) {
					$custom_id = $custom_setting['custom_id'];
				}
			}
			if ( ! empty( $custom_settings ) ) {
				$custom_id ++;
			}

			$updated_settings['custom_id'] = $custom_id;
			$updated_settings['order']     = 0;
			array_unshift( $custom_settings, $updated_settings );
		}

		$this->update_tmp_post( $post_id, $scope, $custom_id );
		unset( $custom_settings[ - 1 ] );

		$this->set( $scope, $custom_settings );

		return $updated_settings_index;
	}
}
