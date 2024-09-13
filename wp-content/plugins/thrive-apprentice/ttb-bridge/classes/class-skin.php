<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TTB;

use Exception;
use JsonSerializable;
use ReturnTypeWillChange;
use Thrive_Skin;
use Thrive_Theme;
use Thrive_Theme_Cloud_Api_Factory;
use Thrive_Typography;
use TVA_Const;
use TVA_Course_Completed;
use WP_Query;
use WP_REST_Request;
use WP_REST_Server;
use WP_Term;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Skin
 *
 * @package TVA\TTB
 *
 * @property-read string $tag                tag
 * @property-read string $thumb              thumbnail URL
 * @property-read string $name               term name
 * @property-read string $description        term description
 * @property-read int    $term_id            ID of term
 * @property-read int    $logo               attachment ID of the logo selected for this skin
 * @property-read bool   $inherit_typography whether or not to inherit the typography from the theme
 *
 */
class Skin extends Thrive_Skin implements JsonSerializable {
	protected static $_instances = [];

	/**
	 * Cache for template data
	 *
	 * @var array
	 */
	protected static $_cache_templates = [
		'no_access'                     => null,
		'complete_post'                 => null,
		'certificate_verification_post' => null,
		'assessment_post'               => null,
	];

	/**
	 * Modified from Thrive_Skin
	 * Contains prefix for style file
	 *
	 * @var string
	 */
	protected $style_file_prefix = 'apprentice-template';

	/**
	 * Override the option from the Theme Skin
	 *
	 * @return string|void
	 */
	public function get_template_style_option_name() {
		return 'thrive_apprentice_template_style';
	}

	/**
	 * General singleton implementation for class instance that also requires an id
	 *
	 * @param int $id
	 *
	 * @return static
	 */
	public static function instance_with_id( $id = 0 ) {
		if ( ! isset( static::$_instances[ $id ] ) ) {
			static::$_instances[ $id ] = new static( $id );
		}

		return static::$_instances[ $id ];
	}

	/**
	 * Thrive_Skin constructor. Modified to also support WP_Term parameter
	 *
	 * @param int|string|WP_Term $skin
	 */
	public function __construct( $skin ) {
		if ( $skin instanceof WP_Term ) {
			$skin = $skin->term_id;
		}
		parent::__construct( $skin );
	}

	/**
	 * Serialization needed for admin CRUD
	 *
	 * @return array|mixed
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		$default_skin_id = Main::get_default_skin_id();
		$data            = $this->term->to_array();

		return array_merge( $data, [
			'is_active'           => $default_skin_id === $this->term->term_id,
			'is_assessment_ready' => (int) $this->is_assessment_ready(),
			'legacy'              => 0,
			'thumb'               => $this->thumb,
			'templates'           => Skin_Template::localize_all( $this->term->term_id ),
			'inherit_typography'  => (bool) $this->inherit_typography,
			'typography'          => $this->get_active_typography( 'array' ),
		] );
	}

	/**
	 * Magic (meta) getter. Adds a tva_ prefix for the meta key before querying
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {

		if ( method_exists( $this, 'get_' . $name ) ) {
			$method_name = 'get_' . $name;

			return $this->$method_name();
		}

		if ( isset( $this->term->$name ) ) {
			return $this->term->$name;
		}

		$name = 'tva_' . str_replace( 'tva_', '', $name );

		return $this->get_meta( $name );
	}

	/**
	 * Clone the current skin
	 *
	 * @return int the new skin ID
	 */
	public function duplicate() {
		$name     = 'Copy of ' . $this->name;
		$new_skin = new static( Main::create_skin( $name, null, false ) );

		/* duplicate the meta fields from the source to the new skin */
		$new_skin->duplicate_meta( $this->ID );

		/* create templates */
		Default_Data::create_skin_templates( $new_skin->ID, $this->ID );

		/* create default typography */
		Default_Data::create_skin_typographies( $new_skin->ID, $this->ID );

		return $new_skin->ID;
	}

