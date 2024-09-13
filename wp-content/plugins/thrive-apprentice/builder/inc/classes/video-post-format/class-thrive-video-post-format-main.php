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
 * Class Thrive_Video_Post_Format_Main
 */
class Thrive_Video_Post_Format_Main {

	const VIDEO_META_PREFIX      = 'thrive_meta_postformat_video';
	const VIDEO_META_TYPE_PREFIX = 'thrive_meta_postformat_video_type';
	const VIDEO_META_OPTION      = 'thrive_theme_video_format_meta';

	const CUSTOM   = 'custom';
	const VIMEO    = 'vimeo';
	const WISTIA   = 'wistia';
	const YOUTUBE  = 'youtube';
	const BUNNYNET = 'bunnynet';

	const ALL_VIDEO_TYPES = [ self::YOUTUBE, self::VIMEO, self::WISTIA, self::CUSTOM, self::BUNNYNET ];

	/**
	 * Get the video type.
	 *
	 * @return array|mixed
	 */
	public static function get_type() {
		$post_id = get_the_ID();
		$options = get_post_meta( $post_id, static::VIDEO_META_OPTION, true );

		if ( empty( $options ) ) {
			/* if the new options are empty, look for options from the old themes */
			$old_theme_type_key = '_' . static::VIDEO_META_TYPE_PREFIX;
			$type               = get_post_meta( $post_id, $old_theme_type_key, true );
		} elseif ( ! empty( $options['type'] ) ) {
			$type = $options['type'];
		}

		/* if nothing is found, use youtube as a default value */
		$type = empty( $type ) ? static::YOUTUBE : $type;

		return $type;
	}

	/*
	 * Called by the 'save_post' action. Saves the video post meta settings.
	 */
	public static function save_video_meta_fields() {
		if ( empty( get_the_ID() ) ) {
			return;
		}

		/* if type is not set, something is terribly wrong */
		if ( isset( $_POST[ static::VIDEO_META_TYPE_PREFIX ] ) ) {
			$type = $_POST[ static::VIDEO_META_TYPE_PREFIX ];

			$post_format = thrive_video_post_format( $type );

			if ( ! empty( $post_format ) ) {
				$post_format->save_options( $_POST );
			}
		}
	}

	/**
	 * Render the thumbnail and the iframe separately.
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return mixed
	 */
	public static function render( $attr = [], $content = '' ) {
		$classes = [ 'tve_responsive_video_container' ];

		/* check if we should hide this element from the page ( by returning nothing or by adding classes to hide it ) */
		if ( ! thrive_post()->is_element_visible( 'featured_video', $classes ) ) {
			return '';
		}

		if ( empty( $content ) ) {
			$thumbnail = static::render_thumbnail( $attr );
		} else {
			add_shortcode( 'thrive_dynamic_video_cover', [ __CLASS__, 'render_video_cover' ] );

			$thumbnail = do_shortcode( $content );

			remove_shortcode( 'thrive_dynamic_video_cover' );

			/* we have to decode the encoded '[' and ']'s in order for tve_parse_events() to work */
			$content = unescape_invalid_shortcodes( $content );

			tve_parse_events( $content );
		}

		if ( isset( $attr['thumbnail-icon-id'] ) ) {
			/*Unset the icon related attributes since we don't need them anymore*/
			unset( $attr['thumbnail-icon-id'], $attr['thumbnail-icon-path'], $attr['thumbnail-icon-style'], $attr['thumbnail-icon-viewbox'] );
		}

		/**
		 * Allow other functionality to be injected here to modify the type
		 * Used in Thrive Apprentice Plugin to display Video Lessons
		 *
		 * @param {string} $type
		 * @param {int} post_id
		 */
		$type = apply_filters( 'thrive_theme_video_post_type', static::get_type(), get_the_ID() );
		$video_type = thrive_video_post_format( $type );

		/* forward the render job to the class with the current video type */
		$iframe = $video_type->render( static::has_thumbnail( $attr ), $attr );
		$container = $thumbnail . $iframe;

		/* We read the settings and add the on the video container */
		$attr = array_merge( static::get_video_post_meta(), $attr );
		/* If the video is floating, the thumbnail should be wrapped inside the floating container*/
		if ( ! empty( $attr['is-floating'] ) || ( isset( $attr['float'] ) && $attr['float'] === 'true' ) ) {
			$classes[] = 'thrive-floating-dynamic-video';

			if ( ! empty( $attr['float-close'] ) ) {
				$close_icon = thrive_template()->get_icon_svg( 'icon-close-solid' );
				$close_icon = str_replace( 'tcb-icon', 'tcb-float-close-button tcb-icon', $close_icon );
				$container  = $close_icon . $container;
			}
			$container = TCB_Utils::wrap_content( $container, 'div', '', 'tcb-video-float-container' );
		}

		$style = [];

		/* Do not add the style as a data attribute, add it as a style */
		if ( ! empty( $attr['style'] ) ) {
			$style['style'] = $attr['style'];
			unset( $attr['style'] );
		}
		$video_attrs = array_merge( Thrive_Utils::create_attributes( $attr ), $style );
		$video_attrs = $video_type->parse_video_attributes( $video_attrs );

		return $iframe ? TCB_Utils::wrap_content( $container, 'div', '', $classes, $video_attrs ) : '';
	}

