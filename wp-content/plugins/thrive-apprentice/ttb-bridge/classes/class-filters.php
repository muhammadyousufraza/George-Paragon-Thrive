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
 * Class Filters
 */
class Filters {

	/**
	 * Initialize filters
	 */
	static function init() {
		if ( Main::uses_builder_templates() ) {
			/* let our templates run with our content */
			remove_filter( 'thrive_theme_template_content', 'tva_thrive_theme_template_content', 10 );

			add_filter( 'tcb_post_types', [ __CLASS__, 'tcb_post_types' ] );

			/* each appr template should render it's own css file */
			add_filter( 'pre_option_thrive_use_inline_css', [ __CLASS__, 'pre_option_thrive_use_inline_css' ] );

			/* allow our scripts to be loaded all the time */
			add_filter( 'pre_option_tva_load_all_scripts', '__return_true' );

			add_filter( 'template_include', [ __CLASS__, 'template_include' ], PHP_INT_MAX );

			add_filter( 'rest_pre_dispatch', [ __CLASS__, 'rest_pre_dispatch' ], 0, 3 );

			add_filter( 'thrive_theme_use_inline_css', [ __CLASS__, 'use_inline_css' ] );

			add_filter( 'pre_do_shortcode_tag', [ __CLASS__, 'handle_render_third_party_shortcodes' ], 10, 4 );
		}

		add_filter( 'post_thumbnail_id', [ __CLASS__, 'post_thumbnail_id' ], 10, 2 );

		add_filter( 'thrive_theme_template_meta', [ __CLASS__, 'thrive_theme_template_meta' ], 11 ); //Must be executed after the TTB one

		add_filter( 'thrive_theme_template_url', [ __CLASS__, 'thrive_theme_template_url' ], 10, 5 );

		add_filter( 'thrive_theme_get_content_archive', [ __CLASS__, 'get_content_archive' ], 10, 2 );

		add_filter( 'thrive_theme_override_skin', [ __CLASS__, 'thrive_theme_override_skin' ], 10, 1 );

		add_filter( 'tcb_editor_edit_link_query_args', [ __CLASS__, 'tcb_editor_edit_link_query_args' ], 10, 2 );

		add_action( 'tcb_get_extra_global_variables', [ __CLASS__, 'output_skin_variables' ] );

		add_action( 'thrive_theme_deny_create_default_skin', [ __CLASS__, 'deny_create_default_skin' ] );

		add_filter( 'tcb_logo_url', [ __CLASS__, 'logo_url' ] );

		/* default content for sections when we create a new template */
		add_filter( 'thrive_theme_section_default_content', [ __CLASS__, 'default_section_content' ], 10, 3 );

		/* TTB hijacks the preview link args filter from TAr. need to add it here.. */
		add_filter( 'thrive_theme_preview_url_args', [ static::class, 'tcb_editor_edit_link_query_args' ], 10, 2 );

		add_filter( 'thrive_theme_typography_style_array', [ static::class, 'typography_style_array' ], 10, 2 );

		add_filter( 'tcb_should_print_unified_styles', [ static::class, 'should_print_unified_styles' ], 11 ); //Must be executed after the TTB one
		add_filter( 'tcb_output_default_styles', [ static::class, 'should_print_unified_styles' ], 11 ); //Must be executed after the TTB one);

		add_filter( 'tcb_gutenberg_switch', [ static::class, 'gutenberg_switch' ], 11 ); //Must be executed after the TTB one

		add_filter( 'thrive_theme_palette_transfer_get_palette', [ static::class, 'get_palette' ] );

		add_filter( 'allow_theme_scripts', [ static::class, 'allow_theme_scripts' ] );

		add_filter( 'thrive_breadcrumb_post_link', [ static::class, 'breadcrumb_permalink' ], 10, 2 );

		add_filter( 'thrive_theme_breadcrumbs_root_items', [ static::class, 'breadcrumb_root_items' ], 10, 3 );
		add_filter( 'thrive_theme_breadcrumbs_archive_title', [ static::class, 'breadcrumb_archive_title' ] );

		add_filter( 'tcb_author_bio', [ static::class, 'author_bio_shortcode' ] );

		add_filter( 'thrive_theme_ignore_post_types', [ static::class, 'ignored_post_types' ] );

		add_filter( 'thrive_theme_load_content_components', [ static::class, 'load_content_components' ] );

		add_filter( 'thrive_theme_allow_page_edit', [ static::class, 'display_ttb_sidebar_icon' ] );

		add_filter( 'thrive_theme_needs_localization', [ static::class, 'enable_theme_localization' ] );

		add_filter( 'thrive_theme_frontend_queried_object', [ static::class, 'filter_editor_queried_object' ] );

		add_filter( 'thrive_theme_allow_architect_switch', [ static::class, 'allow_architect_switch' ] );

		add_filter( 'thrive_theme_demo_content_url', [ static::class, 'demo_content_url' ] );

		add_filter( 'thrive_theme_get_posts_args', [ static::class, 'content_switch_post_args' ], 10, 2 );

		add_filter( 'thrive_theme_switch_content_name', [ static::class, 'content_switch_name' ], 10, 3 );

		add_filter( 'thrive_theme_switch_content_build_preview_url', [ static::class, 'content_switch_url_args' ], 10, 1 );
		add_filter( 'thrive_theme_switch_content_build_edit_url', [ static::class, 'content_switch_url_args' ], 10, 1 );

		add_filter( 'tcb_post_visibility_options_availability', [ static::class, 'blacklist_post_visibility_options' ] );

		add_filter( 'tcb_post_element_extend_config', [ static::class, 'hide_post_options' ], 11 ); // make sure it's executed after the one from TTB

		add_filter( 'thrive_theme_query_vars', [ static::class, 'localize_query_vars' ] );

		add_filter( 'thrive_template_singular_args', [ static::class, 'singular_template_args' ] );

		add_filter( 'comments_template', [ static::class, 'handle_comments_template' ] );
		add_filter( 'comments_open', [ static::class, 'toggle_comments' ], 10, 2 );
		add_action( 'comment_form_comments_closed', [ static::class, 'get_closed_comments_content_frontend' ] );
		add_filter( 'tcm_active', [ static::class, 'tc_active' ] );
		add_filter( 'tcm_allow_comments_editor', [ static::class, 'allow_thrive_comments' ] );
		add_filter( 'tcm_comment_mail_post_title', [ static::class, 'comments_mail_post_title' ], 10, 2 );

		add_filter( 'tva_course_list_get_courses_args', [ static::class, 'course_list_get_courses_args' ] );

		add_filter( 'thrive_theme_cloud_hf_templates', [ static::class, 'remove_default_hf_templates' ] );

		add_filter( 'thrive_theme_hide_comments_element', [ static::class, 'template_hide_comments_element' ] );

		/**
		 * BEGIN: Preview SKIN Functionality
		 *
		 * Adds tva_skin_id query string in the URL if present in the request
		 */
		add_filter( 'page_link', [ static::class, 'maybe_add_tva_skin_id' ], 10, 2 );
		add_filter( 'post_link', [ static::class, 'maybe_add_tva_skin_id' ], 10, 2 );
		add_filter( 'post_type_link', [ static::class, 'maybe_add_tva_skin_id' ], 10, 2 );
		add_filter( 'term_link', [ static::class, 'maybe_add_tva_skin_id' ], 10, 2 );
		/**
		 * END: Preview SKIN Functionality
		 */

		add_filter( 'thrive_theme_block_theme_template_styles', [ static::class, 'block_theme_template_styles' ] );

		/* the following 2 filters ensure TA does not try to load optimized assets for the time being */
		add_filter( 'tcb_lightspeed_requires_architect_assets', [ static::class, 'should_load_flat' ] );
		add_filter( 'tcb_lightspeed_should_load_flat', [ static::class, 'should_load_flat' ] );

		add_filter( 'tcb_clean_frontend_content', [ static::class, 'editor_template_content' ] );
		add_filter( 'the_content', [ static::class, 'editor_template_content' ] );

		add_filter( 'tcb_edit_post_default_url', [ static::class, 'template_dashboard_redirect' ], 11, 2 );

		add_filter( 'thrive_theme_default_templates', [ static::class, 'theme_default_templates' ], 10, 3 );

		add_filter( 'thrive_theme_section_html', [ static::class, 'theme_section_html' ], 10, 2 );

		add_filter( 'tcb_get_post_content', [ static::class, 'get_frontend_post_content' ], 10, 2 );

		add_filter( 'thrive_theme_content_switch_items', [ static::class, 'filter_content_switch_items' ], 10, 2 );

		add_filter( 'tcb_get_the_title', [ static::class, 'maybe_modify_post_title' ], 10, 2 );
	}

