<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVA_Settings We're creating a singleton here, as we're having tons of issues with calls to these settings
 * (we call hundreds of times the same settings)
 *
 * @deprecated
 */
class TVA_Settings {

	/**
	 * @var null
	 */
	private static $instance = null;
	/**
	 * @var array|mixed|void
	 */
	private $settings = array();

	/**
	 * @var WP_Post
	 */
	protected $checkout_page_post;

	protected $_defaults
		= array(
			'template' => array(
				'collapse_modules'  => false,
				'collapse_chapters' => false,
			),
		);

	/**
	 * Name for login page option
	 */
	const LOGIN_PAGE = 'tva_login_page';

	/**
	 * Holds a minimal representation of a page used by TA
	 *
	 * @var array
	 */
	public static $default_page
		= array(
			'ID'   => '',
			'name' => '',
		);

	/**
	 * TVA_Settings constructor.
	 */
	private function __construct() {
	}

	/**
	 * "Refactored" this out of the constructor call because it calls the constructor again from tva_get_preview_url()
	 */
	public function init_all() {

		global $is_thrive_theme;
		if ( tva_is_inner_frame() && isset( $_REQUEST['tva_advanced'] ) ) {

			$this->settings = get_option( 'tva_template_advanced_settings', array() );

			return;
		}

		$reg_page_option = get_option( 'tva_default_register_page' );
		$this->settings  = get_option( 'tva_template_general_settings', array() );
		$theme_options   = get_option( 'thrive_theme_options' );

		/**
		 * In case available settings aren't valid we will start with deafult settings
		 */
		if ( ! $this->validate_settings() ) {
			delete_option( 'tva_template_general_settings' );
			$this->settings = array();
		}

		/**
		 * check if we have the old settings
		 */
		if ( empty( $this->settings ) ) {
			$this->settings                                            = include dirname( dirname( dirname( __FILE__ ) ) ) . '/templates/template_1/data.php';
			$this->settings['template']['page_headline_text']          = TVA_Const::TVA_ABOUT;
			$this->settings['template']['start_course']                = TVA_Const::TVA_START;
			$this->settings['template']['course_type_guide']           = TVA_Const::TVA_COURSE_TYPE_GUIDE;
			$this->settings['template']['course_type_audio']           = TVA_Const::TVA_COURSE_TYPE_AUDIO;
			$this->settings['template']['course_type_video']           = TVA_Const::TVA_COURSE_TYPE_VIDEO;
			$this->settings['template']['course_type_text']            = TVA_Const::TVA_COURSE_TYPE_TEXT;
			$this->settings['template']['course_type_video_text_mix']  = TVA_Const::TVA_COURSE_TYPE_VIDEO_TEXT_MIX;
			$this->settings['template']['course_type_big_mix']         = TVA_Const::TVA_COURSE_TYPE_BIG_MIX;
			$this->settings['template']['course_type_audio_text_mix']  = TVA_Const::TVA_COURSE_TYPE_AUDIO_TEXT_MIX;
			$this->settings['template']['course_type_video_audio_mix'] = TVA_Const::TVA_COURSE_TYPE_VIDEO_AUDIO_MIX;
			$this->settings['template']['course_lessons_plural']       = TVA_Const::TVA_COURSE_LESSONS_TEXT;
			$this->settings['template']['course_lessons']              = TVA_Const::TVA_COURSE_LESSONS_TEXT;
			$this->settings['template']['course_chapters']             = TVA_Const::TVA_COURSE_CHAPTERS_TEXT;
			$this->settings['template']['course_modules']              = TVA_Const::TVA_COURSE_MODULES_TEXT;
			$this->settings['template']['course_lesson']               = TVA_Const::TVA_COURSE_LESSON_TEXT;
			$this->settings['template']['course_chapter']              = TVA_Const::TVA_COURSE_CHAPTER_TEXT;
			$this->settings['template']['course_module']               = TVA_Const::TVA_COURSE_MODULE_TEXT;
			$this->settings['template']['course_more_details']         = TVA_Const::TVA_COURSE_DETAILS_TEXT;
			$this->settings['template']['course_more_read']            = TVA_Const::TVA_COURSE_READ_TEXT;
			$this->settings['template']['members_only']                = TVA_Const::TVA_COURSE_MEMBERS_ONLY;
			$this->settings['template']['view_lesson']                 = TVA_Const::TVA_COURSE_VIEW_LESSON;
			$this->settings['template']['logo_type']                   = false;
			$this->settings['template']['progress_bar']                = TVA_Const::TVA_SHORTCODE_PROGRESS;
			$this->settings['template']['progress_bar_finished']       = TVA_Const::TVA_SHORTCODE_PROGRESS_FINISHED;
			$this->settings['template']['progress_bar_not_started']    = TVA_Const::TVA_SHORTCODE_PROGRESS_NOT_STARTED;
			$this->settings['template']['author']                      = TVA_Const::TVA_SHORTCODE_AUTHOR;
			$this->settings['template']['lesson_list']                 = TVA_Const::TVA_SHORTCODE_LESSONS;
			$this->settings['template']['lesson_list_progress']        = TVA_Const::TVA_SHORTCODE_LESSONS_PROGRESS;
			$this->settings['template']['lesson_list_not_viewed']      = TVA_Const::TVA_SHORTCODE_LESSONS_NOT_VIEWED;
			$this->settings['template']['lesson_list_completed']       = TVA_Const::TVA_SHORTCODE_LESSONS_COMPLETED;
			$this->settings['template']['next_lesson']                 = TVA_Const::TVA_NEXT_LESSON;
			$this->settings['template']['prev_lesson']                 = TVA_Const::TVA_PREV_LESSON;
			$this->settings['template']['to_course_page']              = TVA_Const::TVA_TO_COURSE_PAGE;
			$this->settings['template']['to_completion_page']          = TVA_Const::TVA_TO_COMPLETION_PAGE;
			$this->settings['template']['course_structure']            = TVA_Const::TVA_COURSE_STRUCTURE;
			$this->settings['template']['search_text']                 = TVA_Const::TVA_SEARCH_TEXT;

			$this->settings['per_page'] = TVA_Const::DEFAULT_COURSES_PER_PAGE;
			if ( ! empty( $reg_page_option ) ) {
				$this->settings['register_page'] = array( 'name' => $reg_page_option['name'], 'ID' => $reg_page_option['ID'] );
			} else {
				$this->settings['register_page'] = [
					'name' => '',
					'ID'   => 0,
				];
			}
			$this->settings['index_page']       = get_option( 'tva_provisional_index_page', array( 'name' => '', 'ID' => '' ) );
			$this->settings['load_scripts']     = get_option( 'tva_load_all_scripts', false );
			$this->settings['auto_login']       = get_option( 'tva_auto_login', true );
			$this->settings['apprentice_label'] = true;
			$this->settings['comment_status']   = TVA_Const::TVA_DEFAULT_COMMENT_STATUS;

			update_option( 'tva_template_general_settings', $this->settings );
		} else {
			/**
			 * Update late added settings
			 */
			$updated = get_option( 'tva_updated_settings' );

			if ( ! $updated ) {
				$this->settings['template']['course_type_big_mix']        = TVA_Const::TVA_COURSE_TYPE_BIG_MIX;
				$this->settings['template']['course_type_audio_text_mix'] = TVA_Const::TVA_COURSE_TYPE_AUDIO_TEXT_MIX;

				$this->settings['template']['course_type_video_text_mix'] = ! empty( $this->settings['template']['course_type_mix'] )
					? $this->settings['template']['course_type_mix'] : TVA_Const::TVA_COURSE_TYPE_VIDEO_TEXT_MIX;

				$this->settings['template']['course_type_video_audio_mix'] = TVA_Const::TVA_COURSE_TYPE_VIDEO_AUDIO_MIX;
				$this->settings['template']['course_type_audio']           = TVA_Const::TVA_COURSE_TYPE_AUDIO;
				$this->settings['template']['course_chapters']             = TVA_Const::TVA_COURSE_CHAPTERS_TEXT;
				$this->settings['template']['course_modules']              = TVA_Const::TVA_COURSE_MODULES_TEXT;
				$this->settings['template']['course_lesson']               = TVA_Const::TVA_COURSE_LESSON_TEXT;
				$this->settings['template']['course_chapter']              = TVA_Const::TVA_COURSE_CHAPTER_TEXT;
				$this->settings['template']['course_module']               = TVA_Const::TVA_COURSE_MODULE_TEXT;
				$this->settings['template']['course_structure']            = TVA_Const::TVA_COURSE_STRUCTURE;
				$this->settings['template']['lesson_headline']             = 24;
				$this->settings['template']['chapter_headline']            = 24;
				$this->settings['template']['module_headline']             = 24;

				update_option( 'tva_template_general_settings', $this->settings );
				update_option( 'tva_updated_settings', true );
			}

			$updated_next = get_option( 'tva_updated_next_prev_labels' );

			if ( ! $updated_next ) {
				$this->settings['template']['next_lesson']        = TVA_Const::TVA_NEXT_LESSON;
				$this->settings['template']['prev_lesson']        = TVA_Const::TVA_PREV_LESSON;
				$this->settings['template']['to_course_page']     = TVA_Const::TVA_TO_COURSE_PAGE;
				$this->settings['template']['to_completion_page'] = TVA_Const::TVA_TO_COMPLETION_PAGE;

				update_option( 'tva_updated_next_prev_labels', true );
				update_option( 'tva_template_general_settings', $this->settings );
			}

			$update_search = get_option( 'tva_search_option_updated' );

			if ( ! $update_search ) {
				$this->settings['template']['search_text'] = TVA_Const::TVA_SEARCH_TEXT;

				update_option( 'tva_template_general_settings', $this->settings );
				update_option( 'tva_search_option_updated', true );
			}

			$this->settings['template']['course_lessons_plural'] = isset( $this->settings['template']['course_lessons_plural'] ) ? $this->settings['template']['course_lessons_plural'] : TVA_Const::TVA_COURSE_LESSONS_TEXT;
		}

		/**
		 * check for the widgets in the archive page so we can do the options correctly
		 */
		$active_widgets = get_option( 'sidebars_widgets' );

		$this->settings['template']['has_progress_bar'] = false;
		$this->settings['template']['has_author']       = false;
		$this->settings['template']['has_lesson_list']  = false;

		/**
		 * backwards compatibility for the fonts
		 */
		if ( ! isset( $this->settings['template']['font_source'] ) ) {
			$this->settings['template']['font_source'] = $this->settings['template']['font_family'] == 'MavenProRegular, sans-serif' ? 'google' : 'safe';

			if ( $this->settings['template']['font_source'] == 'google' ) {
				$this->settings['template']['font_regular'] = 'regular';
				$this->settings['template']['font_bold']    = 500;
				$this->settings['template']['font_charset'] = 'latin';
				$this->settings['template']['font_url']     = '//fonts.googleapis.com/css?family=Maven+Pro:regular,500&subset=latin';
			} else {
				$this->settings['template']['font_regular'] = '';
				$this->settings['template']['font_bold']    = '';
				$this->settings['template']['font_charset'] = '';
				$this->settings['template']['font_url']     = '';
			}
		}

		if ( isset( $active_widgets['tva-sidebar'] ) && ! empty( $active_widgets['tva-sidebar'] ) ) {
			foreach ( $active_widgets['tva-sidebar'] as $widget ) {
				if ( strpos( $widget, 'tva_progress_bar_widget' ) !== false ) {
					$this->settings['template']['has_progress_bar'] = true;
				}

				if ( strpos( $widget, 'tva_author_widget' ) !== false ) {
					$this->settings['template']['has_author'] = true;
				}

				if ( strpos( $widget, 'tva_lesson_list_widget' ) !== false ) {
					$this->settings['template']['has_lesson_list'] = true;
				}
			}
		}

		if ( isset( $active_widgets['tva-lesson-sidebar'] ) && ! empty( $active_widgets['tva-lesson-sidebar'] ) ) {
			foreach ( $active_widgets['tva-lesson-sidebar'] as $widget ) {
				if ( strpos( $widget, 'tva_progress_bar_widget' ) !== false ) {
					$this->settings['template']['has_progress_bar'] = true;
				}

				if ( strpos( $widget, 'tva_author_widget' ) !== false ) {
					$this->settings['template']['has_author'] = true;
				}

				if ( strpos( $widget, 'tva_lesson_list_widget' ) !== false ) {
					$this->settings['template']['has_lesson_list'] = true;
				}
			}
		}

		$this->settings['preview_url'] = tva_get_preview_url();

		$args = array(
			'taxonomy'   => TVA_Const::OLD_POST_TAXONOMY,
			'hide_empty' => false,
			'fields'     => 'all',
			'per_page'   => - 1,
		);

		$term_query = new WP_Term_Query( $args );

		$this->settings['apprentice_url']            = isset( $this->settings['index_page']['ID'] ) ? get_permalink( $this->settings['index_page']['ID'] ) : '';
		$this->settings['import']                    = get_option( 'tva_import_decission', false );
		$this->settings['apprentice']                = isset( $theme_options['appr_enable_feature'] ) && (int) $theme_options['appr_enable_feature'] == 1 ? true : false;
		$this->settings['is_thrivetheme']            = isset( $is_thrive_theme ) && $is_thrive_theme == true;
		$this->settings['has_importcourses']         = ! empty( $term_query->terms );
		$this->settings['apprentice_ribbon']         = get_option( 'tva_apprentice_ribbon_decission', false );
		$this->settings['preview_option']            = tva_get_preview_option();
		$this->settings['wizard']                    = get_option( 'tva_wizard_completed', false ); // <-  Backwards compatible for apprentice 3.0 -> Send the wizard flag through settings
		$this->settings['membership_plugin']         = [];
		$this->settings['apprentice_label']          = isset( $this->settings['apprentice_label'] ) ? $this->settings['apprentice_label'] : true;
		$this->settings['register_page']             = ! empty( $this->settings['register_page'] )
			? $this->settings['register_page']
			: array(
				'name' => $reg_page_option['name'],
				'ID'   => $reg_page_option['ID'],
			);
		$this->settings['template']['course_lesson'] = isset( $this->settings['template']['course_lesson'] )
			? $this->settings['template']['course_lesson']
			: TVA_Const::TVA_COURSE_LESSON_TEXT;
		$this->settings['comment_status']            = empty( $this->settings['comment_status'] ) ?
			TVA_Const::TVA_DEFAULT_COMMENT_STATUS : $this->settings['comment_status'];
		$this->settings['index_page']                = ! empty( $this->settings['index_page']['ID'] )
			? $this->settings['index_page']
			: get_option(
				'tva_provisional_index_page',
				array(
					'name' => '',
					'ID'   => '',
				)
			);

		$this->settings['switch_topic_options'] = get_option( 'tva_switch_topic_options', false );
		$this->settings['template']             = wp_parse_args( $this->settings['template'], $this->_defaults['template'] );

		/**
		 * Tis is not good but we have to live with it.
		 * On the long run we should remove sendowl settings from this instance
		 */
		$this->settings = wp_parse_args( $this->settings, TVA_Sendowl_Settings::instance()->get_settings() );

		$this->_set_settings();
		$this->hooks();
	}

