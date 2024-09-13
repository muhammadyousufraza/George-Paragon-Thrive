<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TTB;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Main
 */
class Main {

	/**
	 * Option name that stores the info about compatibility stuff
	 * Contains information if the system should auto download certain templates or not
	 */
	const COMPAT_OPTION_NAME = 'tva_visual_builder_skin_compat';

	public static function init() {

		if ( static::is_thrive_theme_active() ) {
			/* TODO: not sure if we have to do something here or not */
		} else {
			static::include_theme_builder();
		}

		if ( ! class_exists( '\Thrive_Theme', false ) || ! class_exists( '\Thrive_Theme_Default_Data', false ) ) {
			/**
			 * Fix for plesk environments
			 * Updates can be run via WP-ToolKit via WP-CLI and on that request the theme classes are not found
			 */
			return;
		}

		if ( Apprentice_Wizard::is_frontend() ) {
			add_action( 'init', '\\TVA\\TTB\\tva_wizard' );

			if ( Apprentice_Wizard::is_during_preview() ) {
				add_filter( 'thrive_wizard_instance', '\\TVA\\TTB\\tva_wizard' );

				add_filter( 'thrive_theme_localize_front', static function ( $data ) {
					if ( tva_wizard()->step() === 'sidebar' ) {
						$data['sidebar_visibility'] = [
							'desktop' => 1,
							'tablet'  => 1,
						];
					}

					return $data;
				} );
			}
		}

		/**
		 * We need here to create an instance of tva_palettes for the hooks to have listeners
		 *
		 * Ex: when changing the theme color and the "share_color" option is enabled we need to trigger a change for TA color also
		 */
		Palette::instance();


		Filters::init();
		Actions::init();
		Reset::init();

		add_action( 'wp', [ Filters::class, 'wp_init' ] );
		add_action( 'wp', [ Actions::class, 'wp_init' ] );
	}

	/**
	 * Instantiate a new Skin. If no id passed => instantiate the default skin
	 *
	 * @param int|\WP_Term|null $id
	 *
	 * @return Skin
	 */
	public static function skin( $id = null ) {
		if ( $id && $id instanceof \WP_Term ) {
			$id = $id->term_id;
		}

		return Skin::instance_with_id( $id ?: static::get_default_skin_id() );
	}

	/**
	 * Returns currently / requested / previewed skin ID
	 *
	 * @return int
	 */
	public static function requested_skin_id() {
		return (int) ( empty( $_REQUEST['tva_skin_id'] ) ? static::get_default_skin_id() : $_REQUEST['tva_skin_id'] );
	}

	/**
	 * Returns the currently / requested / previewed skin
	 *
	 * @return Skin
	 */
	public static function requested_skin() {
		return Main::skin( static::requested_skin_id() );
	}

	public static function include_theme_builder() {

		$builder_path = static::get_builder_path();

		/* redefine the path and url */
		define( 'THEME_URL', \TVA_Const::plugin_url( '/builder' ) );
		define( 'THEME_PATH', $builder_path );

		require_once $builder_path . '/functions.php';

		/* we need to call this manually because it's on the same hook the current function is called */
		\Thrive_Architect::after_setup_theme();

		/**
		 * If the theme is not active, we need to remove some filters
		 */
		remove_filter( 'post_class', [ \Thrive_Architect::class, 'post_class' ] );
	}

	/**
	 * @param bool $check_wizard_request Whether or not to check for possible short-circuits (e.g. return true if viewing a wizard page)
	 *
	 * @return bool
	 */
	public static function uses_builder_templates( $check_wizard_request = true ) {
		/* short-circuit :: skin wizard preview -> ALWAYS use templates */
		if ( $check_wizard_request && ( Apprentice_Wizard::is_frontend() || ! empty( $_REQUEST['tva_skin_id'] ) ) ) {
			return true;
		}

		return ! empty( get_option( 'tva_use_builder_templates', 0 ) );
	}

	public static function set_use_builder_templates( $value = 1 ) {
		return update_option( 'tva_use_builder_templates', (int) $value );
	}

	public static function set_default_skin_id( $id ) {
		update_option( 'tva_default_skin', (int) $id );
	}

	/**
	 * Return the current active apprentice skin
	 *
	 * @return int
	 */
	public static function get_default_skin_id() {
		if ( ! static::uses_builder_templates( false ) ) {
			return 0;
		}

		$skin_id = get_option( 'tva_default_skin', 0 );

		if ( empty( $skin_id ) ) {
			$skin_id = static::create_skin( 'Default design', 'tva_default' );

			update_option( 'tva_default_skin', $skin_id );
		}

		return (int) $skin_id;
	}

