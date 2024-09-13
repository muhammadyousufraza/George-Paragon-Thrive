<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Course_List;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Dropdown
 *
 * @package  TVA\Architect\Course_List
 * @project  : thrive-apprentice
 */
class Dropdown {
	/**
	 * Course Dropdown Identifier
	 */
	const IDENTIFIER = '.tva-course-list-dropdown';

	/**
	 * @var Dropdown
	 */
	private static $instance;

	/**
	 * @var bool
	 */
	public static $is_editor_page = false;

	/**
	 * @var string[]
	 */
	private $shortcodes = array(
		'tva_course_list_dropdown' => 'render',
	);

	/**
	 * Dropdown constructor.
	 */
	private function __construct() {
		foreach ( $this->shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( $this, $function ) );
		}
	}

	/**
	 * Singleton implementation for Dropdown
	 *
	 * @return Dropdown
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		static::$is_editor_page = is_editor_page_raw( true );

		return self::$instance;
	}

	/**
	 * Renders the course list dropdown
	 *
	 * @param array  $attr
	 * @param string $content
	 *
	 * @return string
	 */
	public function render( $attr = array(), $content = '' ) {
		if ( ! \TCB_Post_List::is_outside_post_list_render() ) {
			/**
			 * If the request is inside a post list, we return the empty string
			 */
			return '';
		}
		$attr = array_merge( $this->default_args(), $attr );

		$classes = $this->get_classes( $attr );
		$data    = array();

		foreach ( $attr as $key => $value ) {
			if ( $key !== 'class' ) { /* we don't want data-class to persist, we process it inside get_classes() */
				$data[ 'data-' . $key ] = esc_attr( $value );
			}
		}

		$html = $this->get_content( $attr );

		return \TCB_Utils::wrap_content( $html, 'div', '', $classes, $data );
	}

	/**
	 * Return the shortcode list needed to render the Course List Dropdown
	 *
	 * @return array
	 */
	public function get_shortcodes() {
		return array_keys( $this->shortcodes );
	}

	/**
	 * Render the course list dropdown classes
	 *
	 * @param array $attr
	 *
	 * @return array
	 */
	private function get_classes( $attr ) {
		$classes = array( str_replace( '.', '', static::IDENTIFIER ), 'thrv_wrapper', 'tcb-form-dropdown', 'tcb-form-dropdown-with-palettes' );

		/* hide the 'Save as Symbol' icon */
		if ( static::$is_editor_page ) {
			$classes[] = 'tcb-selector-no_save';
			$classes[] = 'tve_no_duplicate';
		}

		if ( ! empty( $attr['dropdown-animation'] ) ) {
			$classes[] = $attr['dropdown-animation'];
		}

		/* set custom classes, if they are present */
		if ( ! empty( $attr['class'] ) ) {
			$classes[] = $attr['class'];
		}

		return $classes;
	}

	/**
	 * Default args
	 *
	 * @return array
	 */
	private function default_args() {
		return array(
			'style'                   => 'default',
			'icon'                    => 'style_1',
			'placeholder'             => esc_attr__( 'All courses...', 'thrive-apprentice' ),
			'css'                     => substr( uniqid( 'tve-u-', true ), 0, 17 ),
			'topics-subheading'       => esc_attr__( 'Topics', 'thrive-apprentice' ),
			'restrictions-subheading' => esc_attr__( 'Access restrictions', 'thrive-apprentice' ),
			'progress-subheading'     => esc_attr__( 'My courses', 'thrive-apprentice' ),
			'filter-topics'           => '1',
			'filter-restrictions'     => '1',
			'filter-progress'         => '1',
		);
	}

	/**
	 * Returns the dropdown content
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	private function get_content( $data = array() ) {

		if ( ! class_exists( 'TCB_Course_List_Dropdown_Element', false ) ) {
			include_once \TVA_Const::plugin_path( 'tcb-bridge/editor-elements/course-list/class-tcb-course-list-dropdown-element.php' );
		}

		$icon_styles = tcb_elements()->element_factory( 'course-list-dropdown' )->get_icon_styles();
		if ( ! in_array( $data['icon'], array_keys( $icon_styles ) ) ) {
			$data['icon'] = 'style_1';
		}


		$data['is_editor_page']   = static::$is_editor_page;
		$data['icon']             = $icon_styles[ $data['icon'] ];
		$data['has_topics']       = ! empty( $data['filter-topics'] ) && (int) $data['filter-topics'] === 1;
		$data['has_restrictions'] = ! empty( $data['filter-restrictions'] ) && (int) $data['filter-restrictions'] === 1;
		$data['has_progress']     = ! empty( $data['filter-progress'] ) && (int) $data['filter-progress'] === 1;
		$data['has_option_group'] = $data['has_topics'] && $data['has_restrictions'];
		$data['topics']           = \TVA_Topic::get_items();
		$data['restrictions']     = array_values( array_filter( tva_get_labels(), static function ( $label_data ) {
			/**
			 * We won't display the no label as filtering option in the front-end
			 */
			return $label_data['ID'] !== \TVA_Const::NO_LABEL_ID;
		} ) );
		$data['progress']         = tva_customer()->get_progress_labels();

		ob_start();

		include \TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/elements/course-list-dropdown-content.php' ); //We need to use include here because of ajax

		$content = ob_get_contents();

		ob_end_clean();

		return trim( $content );
	}
}

/**
 * Returns the dropdown shortcode instance
 *
 * @return Dropdown
 */
function tcb_course_list_dropdown_shortcode() {
	return Dropdown::get_instance();
}
