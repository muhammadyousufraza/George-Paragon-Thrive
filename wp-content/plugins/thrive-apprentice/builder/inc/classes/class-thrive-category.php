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
 * Class Thrive_Category
 */
class Thrive_Category {

	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/**
	 * Use the shortcuts for term meta setters and getters
	 */
	use Thrive_Term_Meta;

	/**
	 * Landing page id where the category will be redirected
	 */
	const PAGE_REDIRECT = 'page_redirect';

	/**
	 * Current term id
	 *
	 * @var false|int|null
	 */
	public $ID;

	/**
	 * Current term
	 *
	 * @var array|WP_Term|null
	 */
	private $term;

	/**
	 * Thrive_Category constructor.
	 *
	 * @param $id
	 */
	public function __construct( $id = null ) {
		$this->term = get_term( $id );
		$this->ID   = $id;
	}

	public static function init() {

		/**
		 * Alter the category link
		 */
		add_filter( 'term_link', function ( $link, $term ) {
			return thrive_category( $term->term_id )->get_link( $link );
		}, 10, 3 );

		if ( is_admin() ) {
			/**
			 * Add extra fields for category edit and add page
			 */
			add_action( 'category_edit_form_fields', function ( $term ) {
				include THEME_PATH . '/inc/templates/admin/category/edit-form.php';
			} );
			add_action( 'category_add_form_fields', function () {
				include THEME_PATH . '/inc/templates/admin/category/add-form.php';
			} );

			/**
			 * Save category extra fields
			 */
			add_action( 'edited_category', function ( $term_id ) {
				thrive_category( $term_id )->save_extra_fields();
			} );
			add_action( 'create_category', function ( $term_id ) {
				thrive_category( $term_id )->save_extra_fields();
			} );
		}
	}

	/**
	 * Save additional fields when editing or adding a category
	 */
	public function save_extra_fields() {
		if ( isset( $_POST[ static::PAGE_REDIRECT ] ) ) {
			$this->set_meta( static::PAGE_REDIRECT, sanitize_text_field( $_POST[ static::PAGE_REDIRECT ] ) );
		}
	}

	/**
	 * Return the landing page id where the category will be redirected
	 *
	 * @return mixed
	 */
	public function get_redirect_page_id() {
		return $this->get_meta( static::PAGE_REDIRECT );
	}

	/**
	 * Get a link for a category
	 *
	 * @param string $default_link
	 *
	 * @return false|string
	 */
	public function get_link( $default_link = '' ) {
		$page_id = $this->get_redirect_page_id();

		if ( (int) $page_id && ! is_editor_page_raw() ) {
			$link = get_permalink( $page_id );
		}

		return empty( $link ) ? $default_link : $link;
	}
}

if ( ! function_exists( 'thrive_category' ) ) {
	/**
	 * Return Thrive_Category instance
	 *
	 * @param int id - term id
	 *
	 * @return Thrive_Category
	 */
	function thrive_category( $id = 0 ) {
		return Thrive_Category::instance_with_id( $id );
	}
}
