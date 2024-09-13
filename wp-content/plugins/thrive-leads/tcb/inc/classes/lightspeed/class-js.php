<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Lightspeed;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class JS
 *
 * @package TCB\Lightspeed
 */
class JS {
	private static $instances = [];

	private $ID;
	private $key;
	private $modules;

	public static $is_fully_loaded = false;

	public static function get_instance( $post_id = 0, $key = '' ) {
		$instance = $post_id . '_' . $key;

		if ( empty( self::$instances[ $instance ] ) ) {
			self::$instances[ $instance ] = new self( $post_id );
		}

		return self::$instances[ $instance ]->set_key( "_tve_js_modules$key" );
	}

	public function __construct( $post_id ) {
		$this->ID = $post_id;
	}

	/**
	 * Set meta key for reading/writing modules
	 *
	 * @param $key
	 *
	 * @return $this
	 */
	public function set_key( $key ) {
		$this->key = $key;

		return $this;
	}

	/**
	 * Save js modules that are used in this post
	 *
	 * @param array $modules Array of modules
	 */
	public function save_js_modules( $modules = [] ) {
		if ( ! is_array( $modules ) ) {
			$modules = [];
		}

		update_post_meta( $this->ID, $this->key, $modules );
	}

	public function enqueue_scripts() {
		/* enqueue general file with the base functionality */
		tve_enqueue_script( 'tve_frontend', tve_editor_js() . '/modules/general' . \TCB_Utils::get_js_suffix(), [
			'jquery',
			'jquery-masonry',
		] );

		$this->load_modules();
	}

	/**
	 * Load js modules needed for the current post, or all of them if it was not optimized
	 *
	 * @param false $return_inline
	 *
	 * @return string
	 */
	public function load_modules( $return_inline = false ) {
		$js_modules = '';

		$modules_to_load = $this->get_modules_to_load();

		foreach ( $modules_to_load as $module ) {
			$js_modules .= JSModule::get_instance( $module, static::get_module_data( $module, 'libraries' ) )->load( $return_inline );
		}

		do_action( 'tve_lightspeed_enqueue_module_scripts', $this->ID, $modules_to_load );

		return $js_modules;
	}

	/**
	 * Return urls for the modules we have to load
	 *
	 * @return array|array[]
	 */
	public function get_modules_urls() {
		$modules_to_load = $this->get_modules_to_load();

		return JS::get_js_urls( $modules_to_load );
	}

	/**
	 * Return urls for an array of modules
	 *
	 * @return array|array[]
	 */
	public static function get_js_urls( $modules ) {
		$urls = [];
		if ( is_array( $modules ) ) {
			foreach ( $modules as $module ) {
				$urls = array_merge( $urls, static::get_module_data( $module, 'libraries' ) );

				$urls[ $module ] = JSModule::get_instance( $module, [] )->get_url();
			}
		}

		return $urls;
	}

	public function has_optimized_modules() {
		return
			empty( $_GET['force-all-js'] ) &&
			Main::is_enabled() &&
			! is_editor_page_raw() && /* never optimize editor JS */
			metadata_exists( 'post', $this->ID, $this->key ); /* make sure the meta is set */
	}

	public function get_modules_to_load() {
		if ( $this->has_optimized_modules() ) {
			if ( empty( $this->modules ) ) {
				$this->modules = get_post_meta( $this->ID, $this->key, true );

				if ( empty( $this->modules ) || ! is_array( $this->modules ) ) {
					$this->modules = [];
				}

				if ( ! empty( $this->modules ) ) {
					$this->load_module_dependencies();
				}

				$this->include_post_format_modules();
			}
		} else if ( Main::requires_architect_assets( $this->ID ) || Main::has_architect_content( $this->ID ) ) {
			/* load all modules */
			$this->modules = array_keys( static::get_module_data() );

			if ( ! empty( $this->modules ) ) {
				$this->load_module_dependencies();
			}
		} else {
			$this->modules = [];
		}

		return $this->modules;
	}

	/**
	 * Each module can have other modules as dependencies, and through this function they are also added to the module list
	 */
	public function load_module_dependencies() {
		/* load the dependencies for each module */
		foreach ( $this->modules as $module ) {
			$dependencies = static::get_module_data( $module, 'dependencies' );

			foreach ( $dependencies as $dependency ) {
				$this->add_module( $dependency );
			}
		}
	}

	/**
	 * If this post has an audio/video format, add that JS file to the modules that we're loading ( if it's not already included )
	 */
	public function include_post_format_modules() {
		$post_format = get_post_format( $this->ID );

		if ( ! empty( $post_format ) && in_array( $post_format, [ 'audio', 'video' ], true ) ) {
			$this->add_module( $post_format );
		}
	}

	/**
	 * @param $module_to_add
	 */
	public function add_module( $module_to_add ) {
		if ( ! in_array( $module_to_add, $this->modules, true ) ) {
			$this->modules[] = $module_to_add;
		}
	}