	/**
	 * Set plugin general settings
	 *
	 * @TODO set all plugin general settings inside this function
	 */
	private function _set_settings() {

		$this->settings['login_page'] = $this->_get_option( self::LOGIN_PAGE, self::$default_page );

		return $this->settings;
	}

	public function hooks() {
		add_filter( 'tva_admin_localize', array( $this, 'localize' ) );
	}

	public function localize( $data ) {

		$data['data']['settings']['login_page']['edit_url']    = tva_get_editor_url( $this->settings['login_page']['ID'] );
		$data['data']['settings']['login_page']['edit_text']   = __( 'Edit with Thrive Architect', 'thrive-apprentice' );
		$data['data']['settings']['login_page']['preview_url'] = get_permalink( $this->settings['login_page']['ID'] );

		return $data;
	}

	/**
	 * Get an option from db
	 * If any default is provided force returned value to be of the same type as the default
	 *
	 * @param      $option
	 * @param bool $default
	 *
	 * @return bool|mixed
	 */
	private function _get_option( $option, $default = false ) {
		$_value = get_option( $option, $default );

		/**
		 * Force the returned value to be of the same type as default one
		 */
		if ( false !== $default && gettype( $_value ) !== gettype( $default ) ) {
			$_value = $default;
		}

		return $_value;
	}

