<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Post_List_Content
 */
class TCB_Post_List_Content {

	/* the default read more is blank */
	public static $default_read_more = '';

	/**
	 * Some patterns to remove in the case of excerpt and words.
	 *
	 * @var array
	 */
	static public $patterns_to_remove = array(
		'/___TVE_SHORTCODE_RAW__(.+?)__TVE_SHORTCODE_RAW___/s',
		'#__CONFIG_group_edit__(.+?)__CONFIG_group_edit__#',
		'#__CONFIG_colors_palette__(.+?)__CONFIG_colors_palette__#',
		'#__CONFIG_local_colors__(.+?)__CONFIG_local_colors__#',
		/* some custom menus are saved without the __CONFIG_widget_menu key (in symbols, this is a problem) - we need to get rid of all json-encoded string from the content */
		'#{"menu_id"(.+)(true|false|null|"|\'|\d|]|})}#',
		/* Custom menu - REST API version - encoded json */
		'@{&#8220;menu_id&#8221;(.+)(true|false|null|&#8221;|\d|]|})}@',
		/* the audio shortcode doesn't really behave well, so it's better to just remove it */
		'#<audio(.*?)><\/audio>#',
	);

	/**
	 * Return the post content after calculating excerpt / word counts.
	 *
	 * @param $attr
	 *
	 * @return mixed
	 */
	public static function get_content( $attr ) {
		global $tcb_read_more_link;
		global $post;

		/**
		 * Do a wp-query post-data setup for the current post;
		 * If this is a post list and the loop finishes, we call setup_postdata again after the loop ( @see TCB_Post_List::render() ) in order to restore things to normal
		 */
		setup_postdata( $post );

		/* if the post content is not inside the post list, it means this is called from TTB -> return the regular full content without changing anything */
		if ( TCB_Post_List::is_outside_post_list_render() ) {
			$element_class = 'tcb-post-content' . ' ' . TCB_SHORTCODE_CLASS;
			if ( is_editor_page() ) {
				$element_class .= ' tcb-no-delete tcb-selector-no_clone';
			}

			$content = TCB_Post_List_Shortcodes::shortcode_function_content( 'the_content' );

			$content .= wp_link_pages(
				array(
					'before'      => '<nav class="post-nav-links bg-light-background" aria-label="' . esc_attr__( 'Page', 'thrive-cb' ) . '">
				<span class="label">' . __( 'Pages:', 'thrive-cb' ) . '</span>',
					'after'       => '</nav>',
					'link_before' => '<span class="page-number">',
					'link_after'  => '</span>',
					'echo'        => false,
				)
			);

			/**
			 * Allows other functionality to hook here
			 *
			 * Used in TA Visual builder to completely hide post content from frontend if is empty
			 *
			 * @param string $post_content
			 * @param string $content
			 */
			return apply_filters( 'tcb_get_post_content', TCB_Post_List_Shortcodes::before_wrap( [
				'content' => $content,
				'tag'     => 'section',
				'class'   => $element_class,
			], $attr ), trim( $content ) );
		}

		$attr = array_merge( static::default_attr(), $attr );

		$is_editor_page = TCB_Utils::in_editor_render();

		if ( $is_editor_page ) {
			/* we don't add read more inside the editor so we can better handle the post_content and modify the read more text */
			$tcb_read_more_link = ' ';
		} else {
			$tcb_read_more_link = TCB_Utils::wrap_content( $attr['read_more'], 'a', '', 'more-link', array( 'href' => get_the_permalink() . '#more-' . get_the_ID() ) );
		}

		/* if the post has a tcb read more element, display the content only until there. filter is called in functions.php */
		add_filter( 'tcb_force_excerpt', '__return_true' );

		/* remove the 'Continue reading' text added by the TAr more tag */
		add_filter( 'the_content_more_link', [ __CLASS__, 'the_content_more_link_filter' ] );

		$is_rest = TCB_Utils::is_rest();

		/* if this is a REST request, we have to manually add the tve_editor_content to the content filter in order for it to cut off the excerpt */
		if ( $is_rest && ! doing_filter( 'the_content' ) ) {
			add_filter( 'the_content', 'tve_editor_content' );

			/*
			 * This filter is added along with the content filter on the frontend, so we add it here too
			 * It seems to remove duplicated content ( if this is not added, the content is shown twice in the editor )
			 */
			add_filter( 'the_content', 'tve_clean_wp_editor_content', - 100 );
		}

		switch ( $attr['size'] ) {
			case 'excerpt':
				$content = static::get_excerpt( $tcb_read_more_link );
				break;
			case 'content':
				$content = static::get_full_content( $attr );
				break;
			case 'words':
			default:
				$content = static::get_words( $attr['words'], $tcb_read_more_link );
		}

		/* remove all the filters that we added before */
		if ( $is_rest && ! doing_filter( 'the_content' ) ) {
			remove_filter( 'the_content', 'tve_editor_content' );
			remove_filter( 'the_content', 'tve_clean_wp_editor_content', - 100 );
		}

		remove_filter( 'tcb_force_excerpt', '__return_true' );
		remove_filter( 'the_content_more_link', [ __CLASS__, 'the_content_more_link_filter' ] );

		$classes = 'tcb-post-content ' . TCB_SHORTCODE_CLASS . ( $is_editor_page ? ' tcb-post-content-placeholder' : '' );

		return TCB_Post_List_Shortcodes::before_wrap( [
			'content' => $content,
			'tag'     => 'section',
			'class'   => $classes,
		], $attr );
	}