	/**
	 * Get all the meta fields for a skin
	 *
	 * @return array
	 */
	public static function meta_fields() {
		return parent::meta_fields() + [
				'thrive_scope'           => 'tva',
				'tva_thumb'              => '',
				'tva_inherit_typography' => true,
			];
	}

	/**
	 * Ensure that this skin has the correct `thrive_scope` meta assigned
	 */
	public function ensure_scope() {
		update_term_meta( $this->ID, 'thrive_scope', 'tva' );
	}

	/**
	 * Set skin palettes
	 *
	 * @param array $palettes
	 *
	 * @return Skin
	 */
	public function set_palettes( $palettes ) {
		$this->set_meta( static::SKIN_META_PALETTES_V2, $palettes );

		return $this;
	}

	/**
	 * Check if a skin has TVA scope
	 *
	 * @param $skin_id
	 *
	 * @return bool
	 */
	public static function has_tva_scope( $skin_id ) {
		return get_term_meta( $skin_id, 'thrive_scope', true ) === 'tva';
	}

	/**
	 * This function should be overridden with empty content because we do not want the normalize logic for skin to be applied also here
	 */
	public function normalize_palettes() {
		//Nothing should happen here
	}

	/**
	 * We always return the template string here
	 *
	 * Overrides the Builder Skin function
	 *
	 * @param array  $templates
	 * @param string $type
	 *
	 * @return array|mixed
	 */
	public function filter_templates( $templates, $type = '' ) {
		return $templates;
	}

	/**
	 * @return string
	 */
	public function css( $include_theme_master = false ) {

		$data = '';

		if ( empty( tva_palettes()->get_master_hsl() ) ) {
			tva_palettes()->reset_master_hsl();
		}

		if ( tva_palettes()->has_palettes() && ! empty( $this->get_meta( static::SKIN_META_PALETTES_V2 ) ) ) {

			$palette = tva_palettes()->get_palette();

			foreach ( $palette as $variable_id => $variable ) {

				$color_name = '--tva-skin-color-' . $variable['id'];
				if ( ! empty( $variable['hsla_code'] ) && ! empty( $variable['hsla_vars'] ) && is_array( $variable['hsla_vars'] ) ) {
					$data .= $color_name . ':' . $variable['hsla_code'] . ';';

					foreach ( $variable['hsla_vars'] as $var => $css_variable ) {
						$data .= $color_name . '-' . $var . ':' . $css_variable . ';';
					}
				} else {
					$data .= $color_name . ':' . $variable['color'] . ';';

					if ( function_exists( 'tve_rgb2hsl' ) && function_exists( 'tve_print_color_hsl' ) ) {
						$data .= tve_print_color_hsl( $color_name, tve_rgb2hsl( $variable['color'] ) );
					}
				}
			}
		}

		if ( $include_theme_master && Thrive_Theme::is_active() ) {
			$theme_master_variable = thrive_palettes()->get_master_hsl();

			$data .= str_replace( '--tcb-main-master', '--tcb-theme-main-master', tve_prepare_master_variable( array( 'hsl' => $theme_master_variable ) ) );
		}

		$share_ttb_color = tva_get_settings_manager()->factory( 'share_ttb_color' )->get_value();
		if ( ! empty( $share_ttb_color ) ) {
			$master_variable = [
				'h' => 'var(--tcb-theme-main-master-h)',
				's' => 'var(--tcb-theme-main-master-s)',
				'l' => 'var(--tcb-theme-main-master-l)',
				'a' => 'var(--tcb-theme-main-master-a)',
			];
		} else {
			$master_variable = tva_palettes()->get_master_hsl();
		}

		$general_master_variable = tve_prepare_master_variable( array( 'hsl' => $master_variable ) );
		$ta_master_variable      = str_replace( '--tcb-main-master', '--tva-main-master', $general_master_variable );

		$data .= $general_master_variable;
		$data .= $ta_master_variable;

		return $data;
	}