	/**
	 * Called on the WP hook - used to add some conditional filters after the query (request) data is available
	 */
	public static function wp_init() {
	}

	/**
	 * Deny create of default skin for thrive apprentice requests
	 *
	 * @param {boolean} $deny
	 *
	 * @return boolean
	 */
	public static function deny_create_default_skin( $deny ) {

		if ( ! empty( $_REQUEST['tva_skin_id'] ) ) {
			$deny = true;
		}

		return $deny;
	}

	/**
	 * When we're on a apprentice template, mark it as editable with architect
	 *
	 * @param $blacklist_post_types
	 *
	 * @return mixed
	 */
	public static function tcb_post_types( $blacklist_post_types ) {
		if ( tva_is_apprentice() || tva_general_post_is_apprentice() ) {
			if ( empty( $blacklist_post_types['force_whitelist'] ) ) {
				$blacklist_post_types['force_whitelist'] = [];
			}

			$blacklist_post_types['force_whitelist'][] = get_post_type();
		}

		return $blacklist_post_types;
	}

	/**
	 * Filter to add access to ttb
	 */
	public static function on_dashboard_loaded() {
		add_filter( 'thrive_has_access_' . \Thrive_Theme_Product::TAG, [ static::class, 'allow_ttb_access' ] );
	}


	/**
	 * Filters the post thumbnail ID.
	 * Modifies the post thumbnail ID with the cover ID for the course content to later be shared on social media
	 *
	 * @param int|false $thumbnail_id Post thumbnail ID or false if the post does not exist.
	 * @param int|/WP_Post|null $post         Post ID or WP_Post object. Default is global `$post`.
	 *
	 * @return int
	 */
	public static function post_thumbnail_id( $thumbnail_id, $post ) {

		if ( Main::uses_builder_templates() && $post instanceof \WP_Post && in_array( $post->post_type, [ \TVA_Const::LESSON_POST_TYPE, \TVA_Const::MODULE_POST_TYPE, \TVA_Course_Overview_Post::POST_TYPE, \TVA_Course_Completed::POST_TYPE, \TVA_Const::ASSESSMENT_POST_TYPE ] ) ) {
			$post_featured = \TVA\Architect\Visual_Builder\tcb_tva_visual_builder()->get_cover_image();
			if ( ! empty( $post_featured ) ) {
				$thumbnail_id = attachment_url_to_postid( $post_featured );
			}
		}

		return $thumbnail_id;
	}

	/**
	 * The course homepage needs a different meta, else it will be recognized as a normal page
	 *
	 * @param $meta
	 *
	 * @return array
	 */
	public static function thrive_theme_template_meta( $meta ) {

		if ( Main::uses_builder_templates() ) {
			if ( tva_get_settings_manager()->is_index_page( get_the_ID() ) ) {
				$meta = [
					THRIVE_PRIMARY_TEMPLATE   => THRIVE_HOMEPAGE_TEMPLATE,
					THRIVE_SECONDARY_TEMPLATE => \TVA_Const::COURSE_POST_TYPE,
				];
			} elseif ( ! is_editor_page_raw( true ) && ! tva_access_manager()->has_access() ) {
				/**
				 * If the user is on frontend on a module or lesson page and it doesn't have access to it, we display the no access content
				 */
				$meta = [
					THRIVE_PRIMARY_TEMPLATE   => THRIVE_SINGULAR_TEMPLATE,
					THRIVE_SECONDARY_TEMPLATE => is_tax( \TVA_Const::COURSE_TAXONOMY ) || in_array( get_post_type(), [ \TVA_Const::LESSON_POST_TYPE, \TVA_Const::MODULE_POST_TYPE, \TVA_Course_Completed::POST_TYPE, \TVA_Const::ASSESSMENT_POST_TYPE ] ) ? \TVA_Const::NO_ACCESS : \TVA_Const::NO_ACCESS_POST,
					THRIVE_VARIABLE_TEMPLATE  => '',
				];
			}
		}

		if ( ! \TVA_Product::has_access() && tva_is_private_term() ) {
			/**
			 * For non logged in users when accessing demo content we need to redirect to 404 page
			 */
			$meta = [
				THRIVE_PRIMARY_TEMPLATE   => THRIVE_ERROR404_TEMPLATE,
				THRIVE_SECONDARY_TEMPLATE => '',
				THRIVE_VARIABLE_TEMPLATE  => '',
			];
		}

		return $meta;
	}

	/**
	 * This should be done better //TODO find a way to load css from each template - generate file from appr skin
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public static function pre_option_thrive_use_inline_css( $value ) {
		if ( tva_is_apprentice() || tva_general_post_is_apprentice() ) {
			$value = true;
		}

		return $value;
	}

	/**
	 * On rest request, if the appr_skin param has been set, set the current apprentice skin as active
	 *
	 * @param $result
	 * @param $instance
	 * @param $request
	 *
	 * @return mixed
	 */
	public static function rest_pre_dispatch( $result, $instance, $request ) {
		if ( $request instanceof \WP_REST_Request && ! empty( $request->get_param( 'tva_skin' ) ) ) {
			Main::skin();
		}

		return $result;
	}

	/**
	 * For apprentice content we need always to use inline css
	 *
	 * @param boolean $use_inline_css
	 *
	 * @return boolean
	 */
	public static function use_inline_css( $use_inline_css ) {
		if ( ! empty( Main::requested_skin_id() ) && ( tva_is_apprentice() || tva_general_post_is_apprentice() || tva_is_apprentice_template() || Check::typography_page() || tva_is_editable( get_post_type() ) ) ) {
			$use_inline_css = true;
		}

		return $use_inline_css;
	}