	/**
	 * Get the styles used by the current post.
	 *
	 * @param $post_id
	 *
	 * @return string
	 */
	public static function get_post_styles( $post_id ) {
		$global_css_prefix = tcb_selection_root();
		$selector          = apply_filters( 'tcb_global_styles_selector', $global_css_prefix );

		$post_css = tve_get_post_meta( $post_id, 'tve_custom_css', true );

		return sprintf( '<style type="text/css" class="tve_custom_style">%s</style>', str_replace( $global_css_prefix, $selector, $post_css ) );
	}

	/**
	 * Get the excerpt.
	 *
	 * @param $tcb_read_more_link
	 *
	 * @return mixed|string|string[]|null
	 */
	public static function get_excerpt( $tcb_read_more_link ) {
		/* we add this filter so functions will use our read more text and we remove it later */
		add_filter( 'excerpt_more', [ __CLASS__, 'excerpt_more_filter' ] );

		$content = TCB_Post_List_Shortcodes::shortcode_function_content( 'the_excerpt' );

		$tcb_enabled = (int) get_post_meta( get_the_ID(), 'tcb_editor_enabled', true );

		/* if there's no content for this post, and it is a WP Post, and we're in the editor, take the original WP content */
		if ( empty( $content ) && is_editor_page() && ! $tcb_enabled ) {
			global $post;
			$content = $post->post_content;
		}

		$content = static::replace_tve_shortcodes( $content );

		$content = str_replace( ']]>', ']]&gt;', $content );

		$content = static::filter_content( $content );

		/* replace these manually in case preg_replace only found the start or the end _config_ */
		$content = str_replace( [ '___TVE_SHORTCODE_RAW__', '__TVE_SHORTCODE_RAW___' ], '', $content );

		$content = strip_shortcodes( $content );

		/* make sure we always display the read more link, and only add it if it doesn't exist in the content already */
		if (
			strpos( $content, $tcb_read_more_link ) === false &&
			/* also check the 'decoded' version of the content in case '...' was turned into '&#8230;' */
			strpos( str_replace( '&#8230;', '...', $content ), $tcb_read_more_link ) === false
		) {
			/* if there are no paragraphs in the content, just add the read more at the end */
			if ( strpos( $content, '<\/p>' ) === false ) {
				$content .= $tcb_read_more_link;
			} else {
				/* if there are paragraphs, insert the read more before the closing of the last paragraph tag */
				$content = preg_replace( '/<\/p>$/', " {$tcb_read_more_link}$0", trim( $content ) );
			}
		}

		remove_filter( 'excerpt_more', [ __CLASS__, 'excerpt_more_filter' ] );
		$tcb_read_more_link = null;

		return $content;
	}