	/**
	 * Checks if $post is the post set as TA login page
	 *
	 * @param WP_Post|int $post
	 *
	 * @return bool
	 */
	public function is_login_page( $post = null ) {

		return $this->_is_ta_page( self::LOGIN_PAGE, $post );
	}

	/**
	 * Check if a given page is one of those used by TA based on the provided key
	 *
	 * @param string                    $key
	 * @param WP_Post|int|stdClass|null $page
	 *
	 * @return bool
	 */
	private function _is_ta_page( $key, $page = null ) {

		$page = null !== $page ? $page : get_post();

		$allowed_keys = array(
			self::LOGIN_PAGE => 'login_page',
		);

		if ( ! array_key_exists( $key, $allowed_keys ) ) {
			return false;
		}

		if ( $page instanceof WP_Post || $page instanceof stdClass ) {
			return isset( $page->ID ) && (int) $page->ID === (int) $this->settings[ $allowed_keys[ $key ] ]['ID'];
		}

		if ( is_int( $page ) ) {
			return $page === (int) $this->settings[ $allowed_keys[ $key ] ]['ID'];
		}

		return false;
	}

	/**
	 * Validate available settings
	 *
	 * @return bool
	 */
	public function validate_settings() {
		if ( ! $this->settings || ! is_array( $this->settings ) || ! isset( $this->settings['template'] ) || ! isset( $this->settings['index_page'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return null|TVA_Settings
	 */
	public static function instance() {
		// Check if instance is already exists
		if ( self::$instance == null ) {
			self::$instance = new TVA_Settings();
			self::$instance->init_all();
		}

		return self::$instance;
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	public function get_settings( $key = '' ) {
		$settings = $this->settings;

		if ( ! empty( $key ) ) {
			$settings = $settings[ $key ];
		}

		return $settings;
	}

	/**
	 * @return array|null
	 */
	public function get_checkout_page() {

		$settings      = $this->get_settings();
		$checkout_page = null;

		if ( ! empty( $settings['checkout_page'] ) ) {
			$checkout_page = $settings['checkout_page'];
		}

		return $checkout_page;
	}

	/**
	 * Based on settings gets WP_Post
	 *
	 * @return null|WP_Post
	 */
	public function get_checkout_page_post() {

		if ( $this->checkout_page_post instanceof WP_Post ) {
			return $this->checkout_page_post;
		}

		$page = $this->get_checkout_page();

		if ( $page && ! empty( $page['ID'] ) ) {
			$_post                    = get_post( $page['ID'] );
			$this->checkout_page_post = $_post instanceof WP_Post ? $_post : null;
		}

		return $this->checkout_page_post;
	}

	/**
	 * @return mixed|null
	 */
	public function get_checkout_page_url() {

		$url = null;

		$page = $this->get_checkout_page_post();
		if ( $page instanceof WP_Post ) {
			$url = get_permalink( $page );
		}

		return $url;
	}

	/**
	 * Gets the array of Index Page setting
	 * - name: string of page title
	 * - ID: int of post
	 *
	 * @return array|null
	 */
	public function get_index_page() {

		$index_page = null;

		if ( isset( $this->settings['index_page'] ) && is_array( $this->settings['index_page'] ) && count( $this->settings['index_page'] ) === 2 ) {
			$index_page = $this->settings['index_page'];
		}

		return $index_page;
	}

	/**
	 * Checks if $post parameters is the page for all Thrive Apprentice Courses
	 * List of all courses
	 *
	 * @param $post WP_Post|int
	 *
	 * @return bool
	 */
	public function is_index_page( $post ) {

		if ( ! is_int( $post ) && ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		$index_page    = $this->get_index_page();
		$index_page_id = $index_page ? intval( $index_page['ID'] ) : null;

		return $index_page_id && $index_page_id === $post->ID;
	}

	/**
	 * Get index page url
	 *
	 * @return bool|false|string
	 */
	public static function get_index_page_url() {

		$settings = self::instance()->settings;

		if ( ! isset( $settings['index_page'] ) ) {
			return false;
		}

		$index = $settings['index_page'];

		if ( ! is_array( $index ) || ! isset( $index['ID'] ) || ! is_int( $index['ID'] ) ) {
			return false;
		}

		$url = get_permalink( $index['ID'] );

		return $url;
	}

	/**
	 * Gets the array of Register Page setting
	 * - name: string of page title
	 * - ID: int of post
	 *
	 * @return array|null
	 */
	public function get_register_page() {

		$register_page = null;

		if ( isset( $this->settings['register_page'] ) && is_array( $this->settings['register_page'] ) && count( $this->settings['register_page'] ) === 2 ) {
			$register_page = $this->settings['register_page'];
		}

		return $register_page;
	}

	/**
	 * Checks if $post is set as Registration Page in General Settings
	 *
	 * @param $post int|WP_Post
	 *
	 * @return bool
	 */
	public function is_register_page( $post ) {

		if ( ! is_int( $post ) && ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		if ( is_int( $post ) ) {
			$post = get_post( $post );
		}

		$register_page    = $this->get_register_page();
		$register_page_id = $register_page ? intval( $register_page['ID'] ) : null;

		return $register_page_id && $register_page_id === $post->ID;
	}

	/**
	 * Get the login WP_Post set as login page
	 *
	 * @return null|WP_Post
	 */
	public function get_login_page() {

		$post = null;

		if ( ! empty( $this->settings['login_page']['ID'] ) ) {
			$post = get_post( $this->settings['login_page']['ID'] );

			return $post instanceof WP_Post ? $post : null;
		}

		return $post;
	}
}