	/**
	 * Return the inner frame url for apprentice templates
	 *
	 * @param string           $url
	 * @param \Thrive_Template $template
	 * @param string           $primary
	 * @param string           $secondary
	 * @param string           $variable_template
	 *
	 * @return array|false|int|object|string|\WP_Error|\WP_Term|null
	 */
	public static function thrive_theme_template_url( $url, $template, $primary, $secondary, $variable_template ) {
		if ( Check::apprentice_skin( $template ) ) {
			$from_tar = \Thrive_Utils::from_tar();

			if ( ! empty( $from_tar ) && is_numeric( $from_tar ) ) {
				$variable_template = (int) $from_tar;
			}

			switch ( $template->secondary_template ) {
				case \TVA_Const::COURSE_TAXONOMY:
				case \TVA_Const::MODULE_POST_TYPE:
				case \TVA_Const::LESSON_POST_TYPE:
				case \TVA_Const::NO_ACCESS:
				case \TVA_Const::NO_ACCESS_POST:
				case \TVA_Course_Completed::POST_TYPE:
				case \TVA_Const::ASSESSMENT_POST_TYPE:
					$cookie_data = thrive_content_switch()->get_cookie_data( $template, $template->primary_template, $template->secondary_template, $template->variable_template );
					$prefer_demo = false;
					/* trap the demo content using the secondary_template field - this is how it gets saved in the cookie data */
					if ( ! empty( $cookie_data['secondary_template'] ) && $cookie_data['secondary_template'] === 'thrive_demo_post' ) {
						$prefer_demo = true;
					}
					$content_type = $template->secondary_template;
					/* make sure the correct lesson format is used to preview the template */
					if ( $template->format !== 'standard' && \TVA_Const::LESSON_POST_TYPE === $template->secondary_template ) {
						$content_type = "{$template->format}_lesson";
					}
					$url = Apprentice_Wizard::get_post_or_demo_content_url( $content_type, (int) $variable_template, $prefer_demo );
					break;

				case \TVA_Const::COURSE_POST_TYPE: //School Page check
					$index_page_id = tva_get_settings_manager()->factory( 'index_page' )->get_value();
					$url           = add_query_arg( [ 'tva_is_apprentice' => 1 ], get_permalink( $index_page_id ?: $template->ID ) );
					break;
				case \TVA_Const::CERTIFICATE_VALIDATION_POST:
					$page_id = tva_get_settings_manager()->factory( 'certificate_validation_page' )->get_value();
					$url     = add_query_arg( [ 'tva_is_apprentice' => 1 ], get_permalink( $page_id ?: $template->ID ) );
					break;
			}
		}

		return $url;
	}

	/**
	 * Gets called from content-switch function
	 * Returns the Course overview content depending on the content selected
	 *
	 * @param array  $content
	 * @param string $secondary
	 *
	 * @return array
	 */
	public static function get_content_archive( $content = [], $secondary = '' ) {
		if ( $secondary === \TVA_Const::COURSE_TAXONOMY ) {

			$content = [];
			$args    = [
				'limit'         => CONTENT_SWITCH_ITEMS_TO_LOAD,
				'status'        => 'publish',
				'overview_post' => true,
			];

			if ( ! empty( thrive_content_switch()->number_of_items ) ) {
				$args['offset'] = thrive_content_switch()->number_of_items;
			}

			if ( ! empty( thrive_content_switch()->search ) ) {
				$args['search'] = thrive_content_switch()->search;
			}

			$courses = \TVA_Course_V2::get_items( $args );

			/**
			 * @var \TVA_Course_V2 $course
			 */
			foreach ( $courses as $course ) {
				$url = get_term_link( $course->get_wp_term() );

				$content[] = [
					'id'           => $course->get_id(),
					'title'        => $course->name,
					'url'          => thrive_content_switch()->build_edit_url( $url ),
					'preview_url'  => thrive_content_switch()->build_preview_url( $url ),
					'tar_edit_url' => tcb_get_editor_url( $course->get_overview_post()->ID ),
				];
			}
		}

		return $content;
	}

	/**
	 * Add skin_id param to the editor edit links and preview url
	 *
	 * @param array $params
	 * @param int   $post_id
	 *
	 * @return array
	 */
	public static function tcb_editor_edit_link_query_args( $params = [], $post_id = 0 ) {
		$post_type = get_post_type( $post_id );
		if ( $post_type === THRIVE_TEMPLATE || $post_type === THRIVE_TYPOGRAPHY ) {
			$skin_id = Check::apprentice_skin( $post_id );
			if ( $skin_id ) {
				$params['tva_skin_id'] = $skin_id;
			}
		}

		return $params;
	}

	/**
	 * Outputs the requested skin variables
	 */
	public static function output_skin_variables() {
		if ( ! empty( Main::requested_skin_id() ) && ( tva_is_apprentice() || tva_general_post_is_apprentice() || tva_is_apprentice_template() || Check::typography_page() || tva_is_editable( get_post_type() ) ) ) {
			echo Main::requested_skin()->css();
		}
	}

	/**
	 * Override the skin that is computed into the thrive-theme
	 *
	 * @param \Thrive_Skin $skin
	 *
	 * @return Skin|\Thrive_Skin
	 */
	public static function thrive_theme_override_skin( $skin ) {

		if ( \Thrive_Theme::is_active() && tva_is_private_term() && ! \TVA_Product::has_access() ) {
			/**
			 * If the theme is active and the system is displaying demo content without access we need to return the skin from the theme
			 *
			 * This needs to happen because we need to get the 404 template from the theme skin
			 */
			return $skin;
		}

		if ( ! empty( $_REQUEST['tva_skin_id'] ) ) {
			$skin = Main::skin( (int) $_REQUEST['tva_skin_id'] );
		} elseif ( Main::uses_builder_templates() ) {
			/* if the current page is the editor page, check the post_type being edited */
			$is_editing_apprentice = \is_editor_page_raw() && ( Check::course_overview() || Check::course_item() );
			if ( $is_editing_apprentice || tva_is_apprentice() || tva_general_post_is_apprentice() ) {
				$skin = Main::skin();
			}
		}

		return $skin;
	}

	/**
	 * Include our template and run the template logic
	 *
	 * @param string $template
	 *
	 * @return mixed
	 */
	public static function template_include( $template ) {
		if ( tva_is_apprentice() || tva_general_post_is_apprentice() ) {
			$template = \TVA_Const::plugin_path( 'ttb-bridge/templates/builder-template.php' );
		}

		return $template;
	}

	/**
	 * Set default content for sections on apprentice templates
	 * TODO: add default template files
	 *
	 * @param string          $content
	 * @param \Thrive_Section $section_instance
	 * @param string          $type
	 *
	 * @return string
	 */
	public static function default_section_content( $content, $section_instance, $type ) {

		if ( empty( $content ) && $type === 'content' && tva_is_apprentice() ) {

			switch ( $section_instance->template->get_secondary() ) {
				case \TVA_Const::LESSON_POST_TYPE:
				case \TVA_Const::MODULE_POST_TYPE:
				case \TVA_Const::COURSE_TAXONOMY:
				case \TVA_Const::NO_ACCESS:
				case \TVA_Course_Completed::POST_TYPE:
				case \TVA_Const::ASSESSMENT_POST_TYPE:
					$file = \TVA_Const::plugin_path( 'ttb-bridge/templates/lesson-singular-content.php' );
					break;
				default:
					$file = '';
					break;
			}

			if ( is_file( $file ) ) {
				ob_start();

				include $file;

				$content = ob_get_clean();

				$content = do_shortcode( $content );
			}
		}

		return $content;
	}

