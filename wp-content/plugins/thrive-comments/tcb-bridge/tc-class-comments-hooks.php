<?php
/**
 * FileName  tc-class-hooks.php.
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TCB_Comments_Hooks {

	/**
	 * The single instance of the class.
	 *
	 * @var TCB_Comments_Hooks singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main TCB Comments Hooks instance.
	 * Ensures only one instance of TCB_Comments_Hooks is loaded or can be loaded.
	 *
	 * @return TCB_Comments_Hooks
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		/**
		 * TCB 2.0 Hooks
		 */
		add_filter( 'tcb_elements', array( $this, 'tc_tcb_add_elements' ), 10, 1 );

		/**
		 * Adds extra script(s) to the main frame
		 */
		add_action( 'tcb_main_frame_enqueue', array( $this, 'tcm_tcb_enqueue_scripts' ), 10, 0 );

		/**
		 * Add Thrive Comments scripts for frontend
		 */
		add_action( 'tve_frontend_extra_scripts', array( $this, 'tc_tcb_frontend_scripts' ) );

		/**
		 * Add Thrive Comments component in tcb
		 */
		add_filter( 'tcb_menu_path_thrive_comments', array( $this, 'tc_tcb_add_component' ) );

		/**
		 * Change thrive theme comments section attributes
		 */
		add_filter( 'thrive_theme_comments_attributes', array( $this, 'theme_comments_attributes' ) );

		/**
		 * Change thrive theme comments section name
		 */
		add_filter( 'thrive_theme_comments_section_name', array( $this, 'theme_comments_section_name' ) );
	}

	/**
	 * Add the TC Component
	 *
	 * @return string
	 */
	public function tc_tcb_add_component() {
		if ( $this->tc_comments_allowed() ) {
			return __DIR__ . '/templates/thrive_comments_component.php';
		}

		return '';
	}

	/**
	 * Include Thrive Comments elements into tcb
	 *
	 * @param array $elements
	 *
	 * @return array
	 */
	public function tc_tcb_add_elements( $elements = array() ) {
		if ( $this->tc_comments_allowed() ) {
			require_once __DIR__ . '/classes/class-tcb-thrive-comments-element.php';
			$elements['thrive_comments'] = new TCB_Thrive_Comments_Element( 'thrive_comments' );
		}

		return $elements;
	}

	/**
	 * Enqueue tcb scripts
	 */
	public function tcm_tcb_enqueue_scripts() {
		if ( $this->tc_comments_allowed() ) {
			tcm()->tcm_enqueue_script( 'tcm_tcb_editor', tcm()->plugin_url( 'tcb-bridge/js/tcb-tc-comments.min.js' ), array(), false, false );
		}
	}

	/**
	 * Include scripts for thrive comments on tcb frontend
	 */
	public function tc_tcb_frontend_scripts() {
		if ( $this->tc_comments_allowed() ) {
			tcm()->tcm_enqueue_style( 'tcm-front-styles-css', tcm()->plugin_url( '/assets/css/styles.css' ) );

			tcm()->tcm_enqueue_script( 'tcm-frontend-js', tcm()->plugin_url( '/assets/js/frontend.min.js' ), array(
				'jquery',
				'backbone',
			), false, true );

			tcm()->tcm_enqueue_script( 'libs-tcb', tcm()->plugin_url( 'assets/js/libs-frontend.min.js' ), array( 'jquery' ) );

			if ( ! is_user_logged_in() ) {
				add_action( 'wp_footer', 'wp_auth_check_html', 5 );
				wp_enqueue_style( 'wp-auth-check' );
			}
		}
	}

	/**
	 * Check if comments are allwoed to be added with TAr on a certain post / page
	 *
	 * @return bool
	 */
	public function tc_comments_allowed() {
		$allow = false;
		//for the moment we are only allowing comments on landing pages from TAr
		if ( function_exists( 'tve_post_is_landing_page' ) && $is_landing_page = tve_post_is_landing_page( get_the_ID() ) ) {
			$post                  = tcmc()->tc_get_post();
			$tc_comments_closed    = tcms()->tcm_get_setting_by_name( 'activate_comments' );
			$post_content          = get_post_meta( $post->ID, 'tve_updated_post_' . $is_landing_page, true );
			$has_comment_container = strpos( $post_content, 'thrive-comments' );

			$allow = is_editor_page_raw() || ( $tc_comments_closed && $has_comment_container !== false && ! tcms()->close_comments( $post->ID ) );
		}

		/**
		 * Allow comments filter inside the editor
		 */
		return apply_filters( 'tcm_allow_comments_editor', $allow );
	}

	/**
	 * Change thrive theme comments section attributes
	 *
	 * @param array $attr
	 *
	 * @return mixed
	 */
	public function theme_comments_attributes( $attr ) {
		unset( $attr['ct'], $attr['ct-name'] );

		return $attr;
	}

	/**
	 * Change thrive theme comments section name
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function theme_comments_section_name( $name ) {
		return 'Thrive Comments Section';
	}

}

/**
 *  Main instance of Thrive Comments Db.
 *
 * @return TCB_Comments_Hooks
 */
function tcbh() {
	return TCB_Comments_Hooks::instance();
}

tcbh();

