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
 * Class Thrive_Theme_List
 */
class Thrive_Theme_List {

	/**
	 * @var array
	 */
	private $attr;

	/**
	 * @var array
	 */
	private $query;

	/**
	 * @var boolean
	 */
	private $is_demo_content;

	/**
	 * Thrive_Theme_List constructor.
	 *
	 * @param array   $attr
	 * @param boolean $use_demo_content
	 */
	public function __construct( $attr, $use_demo_content ) {
		$this->is_demo_content = $use_demo_content;
		$this->parse_attributes( $attr );
	}

	/**
	 * @param array $attr
	 */
	public function parse_attributes( $attr ) {
		$attr = array_merge( [ 'icon' => 'icon-angle-right-light' ], $attr );

		if ( is_array( $attr['query'] ) ) {

			if ( empty( $attr['query']['post_type'] ) ) {
				$attr['query']['post_type'] = $attr['type']; // for backwards compatibility
			}

			$query = $attr['query'];
		} else {
			/* replace single quotes with double quotes */
			$decoded_string = str_replace( "'", '"', html_entity_decode( $attr['query'], ENT_QUOTES ) );

			/* replace newlines and tabs */
			$decoded_string = preg_replace( '/[\r\n]+/', ' ', $decoded_string );

			$query = json_decode( $decoded_string, true );
		}

		$query['offset'] = isset( $query['offset'] ) ? (int) ( $query['offset'] ) - 1 : 0;

		/* backwards compatibility */
		if ( ! empty( $attr['limit'] ) ) {
			$query['posts_per_page'] = $attr['limit'];
			unset( $attr['limit'] );
		}

		$this->set_attr( $attr );
		$this->set_query( $query );
	}

	/**
	 * Get all the list items
	 *
	 * @return array
	 */
	public function get_items() {
		$list_helper = new Thrive_Dynamic_List_Helper( $this->query, $this->is_demo_content );

		return $list_helper->get_results();
	}

	/**
	 * Render dynamic list content
	 *
	 * @return string
	 */
	public function render() {

		$items = $this->get_items();

		if ( empty( $items ) ) {
			$content = empty( $this->query['no_posts_text'] ) ? '' : '<p class="no-results-text">' . $this->query['no_posts_text'] . '</p>';
		} else {
			$content = Thrive_Utils::get_element( 'dynamic-list', [
				'icon'  => $this->attr['icon'],
				'items' => $items,
			], false );
		}

		/* When we render the content the query needs to be encoded */
		if ( is_array( $this->attr['query'] ) ) {
			$this->attr['query'] = json_encode( $this->attr['query'] );
		}

		return Thrive_Shortcodes::before_wrap( [
			'content' => $content,
			'tag'     => 'div',
			'class'   => THRIVE_SHORTCODE_CLASS . ' thrive-dynamic-list',
		], $this->attr );

	}

	/**
	 * @param array $query
	 */
	public function set_query( $query ) {
		$this->query = $query;
	}

	/**
	 * @param array $attr
	 */
	public function set_attr( $attr ) {
		$this->attr = $attr;
	}

	/**
	 * Default attributes for theme list shortcode
	 *
	 * @return array[]
	 */
	public static function default_list_attributes() {
		return [
			'query' => [
				'filter'         => 'custom',
				'posts_per_page' => 5,
				'order'          => 'ASC',
				'offset'         => 1,
			],
		];
	}

}
