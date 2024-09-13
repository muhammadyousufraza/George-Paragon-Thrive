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
 * Class Tcb_Scripts
 */
class Tcb_Scripts {

	const HEAD_SCRIPT   = 'head';
	const BODY_SCRIPT   = 'body';
	const FOOTER_SCRIPT = 'footer';

	/**
	 * All types of scripts that can be saved for a post
	 */
	const ALL = [ self::HEAD_SCRIPT, self::BODY_SCRIPT, self::FOOTER_SCRIPT ];

	/**
	 * Option name where we are saving the scripts ( the same as the one from TAR )
	 */
	const OPTION_NAME = 'tve_global_scripts';

	/**
	 * @var TCB_Post
	 */
	private $post;

	/**
	 * @var null instance
	 */
	protected static $_instance;

	/**
	 * General singleton implementation for class instance that also requires an id
	 *
	 * @param int $id
	 *
	 * @return null
	 */
	public static function instance_with_id( $id = 0 ) {
		/* if we don't have any instance or when we send an id that it's not the same as the previous one, we create a new instance */
		if ( empty( static::$_instance ) || is_wp_error( $id ) || ( ! empty( $id ) && static::$_instance->ID !== $id ) ) {
			static::$_instance = new self( $id );
		}

		return static::$_instance;
	}

	/**
	 * Tcb_Scripts constructor.
	 *
	 * @param int $id
	 */
	public function __construct( $id = 0 ) {
		$this->post = new TCB_Post( $id );
	}

	/**
	 * Add actions in order to insert the scripts properly
	 */
	public function hooks() {
		add_action( 'wp_head', function () {
			echo $this->get_all( self::HEAD_SCRIPT ); // phpcs:ignore;
		} );

		add_action( 'theme_after_body_open', function () {
			echo $this->get_all( self::BODY_SCRIPT ); // phpcs:ignore;
		} );

		add_action( 'theme_before_body_close', function () {
			echo $this->get_all( self::FOOTER_SCRIPT ); // phpcs:ignore;
		} );
	}

	/**
	 * Get the posts global scripts
	 *
	 * @param string $type
	 *
	 * @return array|mixed|string
	 */
	public function get_all( $type = '' ) {
		$scripts = $this->post->meta( static::OPTION_NAME );
		$all     = [];

		foreach ( static::ALL as $value ) {
			$all[ $value ] = isset( $scripts[ $value ] ) ? $scripts[ $value ] : '';
		}

		if ( empty( $type ) ) {
			$scripts = $all;
		} else {
			$scripts = isset( $all[ $type ] ) ? $all[ $type ] : '';
		}


		return $scripts;
	}

	/**
	 * Save scripts
	 *
	 * @param $data
	 */
	public function save( $data ) {
		$scripts = [];

		foreach ( static::ALL as $value ) {
			$key               = "thrive_{$value}_scripts";
			$scripts[ $value ] = isset( $data[ $key ] ) ? $data[ $key ] : '';
		}

		if ( ! empty( $scripts ) ) {
			$this->post->meta( static::OPTION_NAME, $scripts );
		}
	}
}

if ( ! function_exists( 'tcb_scripts' ) ) {
	/**
	 * Return Thrive_Post instance
	 *
	 * @param int id - post id
	 *
	 * @return Tcb_Scripts
	 */
	function tcb_scripts( $id = 0 ) {
		return Tcb_Scripts::instance_with_id( $id );
	}
}
