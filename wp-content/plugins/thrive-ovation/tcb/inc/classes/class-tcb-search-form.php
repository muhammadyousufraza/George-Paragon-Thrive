<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Search_Form
 */
class TCB_Search_Form {

	private $shortcode_tag = 'tcb_search_form';

	/**
	 * Default values for search post types
	 *
	 * @var array
	 */
	public static $default_post_types = [ 'post' => 'Post', 'page' => 'Page' ];

	/**
	 * TCB_Search_Form constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'init_shortcode' ] );

		add_filter( 'tcb_content_allowed_shortcodes', [ $this, 'tcb_content_allowed_shortcodes' ] );

		add_action( 'pre_get_posts', [ $this, 'filter_post_types' ] );
	}

	/**
	 * Add the logo shortcode.
	 */
	public function init_shortcode() {

		add_shortcode( $this->shortcode_tag, static function ( $attr ) {

			/**
			 * Type validation
			 */
			if ( ! is_array( $attr ) ) {
				$attr = [];
			}

			if ( ! empty( $attr['wrapper-events'] ) && is_string( $attr['wrapper-events'] ) ) {
				$attr['wrapper-events'] = '__TCB_EVENT_[' . $attr['wrapper-events'] . ']_TNEVE_BCT__';
			}

			if ( ! empty( $attr['post-types'] ) && is_string( $attr['post-types'] ) ) {
				$attr['post-types'] = json_decode( $attr['post-types'], true );
			}


			$attr = array_merge( static::default_attrs(), $attr );

			$id = $attr['wrapper-id'];

			$classes = 'thrv_wrapper thrv-search-form ' . $attr['wrapper-class'];
			$content = static::render( $attr );


			$wrapper_attr     = [
				'data-css'        => esc_attr( $attr['data-css-form'] ),
				'data-tcb-events' => esc_html( $attr['wrapper-events'] ),
				'data-ct-name'    => esc_attr( $attr['data-ct-name'] ),
				'data-ct'         => esc_attr( $attr['data-ct'] ),
				'data-list'       => isset( $attr['data-list'] ) ? esc_attr( $attr['data-list'] ) : '',
				'data-display-d'  => esc_attr( $attr['data-display-d'] ),
				'data-position-d' => esc_attr( $attr['data-position-d'] ),
			];
			$additional_attrs = [ 'data-display-t', 'data-position-t', 'data-display-m', 'data-position-m', 'data-editor-preview' ];

			foreach ( $additional_attrs as $additional_attr ) {
				if ( isset( $attr[ $additional_attr ] ) ) {
					$wrapper_attr[ $additional_attr ] = esc_attr( $attr[ $additional_attr ] );
				}
			}


			return TCB_Utils::wrap_content( $content, 'div', $id, $classes, $wrapper_attr );
		} );
	}

	/**
	 * Modify WP_Query before it asks the database what data to retrieve
	 *
	 * @param WP_Query $query
	 *
	 * @return void
	 */
	public function filter_post_types( $query ) {

		// Don't run on admin
		if ( $query->is_admin ) {
			return;
		}

		// IF main query and search page
		if ( $query->is_main_query() && $query->is_search() && isset( $_GET['tcb_sf_post_type'] ) ) {

			$post_types = map_deep( $_GET['tcb_sf_post_type'], 'sanitize_text_field' );

			/**
			 * Type validation
			 */
			if ( ! is_array( $post_types ) || empty( $post_types ) ) {
				$post_types = static::$default_post_types;
			}

			$query->set( 'post_type', $post_types );
		}
	}

	public function tcb_content_allowed_shortcodes( $shortcodes = [] ) {
		if ( is_editor_page_raw( true ) ) {
			$shortcodes = array_merge( $shortcodes, [ $this->shortcode_tag ] );
		}

		return $shortcodes;
	}

	/**
	 * Returns the default shortcode attributes
	 *
	 * @return array
	 */
	public static function default_attrs() {
		return array(
			'wrapper-id'        => '',
			'wrapper-class'     => '',
			'wrapper-events'    => '',
			'data-css-form'     => '',
			'data-ct'           => 'search_form-56234',
			'data-ct-name'      => 'Default Template',
			'data-css-input'    => '',
			'data-css-submit'   => '',
			'data-css-icon'     => '',
			'data-display-d'    => 'none',
			'data-position-d'   => 'left',
			'button-icon'       => '<svg class="tcb-icon" viewBox="0 0 512 512" data-id="icon-search-regular"><path d="M508.5 468.9L387.1 347.5c-2.3-2.3-5.3-3.5-8.5-3.5h-13.2c31.5-36.5 50.6-84 50.6-136C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c52 0 99.5-19.1 136-50.6v13.2c0 3.2 1.3 6.2 3.5 8.5l121.4 121.4c4.7 4.7 12.3 4.7 17 0l22.6-22.6c4.7-4.7 4.7-12.3 0-17zM208 368c-88.4 0-160-71.6-160-160S119.6 48 208 48s160 71.6 160 160-71.6 160-160 160z"></path></svg>',
			'button-layout'     => 'icon_text',
			'button-label'      => __( 'Search', 'thrive-cb' ),
			'input-placeholder' => esc_attr__( 'Search', 'thrive-cb' ),
			'post-types'        => static::$default_post_types,
		);
	}

	/**
	 * Render shortcode
	 *
	 * @param array $attr
	 *
	 * @return string|null
	 */
	public static function render( $attr = [] ) {
		return trim( tcb_template( 'search-form/shortcode', $attr, true ) );
	}
}

new TCB_Search_Form();