	/**
	 * Get an array of Skin template objects that match primary, secondary and variable template fields
	 *
	 * @param string|null $primary
	 * @param string|null $secondary
	 * @param string|null $variable
	 * @param array       $query_args extra query args
	 *
	 * @return Skin_Template[]
	 */
	public function get_templates_by_type( $primary = null, $secondary = null, $variable = null, $query_args = [] ) {
		$filters = array_filter( compact( 'primary', 'secondary', 'variable' ) );

		$args = [
			'post_type'      => THRIVE_TEMPLATE,
			'posts_per_page' => - 1,
			'tax_query'      => [ $this->build_skin_query_params() ],
		];

		if ( $filters ) {
			$args['meta_query'] = [
				'relation' => 'AND',
			];
			foreach ( $filters as $meta_field => $meta_value ) {
				$args['meta_query'][] = [
					'key'   => "{$meta_field}_template",
					'value' => $meta_value,
				];
			}
		}
		if ( isset( $query_args['default'] ) ) {
			$args['meta_query'] [] = [
				'key'   => 'default',
				'value' => (int) $query_args['default'],
			];
		}
		if ( isset( $query_args['format'] ) ) {
			$args['meta_query'] [] = [
				'key'   => 'format',
				'value' => $query_args['format'],
			];
		}

		return array_map( static function ( $post ) {
			return new Skin_Template( $post->ID );
		}, get_posts( $args ) );
	}

	/**
	 * Get a default template for the $content_type parameter
	 *
	 * @param string $content_type
	 *
	 * @return Skin_Template|null
	 */
	public function get_default_template( $content_type = 'lesson' ) {
		$primary   = THRIVE_SINGULAR_TEMPLATE;
		$secondary = null;
		$variable  = null;
		$params    = [ 'default' => 1 ];

		switch ( $content_type ) {
			case 'lesson':
				$secondary        = TVA_Const::LESSON_POST_TYPE;
				$params['format'] = 'standard';
				break;
			case 'module':
				$secondary = TVA_Const::MODULE_POST_TYPE;
				break;
			case 'school':
				$primary   = THRIVE_HOMEPAGE_TEMPLATE;
				$secondary = TVA_Const::COURSE_POST_TYPE;
				break;
			case 'course':
				$primary   = THRIVE_ARCHIVE_TEMPLATE;
				$secondary = TVA_Const::COURSE_TAXONOMY;
				break;
			case TVA_Const::CERTIFICATE_VALIDATION_POST:
				$secondary = TVA_Const::CERTIFICATE_VALIDATION_POST;
				break;
		}
		$templates = $this->get_templates_by_type( $primary, $secondary, $variable, $params );

		if ( empty( $templates ) ) {
			$templates = $this->get_templates_by_type( $primary, $secondary, $variable );
		}

		return isset( $templates[0] ) ? $templates[0] : null;
	}

	/**
	 * Get the skin typography - always return the first typography - TA skins have only one typography
	 *
	 * @param string $output
	 *
	 * @return mixed
	 */
	public function get_active_typography( $output = 'ids' ) {
		/* also allow singular */
		if ( $output === 'id' ) {
			$output = 'ids';
		} elseif ( $output === 'object' ) {
			$output = 'objects';
		}
		$typographies = $this->get_typographies( $output );

		switch ( $output ) {
			case 'ids':
				$default = 0;
				break;
			case 'array':
				$default = [];
				break;
			case 'objects':
			default:
				$default = new Thrive_Typography( 0 );
				break;
		}

		return isset( $typographies[0] ) ? $typographies[0] : $default;
	}

	/**
	 * Getter / setter for inherit_typography field
	 *
	 * @param null $value
	 *
	 * @return mixed
	 */
	public function inherit_typography( $value = null ) {
		if ( $value === null ) {
			return $this->inherit_typography;
		}

		update_term_meta( $this->ID, 'tva_inherit_typography', (bool) $value );
	}

