<?php

/**
 * Class TVA_Settings_Controller
 *
 * @deprecated
 */
class TVA_Settings_Controller extends TVA_REST_Controller {

	/**
	 * Controller base
	 *
	 * @var string
	 */
	public $base = 'settings';

	/**
	 * Register the routes
	 */
	public function register_routes() {

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/save_settings/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'save_settings' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/refresh_sendowl_products/', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'refresh_sendowl_products' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/refresh_sendowl_bundles/', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'refresh_sendowl_bundles' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route(
			self::$namespace . self::$version,
			'/' . $this->base . '/refresh_sendowl_memberships/',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'refresh_sendowl_memberships' ),
					'permission_callback' => array( $this, 'settings_permissions_check' ),
					'args'                => array(),
				),
			)
		);

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/refresh_sendowl_discounts/', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'refresh_sendowl_discounts' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/search_pages/', array(
			array(
				'methods'             => WP_REST_Server::ALLMETHODS,
				'callback'            => array( $this, 'search_pages' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/get_user_settings/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'get_user_settings' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/get_preview_url/', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_preview_url' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/set_advanced_user_settings/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'set_advanced_user_settings' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/get_old_courses_lessons/', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_old_courses_lessons' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/set_import/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'set_import' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/disable_apprentice/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'disable_apprentice' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/disable_apprentice_ribbon/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'disable_apprentice_ribbon' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/create_new_page/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_new_page' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/update_provisional_index_page/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_provisional_index_page' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/switch_preview/', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'switch_preview' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( self::$namespace . self::$version, '/' . $this->base . '/get_available_settings/', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_available_settings' ),
				'permission_callback' => array( $this, 'settings_permissions_check' ),
				'args'                => array(),
			),
		) );

	}

	/**
	 * Force refresh for products
	 *
	 * @return WP_REST_Response
	 */
	public function refresh_sendowl_products() {
		TVA_SendOwl::get_memberships( true );

		$products = TVA_SendOwl::get_products();

		return new WP_REST_Response( $products, 200 );
	}

	/**
	 * Force refresh for bundles
	 *
	 * @return WP_REST_Response
	 */
	public function refresh_sendowl_bundles() {
		TVA_SendOwl::get_memberships( true );

		$products = TVA_SendOwl::get_bundles();

		return new WP_REST_Response( $products, 200 );
	}

	/**
	 * Force refresh for bundles
	 *
	 * @return WP_REST_Response
	 */
	public function refresh_sendowl_memberships() {
		$memberships = TVA_SendOwl::get_memberships( true );

		return new WP_REST_Response( $memberships, 200 );
	}

	/**
	 * Force refresh for discounts
	 *
	 * @return WP_REST_Response
	 */
	public function refresh_sendowl_discounts() {
		$discounts = TVA_SendOwl::get_discounts( true );

		return new WP_REST_Response( $discounts, 200 );
	}

	/**
	 * Create new page for apprentice index page
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_new_page( $request ) {
		$data    = array(
			'post_type'    => 'page',
			'post_title'   => $request->get_param( 'page_name' ),
			'post_content' => '',
			'post_status'  => 'publish',
		);
		$page_id = wp_insert_post( $data );
		if ( $page_id ) {
			update_option( 'tva_provisional_index_page', array( 'name' => $request->get_param( 'page_name' ), 'ID' => $page_id ) );

			return new WP_REST_Response(
				array(
					'ID'             => $page_id,
					'apprentice_url' => get_permalink( $page_id ),
					'preview_url'    => tva_get_preview_url(),
				), 200 );
		}

		return new WP_Error( 'no-results', __( 'Page cannot be created please try again.', 'thrive-apprentice' ) );
	}

	/**
	 * Set the index page for the iframe when the settings are not saved
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_provisional_index_page( $request ) {
		$index_page = $request->get_param( 'index_page' );
		update_option( 'tva_provisional_index_page', $index_page );

		$response['apprentice_url'] = get_permalink( $index_page['ID'] );
		$response['preview_url']    = tva_get_preview_url();

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function save_settings( $request ) {
		$template = $request->get_param( 'template' );

		$model = array(
			'per_page'         => $request->get_param( 'per_page' ),
			'index_page'       => $request->get_param( 'index_page' ),
			'load_scripts'     => $request->get_param( 'load_scripts' ),
			'auto_login'       => $request->get_param( 'auto_login' ),
			'apprentice_label' => $request->get_param( 'apprentice_label' ),
			'register_page'    => $request->get_param( 'register_page' ),
			'comment_status'   => $request->get_param( 'comment_status' ),
			'template'         => $template,
		);

		$result                        = update_option( 'tva_template_general_settings', $model );
		$advanced_settings             = get_option( 'tva_template_advanced_settings', array() );
		$advanced_settings['template'] = $template;

		update_option( 'tva_load_all_scripts', $request->get_param( 'load_scripts' ) );
		update_option( 'tva_wizard_completed', true );
		update_option( 'tva_template_advanced_settings', $advanced_settings );
		delete_option( 'tva_provisional_index_page' );

		/*
		 * We update comment status for courses only if has been changed
		 */
		if ( $request->get_param( 'is_comment_status_changed' ) === true ) {
			TVA_Courses_Controller::tva_update_courses_comment_status( $model['comment_status'] );
		}

		if ( ! is_wp_error( $result ) ) {
			$index_page              = get_post( $request['index_page']['ID'] );
			$model['apprentice_url'] = get_permalink( $index_page->ID );
			$model['preview_url']    = tva_get_preview_url();

			return new WP_REST_Response( $model, 200 );
		}

		return new WP_Error( 'no-results', __( $result, 'thrive-apprentice' ) );
	}

	/**
	 * @param $request WP_REST_Request
	 *
	 * @return WP_REST_Response
	 */
	public function search_pages( $request ) {
		$term        = $request->get_param( 'term' );
		$so_settings = TVA_Sendowl_Settings::instance()->get_settings();
		$post_type   = $request->get_param( 'post_type' );
		if ( empty( $post_type ) ) {
			$post_type = 'page';
		}
		$post_type = explode( ',', $post_type );

		$exclude = array(
			tva_get_setting( 'register_page' ),
			tva_get_setting( 'index_page' ),
		);

		if ( isset( $so_settings['is_connected'] ) && true === (bool) $so_settings['is_connected'] ) {
			$exclude[] = $so_settings['checkout_page']['ID'];
			$exclude[] = $so_settings['thankyou_page']['ID'];
			$exclude[] = $so_settings['thankyou_multiple_page']['ID'];
		}

		$meta_query = [];
		if ( array_search( 'page', $post_type ) !== false && $request->get_param( 'no_landing_pages' ) ) {
			$meta_query = Thrive_Utils::meta_query_no_landing_pages();
		}

		$args = array(
			'post_type'    => $post_type,
			'post_status'  => 'publish',
			's'            => $term,
			'numberposts'  => 10,
			'post__not_in' => $exclude,
			'meta_query'   => $meta_query,
		);

		if ( preg_match( '/[.][a-z]{2,}/', $term ) ) {
			$results = array(
				array(
					'id'          => 0,
					'label'       => $term,
					'post_type'   => '',
					'preview_url' => $term,
					'type'        => 'Link',
					'url'         => $term,
				),
			);
		}

		remove_all_actions( 'pre_get_posts' );

		foreach ( get_posts( $args ) as $post ) {
			$post_type_obj = get_post_type_object( $post->post_type );

			$permalink = get_permalink( $post->ID );

			$results [] = array(
				'id'          => $post->ID,
				'label'       => $post->post_title,
				'type'        => $post_type_obj->labels->singular_name,
				'url'         => $permalink,
				'preview_url' => $permalink,
				'post_type'   => $post->post_type,
			);
		}

		return new WP_REST_Response( $results, 200 );
	}

	/**
	 * @param $request WP_REST_Request
	 *
	 * @return WP_REST_Response
	 */
	public function get_user_settings( $request ) {
		$model = $request->get_param( 'template' );

		/**
		 * We're setting these as the old settings in case the user refreshes the page after he changed the template,
		 * this way we can always revert back to the old settings
		 */
		update_option( 'tva_template_general_settings_old', $model );
		delete_option( 'tva_template_general_settings' );

		return new WP_REST_Response( $model, 200 );
	}

	/**
	 * @param $request WP_REST_Request
	 *
	 * @return WP_REST_Response
	 */
	public function set_advanced_user_settings( $request ) {
		$model = $request->get_param( 'template' );

		/**
		 * We're setting these as the old settings in case the user refreshes the page after he changed the template,
		 * this way we can always revert back to the old settings
		 */
		update_option( 'tva_template_advanced_settings', $model );

		$response        = array();
		$response['url'] = tva_get_preview_url();

		return new WP_REST_Response( $response, 200 );
	}

	/**
	 * Import the Old Apprentice Lessons to the new apprentice
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_old_courses_lessons() {
		$data = array();

		$args = array(
			'taxonomy'   => TVA_Const::OLD_POST_TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'all',
			'per_page'   => - 1,
		);

		$term_query = new WP_Term_Query( $args );

		if ( ! empty( $term_query->terms ) ) {

			foreach ( $term_query->terms as $term ) {

				$args = array(
					'posts_per_page' => - 1,
					'post_type'      => TVA_Const::OLD_POST_TYPE,
					'post_status'    => TVA_Post::$accepted_statuses,
					'tax_query'      => array(
						array(
							'taxonomy' => TVA_Const::OLD_POST_TAXONOMY,
							'field'    => 'term_id',
							'terms'    => array( $term->term_id ),
							'operator' => 'IN',
						),
					),
				);

				$posts         = get_posts( $args );
				$term->lessons = array();
				$term->checked = $term->imported ? false : true;
				if ( $posts ) {
					foreach ( $posts as $post ) {
						$post->course_id = $term->term_id;
						$post->old_id    = $post->ID;
						$post->imported  = get_post_meta( $post->old_id, 'tva_imported', true );
						$post->checked   = $post->imported ? false : true;
						unset( $post->ID );

						$term->lessons[] = $post;
					}
				}

				$data[] = $term;
			}

			return new WP_REST_Response( $data, 200 );
		}

		return new WP_Error( 'no-results', __( 'No courses found to be imported', 'thrive-apprentice' ) );
	}

	/**
	 * Set the import option to true
	 *
	 * @return WP_REST_Response
	 */
	public function set_import() {
		$result = update_option( 'tva_import_decission', true );

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * @return WP_Error|WP_REST_Response
	 */
	public function disable_apprentice() {
		$theme_options = get_option( 'thrive_theme_options' );

		$theme_options['appr_enable_feature'] = 0;

		$result = update_option( 'thrive_theme_options', $theme_options );
		if ( $result ) {
			return new WP_REST_Response( $result, 200 );
		}

		return new WP_Error( 'no-results', __( 'Apprentice cannot be disabled. You can also disable it from Theme Options', 'thrive-apprentice' ) );
	}

	/**
	 * Save the decission of not showing the apprentice ribbon anymore
	 */
	public function disable_apprentice_ribbon() {

		$result = update_option( 'tva_apprentice_ribbon_decission', true );

		if ( $result ) {
			return new WP_REST_Response( $result, 200 );
		}

		return new WP_Error( 'no-results', __( 'Something went wrong !', 'thrive-apprentice' ) );
	}

	/**
	 * Change the preview option for the user
	 *
	 * @param $request WP_REST_Request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function switch_preview( $request ) {
		$response = array();
		$result   = update_option( 'tva_preview_option', $request->get_param( 'preview_option' ) );

		$response['url'] = tva_get_preview_url();

		return new WP_REST_Response( $response, 200 );

	}

	/**
	 * @return array
	 */
	public function get_available_settings() {
		$settings = array();

		$courses = tva_get_courses( array( 'published' => true ) );

		if ( ! empty( $courses ) ) {
			$guide           = wp_list_filter( $courses, array( 'published_lessons_count' => 1 ) );
			$text            = wp_list_filter( $courses, array( 'course_type_name' => 'text' ) );
			$audio           = wp_list_filter( $courses, array( 'course_type_name' => 'audio' ) );
			$video           = wp_list_filter( $courses, array( 'course_type_name' => 'video' ) );
			$big_mix         = wp_list_filter( $courses, array( 'course_type_name' => 'big_mix' ) );
			$audio_text_mix  = wp_list_filter( $courses, array( 'course_type_name' => 'audio_text_mix' ) );
			$video_text_mix  = wp_list_filter( $courses, array( 'course_type_name' => 'video_text_mix' ) );
			$video_audio_mix = wp_list_filter( $courses, array( 'course_type_name' => 'video_audio_mix' ) );
			$not_guide       = array_filter( $courses, function ( $course ) {
				return $course->published_lessons_count > 1;
			} );

			$settings['lesson_headline']     = 1;
			$settings['read']                = count( $guide ) > 0;
			$settings['guide']               = count( $guide ) > 0;
			$settings['text']                = count( $text ) > 0;
			$settings['audio']               = count( $audio ) > 0;
			$settings['video']               = count( $video ) > 0;
			$settings['details']             = count( $courses ) > count( $guide );
			$settings['big_mix']             = count( $big_mix ) > 0;
			$settings['audio_text_mix']      = count( $audio_text_mix ) > 0;
			$settings['video_text_mix']      = count( $video_text_mix ) > 0;
			$settings['video_audio_mix']     = count( $video_audio_mix ) > 0;
			$settings['lessons_plural_text'] = count( $not_guide ) > 0;
			$settings['not_viewed']          = 1;

			$progress = tva_get_user_progress( $courses[0] );

			$progress < 100 ? $settings['progress'] = 1 : $settings['finished'] = 1;

			$lessons_learned = tva_get_learned_lessons();

			foreach ( $courses[0]->lessons as $lesson ) {
				if ( array_key_exists( $courses[0]->term_id, $lessons_learned ) && array_key_exists( $lesson->ID, $lessons_learned[ $courses[0]->term_id ] ) ) {
					$lessons_learned[ $courses[0]->term_id ][ $lesson->ID ] == 1 ? $settings['completed'] = 1 : $settings['lesson_progress'] = 1;
				} else {
					$settings['not_viewed'] = 1;
				}
			}

			$settings[ $courses[0]->course_type_name ] = 1;

			if ( $courses[0]->logged_in === 1 ) {
				$settings['members_only'] = 1;
			}

			$count_lessons  = count( $courses[0]->lessons );
			$count_chapters = count( $courses[0]->chapters );
			$count_modules  = count( $courses[0]->modules );


			if ( $count_lessons === 1 ) {
				$settings['read']        = 1;
				$settings['lesson_text'] = 1;
			} elseif ( $count_lessons > 1 ) {
				$settings['details']      = 1;
				$settings['lessons_text'] = 1;
			}

			if ( $count_chapters === 1 ) {
				$settings['course_chapter']   = 1;
				$settings['chapter_headline'] = 1;

				$chapter = $courses[0]->chapters[0];

				if ( count( $chapter->lessons ) === 1 ) {
					$settings['lesson_text'] = 1;
				} elseif ( count( $chapter->lessons ) > 1 ) {
					$settings['lessons_text'] = 1;
				}
			} elseif ( $count_chapters > 1 ) {
				$settings['course_chapters']  = 1;
				$settings['chapter_headline'] = 1;
				$settings['lessons_text']     = 1;
			}

			if ( $count_modules === 1 ) {
				$settings['course_module']   = 1;
				$settings['module_headline'] = 1;

				$module = $courses[0]->modules[0];

				if ( count( $module->lessons ) === 1 ) {
					$settings['lesson_text'] = 1;
				} elseif ( count( $module->lessons ) > 1 ) {
					$settings['lessons_text'] = 1;
				}

				// maybe we have chapters
				if ( count( $module->chapters ) === 1 ) {
					$settings['course_chapter']   = 1;
					$settings['chapter_headline'] = 1;

					if ( count( $module->chapters[0]->lessons ) === 1 ) {
						$settings['lesson_text'] = 1;
					} elseif ( $module->chapters[0]->lessons > 1 ) {
						$settings['lessons_text'] = 1;
					}
				} elseif ( count( $module->chapters ) > 1 ) {
					$settings['course_chapters']  = 1;
					$settings['chapter_headline'] = 1;
					$settings['lessons_text']     = 1;
				}
			} elseif ( $count_modules > 1 ) {
				$settings['course_modules']  = 1;
				$settings['module_headline'] = 1;
				$settings['lessons_text']    = 1;
				$count_chapters              = 0;

				foreach ( $courses[0]->modules as $module ) {
					$count_chapters = $count_chapters + count( $module->chapters );
				}

				$settings['chapter_headline'] = $count_chapters > 0;
				$settings['course_chapter']   = $count_chapters === 1;
				$settings['course_chapters']  = $count_chapters > 1;
			}
		}

		$settings['preview_url'] = $this->get_preview_url();

		return $settings;
	}

	/**
	 * Check if the user has permission to execute this ajax call
	 *
	 * @param $request
	 *
	 * @return bool
	 */
	public function settings_permissions_check( $request ) {
		/**
		 * If the current has access to TVA but can't edit published pages (eg author/contributor) we should block him for editing sendowl settings too
		 */
		return TVA_Product::has_access() && current_user_can( 'edit_published_pages' );
	}

	/**
	 * Get preview url
	 *
	 * @return mixed
	 */
	public function get_preview_url() {
		return tva_get_preview_url();
	}
}
