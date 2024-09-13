<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Visual_Builder;

use TCB\VideoReporting\Video;
use TCB_Element_Abstract;
use TD_TTW_User_Licenses;
use Thrive\Theme\Integrations\WooCommerce\Helpers;
use Thrive_Theme;
use Thrive_Utils;
use Thrive_Video_Post_Format_Main;
use TVA\Architect\Utils;
use TVA\TTB\Check;
use TVA_Access_Restriction;
use TVA_Audio;
use TVA_Author;
use TVA_Const;
use TVA_Course_Certificate;
use TVA_Course_Completed;
use TVA_Course_Overview_Post;
use TVA_Course_V2;
use TVA_Lesson;
use TVA_Post;
use TVA_Topic;
use TVA_Video;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;
use function TVA\TTB\thrive_apprentice_template;
use function TVA\TTB\tva_palettes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Hooks
 *
 * @package  TVA\Architect\Visual_Builder
 * @project  : thrive-apprentice
 */
class Hooks {

	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		$this->actions();
		$this->filters();
		$this->compatibility_hooks();
	}

	/**
	 * Contains a list of actions & filters that make visual builder for apprentice to work with other plugins & themes
	 *
	 * @return void
	 */
	public function compatibility_hooks() {

		/**
		 * Fix: when custom fonts plugin is active, it returns fatal error.
		 * The reason behind this is that the 'wp' filter has a implementation with callback 5 and it loads some logic onto it
		 *
		 * Compatibility with Custom Fonts plugin By Brainstorm Force
		 *
		 * https://wordpress.org/plugins/custom-fonts/
		 */
		add_action( 'custom_fonts_update_before', [ $this, 'set_objects' ] );
	}

	public function actions() {
		add_action( 'wp', [ $this, 'set_objects' ] );

		add_action( 'thrive_theme_section_before_download', [ $this, 'set_objects' ] );
		add_action( 'thrive_section_template_download', [ $this, 'set_objects' ] );

		add_action( 'tcb_before_get_content_template', [ $this, 'set_objects' ] );

		add_action( 'tcb_before_load_content_template', [ $this, 'set_objects' ] );

		add_action( 'tva_set_objects', [ $this, 'set_objects' ] );
	}

	public function filters() {
		add_filter( 'tcb_content_allowed_shortcodes', [ $this, 'content_allowed_shortcodes_filter' ] );

		add_filter( 'tcb_categories_order', [ $this, 'add_category_to_order' ] );

		add_filter( 'tcb_element_instances', [ $this, 'tcb_element_instances' ] );

		add_filter( 'tcb_remove_instances', [ $this, 'tcb_remove_instances' ], 101 );//We need to execute this after the TTB one. TTB also adds elements on remove_instances filter

		add_filter( 'tcb_main_frame_localize', [ $this, 'main_frame_localize' ], 11 );//We need to execute after the TTB one

		add_filter( 'tcb_inline_shortcodes', [ $this, 'inline_shortcodes' ] );

		add_filter( 'tcb_dynamic_field_author', [ $this, 'author_image' ] );
		add_filter( 'tcb_dynamic_field_featured', [ $this, 'featured_image' ] );

		add_filter( 'tve_dash_admin_bar_nodes', [ $this, 'admin_bar_nodes' ], 10, 1 );
		add_filter( 'thrive_theme_content_types', [ $this, 'theme_builder_content_types' ] );

		add_filter( 'tcb_display_button_in_admin_bar', [ $this, 'edit_with_tar_display' ] );

		add_filter( 'tva_enqueue_frontend', [ $this, 'tva_enqueue_frontend' ] );

		add_filter( 'thrive_theme_audio_post_custom_content', [ $this, 'render_audio_lesson_custom_content' ], 10, 2 );
		add_filter( 'thrive_theme_video_post_custom_content', [ $this, 'render_video_lesson_custom_content' ], 10, 3 );
		add_filter( 'thrive_theme_video_post_custom_content', [ $this, 'render_video_course_overview_custom_content' ], 10, 2 );
		add_filter( 'thrive_theme_video_post_type', [ $this, 'video_lesson_type' ], 10, 2 );
		add_filter( 'thrive_theme_video_post_type', [ $this, 'video_course_overview_type' ], 10, 2 );

		add_filter( 'thrive_theme_sidebar_icon_redirect', [ $this, 'sidebar_icon_redirect' ] );

		add_filter( 'tcb_editor_javascript_params', [ $this, 'editor_javascript_params' ], 10, 3 );

		add_filter( 'tcb_post_content_element_category', [ $this, 'post_content_element_category' ] );
		add_filter( 'tcb_post_content_element_title', [ $this, 'post_content_element_title' ] );
		add_filter( 'tcb_post_author', [ $this, 'get_course_content_author' ] );

		add_filter( 'architect.branding', [ $this, 'architect_branding' ], 11, 2 );
		add_filter( 'ttb_branding', [ $this, 'ttb_branding' ] );

		add_filter( 'tcb_cloud_request_params', [ $this, 'cloud_request_params' ], PHP_INT_MAX );

		add_filter( 'thrive_ignored_post_types', [ $this, 'ignored_post_types' ] );

		add_filter( 'thrive_theme_visibility_config_post_format', [ $this, 'visibility_config_post_format' ], 10, 2 );

		add_filter( 'thrive_theme_visibility_config', [ $this, 'visibility_config' ], 10, 2 );

		add_filter( 'tcb_allow_landing_page_edit', [ $this, 'allow_landing_page_edit' ] );

		add_filter(
			'tcb_gutenberg_switch',
			static function ( $switch ) {

				if ( tva_get_settings_manager()->is_certificate_validation_page() ) {
					$switch = true;
				}

				return $switch;
			},
			12
		);
		// Identify cloud requests as being apprentice templates
		add_filter( 'tcb_cloud_request_params', static function ( $params ) {
			if ( ! empty( $_REQUEST['tva_skin_id'] ) || ! empty( $params['appr'] ) ) {
				if ( empty( $params['filters'] ) ) {
					$params['filters'] = [];
				}

				$params['filters']['scope'] = 'tva';
			}

			return $params;
		}, 11 );
	}

	/**
	 * Renders HTML for dynamic video shortcode set on TTB templates(course overview template)
	 * - if in TTB template editor something has to be rendered, even an overlay
	 * - in frontend the shortcode is rendered only if a dynamic video element DOES NOT exists in course overview post's content
	 *
	 * @param string $content
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public function render_video_course_overview_custom_content( $content, $post_id ) {

		if ( get_post_type( $post_id ) === TVA_Course_Overview_Post::POST_TYPE ) {

			if ( Thrive_Utils::is_editor() || is_editor_page() ) { //here we have to render whatever we have because we're in TTB editor
				if ( tva_course()->has_video() ) {
					$content = tva_course()->get_video()->get_embed_code();
				} else { //just render an overlay
					ob_start();
					include Utils::get_integration_path( 'editor-layouts/elements/dynamic-video/course-overview-overlay.php' );
					$content = ob_get_clean();
				}
			} else { //if in frontend, render the video only if it is not added in course's overview post content

				$has_in_content = tva_course()->get_overview_post( true )->has_dynamic_video_in_content();

				if ( ! $has_in_content && tva_course()->has_video() ) {
					$meta  = get_post_meta( $post_id, 'thrive_theme_video_format_meta', true );
					$video = tva_course()->get_video();

					if ( ! empty( $meta['video_options']['url'] ) ) {
						$video->type   = $meta['type'];
						$video->source = $meta['video_options']['url']['value'];
					}

					$content = $video->get_embed_code();
				}
			}
		}

		return $content;
	}

	/**
	 * Change the video post format to custom so that, later in execution,
	 * a new video from TA items can be rendered
	 * - for course overview posts
	 */
	public function video_course_overview_type( $type, $post_id ) {

		if ( get_post_type( $post_id ) === TVA_Course_Overview_Post::POST_TYPE ) {
			$type = Thrive_Video_Post_Format_Main::CUSTOM;
		}

		return $type;
	}

	/**
	 * Include the visual editor elements
	 *
	 * @param array $instances
	 *
	 * @return array
	 */
	public function tcb_element_instances( $instances = array() ) {

		if ( tva_is_course_template() ) {
			$root_path = TVA_Const::plugin_path( 'tcb-bridge/editor-elements/visual-builder/' );

			/* include this before we include the dependencies */
			require_once $root_path . '/class-abstract-visual-builder-element.php';

			$sub_element_path = $root_path . '/sub-elements';

			$instances = array_merge( $instances, Utils::get_tcb_elements( $root_path, $sub_element_path ) );

		}

		return $instances;
	}

	/**
	 * Remove some Theme Elements instances for apprentice visual builder
	 *
	 * @param array $instances
	 *
	 * @return array
	 */
	public function tcb_remove_instances( $instances = array() ) {
		if ( tva_is_course_template() ) {
			unset( $instances['thrive_dynamic_list'] );

			/**
			 * @var $instance TCB_Element_Abstract
			 */
			foreach ( $instances as $instance ) {
				/**
				 * We need to remove all WOO Elements from apprentice visual editor
				 */
				if ( class_exists( '\Thrive\Theme\Integrations\WooCommerce\Helpers', false ) && $instance->category() === Helpers::get_products_category_label() ) {
					unset( $instances[ $instance->tag() ] );
				}
			}
		}

		return $instances;
	}

	/**
	 * Localize data from Visual Builder - Main Frame
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function main_frame_localize( $data = [] ) {
		$apprentice = [];

		if ( Thrive_Utils::is_end_user_site() && ( tva_is_apprentice_template() || Check::typography_page() || tva_get_settings_manager()->is_certificate_validation_page( get_the_ID() ) || in_array( get_post_type(), [
					TVA_Const::LESSON_POST_TYPE,
					TVA_Const::MODULE_POST_TYPE,
					TVA_Course_Overview_Post::POST_TYPE,
					TVA_Course_Completed::POST_TYPE,
					TVA_Const::ASSESSMENT_POST_TYPE,
				] ) ) ) {

			$apprentice['skin_id'] = \TVA\TTB\Main::requested_skin_id();

			if ( ! empty( $apprentice['skin_id'] ) ) {

				if ( tva_palettes()->has_palettes() ) {
					$apprentice['palette_colors'] = tva_palettes()->get_palette();
				}

				$apprentice['palettes'] = \TVA\TTB\Main::requested_skin()->get_palettes();
				$apprentice['routes']   = [
					'palette' => tva_get_route_url( 'palette' ),
					'skins'   => tva_get_route_url( 'skins' ),
				];

				$data['external_palettes'] = 'tva';
			}

			if ( ! Thrive_Theme::is_active() && ! empty( $data['theme'] ) ) {
				/**
				 * If the theme is not active, we need to remove some color localization so that we do not have the color options in the color picker
				 */
				unset( $data['theme']['skin_palettes'], $data['theme']['skin_variables'], $data['theme']['palette_colors'] );
			}
		}

		$data['apprentice'] = $apprentice;

		return $data;
	}

	/**
	 * Get the post_author setup for the course, in order to display the correct author image
	 *
	 * @param $post_author
	 *
	 * @return mixed|string
	 */
	public function author_image( $post_author ) {

		/* also support ajax-loading of cloud content templates, e.g. author boxes */
		$maybe_apprentice = wp_doing_ajax() && ! empty( tva_course()->get_id() );

		if ( $maybe_apprentice || in_array( get_post_type(),
				[
					TVA_Const::MODULE_POST_TYPE,
					TVA_Const::LESSON_POST_TYPE,
					TVA_Course_Overview_Post::POST_TYPE,
					TVA_Course_Certificate::POST_TYPE,
				] ) ) {
			$user = tva_course()->get_author()->get_user();
			if ( ! empty( $user->ID ) ) {

				$post_author = $user->ID;

				$avatar_url = tva_course()->get_author()->get_avatar();
				if ( $avatar_url ) {
					/**
					 * For every `pre_get_avatar_data` concerning the same user id in this request, make sure the correct URL is returned for the users' avatar, if a custom one is set
					 *
					 * @param array      $args
					 * @param string|int $id_or_email
					 *
					 * @return mixed
					 */
					$src_fn = static function ( $args, $id_or_email ) use ( $avatar_url, $user, &$src_fn ) {
						if ( is_scalar( $id_or_email ) && (int) $id_or_email === $user->ID ) {
							$args['url'] = tva_fix_gravatar_url( $avatar_url );
						} else {
							remove_filter( 'pre_get_avatar_data', $src_fn );
						}

						return $args;
					};

					add_filter( 'pre_get_avatar_data', $src_fn, 10, 2 );
				}
			}
		}

		return $post_author;
	}

	/**
	 * Featured Image for apprentice pages
	 *
	 * @param $post_featured
	 *
	 * @return mixed|string
	 */
	public function featured_image( $post_featured ) {
		if ( in_array( get_post_type(), [
			TVA_Const::MODULE_POST_TYPE,
			TVA_Const::LESSON_POST_TYPE,
			TVA_Course_Overview_Post::POST_TYPE,
			TVA_Course_Certificate::POST_TYPE,
		] ) ) {
			$post_featured = tcb_tva_visual_builder()->get_cover_image();
		}

		return $post_featured;
	}

	/**
	 * Adds the course list element and certificate inline shortcodes
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public function inline_shortcodes( $shortcodes = array() ) {

		if ( tva_is_apprentice() && ! empty( tcb_tva_visual_builder()->get_active_course() ) ) {

			$inline_shortcodes = array(
				array(
					'option' => __( 'Title', 'thrive-apprentice' ),
					'value'  => 'tva_content_post_title',
				),
				array(
					'option' => __( 'Summary', 'thrive-apprentice' ),
					'value'  => 'tva_content_post_summary',
				),
				array(
					'option' => __( 'Course difficulty level', 'thrive-apprentice' ),
					'value'  => 'tva_content_difficulty_name',
				),
				array(
					'option' => __( 'Course type', 'thrive-apprentice' ),
					'value'  => 'tva_content_course_type',
				),
				array(
					'option' => __( 'Course progress status', 'thrive-apprentice' ),
					'value'  => 'tva_content_course_progress',
				),
				array(
					'option' => __( 'Course topic', 'thrive-apprentice' ),
					'value'  => 'tva_content_course_topic_title',
				),
				array(
					'option' => __( 'Course label', 'thrive-apprentice' ),
					'value'  => 'tva_content_course_label_title',
				),
				array(
					'option' => __( 'Course author name', 'thrive-apprentice' ),
					'value'  => 'tcb_post_author_name',
				),
				array(
					'option' => __( 'Course author role', 'thrive-apprentice' ),
					'value'  => 'tcb_post_author_role',
				),
				array(
					'option' => __( 'Course author bio', 'thrive-apprentice' ),
					'value'  => 'tcb_post_author_bio',
				),
				array(
					'option' => __( 'Published date', 'thrive-apprentice' ),
					'value'  => 'tcb_post_published_date',
				),
			);

			$post_type = get_post_type();
			if ( in_array( $post_type, array( TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE, TVA_Course_Completed::POST_TYPE, TVA_Const::ASSESSMENT_POST_TYPE ) ) ) {
				$inline_shortcodes = array_merge( $inline_shortcodes, array(
					array(
						'option' => __( 'Course title', 'thrive-apprentice' ),
						'value'  => 'tva_content_course_title',
						'input'  => array(
							'link'   => array(
								'type'  => 'checkbox',
								'label' => __( 'Link to course overview page', 'thrive-apprentice' ),
								'value' => true,
							),
							'target' => array(
								'type'       => 'checkbox',
								'label'      => __( 'Open in new tab', 'thrive-apprentice' ),
								'value'      => false,
								'disable_br' => true,
							),
							'rel'    => array(
								'type'  => 'checkbox',
								'label' => __( 'No follow', 'thrive-apprentice' ),
								'value' => false,
							),
						),
					),
				) );
			}

			$shortcodes = array_merge_recursive( array(
				'Thrive Apprentice' => $inline_shortcodes,
			), $shortcodes );
		}

		return $shortcodes;
	}

	/**
	 * Sets the objects on apprentice load
	 */
	public function set_objects() {
		$is_editor_ajax = is_editor_page_raw( true ) && ( wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) );

		/**
		 * Allow modifying this in some cases ( such as initializing while not in the editor)
		 *
		 * @param bool $is_editor_ajax
		 *
		 * @return bool
		 */
		$is_editor_ajax = apply_filters( 'tva_visual_builder_is_editor_ajax', $is_editor_ajax );

		if ( $is_editor_ajax ) {

			$post_id          = null;
			$active_course_id = null;
			$check            = false;
			if ( ! empty( $_REQUEST['post_id'] ) ) {
				$post_id = (int) $_REQUEST['post_id'];
			} elseif ( ! empty( $_REQUEST['query_vars']['page_id'] ) ) {
				$post_id = (int) $_REQUEST['query_vars']['page_id'];
			} elseif ( ! empty( $_REQUEST['query_vars'] ) ) {
				/* course overview page */
				Thrive_Utils::set_query_vars( $_REQUEST['query_vars'] );
				$active_course = tva_course();
				if ( $active_course->get_id() ) {
					$active_course_id = $active_course->get_id();
					$check            = true;
				}
				$post_id = get_the_ID(); // this gets setup from the set_query_vars() method call
			}

			if ( ! $check ) {
				$check = ! empty( $post_id );
			}
		} else {
			$post_id = get_the_ID();
			$check   = tva_is_apprentice();
		}

		if ( $check ) {
			if ( ! isset( $active_course_id ) && isset( $post_id ) ) {
				$active_course_id = TVA_Course_V2::get_active_course_id( $post_id );
			}

			if ( ! empty( $active_course_id ) ) {
				$course = new TVA_Course_V2( $active_course_id );

				tcb_tva_visual_builder()->set_active_course( $course );
				tcb_tva_dynamic_actions()->set_active_course( $course );
			}
			$post_types = [ TVA_Const::LESSON_POST_TYPE, TVA_Const::MODULE_POST_TYPE ];

			if ( \TVA\TTB\Main::uses_builder_templates() ) {
				$post_types[] = TVA_Const::ASSESSMENT_POST_TYPE;
			}

			if ( in_array( get_post_type( $post_id ), $post_types ) ) {
				$tva_post = TVA_Post::factory( get_post( $post_id ) );

				tcb_tva_visual_builder()->set_active_object( $tva_post );
				tcb_tva_dynamic_actions()->set_active_object( $tva_post );
			}
		}
	}

	/**
	 * Allow the course shortcode to be rendered in the editor
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public function content_allowed_shortcodes_filter( $shortcodes = array() ) {

		if ( is_editor_page() && tva_is_course_template() ) {
			$shortcodes = array_merge(
				$shortcodes,
				tcb_tva_visual_builder()->get_shortcodes()
			);
		}

		return $shortcodes;
	}

	/**
	 * Called from tcb_categories_order filter
	 *
	 * Adds elements_group_label category to order array
	 *
	 * @param array $order
	 *
	 * @return array
	 */
	public function add_category_to_order( $order = [] ) {
		if ( tva_is_course_template() ) {
			$order[2] = tcb_tva_visual_builder()->get_elements_category();
		}

		return $order;
	}

	/**
	 * Prepare Thrive Apprentice node
	 *
	 * @param array $nodes
	 *
	 * @return array
	 */
	public function admin_bar_nodes( $nodes = [] ) {

		if ( current_user_can( 'edit_posts' ) && tva_is_apprentice() && ( ! empty( $_REQUEST['tva_skin_id'] ) || ! empty( \TVA\TTB\Main::get_default_skin_id() ) ) ) {
			$license_expired_class = '';
			if ( ! apply_filters( 'tcb_skip_license_check', false ) ) {
				if ( TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'tva' ) ) {
					$license_expired_class = ' thrive-license-warning';
				} elseif ( ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'tva' ) ) {
					$license_expired_class = ' thrive-license-warning-red';
				}
			}
			$template_id = ! empty( $_REQUEST['tvet'] ) ? (int) $_REQUEST['tvet'] : 0;
			$template    = thrive_apprentice_template( $template_id );
			if ( ! empty( $template->ID ) ) {
				$args = [
					'id'    => 'thrive-builder',
					'meta'  => [ 'class' => 'thrive-apprentice' . $license_expired_class ],
					'title' => __( 'Edit Apprentice Template', 'thrive-apprentice' ) . ' "' . $template->post_title . '"',
					'href'  => add_query_arg( [ 'from_tar' => get_the_ID() ], tcb_get_editor_url( $template->ID ) ),
					'order' => 1,
				];

				/* Add the node to the others */
				$nodes[] = $args;
			}
		}

		return $nodes;
	}

	/**
	 * For School Homepage, reset the types array
	 * It is required for displaying the edit school homepage template when logged on frontend
	 *
	 * @param array $types
	 *
	 * @return array
	 */
	public function theme_builder_content_types( $types = [] ) {

		if ( get_post_type() === 'page' && tva_is_apprentice() && thrive_apprentice_template()->is_school_homepage() ) {
			$types = [];
		}

		return $types;
	}

	/**
	 * Disable EDIT WITH TAR button for school homepage
	 *
	 * @param {boolean} $display
	 *
	 * @return false
	 */
	public function edit_with_tar_display( $display ) {

		$is_tva_apprentice = tva_is_apprentice();

		if ( $is_tva_apprentice && thrive_apprentice_template()->is_school_homepage() ) {
			$display = false;
		}

		if ( $is_tva_apprentice && tva_get_settings_manager()->is_certificate_validation_page() ) {
			$display = false;
		}

		return $display;
	}

	/**
	 * Filter that allows tcb scripts & style when in apprentice context
	 *
	 * @param boolean $should_enqueue
	 *
	 * @return bool
	 */
	public function tva_enqueue_frontend( $should_enqueue ) {

		if ( ! is_editor_page_raw() && tva_is_apprentice() ) {
			$should_enqueue = true;
		}

		return $should_enqueue;
	}


	/**
	 * @param {string} $content
	 * @param {number} $post_id
	 *
	 * @return string
	 */
	public function render_audio_lesson_custom_content( $content, $post_id ) {
		if ( ! empty( tcb_tva_visual_builder()->get_active_object() ) && $post_id === tcb_tva_visual_builder()->get_active_object()->ID ) {

			/**
			 * @var $audio TVA_Audio
			 */
			$audio = tcb_tva_visual_builder()->get_active_object()->get_audio();

			$content = $audio->get_embed_code();
		}

		return $content;
	}

	/**
	 * Override the theme custom video types
	 *
	 * @param {string} $type
	 * @param {number} $post_id
	 *
	 * @return mixed|string
	 */
	public function video_lesson_type( $type, $post_id ) {

		if ( ! empty( tcb_tva_visual_builder()->get_active_object() ) && $post_id === tcb_tva_visual_builder()->get_active_object()->ID ) {
			$type = Thrive_Video_Post_Format_Main::CUSTOM;
		}

		return $type;
	}

	/**
	 * Modify the sidebar redirect icon used for editing content with tar
	 *
	 * @param $icon
	 *
	 * @return string
	 */
	public function sidebar_icon_redirect( $icon ) {

		if ( in_array( get_post_type(), [ TVA_Const::MODULE_POST_TYPE, TVA_Const::LESSON_POST_TYPE, TVA_Course_Overview_Post::POST_TYPE ] ) ) {
			$icon = 'tva';
		}

		return $icon;
	}

	/**
	 * @param {string} $content
	 * @param {number} $post_id
	 * @param {boolean} $has_thumbnail
	 *
	 * @return string
	 */
	public function render_video_lesson_custom_content( $content, $post_id, $has_thumbnail ) {

		if ( ! empty( tcb_tva_visual_builder()->get_active_object() ) && $post_id === tcb_tva_visual_builder()->get_active_object()->ID ) {
			/**
			 * @var $video TVA_Video
			 */
			$video = tcb_tva_visual_builder()->get_active_object()->get_video();

			$content = $video->get_embed_code();

			if ( is_editor_page_raw() || $has_thumbnail ) {
				$content = str_replace( '&autoplay=1&mute=1', '', $content );
			}

			if ( true === $video->progress_enabled ) {
				$data_attrs = ' data-reporting-enabled="1" data-completion-rate="' . $video->progress_percentage . '" data-type="' . $video->type . '"';
				$data_attrs .= 'custom' !== $video->type ? ' data-url="' . $video->source . '"' : '';
				$content    = str_replace( '>', $data_attrs . '>', $content );

				if ( null === Video::get_post_id_by_video_url( $video->source ) ) {
					Video::insert_post( array(
							'url'                    => $video->source,
							'provider'               => $video->type,
							'title'                  => $video->title,
							'duration'               => $video->duration,
							'percentage_to_complete' => $video->progress_percentage,
						)
					);
				}
				$video_id = Video::get_post_id_by_video_url( $video->source );
				update_post_meta( (int) $video_id, 'percentage_to_complete', $video->progress_percentage );
			}
		}

		return $content;
	}

	/**
	 * Values for the current post from the iframe.
	 * Works well also with Apprentice Visual Editor
	 *
	 * It adds these values into TVE.CONST.
	 * Ex: TVE.CONST.active_course_id
	 */
	public function editor_javascript_params( $tve_path_params, $post_id, $post_type ) {
		if ( tva_is_apprentice() ) {
			$course = tcb_tva_visual_builder()->get_active_course();

			if ( ! empty( $course ) ) {
				/**
				 * @var TVA_Topic
				 */
				$topic = tcb_tva_visual_builder()->get_active_course_topic(); //TODO maybe filter only what is needed here
				/**
				 * @var TVA_Author $author
				 */
				$author     = tcb_tva_visual_builder()->get_active_course()->get_author();
				$apprentice = [
					'course'       => [
						'id'        => $course->get_id(),
						'topic'     => $topic, //TODO maybe filter only what is needed here
						'label'     => $course->get_label_data(), //Todo maybe filter only what is needed here
						'has_video' => $course->has_video(),
					],
					'dynamic_data' => [
						'author_image'       => tcb_tva_visual_builder()->get_author_image(),
						'author_name'        => get_the_author_meta( 'display_name', tcb_tva_visual_builder()->get_active_course_author_id() ),
						'author_role'        => ! empty( $author->get_user()->roles ) ? $author->get_user()->roles[0] : esc_html__( 'No Author Role', 'thrive-apprentice' ),
						'author_bio'         => strip_tags( $author->get_bio() ),
						'featured_image'     => tcb_tva_visual_builder()->get_cover_image(),
						'post_title'         => tcb_tva_visual_builder()->get_title(),
						'course_title'       => $course->name,
						'post_summary'       => tcb_tva_visual_builder()->get_summary(),
						'difficulty_name'    => tcb_tva_visual_builder()->get_difficulty_name(),
						'course_type'        => tcb_tva_visual_builder()->get_course_type(),
						'course_type_icon'   => tcb_tva_visual_builder()->get_course_type_icon(),
						'course_progress'    => tcb_tva_visual_builder()->get_course_progress(),
						'course_topic_title' => esc_attr( $topic->title ),
						'course_topic_icon'  => tcb_tva_visual_builder()->get_course_topic_icon(),
						'course_label_title' => tcb_tva_visual_builder()->get_course_label()['title'],
					],
				];

				if ( $course->has_video() ) {
					$apprentice['course']['video']       = $course->get_video();
					$apprentice['course']['video_embed'] = $course->get_video()->get_embed_code();
				}

				$tve_path_params['apprentice'] = $apprentice;
			}
		}

		return $tve_path_params;
	}

	/**
	 * Adds the Visual Builder Category to TAR Post Content Element
	 *
	 * @param string $category
	 *
	 * @return string
	 */
	public function post_content_element_category( $category ) {
		if ( tva_is_course_template() && ! thrive_apprentice_template()->is_school_homepage() ) {
			$category = tcb_tva_visual_builder()->get_elements_category();
		}

		return $category;
	}

	/**
	 * Post Content title override for Apprentice Visual Builder
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function post_content_element_title( $title ) {
		if ( tva_is_course_template() ) {
			$title = __( 'Content', 'thrive-apprentice' );
		}

		return $title;
	}


	/**
	 * Used to modify the author ID for author shortcodes
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public function get_course_content_author( $user_id ) {
		if ( ! empty( tcb_tva_visual_builder()->get_active_course() ) ) {
			return tcb_tva_visual_builder()->get_active_course_author_id();
		}

		return $user_id;
	}

	/**
	 * Changes the TAR icon from top right sidebar
	 *
	 * @param string $string
	 * @param string $type
	 *
	 * @return string
	 */
	public function architect_branding( $string, $type = 'text' ) {
		if ( ! empty( $_REQUEST['tva_skin_id'] ) ) {
			switch ( $type ) {
				case 'text':
					$string = 'Thrive Apprentice Builder';
					break;
				case 'logo_src':
					$string = TVA_Const::plugin_url( 'tcb-bridge/assets/images/tva-builder-logo.png' );
					break;
				default:
					break;
			}
		}

		return $string;
	}

	/**
	 * Changes the TTB icon from top left sidebar above the components
	 *
	 * @param string $icon
	 *
	 * @return string
	 */
	public function ttb_branding( $icon ) {
		if ( tva_is_apprentice_template() ) {
			$icon = tcb_icon( 'tva-strong', true, 'sidebar', '' );
		}

		return $icon;
	}

	/**
	 * For theme elements, load the elements for the requested Apprentice Skin
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function cloud_request_params( $params = [] ) {
		if ( ! empty( $params['theme_element'] ) && ! empty( $_REQUEST['tva_skin_id'] ) ) {
			$params['ttb_skin'] = \TVA\TTB\Main::requested_skin()->get_tag();
		}

		return $params;
	}

	/**
	 * Exclude the following post types from the TAR search and post list UI
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public function ignored_post_types( $post_types = [] ) {

		$post_types[] = TVA_Course_Overview_Post::POST_TYPE;
		$post_types[] = TVA_Const::CHAPTER_POST_TYPE;
		$post_types[] = TVA_Access_Restriction::POST_TYPE;
		$post_types[] = TVA_Course_Certificate::POST_TYPE;
		$post_types[] = TVA_Course_Certificate::USER_TEMPLATE_POST_TYPE;
		$post_types[] = TVA_Course_Completed::POST_TYPE;

		return $post_types;
	}

	/**
	 * Post format compatibility for lessons
	 *
	 * @param string $format
	 * @param int    $post_id
	 *
	 * @return string
	 */
	public function visibility_config_post_format( $format, $post_id ) {
		if ( is_editor_page_raw() && get_post_type( $post_id ) === TVA_Const::LESSON_POST_TYPE ) {

			$lesson = new TVA_Lesson( $post_id );

			$format = $lesson->get_type();
		}

		return $format;
	}

	/**
	 * When editing apprentice content, disable some toggles form Visibility control
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function visibility_config( $config = [] ) {
		if ( Thrive_Utils::is_end_user_site() && in_array( get_post_type(), [
				TVA_Const::LESSON_POST_TYPE,
				TVA_Const::MODULE_POST_TYPE,
				TVA_Course_Overview_Post::POST_TYPE,
				TVA_Course_Completed::POST_TYPE,
				TVA_Const::ASSESSMENT_POST_TYPE,
			] ) ) {

			unset( $config['elements']['post_title'] );

			if ( get_post_type() === TVA_Course_Completed::POST_TYPE || get_post_type() === TVA_Const::ASSESSMENT_POST_TYPE ) {
				unset( $config['elements']['featured_image'], $config['elements']['author_box'] );
			}
		}

		return $config;
	}

	/**
	 * Landing page viewing/editing functionality compatibility with access restriction
	 *
	 * @param boolean $allow
	 *
	 * @return boolean
	 */
	public function allow_landing_page_edit( $allow ) {

		if ( tva_general_post_is_apprentice() ) {
			$allow = false;
		}

		return $allow;
	}
}