	/**
	 * Modify the logo url to point to "school homepage"
	 *
	 * @param string $url
	 *
	 * @return false|string|\WP_Error
	 */
	public static function logo_url( $url ) {
		$is_appr_related = false;

		/* 1. check if this looks like a wizard preview request */
		if ( \TCB_Utils::is_rest() && ! empty( $_REQUEST['skin_id'] ) ) {
			$is_appr_related = true;
		}

		/* 2. check if this is a regular apprentice-related request */
		if ( ! $is_appr_related && tva_is_apprentice() ) {
			$is_appr_related = true;
		}

		if ( $is_appr_related && ( empty( $url ) || strpos( $url, 'thrivethemes.com' ) !== false ) ) {
			/**
			 * If the URL comes empty or the URL is set from the builder website we override the URL to the school homepage
			 * The LOGO url should come dynamic as school homepage from cloud
			 *
			 * This is done to allow the users to insert other URL for the TA Logo
			 */
			$url = tva_get_settings_manager()->factory( 'index_page' )->get_link();
		}

		return $url;
	}

	/**
	 * Checks if request is Apprentice related
	 *
	 * @return bool
	 */
	protected static function is_apprentice_related() {
		$is_appr_related = false;

		/* 1. check if this looks like a wizard preview request */
		if ( \TCB_Utils::is_rest() && ! empty( $_REQUEST['skin_id'] ) ) {
			$is_appr_related = true;
		}

		/* 2. check if this is a regular apprentice-related request */
		if ( ! $is_appr_related && tva_is_apprentice() && ! empty( Main::requested_skin_id() ) ) {
			$is_appr_related = true;
		}

		/* 3. check if it's apprentice template */
		if ( ! $is_appr_related && tva_is_apprentice_template() ) {
			$is_appr_related = true;
		}

		/* 4. check if it's currently in wizard */
		if ( ! $is_appr_related && tva_wizard()->is_during_preview() ) {
			$is_appr_related = true;
		}

		return $is_appr_related;
	}

	/**
	 * Modify the typography styles accordingly based on the skin settings
	 *
	 * if skin should inherit typography from the theme:
	 *      -> if TTB is active, output TTB typography styles
	 *      -> if TTB is not active, do not output anything
	 * if skin should not inherit typography from the theme:
	 *      -> output the skin typography styles
	 *
	 * @param array              $styles
	 * @param \Thrive_Typography $typography
	 *
	 * @return array
	 */
	public static function typography_style_array( $styles, $typography ) {
		// if currently on the typography preview / edit page, return the styles as they are
		if ( Check::typography_page() ) {
			return $styles;
		}

		// if on an apprentice-related piece of content, check skin settings
		if ( Check::apprentice_visual() && \Thrive_Theme::is_active() && Main::requested_skin()->inherit_typography ) {
			// if thrive theme is active, need to output typography from TTB ..
			$active_typography = thrive_skin( 0, false )->get_active_typography();
			if ( $active_typography !== $typography->ID ) {
				$styles = thrive_typography( thrive_skin( 0, false )->get_active_typography() )->get_style();
			}
		}

		return $styles;
	}

	/**
	 * Handles Printing styles from typography when TTB is not active
	 *
	 * @param boolean $print
	 *
	 * @return boolean
	 */
	public static function should_print_unified_styles( $print ) {

		if ( ! \Thrive_Theme::is_active() && ! tva_is_apprentice() && ! tva_general_post_is_apprentice() ) {
			$print = false;
		}

		return $print;
	}

	/**
	 * If thrive theme is not active, remove the "Edit with TAR" view post.php?action=edit page
	 *
	 * @param bool $switch
	 *
	 * @return bool
	 */
	public static function gutenberg_switch( $switch ) {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if ( ! \Thrive_Theme::is_active() && ! is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' ) ) {
			$switch = true;
		}

		return $switch;
	}

	/**
	 * Returns the correct palette when transfering a TVA SKIN
	 *
	 * @param \Thrive_Palette $palette
	 *
	 * @return \Thrive_Palette
	 */
	public static function get_palette( $palette ) {

		if ( ! empty( $_REQUEST['skin_scope'] ) && $_REQUEST['skin_scope'] === 'tva' ) {
			$palette = tva_palettes();
		}

		return $palette;
	}

	/**
	 * Whether or not to allow theme scripts on a TAr editor page. Basically, if TTB is not active, this should be false, except for when editing TA templates
	 *
	 * @param boolean $allow
	 *
	 * @return boolean
	 */
	public static function allow_theme_scripts( $allow ) {
		if ( ! \Thrive_Theme::is_active() && ! \Thrive_Utils::is_theme_template() ) {
			$allow = false;
		}

		return $allow;
	}

	/**
	 * Filter the post types that should be ignored when editing content, depending on the whether TTB is active and TA uses builder templates
	 *
	 * @param string[] $ignored_post_types
	 *
	 * @return mixed
	 */
	public static function ignored_post_types( $ignored_post_types ) {
		/* If TTB is not active, we need to unset page and post from there */
		if ( ! \Thrive_Theme::is_active() && ! \Thrive_Utils::is_theme_template() ) {
			$ignored_post_types [] = 'post';
			$ignored_post_types [] = 'page';
		}

		/* if visual editing is not used, do not show "you are editing content" on the following post types from apprentice */
		if ( ! Main::uses_builder_templates() || \Thrive_Utils::in_theme_dashboard() ) {
			$ignored_post_types [] = \TVA_Const::LESSON_POST_TYPE;
			$ignored_post_types [] = \TVA_Const::CHAPTER_POST_TYPE;
			$ignored_post_types [] = \TVA_Const::MODULE_POST_TYPE;
			$ignored_post_types [] = \TVA_Course_Overview_Post::POST_TYPE;
			$ignored_post_types [] = \TVA_Course_Completed::POST_TYPE;
			$ignored_post_types [] = \TVA_Const::ASSESSMENT_POST_TYPE;
		}
		$ignored_post_types [] = \TVA_Access_Restriction::POST_TYPE;
		$ignored_post_types [] = \TVA_Product_Access_Restriction::POST_TYPE;

		if ( ! \Thrive_Theme::is_active() && ! tva_is_apprentice_template() && ( Main::uses_builder_templates() && ! in_array( get_post_type(), [
					\TVA_Const::LESSON_POST_TYPE,
					\TVA_Const::MODULE_POST_TYPE,
					\TVA_Course_Overview_Post::POST_TYPE,
					\TVA_Course_Completed::POST_TYPE,
					\TVA_Const::ASSESSMENT_POST_TYPE,
				] ) ) ) {
			/**
			 * Fix the case when you can not edit Custom Post types with TAR if apprentice is active and theme builder is not active
			 */
			$public_post_types = get_post_types( [ 'public' => true ], 'names' );

			$ignored_post_types = array_merge( $ignored_post_types, array_keys( $public_post_types ) );
		}

		return $ignored_post_types;
	}

