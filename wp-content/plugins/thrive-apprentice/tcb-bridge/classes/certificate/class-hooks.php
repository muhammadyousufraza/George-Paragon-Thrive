<?php

namespace TVA\Architect\Certificate;

use TVA\Architect\Utils;
use TVA_Const;
use function TVA\Architect\Visual_Builder\tcb_tva_visual_builder;

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Hooks {

	public static function init() {

		add_filter( 'tcb_can_use_landing_pages', [ __CLASS__, 'can_use_landing_pages' ] );

		add_filter( 'tcb_has_templates_tab', [ __CLASS__, 'has_templates_tab' ] );

		add_filter( 'tcb_modal_templates', [ __CLASS__, 'modal_templates' ] );

		add_filter( 'tcb_lazy_load_data', [ __CLASS__, 'localize_data' ], 10, 3 );

		add_filter( 'tcb_inline_shortcodes', [ __CLASS__, 'inline_shortcodes' ] );

		add_filter( 'tcb_elements', [ __CLASS__, 'filter_elements' ], 11 );

		add_filter( 'tcb_custom_post_layouts', [ __CLASS__, 'tcb_editor_layout' ], 10, 3 );

		add_filter( 'tcb_post_types', [ __CLASS__, 'allow_edit_post_type' ], 10, 3 );

		add_filter( 'tcb_sidebar_icon_availability', [ __CLASS__, 'sidebar_icon_availability' ], 10, 3 );

		add_filter( 'tcb_has_save_template_button', [ __CLASS__, 'has_save_template_button' ] );

		add_filter( 'tcb_menu_path_certificate', [ __CLASS__, 'tva_include_certificate_menu' ] );

		add_filter( 'tcb_menu_path_certificate_qr_code', [ __CLASS__, 'tva_include_certificate_qr_code_menu' ] );

		add_action( 'tcb_element_instances', [ __CLASS__, 'add_tcb_elements' ] );

		add_action( 'tcb_ajax_save_post', [ __CLASS__, 'save_post_ajax' ], 10, 2 );

		add_filter( 'tcb_post_visibility_options_availability', [ __CLASS__, 'blacklist_post_visibility_options' ] );

		add_filter( 'tva_enqueue_frontend', [ __CLASS__, 'enqueue_frontend' ] );

		add_filter( 'tve_get_current_user_id', [ __CLASS__, 'current_user_id' ] );

		add_filter( 'tva_get_frontend_localization', [ __CLASS__, 'get_frontend_localization' ] );

		add_filter( 'tcb_main_frame_localize', [ __CLASS__, 'main_frame_localize' ], 11 );

		add_action( 'before_delete_post', [ __CLASS__, 'remove_generated_certificate_data' ], 10, 2 );

		add_action( 'tcb_get_extra_global_variables', [ __CLASS__, 'output_extra_global_variables' ] );

		add_action( 'tcb_custom_fields_render_tva_verification_qr', [ __CLASS__, 'qr_code_shortcode_render' ], 10, 2 );

		/**
		 * Priority needs to be 0 because of conflicts with other redirects.
		 * Ex Fix conflict with Thrive Theme template redirect with prio 8
		 */
		add_action( 'template_redirect', [ __CLASS__, 'certificate_verification_page_redirect' ], 0, 0 );

		add_action( 'template_redirect', [ __CLASS__, 'certificate_download_redirect' ], 10, 0 );

		add_filter( 'tcb_dynamiclink_data', [ __CLASS__, 'dynamiclink_data' ], 100 );

		add_filter( 'tcb_main_frame_localize', [ __CLASS__, 'append_tve_const_data' ] );

		add_filter( 'tve_frontend_options_data', [ __CLASS__, 'tve_frontend_data' ] );

		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );

		add_action( 'tva_settings_saved_certificate_verification', array( __CLASS__, 'on_setting_change' ) );

		add_action( 'edited_' . TVA_Const::COURSE_TAXONOMY, [ __CLASS__, 'after_edit_course' ], 10, 2 );

		add_action( 'profile_update', [ __CLASS__, 'after_profile_update' ] );

		/**
		 * @param \TCB_Editor_Ajax $editor_ajax
		 */
		add_action( 'tcb_ajax_before_cloud_content_template_download_without_element', static function ( $editor_ajax ) {
			if ( ! empty( $_POST['post_id'] ) && ! empty( $_POST['type'] ) && $_POST['type'] === 'certificate' ) {
				global $post;

				if ( empty( $post ) ) {
					$post = get_post( (int) $_POST['post_id'] );
				}
			}
		} );

		add_filter(
			'tcb_menu_path_certificate_verification',
			static function () {
				return Utils::get_integration_path( 'editor-layouts/menus/certificate/component.php' );
			}
		);
		add_filter(
			'tcb_menu_path_certificate_form_input',
			static function () {
				return Utils::get_integration_path( 'editor-layouts/menus/certificate/form_input_component.php' );
			}
		);

		/**
		 * Hooks into save template functionality.
		 * Modify the post type when a certificate template is saved from the certificate editor
		 *
		 * @param string $post_type
		 */
		add_filter( 'tcb_user_templates_get_post_type_name', static function ( $post_type ) {
			$post_id = get_the_ID();

			if ( empty( $post_id ) && ! empty( $_REQUEST['post_id'] ) ) {
				$post_id = (int) $_REQUEST['post_id'];
			}

			if ( get_post_type( $post_id ) === \TVA_Course_Certificate::POST_TYPE ) {
				$post_type = \TVA_Course_Certificate::USER_TEMPLATE_POST_TYPE;
			}

			return $post_type;
		} );

		/**
		 * Applied this filter to display the only button for verification page
		 * - Edit Template with TTB
		 */
		add_filter( 'tve_dash_admin_bar_nodes', static function ( $nodes ) {
			if ( \TVA_Product::has_access() && tva_get_settings_manager()->is_certificate_validation_page() ) {
				$template = \TVA\TTB\thrive_apprentice_template();
				$args     = [
					'id'    => 'thrive-builder',
					'meta'  => [ 'class' => 'thrive-apprentice' ],
					'title' => __( 'Edit Apprentice Template', 'thrive-apprentice' ) . ' "' . $template->post_title . '"',
					'href'  => add_query_arg( [ 'from_tar' => get_the_ID() ], tcb_get_editor_url( $template->ID ) ),
					'order' => 1,
				];

				$nodes = [ $args ];//overwrite the nodes with the only button
			}

			return $nodes;
		}, 12, 1 );
	}

	/**
	 * When the toggle in frontend is switched on/off
	 * - check if the pages is already set
	 * - inserts a new page if it is not set
	 * - drafts the page when the switch is turned off
	 *
	 * @param int $value
	 *
	 * @return void
	 */
	public static function on_setting_change( $value ) {

		$page_id = tva_get_settings_manager()->get_setting( 'certificate_validation_page' );

		//Certificate Validation is turned on
		if ( $value ) {
			if ( $page_id ) {
				wp_update_post( array(
					'ID'          => $page_id,
					'post_status' => 'publish',
				) );
			} else {
				$data    = array(
					'post_title'  => 'Certificate Verification',
					'post_status' => 'publish',
					'post_type'   => 'page',
				);
				$page_id = wp_insert_post( $data );
				tva_get_settings_manager()->factory( 'certificate_validation_page' )->set_value( $page_id );
			}
		} else {
			//Certificate Validation is turned off
			wp_update_post( array(
				'ID'          => $page_id,
				'post_status' => 'draft',
			) );
		}
	}

	/**
	 * Triggered after a course was edited
	 *
	 * Example: change course name, change some course meta
	 *
	 * Clears all the certificates that was generated for the course.
	 * This ensures the course name and course meta on the certificate is up to date
	 *
	 * @return void
	 */
	public static function after_edit_course( $course_id, $taxonomy_id ) {
		$course = new \TVA_Course_V2( (int) $course_id );

		if ( $course->has_certificate() ) {
			Main::remove_generated_certificates( $course->get_certificate()->ID );
		}
	}

	/**
	 * Fires immediately after an existing user is updated.
	 *
	 * Removes the previous stored certificates assigned to user that profile was updated
	 * Reason: some user shortcodes can be invalid after profile updated
	 *
	 * @param int $user_id User ID.
	 */
	public static function after_profile_update( $user_id ) {
		$all_meta = get_user_meta( $user_id );

		$only_certificate_meta = array_filter( $all_meta, function ( $key ) {
			return strpos( $key, 'tva_certificate_' ) === 0;
		}, ARRAY_FILTER_USE_KEY );

		foreach ( array_keys( $only_certificate_meta ) as $meta_key ) {
			$certificate_id = (int) str_replace( 'tva_certificate_', '', $meta_key );

			if ( is_numeric( $certificate_id ) ) {

				\TVD_PDF_From_URL::delete_by_prefix( \TVA_Course_Certificate::FILE_NAME_PREFIX . '-' . $certificate_id . '-' . $only_certificate_meta[ $meta_key ][0] );
				delete_user_meta( $user_id, $meta_key );
			}
		}
	}

	public static function enqueue_scripts() {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			tve_dash_enqueue_script( 'tva-qr-code', TVE_DASH_URL . '/js/dist/qrious.min.js' );
			tva_enqueue_script( 'tva-certificates-qr-code', TVA_Const::plugin_url( 'js/dist/certificates-qr-code.min.js' ), array( 'tva-qr-code' ), false, false );
		}
	}

	/**
	 * Add some data to the frontend localized object
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public static function tve_frontend_data( $data ) {

		$data['routes']['certificate_search'] = get_rest_url( get_current_blog_id(), \TVA_Const::REST_NAMESPACE . '/certificate/search' );

		$data['query_vars']['certificate_u'] = ! empty( $_GET['u'] ) ? sanitize_text_field( $_GET['u'] ) : '';

		return $data;
	}

	/**
	 * Append data to TVE.CONST in order to be used in TAr
	 * - on certificate validation page we should NOT allow users to delete the element
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function append_tve_const_data( $data ) {

		$data['is_certificate_validation_page'] = tva_get_settings_manager()->is_certificate_validation_page( get_the_ID() );
		if ( Main::is_lp_build() ) {
			$data['tva_allow_more'] = true;
		}

		return $data;
	}

	public static function dynamiclink_data( $data ) {

		if ( tva_get_settings_manager()->is_certificate_validation_page( get_the_ID() ) || Main::is_lp_build() ) {
			$data['Apprentice certificate verification'] = array(
				'links'     => array(
					0 => array(
						'bk_to_from'           => array(
							'name'  => 'Back to form',
							'label' => 'Back to form',
							'url'   => '',
							'show'  => 1,
							'id'    => 'form',
						),
						'download_certificate' => array(
							'name'  => 'Download certificate',
							'label' => 'Download certificate',
							'url'   => '',
							'show'  => 1,
							'id'    => 'download_certificate',
						),
					),
				),
				'shortcode' => 'tva_certificate_inline_link',
			);
		}

		return $data;
	}

	/**
	 * Redirect to verification page functionality
	 *
	 * @return void
	 */
	public static function certificate_verification_page_redirect() {
		if ( ! empty( $_GET[ \TVA_Course_Certificate::VERIFICATION_PAGE_QUERY_NAME ] ) && $_GET[ \TVA_Course_Certificate::VERIFICATION_PAGE_QUERY_NAME ] === \TVA_Course_Certificate::VERIFICATION_PAGE_QUERY_VAL && ! empty( $_GET['u'] ) ) {

			$verification_page = tva_get_settings_manager()->factory( 'certificate_validation_page' )->get_link();
			if ( empty( $verification_page ) ) {
				$verification_page = home_url();
			} else {
				$verification_page = add_query_arg( [
					'u' => (string) $_GET['u'],
				], $verification_page );
			}

			wp_redirect( $verification_page );
			exit();
		}
	}

	/**
	 * Certificate download via a direct link
	 *
	 * @return void
	 */
	public static function certificate_download_redirect() {

		if ( ! empty( $_GET[ \TVA_Course_Certificate::DOWNLOAD_URL_QUERY_NAME ] ) && $_GET[ \TVA_Course_Certificate::DOWNLOAD_URL_QUERY_NAME ] === \TVA_Course_Certificate::DOWNLOAD_URL_QUERY_VAL ) {
			$no_access_link = add_query_arg( [ 'tva-show-message' => 'download_certificate_notice' ], get_term_link( tva_course()->get_id() ) );

			if ( ! is_user_logged_in() || empty( tva_course()->get_id() ) || ! tva_course()->has_certificate() || ( ! tva_customer()->has_completed_course( tva_course() ) && ! \TVA_Product::has_access() ) ) {
				/**
				 * We redirect the user to a no access page if the following conditions are met
				 * user is not logged in
				 * OR
				 * course is not localized
				 * OR
				 * course has no certificate
				 * OR
				 * course is not completed AND user has no access (so it is not an admin user)
				 */
				wp_redirect( $no_access_link );

				return;
			}

			$response = tva_course()->get_certificate()->download( tva_customer() );

			if ( ! empty( $response['error'] ) ) {
				wp_redirect( $no_access_link );

				return;
			}

			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Disposition: attachment; filename="' . sanitize_title( tva_course()->get_certificate()->post_title ) . '.pdf"' );
			header( 'Content-Length: ' . filesize( $response['file'] ) );
			if ( ob_get_contents() ) {
				ob_end_clean();
			}

			readfile( $response['file'] );
			exit();
		}
	}

	/**
	 * Callback for the QR Code verification URL
	 *
	 * @param string $return
	 * @param array  $args
	 *
	 * @return string
	 */
	public static function qr_code_shortcode_render( $return = '', $args = [] ) {
		$setting = tva_get_setting( 'certificate_verification' );

		if ( ! is_editor_page_raw() && ( ( empty( $setting ) && (int) $setting === 0 ) || empty( tva_get_settings_manager()->factory( 'certificate_validation_page' )->get_link() ) ) ) {
			return '';
		}
		$qr_source = do_shortcode( '[tva_qr_source type=verification]' );

		return '<div class="thrv_wrapper tva-certificate-qr-code tcb-dynamic-field-source" style="width:60px" data-qr-dynamic="' . $qr_source . '">
				<img class="tve-qr-code" width="60" data-d-f="verification" />
				</div>';
	}

	public static function has_templates_tab( $has_templates_tab = true ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$has_templates_tab = true;
		}

		return $has_templates_tab;
	}

	public static function can_use_landing_pages( $is_allowed = true ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$is_allowed = false;
		}

		return $is_allowed;
	}

	/**
	 * Include modal files
	 *
	 * @param array $files
	 *
	 * @return array
	 */
	public static function modal_templates( $files = [] ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$files[] = \TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/modals/templates-certificates.php' );
		}

		return $files;
	}

	/**
	 * Localize data and injects Certificate cloud templates
	 *
	 * @param array            $data
	 * @param integer          $post_id
	 * @param \TCB_Editor_Ajax $editor_ajax
	 *
	 * @return array
	 */
	public static function localize_data( $data, $post_id, $editor_ajax ) {
		if ( get_post_type( $post_id ) === \TVA_Course_Certificate::POST_TYPE ) {
			$element = new Certificate_Element();
			$course  = Main::get_certificate_course( $post_id );

			$author_image = $course->get_author()->get_avatar();

			$data['certificate'] = [
				'dynamic_data'   => [
					'title'          => $course->name,
					'featured_image' => $course->cover_image,
					'author_image'   => empty( $author_image ) ? \TCB_Post_List_Author_Image::get_default_url() : $author_image,
				],
				'templates'      => $element->get_cloud_templates(),
				'course_name'    => $course->name,
				'course_summary' => $course->excerpt,
				'course_author'  => $course->get_author()->get_user()->display_name,
				'number'         => empty( $course->get_certificate()->number ) ? $course->get_certificate()->generate_code( get_current_user_id() ) : $course->get_certificate()->number,
				'recipient'      => tva_customer()->get_user()->display_name,
			];

		}

		return $data;
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	public static function main_frame_localize( $data = [] ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$data['tva_open_modal_if_empty'] = (int) \TVA\TTB\Check::is_end_user_site();
			$data['tva_certificate_qr_url']  = TVA_Const::plugin_url( '/admin/img/thrive-themes-qr.png' );
		}

		return $data;
	}

	/**
	 * Filter that allows tcb scripts & style when in apprentice context
	 *
	 * @param boolean $should_enqueue
	 *
	 * @return bool
	 */
	public static function enqueue_frontend( $should_enqueue ) {

		if ( ! is_editor_page_raw() && get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$should_enqueue = true;
		}

		return $should_enqueue;
	}

	/**
	 * @param int $current_user_id
	 *
	 * @return int
	 */
	public static function current_user_id( $current_user_id ) {
		if ( ! empty( $_GET['tva_certificate_user_id'] ) ) {
			$current_user_id = (int) $_GET['tva_certificate_user_id'];
		}

		return $current_user_id;
	}

	/**
	 * Hides the main post option from breadcrumbs for certificate editor
	 *
	 * @param array $post_types
	 *
	 * @return array
	 */
	public static function blacklist_post_visibility_options( $post_types ) {
		$post_type = get_post_type( get_the_ID() );

		if ( $post_type === \TVA_Course_Certificate::POST_TYPE ) {
			$post_types[] = $post_type;
		}

		return $post_types;
	}

	/**
	 * @return array
	 */
	public static function inline_shortcodes( $shortcodes ) {

		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$inline_shortcodes = array(
				'Apprentice Course'      => array(
					array(
						'option' => __( 'Course name', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_course_name',
					),
					array(
						'option' => __( 'Course summary', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_course_summary',
					),
					array(
						'option' => __( 'Course author name', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_course_author',
					),
				),
				'Apprentice Certificate' => array(
					array(
						'option' => __( 'Certificate title', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_title',
					),
					array(
						'option' => __( 'Certificate number', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_number',
					),
					array(
						'option' => __( 'Certificate recipient', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_recipient',
					),
					array(
						'option' => 'Certificate date (14 Aug 2022)',
						'value'  => 'tva_certificate_date1',
					),
					array(
						'option' => 'Certificate date (14/8/2022)',
						'value'  => 'tva_certificate_date2',
					),
					array(
						'option' => 'Certificate date (8/14/2022)',
						'value'  => 'tva_certificate_date3',
					),
				),
			);
			$shortcodes        = array_merge_recursive( $inline_shortcodes, $shortcodes );
		}

		if ( tva_get_settings_manager()->is_certificate_validation_page( get_the_ID() ) ) {
			$inline_shortcodes = array(
				'Apprentice Certificate Verification' => array(
					array(
						'option' => __( 'Certificate number', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_number',
					),
					array(
						'option' => __( 'Certificate recipient', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_recipient',
					),
					array(
						'option' => __( 'Certificate course name', 'thrive-apprentice' ),
						'value'  => 'tva_certificate_course_name',
					),
					array(
						'option' => 'Certificate date (14 Aug 2022)',
						'value'  => 'tva_certificate_date1',
					),
					array(
						'option' => 'Certificate date (14/8/2022)',
						'value'  => 'tva_certificate_date2',
					),
					array(
						'option' => 'Certificate date (8/14/2022)',
						'value'  => 'tva_certificate_date3',
					),
				),
			);

			$shortcodes = array_merge_recursive( $inline_shortcodes, $shortcodes );
		}

		return $shortcodes;
	}

	/**
	 * Elements for Certificate editor
	 *
	 * @param array $elements_instances
	 *
	 * @return array
	 */
	public static function filter_elements( $elements_instances = [] ) {

		if ( get_post_type() !== \TVA_Course_Certificate::POST_TYPE ) {
			return $elements_instances;
		}

		$allowed_tags = [
			'text',
			'image',
			'columns',
			'column',
			'contentbox',
			'logo',
			'divider',
			'fillcounter',
			'icon',
			'styledlist',
			'table',
			'numberedlist',
			'stylebox',
			'certificate',
			'certificate_qr_code',
		];

		/**
		 * @var $element_instance
		 */
		foreach ( $elements_instances as $key => $element_instance ) {
			if ( '.tve-post-options-element' === $element_instance->identifier() ) {
				continue;
			}

			if ( ! in_array( $element_instance->tag(), $allowed_tags, true ) ) {
				unset( $elements_instances[ $key ] );
			}
		}

		return $elements_instances;
	}

	/**
	 * Modifies the TCB Layout for the certificate post type
	 *
	 * @param array  $layouts
	 * @param int    $post_id
	 * @param string $post_type
	 *
	 * @return mixed
	 */
	public static function tcb_editor_layout( $layouts, $post_id, $post_type ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {

			$file_path = TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/layout-certificate.php' );

			if ( ! is_file( $file_path ) ) {
				return $layouts;
			}

			$layouts['symbol_template'] = $file_path;
		}

		return $layouts;
	}

	/**
	 * Force whitelist the post type so it can be edited with TAR
	 *
	 * @param $post_types
	 *
	 * @return array
	 */
	public static function allow_edit_post_type( $post_types = [] ) {
		if ( isset( $post_types['force_whitelist'] ) ) {
			$post_types['force_whitelist'][] = \TVA_Course_Certificate::POST_TYPE;
		}

		return $post_types;
	}

	/**
	 * @param string $display
	 * @param string $icon
	 *
	 * @return string
	 */
	public static function sidebar_icon_availability( $display, $icon, $tcb_editor ) {

		if ( $icon === 'ab-test' && get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$display = 'hide';
		}

		return $display;
	}

	/**
	 * @param boolean $allow
	 *
	 * @return boolean
	 */
	public static function has_save_template_button( $allow = true ) {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$allow = true;
		}

		return $allow;
	}

	/**
	 * @param $file
	 *
	 * @return string
	 */
	public static function tva_include_certificate_menu( $file ) {

		return Utils::get_integration_path( 'editor-layouts/menus/certificate.php' );
	}

	/**
	 * @param $file
	 *
	 * @return string
	 */
	public static function tva_include_certificate_qr_code_menu() {

		return Utils::get_integration_path( 'editor-layouts/menus/certificate_qr_code.php' );
	}

	/**
	 * @param array $element_instances
	 *
	 * @return array
	 */
	public static function add_tcb_elements( $element_instances = [] ) {

		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {

			$element = new Certificate_Element();
			$qr      = new Certificate_Qr_Code_Element();

			$element_instances[ $element->tag() ] = $element;
			$element_instances[ $qr->tag() ]      = $qr;
		}

		return $element_instances;
	}

	/**
	 * @param integer $post_id
	 * @param array   $post_data
	 *
	 * @return void Saves certificate dimensions & post title on save post request
	 */
	public static function save_post_ajax( $post_id, $post_data ) {
		if ( get_post_type( $post_id ) === \TVA_Course_Certificate::POST_TYPE ) {
			if ( ! empty( $post_data['tva_certificate_dimensions'] ) && is_string( $post_data['tva_certificate_dimensions'] ) ) {
				update_post_meta( $post_id, 'tva_certificate_dimensions', sanitize_title( $post_data['tva_certificate_dimensions'] ) );
			}

			if ( ! empty( $post_data['tva_certificate_title'] ) && is_string( $post_data['tva_certificate_title'] ) ) {
				wp_update_post( [
					'ID'                => $post_id,
					'post_status'       => 'publish',
					'post_name'         => wp_unique_post_slug( sanitize_text_field( $post_data['tva_certificate_title'] ), $post_id, 'publish', \TVA_Course_Certificate::POST_TYPE, 0 ),
					'post_modified'     => current_time( 'mysql' ),
					'post_modified_gmt' => current_time( 'mysql' ),
					'post_title'        => sanitize_text_field( $post_data['tva_certificate_title'] ),
				] );
			}

			/**
			 * On save post removes all PDFs generated for that particular post
			 */
			Main::remove_generated_certificates( $post_id );
		}
	}

	/**
	 * Fires before a post is deleted, at the start of wp_delete_post().
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 *
	 * @see wp_delete_post()
	 */
	public static function remove_generated_certificate_data( $post_id, $post ) {
		if ( $post->post_type === \TVA_Course_Certificate::POST_TYPE ) {
			Main::remove_generated_certificates( $post_id );
		}
	}

	/**
	 * Outputs dynamic variables for the dynamic controls
	 * course cover image, course author image and user image
	 *
	 * @return void
	 */
	public static function output_extra_global_variables() {
		if ( get_post_type() === \TVA_Course_Certificate::POST_TYPE ) {
			$active_course_id = \TVA_Course_V2::get_active_course_id();

			if ( ! empty( $active_course_id ) ) {
				tcb_tva_visual_builder()->set_active_course( new \TVA_Course_V2( (int) $active_course_id ) );

				/**
				 * User image instance.
				 * Needed for computing the current user image
				 */
				$user_image = new \TCB_Post_List_User_Image( tve_get_current_user_id() );

				echo TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX . 'certificate-image-featured:url("' . tcb_tva_visual_builder()->get_cover_image() . '");';
				echo TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX . 'certificate-image-author:url("' . tcb_tva_visual_builder()->get_author_image() . '");';
				echo TVE_DYNAMIC_BACKGROUND_URL_VAR_CSS_PREFIX . 'certificate-image-user:url("' . $user_image->user_avatar() . '");';
			}
		}
	}

	/**
	 * Certificate frontend localization
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public static function get_frontend_localization( $data = [] ) {
		if ( get_current_user_id() ) {
			$course = tcb_tva_visual_builder()->get_active_course();

			if ( $course instanceof \TVA_Course_V2 && $course->has_certificate() ) {
				$certificate         = $course->get_certificate();
				$data['certificate'] = [
					'title'          => sanitize_title( $certificate->post_title ),
					'allow_download' => \TVA_Product::has_access() || (int) ( tva_customer()->has_completed_course( $course ) ),
				];
			}
		}

		return $data;
	}
}
