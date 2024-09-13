<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Thrive_AB_Post {
	/**
	 * @var WP_Post
	 */
	protected $_post;

	/**
	 * @var Thrive_AB_Meta
	 */
	private $_meta;

	/**
	 * Thrive_AB_Post constructor.
	 *
	 * @param $post WP_Post|int
	 *
	 * @throws Exception
	 */
	public function __construct( $post ) {

		$post = is_int( $post ) ? get_post( $post ) : $post;

		if ( ! ( $post instanceof WP_Post ) ) {
			throw new Exception( __( 'Post not found', 'thrive-ab-page-testing' ) );
		}

		$this->_post = $post;
	}

	public function get_meta() {

		if ( empty( $this->_meta ) ) {
			$this->_meta = new Thrive_AB_Meta( $this->_post->ID );
		}

		return $this->_meta;
	}

	/**
	 * @param $meta Thrive_AB_Meta
	 */
	public function set_meta( $meta ) {
		$this->_meta = $meta;
	}

	public function __get( $key ) {

		$value = null;

		if ( method_exists( $this, $key ) ) {
			$value = call_user_func( array( $this, $key ) );
		} elseif ( isset( $this->_post->$key ) ) {
			$value = $this->_post->$key;
		} elseif ( ( $meta = $this->get_meta()->get( $key ) ) !== null ) {
			$value = $meta;
		}

		return $value;
	}

	public function get_post() {

		return $this->_post;
	}
}