	/**
	 * Check if the Builder should load TAR components
	 *
	 * @param bool $should_load
	 *
	 * @return bool
	 */
	public static function load_content_components( $should_load ) {
		if ( ! \Thrive_Theme::is_active() && Main::uses_builder_templates() && in_array( get_post_type(), [
				\TVA_Const::LESSON_POST_TYPE,
				\TVA_Const::MODULE_POST_TYPE,
				\TVA_Course_Overview_Post::POST_TYPE,
				\TVA_Course_Completed::POST_TYPE,
				\TVA_Const::ASSESSMENT_POST_TYPE,
			] ) ) {

			$should_load = true;
		}

		return $should_load;
	}

	/**
	 * Override the author bio shortcode with what has been setup for the current course
	 *
	 * This can also be called in a REST API request, when fetching default data for an apprentice template inline shortcodes
	 *
	 * @param string $author_bio
	 *
	 * @return string
	 */
	public static function author_bio_shortcode( $author_bio ) {
		/* also support ajax-loading for cloud templates */
		$maybe_apprentice_ajax = wp_doing_ajax() && ! empty( tva_course()->get_id() );

		if ( \TCB_Utils::is_rest() ) {
			if ( get_the_ID() && Check::course_item() ) {
				$post   = new \TVA_Post( get_the_ID() );
				$course = $post->get_course_v2();
			}
		} elseif ( $maybe_apprentice_ajax || tva_is_apprentice() ) {
			$course = tva_course();
		}

		if ( ! empty( $course ) ) {
			$author_bio = $course->get_author()->get_bio();
			if ( empty( $author_bio ) ) {
				$author_bio = __( 'No Author Description', 'thrive-apprentice' );
			} else {
				/* don't allow <p> elements here. TODO this is an inline shortcode. HTML tags are not quite supported. Block tags like <ul>, <ol>, headings etc will break the editor */
				$author_bio = preg_replace( '#<p(.*?)>|</p>#s', '', $author_bio );
			}
		}

		return $author_bio;
	}

	/**
	 * Modify the href attribute for the "Chapter" breadcrumb item. There's no special page for the chapter,
	 * so just point the link to the course overview page
	 *
	 * @param string $href
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public static function breadcrumb_permalink( $href, $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( $post_type === \TVA_Const::CHAPTER_POST_TYPE ) {
			$course = \TVA_Post::factory( get_post( $post_id ) )->get_course_v2();
			if ( $course ) {
				$href = $course->get_link() . "#tva-chapter-{$post_id}";
			}
		} elseif ( $post_type === \TVA_Const::MODULE_POST_TYPE ) {
			$href = get_permalink( $post_id );
		}

		return $href;
	}

	/**
	 * Modify the root breadcrumb item from the homepage to the school homepage, and add the course to the breadcrumbs on the second position
	 *
	 * @param string[] $items
	 * @param int      $index
	 * @param boolean  $in_editor
	 *
	 * @return string[]
	 */
	public static function breadcrumb_root_items( $items, $index, $in_editor = false ) {
		if ( ! tva_is_apprentice() ) {
			return $items;
		}

		$index         = 0;
		$index_page_id = (int) tva_get_settings_manager()->factory( 'index_page' )->get_value();
		if ( $index_page_id === get_the_ID() ) {
			/* we are on the course index page (school homepage) - no root item */
			return [];
		}

		$index_page = get_post( $index_page_id );
		/* 1. add the school homepage */
		$items = [
			\Thrive_Breadcrumbs::create_item( $index, $index_page->post_title, get_permalink( $index_page ), [ 'home', $in_editor ? 'home-label' : '' ] ),
		];

		$course = tva_course();
		if ( empty( $course ) || empty( $course->get_id() ) ) {
			/* nothing more to add here */
			return $items;
		}

		/* if not on a course page, also add the course to the list of breadcrumbs */
		if ( is_singular( [ \TVA_Const::MODULE_POST_TYPE, \TVA_Const::LESSON_POST_TYPE, \TVA_Const::NO_ACCESS, \TVA_Course_Completed::POST_TYPE, \TVA_Const::ASSESSMENT_POST_TYPE ] ) ) {
			$items [] = \Thrive_Breadcrumbs::create_item( $index, $course->get_wp_term()->name, $course->get_link() );
		}

		return $items;
	}

	/**
	 * On the course page, do not prepend "Courses: " string to the breadcrumb item
	 *
	 * @param string|null $title
	 *
	 * @return string
	 */
	public static function breadcrumb_archive_title( $title ) {
		if ( Check::course_overview() ) {
			$object = get_queried_object();
			if ( $object instanceof \WP_Term ) {
				$title = tva_course()->name;
			}
		}

		return $title;
	}

	/**
	 * Prevent THEME edit button being displayed on TA pages
	 *
	 * @param bool $allow_editing
	 *
	 * @return bool
	 */
	public static function display_ttb_sidebar_icon( $allow_editing ) {
		$skip_page = tva_get_settings_manager()->is_checkout_page();
		$skip_page = $skip_page || tva_get_settings_manager()->is_thankyou_multiple_page();
		$skip_page = $skip_page || tva_get_settings_manager()->is_thankyou_page();

		if ( ! Main::uses_builder_templates() ) {
			$skip_page = $skip_page || ( \TVA_Course_Overview_Post::POST_TYPE === get_post_type() );
		}

		if ( $skip_page ) {
			$allow_editing = false;
		}

		return $allow_editing;
	}

	/**
	 * Make sure that when editing a course overview post ( the TAr content for the course's associated post ) the correct post
	 * object is returned instead of the course taxonomy
	 *
	 * @param array $queried_object
	 *
	 * @return array
	 */
	public static function filter_editor_queried_object( $queried_object ) {
		if ( tcb_editor()->is_inner_frame() && tva_is_apprentice() && Check::course_overview() ) {
			$course = tva_course();

			if ( $course ) {
				$queried_object['ID'] = $course->get_overview_post( true )->ID;
			}
		}

		return $queried_object;
	}

	/**
	 * Make sure that a "Edit with TAr" icon is displayed when editing a TTB template for the course overview
	 *
	 * @param bool $allowed
	 *
	 * @return bool
	 */
	public static function allow_architect_switch( $allowed ) {

		$template = thrive_apprentice_template();

		if ( ! $allowed && \Thrive_Utils::is_theme_template() ) {
			if ( Check::apprentice_skin( $template ) && $template->is_course_overview() ) {
				$allowed = true;
			}
		}

		if ( $template->is_certificate_verification() ) {
			$allowed = false;
		}

		return $allowed;
	}

	/**
	 * Make sure TTB localization is outputted where needed, even if TTB is inactive
	 *
	 * @param bool $allow
	 *
	 * @return bool
	 */
	public static function enable_theme_localization( $allow ) {
		if ( ! $allow && array_key_exists( get_post_type(), \Thrive_Utils::get_content_types() ) ) {
			$allow = true;
		}

		return $allow;
	}