	/**
	 * Return the path to the theme builder by checking if it's active or not
	 *
	 * @return string
	 */
	public static function get_builder_path() {
		return static::is_thrive_theme_active() ? THEME_PATH : \TVA_Const::plugin_path( 'builder' );
	}

	/**
	 * Check if the theme builder is active or not
	 *
	 * @return bool
	 */
	public static function is_thrive_theme_active() {
		$current_theme = wp_get_theme();

		return $current_theme->get_template() === 'thrive-theme';
	}

	/**
	 * Get the current active template on the page
	 *
	 * @return false|int
	 */
	public static function get_active_template() {
		$template_id = 0;
		$meta        = \Thrive_Utils::localize_url();

		if ( \Thrive_Utils::inner_frame_id() ) {
			$template_id = \Thrive_Utils::inner_frame_id();
		} elseif ( get_post_type() === THRIVE_TEMPLATE ) {
			$template_id = get_the_ID();
		} elseif ( static::uses_builder_templates() && ( tva_is_apprentice() || tva_general_post_is_apprentice() ) ) {

			$args = [
				'posts_per_page' => 1,
				'post_type'      => THRIVE_TEMPLATE,
				'tax_query'      => [ static::requested_skin()->build_skin_query_params() ],
				'meta_query'     => [
					[
						'key'   => 'default',
						'value' => '1',
					],
					[
						'key'   => THRIVE_PRIMARY_TEMPLATE,
						'value' => $meta[ THRIVE_PRIMARY_TEMPLATE ],
					],
					[
						'key'   => THRIVE_SECONDARY_TEMPLATE,
						'value' => $meta[ THRIVE_SECONDARY_TEMPLATE ],
					],
					[
						'key'   => THRIVE_VARIABLE_TEMPLATE,
						'value' => in_array( $meta[ THRIVE_PRIMARY_TEMPLATE ], [ 'home', 'archive' ] ) ? '' : ( empty( $meta[ THRIVE_VARIABLE_TEMPLATE ] ) ? '' : $meta[ THRIVE_VARIABLE_TEMPLATE ] ),
					],
				],
			];

			/**
			 * For lesson posts, we also need to take into account the lesson format (audio, video, text=standard)
			 */
			if ( is_singular( \TVA_Const::LESSON_POST_TYPE ) ) {
				/** @var \TVA_Lesson $lesson */
				$lesson                = \TVA_Post::factory( get_post() );
				$format                = $lesson->get_type();
				$args['meta_query'] [] = [
					'key'   => 'format',
					'value' => $format,
				];
				/* try to get a template specific for this format */
				$templates = get_posts( $args );
				if ( empty( $templates ) ) {
					/* nothing found, throw away the meta_query related to post formats, and replace it with a "standard" format */
					array_splice( $args['meta_query'], - 1, 1, [
						[
							'key'   => 'format',
							'value' => THRIVE_STANDARD_POST_FORMAT,
						],
					] );
				}
			}

			if ( empty( $templates ) ) {
				/* avoid a double get_posts() call when possible */
				$templates = get_posts( $args );
			}

			$templates = apply_filters( 'thrive_theme_default_templates', $templates, $args, $meta );

			if ( ! empty( $templates ) ) {
				$template_id = $templates[0]->ID;
			}
		}

		return $template_id;
	}

	/**
	 * @return bool Whether or not this install of TA needs to show a legacy design template
	 */
	public static function has_legacy_design() {
		return ! get_option( 'tva_hide_legacy_design', 0 );
	}

	/**
	 * hide the legacy design from the list of skins
	 */
	public static function hide_legacy_design() {
		update_option( 'tva_hide_legacy_design', 1 );
	}

	/**
	 * show the legacy design in list of skins
	 */
	public static function show_legacy_design() {
		update_option( 'tva_hide_legacy_design', 0 );
	}

	/**
	 * Sanity check all skins
	 * Checks for a skin to be "completed" aka to have all types of templates
	 *
	 * @return void
	 */
	public static function sanity_check() {
		if ( \TVA\TTB\Check::is_end_user_site() ) {
			$skins = static::get_all_skins( false, false );
			$check = get_option( static::COMPAT_OPTION_NAME, [] );

			if ( ! is_array( $check ) ) {
				$check = [];
			}

			$initial_check = $check;
			$initial_len   = count( $check );

			foreach ( $skins as $skin ) {
				$check = array_merge( $check, $skin->sanity_check( $initial_check ) );
			}

			if ( count( $check ) !== $initial_len ) {
				update_option( static::COMPAT_OPTION_NAME, $check );
			}
		}
	}

