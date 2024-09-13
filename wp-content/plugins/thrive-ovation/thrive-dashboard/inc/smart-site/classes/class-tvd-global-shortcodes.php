<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVD_Global_Shortcodes
 */
class TVD_Global_Shortcodes {

	public static $dynamic_shortcodes = array(
		'thrive_global_shortcode_url' => 'global_shortcode_url',
		'thrv_dynamic_data_request'   => 'request_data_shortcode',
		'thrv_dynamic_data_date'      => 'date_shortcode',
		'thrv_dynamic_data_content'   => 'content_shortcode',
		'thrv_dynamic_data_user'      => 'user_data_shortcode',
		'thrv_dynamic_data_source'    => 'source_shortcode',
		'thrv_dynamic_data_user_acf'  => 'acf_user_field',
	);

	public function __construct() {
		add_filter( 'tcb_content_allowed_shortcodes', array( $this, 'allowed_shortcodes' ) );
		add_filter( 'tcb_dynamiclink_data', array( $this, 'links_shortcodes' ) );
		add_filter( 'tcb_inline_shortcodes', array( $this, 'tcb_inline_shortcodes' ), 99, 1 );
		$this->add_shortcodes();
	}

	public function add_shortcodes() {

		foreach ( static::$dynamic_shortcodes as $shortcode => $func ) {
			$function = array( $this, $func );
			add_shortcode( $shortcode, static function ( $attr ) use ( $function ) {
				$output = call_user_func_array( $function, func_get_args() );

				return TVD_Global_Shortcodes::maybe_link_wrap( $output, $attr );
			} );
		}

	}

	/**
	 * Checks if shortcode content needs to be wrapped in a link (if a custom json-encoded attribute exists in the shortcode)
	 *
	 * @param string $shortcode_content
	 * @param array  $attr
	 *
	 * @return string
	 */
	public static function maybe_link_wrap( $shortcode_content, $attr ) {
		/**
		 * If a static link is detected in config, we need to wrap $content in that link (only if a link doesn't already exist in $shortcode_content ..)
		 */
		if ( $shortcode_content && ! empty( $attr['static-link'] ) ) {
			$link_attr = json_decode( htmlspecialchars_decode( $attr['static-link'] ), true );
			if ( strpos( $shortcode_content, '</a>' ) === false ) {

				if ( ! empty( $link_attr ) && ! empty( $link_attr['href'] ) ) {
					/* replacement for shortcode open "[" */
					if ( strpos( $link_attr['href'], '((' ) === 0 ) {
						$link_attr['href'] = str_replace( array( '((', '))' ), array( '[', ']' ), $link_attr['href'] );
						$link_attr['href'] = do_shortcode( $link_attr['href'] );
					}
					/* put the brackets back in the title attribute if they were replaced before */
					if ( ! empty( $link_attr['title'] ) && strpos( $link_attr['title'], '((' ) !== false ) {
						$link_attr['title'] = str_replace( array( '((', '))' ), array(
							'[',
							']',
						), $link_attr['title'] );
					}
					$attributes = array();
					foreach ( $link_attr as $attr_name => $value ) {
						$attributes[] = ( $attr_name === 'className' ? 'class' : $attr_name ) . '="' . esc_attr( $value ) . '"';
					}
					$shortcode_content = '<a ' . implode( ' ', $attributes ) . '>' . $shortcode_content . '</a>';
				}
			} elseif ( extension_loaded( 'dom' ) && isset( $link_attr['className'] ) && function_exists( 'mb_convert_encoding' ) ) {
				/**
				 * For elements already containing a link just add the old classes (e.g. global styles)
				 */
				$dom = new DOMDocument;
				@$dom->loadHTML( mb_convert_encoding( $shortcode_content, 'HTML-ENTITIES', 'UTF-8' ) );
				$dom->encoding = 'UTF-8';

				$items = $dom->getElementsByTagName( 'a' );

				for ( $i = 0; $i < $items->length; $i ++ ) {
					$link = $items->item( $i );
					if ( $link ) {
						$link->setAttribute( 'class', $link_attr['className'] );

						if ( isset( $link_attr['data-css'] ) ) {
							$link->setAttribute( 'data-css', $link_attr['data-css'] );
						}
					}
				}

				$body = $dom->getElementsByTagName( 'body' );
				if ( $body && $body->length ) {
					$shortcode_content = '';
					foreach ( $body[0]->childNodes as $child ) {
						$shortcode_content .= $dom->saveHTML( $child );
					}
				} else {
					$shortcode_content = $dom->saveHTML();
				}
			}
		}

		return $shortcode_content;
	}

