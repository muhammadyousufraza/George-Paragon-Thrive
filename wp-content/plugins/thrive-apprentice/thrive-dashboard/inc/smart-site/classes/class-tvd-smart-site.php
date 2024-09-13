<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

if ( ! class_exists( 'TVD_Smart_Site' ) ) :

	final class TVD_Smart_Site {

		/**
		 * Global fields shortcode key.
		 */
		const GLOBAL_FIELDS_SHORTCODE = 'thrive_global_fields';

		/**
		 * Global fields url shortcode key.
		 */
		const GLOBAL_FIELDS_SHORTCODE_URL = 'thrive_global_fields_url';

		/**
		 * Database instance for Smart Site
		 *
		 * @var TVD_Smart_DB
		 */
		private $db;

		/**
		 * @var string
		 */
		private $_dashboard_page = 'tve_dash_smart_site';

		/**
		 * @var TVD_Smart_Shortcodes
		 */
		public $shortcodes;
		public $global_shortcodes;

		/**
		 * TVD_Smart_Site constructor.
		 *
		 * @throws Exception
		 */
		public function __construct() {
			$this->db = new TVD_Smart_DB();
			$this->action_filters();
			$this->shortcodes        = new TVD_Smart_Shortcodes();
			$this->global_shortcodes = new TVD_Global_Shortcodes();
		}

		/**
		 *  Add actiions and filters
		 */
		private function action_filters() {
			add_action( 'current_screen', array( $this, 'conditional_hooks' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'tve_after_db_migration', array( $this, 'insert_default_data' ), 10, 1 );
			add_action( 'rest_api_init', array( $this, 'create_initial_rest_routes' ) );

			add_filter( 'tve_dash_filter_features', array( $this, 'smart_site_feature' ) );
			add_filter( 'tcb_inline_shortcodes', array( $this, 'smart_site_tcb_shortcodes' ), 10, 1 );
			add_filter( 'tcb_dynamiclink_data', array( $this, 'smart_site_links_shortcodes' ) );
			add_filter( 'tcb_content_allowed_shortcodes', array( $this, 'allowed_shotcodes' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue_scripts' ), 11 );
		}

		/**
		 * Filter allowed shortcodes for tve_do_wp_shortcodes
		 *
		 * @param $shortcodes
		 *
		 * @return array
		 */
		public function allowed_shotcodes( $shortcodes ) {
			return array_merge( $shortcodes, array( TVD_Smart_Site::GLOBAL_FIELDS_SHORTCODE, TVD_Smart_Site::GLOBAL_FIELDS_SHORTCODE_URL ) );
		}

		/**
		 * Hooks for the edit screen
		 *
		 */
		public function conditional_hooks() {
			$screen = tve_get_current_screen_key();

			/**
			 * Main Dashboard section
			 */
			if ( $screen === 'toplevel_page_tve_dash_section' ) {
				add_filter( 'tve_dash_filter_features', array( $this, 'smart_site_feature' ) );
			}

			/**
			 * Smart Site Dashboard
			 */
			if ( $screen === 'admin_page_' . $this->_dashboard_page ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
				add_action( 'admin_print_footer_scripts', array( $this, 'backbone_templates' ) );
			}
		}

		/**
		 * Add Card to dashboard
		 *
		 * @param $features
		 *
		 * @return mixed
		 */
		public function smart_site_feature( $features ) {
			$features['smart_site'] = array(
				'icon'        => 'tvd-smart-site',
				'title'       => 'Smart Site',
				'description' => __( 'Define variables and values sitewide to use when building content', 'thrive-dash' ),
				'btn_link'    => add_query_arg( 'page', $this->_dashboard_page, admin_url( 'admin.php' ) ),
				'btn_text'    => __( 'Smart Settings', 'thrive-dash' ),
			);

			return $features;
		}

		/**
		 * Add to admin menu
		 */
		public function admin_menu() {
			add_submenu_page( '', __( 'Smart Site', 'thrive-dash' ), __( 'Smart Site', 'thrive-dash' ), TVE_DASH_CAPABILITY, $this->_dashboard_page, array(
				$this,
				'admin_dashboard',
			) );
		}

		/**
		 * Main Smart Site page content
		 */
		public function admin_dashboard() {
			include TVE_DASH_PATH . '/inc/smart-site/views/admin/templates/dashboard.phtml';
		}

		/**
		 * Insert the default data in the DB
		 *
		 * @param string $option_name
		 */
		public function insert_default_data( $option_name ) {
			if ( $option_name === 'tve_td_db_version' ) {
				$db = new TVD_Smart_DB();
				$db->insert_default_data();
			}
		}

		/**
		 * Enqueue admin styles
		 */
		public function enqueue_styles() {
			tve_dash_enqueue_style( 'tvd-ss-admin', TVD_Smart_Const::url( 'assets/admin/css/styles.css' ) );
		}

		/**
		 * Enqueue admin scripts
		 */
		public function enqueue_scripts() {
			if ( tve_get_current_screen_key() === 'admin_page_' . $this->_dashboard_page ) {
				remove_all_actions( 'admin_notices' );

				tve_dash_enqueue();

				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'backbone' );

				tve_dash_enqueue_script( 'tvd-ss-admin', TVD_Smart_Const::url( 'assets/admin/js/dist/admin.min.js' ), array(
					'jquery',
					'backbone',
					'tve-dash-main-js',
				), false, true );

				wp_localize_script( 'tvd-ss-admin', 'SmartSite', $this->localize() );

			}
		}

		/**
		 * Enqueue scripts in the frontend
		 */
		public function frontend_enqueue_scripts() {
			if ( function_exists( 'is_editor_page' ) && is_editor_page() ) {
				tve_dash_enqueue_script( 'tvd-ss-tcb-hooks', TVD_Smart_Const::url( 'assets/js/tcb_hooks.js' ), array(
					'jquery',
				), false, true );
			}
		}

		/**
		 * Localize admin data
		 *
		 * @return array
		 */
		public function localize() {
			return array(
				't'        => include TVD_Smart_Const::path( 'i18n.php' ),
				'dash_url' => admin_url( 'admin.php?page=tve_dash_section' ),
				'url'      => TVD_Smart_Const::url(),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'routes'   => array(
					'groups' => $this->get_route_url( 'groups' ),
					'fields' => $this->get_route_url( 'fields' ),
				),
				'data'     => array(
					'groups'     => $this->db->get_groups(),
					'fieldTypes' => TVD_Smart_DB::field_types(),
				),
			);
		}

		/**
		 * Add backbone templates
		 */
		public function backbone_templates() {
			$templates = tve_dash_get_backbone_templates( TVD_Smart_Const::path( 'views/admin/templates' ), 'templates' );
			tve_dash_output_backbone_templates( $templates );
		}

		/**
		 * Create the rest routes
		 */
		public function create_initial_rest_routes() {
			$endpoints = array(
				'TVD_Groups_Controller',
				'TVD_Fields_Controller',
			);

			foreach ( $endpoints as $e ) {
				/** @var TVD_REST_Controller $controller */
				$controller = new $e();
				$controller->register_routes();
			}
		}

		/**
		 * Get the route url
		 *
		 * @param       $endpoint
		 * @param int   $id
		 * @param array $args
		 *
		 * @return string
		 */
		private function get_route_url( $endpoint, $id = 0, $args = array() ) {

			$url = get_rest_url() . TVD_Smart_Const::REST_NAMESPACE . '/' . $endpoint;

			if ( ! empty( $id ) && is_numeric( $id ) ) {
				$url .= '/' . $id;
			}

			if ( ! empty( $args ) ) {
				add_query_arg( $args, $url );
			}

			return $url;
		}

		public function add_phone_link( $fields_phone ) {

			foreach ( $fields_phone as &$field ) {
				$field['name'] .= ' (Click to call)';
				if ( ! empty( $field['data'] ) ) {
					$field_data = maybe_unserialize( $field['data'] );
					if ( ! empty( $field_data['phone'] ) ) {
						$field_data['url'] = 'tel:' . $field_data['phone'];
						$field['data']     = maybe_serialize( $field_data );
					}
				}
			}

			return $fields_phone;
		}

		public function add_email_link( $field_email ) {

			foreach ( $field_email as &$field ) {
				$field['name'] .= ' (Click to email)';
				if ( ! empty( $field['data'] ) ) {
					$field_data = maybe_unserialize( $field['data'] );
					if ( ! empty( $field_data['email'] ) ) {
						$field_data['url'] = 'mailto:' . $field_data['email'];
						$field['data']     = maybe_serialize( $field_data );
					}
				}
			}

			return $field_email;
		}

		/**
		 * Get the data from global fields for the links that have a value set
		 *
		 * @param $dynamic_links
		 *
		 * @return mixed
		 */
		public function smart_site_links_shortcodes( $dynamic_links ) {
			if ( ! empty( $_REQUEST[ TVE_FRAME_FLAG ] ) ) {

				$groups = $this->db->get_groups( 0, false );

				$links = array();

				foreach ( $groups as $group ) {
					$fields_link  = $this->db->get_fields_by_type( $group['id'], TVD_Smart_DB::$types['link'] );
					$fields_phone = $this->db->get_fields_by_type( $group['id'], TVD_Smart_DB::$types['phone'] );
					$field_email  = $this->db->get_fields_by_type( $group['id'], TVD_Smart_DB::$types['email'] );

					$fields_phone = $this->add_phone_link( $fields_phone );
					$field_email  = $this->add_email_link( $field_email );


					$new_fields = array_merge( $fields_phone, $field_email );
					$fields     = array_merge( $new_fields, $fields_link );
					$name       = $group['name'];

					/* Add the fields in the group only when the group is empty */
					if ( ! empty( $fields ) && empty( $links[ $name ] ) ) {

						$links[ $name ] = array();

						foreach ( $fields as $global_field ) {
							$data        = maybe_unserialize( $global_field['data'] );
							$inserted_id = $global_field['id'];

							$links[ $name ][] = array(
								'name'        => $global_field['name'],
								'id'          => empty( $global_field['identifier'] ) ? $inserted_id : $global_field['identifier'],
								'inserted_id' => $inserted_id, /* used for backwards compatibility, mostly */
								'identifier'  => $global_field['identifier'],
								'url'         => empty( $data['url'] ) ? '' : $data['url'],
							);
						}
					}

					if ( empty( $links[ $name ] ) ) {
						unset( $links[ $name ] );
					}
				}

				if ( ! empty( $links ) ) {
					$dynamic_links['Global Fields'] = array(
						'links'     => $links,
						'shortcode' => TVD_Smart_Site::GLOBAL_FIELDS_SHORTCODE_URL,
					);
				}
			}

			return $dynamic_links;
		}

		/**
		 * Add the shortcodes to the froala editor
		 *
		 * @param $shortcodes
		 *
		 * @return array
		 */
		public function smart_site_tcb_shortcodes( $shortcodes ) {
			if ( ! empty( $_REQUEST[ TVE_FRAME_FLAG ] ) ) {
				$groups = $this->db->get_groups();

				$result_shortcodes = array();


				foreach ( $groups as $group ) {
					if ( ! empty( $group['fields'] ) ) {
						$result_shortcodes[ $group['id'] ] = array(
							'name'        => $group['name'],
							'option'      => $group['name'],
							'value'       => TVD_Smart_Site::GLOBAL_FIELDS_SHORTCODE,
							'extra_param' => $group['id'],
							'input'       => array(
								'id' => array(
									'type'          => 'select',
									'label'         => __( 'Field', 'thrive-dash' ),
									'value'         => array(),
									'extra_options' => array(
										'multiline'    => array(
											'available_for' => array( TVD_Smart_DB::$types['address'] ),
											'type'          => 'checkbox',
											'label'         => __( 'Include line breaks', 'thrive-dash' ),
											'value'         => false,
										),
										'enable_call'  => array(
											'available_for' => array( TVD_Smart_DB::$types['phone'] ),
											'type'          => 'checkbox',
											'label'         => __( 'Enable click to call', 'thrive-dash' ),
											'value'         => false,
										),
										'enable_email' => array(
											'available_for' => array( TVD_Smart_DB::$types['email'] ),
											'type'          => 'checkbox',
											'label'         => __( 'Enable click to email', 'thrive-dash' ),
											'value'         => false,
										),
									),
								),
							),
						);
						foreach ( $group['fields'] as $field ) {
							$result_shortcodes[ $group['id'] ]['input']['id']['value'][ $field['id'] ]      = $field['name'];
							$result_shortcodes[ $group['id'] ]['input']['id']['value_type'][ $field['id'] ] = $field['type'];
							$result_shortcodes[ $group['id'] ]['input']['id']['real_data'][ $field['id'] ]  = $this->shortcodes->tvd_tss_smart_fields( array( 'id' => $field['id'] ) );
						}
					}
				}

				$shortcode['Global fields'] = array_values( $result_shortcodes );

				$shortcodes = array_merge( $shortcodes, $shortcode );
			}

			return $shortcodes;
		}
	}

endif;

/**
 * Start Smart Site
 */
new TVD_Smart_Site();