	public static function render_video_cover( $attr, $content ) {
		return $content;
	}

	/**
	 * Get the video settings from post meta
	 *
	 * @return array|bool
	 */
	public static function get_video_post_meta() {
		$attr = tve_get_post_meta( get_the_ID(), static::VIDEO_META_OPTION );

		return static::change_options_attr( $attr );
	}

	/**
	 * Return a simple array of attributes instead of a nested one
	 *
	 * @param $attr
	 *
	 * @return array|bool
	 */
	public static function change_options_attr( $attr ) {
		if ( ! is_array( $attr ) ) {
			return [];
		}
		$result = [];
		foreach ( $attr as $key => $value ) {
			if ( is_array( $value ) && $key === 'video_options' ) {
				foreach ( $value as $k => $v ) {
					if ( is_array( $v ) ) {
						$result[ $k ] = $v['value'];
					}
				}
			} else {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Generate the video thumbnail. If there is a thumbnail url, use an image, otherwise use a placeholder.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function render_thumbnail( $attr ) {
		$thumbnail_type = isset( $attr['thumbnail-type'] ) ? $attr['thumbnail-type'] : 'none';

		/* for dynamic thumbnail, use the featured image url shortcode as image background url */
		if ( $thumbnail_type === 'dynamic' ) {
			$thumbnail_url = Thrive_Shortcodes::the_post_thumbnail_url();
		} elseif ( $thumbnail_type === 'static' && ! empty( $attr['thumbnail-url'] ) ) {
			/* else, use the url that came through the shortcode */
			$thumbnail_url = $attr['thumbnail-url'];
		}
		$overlay_play_icon = '';
		if ( isset( $attr['thumbnail-icon-id'] ) ) {
			$path = '<path d="' . $attr['thumbnail-icon-path'] . '"></path>';

			/* Get the attributes needed for building the play icon */
			$svg_attr = [
				'data-id'  => $attr['thumbnail-icon-id'],
				'data-css' => $attr['thumbnail-icon-style'],
				'viewBox'  => $attr['thumbnail-icon-viewbox'],
			];
			/* Build the play icon */
			$svg               = TCB_Utils::wrap_content( $path, 'svg', '', 'tcb-icon', $svg_attr );
			$icon              = TCB_Utils::wrap_content( $svg, 'div', '', [ 'thrv_icon', 'tcb-not-editable', 'tcb-no-highlight' ] );
			$overlay_play_icon = TCB_Utils::wrap_content( $icon, 'span', '', 'overlay_play_button' );
		}
		/* if the URL is empty, use a placeholder in the editor and nothing on the frontend */
		if ( empty( $thumbnail_url ) ) {
			if ( Thrive_Utils::is_inner_frame() ) {
				$thumbnail = static::render_placeholder();
			} else {
				$thumbnail = TCB_Utils::wrap_content( '', 'div', 'video-overlay', 'video_overlay' );
			}
		} else {
			$thumbnail = Thrive_Utils::return_part( '/inc/templates/parts/dynamic-video-overlay.php', [
				'thumbnail-url'       => $thumbnail_url,
				'thumbnail-play-icon' => $overlay_play_icon ? $overlay_play_icon : '',
			] );
		}

		return $thumbnail;
	}

	/**
	 * Return true if the video has a thumbnail, false if it doesn't.
	 *
	 * @param $attr
	 *
	 * @return bool
	 */
	public static function has_thumbnail( $attr ) {
		$thumbnail_type = isset( $attr['thumbnail-type'] ) ? $attr['thumbnail-type'] : 'none';

		/* the video has a thumbnail if the thumbnail type is dynamic or if it's static and there is an url */

		return $thumbnail_type === 'dynamic' || ( $thumbnail_type === 'static' && ! empty( $attr['thumbnail-url'] ) );
	}

	public static function render_placeholder() {
		if ( is_editor_page_raw( true ) ) {
			$content = Thrive_Utils::return_part( '/inc/templates/parts/dynamic-video-placeholder.php' );
		} else {
			$content = '';
		}

		return $content;
	}
}
