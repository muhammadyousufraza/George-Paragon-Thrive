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

/**
 * Class Shortcodes - handles the inner shortcodes for Display Testimonials
 *
 * @package TVO\DisplayTestimonials
 */
class Shortcodes {
	public static $shortcodes = array(
		'tvo_testimonial_title'             => 'testimonial_title',
		'tvo_testimonial_content'           => 'testimonial_content',
		'tvo_testimonial_author'            => 'testimonial_author',
		'tvo_testimonial_role'              => 'testimonial_role',
		'tvo_testimonial_website'           => 'testimonial_website',
		/* Used for handling the url images */
		'tvo_testimonial_dynamic_variables' => 'dynamic_variables',
		'tvo_testimonial_image'             => 'testimonial_image',
	);

	/**
	 * Adds the display Testimonials inner shortcodes
	 */
	public static function init() {
		foreach ( static::$shortcodes as $shortcode => $function ) {
			add_shortcode(
				$shortcode,
				function ( $attr, $content, $tag ) use ( $function ) {
					$output = '';

					if ( method_exists( __CLASS__, $function ) ) {
						$attr = \TCB_Post_List_Shortcodes::parse_attr( $attr, $tag );

						$output = Shortcodes::$function( $attr, $content, $tag );
					}

					return $output;
				} );
		}
	}

	public static function testimonial_title( $attr = [] ) {
		$attr = empty( $attr ) ? [] : $attr;

		$content = Utils::get_testimonial_meta_value( 'title' );

		if ( ! \TCB_Post_List_Shortcodes::is_inline( $attr ) ) {
			$tag     = empty( $attr['tag'] ) ? 'h2' : $attr['tag'];
			$classes = [ TCB_SHORTCODE_CLASS ];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = Utils::before_wrap( [
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			], $attr );
		}

		/* If we do not have content, but we have a default value we display it */
		if ( empty( $content ) && ! empty( $attr['default'] ) ) {
			$content = $attr['default'];
		}

		return $content;
	}

	public static function testimonial_content( $attr = [] ) {
		$attr    = empty( $attr ) ? [] : $attr;
		$content = Utils::get_testimonial_meta_value( 'content' );

		if ( ! \TCB_Post_List_Shortcodes::is_inline( $attr ) ) {
			$classes = [ TCB_SHORTCODE_CLASS ];

			$tag = empty( $attr['tag'] ) ? 'div' : $attr['tag'];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = Utils::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		return $content;
	}

	public static function testimonial_author( $attr = [] ) {
		$attr    = empty( $attr ) ? [] : $attr;
		$content = Utils::get_testimonial_meta_value( 'author' );

		if ( ! \TCB_Post_List_Shortcodes::is_inline( $attr ) ) {
			$classes = [ TCB_SHORTCODE_CLASS ];

			$tag = empty( $attr['tag'] ) ? 'div' : $attr['tag'];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = Utils::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		return $content;
	}

	public static function testimonial_role( $attr = [] ) {
		$attr    = empty( $attr ) ? [] : $attr;
		$content = Utils::get_testimonial_meta_value( 'role' );

		if ( ! \TCB_Post_List_Shortcodes::is_inline( $attr ) ) {
			$classes = [ TCB_SHORTCODE_CLASS ];

			$tag = empty( $attr['tag'] ) ? 'div' : $attr['tag'];

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = Utils::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		}

		/* If we do not have content, but we have a default value we display it */
		if ( empty( $content ) && ! empty( $attr['default'] ) ) {
			$content = $attr['default'];
		}

		return $content;
	}

	public static function testimonial_website( $attr = [] ) {
		$attr    = empty( $attr ) ? [] : $attr;
		$content = Utils::get_testimonial_meta_value( 'website' );

		/* If we do not have content, but we have a default value we display it */
		if ( empty( $content ) && ! empty( $attr['default'] ) ) {
			$content = $attr['default'];
		}

		$link_attrs = array(
			'href'     => $content,
			'data-css' => ! empty( $attr['link-css-attr'] ) ? $attr['link-css-attr'] : '',
		);

		if ( ! \TCB_Post_List_Shortcodes::is_inline( $attr ) ) {
			$classes = [ TCB_SHORTCODE_CLASS ];

			$tag = empty( $attr['tag'] ) ? 'div' : $attr['tag'];

			$content = \TCB_Utils::wrap_content( $content, 'a', '', '', $link_attrs );

			if ( $tag === 'span' ) {
				$classes[] = 'tcb-plain-text';
			}

			$content = Utils::before_wrap( array(
				'content' => $content,
				'tag'     => $tag,
				'class'   => implode( ' ', $classes ),
			), $attr );
		} else {
			$content = \TCB_Utils::wrap_content( $content, 'a', '', '', $link_attrs );
		}

		return $content;
	}

	/**
	 * Callback for dynamic style shortcode
	 *
	 * @param array  $attr
	 * @param string $style_css
	 *
	 * @return string
	 */
	public static function dynamic_variables( $attr = [], $style_css = '' ) {
		$attr = empty( $attr ) ? [] : $attr;

		$css = Main::get_dynamic_variables();

		return \TCB_Utils::wrap_content( $css, 'style', '', 'tvo-display-testimonials-dynamic-variables', [ 'type' => 'text/css' ] );
	}

	public static function testimonial_image( $attr = [] ) {
		$attr = empty( $attr ) ? [] : $attr;

		$attr['src'] = Utils::get_testimonial_meta_value( 'picture' );
		$classes     = ! empty( $attr['class'] ) ? $attr['class'] : 'tve_image';

		return \TCB_Utils::wrap_content( '', 'img', '', $classes, $attr );
	}
}