	//todo add more modules, including 'general', which depends on buttons, content boxes, etc
	public static function get_module_data( $module = '', $key = '' ) {
		$data = [
			'acf-dynamic-elements'  => [
				/**
				 * '.tcb-custom-field-source' is the general ACF class
				 *  [data-tar-shortcode-attr] is for some frontend json parsing
				 *  a[data-shortcode-id^="acf_"] is for checking ACF shortcode links
				 */
				'identifier' => '.tcb-custom-field-source,[data-tar-shortcode-attr],a[data-shortcode-id^="acf_"]',
			],
			'audio'                 => [
				'identifier' => '.thrv_audio',
			],
			'carousel'              => [
				'identifier' => '[data-type="carousel"]',
				'libraries'  => [
					'carousel-libs' => tve_editor_js( '/carousel-libs' . \TCB_Utils::get_js_suffix() ),
				],
			],
			/* this is the old contact form element which is no longer visible in the sidebar, but still has frontend JS */
			'contact-form-compat'   => [
				'identifier' => '.thrv-contact-form',
			],
			'content-reveal'        => [
				'identifier' => '.thrv_content_reveal',
			],
			'countdown'             => [
				'identifier' => '.tve-countdown',
			],
			'conditional-display'   => [
				'identifier' => '[data-display-group]',
			],
			'search-form'           => [
				'identifier' => '.thrv-search-form',
			],
			'dropdown'              => [
				'identifier' => '.tve_lg_dropdown, .tcb-form-dropdown, .tve-dynamic-dropdown',
			],
			'divider'               => [
				'identifier' => '.thrv-divider',
			],
			'file-upload'           => [
				'identifier' => '.tve_lg_file',
				'libraries'  => [
					'moxie'    => includes_url() . 'js/plupload/moxie.min.js',
					'plupload' => includes_url() . 'js/plupload/plupload.min.js',
				],
			],
			'avatar-picker'         => [
				'identifier'   => '.tve-avatar-picker-element',
				'dependencies' => [ 'google-api', 'facebook-api', 'modal' ],
				'libraries'    => [
					'google-client' => 'https://accounts.google.com/gsi/client',
					'google-api'    => 'https://apis.google.com/js/api.js',
				],
			],
			'fill-counter'          => [
				'identifier' => '.thrv-fill-counter',
			],
			'number-counter'        => [
				'identifier' => '.tve-number-counter',
			],
			'image-gallery'         => [
				'identifier'   => '.tcb-image-gallery',
				'libraries'    => [
					'image-gallery-libs' => tve_editor_js( '/image-gallery-libs.min.js' ),
				],
				'dependencies' => [ 'carousel' ],
			],
			'lead-generation'       => [
				'identifier'   => '.thrv_lead_generation',
				'dependencies' => [ 'dropdown' ],
			],
			'login'                 => [
				'identifier'   => '.thrv-login-element',
				'dependencies' => [ 'lead-generation' ],
			],
			'menu'                  => [
				'identifier' => '.thrv_widget_menu',
			],
			'number-counter-compat' => [
				'identifier' => '.thrv_number_counter',
			],
			'post-grid-compat'      => [
				'identifier' => '.thrv_post_grid',
			],
			'pagination'            => [
				'identifier'   => '.tcb-pagination',
				'dependencies' => [ 'post-list' ],
			],
			'post-list'             => [
				'identifier'   => '.tcb-post-list, .tva-course-list, .thrive-display-testimonials',
				'dependencies' => [ 'post-grid-compat', 'dropdown' ],
			],
			'post-list-filter'      => [
				'identifier' => '.tcb-post-list-filter',
			],
			'pricing-table'         => [
				'identifier' => '.thrv-pricing-table',
			],
			'progress-bar'          => [
				'identifier' => '.tve-progress-bar-wrapper',
			],
			'social-share'          => [
				'identifier' => '.thrv_social_custom,.tve_social_custom',
			],
			'table'                 => [
				'identifier' => '.thrv_table',
			],
			'tabs'                  => [
				'identifier' => '.thrv_tabs_shortcode, .thrv-tabbed-content',
			],
			'timer'                 => [
				'identifier' => '.thrv-countdown_timer_evergreen,.tve_countdown_timer_evergreen,.thrv-countdown_timer_plain,.thrv_countdown_timer',
			],
			'toc'                   => [
				'identifier' => '.tve-toc, .thrv_contents_table', /* thrv_contents_table is the old TOC */
			],
			'toggle'                => [
				'identifier' => '.thrv_toggle, .thrv_toggle_shortcode',
			],
			'twitter'               => [
				'identifier' => '.thrv_tw_qs',
			],
			'user-profile'          => [
				'identifier' => '.tve-user-profile',
			],
			'video'                 => [
				'identifier' => '.thrv_responsive_video, .tcb-responsive-video, .tcb-video-background-el',
			],
		];

		if ( ! empty( $key ) ) {
			$data = array_map( static function ( $item ) use ( $key ) {
				return empty( $item[ $key ] ) ? [] : $item[ $key ];
			}, $data );
		}

		if ( ! empty( $module ) ) {
			$data = isset( $data[ $module ] ) ? $data[ $module ] : [];
		}

		return $data;
	}

	/**
	 * these libraries are enqueued from wordpress, so we don't have to do it ourselves; however, when printing inline, they still have to be printed
	 */
	const LIBRARIES_ENQUEUED_AUTOMATICALLY = [ 'moxie' ];
}
