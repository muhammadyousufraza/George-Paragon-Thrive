<?php

/**
 * Class TQB_Export_Structure_Item
 * Wrapper over Structure Item Page
 * - which allows to export post its self
 * - export variations
 * - export tests
 */
class TQB_Export_Structure_Item {

	/**
	 * @var int
	 */
	protected $page_id;

	/**
	 * @var WP_Post
	 */
	protected $post;

	/**
	 * @var int
	 */
	protected $quiz_id;

	/**
	 * @var string where import files exists
	 */
	protected $_site_url;

	/**
	 * TQB_Export_Structure_Item constructor.
	 *
	 * @param int $page_id
	 * @param int $quiz_id
	 */
	public function __construct( $page_id, $quiz_id = null ) {
		$this->page_id   = (int) $page_id;
		$this->quiz_id   = (int) $quiz_id;
		$this->_site_url = str_replace( array( 'http://', 'https://', '//' ), '', site_url() );
	}

	/**
	 * Returns post from DB to be written into file
	 *
	 * @return WP_Post|null
	 */
	public function get_post() {

		if ( empty( $this->post ) ) {
			$this->post = get_post( $this->page_id );
		}

		$type        = str_replace( 'tqb_', '', $this->post->post_type );
		$method_name = "alter_{$type}_post";

		if ( method_exists( $this, $method_name ) ) {
			$this->$method_name();
		}

		return $this->post;
	}

	/**
	 * Dynamic method called from get_post method
	 *
	 * Modify the post object before export
	 *
	 * @return void
	 */
	public function alter_optin_post() {
		/**
		 * Adds meta for skip optin
		 */
		$this->post->meta = array(
			TQB_Post_meta::META_NAME_FOR_SKIP_OPTIN => TQB_Post_meta::get_quiz_page_skip_optin( $this->page_id ),
		);
	}

	/**
	 * Gets page variations
	 *
	 * @return array
	 */
	public function get_variations() {

		$manager = new TQB_Variation_Manager( $this->quiz_id, $this->page_id );

		return $manager->get_page_variations();
	}

	/**
	 * Gets the running test for current structure item
	 *
	 * @return array
	 */
	public function get_test() {

		$page_manager = new TQB_Page_Manager( $this->page_id );
		$running_test = $page_manager->get_tests_for_page(
			array(
				'page_id' => $this->page_id,
				'status'  => 1,
			),
			true
		);

		if ( ! empty( $running_test ) ) {
			$test_manager               = new TQB_Test_Manager( $running_test['id'] );
			$running_test['test_items'] = $test_manager->get_test_items(
				array(
					'test_id' => $running_test['id'],
				)
			);
		}

		return ! empty( $running_test ) ? $running_test : array();
	}
}