	/**
	 * Returns the skin thumb
	 *
	 * @return string
	 */
	private function get_thumb() {

		/**
		 * Allow custom thumb location for skins. If any of the filter implementations return a non-empty string, it will be used as thumb url
		 *
		 * @param string $thumb_url
		 * @param Skin   $skin skin instance
		 */
		$thumb_url = apply_filters( 'tva_skin_thumb_url', '', $this );

		if ( $thumb_url !== '' ) {
			return $thumb_url;
		}

		$host = '//landingpages.thrivethemes.com';

		if ( defined( 'TCB_CLOUD_API_LOCAL' ) ) {
			$host = str_replace( '/cloud-api/index-api.php', '', TCB_CLOUD_API_LOCAL );
		}

		$thumb = (string) $this->get_meta( 'tva_thumb' );

		return rtrim( $host, '/' ) . '/data/skins/thumbnails/' . ( $thumb ? $thumb : 'thumb-' . $this->tag . '.png' );
	}

	/**
	 * Mark this skin as active and deactivate the currently active skin
	 *
	 * @return Skin
	 */
	public function activate() {
		Main::set_default_skin_id( $this->ID );
		Main::set_use_builder_templates();
		/* make sure the "load_scripts" setting is turned ON */
		tva_get_settings_manager()->save_setting( 'load_scripts', 1 );

		return $this;
	}

	/**
	 * Checks if the skin is valid for localization
	 * Contains logic that was added after the visual builder release
	 *
	 * Ex: auto-downloads General No Access template if it doesn't exist
	 *
	 * @throws Exception
	 */
	public function sanity_check( $check = [] ) {

		if ( empty( $check['assessment_demo'] ) ) {
			$check['assessment_demo'] = 1;
			$check_assessment_demo    = $this->check_for_demo( TVA_Const::ASSESSMENT_POST_TYPE );

			if ( $check_assessment_demo->found_posts === 0 ) {
				$this->add_demo_content( [
					'name'        => 'Thrive Themes - Assessment course completion',
					'assessments' => [
						[
							'args'                => [
								'post_title'   => __( 'Assessment: Two Scarcity Marketing Recipes', 'thrive-apprentice' ),
								'post_type'    => TVA_Const::ASSESSMENT_POST_TYPE,
								'post_excerpt' => __( 'Examinations, finals, quizzes, and graded papers are examples of summative assessments that test student knowledge of a given topic or subject. These graded assessments and assignments are often high stakes and are geared towards testing students.', 'thrive-apprentice' ),
								'post_content' => __( 'Lorem Ipsum este pur și simplu un text fals al industriei de tipărire și de tipărire. Lorem Ipsum a fost textul fals standard al industriei încă din anii 1500, când o imprimantă necunoscută a luat o bucătărie de tipărire și a amestecat-o pentru a face o carte cu specimene de tipar. A supraviețuit nu numai cinci secole, ci și saltului în compunerea electronică, rămânând în esență neschimbat. A fost popularizat în anii 1960 odată cu lansarea foilor Letraset care conțin pasaje Lorem Ipsum și, mai recent, cu software-ul de publicare desktop precum Aldus PageMaker, inclusiv versiuni de Lorem Ipsum.', 'thrive-apprentice' ),
								'post_status'  => 'publish',
							],
							'tva_assessment_type' => 'youtube_link',
						],
					],
				] );
			}
		}

		if ( empty( $check['assessments_tpl'] ) ) {
			$check['assessments_tpl'] = 1;
			$check_assessments_tpl    = $this->check_for_template( TVA_Const::ASSESSMENT_POST_TYPE );

			if ( $check_assessments_tpl->found_posts === 0 ) {
				$this->maybe_auto_download_template( 'Assessment', TVA_Const::ASSESSMENT_POST_TYPE, static::$_cache_templates['assessment_post'] );
			}
		}

		if ( empty( $check['completed_course_demo'] ) ) {
			$check['completed_course_demo'] = 1;
			$check_completed_course_demo    = $this->check_for_demo( TVA_Course_Completed::POST_TYPE );

			if ( $check_completed_course_demo->found_posts === 0 ) {
				$this->add_demo_content( [
					'name'      => 'Thrive Themes - Demo course completion',
					'completed' => array(
						array(
							'args' => array(
								'post_title'   => __( 'Congratulations!', 'thrive-apprentice' ),
								'post_type'    => TVA_Course_Completed::POST_TYPE,
								'post_excerpt' => __( 'It is indeed great news as you have completed the course in a short amount of time. I have seen you study all day and be hard on yourself for so many days. I really hoped that your hard work would pay off, and I am thrilled as God has granted my wish. It\'s hard work and dedication that have impacted your result.', 'thrive-apprentice' ),
								'post_content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus tristique ligula sit amet nulla eleifend, nec imperdiet ante semper. Nulla lobortis urna ac massa sagittis, a posuere ipsum commodo. Sed at nisl urna. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Pellentesque laoreet hendrerit metus, at fermentum dui elementum eget. Fusce porta tincidunt nisl id viverra. Nunc accumsan lectus eget nunc ultricies, vestibulum commodo purus suscipit. Quisque vestibulum elit ullamcorper, scelerisque velit nec, rutrum mi. In ac ligula at odio malesuada tristique et ac mauris. Morbi sed tristique lectus.',
								'post_status'  => 'publish',
							),
						),
					),
				] );
			}
		}

		if ( empty( $check['completed_course_tpl'] ) ) {
			$check['completed_course_tpl'] = 1;
			$check_completed_course_tpl    = $this->check_for_template( TVA_Course_Completed::POST_TYPE );

			if ( $check_completed_course_tpl->found_posts === 0 ) {
				$this->maybe_auto_download_template( 'Course Completion', TVA_Course_Completed::POST_TYPE, static::$_cache_templates['complete_post'] );
			}
		}

		if ( empty( $check['general_no_access_tpl'] ) ) {
			$check['general_no_access_tpl'] = 1;
			$check_general_no_access        = $this->check_for_template( TVA_Const::NO_ACCESS_POST );

			if ( $check_general_no_access->found_posts === 0 ) {
				$this->maybe_auto_download_template( 'Restricted Site Content', TVA_Const::NO_ACCESS_POST, static::$_cache_templates['no_access'] );
			}
		}
		if ( empty( $check['verification_page_tpl'] ) ) {
			$check['verification_page_tpl'] = 1;
			$check_verification_page_tpl    = $this->check_for_template( TVA_Const::CERTIFICATE_VALIDATION_POST );

			if ( $check_verification_page_tpl->found_posts === 0 ) {
				$this->maybe_auto_download_template( 'Certificate verification', TVA_Const::CERTIFICATE_VALIDATION_POST, static::$_cache_templates['certificate_verification_post'] );
			}
		}

		return $check;
	}

