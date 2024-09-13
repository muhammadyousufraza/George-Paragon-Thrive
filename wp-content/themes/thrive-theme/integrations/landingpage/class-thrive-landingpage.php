<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Landingpage
 */
class Thrive_Landingpage {

	/**
	 * Landing page script identifier
	 */
	const LANDINGPAGE_SCRIPT = 'thrive-landingpage-script';

	/**
	 * Landing page path from integrations
	 */
	const LANDINGPAGE_PATH = THEME_PATH . '/integrations/landingpage';

	/**
	 * Initialize everything for a landing page
	 */
	public static function init() {

		/* Includes */
		require_once __DIR__ . '/class-thrive-landingpage-section.php';

		/* Hooks */
		self::actions();
		self::filters();
	}

	/**
	 * Add actions
	 */
	public static function actions() {
		add_action( 'tcb_main_frame_enqueue', [ __CLASS__, 'tcb_main_frame_enqueue' ], 9 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'inner_frame_enqueue' ] );
		add_action( 'tcb_output_components', [ __CLASS__, 'tcb_output_components' ] );
		add_action( 'tcb_ajax_save_post', [ __CLASS__, 'tcb_ajax_save_post' ], 10, 2 );


	}

	/**
	 * Add filters
	 */
	public static function filters() {
		add_filter( 'tve_main_js_dependencies', [ __CLASS__, 'tve_main_js_dependencies' ] );
		add_filter( 'tcb_element_instances', [ __CLASS__, 'tcb_element_instances' ] );
		add_filter( 'thrive_theme_section_download', [ __CLASS__, 'section_download' ], 10, 2 );
		add_filter( 'thrive_theme_section_template_type', [ __CLASS__, 'section_template_type' ], 10, 2 );
		add_filter( 'tcb_lazy_load_data', [ __CLASS__, 'add_lazy_load_data' ], 10, 2 );
		add_filter( 'thrive_theme_section_object', [ __CLASS__, 'section_object' ], 10, 2 );
		add_filter( 'tcb.landing_page_content', [ __CLASS__, 'tcb_add_sections' ], 10, 2 );

		add_filter( 'tcb_lp_sections_export', [ __CLASS__, 'export_lp_sections' ], 10, 3 );
		add_filter( 'tcb_lp_sections_import', [ __CLASS__, 'import_lp_sections' ], 10, 3 );
	}

	/**
	 * Add css inside the inner frame
	 */
	public static function inner_frame_enqueue() {
		if ( thrive_post()->is_landing_page() ) {
			if ( is_editor_page_raw( true ) ) {
				wp_enqueue_style( 'thrive-theme-landingpage-editor', THEME_ASSETS_URL . '/landingpage-editor.css', [], THEME_VERSION );
			}

			wp_enqueue_style( 'thrive-theme-landingpage-front', THEME_ASSETS_URL . '/landingpage-front.css', [], THEME_VERSION );
		}
	}

	/**
	 * Enqueue assets inside the landing page
	 */
	public static function tcb_main_frame_enqueue() {
		$page = thrive_post();
		if ( $page->is_landing_page() ) {
			tve_dash_enqueue_script( static::LANDINGPAGE_SCRIPT, THEME_ASSETS_URL . '/landingpage.min.js', [
				'jquery',
				'backbone',
				'underscore',
				'jquery-ui-autocomplete',
			], THEME_VERSION, true );

			wp_localize_script( static::LANDINGPAGE_SCRIPT, 'ttb_landingpage_localize', [
				'ID'         => $page->ID,
				'body_class' => $page->body_class(),
			] );

			wp_enqueue_style( 'thrive-theme-landingpage-main', THEME_ASSETS_URL . '/landingpage-main.css', [], THEME_VERSION );
		}
	}

	/**
	 * Set the js file a dependency for the main file so it will load before
	 *
	 * @param array $dependencies
	 *
	 * @return mixed
	 */
	public static function tve_main_js_dependencies( $dependencies ) {
		if ( thrive_post()->is_landing_page() ) {
			$dependencies[] = static::LANDINGPAGE_SCRIPT;
		}

		return $dependencies;
	}

	/**
	 * Add landing page section element
	 *
	 * @param array $instances
	 *
	 * @return array
	 */
	public static function tcb_element_instances( $instances ) {
		if ( thrive_post()->is_landing_page() ) {
			$elements  = Thrive_Architect_Utils::get_architect_theme_elements( static::LANDINGPAGE_PATH . '/elements' );
			$instances = array_merge( $instances, $elements );
		}

		return $instances;
	}

	/**
	 * Adapt the theme section to the landing page section
	 *
	 * @param array          $response
	 * @param Thrive_Section $section
	 *
	 * @return mixed
	 */
	public static function section_download( $response, $section ) {
		$page = thrive_post();

		if ( $page->is_landing_page() ) {

			/**
			 * The downloaded section is unlinked and the css is built with the template class from the section instance.
			 * So, in order to make the replace correctly we need to use the body class from that template
			 */
			$body_class = $section->template->body_class( false, 'string', true );

			$search  = [ $body_class, 'theme-', 'theme_section' ];
			$replace = [ $page->body_class(), 'landingpage-', Thrive_Landingpage_Section::ELEMENT_TYPE ];

			$data     = json_encode( $response );
			$data     = str_replace( $search, $replace, $data );
			$response = json_decode( $data, true );
		}

		return $response;
	}

	/**
	 * Change the type of the sections from the cloud when we are on a landing page.
	 * We will always need the single type
	 *
	 * @param string $template_type
	 *
	 * @return string
	 */
	public static function section_template_type( $template_type ) {
		if ( isset( $_GET['template_id'] ) && thrive_post( $_GET['template_id'] )->is_landing_page() ) {
			$template_type = THRIVE_SINGULAR_TEMPLATE;
		}

		return $template_type;
	}

	/**
	 * Include landing page components
	 */
	public static function tcb_output_components() {
		if ( thrive_post()->is_landing_page() ) {
			$path  = static::LANDINGPAGE_PATH . '/views/components/';
			$files = array_diff( scandir( $path ), [ '.', '..' ] );

			foreach ( $files as $file ) {
				include $path . $file;
			}
		}
	}

	/**
	 * For landing pages we are saving also the extra data from the theme ( for the moment top / bottom sections )
	 *
	 * @param int   $post_id
	 * @param array $request
	 */
	public static function tcb_ajax_save_post( $post_id, $request ) {
		$page = thrive_post( $post_id );

		if ( $page->is_landing_page() && ! empty( $request['theme_extra']['sections'] ) ) {
			$page->set_meta( 'sections', $request['theme_extra']['sections'] );
		}
	}

	/**
	 * Appends all sections needed for a theme template.
	 *
	 * @param array $data
	 * @param int   $post_id
	 *
	 * @return array
	 */
	public static function add_lazy_load_data( $data, $post_id ) {
		if ( thrive_post( $post_id )->is_landing_page() ) {
			$data['theme_sections'] = thrive_skin()->get_sections();
		}

		return $data;
	}

	/**
	 * We need the section object in order to use the one from the landing pages
	 *
	 * @param Thrive_Section $section
	 *
	 * @return Thrive_Landingpage_Section
	 */
	public static function section_object( $section ) {
		if ( thrive_post()->is_landing_page() ) {
			$section = new Thrive_Landingpage_Section( $section->ID );
		}

		return $section;
	}

	/**
	 * We are adding here the top & bottom sections
	 *
	 * @param string $content
	 * @param bool   $is_landing_page
	 *
	 * @return string
	 */
	public static function tcb_add_sections( $content, $is_landing_page ) {
		if ( $is_landing_page ) {
			$post = thrive_post( get_the_ID() );
			/* make sure the correct post is loaded */
			$top    = $post->get_section_content( 'top' );
			$bottom = $post->get_section_content( 'bottom' );

			$content = $top . $content . $bottom;
		}

		return $content;
	}

	/**
	 * Export theme sections from a landing page
	 *
	 * @param array                     $sections
	 * @param int                       $page_id
	 * @param TCB_Landing_Page_Transfer $export_instance
	 *
	 * @return array
	 */
	public static function export_lp_sections( $sections, $page_id, $export_instance ) {

		$theme_sections = get_post_meta( $page_id, 'sections', true );

		if ( ! empty( $theme_sections ) && is_array( $theme_sections ) ) {
			foreach ( $theme_sections as $type => $section ) {
				if ( isset( $section['id'] ) ) {

					if ( empty( $section['id'] ) ) {
						$export_instance->exportParseImages( $section['content'] );
						$section['content'] = $export_instance::replace_ids_on_export( $section['content'] );
						$export_instance->parse_content_symbols( $section['content'] );
					} else {
						$theme_section = new Thrive_Landingpage_Section( $section['id'] );
						$export_data   = $theme_section->export();

						$export_instance->parse_content_symbols( $export_data['meta_input']['content'] );

						$export_data = json_encode( $export_data );
						add_filter( 'tve_should_minify_css', '__return_false' );
						$export_data = tve_prepare_global_variables_for_front( $export_data, true );
						remove_filter( 'tve_should_minify_css', '__return_false' );
						$export_instance->exportParseImages( $export_data );
						$export_data = $export_instance::replace_ids_on_export( $export_data );

						$section['id'] = json_decode( $export_data, true );
					}

					$sections[ $type ] = $section;
				}
			}
		}

		return $sections;
	}

	/**
	 * Import top and bottom sections if present in the archive config
	 *
	 * @param array                     $config
	 * @param int                       $page_id
	 * @param TCB_Landing_Page_Transfer $import_instance
	 *
	 * @return array
	 */
	public static function import_lp_sections( $config, $page_id, $import_instance ) {
		$sections = [];

		foreach ( [ 'top', 'bottom' ] as $type ) {
			if ( ! empty( $config['sections'][ $type ] ) ) {

				$section = json_encode( $config['sections'][ $type ] );
				$section = $import_instance->replace_image_hash( $section );
				$section = $import_instance->replace_content_symbols_on_insert( $section );
				$section = json_decode( $section, true );

				/**
				 * Numeric $section['id'] means that it was already imported & handled
				 */
				if ( ! empty( $section['id'] ) && ! is_numeric( $section['id'] ) ) {
					if ( empty( $GLOBALS['tcb_symbol_map'][ $section['id']['ID'] ] ) ) {
						$old_section_id = $section['id']['ID'];
						/* create a new theme section */
						$section['id']                                = Thrive_Landingpage_Section::import( $section['id'] );
						$GLOBALS['tcb_symbol_map'][ $old_section_id ] = $section['id'];
					} else {
						$section['id'] = $GLOBALS['tcb_symbol_map'][ $section['id']['ID'] ];
					}
				}

				$sections[ $type ]           = $section;
				$config['sections'][ $type ] = array_merge( $config['sections'][ $type ], $section );
			}
		}

		update_post_meta( $page_id, 'sections', $sections );


		/* update css selectors from the old page id to the new one */
		$head_css = tve_get_post_meta( $page_id, 'tve_custom_css' );
		$head_css = preg_replace( '/\.page-id-\d*/m', ".page-id-$page_id", $head_css );
		tve_update_post_meta( $page_id, 'tve_custom_css', $head_css );

		return $config;
	}
}
