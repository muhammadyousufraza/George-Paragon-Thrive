<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ovation
 */

namespace TVO\DisplayTestimonials;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Utils {
	public static function get_ovation_inline_shortcodes() {

		return [
			'Ovation' => [
				[
					'name'   => __( 'Title', 'thrive-ovation' ),
					'option' => __( 'Title', 'thrive-ovation' ),
					'value'  => 'tvo_testimonial_title',
					'input'  => [
						'default' => [
							'type'  => 'input',
							'label' => __( 'Default value', 'thrive-ovation' ),
							'value' => '',
						],
					],
				],
				[
					'name'   => __( 'Full name', 'thrive-ovation' ),
					'option' => __( 'Full name', 'thrive-ovation' ),
					'value'  => 'tvo_testimonial_author',
				],
				[
					'name'   => __( 'Role/Occupation', 'thrive-ovation' ),
					'option' => __( 'Role/Occupation', 'thrive-ovation' ),
					'value'  => 'tvo_testimonial_role',
					'input'  => [
						'default' => [
							'type'  => 'input',
							'label' => __( 'Default value', 'thrive-ovation' ),
							'value' => '',
						],
					],
				],
				[
					'name'   => __( 'Website', 'thrive-ovation' ),
					'option' => __( 'Website', 'thrive-ovation' ),
					'value'  => 'tvo_testimonial_website',
					'input'  => array(
						'default' => [
							'type'  => 'input',
							'label' => __( 'Default value', 'thrive-ovation' ),
							'value' => '',
						],
						'target'  => array(
							'type'       => 'checkbox',
							'label'      => __( 'Open in new tab', 'thrive-ovation' ),
							'value'      => true,
							'disable_br' => true,
						),
						'rel'     => array(
							'type'  => 'checkbox',
							'label' => __( 'No follow', 'thrive-ovation' ),
							'value' => false,
						),
					),
				],
			],
		];
	}

	/**
	 * @param $content
	 *
	 * @return array|string|string[]
	 */
	public static function clean_testimonial_content( $content ) {
		$content = tvo_sanitize_testimonial_field( $content );

		return str_replace( [ ' ', '<div', '</div' ], [ ' ', '<p', '</p' ], $content );
	}

	/**
	 * Include a template file for Display Testimonial elements
	 *
	 * @param      $file
	 * @param null $data
	 *
	 * @return false|string
	 */
	public static function tvo_template( $file, $data = null ) {
		if ( strpos( $file, '.php' ) === false && strpos( $file, '.phtml' ) === false ) {
			$file .= '.php';
		}

		$folder    = 'tcb-bridge/templates/display-testimonials/elements/';
		$file      = ltrim( $file, '\\/' );
		$file_path = TVO_PATH . $folder . $file;

		if ( ! is_file( $file_path ) ) {
			return false;
		}

		ob_start();
		include $file_path;

		return ob_get_clean();
	}

	/**
	 * @param array $wrap_args
	 * @param array $attr
	 *
	 * @return false|mixed
	 */
	public static function before_wrap( $wrap_args = [], $attr = [] ) {
		/* attributes that have to be present also on front */
		$front_attr = Main::$front_attr;

		$wrap_args = array_merge(
			array(
				'content' => '',
				'tag'     => 'div',
				'id'      => '',
				'class'   => '',
				'attr'    => array(),
			),
			$wrap_args
		);
		/* extra classes that are sent through data attr */
		$wrap_args['class'] .= ' ' . ( strpos( $wrap_args['class'], THRIVE_WRAPPER_CLASS ) === false ? THRIVE_WRAPPER_CLASS : '' ) . ( empty( $attr['class'] ) ? '' : ' ' . $attr['class'] );
		/* attributes that come directly from the shortcode */
		foreach ( $attr as $key => $value ) {
			if (
				\TCB_Utils::in_editor_render( true ) || /* in the editor, always add the attributes */
				in_array( $key, $front_attr, true ) /* if this attr has to be added on the frontend, add it */
			) {
				$wrap_args['attr'][ 'data-' . $key ] = $value;
				unset( $wrap_args['attr'][ $key ] );
			}
		}

		return call_user_func_array( array( 'TCB_Utils', 'wrap_content' ), $wrap_args );
	}

	/**
	 * Returns an array containing the testimonial meta data
	 *
	 * @return array
	 */
	public static function get_testimonial_meta_data() {
		global $post;


		if ( $post instanceof \WP_Post ) {
			$testimonial_meta = $post->_tvo_testimonial_attributes;

			return [
				'ID'      => get_the_ID(),
				'title'   => $post->post_title,
				'author'  => ! empty( $testimonial_meta['name'] ) ? $testimonial_meta['name'] : '',
				'picture' => ! empty( $testimonial_meta['picture_url'] ) ? $testimonial_meta['picture_url'] : tvo_get_default_image_placeholder(),
				'content' => $post->post_content,
				'role'    => ! empty( $testimonial_meta['role'] ) ? $testimonial_meta['role'] : '',
				'website' => ! empty( $testimonial_meta['website_url'] ) ? $testimonial_meta['website_url'] : '',
			];
		}

		return [];
	}

	public static function is_placeholder_image( $image ) {
		return $image === tvo_get_default_image_placeholder();
	}

	/**
	 * Returns the value of a testimonial meta data
	 *
	 * @param $key
	 *
	 * @return string
	 */
	public static function get_testimonial_meta_value( $key ) {
		$testimonial_meta = self::get_testimonial_meta_data();

		return isset( $testimonial_meta[ $key ] ) ? $testimonial_meta[ $key ] : '';
	}
}