	/**
	 * Checks if the skin is ready for the assessment feature
	 *
	 * @return bool
	 */
	private function is_assessment_ready() {

		/**
		 * For performance, we do this only for the active skin
		 */
		if ( $this->ID !== Main::get_default_skin_id() ) {
			//We do this check only for default skin ID
			//Only the default skin is required here since we take templates only from the default skin
			return true;
		}

		$is_rdy = (int) $this->get_meta( 'tva_is_assessment_ready', 0 );

		if ( $is_rdy ) {
			return true;
		}

		$is_rdy = true;
		$config = [
			'lesson'    => [ 'primary' => THRIVE_SINGULAR_TEMPLATE, 'secondary' => TVA_Const::LESSON_POST_TYPE ],
			'module'    => [ 'primary' => THRIVE_SINGULAR_TEMPLATE, 'secondary' => TVA_Const::MODULE_POST_TYPE ],
			'completed' => [ 'primary' => THRIVE_SINGULAR_TEMPLATE, 'secondary' => TVA_Course_Completed::POST_TYPE ],
			'overview'  => [ 'primary' => THRIVE_ARCHIVE_TEMPLATE, 'secondary' => TVA_Const::COURSE_TAXONOMY ],
			'no_access' => [ 'primary' => THRIVE_SINGULAR_TEMPLATE, 'secondary' => TVA_Const::NO_ACCESS ],
		];

		foreach ( $config as $key => $data ) {
			$templates = $this->get_templates_by_type( $data['primary'], $data['secondary'], null, [ 'default' => 1 ] );
			$template  = reset( $templates );

			if ( ! $template->is_assessment_ready() ) {
				$is_rdy = false;
				break;
			}
		}

		if ( $is_rdy ) {
			$this->set_meta( 'tva_is_assessment_ready', 1 );
		}

		return $is_rdy;
	}