	/**
	 * URL of demo content displayed when editing a TA template
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function demo_content_url( $url ) {
		$template = thrive_apprentice_template();
		if ( Check::apprentice_skin( $template ) ) {
			if ( $template->is_singular() ) {
				// module or lesson - get the first module/lesson post that was created, those should be the demo posts
				$args = [
					'post_type'      => $template->secondary_template === \TVA_Const::NO_ACCESS ? \TVA_Const::LESSON_POST_TYPE : $template->secondary_template,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'meta_query'     => [
						[
							'key'   => 'tva_is_demo',
							'value' => '1',
						],
					],
				];
				if ( $template->secondary_template === \TVA_Const::LESSON_POST_TYPE && 'standard' !== $template->format ) {
					$args['meta_query'] [] = [
						[
							'key'   => 'tva_lesson_type',
							'value' => $template->format,
						],
					];
				}
				$posts = get_posts( $args );
				if ( ! empty( $posts[0] ) ) {
					$url = get_permalink( $posts[0] );
				}
			} elseif ( $template->is_course_overview() ) {
				$url = Apprentice_Wizard::get_post_or_demo_content_url( $template->secondary_template, $template->variable_template, true );
			}
		}

		return $url;
	}

	/**
	 * Filter the default get_posts() arguments used in the content switcher when editing a template
	 *
	 * @param array  $args
	 * @param string $context
	 *
	 * @return array
	 */
	public static function content_switch_post_args( $args, $context ) {
		$template = thrive_apprentice_template();
		if ( $context === 'content_switch' && Check::apprentice_skin( $template ) && $template->is_singular() ) {

			if ( $args['post_type'] === \TVA_Const::NO_ACCESS ) {
				/**
				 * For no Access template we need to fetch the Module & Lesson content
				 */
				$args['post_type'] = array( \TVA_Const::LESSON_POST_TYPE, \TVA_Const::MODULE_POST_TYPE );
			} elseif ( $args['post_type'] === \TVA_Const::NO_ACCESS_POST ) {
				$args['post_type'] = array( 'post', 'page' );
			} elseif ( $args['post_type'] === \TVA_Const::LESSON_POST_TYPE ) {
				/* make sure only lessons have the same format are displayed in the content switcher */
				$lesson_type_meta   = $template->format === 'standard' ? 'text' : $template->format;
				$args['meta_query'] = [
					'relation' => 'AND',
					[
						'key'   => 'tva_lesson_type',
						'value' => $lesson_type_meta,
					],
					$args['meta_query'],
				];
			} elseif ( $args['post_type'] === \TVA_Const::CERTIFICATE_VALIDATION_POST ) {
				$page_id           = tva_get_settings_manager()->factory( 'certificate_validation_page' )->get_value();
				$args['post_type'] = array( 'post', 'page' );
				$args['post__in']  = [ $page_id ];
			}

			//exclude modules/lessons from the default demo courses
			$demo_course_ids = array_map( static function ( $course ) {
				return $course->get_id();
			}, \TVA_Course_V2::get_items( [ 'status' => 'private' ] ) );

			if ( empty( $args['tax_query'] ) ) {
				$args['tax_query'] = [];
			}
			$args['tax_query'] [] = [
				'taxonomy' => \TVA_Const::COURSE_TAXONOMY,
				'field'    => 'term_id',
				'operator' => 'NOT IN',
				'terms'    => $demo_course_ids,
			];
		}

		return $args;
	}

	/**
	 * Modify the list of content switch items to include icons for lessons
	 *
	 * @param array $items
	 * @param array $args
	 *
	 * @return mixed
	 */
	public static function filter_content_switch_items( $items, $args ) {
		if ( $args['post_type'] === \TVA_Const::LESSON_POST_TYPE ) {
			foreach ( $items as $i => $lesson ) {
				$lesson_type                 = get_post_meta( $lesson['id'], 'tva_lesson_type', true );
				$items[ $i ]['prepend_html'] = tcb_icon( "lesson-{$lesson_type}", true, 'sidebar', 'tva-lesson-type' );
			}
		}

		return $items;
	}

	/**
	 * Modify the displayed content name in content switcher
	 *
	 * @param string           $content_name
	 * @param \Thrive_Template $template
	 * @param array            $cookie_data
	 *
	 * @return string
	 */
	public static function content_switch_name( $content_name, $template, $cookie_data ) {
		if ( empty( $content_name ) && empty( $cookie_data['name'] ) && Check::apprentice_skin( $template ) ) {

			$demo_content = __( 'Demo Content', THEME_DOMAIN );

			$prefer_demo = false;
			/* trap the demo content using the secondary_template field - this is how it gets saved in the cookie data */
			if ( ! empty( $cookie_data['secondary_template'] ) && $cookie_data['secondary_template'] === 'thrive_demo_post' ) {
				$prefer_demo = true;
			}
			$content_type = $template->secondary_template;
			/* make sure the correct lesson format is used to preview the template */
			if ( $template->format !== 'standard' && \TVA_Const::LESSON_POST_TYPE === $template->secondary_template ) {
				$content_type = "{$template->format}_lesson";
			}
			// get the name for the first applicable content that will actually be displayed
			$object = Apprentice_Wizard::get_object_or_demo_content( $content_type, ! empty( $cookie_data['variable_template'] ) ? $cookie_data['variable_template'] : $template->variable_template, $prefer_demo );
			if ( $object instanceof \WP_Post ) {
				$content_name = $object->tva_is_demo ? $demo_content : $object->post_title;
			} elseif ( $object instanceof \WP_Term ) {
				$course_status = get_term_meta( $object->term_id, 'tva_status', true );
				$content_name  = $course_status === 'private' ? $demo_content : $object->name;
			}
		}

		return $content_name;
	}

	/**
	 * Adds the tva_skin_id parameter to content switch edit and preview URLs
	 *
	 * @param array $args
	 *
	 * @return array|mixed
	 */
	public static function content_switch_url_args( $args = [] ) {

		if ( ! empty( $_REQUEST['tva_skin_id'] ) ) {
			$args['tva_skin_id'] = $_REQUEST['tva_skin_id'];
		} elseif ( ! empty( $_REQUEST['template_id'] ) && Check::apprentice_skin( $_REQUEST['template_id'] ) ) {
			$apprentice_template = thrive_apprentice_template( $_REQUEST['template_id'] );

			$args['tva_skin_id'] = $apprentice_template->get_skin_id();
		}

		return $args;
	}


	/**
	 * Post visibility options blacklist. On these post types there should be no "Post options" breadcrumb.
	 * If visual editing is enabled, the option should always be displayed
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public static function blacklist_post_visibility_options( $post_types ) {
		if ( ! Main::uses_builder_templates() ) {

			$post_types = array_merge( $post_types, array(
				\TVA_Const::LESSON_POST_TYPE,
				\TVA_Const::MODULE_POST_TYPE,
				\TVA_Const::OLD_POST_TYPE,
				\TVA_Const::COURSE_POST_TYPE,
				\TVA_Const::CHAPTER_POST_TYPE,
				\TVA_Course_Overview_Post::POST_TYPE,
				\TVA_Course_Completed::POST_TYPE,
				\TVA_Const::ASSESSMENT_POST_TYPE,
			) );
		}

		return $post_types;
	}

	/**
	 * Hide the "Status & Visibility" options from the post options
	 *
	 * @param array $element_config
	 *
	 * @return array
	 */
	public static function hide_post_options( $element_config ) {
		if ( Check::course_item() || Check::course_overview() || Check::course_certificate() ) {

			$element_config = \Thrive_Architect::tcb_post_element_extend_config( $element_config );

			unset( $element_config['post'], $element_config['page_content_settings'] );
			remove_filter( 'tcb_element_post_config', [ \Thrive_Utils::class, 'tcb_element_post_config' ] );
		}

		return $element_config;
	}

