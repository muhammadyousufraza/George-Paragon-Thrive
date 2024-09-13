<?php

/**
 * Created by PhpStorm.
 * User: radu
 * Date: 19.09.2014
 * Time: 10:15
 */

use TCB\SavedLandingPages\Post_Type;

if ( ! class_exists( 'TCB_Landing_Page' ) ) {

	class TCB_Landing_Page extends TCB_Post {
		const HOOK_HEAD       = 'tcb_landing_head';
		const HOOK_BODY_OPEN  = 'tcb_landing_body_open';
		const HOOK_FOOTER     = 'tcb_landing_footer';
		const HOOK_BODY_CLOSE = 'tcb_landing_body_close';

		/**
		 * landing page id
		 *
		 * @var int
		 */
		protected $id;

		/**
		 * holds the configuration array for the landing page
		 *
		 * @var array
		 */
		protected $config = [];

		/**
		 * holds the tve_globals meta configuration values
		 *
		 * @var array
		 */
		protected $globals = [];

		/**
		 * currently used landing page template
		 *
		 * @var string
		 */
		protected $template = '';

		/**
		 * Holds the landing page set value
		 *
		 * @var string
		 */
		public $set = '';

		/**
		 * javascripts for the head and footer section, if any
		 *
		 * @var array
		 */
		protected $global_scripts = [];

		/**
		 * stores the configuration for a template downloaded from the cloud, if this landing page is using one
		 *
		 * @var array
		 */
		protected $cloud_template_data = [];

		/**
		 * flag that holds whether or not this is a template downloaded from the cloud
		 *
		 * @var bool
		 */
		public $is_cloud_template = false;

		/**
		 * holds the events setup from page event manager
		 *
		 * @var array
		 */
		protected $page_events = [];

		/**
		 * Template styles
		 *
		 * @var array
		 */
		public $template_styles = [
			'button'     => [],
			'section'    => [],
			'contentbox' => [],
		];

		/**
		 * Template CSS Vars
		 *
		 * @var array
		 */
		public $template_vars = [
			'colours'   => [],
			'gradients' => [],
		];

		/**
		 * Template Palettes
		 *
		 * @var array
		 */
		public $palettes = [];

		/**
		 * Flag that signifies that a Landing Page Template has styles or vars
		 *
		 * @var bool
		 */
		public $has_template_data = false;

		/**
		 * Get the full path to the folder storing cloud templates on the user's wp install
		 *
		 * @var string
		 */
		private $cloud_base_url;

		/**
		 * Registry holding LP instances
		 *
		 * @var array
		 */
		private static $instances = [];

		/**
		 * Instance of LP Palettes
		 *
		 * @var bool | TCB_LP_Palettes instance
		 */
		private $lp_palette_instance;

		public $skin_typography;

		/**
		 * sent all necessary parameters to avoid extra calls to get_post_meta
		 *
		 * @param int    $landing_page_id
		 * @param string $landing_page_template
		 */
		public function __construct( $landing_page_id, $landing_page_template ) {
			parent::__construct( $landing_page_id );
			if ( is_null( $landing_page_template ) ) {
				$landing_page_template = $this->is_landing_page();
			}

			if ( empty( $this->post ) ) {
				return;
			}

			$this->id             = $this->post->ID;
			$this->globals        = $this->meta( 'tve_globals', null, true, [] );
			$this->template       = $landing_page_template;
			$this->set            = $this->meta( 'tve_landing_set' );
			$this->global_scripts = $this->meta( 'tve_global_scripts' );
			$this->page_events    = $this->meta( 'tve_page_events', null, true, [] );
			$this->cloud_base_url = tcb_get_cloud_base_url();

			$this->config = tve_get_landing_page_config( $landing_page_template );

			if ( $landing_page_template && tve_is_cloud_template( $landing_page_template ) ) {

				if ( empty( $this->set ) ) {
					/**
					 * If the set is empty, we fetch the set from the downloaded templates
					 *
					 * Added in 2.4.2 TAR version
					 */
					$downloaded = tve_get_downloaded_templates();
					if ( ! empty( $downloaded[ $this->template ]['set'] ) ) {
						$this->set = $downloaded[ $this->template ]['set'];
						$this->meta( 'tve_landing_set', $this->set );
					}
				}

				$this->is_cloud_template   = true;
				$this->cloud_template_data = tve_get_cloud_template_config( $landing_page_template );

				/**
				 * Retrieves the palettes that are shown on the landing page Central Style Panel
				 *
				 * @param array page palettes
				 * @param TCB_Landing_Page class instance
				 */
				$this->palettes = apply_filters( 'tcb_get_page_palettes', $this->get_template_palettes(), $this );

				if ( empty( $this->palettes ) && ! empty( $this->config['skin_palettes'] ) && ! empty( $this->config['skin_palettes_config'] ) && ! empty( $this->config['skin_typography'] ) ) {
					$this->palettes            = $this->config['skin_palettes'];
					$this->has_template_data   = true;
					$this->lp_palette_instance = $this->get_palette_instance();
				}
				if ( ! empty( $this->config['skin_typography'] ) ) {
					$this->skin_typography = $this->config['skin_typography'];
				}

				foreach ( $this->template_styles as $key => $value ) {
					$this->template_styles[ $key ] = $this->get_template_styles( $key );

					if ( ! empty( $this->template_styles[ $key ] ) ) {
						$this->has_template_data = true;
					}
				}

				foreach ( $this->template_vars as $key => $value ) {
					/**
					 * Retrieves the landing page variables that work with central style panel
					 *
					 * @param array Page Variables
					 * @param TCB_Landing_Page class instance
					 * @param string variable key (color|gradient)
					 */
					$this->template_vars[ $key ] = apply_filters( 'tcb_get_page_variables', $this->get_template_css_variables( $key ), $this, $key );

					if ( ! empty( $this->template_vars[ $key ] ) ) {
						$this->has_template_data = true;
					}
				}
			}

			$this->globals     = empty( $this->globals ) ? [] : $this->globals;
			$this->page_events = empty( $this->page_events ) ? [] : $this->page_events;
		}

		/**
		 * Get a LP instance. Makes sure each needed LP is not instantiated more than once
		 *
		 * @param $landing_page_id
		 * @param $landing_page_template
		 *
		 * @return TCB_Landing_Page
		 */
		public static function get_instance( $landing_page_id, $landing_page_template = null ) {
			if ( empty( static::$instances[ $landing_page_id ] ) ) {
				static::$instances[ $landing_page_id ] = new static( $landing_page_id, $landing_page_template );
			}

			return static::$instances[ $landing_page_id ];
		}

		/**
		 * outputs the HEAD section specific to the landing page
		 * finally, it calls the tcb_landing_head hook to allow injecting other stuff in the head
		 */
		public function head() {
			/* I think the favicon should be added using the wp_head hook and not like this */
			if ( function_exists( 'thrive_get_options_for_post' ) ) {
				$options = thrive_get_options_for_post();
				if ( ! empty( $options['favicon'] ) ) {
					echo '<link rel="shortcut icon" href="' . esc_url( $options['favicon'] ) . '"/>';
				}
			}

			$this->fonts();

			if ( ! empty( $this->global_scripts['head'] ) && ! is_editor_page() ) {
				$this->global_scripts['head'] = $this->remove_jquery( $this->global_scripts['head'] );
				echo $this->global_scripts['head']; // phpcs:ignore
			}

			/**
			 * TAR-8556 very specific integration with PixelYourSite plugin for LPs, if in the future
			 * we need to add stuff for more plugins maybe we should find more general way of doing this
			 */
			if ( class_exists( 'PixelYourSite\PYS', false ) ) {
				$pixel_your_site_instance = PixelYourSite\PYS();
				if ( method_exists( $pixel_your_site_instance, 'managePixels' ) ) {
					$pixel_your_site_instance->managePixels();
				}
			}

			\TCB\Lightspeed\Css::get_instance( $this->id )->load_optimized_style( 'base' );

			if ( $this->should_strip_head_css() ) {
				$this->strip_head_css();
			} else {
				wp_head();
			}


			$hook = self::HOOK_HEAD;

			/**
			 * Action called right before outputting the </head> closing tag
			 */
			do_action( $hook, $this->id );

			$page = is_editor_page() ? 'editor' : 'frontend';

			/**
			 * Specialized action depending on whether the current page is an editor page or not
			 *
			 * In general no javascript should be outputted in the <head> element while in the editor page.
			 * This allows hooking only in the frontend context for such cases (e.g. outputting global scripts from TD)
			 *
			 * @param int $id current landing page id
			 */
			do_action( "{$hook}_{$page}", $this->id );

			if ( $this->is_v2() ) {
				/** On thrive themes, there is a nasty overflow on html */
				/** echo '<style>html,body{overflow-x:initial}</style>'; */
			}
		}

		/**
		 * Adds landing page set styles to the list of global styles rendered by TAR
		 *
		 * @param $global_styles
		 *
		 * @return array
		 */
		public static function add_landing_page_styles( $global_styles = [] ) {
			$post_id = get_the_ID();

			/* make sure we send lp elements style properly while changing other global styles */
			if ( wp_doing_ajax() && empty( $post_id ) && isset( $_POST['post_id'] ) ) {
				$post_id = absint( $_POST['post_id'] );
			}
			if ( tve_post_is_landing_page( $post_id ) ) {
				$element_type = [
					'button',
					'section',
					'contentbox',
				];
				foreach ( $element_type as $type ) {
					$global_styles[] = get_post_meta( $post_id, 'thrv_lp_template_' . $type, true );
				}
			}

			return $global_styles;
		}

		/**
		 * For landing pages, returns the landing page set needed for content blocks cloud call
		 *
		 * @param string $special_set
		 *
		 * @return string
		 */
		public static function get_lp_set( $special_set = '' ) {
			$post_id = get_the_ID();

			if ( wp_doing_ajax() && empty( $post_id ) && isset( $_POST['post_id'] ) ) {
				$post_id = absint( $_POST['post_id'] );
			}

			if ( tve_post_is_landing_page( $post_id ) ) {
				$special_set = get_post_meta( $post_id, 'tve_landing_set', true );
			}

			return $special_set;
		}

		/**
		 * Prepare LP variables for frontend
		 *
		 * @return array
		 */
		public static function prepare_landing_page_variables_for_front() {

			$search  = [];
			$replace = [];
			$post_id = get_the_ID();
			if ( tve_post_is_landing_page( $post_id ) ) {
				$set_colors    = get_post_meta( $post_id, 'thrv_lp_template_colours', true );
				$set_gradients = get_post_meta( $post_id, 'thrv_lp_template_gradients', true );

				if ( ! is_array( $set_colors ) ) {
					$set_colors = [];
				}

				if ( ! is_array( $set_gradients ) ) {
					$set_gradients = [];
				}

				foreach ( $set_colors as $color ) {
					$search[]  = 'var(' . TVE_LP_COLOR_VAR_CSS_PREFIX . $color['id'] . ')';
					$replace[] = $color['color'];

				}
				foreach ( $set_gradients as $gradient ) {
					$search[]  = 'var(' . TVE_LP_GRADIENT_VAR_CSS_PREFIX . $gradient['id'] . ')';
					$replace[] = $gradient['gradient'];
				}
			}

			return [
				'search'  => $search,
				'replace' => $replace,
			];
		}

		/**
		 * Prepares the Landing Page for custom export (by the user with the build-in export functionality)
		 */
		public function prepare_landing_page_for_export() {

			$config = [];

			foreach ( $this->template_styles as $element_type => $value ) {
				$meta_value = get_post_meta( $this->id, 'thrv_lp_template_' . $element_type, true );

				if ( ! empty( $meta_value ) ) {
					$config['page_styles'][ $element_type ] = $meta_value;
				}
			}

			$tpl_colors    = get_post_meta( $this->id, 'thrv_lp_template_colours', true );
			$tpl_gradients = get_post_meta( $this->id, 'thrv_lp_template_gradients', true );

			$config['page_palettes'] = $this->get_template_palettes();

			if ( ! empty( $tpl_colors ) ) {
				$config['page_vars']['colors'] = $tpl_colors;
			}

			if ( ! empty( $tpl_gradients ) ) {
				$config['page_vars']['gradients'] = $tpl_gradients;
			}

			return $config;
		}

		/**
		 * Outputs Landing Page template global variables
		 * TODO change this function to be on the LP instance, we already use the id there
		 *
		 * This variables comes from
		 */
		public static function output_landing_page_variables() {
			$post_id = get_the_ID();
			if ( tve_post_is_landing_page( $post_id ) ) {
				$tpl_colors    = get_post_meta( $post_id, 'thrv_lp_template_colours', true );
				$tpl_gradients = get_post_meta( $post_id, 'thrv_lp_template_gradients', true );

				if ( ! is_array( $tpl_colors ) ) {
					$tpl_colors = [];
				}

				if ( ! is_array( $tpl_gradients ) ) {
					$tpl_gradients = [];
				}


				foreach ( $tpl_colors as $color ) {
					$color_name = TVE_LP_COLOR_VAR_CSS_PREFIX . $color['id'];
					echo $color_name . ':' . $color['color'] . ';'; //phpcs:ignore

					/* convert variables to hsl & print them */
					$hsl_data = tve_rgb2hsl( $color['color'] );
					echo tve_print_color_hsl( $color_name, $hsl_data ); //phpcs:ignore
				}
				foreach ( $tpl_gradients as $gradient ) {
					echo TVE_LP_GRADIENT_VAR_CSS_PREFIX . $gradient['id'] . ':' . $gradient['gradient'] . ';'; //phpcs:ignore
				}

				$master_variables = array_filter( $tpl_colors, function ( $ar ) {
					return ( isset( $ar['parent'] ) && (int) $ar['parent'] === - 1 );
				} );

				if ( ! empty( $master_variables ) ) {
					$master_variable = reset( $master_variables );

					echo tve_prepare_master_variable( $master_variable ); //phpcs:ignore
				}
				/* We only print the variables when TTB is not active */
				if ( ! tve_dash_is_ttb_active() ) {
					/* We need LP config to get the palettes configs from it */
					$config = ! empty( tcb_landing_page( $post_id )->config ) ? tcb_landing_page( $post_id )->config : tve_get_landing_page_config( tve_post_is_landing_page( $post_id ) );

					if ( isset( $config['skin_palettes'], $config['skin_palettes_config'] ) ) {
						$lp_palettes_instance = new TCB_LP_Palettes( $post_id, $config['skin_palettes'], $config['skin_palettes_config'] );
						$skin_palettes        = $lp_palettes_instance->get_variables_for_css();

						/* Print the css on the page */
						if ( ! empty( $skin_palettes ) ) {
							echo $skin_palettes;
						}
					}
				}
			}
		}

		/**
		 * Modifies the global styles CSS
		 *
		 * @param $value
		 * @param $key
		 */
		public function modify_cloud_global_style_css( &$value, $key ) {
			$value = str_replace( '{tcb_lp_base_url}', $this->cloud_base_url . 'templates/css/images/', $value );
		}

		/**
		 * BULK Updates the global styles from the cloud
		 *
		 * @param array $page_styles
		 *
		 * Called from set_cloud_template method and form import LP from zip method
		 */
		public function update_template_global_styles( $page_styles = [] ) {

			if ( empty( $page_styles ) && ! empty( $this->cloud_template_data['page_styles'] ) ) {
				$page_styles = $this->cloud_template_data['page_styles'];

				/**
				 * Element styles that are from cloud, images needs to be fetches from local
				 */
				array_walk_recursive( $page_styles, [ $this, 'modify_cloud_global_style_css' ] );
			}


			foreach ( $this->template_styles as $element_type => $value ) {
				$this->meta_delete( 'thrv_lp_template_' . $element_type );
			}

			if ( empty( $page_styles ) ) {
				return;
			}

			foreach ( $this->template_styles as $element_type => $value ) {
				$style_post_meta[ $element_type ] = 'thrv_lp_template_' . $element_type;

				if ( ! empty( $page_styles[ $element_type ] ) && is_array( $page_styles[ $element_type ] ) ) {
					update_post_meta( $this->id, $style_post_meta[ $element_type ], $page_styles[ $element_type ] );
				}
			}
		}

		/**
		 * BULK Updates the variable palettes from the cloud
		 *
		 * @param array   $palettes
		 * @param boolean $bulk_update
		 */
		public function update_template_palettes( $palettes = [], $bulk_update = false ) {
			if ( ! apply_filters( 'tcb_allow_landing_page_set_data', true, $this ) ) {
				return;
			}

			if ( empty( $palettes ) && ! empty( $this->cloud_template_data['page_palettes'] ) ) {
				$palettes = $this->cloud_template_data['page_palettes'];
			}

			if ( ! empty( $palettes ) && is_array( $palettes ) ) {

				if ( $bulk_update ) {

					$meta_value = $palettes;

				} else {

					$meta_value = [
						'original'  => $palettes,
						'modified'  => $palettes,
						'active_id' => 0,
					];
				}
				update_post_meta( $this->id, 'thrv_lp_template_palettes', $meta_value );
			} else {
				$this->meta_delete( 'thrv_lp_template_palettes' );
			}
		}

		/**
		 * BULK Updates the LP CSS Variables
		 *
		 * @param array $page_vars
		 *
		 * Called from set_cloud_template method and from import LP from zip method
		 */
		public function update_template_css_variables( $page_vars = [] ) {

			if ( ! apply_filters( 'tcb_allow_landing_page_set_data', true, $this ) ) {
				return;
			}

			if ( empty( $page_vars ) && ! empty( $this->cloud_template_data['page_vars'] ) ) {
				$page_vars = $this->cloud_template_data['page_vars'];
			}

			if ( ! empty( $page_vars['colors'] ) && is_array( $page_vars['colors'] ) ) {
				update_post_meta( $this->id, 'thrv_lp_template_colours', $page_vars['colors'] );
			} else {
				$this->meta_delete( 'thrv_lp_template_colours' );
			}

			if ( ! empty( $page_vars['gradients'] ) && is_array( $page_vars['gradients'] ) ) {
				update_post_meta( $this->id, 'thrv_lp_template_gradients', $page_vars['gradients'] );
			} else {
				$this->meta_delete( 'thrv_lp_template_gradients' );
			}
		}

		/**
		 * Returns template css variables
		 *
		 * @param string $for
		 *
		 * @return array
		 */
		protected function get_template_css_variables( $for = '' ) {
			$post_meta_name = 'thrv_lp_template_' . $for;

			$template_css_variables = get_post_meta( $this->id, $post_meta_name, true );

			if ( ! is_array( $template_css_variables ) ) {
				$template_css_variables = [];
			}

			return array_values( $template_css_variables );
		}

		/**
		 * Returns the template styles for a particular element
		 *
		 * @param string $for_element
		 *
		 * @return array
		 */
		protected function get_template_styles( $for_element = '' ) {
			$post_meta_name = 'thrv_lp_template_' . $for_element;
			$post_id        = $this->id;

			$element_template_styles = tve_get_global_styles( $for_element, $post_meta_name, $post_id );

			return $element_template_styles;
		}

		/**
		 * Returns the variable palettes
		 *
		 * @return array
		 */
		private function get_template_palettes() {
			$palettes = get_post_meta( $this->id, 'thrv_lp_template_palettes', true );

			if ( ! is_array( $palettes ) ) {
				$palettes = [];
			}

			return $palettes;
		}

		/**
		 * Updates the Template Styles
		 *
		 * Template Buttons, Template Sections and Template Contentboxes
		 *
		 * @param string $identifier
		 * @param string $for_element
		 * @param string $name
		 * @param        $css
		 * @param array  $fonts
		 * @param bool   $ignore_css
		 */
		public function update_template_style( $identifier, $for_element, $name, $css, $fonts = [], $ignore_css = false ) {

			$post_meta_name = 'thrv_lp_template_' . $for_element;

			$template_styles = get_post_meta( $this->id, $post_meta_name, true );

			if ( ! is_array( $template_styles ) ) {
				/**
				 * Security check: if the option is not empty and somehow the stored value is not an array, make it an array.
				 */
				$template_styles = [];
			}

			if ( ! empty( $template_styles[ $identifier ] ) ) {

				/**
				 * Edit Global Style
				 */
				if ( false === $ignore_css ) {
					$template_styles[ $identifier ]['css']   = $css;
					$template_styles[ $identifier ]['fonts'] = $fonts;
				}
				if ( $name ) {
					$template_styles[ $identifier ]['name'] = $name;
				}

				update_post_meta( $this->id, $post_meta_name, $template_styles );
			}
		}

		/**
		 * Updates Template CSS variables
		 *
		 * Template Colors and Template Gradients
		 *
		 * @param int   $id
		 * @param array $data
		 */
		public function update_template_css_variable( $id = 0, $data = [] ) {

			$post_meta_name = [
				'color'    => 'thrv_lp_template_colours',
				'gradient' => 'thrv_lp_template_gradients',
			];

			$tpl_css_var_post_meta_name = $post_meta_name[ $data['key'] ];
			$tpl_css_var_values         = get_post_meta( $this->id, $tpl_css_var_post_meta_name, true );
			if ( ! is_array( $tpl_css_var_values ) ) {
				$tpl_css_var_values = [];
			}

			$index = - 1;

			foreach ( $tpl_css_var_values as $key => $tpl_val ) {
				if ( (int) $tpl_val['id'] === (int) $id ) {
					$index = $key;
					break;
				}
			}

			if ( $index > - 1 ) {
				$tpl_css_var_values[ $index ][ $data['key'] ] = $data['value'];
				$tpl_css_var_values[ $index ]['name']         = $data['name'];

				if ( ! empty( $data['hsl'] ) && is_array( $data['hsl'] ) ) {
					$tpl_css_var_values[ $index ]['hsl'] = $data['hsl'];
				}

				if ( ! empty( $data['hsl_parent_dependency'] ) && is_array( $data['hsl_parent_dependency'] ) ) {
					$tpl_css_var_values[ $index ]['hsl_parent_dependency'] = $data['hsl_parent_dependency'];
				}

				if ( $data['custom_name'] ) {
					/**
					 * Update the custom name only if the value is 1
					 */
					$tpl_css_var_values[ $index ]['custom_name'] = $data['custom_name'];
				}
			}

			/**
			 * Process Linked Vars
			 */
			foreach ( $data['linked_variables'] as $var_id => $new_value ) {
				$index = - 1;

				foreach ( $tpl_css_var_values as $key => $tpl_val ) {
					if ( intval( $tpl_val['id'] ) === intval( $var_id ) ) {
						$index = $key;
						break;
					}
				}

				if ( $index > - 1 ) {

					$variable_value = $new_value;

					if ( is_array( $new_value ) && ! empty( $new_value['hsl_parent_dependency'] ) ) {
						$tpl_css_var_values[ $index ]['hsl_parent_dependency'] = $new_value['hsl_parent_dependency'];
						$variable_value                                        = $new_value['value'];
					}

					$tpl_css_var_values[ $index ][ $data['key'] ] = $variable_value;
				}
			}

			update_post_meta( $this->id, $tpl_css_var_post_meta_name, $tpl_css_var_values );
		}

		/**
		 * Updates the template modified palette
		 *
		 * @param int   $active_id
		 * @param int   $modified_id
		 * @param array $modified_values
		 */
		public function update_template_palette( $active_id = 0, $modified_id = 0, $modified_values = [] ) {
			$meta_value = get_post_meta( $this->id, 'thrv_lp_template_palettes', true );

			if ( is_array( $meta_value ) && ! empty( $meta_value['modified'][ $modified_id ] ) ) {

				$meta_value['active_id'] = intval( $active_id );
				$modified_values['name'] = $meta_value['modified'][ $modified_id ]['name'];

				$meta_value['modified'][ $modified_id ] = $modified_values;
			}

			update_post_meta( $this->id, 'thrv_lp_template_palettes', $meta_value );
		}

		/**
		 * Returns the Palettes instance specific to this LP
		 *
		 * @return bool|TCB_LP_Palettes
		 */
		public function get_palette_instance() {
			if ( $this->lp_palette_instance === null && ! empty( $this->config['skin_palettes'] ) && ! empty( $this->config['skin_palettes_config'] ) ) {
				$this->lp_palette_instance = new TCB_LP_Palettes( $this->id, $this->config['skin_palettes'], $this->config['skin_palettes_config'] );
			}

			return $this->lp_palette_instance;
		}

		/**
		 * Updates the auxiliary variable from the Palette instance
		 *
		 * @param $color_id
		 * @param $color
		 */
		public function update_auxiliary_variable( $color_id, $color ) {
			$this->get_palette_instance()->update_auxiliary_variable( $color_id, $color );
		}


		/**
		 * Checks if a variable is auxiliary it's not the master variable)
		 *
		 * @param $id
		 *
		 * @return bool
		 */
		public function is_auxiliary_variable( $id ) {
			return $this->get_palette_instance()->is_auxiliary_variable( $id );
		}

		/**
		 * Updates the Master variable from the Palette instance
		 *
		 * @param array $master_variables
		 * @param       $active_id
		 */
		public function update_master_hsl( $master_variables = [], $active_id = 0 ) {
			$this->get_palette_instance()->update_master_hsl( $master_variables, $active_id );
		}

		/**
		 * Updates the colors in the Palettes config
		 *
		 * @param $hsl
		 */
		public function update_variables_in_config( $hsl ) {
			$this->get_palette_instance()->update_variables_in_config( $hsl );
		}

		/**
		 * Function that allows the central style panel to be displayed on a content edited with TAR
		 *
		 * @param bool $return
		 *
		 * @return bool
		 */
		public static function tcb_skin_allow_central_style_panel( $return = false ) {
			$lp_id        = get_the_ID();
			$landing_page = tcb_landing_page( $lp_id );

			if ( $landing_page->is_landing_page() && ! tve_dash_is_ttb_active() && $landing_page->has_skin_style_panel() ) {
				$return = true;
			}

			return $return;
		}

		/**
		 * Prints the skin typography on a page, this will be called only when we have TTB LPs but no TTB active
		 *
		 * @param $post_id
		 */
		public static function print_skin_typography( $post_id ) {
			$lp            = tcb_landing_page( $post_id );
			$lp_typography = $lp->skin_typography;
			$lp_inherit    = $lp->meta( 'ttb_inherit_typography', null, true );

			if ( ! empty( $lp_typography ) && ( ! tve_dash_is_ttb_active() || $lp_inherit === '0' ) ) {
				/* Replace the variable to match the colors from config */
				$lp_typography = str_replace( '--tcb-color-', TCB_LP_Palettes::SKIN_COLOR_VARIABLE_PREFIX, $lp_typography );

				$lp_typography = tcb_custom_css( $lp_typography );

				echo '<style type="text/css" class="tcb_skin_lp_typography">' . $lp_typography . '</style>';
			}

			/* If we have TTB active and the LP inherits the TTB typography we should print it on the page */
			if ( tve_dash_is_ttb_active() && ! empty( $lp_inherit ) && ! empty( $lp->meta( 'theme_skin_tag' ) ) ) {
				$lp_typography = tcb_default_style_provider()->get_processed_styles( thrive_typography()->style(), 'string' );

				echo '<style type="text/css" class="tcb_skin_lp_typography">' . $lp_typography . '</style>';
			}
		}

		/**
		 * Checks if an LP has a Central Style panel from a skin
		 *
		 * @return bool
		 */
		public function has_skin_style_panel() {
			$lp_palettes_instance = $this->get_palette_instance();

			if ( ! $lp_palettes_instance ) {
				return false;
			}

			return ! empty( $lp_palettes_instance->get_smart_lp_palettes_v2() ) && $lp_palettes_instance->get_smart_lp_palettes_config_v2();
		}

		/**
		 * outputs <link>s for each font used by the page
		 * fonts come from the configuration array
		 *
		 * @return TCB_Landing_Page allows chained calls
		 */
		protected function fonts() {
			if ( empty( $this->config['fonts'] ) ) {
				return $this;
			}
			foreach ( $this->config['fonts'] as $font ) {
				if ( strrpos( $font, 'fonts.googleapis.com' ) !== false && tve_dash_is_google_fonts_blocked() ) {
					continue;
				}
				echo sprintf( '<link href="%s" rel="stylesheet" type="text/css" />', esc_url( $font ) );
			}

			return $this;
		}

		/**
		 * this calls the WP wp_head() function, it will remove every <style>..</style> from the head
		 */
		protected function strip_head_css() {
			/* capture the output and strip out some of the <style></style> nodes */
			ob_start();
			wp_head();
			$contents = ob_get_clean();
			/* keywords to search for within the CSS rules */
			$tcb_rules_keywords = [
				'.ttfm',
				'data-tve-custom-colour',
				'.tve_more_tag',
				'.thrive-adminbar-icon',
				'#wpadminbar',
				'html { margin-top: 32px !important; }',
				'img.emoji',
				'img.wp-smiley',
				/* Social Warfare style - SUPP-6725 */
				'sw-icon-font',
			];
			/* keywords to search for within CSS style node - classes and ids for the <style> element */
			$tcb_style_classes = [
				'thrive-default-styles',
				'tve_user_custom_style',
				'tve_custom_style',
				'tcb_skin_lp_typography',
				'tve_global_style',
				'tve_global_variables',
				'optm_lazyload',
				/* custom fonts plugin */
				'cf-frontend-style-inline-css',
				/* lightspeed styles */
				'tcb-style-',
			];
			/**
			 * Filter list of CSS classes / DOM attributes for style nodes that should be kept
			 *
			 * @param array            $tcb_style_classes list of strings
			 * @param TCB_Landing_Page $this              Landing Page instance
			 *
			 * @return array filtered list of styles
			 */
			$tcb_style_classes = apply_filters( 'tcb_lp_strip_css_whitelist', $tcb_style_classes, $this );

			$theme_dependency = get_post_meta( $this->id, 'tve_disable_theme_dependency', true );
			if ( empty( $theme_dependency ) ) {
				$tcb_style_classes[] = 'wp-custom-css';
			}

			if ( preg_match_all( '#<style(.*?)>(.*?)</style>#ms', $contents, $m ) ) {
				foreach ( $m[2] as $index => $css_rules ) {
					$css_node  = $m[1][ $index ];
					$remove_it = true;
					foreach ( $tcb_rules_keywords as $tcb_keyword ) {
						if ( strpos( $css_rules, $tcb_keyword ) !== false ) {
							$remove_it = false;
							break;
						}
					}
					if ( $remove_it ) {
						foreach ( $tcb_style_classes as $style_class ) {
							if ( strpos( $css_node, $style_class ) !== false ) {
								$remove_it = false;
								break;
							}
						}
					}
					if ( $remove_it ) {
						$contents = str_replace( $m[0][ $index ], '', $contents );
					}
				}
			}
			echo $contents; //phpcs:ignore

			/**
			 * Support for Custom Fonts plugin
			 */
			if ( class_exists( 'Bsf_Custom_Fonts_Render' ) ) {
				/** @var Bsf_Custom_Fonts_Render $bsf_renderer */
				$bsf_renderer = Bsf_Custom_Fonts_Render::get_instance();
				if ( method_exists( $bsf_renderer, 'add_style' ) ) {
					$bsf_renderer->add_style();
				}
			}
		}

		/**
		 * get all the css data needed for this landing page that's been previously saved from the editor
		 * example: body background, content background (if content is outside tve_editor) etc
		 *
		 * @return array
		 */
		public function get_css_data_tcb2() {
			$config = $this->globals;

			return array(
				'custom_color' => ! empty( $config['body_css'] ) ? ' data-css="' . $config['body_css'] . '"' : '',
				'class'        => '',
				'css'          => '',
				'main_area'    => [
					'css' => '',
				],
			);
		}

		/**
		 * get all the css data needed for this landing page that's been previously saved from the editor
		 * example: body background, content background (if content is outside tve_editor) etc
		 *
		 * @return array
		 */
		public function get_css_data_tcb1() {
			$config  = $this->globals;
			$lp_data = array(
				'custom_color' => ! empty( $config['lp_bg'] ) ? ' data-tve-custom-colour="' . $config['lp_bg'] . '"' : '',
				'class'        => ! empty( $config['lp_bgcls'] ) ? ' ' . $config['lp_bgcls'] : '',
				'css'          => '',
				'main_area'    => [
					'css' => '',
				],
			);
			if ( ! empty( $config['lp_bg'] ) && $config['lp_bg'] === '#ffffff' ) {
				$lp_data['custom_color'] = '';

				$lp_data['css'] .= 'background-color:#ffffff;';
			}
			if ( ! empty( $config['lp_bgp'] ) ) {
				if ( $config['lp_bgp'] === 'none' ) {
					$background_string = 'background-image:none;';
				} else {
					$background_string = "background-image:url('{$config['lp_bgp']}');";
				}
				$lp_data['css'] .= $background_string . 'background-repeat:repeat;background-size:auto;';
			} elseif ( ! empty( $config['lp_bgi'] ) ) {
				if ( $config['lp_bgi'] === 'none' ) {
					$background_string = 'background-image:none;';
				} else {
					$background_string = "background-image:url('{$config['lp_bgi']}');";
				}
				$lp_data['css'] .= $background_string . 'background-repeat:no-repeat;background-size:cover;background-position:center center;';
			}
			if ( ! empty( $config['lp_bga'] ) ) {
				$lp_data['css'] .= "background-attachment:{$config['lp_bga']};";
				if ( $config['lp_bga'] === 'fixed' ) {
					$lp_data['class'] .= ( $lp_data['class'] ? ' ' : '' ) . 'tve-lp-fixed';
				}
			}
			if ( ! empty( $config['lp_cmw'] ) && ! empty( $config['lp_cmw_apply_to'] ) ) { // landing page - content max width
				if ( $config['lp_cmw_apply_to'] === 'tve_post_lp' ) {
					$lp_data['main_area']['css'] .= "max-width: {$config['lp_cmw']}px;";
				}
			}

			return $lp_data;
		}

		/**
		 * get all the css data needed for this landing page that's been previously saved from the editor
		 * example: body background, content background (if content is outside tve_editor) etc
		 *
		 * @return array
		 */
		public function get_css_data() {
			if ( isset( $this->globals['body_css'] ) ) {
				/* TCB2 - just a single body attribute which controls all styles */
				$lp_data = $this->get_css_data_tcb2();
			} else {
				$lp_data = $this->get_css_data_tcb1();
			}

			$lp_data['class'] .= ! empty( $lp_data['class'] ) ? ' tve_lp' : 'tve_lp';
			$lp_data['class'] .= is_editor_page() ? ' tve_editor_page tve_editable' : '';

			if ( ! empty( $this->globals['body_class'] ) ) {
				$lp_data['class'] .= ' ' . ( is_array( $this->globals['body_class'] ) ? implode( ' ', $this->globals['body_class'] ) : $this->globals['body_class'] );
			}
			$lp_data['class'] = apply_filters( 'tcb_lp_body_class', $lp_data['class'] );

			return $lp_data;
		}

		/**
		 * called right after <body> open tag
		 */
		public function after_body_open() {
			if ( ! empty( $this->global_scripts['body'] ) && ! is_editor_page() ) {
				$this->global_scripts['body'] = $this->remove_jquery( $this->global_scripts['body'] );
				echo $this->global_scripts['body']; // phpcs:ignore
			}

			$hook = self::HOOK_BODY_OPEN;

			/**
			 * Action called right after the body opening tag
			 */
			do_action( $hook, $this->id );

			$page = is_editor_page() ? 'editor' : 'frontend';

			/**
			 * Specialized action depending on whether the current page is an editor page or not
			 *
			 * In general no javascript should be outputted in the <body> element while in the editor page.
			 * This allows hooking only in the frontend context for such cases (e.g. outputting global scripts from TD)
			 *
			 * @param int $id current landing page id
			 */
			do_action( "{$hook}_{$page}", $this->id );
		}

		/**
		 * called before the WP get_footer hook
		 */
		public function footer() {
			do_action( self::HOOK_FOOTER, $this->id );
		}

		/**
		 * called right before the <body> end tag
		 */
		public function before_body_end() {
			$hook = self::HOOK_BODY_CLOSE;

			/**
			 * Action called right before outputting the </body> closing tag
			 *
			 * @param int $id current landing page id
			 */
			do_action( $hook, $this->id );

			$page = is_editor_page() ? 'editor' : 'frontend';

			/**
			 * Specialized action depending on whether the current page is an editor page or not
			 *
			 * In general no javascript should be outputted in the <body> element while in the editor page.
			 * This allows hooking only in the frontend context for such cases (e.g. outputting global scripts from TD)
			 *
			 * @param int $id current landing page id
			 */
			do_action( "{$hook}_{$page}", $this->id );

			if ( ! empty( $this->global_scripts['footer'] ) && ! is_editor_page() ) {
				$this->global_scripts['footer'] = $this->remove_jquery( $this->global_scripts['footer'] );
				echo $this->global_scripts['footer']; // phpcs:ignore
			}
		}

		/**
		 * whether or not this landing page should have lightbox associated
		 */
		public function needs_lightbox() {
			return ! empty( $this->config['has_lightbox'] );
		}

		/**
		 * check if the associated lightbox exists and, if not, create it
		 *
		 * @param bool $replace_default_texts
		 */
		public function check_lightbox( $replace_default_texts = true ) {
			if ( $replace_default_texts ) {
				$this->replace_default_texts();
			}

			if ( ! $this->needs_lightbox() ) {
				return;
			}

			if ( isset( $this->globals['lightbox_id'] ) ) {
				$lightbox = get_post( $this->globals['lightbox_id'] );
				if ( ! $lightbox || $lightbox->post_type !== 'tcb_lightbox' ) {
					unset( $lightbox );
				}
			}

			if ( empty( $lightbox ) ) {

				$this->globals['lightbox_id'] = $this->new_lightbox();

				tve_update_post_meta( $this->id, 'tve_globals', $this->globals );
			}
			if ( ! empty( $this->config['lightbox'] ) && ! empty( $this->config['lightbox']['exit_intent'] ) && ! $this->has_page_exit_intent() ) {
				/* setup the lightbox to be triggered on exit intent */
				$this->page_events    = empty( $this->page_events ) ? [] : $this->page_events;
				$this->page_events [] = [
					't'      => 'exit',
					'a'      => 'thrive_lightbox',
					'config' => [
						'e_mobile' => '1',
						'e_delay'  => '30',
						'l_id'     => $this->globals['lightbox_id'],
						'l_anim'   => 'slide_top',
					],
				];
				tve_update_post_meta( $this->id, 'tve_page_events', $this->page_events );
			}

			/* check if the id of the lightbox from the content is different than the id of the generated lightbox */
			$post_content = tve_get_post_meta( $this->id, 'tve_updated_post' );

			/* 12.10.2015 - lightbox events can also be setup with a simple string: tcb_open_lightbox */
			$open_lightbox_event = '{tcb_open_lightbox}';
			$events_config       = array(
				array(
					't'      => 'click',
					'a'      => 'thrive_lightbox',
					'config' => array(
						'l_id'   => empty( $this->globals['lightbox_id'] ) ? '' : $this->globals['lightbox_id'],
						'l_anim' => 'slide_top',
					),
				),
			);
			$post_content        = str_replace( $open_lightbox_event, '__TCB_EVENT_' . htmlentities( json_encode( $events_config ) ) . '_TNEVE_BCT__', $post_content, $number_of_replacements );
			$save_it             = $number_of_replacements;

			if ( strpos( $post_content, "&quot;l_id&quot;:&quot;{$this->globals['lightbox_id']}&quot;" ) === false ) {
				$post_content = preg_replace( '#&quot;l_id&quot;:(|&quot;)(\d+)(\1)#', '&quot;l_id&quot;:&quot;' . $this->globals['lightbox_id'] . '&quot;', $post_content );
				$save_it      = true;
			}

			if ( $save_it ) {
				tve_update_post_meta( $this->id, 'tve_updated_post', $post_content );
			}
		}

		/**
		 * generate new lightbox specific for this landing page
		 *
		 * @param string $title
		 *
		 * @return int
		 */
		public function new_lightbox( $title = null, $lb_meta = null, $template_suffix = '' ) {
			$landing_page = get_post( $this->id );
			$meta         = [
				'tve_lp_lightbox' => $this->template,
			];

			$tcb_content = $this->lightbox_default_content( $template_suffix );

			if ( $this->is_cloud_template && is_null( $lb_meta ) && ! empty( $this->cloud_template_data['lightbox']['meta'] ) ) {
				$lb_meta = $this->cloud_template_data['lightbox']['meta'];
			}

			if ( $this->is_cloud_template && ! is_null( $lb_meta ) ) {
				$meta                   = array_merge( $meta, $lb_meta );
				$meta['tve_custom_css'] = $this->get_cloud_css_v2( true, $template_suffix );
				$lightbox_globals       = $meta['tve_globals'];
			} else {
				$lightbox_globals = array(
					'l_cmw' => isset( $this->config['lightbox']['max_width'] ) ? $this->config['lightbox']['max_width'] : '600px',
					'l_cmh' => isset( $this->config['lightbox']['max_height'] ) ? $this->config['lightbox']['max_height'] : '600px',
				);
			}

			return TCB_Lightbox::create(
				$title ? $title : ( 'Lightbox - ' . $landing_page->post_title . ' (' . $this->config['name'] . ')' ),
				$tcb_content,
				$lightbox_globals,
				$meta
			);
		}

		public function update_lightbox( $lightbox_id, $title = null, $lb_meta = null, $template_suffix = '' ) {
			$landing_page = get_post( $this->id );
			$meta         = [
				'tve_lp_lightbox' => $this->template,
			];

			$tcb_content = $this->lightbox_default_content( $template_suffix );

			if ( $this->is_cloud_template && is_null( $lb_meta ) && ! empty( $this->cloud_template_data['lightbox']['meta'] ) ) {
				$lb_meta = $this->cloud_template_data['lightbox']['meta'];
			}

			if ( $this->is_cloud_template && ! is_null( $lb_meta ) ) {
				$meta                   = array_merge( $meta, $lb_meta );
				$meta['tve_custom_css'] = $this->get_cloud_css_v2( true, $template_suffix );
				$lightbox_globals       = $meta['tve_globals'];
			} else {
				$lightbox_globals = array(
					'l_cmw' => isset( $this->config['lightbox']['max_width'] ) ? $this->config['lightbox']['max_width'] : '600px',
					'l_cmh' => isset( $this->config['lightbox']['max_height'] ) ? $this->config['lightbox']['max_height'] : '600px',
				);
			}

			return TCB_Lightbox::update(
				$lightbox_id,
				$title ? $title : ( 'Lightbox - ' . $landing_page->post_title . ' (' . $this->config['name'] . ')' ),
				$tcb_content,
				$lightbox_globals,
				$meta
			);
		}

		/**
		 * fetch default lightbox content from one of the files inside landing-page/lightbox/ folder
		 *
		 * @param string $template_suffix used for multi-lightboxes landing pages
		 */
		public function lightbox_default_content( $template_suffix = '' ) {
			if ( $this->is_cloud_template ) {
				/* if it's a cloud template, the lightbox content needs to be fetched from wp-uploads/tcb_lp_templates/lightboxes/{template_name}.tpl */
				$lb_file  = tcb_get_cloud_base_path() . 'lightboxes/' . $this->template . $template_suffix . '.tpl';
				$contents = '';

				if ( file_exists( $lb_file ) ) {
					$contents = file_get_contents( $lb_file );
				}

				$contents = tcb_apply_template_content_filter( $contents, true, $template_suffix );

				return $this->replace_default_texts( $contents );
			}

			/**
			 * from this point forward => this is a regular template - the lightbox content is available in a local php file from the plugin
			 */

			ob_start();
			if ( file_exists( dirname( __DIR__ ) . '/lightboxes/' . $this->template . '.php' ) ) {
				include dirname( __DIR__ ) . '/lightboxes/' . $this->template . '.php';
			}
			$contents = ob_get_clean();

			return $this->replace_default_texts( $contents );
		}

		/**
		 * removes references to jquery loaded directly from CDN - this will break the editor scripts on this page
		 *
		 * @param string $custom_script
		 *
		 * @return string
		 */
		public function remove_jquery( $custom_script ) {
			if ( ! is_editor_page() ) {
				return $custom_script;
			}

			$js_search = '/src=(["\'])(.+?)((code.jquery.com\/jquery-|ajax.googleapis.com\/ajax\/libs\/jquery\/))(\d)(.+?)\1/si';

			return preg_replace( $js_search, 'src=$1$1', $custom_script );
		}

		/**
		 * replace all occurences of custom texts we currently use for generating server-specifing data
		 *
		 * {tcb_timezone}
		 *
		 * @param string $post_content if null it will take by default this contents of this landing page
		 *
		 * @return string
		 */
		public function replace_default_texts( $post_content = null ) {
			if ( null === $post_content ) {
				$update_post_meta = true;
				$post_content     = tve_get_post_meta( $this->id, 'tve_updated_post' );
			}

			if ( empty( $post_content ) ) {
				return '';
			}

			$save_it = false;

			/**
			 * {tcb_timezone}
			 */
			if ( strpos( $post_content, 'data-timezone="{tcb_timezone}"' ) !== false ) {
				$timezone_offset = get_option( 'gmt_offset' );
				$sign            = ( $timezone_offset < 0 ? '-' : '+' );
				$min             = abs( $timezone_offset ) * 60;
				$hour            = floor( $min / 60 );
				$tzd             = $sign . str_pad( $hour, 2, '0', STR_PAD_LEFT ) . ':' . str_pad( $min % 60, 2, '0', STR_PAD_LEFT );
				$post_content    = str_replace( 'data-timezone="{tcb_timezone}"', 'data-timezone="' . $tzd . '"', $post_content );
				$save_it         = true;
			}

			if ( strpos( $post_content, '{tcb_lp_base_url}' ) !== false ) {
				$replacement  = $this->is_cloud_template ? tcb_get_cloud_base_url() . 'templates' : TVE_LANDING_PAGE_TEMPLATE;
				$post_content = str_replace( '{tcb_lp_base_url}', untrailingslashit( $replacement ), $post_content );
				$save_it      = true;
			}

			/**
			 * Allows modifying LP content before it's actually being used/applied on a page.
			 *
			 * @param string           $post_content content to filter
			 * @param TCB_Landing_Page $this         current LP instance
			 *
			 * @return string content to be applied
			 */
			$post_content = apply_filters( 'tcb_landing_page_default_texts', $post_content, $this );

			if ( isset( $update_post_meta ) && $save_it ) {
				tve_update_post_meta( $this->id, 'tve_updated_post', $post_content );
			}

			return $post_content;
		}

		/**
		 * enqueue the CSS file needed for this template
		 */
		public function enqueue_css() {
			$handle = 'tve_landing_page_' . $this->template;

			if ( $this->is_cloud_template ) {
				if ( (int) $this->cloud_template_data['LP_VERSION'] !== 2 ) {
					tve_enqueue_style( $handle, trailingslashit( tcb_get_cloud_base_url() ) . 'templates/css/' . $this->template . '.css', 100 );
				}
			} elseif ( file_exists( plugin_dir_path( __DIR__ ) . 'templates/css/' . $this->template . '.css' ) ) {
				tve_enqueue_style( $handle, TVE_LANDING_PAGE_TEMPLATE . '/css/' . $this->template . '.css', 100 );
			}
		}

		public function ensure_external_assets() {

			$lightbox_ids = [];

			/**
			 * look for page events
			 */
			foreach ( $this->page_events as $event ) {
				if ( isset( $event['a'] ) && $event['a'] === 'thrive_lightbox' && ! empty( $event['config'] ) && ! empty( $event['config']['l_id'] ) ) {
					$lightbox_ids[] = $event['config']['l_id'];
				}
			}

			/**
			 * look for page invents in content
			 */
			$post_content = tve_get_post_meta( $this->id, 'tve_updated_post' );
			if ( preg_match_all( '#&quot;l_id&quot;:(null|&quot;(.*?)&quot;)#', $post_content, $matches ) ) {
				$lightbox_ids = array_merge( $lightbox_ids, $matches[2] );
			}

			$lightbox_ids = array_unique( $lightbox_ids );

			global $post;
			$old_post = $post;

			/**
			 * This code is executed really early in the request - and sometimes it generates output ( before the <html> tag )
			 * we need to catch and ignore this output
			 */
			ob_start();

			/**
			 * let the others do their content and add their scripts
			 */
			foreach ( $lightbox_ids as $id ) {
				$post = get_post( $id );
				apply_filters( 'the_content', '' );
			}

			/**
			 * get rid of any undesired output.
			 */
			ob_end_clean();

			$post = $old_post;
		}

		/**
		 * check if this landing page has a "Exit Intent" event setup to display a lightbox
		 *
		 * @return bool
		 */
		public function has_page_exit_intent() {
			if ( empty( $this->page_events ) ) {
				return false;
			}
			foreach ( $this->page_events as $page_event ) {
				if ( ! empty( $page_event['t'] ) && ! empty( $page_event['a'] ) && $page_event['t'] === 'exit' && ( $page_event['a'] === 'thrive_lightbox' || $page_event['a'] === 'thrive_leads_2_step' ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * reset landing page to its default content
		 * this assumes that the tve_landing_page post meta field is set to the value of the correct landing page template
		 *
		 * @param bool $reset_global_scripts whether or not to also reset the 'tve_global_scripts' data
		 */
		public function reset( $reset_global_scripts = true ) {

			$post_content    = $this->default_content();
			$meta_key_suffix = '_' . $this->template;
			$globals         = $this->meta( 'tve_globals' . $meta_key_suffix );

			$meta = $this->is_cloud_template && $this->cloud_template_data && ! empty( $this->cloud_template_data['meta'] ) ? $this->cloud_template_data['meta'] : [];

			$meta = wp_parse_args( $meta, [
				'tve_custom_css'         => '',
				'tve_user_custom_css'    => '',
				'tve_has_masonry'        => '',
				'tve_has_typefocus'      => '',
				'tve_has_wistia_popover' => '',
				'tve_page_events'        => [],
				'thrive_icon_pack'       => '',
			] );

			if ( ! empty( $meta['tve_globals'] ) ) {
				$meta['tve_globals']['lightbox_id'] = isset( $globals['lightbox_id'] ) ? $globals['lightbox_id'] : 0;
			} else {
				$meta['tve_globals'] = array( 'lightbox_id' => isset( $globals['lightbox_id'] ) ? $globals['lightbox_id'] : 0 );
			}
			if ( ! $this->is_cloud_template && ! empty( $this->config['globals']['body_css'] ) ) {
				$meta['tve_globals']['body_css'] = $this->config['globals']['body_css'];
			}
			if ( $this->is_cloud_template && ! empty( $this->cloud_template_data ) && (int) $this->cloud_template_data['LP_VERSION'] === 2 ) {
				/* Load the default css for the page */
				$meta['tve_custom_css'] = $this->get_cloud_css_v2();
				if ( ! empty( $this->cloud_template_data['lightboxes'] ) ) {
					$meta['tve_globals']['lb_map'] = isset( $globals['lb_map'] ) ? $globals['lb_map'] : [];
				}
			} elseif ( ! $this->is_cloud_template && ! empty( $this->config['head_css'] ) ) {
				$meta['tve_custom_css'] = $this->config['head_css'];
			}

			$post_content = $this->replace_default_texts( $post_content );
			$this->meta( 'tve_updated_post' . $meta_key_suffix, $post_content );

			foreach ( $meta as $k => $v ) {
				$meta_key = $k;

				if ( $k !== 'tve_disable_theme_dependency' ) {
					$meta_key = $k . $meta_key_suffix;
				}

				$this->meta( $meta_key, $v );
			}

			if ( $reset_global_scripts ) {
				/* this does not use LP-specific meta key */
				$this->meta( 'tve_global_scripts', [] );
			}

			$this->globals = $meta['tve_globals'];

			/* check to see if a default lightbox exists for this and if necessary, create it */
			/* make sure the associated lightbox exists and is setup in the event manager */
			if ( isset( $this->cloud_template_data['lightboxes'] ) ) {
				/**
				 * new version: multiple lightboxes for a landing page
				 */
				$this->ensure_multi_lightbox( isset( $meta['tve_globals']['lb_map'] ) ? $meta['tve_globals']['lb_map'] : [] );
			} else {
				$this->check_lightbox( false );
			}

			if ( empty( $this->cloud_template_data['sections'] ) ) {
				$this->meta( 'sections', '' );
			}

			tve_update_post_custom_fonts( $this->post->ID, [] );
		}

		/**
		 * Make sure all the needed lightboxes exist
		 */
		public function ensure_multi_lightbox( $lb_id_map = [] ) {
			$lightboxes = $this->cloud_template_data['lightboxes'];

			/* check if the id of the lightbox from the content is different than the id of the generated lightbox */
			$post_content = $this->meta( 'tve_updated_post', null, true );

			foreach ( $lightboxes as $lb_id => $lb_data ) {
				if ( isset( $lb_id_map[ $lb_id ] ) ) {
					$lb = get_post( $lb_id_map[ $lb_id ] );
					if ( ! $lb ) {
						unset( $lb_id_map[ $lb_id ] );
					}
				}
				if ( ! isset( $lb_id_map[ $lb_id ] ) ) {
					$lb_id_map[ $lb_id ] = $this->new_lightbox( null, $lb_data['meta'], '-' . $lb_id );
				} else {
					$this->update_lightbox( $lb_id_map[ $lb_id ], null, $lb_data['meta'], '-' . $lb_id );
				}
				$post_content = preg_replace( '#&quot;l_id&quot;:(&quot;)?' . $lb_id . '(&quot;)?#', '&quot;l_id&quot;:&quot;' . $lb_id_map[ $lb_id ] . '&quot;', $post_content );
			}

			/**
			 * Page events
			 */
			if ( ! empty( $this->cloud_template_data['meta']['tve_page_events'] ) ) {
				$this->page_events = $this->cloud_template_data['meta']['tve_page_events'];

				foreach ( $this->page_events as $index => $evt ) {
					if ( $evt['a'] === 'thrive_lightbox' && isset( $lb_id_map[ $evt['config']['l_id'] ] ) ) {
						$this->page_events[ $index ]['config']['l_id'] = $lb_id_map[ $evt['config']['l_id'] ];
					}
				}
				$this->meta( 'tve_page_events', $this->page_events, true );
			}

			$this->globals['lb_map'] = $lb_id_map;

			$this->meta( 'tve_globals', $this->globals, true );
			$this->meta( 'tve_updated_post', $post_content, true );
		}

		/**
		 * Get the CSS text for the landing page or for a lightbox
		 *
		 * @param bool
		 * @param string $lb_suffix
		 *
		 * @return string
		 */
		public function get_cloud_css_v2( $for_lightbox = false, $lb_suffix = '' ) {
			$suffix = $for_lightbox ? ( $lb_suffix . '_lightbox.css' ) : '.css';
			$file   = tcb_get_cloud_base_path() . 'templates/css/' . $this->template . $suffix;
			$css    = '';

			if ( file_exists( $file ) ) {
				$css = str_replace( '{tcb_lp_base_url}', tcb_get_cloud_base_url() . 'templates/css/images/', file_get_contents( $file ) );
			}

			return apply_filters( 'tcb_alter_cloud_css', $css, $this->cloud_template_data, $for_lightbox, $lb_suffix );
		}

		/**
		 * Set the cloud template for this landing page
		 *
		 * @param string $cloud_template
		 * @param array  $config allow passing a configuration object directly
		 *
		 * @return TCB_Landing_Page
		 * @throws Exception
		 *
		 */
		public function set_cloud_template( $cloud_template, $config = null ) {
			if ( ! $config ) {
				$config = tve_get_cloud_template_config( $cloud_template );
				if ( false === $config ) {
					throw new Exception( 'Could not validate Landing Page configuration' );
				}
			}

			$this->template            = $cloud_template;
			$this->set                 = $config['set'];
			$this->is_cloud_template   = true;
			$this->cloud_template_data = $this->config = $config;

			if ( ! tve_dash_is_ttb_active() && ! empty( $config['skin_typography'] ) ) {
				/**
				 * Compatibility with landingpages.thrivethemes.com
				 * Allows skin typography to be inserted from external config
				 */
				$this->skin_typography = $config['skin_typography'];
			}

			$this->meta( 'tve_landing_page', $this->template );
			$this->meta( 'tve_landing_set', $this->set );
			$this->reset( true );

			$this->set_lp_cloud_template_action( $config );
			$this->update_template_css_variables();
			$this->update_template_global_styles();
			$this->update_template_palettes();

			/* If TTB is active and we set the meta for typography inherit */
			if ( tve_dash_is_ttb_active() ) {
				$this->meta( 'ttb_inherit_typography', ! empty( $config['skin_tag'] ), true );
			}

			$this->lp_palette_instance = $this->get_palette_instance();

			if ( ! empty( $config ) ) {
				if ( ! empty( $config['skin_palettes'] ) ) {
					$this->lp_palette_instance->update_lp_palettes_v2( $config['skin_palettes'] );
				}

				if ( ! empty( $config['skin_palettes_config'] ) ) {
					$this->lp_palette_instance->update_lp_palettes_config_v2( $config['skin_palettes_config'] );
				}

				if ( ! empty( $config['skin_palettes'] ) && ! empty( $config['skin_palettes_config'] ) ) {
					$this->has_template_data = true;
				}
			}

			return $this;
		}

		/**
		 * Trigger an action that allows other plugins and themes to hook into it and do extra logic
		 *
		 * Used in ThriveTheme when loading a theme homepage or importing a theme homepage from a zip file
		 *
		 * @param array $config
		 *
		 * @return TCB_Landing_Page
		 */
		public function set_lp_cloud_template_action( $config = [] ) {
			/**
			 * Allows other functionality to link when a landing page is being set
			 *
			 * Used in Thrive Theme when a smart landing page that is linked to a skin is set
			 *
			 * @param TCB_Landing_Page $this
			 * @param array            $config
			 */
			do_action( 'tcb_set_lp_cloud_template', $this, $config );

			return $this;
		}

		/**
		 * remove or change the current landing page template for the post with a default landing page, or a previously saved landing page
		 * this also updates the post meta fields related to the selected template
		 *
		 * if it's a default template, then it will not change anything related to post content, as it will try to load it from the saved template
		 *
		 * each template will have it's own fields saved for the post, this helps users to not lose any content when switching back and forth various templates
		 *
		 * @param     $landing_page_template
		 *
		 * @return TCB_Landing_Page
		 */
		public function change_template( $landing_page_template, $lp_id ) {
			/**
			 * Delete Template Colors, Template Gradients and Template Palettes meta in case the page is not a cloud page
			 */
			$this->meta_delete( 'thrv_lp_template_colours' );
			$this->meta_delete( 'thrv_lp_template_gradients' );
			$this->meta_delete( 'thrv_lp_template_palettes' );
			$this->meta_delete( 'tve_landing_set' );
			/**
			 * Also delete Template styles meta
			 */
			foreach ( $this->template_styles as $element_type => $value ) {
				$this->meta_delete( 'thrv_lp_template_' . $element_type );
			}

			if ( ! $landing_page_template ) {
				$this->template = '';
				$this->set      = '';
				$this->meta_delete( 'tve_landing_page' );
				//Delete Also The Setting To Disable Theme CSS
				$this->meta_delete( 'tve_disable_theme_dependency' );
				tve_clean_up_meta_leftovers( $this->post->ID );

				return $this;
			}

			/* Landing Page default template */
			if ( strpos( $landing_page_template, 'user-saved-template-' ) !== 0 ) {
				/* default landing page template: load in the default template content - this can also be a template downloaded from the cloud */
				$this->template          = $landing_page_template;
				$this->config            = tve_get_landing_page_config( $this->template );
				$this->is_cloud_template = tve_is_cloud_template( $landing_page_template );
				if ( $this->is_cloud_template ) {
					$this->cloud_template_data = tve_get_cloud_template_config( $landing_page_template );
				}

				/* 2014-09-19: reset the landing page contents, the whole page will reload using the clear new template */
				$this->reset( false );

			} else {
				$this->load_saved_lp( $landing_page_template, $lp_id );
			}

			$this->meta( 'tve_landing_page', $this->template );

			return $this;
		}

		/**
		 * Get the full path to the landing-page folder
		 *
		 * @param string|null $file
		 *
		 * @return string
		 */
		public static function path( $file = null ) {
			$file = $file ? ltrim( $file, '/\\' ) : '';

			return plugin_dir_path( __DIR__ ) . $file;
		}

		/**
		 * get all the available landing page templates
		 * this function reads in the landing page config file and returns an array with names, thumbnail images, and template codes
		 *
		 * @return array
		 */
		public static function templates() {
			//TODO: can we remove this ?
			$templates = [];
			$config    = include self::path( 'templates/_config.php' );
			foreach ( $config as $code => $template ) {
				$templates[ $code ] = array(
					'name'       => $template['name'],
					'set'        => $template['set'],
					'tags'       => isset( $template['tags'] ) ? $template['tags'] : [],
					'downloaded' => isset( $template['downloaded'] ) ? $template['downloaded'] : false,
				);
			}
			if ( ! empty( $templates['blank'] ) ) {
				$blank = [ 'blank' => $templates['blank'] ];
				unset( $templates['blank'] );
				$templates = $blank + $templates;
			}

			return $templates;
		}

		/**
		 * Should only return the blank template
		 *
		 * @return array
		 */
		public static function templates_v2() {
			return [
				'blank_v2' => [
					'name'       => 'Blank Page',
					'tags'       => [ 'blank' ],
					'set'        => 'Blank',
					'type'       => 'l',
					'thumb'      => TVE_LANDING_PAGE_TEMPLATE . '/thumbnails/blank_v2.png',
					'LP_VERSION' => 2,
				],
			];
		}

		/**
		 * returns the default template content for a landing page post
		 *
		 * if the landing page template is a local one - the contents are stored in a php file template inside the landing-page folder in the plugin
		 * if the landing page template is a "Cloud" template (previously downloaded from the API) - the contents are stored in a corresponding file in the wp-uploads folder
		 *
		 * @param string $default possibility to use a template as default
		 *
		 * @return string
		 */
		public function default_content( $default = 'blank_v2' ) {
			$template_name = $this->template;
			if ( $this->is_cloud_template ) {

				$downloaded_template_file = tcb_get_cloud_base_path() . 'templates/' . $template_name . '.tpl';
				/* if $data === false => this is not a valid template - this means either some files got deleted, either the wp_options entry is corrupted */
				if ( $this->cloud_template_data && file_exists( $downloaded_template_file ) ) {
					$content = file_get_contents( $downloaded_template_file );
				}
			}

			if ( empty( $content ) ) {
				$landing_page_dir = plugin_dir_path( dirname( __FILE__ ) );

				if ( empty( $template_name ) || ! is_file( $landing_page_dir . 'templates/' . $template_name . '.php' ) ) {
					$template_name = $default;
				}

				ob_start();
				include $landing_page_dir . 'templates/' . $template_name . '.php';
				$content = ob_get_contents();
				ob_end_clean();
			}

			return tcb_apply_template_content_filter( $content, false, '' );
		}

		/**
		 * Check if a Landing Page is actually a TCB2 template-based landing page
		 *
		 * @return bool
		 */
		public function is_v2() {
			if ( empty( $this->config ) ) {
				return false;
			}

			return isset( $this->config['LP_VERSION'] ) && (int) $this->config['LP_VERSION'] === 2;
		}

		/**
		 * Whether or not the current landingpage should load the theme CSS
		 *
		 * @return bool
		 */
		public function should_strip_head_css() {
			return empty( $this->globals['do_not_strip_css'] );
		}

		/**
		 * Whether or not the theme's CSS files should be loaded in the landing page
		 *
		 * @return bool
		 */
		public function should_remove_theme_css() {
			/**
			 * Filter whether or not to strip the styles enqueued by the theme
			 *
			 * @param bool $strip
			 *
			 * @return bool
			 */
			return apply_filters( 'tcb_theme_dependency', (bool) $this->meta( 'tve_disable_theme_dependency' ) );
		}

		/**
		 * Applies a LP cloud template on page
		 *
		 * @param int|string  $page_id
		 * @param string      $cloud_template_key
		 * @param string|null $cloud_template_uid unique identifier from cloud
		 *
		 * @return TCB_Landing_Page
		 * @throws Exception
		 */
		public static function apply_cloud_template( $page_id, $cloud_template_key, $cloud_template_uid = null ) {
			$force_download = defined( 'TCB_CLOUD_DEBUG' ) && TCB_CLOUD_DEBUG;
			if ( ! $force_download ) {
				$transient_name = 'tcb_template_download_' . ( $cloud_template_uid ?: $cloud_template_key );
				if ( get_transient( $transient_name ) === false ) {
					$force_download = true;
					set_transient( $transient_name, 1, DAY_IN_SECONDS );
				}
			}
			$downloaded = tve_get_downloaded_templates();

			if ( $force_download || ! array_key_exists( $cloud_template_key, $downloaded ) || tve_get_landing_page_config( $cloud_template_key ) === false ) {
				/**
				 * this will throw Exception if anything goes wrong
				 */
				TCB_Landing_Page_Cloud_Templates_Api::getInstance()->download(
					array(
						'key' => $cloud_template_key,
						'uid' => (string) $cloud_template_uid,
					)
				);
			}

			$landing_page = new static( $page_id, null );

			return $landing_page->set_cloud_template( $cloud_template_key );
		}

		/**
		 * Loads a saved Landing page
		 *
		 * @param $landing_page_template
		 * @param $lp_id
		 *
		 * @return $this
		 */
		public function load_saved_lp( $landing_page_template, $lp_id ) {
			$template_index = (int) str_replace( 'user-saved-template-', '', $landing_page_template );

			/* in this case we are on a migrated saved template, and all the date will be read from de LP Custom Post Type */
			if ( strpos( $lp_id, TCB\SavedLandingPages\Saved_Lp::OLD_ID_PREFIX ) === false ) {
				$meta[ $template_index ] = [
					'is_migrated' => get_post_meta( $lp_id, 'is_migrated', true ),
				];

				foreach ( TCB\SavedLandingPages\Saved_Lp::get_meta_keys() as $meta_key ) {
					$meta[ $template_index ][ $meta_key ] = get_post_meta( $lp_id, TCB\SavedLandingPages\Saved_Lp::get_post_type_prefix() . $meta_key, true );
				}

				foreach ( TCB\SavedLandingPages\Saved_Lp::get_content_keys() as $meta_key ) {
					$contents[ $template_index ][ $meta_key ] = get_post_meta( $lp_id, TCB\SavedLandingPages\Saved_Lp::get_post_type_prefix() . $meta_key, true );
				}

			} else {
				$template_index = TCB\SavedLandingPages\Saved_Lp::normalize_old_id( $lp_id );
				/* at this point, the template is one of the previously saved templates (saved by the user) - it holds the index from the tve_saved_landing_pages_content which needs to be loaded */
				$contents = get_option( 'tve_saved_landing_pages_content' );
				$meta     = get_option( 'tve_saved_landing_pages_meta' );
			}
			/* make sure we don't mess anything up */
			if ( empty( $contents ) || empty( $meta ) || ! isset( $contents[ $template_index ] ) ) {
				return $this;
			}

			$content        = $contents[ $template_index ];
			$this->template = $landing_page_template = $meta[ $template_index ]['template'];

			/* replace old ids just in case */
			$content = json_encode( $content );
			$content = preg_replace( '/page-id-\d*/m', "page-id-{$this->post->ID}", $content );
			$content = json_decode( $content, true );

			if ( empty( $content['more_found'] ) ) {
				$content['more_found']  = false;
				$content['before_more'] = $content['content'];
			}

			$key = '_' . $landing_page_template;

			if ( empty( $meta[ $template_index ]['theme_dependency'] ) ) {
				$meta[ $template_index ]['theme_dependency'] = 0;
			}

			$saved_template_meta_data = [
				'tpl_colours'            => 'thrv_lp_template_colours',
				'tpl_gradients'          => 'thrv_lp_template_gradients',
				'tpl_button'             => 'thrv_lp_template_button',
				'tpl_section'            => 'thrv_lp_template_section',
				'tpl_contentbox'         => 'thrv_lp_template_contentbox',
				'tpl_palettes'           => 'thrv_lp_template_palettes',
				'tpl_palettes_v2'        => TCB_LP_Palettes::LP_PALETTES,
				'tpl_palettes_config_v2' => TCB_LP_Palettes::LP_PALETTES_CONFIG,
			];

			/**
			 * Page Saved lp meta if present
			 */
			foreach ( $saved_template_meta_data as $meta_data_key => $meta_name ) {
				if ( ! empty( $meta[ $template_index ][ $meta_data_key ] ) ) {
					$this->meta( $meta_name, $meta[ $template_index ][ $meta_data_key ] );
				}
			}

			$this->meta( 'theme_skin_tag', empty( $meta[ $template_index ]['tpl_skin_tag'] ) ? '' : $meta[ $template_index ]['tpl_skin_tag'] );
			$this->meta( 'tve_disable_theme_dependency', $meta[ $template_index ]['theme_dependency'] );
			$this->meta( "tve_content_before_more{$key}", $content['before_more'] );
			$this->meta( "tve_content_more_found{$key}", $content['more_found'] );
			$this->meta( "tve_custom_css{$key}", $content['inline_css'] );
			$this->meta( "tve_user_custom_css{$key}", $content['custom_css'] );
			$this->meta( "tve_updated_post{$key}", $content['content'] );
			$this->meta( "tve_globals{$key}", ! empty( $content['tve_globals'] ) ? $content['tve_globals'] : [] );
			$this->meta( 'tve_global_scripts', ! empty( $content['tve_global_scripts'] ) ? $content['tve_global_scripts'] : [] );
			$this->meta( 'sections', isset( $content['sections'] ) ? $content['sections'] : [] );
			$this->meta( '_tve_header', isset( $content['sections']['header']['ID'] ) ? $content['sections']['header']['ID'] : 0 );
			$this->meta( '_tve_footer', isset( $content['sections']['footer']['ID'] ) ? $content['sections']['footer']['ID'] : 0 );

		}


		/**
		 * When the Typography is inherited from TTB, we need to update the selectors and make them strongger
		 *
		 * @param $defaults
		 *
		 * @return mixed
		 */
		public static function tcb_typography_inherit_selectors_update( $defaults ) {
			$id      = get_the_ID();
			$inherit = tve_get_post_meta( $id, 'ttb_inherit_typography' );

			if ( ! empty( $inherit ) && ! empty( $id ) && tve_post_is_landing_page( $id ) && tve_dash_is_ttb_active() ) {
				foreach ( $defaults as $key => &$selector_data ) {
					$selector_data['selector'] = '#tcb_landing_page ' . $selector_data['selector'];
				}
			}

			return $defaults;
		}
	}

	function tcb_landing_page( $post_id, $landing_page_template = null ) {

		return TCB_Landing_Page::get_instance( $post_id, $landing_page_template );
	}

	/**
	 *
	 * Get the full path to the folder storing cloud templates on the user's wp install
	 *
	 * @return array|string
	 */
	function tcb_get_cloud_base_path() {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return '';
		}

		return trailingslashit( $upload['basedir'] ) . TVE_CLOUD_LP_FOLDER . '/';
	}

	/**
	 *
	 * Get the full path to the folder storing cloud templates on the user's wp install
	 *
	 * @return array|string
	 */
	function tcb_get_cloud_base_url() {
		$upload = wp_upload_dir();
		if ( ! empty( $upload['error'] ) ) {
			return trailingslashit( site_url() ) . 'wp-content/uploads/' . TVE_CLOUD_LP_FOLDER;
		}

		$base_url = str_replace( [ 'http://', 'https://' ], '//', $upload['baseurl'] );

		return trailingslashit( $base_url ) . TVE_CLOUD_LP_FOLDER . '/';
	}

	/**
	 * Applies a filter on a fresh template content
	 *
	 * @param string $content
	 * @param bool   $is_lightbox
	 * @param string $file_suffix
	 *
	 * @return string
	 */
	function tcb_apply_template_content_filter( $content, $is_lightbox, $file_suffix ) {

		/**
		 * Filter. Allows dynamically modifying the contents
		 *
		 * @param string $content         Current content
		 * @param bool   $is_lightbox     whether or not the current filter is applied for a lightbox
		 * @param string $template_suffix a file suffix to be appended to the template file
		 */
		return apply_filters( 'tcb_landing_page_default_content', $content, $is_lightbox, $file_suffix );
	}

	//TODO let's move the filters in another file, and check what functions shoul not be static

	add_action( 'tcb_get_extra_global_variables', [ 'TCB_Landing_Page', 'output_landing_page_variables' ], PHP_INT_MAX );

	add_filter( 'tcb_get_extra_global_styles', [ 'TCB_Landing_Page', 'add_landing_page_styles' ] );

	add_filter( 'tcb_prepare_global_variables_for_front', [ 'TCB_Landing_Page', 'prepare_landing_page_variables_for_front' ], 10, 2 );

	add_filter( 'tcb_get_special_blocks_set', [ 'TCB_Landing_Page', 'get_lp_set' ] );

	add_filter( 'tcb_allow_central_style_panel', [ 'TCB_Landing_Page', 'tcb_skin_allow_central_style_panel' ] );

	add_filter( 'tcb_has_central_style_panel', [ 'TCB_Landing_Page', 'tcb_skin_allow_central_style_panel' ] );

	add_action( 'tcb_print_custom_style', [ 'TCB_Landing_Page', 'print_skin_typography' ] );

	add_filter( 'tcb_typography_inherit', [ 'TCB_Landing_Page', 'tcb_typography_inherit_selectors_update' ] );

}