	public function tcb_inline_shortcodes( $shortcodes ) {
		return array_merge_recursive( TVD_Global_Shortcodes::get_inline_shortcodes(), $shortcodes );
	}

	public static function get_inline_shortcodes() {
		$inline_shortcodes = array();

		$shortcodes_without_params = array(
			/* Content */
			'the_ID'    => array(
				'name'      => __( 'Post ID', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_content',
				'fn'        => 'content_shortcode',
				'group'     => 'Content',
			),
			'the_title' => array(
				'name'      => __( 'Post title', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_content',
				'fn'        => 'content_shortcode',
				'group'     => 'Content',
			),
			'post_type' => array(
				'name'      => __( 'Post type', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_content',
				'fn'        => 'content_shortcode',
				'group'     => 'Content',
			),
			'permalink' => array(
				'name'      => __( 'Post URL', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_content',
				'fn'        => 'content_shortcode',
				'group'     => 'Content',
			),
			/* Time & date*/
			'd M Y'     => array(
				'name'      => esc_html__( 'Date (14 Aug 2029)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'd.n.Y'     => array(
				'name'      => esc_html__( 'Date (14.8.2029)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'd-m-Y'     => array(
				'name'      => esc_html__( 'Date (14-08-2029)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'd/m/Y'     => array(
				'name'      => esc_html__( 'Date (14/08/2029)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'G:i:s'     => array(
				'name'      => esc_html__( 'Time (23:59:59)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'G:i'       => array(
				'name'      => esc_html__( 'Time (23:59)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'd'         => array(
				'name'      => esc_html__( 'Day (01–31)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'jS'        => array(
				'name'      => esc_html__( 'Day (1st, 2nd, 15th - 31st)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'l'         => array(
				'name'      => esc_html__( 'Day of the week (Monday)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'D'         => array(
				'name'      => esc_html__( 'Day of the week (Mon)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'm'         => array(
				'name'      => esc_html__( 'Month (01-12)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'F'         => array(
				'name'      => esc_html__( 'Month (January - December)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'M'         => array(
				'name'      => esc_html__( 'Month (Jan – Dec)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
			'Y'         => array(
				'name'      => esc_html__( 'Year (2029)', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_date',
				'fn'        => 'date_shortcode',
				'group'     => 'Time & date',
			),
		);

		$resources = array(
			'get'    => __( 'URL QueryString', 'thrive-dash' ),
			'post'   => __( 'POST variable', 'thrive-dash' ),
			'cookie' => __( 'Cookie', 'thrive-dash' ),
		);

		$shortcodes_with_default = array(
			/* User Data */
			'username'     => array(
				'name'      => __( 'WordPress username', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
				'link'      => __( 'Link to user profile', 'thrive-dash' ),
			),
			'user_email'   => array(
				'name'      => __( 'WordPress user email', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
			),
			'role'         => array(
				'name'      => __( 'WordPress user role', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
			),
			'first_name'   => array(
				'name'      => __( 'WordPress user first name', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
				'link'      => __( 'Link to user profile', 'thrive-dash' ),
			),
			'last_name'    => array(
				'name'      => __( 'WordPress user last name', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
				'link'      => __( 'Link to user profile', 'thrive-dash' ),
			),
			'nickname'     => array(
				'name'      => __( 'WordPress user nickname', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
			),
			'display_name' => array(
				'name'      => __( 'WordPress user public name', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
			),
			'website'      => array(
				'name'      => __( 'WordPress user website', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
			),
			'user_bio'     => array(
				'name'      => __( 'WordPress user bio', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
			),
			'ip'           => array(
				'name'      => __( 'IP', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
			),
			'browser'      => array(
				'name'      => __( 'Browser', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_user',
				'fn'        => 'user_data_shortcode',
				'group'     => 'User data',
			),
			/* Source */
			'HTTP_REFERER' => array(
				'name'      => __( 'Referring URL', 'thrive-dash' ),
				'shortcode' => 'thrv_dynamic_data_source',
				'fn'        => 'source_shortcode',
				'group'     => 'Source',
			),
		);

		if ( tvd_has_external_fields_plugins() ) {
			// if is acf plugin active, run through all of them and update the custom fields for the user

			foreach ( tvd_get_acf_user_external_fields() as $field ) {

				$shortcodes_with_default[ $field['name'] ] = array(
					'name'      => $field['label'],
					'shortcode' => 'thrv_dynamic_data_user_acf',
					'fn'        => 'acf_user_field',
					'group'     => 'User data',
				);
			}

		}


		foreach ( $shortcodes_with_default as $key => $data ) {
			$shortcode = array(
				'name'        => $data['name'],
				'option'      => $data['name'],
				'value'       => $data['shortcode'],
				'extra_param' => $key,
				'input'       => array(
					'default' => array(
						'type'  => 'input',
						'label' => __( 'Default Value', 'thrive-dash' ),
						'value' => '',
					),
					'id'      => array(
						'extra_options' => array(),
						'real_data'     => array(
							$key => call_user_func( 'TVD_Global_Shortcodes::' . $data['fn'], array( 'id' => $key ) ),
						),
						'type'          => 'hidden',
						'value'         => $key,
					),
				),
			);
			/* Support shortcodes link*/
			if ( isset( $data['link'] ) ) {
				$shortcode['input']['link'] = array(
					'type'  => 'checkbox',
					'label' => $data['link'],
					'value' => true,
				);
			}
			$inline_shortcodes[ $data['group'] ][] = $shortcode;
		}

		foreach ( $shortcodes_without_params as $key => $data ) {
			$shortcode                             = array(
				'name'        => $data['name'],
				'option'      => $data['name'],
				'value'       => $data['shortcode'],
				'extra_param' => $key,
				'input'       => array(
					'id' => array(
						'extra_options' => array(),
						'real_data'     => array(
							$key => call_user_func( 'TVD_Global_Shortcodes::' . $data['fn'], array( 'id' => $key ) ),
						),
						'type'          => 'hidden',
						'value'         => $key,
					),
				),
			);
			$inline_shortcodes[ $data['group'] ][] = $shortcode;
		}

		foreach ( $resources as $key => $name ) {
			$inline_shortcodes['Request data'][] = array(
				'name'        => $name,
				'option'      => $name,
				'value'       => 'thrv_dynamic_data_request',
				'extra_param' => $key,
				'input'       => array(
					'var_name' => array(
						'type'  => 'input',
						'label' => __( 'Variable name', 'thrive-dash' ),
						'value' => '',
					),
					'default'  => array(
						'type'  => 'input',
						'label' => __( 'Default Value', 'thrive-dash' ),
						'value' => '',
					),
					'id'       => array(
						'extra_options' => array(),
						'real_data'     => array(
							$key => TVD_Global_Shortcodes::request_data_shortcode( array(
								'id'       => $key,
								'var_name' => '',
							) ),
						),
						'type'          => 'hidden',
						'value'         => $key,
					),
				),
			);
		}

		return $inline_shortcodes;
	}

	/**
	 * Filter allowed shortcodes for tve_do_wp_shortcodes
	 *
	 * @param $shortcodes
	 *
	 * @return array
	 */
	public function allowed_shortcodes( $shortcodes ) {
		return array_merge( $shortcodes, array_keys( TVD_Global_Shortcodes::$dynamic_shortcodes ) );
	}

	/**
	 * Add global shortcodes to be used in dynamic links
	 *
	 * @param $links
	 *
	 * @return mixed
	 */
	public function links_shortcodes( $links ) {
		$global_links = array();
		foreach ( $this->global_data() as $index => $value ) {
			$value['id']    = $index;
			$global_links[] = $value;
		}
		$links['Site'] = array(
			'links'     => array( $global_links ),
			'shortcode' => 'thrive_global_shortcode_url',
		);

		return $links;
	}

	/**
	 * Global data related to the site
	 *
	 * @return array
	 */
	public function global_data() {
		// phpcs:disable
		return apply_filters( 'tvd_global_data', array(
			array(
				'name' => __( 'Homepage', 'thrive-dash' ),
				'url'  => get_home_url(),
				'show' => true,
			),
			array(
				'name' => __( 'Blog', 'thrive-dash' ),
				'url'  => get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : get_home_url(),
				'show' => true,
			),
			array(
				'name' => __( 'RSS Feed', 'thrive-dash' ),
				'url'  => get_home_url() . '/feed',
				'show' => true,
			),
			array(
				'name' => __( 'Login', 'thrive-dash' ),
				'url'  => wp_login_url(),
				'show' => true,
			),
			array(
				'name' => __( 'Logout', 'thrive-dash' ),
				'url'  => wp_logout_url(),
				'show' => true,
			),
		) );
		// phpcs:enable
	}

	/**
	 * Replace the shortcode with its content
	 *
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public function global_shortcode_url( $args ) {
		$data = '';

		if ( isset( $args['id'] ) ) {
			$groups = $this->global_data();
			$id     = (int) $args['id'];
			$data   = empty( $groups[ $id ] ) ? '' : $groups[ $id ]['url'];
		}

		if ( isset( $args['logout-redirect'] ) ) {
			$data .= '&redirect_to=' . $args['logout-redirect'];
		}

		return $data;
	}

	/**
	 * Shortcode render for user data
	 *
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public static function user_data_shortcode( $args ) {
		$user_data = tve_current_user_data();
		$value     = '';

		if ( isset( $args['id'] ) ) {
			if ( $args['id'] === 'browser' ) {
				$value = '<span class="tve-browser-data"></span >'; /* Replace this with JS because PHP get_browser doesnt work  all the time */
			}
			if ( isset( $user_data[ $args['id'] ] ) ) {
				$value = $user_data[ $args['id'] ];

				if ( $args['id'] === 'website' ) {
					/* create link with user website */
					$value = sprintf( '<a href="%s" target="_blank">%s</a>', $value, $value );
				} elseif ( isset( $args['link'] ) && $args['link'] === '1' ) {
					$value = sprintf( '<a href="%s" target="_blank">%s</a>', $user_data['edit_url'], $value );
				}
			}
		}
		if ( empty( $value ) && isset( $args['default'] ) ) {
			$value = $args['default'];
		}

		return $value;
	}


	/**
	 * Shortcode render for user data from acf
	 *
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public static function acf_user_field( $args ) {
		$value = '';
		if ( tvd_has_external_fields_plugins() ) {
			$value = get_field( $args['id'], 'user_' . get_current_user_id() );
			if ( empty( $value ) && isset( $args['default'] ) ) {
				$value = $args['default'];
			}
		}

		return $value;
	}

	/**
	 * Shortcode render for post data
	 *
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public static function content_shortcode( $args ) {
		$value = '';
		global $post;

		if ( is_singular() || ( wp_doing_ajax() && ! empty( $post ) ) ) {
			if ( isset( $args['id'] ) ) {
				$func = "get_{$args['id']}";
				if ( function_exists( $func ) ) {
					$value = $func();
				}
			}
			if ( empty( $value ) && isset( $args['default'] ) ) {
				$value = $args['default'];
			}
		}

		return $value;
	}

	public static function date_shortcode( $args ) {
		/**
		 * The hour should be in 24h format
		 */
		$format = str_replace( 'g', 'G', $args['id'] );


		if ( function_exists( 'wp_date' ) ) {
			$result = wp_date( $format );
		} else {
			$result = date_i18n( $format );
		}

		return trim( $result );
	}


	/**
	 * Shortcode render for data from page source
	 *
	 * @param $args
	 *
	 * @return mixed|string
	 */
	public static function source_shortcode( $args ) {
		$allowed = array( 'HTTP_REFERER' );
		$value   = '';

		if ( isset( $args['id'], $_SERVER[ $args['id'] ] ) && in_array( $args['id'], $allowed, true ) ) {
			$value = sanitize_text_field( $_SERVER[ $args['id'] ] );
		}
		if ( empty( $value ) && isset( $args['default'] ) ) {
			$value = $args['default'];
		}

		return $value;
	}

	/**
	 * Try to get a value deep down from a variable
	 * e.g a[b][c]
	 *
	 * @param $original_key
	 * @param $params
	 *
	 * @return mixed|string
	 */
	public static function get_deep_value( $original_key, $params ) {
		$original_key = str_replace( ']', '', $original_key );
		$ref          = $params;
		foreach ( explode( '[', $original_key ) as $key ) {
			if ( isset( $ref[ $key ] ) ) {
				$ref = $ref[ $key ];
			} else {
				$ref = '';
			}
		}

		return $ref;
	}

	/**
	 * Shortcode render for data from $_REQUEST
	 *
	 * @param array  $args
	 * @param string $context
	 * @param string $tag
	 *
	 * @return mixed|string
	 */
	public static function request_data_shortcode( $args = array(), $context = '', $tag = '' ) {
		$value             = '';
		$should_load_value = true;

		if ( ! empty( $tag ) && function_exists( 'is_editor_page' ) && is_editor_page() ) {
			//preserve shortcode in case someone adds it as dynamic link cuz why not
			if ( empty( $args['inline'] ) ) {
				$attributes = '';

				foreach ( array( 'var_name', 'id', 'default', 'inline' ) as $key ) {
					if ( ! empty( $args[ $key ] ) ) {
						$attributes .= $key . '=' . $args[ $key ] . ' ';
					}
				}

				return '[' . $tag . ' ' . trim( $attributes ) . ']';
			}

			$should_load_value = false;
		}

		if ( $should_load_value && ! empty( $args['var_name'] ) ) {
			/**
			 * just in case the var_name has spaces check var_Name with underscore
			 * e.g test 01 comes as test_01
			 */
			$var_name     = $args['var_name'];
			$fallback_var = strpos( $var_name, ' ' ) !== false ? preg_replace( "/\s/", "_", $var_name ) : '';

			if ( strpos( $var_name, '((' ) !== false ) {
				$var_name = preg_replace( '#\\(\\(#', '[', $var_name );
				$var_name = preg_replace( '#\\)\\)#', ']', $var_name );
			}
			$global = [];
			switch ( $args['id'] ) {
				case 'post':
					$global = $_POST;
					break;
				case 'get':
					$global = $_GET;
					break;
				case 'cookie':
					$global = $_COOKIE;
					break;
				default:
					break;
			}

			$value = isset( $global[ $var_name ] ) ? $global[ $var_name ] : '';
			if ( empty( $value ) ) {
				$value = static::get_deep_value( $var_name, $global );
			}
			if ( empty( $value ) && ! empty( $fallback_var ) ) {
				$value = isset( $global[ $fallback_var ] ) ? $global[ $fallback_var ] : '';
			}
			if ( empty( $value ) ) {
				$value = static::get_deep_value( $fallback_var, $global );
			}

			$value = sanitize_text_field( $value );
		}
		if ( empty( $value ) && isset( $args['default'] ) ) {
			$value = $args['default'];
		}

		return esc_html( stripslashes( $value ) );
	}
}