	/**
	 * Resets the sanity check operations
	 *
	 * @return void
	 */
	public static function reset_sanity_check() {
		delete_option( static::COMPAT_OPTION_NAME );
	}

	/**
	 * Get a list of all available skins
	 *
	 * @param bool $as_wp_terms    Whether or not to return WP_Term instances
	 * @param bool $include_legacy whether or not to also include the legacy design in the list of returned skins
	 *
	 * @return Skin[]
	 */
	public static function get_all_skins( $as_wp_terms = false, $include_legacy = true ) {
		$skins = array_map( static function ( \WP_Term $term ) use ( $as_wp_terms ) {
			if ( $as_wp_terms ) {
				return $term;
			}

			return new Skin( $term );
		}, get_terms(
			[
				'orderby'    => 'term_id',
				'order'      => 'DESC',
				'hide_empty' => false,
				'taxonomy'   => SKIN_TAXONOMY,
				'meta_key'   => 'thrive_scope',
				'meta_value' => 'tva',
			]
		) );

		if ( ! $skins ) {
			/* make sure the legacy design is marked as default */
			static::set_use_builder_templates( 0 );
			static::show_legacy_design();
		}

		if ( $include_legacy && static::has_legacy_design() ) {
			array_unshift( $skins, Legacy_Skin::instance() );
		}

		return $skins;
	}

	/**
	 * Create a new skin with default data
	 *
	 * @param string      $skin_name
	 * @param string|null $skin_tag            it will be generated if not set
	 * @param bool        $create_default_data whether or not to generate default data for the skin
	 *
	 * @return int the created skin id
	 */
	public static function create_skin( $skin_name, $skin_tag = null, $create_default_data = true ) {
		if ( $skin_tag === null ) {
			$skin_tag = uniqid();
		}

		$skin_id = Default_Data::create_skin( $skin_name, false, $create_default_data );
		$skin    = static::skin( $skin_id );
		/* setup default data for the school homepage template */
		foreach ( $skin->get_templates_by_type( THRIVE_HOMEPAGE_TEMPLATE, \TVA_Const::COURSE_POST_TYPE ) as $template ) {
			$template->setup_default_data();
		}
		$skin->set_meta( \Thrive_Skin::TAG, $skin_tag );

		return $skin_id;
	}

	/**
	 * Get the default school logos (applicable on all apprentice skins)
	 *
	 * @return array of arrays with the following keys:
	 *               - src
	 *               - placeholder
	 *               - tva_logo_tooltip
	 *               - attachment_id (if available)
	 *               - preview_url
	 */
	public static function get_logo() {
		$logos = \TCB_Logo::get_logos();

		$default_logos['preview_url'] = array(
			'src' => plugin_dir_url( dirname( __DIR__ ) ) . 'tcb/editor/css/images/logo-placeholder.png',
		);

		foreach ( $logos as $logo ) {
			if ( isset( $logo['scope'] ) && $logo['scope'] === 'tva' ) {
				$logo['placeholder'] = \TCB_Logo::get_placeholder_src( 0 );
				$logo['src']         = \TCB_Logo::get_src( $logo['id'] );

				$default_logos[ strtolower( $logo['name'] ) ] = $logo;
			}
		}

		$default_logos['tva_logo_tooltip'] = get_user_meta( get_current_user_id(), 'tva_logo_tooltip', true );

		return $default_logos;
	}

	/**
	 * Get the dark logo id ( id is an integer which identifies the logo in the list of logos from TAr )
	 * If it's not set, then returns the default logo
	 *
	 * @return int
	 * @see \TCB_Logo::get_logos()
	 */
	public static function get_logo_id() {
		$dark_id = self::get_logo()['dark']['id'];
		if ( isset ( $dark_id ) ) {
			return $dark_id;
		}

		return 0;
	}

	/**
	 * Returns the apprentice logo or null if no apprentice logo has been defined
	 *
	 * @return array
	 */
	public static function get_school_logo() {
		$logos = \TCB_Logo::get_logos( false );
		$logo  = array();

		foreach ( $logos as $logo_data ) {
			if ( ! empty( $logo_data['scope'] ) && $logo_data['scope'] === 'tva' ) {
				$logo = $logo_data;
				break;
			}
		}

		return $logo;
	}

	/**
	 * Require the class(es) needed for the TTB product instance (used is permission checks for API requests)
	 */
	public static function require_theme_product() {
		require_once THEME_PATH . '/integrations/dashboard/class-thrive-theme-product.php';
	}
}