	/**
	 * Get the full content and prepend the styles used for this content.
	 *
	 * @param $attr
	 *
	 * @return string
	 */
	public static function get_full_content( $attr ) {
		$content = TCB_Post_List_Shortcodes::shortcode_function_content( 'the_content', [ $attr['read_more'] ] );

		$content_text = trim( str_replace( $attr['read_more'], '', strip_tags( $content ) ) );
		if ( empty( $content_text ) ) {
			if ( TCB_Editor()->is_inner_frame() ) {
				$content = TCB_Utils::wrap_content( __( 'No Post Content', 'thrive-cb' ), 'p' );
			}
		} else {
			$queried_object = get_queried_object();
			$post_id        = get_the_ID();

			/* get the styles used by this post, but only if it isn't on the post with the post list that we're rendering */
			if ( ( ! empty( $queried_object ) && property_exists( $queried_object, 'ID' ) && $post_id !== $queried_object->ID ) || ! is_singular() ) {
				$content = static::get_post_styles( $post_id ) . $content;
			}
		}

		return $content;
	}

	/**
	 * Get the first x words of the content.
	 *
	 * @param $words
	 * @param $tcb_read_more_link
	 *
	 * @return mixed|string
	 */
	public static function get_words( $words, $tcb_read_more_link ) {
		$tcb_enabled = (int) get_post_meta( get_the_ID(), 'tcb_editor_enabled', true );

		/* if architect is enabled, the 'plain text' content is kept in post_content, so we use that */
		if ( $tcb_enabled ) {
			global $post;
			$content = $post->post_content;
		}

		/* if the content is empty / architect is not enabled, get the content through some filters */
		if ( empty( $content ) ) {
			$content = TCB_Post_List_Shortcodes::shortcode_function_content( 'the_content' );
		}

		$content = strip_shortcodes( $content );

		$content = static::replace_tve_shortcodes( $content );

		$content = str_replace( ']]>', ']]&gt;', $content );

		$content = static::filter_content( $content );

		$content = wp_trim_words( $content, $words, '' ) . ' ' . $tcb_read_more_link;

		$content = TCB_Utils::wrap_content( $content, 'p' );

		return $content;
	}

	/**
	 * Remove some config text.
	 *
	 * @param $content
	 *
	 * @return string|string[]|null
	 */
	public static function filter_content( $content ) {

		foreach ( static::$patterns_to_remove as $pattern ) {
			$content = preg_replace( $pattern, '', $content );
		}

		return $content;
	}

	/**
	 * The TVE shortcodes have no brackets (they are stored with __CONFIG__ around them), so they cant be modified with the wordpress shortcode functions.
	 * (the tve_thrive_shortcodes() function cannot be used because the regex only works if the config is wrapped in something, and that doesn't happen in the editor)
	 *
	 * @param $content
	 *
	 * @return string|string[]|null
	 */
	public static function replace_tve_shortcodes( $content ) {
		global $tve_thrive_shortcodes;

		foreach ( $tve_thrive_shortcodes as $shortcode => $callback ) {
			$content = preg_replace( '/__CONFIG_' . $shortcode . '__(.+?)__CONFIG_' . $shortcode . '__/', '', $content );
		}

		return $content;
	}

	/**
	 * Add read more text to the excerpt
	 *
	 * @param $read_more
	 *
	 * @return mixed
	 */
	public static function excerpt_more_filter( $read_more ) {
		global $tcb_read_more_link;

		if ( $tcb_read_more_link !== null ) {
			$read_more = ' ' . $tcb_read_more_link;
		}

		return $read_more;
	}

	/**
	 * Remove the WP more tag. (it is added in post-template.php, line 349)
	 *
	 *
	 * @return string
	 */
	public static function the_content_more_link_filter() {
		return '';
	}

	/**
	 * Default attributes for post list available with filter
	 *
	 * @return mixed|void
	 */
	private static function default_attr() {
		return apply_filters( 'tcb_post_list_content_default_attr', [
			'size'      => 'words',
			'read_more' => static::$default_read_more,
			'words'     => 12,
		] );
	}

}