	/**
	 * Add demo content
	 *
	 * @param $args
	 *
	 * @return void
	 */
	private function add_demo_content( $args ) {
		$args = array_merge(
			[
				'args'         => array(
					'description' => __( 'Learn the most reliable method we\'ve ever found, to increase conversion rates.', 'thrive-apprentice' ),
				),
				'cover_image'  => TVA_Const::plugin_url( 'img/default_cover.png' ),
				'term_media'   => [
					'options' => [],
					'source'  => 'https://www.youtube.com/watch?v=P0UogSxdt74',
					'type'    => 'youtube',
				],
				'video_status' => 1,
				'level'        => 0,
				'logged_in'    => 1,
				'roles'        => [],
				'topic'        => 0,
				'status'       => 'private',
				'order'        => 4,
			],
			$args );
		tva_add_course( $args );
	}

	/**
	 * Check for demo content with post type
	 * Used in sanity check function
	 *
	 * @param string $post_type
	 *
	 * @return WP_Query
	 */
	private function check_for_demo( $post_type ) {
		return new WP_Query( [
			'post_type'  => $post_type,
			'meta_query' => [
				[
					'key'     => 'tva_is_demo',
					'value'   => 1,
					'compare' => '=',
				],
			],
		] );
	}

	/**
	 * Check for template
	 * Used in sanity check function
	 *
	 * @param string $secondary
	 *
	 * @return WP_Query
	 */
	private function check_for_template( $secondary ) {
		return new WP_Query( [
			'posts_per_page' => 1, //Limit 1 because we only need to know if exists or not
			'post_type'      => THRIVE_TEMPLATE,
			'tax_query'      => [ $this->build_skin_query_params() ],
			'meta_query'     => [
				'relation' => 'AND',
				[
					'key'     => THRIVE_PRIMARY_TEMPLATE,
					'value'   => THRIVE_SINGULAR_TEMPLATE,
					'compare' => '=',
				],
				[
					'key'     => THRIVE_SECONDARY_TEMPLATE,
					'value'   => $secondary,
					'compare' => '=',
				],
			],
		] );
	}

	/**
	 * Maybe auto download template
	 *
	 * @param string     $template_title
	 * @param string     $secondary
	 * @param null|array $maybe_cache
	 *
	 * @return false|void
	 * @throws Exception
	 */
	private function maybe_auto_download_template( $template_title, $secondary, &$maybe_cache ) {
		if ( $maybe_cache === null ) {
			$maybe_cache = Thrive_Theme_Cloud_Api_Factory::build( 'templates' )->get_items( [
				'filters' => [
					'primary'   => THRIVE_SINGULAR_TEMPLATE,
					'secondary' => $secondary,
				],
			] );
		}

		if ( is_array( $maybe_cache ) && ! empty( $maybe_cache ) ) {
			$current = reset( $maybe_cache );

			if ( ! empty( $current['id'] ) ) {
				/**
				 * Fix issues for downloading the general no access templates and assigning it to the skin
				 */
				$_REQUEST['tva_skin_id'] = $this->ID;

				$request = new WP_REST_Request( WP_REST_Server::CREATABLE, '/tva/v1/templates' );
				$request->set_header( 'content-type', 'application/json' );
				$request->set_body_params( [
					'tva_skin_id'  => $this->ID,
					'post_title'   => $template_title,
					'inherit_from' => $current['id'],
					'meta_input'   => [
						THRIVE_PRIMARY_TEMPLATE   => THRIVE_SINGULAR_TEMPLATE,
						THRIVE_SECONDARY_TEMPLATE => $secondary,
					],
				] );

				try {
					rest_get_server()->dispatch( $request );
				} catch ( Exception $exception ) {
					//do nothing
					return false;
				}
			}
		}
	}
}
