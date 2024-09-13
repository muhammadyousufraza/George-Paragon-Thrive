<?php

require_once TVA_Const::plugin_path( 'admin/includes/tva-class-admin.php' );

class TVA_Admin_Controller extends TVA_REST_Controller {
	/**
	 * @var string
	 */
	public $base = 'admin';

	/**
	 * Register Routes
	 */
	public function register_routes() {

		/**
		 * Localize all data previously loaded during the main request
		 */
		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/localize', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'localize_admin' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( static::$namespace . static::$version, '/' . $this->base . '/get_quizzes', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_quizzes' ),
				'permission_callback' => array( $this, 'permissions_check' ),
				'args'                => array(),
			),
		) );
	}

	/**
	 * @return WP_REST_Response
	 */
	public function get_quizzes() {
		$quizzes = apply_filters( 'tva_tqb_get_quizzes', [] );

		return new WP_REST_Response( $quizzes, 200 );
	}

	/**
	 * AJAX-localize admin
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response
	 */
	public function localize_admin() {

		//Sanity check all skins
		\TVA\TTB\Main::sanity_check();

		return rest_ensure_response(
			apply_filters(
				'tva_admin_localize',
				array(
					'courses'           => array(
						'items' => TVA_Course_V2::get_basic_items( [ 'limit' => 10000 ] ),
						'total' => TVA_Course_V2::get_items( array(), true ),
					),
					'products'          => array(
						'total' => TVA\Product::get_items( array(), true ),
						'items' => TVA\Product::get_light_items( [
							'offset' => 0,
							'number' => TVA_Admin::ITEMS_PER_PAGE,
						] ),
					),
					'design'            => array(
						'demo_courses' => TVA_Course_V2::get_basic_items( [ 'status' => 'private' ] ),
						'fonts'        => array(
							'safe'   => tve_dash_font_manager_get_safe_fonts(),
							'google' => array(), //Populated from front
						),
					),
					'tokens'            => TVA_Token::get_items(),
					'logs'              => array(
						'types' => TVA_Logger::get_log_types(),
						'items' => TVA_Logger::fetch_logs(
							array(
								'offset' => 0,
								'limit'  => TVA_Admin::ITEMS_PER_PAGE,
							)
						),
						'total' => TVA_Logger::fetch_logs( array(), true ),
					),
					'sendowl'           => array(
						'is_available' => TVA_SendOwl::is_connected(),
						'bundles'      => TVA_SendOwl::get_bundles(),
						'products'     => TVA_SendOwl::get_products(),
						'discounts'    => TVA_SendOwl::get_discounts_v2(),
					),
					'topics'            => TVA_Topic::get_items(),
					'levels'            => TVA_Level::get_items(),
					'labels'            => tva_get_labels(),
					'dynamicLabelSetup' => array(
						'settings'                  => TVA_Dynamic_Labels::get(),
						'userLabelContexts'         => TVA_Dynamic_Labels::get_user_switch_contexts(),
						'userLabelDefaults'         => TVA_Dynamic_Labels::get_user_switch_default_labels(),
						'ctaLabelContexts'          => TVA_Dynamic_Labels::get_cta_contexts(),
						'ctaLabelDefaults'          => TVA_Dynamic_Labels::get_cta_default_labels(),
						'courseTypeLabelContexts'   => TVA_Dynamic_Labels::get_course_type_label_contexts(),
						'courseTypeLabelDefaults'   => TVA_Dynamic_Labels::get_course_type_label_default_labels(),
						'courseNavigationContexts'  => TVA_Dynamic_Labels::get_course_navigation_contexts(),
						'courseNavigationDefaults'  => TVA_Dynamic_Labels::get_course_navigation_default_labels(),
						'courseNavigationWarnings'  => TVA_Dynamic_Labels::get_course_navigation_warnings(),
						'courseNavWarningsDefaults' => TVA_Dynamic_Labels::get_course_navigation_default_warnings(),
						'courseStructureContexts'   => TVA_Dynamic_Labels::get_course_structure_contexts(),
						'courseStructureDefaults'   => TVA_Dynamic_Labels::get_course_structure_default_labels(),
						'courseProgressContexts'    => TVA_Dynamic_Labels::get_course_progress_contexts(),
						'courseProgressDefaults'    => TVA_Dynamic_Labels::get_course_progress_default_labels(),
					),
					'bundles'           => TVA_Course_Bundles_Manager::get_bundles( true ),
					'migrate_access'    => [
						'should_migrate' => (int) \TVA\Access\Migration::should_migrate(),
						'route'          => tva_get_route_url( 'access_migration' ),
					],
					'skins'             => \TVA\TTB\Main::get_all_skins(),
					'wizard'            => \TVA\TTB\Apprentice_Wizard::localize_admin(),
					'content_types'     => array(
						'lesson' => array(
							'value' => 'lesson',
							'label' => 'Lesson',
							'route' => '/select2-lessons',
						),
						'module' => array(
							'value' => 'module',
							'label' => 'Module',
							'route' => '/select2-modules',
						),
						'course' => array(
							'value' => 'course',
							'label' => 'Course',
							'route' => '/select2-courses',
						),
					),
					'license'           => [
						'exp'           => ! TD_TTW_User_Licenses::get_instance()->has_active_license( 'tva' ),
						'gp'            => TD_TTW_User_Licenses::get_instance()->is_in_grace_period( 'tva' ),
						'show_lightbox' => TD_TTW_User_Licenses::get_instance()->show_gp_lightbox( 'tva' ),
						'grace_time'    => TD_TTW_User_Licenses::get_instance()->get_grace_period_left( 'tva' ),
						'link'          => tvd_get_individual_plugin_license_link( 'tva' ),
					],
				)
			)
		);
	}
}