	/**
	 * Modify the query vars that will be sent in REST requests from the editor.
	 * Of interest here is setting the correct course taxonomy when editing the course overview template
	 *
	 * @param array $query_vars
	 *
	 * @return array
	 */
	public static function localize_query_vars( $query_vars ) {
		/* identify the correct case - editing a course overview template */
		if ( Check::course_overview() && Check::apprentice_skin( thrive_template() ) ) {
			global $wp_query;
			/* this will contain all necessary query vars, usually a [taxonomy => term_slug] array */
			$query_vars = $wp_query->query;
		}

		return $query_vars;
	}

	/**
	 * For lesson templates, make sure the format is taken into account - add a meta_query for template format
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function singular_template_args( $args ) {
		/* it only applies to lessons */
		if ( is_singular( \TVA_Const::LESSON_POST_TYPE ) ) {

			$tva_lesson  = \TVA_Post::factory( get_post() );
			$lesson_type = $tva_lesson->get_type();

			/**
			 * if user doesn't have access to lesson the template used has to be the default one
			 */
			if ( false === tva_access_manager()->has_access_to_object( $tva_lesson->get_the_post() ) || tva_access_manager()->is_object_locked( $tva_lesson->get_the_post() ) ) {
				$lesson_type = 'text';
			}

			$meta_query_format = [
				'key'   => 'format',
				'value' => $lesson_type === 'text' || empty( $lesson_type ) ? THRIVE_STANDARD_POST_FORMAT : $lesson_type,
			];
			$meta_query        = isset( $args['meta_query'] ) ? $args['meta_query'] : [];
			/* first, try to search for an existing `format` meta_query */
			foreach ( $meta_query as & $query_item ) {
				if ( $query_item['key'] === 'format' ) {
					$query_item['value'] = $meta_query_format['value'];
					$found               = true;
				}
			}
			unset( $query_item );
			/* second, if no `format` query found => append it to the meta_query */
			if ( ! isset( $found ) ) {
				$meta_query [] = $meta_query_format;
			}
			$args['meta_query'] = $meta_query;
		} elseif ( tva_get_settings_manager()->is_certificate_validation_page( get_post() ) ) {
			/**
			 * Overwrite these arguments used for fetching Skin Template Posts from DB
			 * in order to match the certificate templates
			 */
			foreach ( $args['meta_query'] as $key => $meta_query ) {
				if ( $meta_query['key'] === 'secondary_template' ) {
					$args['meta_query'][ $key ]['value'] = \TVA_Const::CERTIFICATE_VALIDATION_POST;
				}
			}
		}

