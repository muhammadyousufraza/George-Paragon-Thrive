<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_AB_Page
 *
 * Page that will have variations for its content
 */
class Thrive_AB_Page extends Thrive_AB_Post {

	/**
	 * @var Thrive_AB_Test
	 */
	protected $_running_test;

	public function __construct( $post ) {

		parent::__construct( $post );

		if ( ! thrive_ab()->is_cpt_allowed( $this->_post->post_type ) ) {
			throw new Exception( __( 'Provided post is not a page', 'thrive-ab-page-testing' ) );
		}
	}

	/**
	 * Query all the variation custom posts for current page post
	 *
	 * @param $filters array wp_query args
	 * @param $type    string default array
	 *
	 * @return array with elements of Thrive_AB_Page_Variation or array
	 * @throws Exception
	 */
	public function get_variations( $filters = array(), $type = 'array' ) {

		if ( $this->_post->post_status === Thrive_AB_Post_Status::VARIATION ) {
			return array();
		}

		$query_args = array(
			'post_type'      => array(
				get_post_type( $this->_post->ID ),
				Thrive_AB_Post_Types::VARIATION,
			),
			'post_parent'    => $this->_post->ID,
			'post_status'    => 'any',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'posts_per_page' => - 1,
		);

		$meta_query = array(
			'relation' => 'AND',
		);

		if ( empty( $filters['all'] ) ) {
			$meta_query['status'] = array(
				'key'   => Thrive_AB_Meta::PREFIX . 'status',
				'value' => 'published',
			);
		} else {
			unset( $filters['all'] );
		}

		$query_args['meta_query'] = $meta_query;
		$query_args               = array_merge( $query_args, $filters );
		$query                    = new WP_Query( $query_args );
		$posts                    = $query->get_posts();

		$variations = array();

		foreach ( $posts as $wp_post ) {
			$variation  = new Thrive_AB_Page_Variation( $wp_post );
			$is_allowed = thrive_ab()->is_cpt_allowed( $variation->post_type );
			if ( $is_allowed && $variation->post_status !== Thrive_AB_Post_Status::VARIATION ) {
				//these posts are pages set as child from WP admin
				//these posts are not variations
				continue;
			}
			$variation->set_page( $this->_post );
			$variations[] = $type === 'array' ? $variation->get_data() : $variation;
		}

		$parent_as_variation = new Thrive_AB_Page_Variation( $this->_post );

		if ( count( $variations ) === 0 ) {
			$parent_as_variation->get_meta()->update( 'traffic', 100 );
			$parent_as_variation->get_meta()->update( 'is_control', 1 );
			$parent_as_variation->get_meta()->update( 'status', 'published' );
		}

		array_unshift( $variations, $type === 'array' ? $parent_as_variation->get_data() : $parent_as_variation );

		return $variations;
	}

	/**
	 * @param array  $filters
	 * @param string $type
	 *
	 * @return array
	 */
	public function get_tests( $filters = array(), $type = 'array' ) {
		$test_manager = new Thrive_AB_Test_Manager();

		$filters = array_merge( $filters, array( 'page_id' => $this->_post->ID ) );

		return $test_manager->get_tests( $filters, $type );
	}

	/**
	 * Returns the running test id of a page or null if the page has no running test
	 *
	 * @return int|null
	 */
	public function get_running_test_id() {

		$running_test = $this->get_tests( array( 'status' => 'running' ) );

		return empty( $running_test ) ? null : $running_test[0]['ID'];
	}

	public function get_running_test() {

		if ( ! $this->_running_test instanceof Thrive_AB_Test ) {
			$tests               = $this->get_tests( array(
				'status' => 'running',
			), OBJECT );
			$this->_running_test = count( $tests ) ? current( $tests ) : null;
		}

		if ( $this->_running_test instanceof Thrive_AB_Test ) {
			$this->_running_test->get_items();
		}

		return $this->_running_test;
	}

	/**
	 * create a custom variation post based on the post
	 * and return it
	 *
	 * @param string $type
	 *
	 * @return Thrive_AB_Variation|array
	 */
	public function get_control_variation( $type = 'array' ) {

		/** @var Thrive_AB_Variation $variation */
		foreach ( $this->get_variations( array(), 'object' ) as $variation ) {
			if ( $variation->get_meta()->get( 'is_control' ) === true ) {
				break;
			}
		}

		return $type === 'array' ? $variation->get_data() : $variation;
	}

	/**
	 * @param array $model
	 *
	 * @return Thrive_AB_Page_Variation
	 * @throws Exception if the post could not be saved or updated
	 *
	 */
	public function save_variation( $model ) {

		/**
		 * Set default data
		 */
		$model = array_merge( array(
			'post_status' => Thrive_AB_Post_Status::VARIATION,
			'post_type'   => get_post_type( $this->_post->ID ),
			'post_parent' => $this->_post->ID,
			'post_title'  => __( 'Variation', 'thrive-ab-page-testing' ),
		), $model );

		if ( empty( $model['ID'] ) ) {
			$post = wp_insert_post( $model );
		} else {
			$post = wp_update_post( $model );
		}

		if ( is_wp_error( $post ) || $post === 0 ) {
			throw new ErrorException( __( 'Variation could not be saved', 'thrive-ab-page-testing' ) );
		}

		$variation = new Thrive_AB_Page_Variation( $post );

		$model_has_meta = ! empty( $model['meta'] ) && is_array( $model['meta'] );

		if ( $model_has_meta ) {
			foreach ( $model['meta'] as $key => $value ) {
				$variation->get_meta()->update( $key, $value );
			}

			$original_page = new Thrive_AB_Meta( $model['post_parent'] );
			$variation->get_meta()->update( 'thrive_post_template', $original_page->get( 'thrive_post_template' ) );
		}

		return $variation;
	}

	/**
	 * Public data to be localized
	 *
	 * @return array
	 */
	public function get_data() {

		return array(
			'edit_link' => get_edit_post_link( $this->_post->ID, '' ),
			'ID'        => $this->_post->ID,
		);
	}

	/**
	 * Implemented as hook for 'delete_post' action
	 *
	 * @param $post_id
	 *
	 * @see Thrive_AB __construct()
	 *
	 */
	public static function delete( $post_id ) {

		try {
			$page = new Thrive_AB_Page( $post_id );

			$variations = $page->get_variations( array(), 'object' );
			array_shift( $variations );

			/** @var Thrive_AB_Page_Variation $variation */
			foreach ( $variations as $variation ) {
				$variation->get_meta()->update( 'is_control', false );
				$variation->delete();
			}
		} catch ( Exception $e ) {
			//if any of the variation cannot be deleted permanently
		}
	}

	/**
	 * Hook into trash action and don't allow to trash page if it has a running test
	 *
	 * @param $null null check is made for NULL
	 * @param $post
	 *
	 * @return NULL or FALSE for not trashing
	 */
	public static function trash( $null, $post ) {

		try {
			$page    = new Thrive_AB_Page( $post );
			$test_id = $page->get_meta()->get( 'running_test_id' );
			$null    = empty( $test_id ) ? null : false;
		} catch ( Exception $e ) {
		}

		return $null;
	}

	/**
	 * Returns the Start Test Url
	 *
	 * @return false|string
	 */
	public function get_start_test_url() {
		$url = get_permalink( $this->_post );
		$url = add_query_arg( 'thrive-variations', 'true', $url );

		return $url;
	}

	public function get_test_link( $test_id ) {

		return Thrive_AB_Test_Manager::get_test_url( $test_id );
	}
}