		return $args;
	}

	/**
	 * For Apprentice Skins we need to remove the custom comments template the TA comes with
	 *
	 * @param $template
	 *
	 * @return mixed
	 */
	public static function handle_comments_template( $template ) {
		if ( tva_is_apprentice() && Check::apprentice_skin( thrive_template() ) ) {
			remove_filter( 'comments_template', 'tva_handle_comments_template', 1000 );

			if ( ! file_exists( $template ) && ! Main::is_thrive_theme_active() && file_exists( Main::get_builder_path() . '/comments.php' ) ) {
				/**
				 * If the comments.php file doens't exist, we include the one from the theme-builder
				 *
				 * comments.php file is removed in the 2022 wp theme - comments.php is replaced with gutenberg blocks
				 */
				$template = Main::get_builder_path() . '/comments.php';
			}
		}

		return $template;
	}

	/**
	 * Toggle the comments status for content controlled by apprentice skin
	 *
	 * @param boolean $open
	 * @param integer $post_id
	 *
	 * @return bool
	 */
	public static function toggle_comments( $open, $post_id ) {
		if ( tva_is_apprentice() && \Thrive_Utils::is_inner_frame() && Check::apprentice_skin( thrive_template() ) ) {
			$open = true;
		}

		return $open;
	}

	/**
	 * Returns the comments closed HTML defined in the editor
	 */
	public static function get_closed_comments_content_frontend() {
		if ( tva_is_apprentice() ) { //tcm - thrive comments
			if ( function_exists( 'tcm' ) ) {
				echo '<style>#comments.comments-area{display:none !important;}</style>';
			} else {
				echo thrive_theme_comments()->get_closed_comments_content_frontend( '' );
			}
		}
	}

	/**
	 * @param bool $active
	 *
	 * @return bool
	 */
	public static function tc_active( $active = true ) {
		if ( tva_is_apprentice() && ! comments_open() ) {
			$active = false;
		}

		return $active;
	}

	/**
	 * Check if Thrive Comments is allowed on Apprentice Templates
	 * If active, Thrive Comments should be allowed on Lesson, Module, and Course Overview Template
	 *
	 * @param bool $allow
	 *
	 * @return bool
	 */
	public static function allow_thrive_comments( $allow = false ) {

		if ( \Thrive_Utils::is_theme_template() ) {
			$template = thrive_apprentice_template();
			if ( Check::apprentice_skin( $template ) && ( $template->is_course_overview() || $template->is_lesson() || $template->is_module() ) ) {
				$allow = true;
			}
		}

		return $allow;
	}

	/**
	 * Modify the course list args for wizard
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function course_list_get_courses_args( $args = [] ) {
		if ( Apprentice_Wizard::is_frontend() && empty( \TVA_Course_V2::get_items( [ 'status' => 'publish' ], true ) ) ) {
			$args['status'] = 'private';
		}

		return $args;
	}

	/**
	 * Only show headers that come from cloud and are associated with the given apprentice skin
	 *
	 * @param array $sections
	 *
	 * @return array|mixed
	 */
	public static function remove_default_hf_templates( $sections = [] ) {
		if ( ! empty( $_REQUEST['tva_skin_id'] ) ) {

			/*Remove the general headers from the list*/
			$sections = array_values( array_filter( $sections, static function ( $section
			) {
				return ! empty( $section['skin_tag'] );
			} ) );
		}

		return $sections;
	}

	/**
	 * Make comments element available in course overview editor
	 *
	 * @param boolean $hide
	 *
	 * @return boolean
	 */
	public static function template_hide_comments_element( $hide ) {
		if ( \Thrive_Utils::is_theme_template() ) {
			$template = thrive_apprentice_template();
			if ( Check::apprentice_skin( $template ) && $template->is_course_overview() ) {
				$hide = false;
			}
		}

		return $hide;
	}

	/**
	 * Preview Functionality for a TVA Skin
	 * Adds the skin_id parameter in the link if present
	 *
	 * @param string            $permalink
	 * @param \WP_Post|\WP_Term $post_or_term
	 *
	 * @return string
	 */
	public static function maybe_add_tva_skin_id( $permalink, $post_or_term ) {

		/**
		 * If the request contains tva_skin_id variable and the request is not an editor page request we add the tva_skin_id variable in the link
		 * Solves the use case when users add a static link (to a random page) in editor page and the system adds the tva_skin_id to that link
		 */
		if ( ! empty( $_REQUEST['tva_skin_id'] ) && ! is_editor_page_raw( true ) ) {
			$permalink = add_query_arg( [ 'tva_skin_id' => $_REQUEST['tva_skin_id'] ], $permalink );
		}

		return $permalink;
	}

	/**
	 * For the time being, lightspeed optimized asset loading is disabled for Thrive Apprentice. This makes sure we load the full thrive_flat style
	 *
	 * @param bool $require
	 *
	 * @return bool|mixed
	 */
	public static function should_load_flat( $require ) {
		/**
		 * This is to be used until TA integrates lightspeed
		 */
		if ( $require === false && ( tva_is_apprentice() || tva_general_post_is_apprentice() ) ) {
			$require = true;
		}

		return $require;
	}

	/**
	 * Allows theme template style logic
	 *
	 * Used when a landing page is protected via a product, to load the general no access template css
	 *
	 * @param boolean $block
	 *
	 * @return boolean
	 */
	public static function block_theme_template_styles( $block ) {
		if ( $block === true && tva_general_post_is_apprentice() ) {
			$block = false;
		}

		return $block;
	}

	/**
	 * Inside the editor, for no access template we always return no content
	 *
	 * @param $content
	 *
	 * @return mixed
	 */
	public static function editor_template_content( $content ) {
		if ( \Thrive_Utils::is_inner_frame() && is_editor_page_raw( true ) ) {

			$template_id = \Thrive_Utils::inner_frame_id();
			if ( ! empty( $template_id ) && ( thrive_apprentice_template( $template_id )->is_no_access() || thrive_apprentice_template( $template_id )->is_general_no_access() ) ) {
				$content = '';
			}
		}

		return $content;
	}

	/**
	 * Replace the redirect link from edit page to template dashboard
	 *
	 * @param string   $redirect_link
	 * @param \WP_Post $post
	 *
	 * @return string
	 */
	public static function template_dashboard_redirect( $redirect_link, $post ) {

		if ( tva_is_apprentice_template() ) {
			$redirect_link = admin_url( 'admin.php?page=thrive_apprentice#design/' . Main::requested_skin_id() . '/skin-templates' );
		}

		return $redirect_link;
	}

	/**
	 * Allow the system to change the course overview template if the user changes it from content control
	 *
	 * @param array $templates
	 * @param array $args
	 * @param array $template_meta
	 *
	 * @return array|int[]|mixed|\WP_Post[]
	 */
	public static function theme_default_templates( $templates = [], $args = [], $template_meta = [] ) {

		if ( ! empty( $template_meta['secondary_template'] ) && $template_meta['secondary_template'] === \TVA_Const::COURSE_TAXONOMY && Check::course_overview() ) {
			$object = get_queried_object();

			if ( $object instanceof \WP_Term ) {
				$course = new \TVA_Course_V2( $object->term_id );

				$overview_post = $course->has_overview_post();

				if ( $overview_post instanceof \WP_Post ) {
					$custom_templates = \Thrive_Utils::get_page_custom_templates( $overview_post->ID );

					if ( ! empty( $custom_templates ) ) {
						$templates = $custom_templates;
					}
				}
			}
		}

		return $templates;
	}


	/**
	 * Theme Section Markup Filter
	 *
	 * Checks if content section has no content hides the section from front-end
	 *
	 * @param string          $section_html
	 * @param \Thrive_Section $theme_section
	 *
	 * @return mixed
	 */
	public static function theme_section_html( $section_html, $theme_section ) {

		if ( ! is_editor_page_raw( true ) && Check::course_item() && $theme_section->type() === 'content' && isset( $theme_section->empty_content ) && $theme_section->empty_content ) {
			$section_html = '';
		}

		return $section_html;
	}

	/**
	 * Alter post content in frontend in certain cases
	 *
	 * @param string $return  Content to return
	 * @param string $content actual post content before wrap
	 *
	 * @return string
	 */
	public static function get_frontend_post_content( $return, $content ) {
		if ( ! is_editor_page_raw( true ) && Main::uses_builder_templates() &&
			 in_array( get_post_type(), [ \TVA_Const::LESSON_POST_TYPE, \TVA_Const::MODULE_POST_TYPE ] ) &&
			 ( empty( $content ) || ( defined( 'TVE_FLAG_HTML_ELEMENT' ) && $content === TVE_FLAG_HTML_ELEMENT ) ) ) {
			$return = '';
		}

		return $return;
	}

	/**
	 * Gives access to the ttb if the user has access to apprentice
	 *
	 * @param $has_access
	 *
	 * @return mixed
	 */
	public static function allow_ttb_access( $has_access ) {
		if ( ! $has_access && ! \Thrive_Theme::is_active() ) {
			$has_access = \TVA_Product::has_access();
		}

		return $has_access;
	}

	/**
	 * For theme builder only apply the product settings for post titles
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public static function maybe_modify_post_title( $title, $id ) {
		if ( Main::is_thrive_theme_active() && ! tva_access_manager()->has_access_to_object( get_post( $id ) ) ) {
			$product = \TVA\Product::get_from_set( \TVD\Content_Sets\Set::get_for_object( get_post( $id ), $id ) );

			if ( $product instanceof \TVA\Product ) {
				$title = tva_access_restriction_settings( $product->get_term() )->the_title( '', '', false );
			}
		}

		return $title;
	}

	/**
	 * If the commented post is the one assigned for course comments, return course title instead of post title
	 *
	 * @param string      $post_title
	 * @param \WP_Comment $comment
	 *
	 * @return false|mixed|string
	 */
	public static function comments_mail_post_title( $post_title, $comment ) {
		if ( ! $comment instanceof \WP_Comment || ! $comment->comment_post_ID ) {
			return $post_title;
		}

		$post = get_post( $comment->comment_post_ID );
		if ( $post->post_type === \TVA_Const::COURSE_POST_TYPE && (int) $post->ID === (int) get_option( 'tva_course_hidden_post_id' ) ) {
			$course_id = get_comment_meta( $comment->comment_ID, 'tva_course_comment_term_id', true );

			$term = get_term( $course_id, \TVA_Const::COURSE_TAXONOMY );
		}

		return ! empty( $term ) ? $term->name : $post_title;
	}

	/**
	 * @param $output
	 * @param $tag
	 * @param $attr
	 * @param $m
	 *
	 * @return mixed
	 */
	public static function handle_render_third_party_shortcodes( $output, $tag, $attr, $m ) {

		/**
		 * If ThriveTheme is active, the logic is controlled by ThriveTheme
		 *
		 * If the admin is inside TAR Editor and we render apprentice content, do not render third party shortcodes. Always display shortcode tags in this case
		 */
		if ( ! \Thrive_Theme::is_active() && tva_is_apprentice() && \TCB_Utils::in_editor_render( true ) ) {
			$is_thrive_shortcode = \Thrive_Utils::is_thrive_shortcode( $tag );

			if ( ! empty( $m[0] ) && ! $is_thrive_shortcode && \TCB_Utils::in_editor_render( true ) ) {
				$output = $m[0];
			}
		}

		return $output;
	}
}
