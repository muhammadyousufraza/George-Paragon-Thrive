<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

use Thrive\Theme\Integrations\WooCommerce\Main as Woo;

/**
 * Class Thrive_Breadcrumbs
 */
class Thrive_Breadcrumbs {
	const SEPARATOR_CLASS = 'thrive-breadcrumb-separator';

	/* this is 80 by default, if you change this, remember to also change it in css ( breadcrumbs.scss) */
	const LEAF_CHAR_NR        = 80;
	const MOBILE_LEAF_CHAR_NR = 25;

	public static $labels = [];

	/**
	 * Register breadcrumb strings for WPML translations
	 */
	public static function register_translate_strings() {
		$default_labels = static::get_default_labels();

		foreach ( $default_labels as $key => $label ) {
			do_action( 'wpml_register_single_string', 'Breadcrumbs', ucfirst( $key ), $label );
		}
	}

	/**
	 * Return breadcrumb with the specific options.
	 * Implemented following schema.org syntax from https://developers.google.com/search/docs/data-types/breadcrumb with Microdata
	 *
	 * @param array $attr
	 *
	 * @return string $breadcrumb
	 */
	public static function render( $attr = [] ) {
		$classes = [ 'thrive-breadcrumbs', THRIVE_WRAPPER_CLASS ];

		/* Add extra-classes (eg.: responsive classes) */
		if ( ! empty( $attr['class'] ) ) {
			$classes = array_merge( $classes, explode( ' ', $attr['class'] ) );
		}

		/* if this is hidden, return ( or add hide classes inside ) */
		if ( ! thrive_post()->is_element_visible( 'breadcrumbs', $classes ) ) {
			return '';
		}

		$attr = array_merge( [
			'css'                   => '',
			'enable-truncate-chars' => 1,
			'separator-type'        => 'character',
			'leaf-chars-d'          => static::LEAF_CHAR_NR,
			'leaf-chars-t'          => static::MOBILE_LEAF_CHAR_NR,
			'leaf-chars-m'          => static::MOBILE_LEAF_CHAR_NR,
		], $attr );

		static::$labels = thrive_skin()->get_breadcrumbs_labels();

		$separator = static::get_separator( $attr );

		/* each breadcrumb item needs a meta tag with the current index; the indexing starts from 0, not 1 */
		$index = 1;

		$root = static::get_home_root( $index );
		$path = static::get_path( $index, $attr );

		$content = implode( $separator, array_merge( $root, $path ) );

		if ( Thrive_Utils::is_inner_frame() ) {
			$attr = Thrive_Utils::create_attributes( $attr );
		} else {
			$attr = [
				/* 'null' means that this attribute is added as a key without a value ( like 'nofollow', 'checked', 'disabled' ) */
				'itemscope'                  => null,
				'itemtype'                   => 'https://schema.org/BreadcrumbList',
				'data-enable-truncate-chars' => $attr['enable-truncate-chars'],
				'data-css'                   => $attr['css'],
			];
		}

		return TCB_Utils::wrap_content( $content, 'ul', '', $classes, $attr );
	}

	/**
	 * Returns the wrapped breadcrumb separator HTML - can be a character, an icon, or nothing ( empty wrapper ).
	 *
	 * @param array $attr
	 * @param array $class
	 *
	 * @return string
	 */
	public static function get_separator( $attr, $class = [] ) {
		switch ( $attr['separator-type'] ) {
			case 'character':
				/* for character, get the separator character from the data, or add '/' by default */
				$arrow_content = empty( $attr['separator'] ) ? ' / ' : $attr['separator'];
				break;
			case 'icon':
				/* for icon, get the icon html from the template */
				$arrow_content = isset( $attr['icon-name'] ) ? Thrive_Shortcodes::get_icon_by_name( $attr['icon-name'] ) : '';
				break;
			default:
				/* if nothing is set, leave this empty */
				$arrow_content = '';
		}

		$class[] = static::SEPARATOR_CLASS;

		return TCB_Utils::wrap_content( $arrow_content, 'li', '', $class );
	}

	/**
	 * Get the 'Home' part of the breadcrumbs.
	 *
	 * @param int $index
	 *
	 * @return array
	 */
	public static function get_home_root( &$index ) {
		$items = [];

		$in_editor = Thrive_Utils::is_inner_frame();

		if ( get_option( 'show_on_front' ) === 'page' ) {
			$homepage_id = get_option( 'page_on_front' );

			if ( empty( $homepage_id ) ) {
				$homepage_url = get_option( 'home' );
			} else {
				$homepage_url = get_page_link( $homepage_id );
			}
		} else {
			$homepage_url = get_option( 'home' );
		}

		$items[] = static::create_item( $index, static::$labels['home'], $homepage_url, [ 'home', $in_editor ? 'home-label' : '' ] );

		/* If the post type has an archive, add that one also to the breadcrumbs */
		if ( is_singular() ) {
			$post_type = get_post_type();

			if ( $post_type === 'post' ) {
				$posts_page_id = get_option( 'page_for_posts' );
				if ( ! empty( $posts_page_id ) ) {
					$items[] = static::create_item( $index, static::$labels['blog'], get_page_link( $posts_page_id ), [
						'home',
						$in_editor ? 'blog-label' : '',
					] );
				}
			} else {
				$post_type_object = get_post_type_object( $post_type );

				if ( $post_type_object !== null && $post_type_object->has_archive ) {
					$items[] = static::create_item( $index, $post_type_object->labels->archives, get_post_type_archive_link( $post_type ) );
				}
			}
		}

		/**
		 * Filter root breadcrumbs items - maybe add some more, depending on custom logic
		 *
		 * @param array   $items
		 * @param int     $index
		 * @param boolean $in_editor Whether or not this is rendered inside the editor frame
		 *
		 * @return array
		 */
		$items = apply_filters( 'thrive_theme_breadcrumbs_root_items', $items, $index, $in_editor );
		$index = count( $items ) + 1;

		return $items;
	}

	/**
	 * Create a breadcrumb path or leaf item with the given attributes.
	 * Adds schema.org structured data to all the breadcrumb items
	 *
	 * @param int    $index
	 * @param string $text
	 * @param string $href
	 * @param array  $class
	 *
	 * @return string
	 */
	public static function create_item( &$index, $text = '', $href = '', $class = [] ) {

		/* all the items have href except the leaf */
		if ( empty( $href ) ) {
			$item = TCB_Utils::wrap_content( $text, 'span', '', '', [
				'itemprop'      => 'name',
				/* the leaf also gets a data-selector */
				'data-selector' => '.thrive-breadcrumb-leaf span',
			] );
		} else {
			$text = TCB_Utils::wrap_content( $text, 'span', '', '', [
				'itemprop' => 'name',
			] );

			$item = TCB_Utils::wrap_content( $text, 'a', '', '', [
				'itemprop' => 'item',
				'href'     => $href,
			] );
		}

		/* meta tag that contains the position of the element */
		$meta = TCB_Utils::wrap_content( '', 'meta', '', '', [
			'content'  => $index ++,
			'itemprop' => 'position',
		] );

		$class[] = 'thrive-breadcrumb thrv_wrapper';

		if ( empty( $href ) ) {
			$class[] = 'thrive-breadcrumb-leaf';
		} else {
			$class[] = 'thrive-breadcrumb-path';
		}

		return TCB_Utils::wrap_content( $item . $meta, 'li', '', $class, [
			/* 'null' means that this attribute is added as a key without a value ( like 'nofollow', 'checked', 'disabled' ) */
			'itemscope' => null,
			'itemprop'  => 'itemListElement',
			'itemtype'  => 'https://schema.org/ListItem',
		] );
	}

	/**
	 * Get the breadcrumbs depending on what type of content we're on.
	 *
	 * @param int   $index
	 * @param array $attr
	 *
	 * @return array
	 */
	public static function get_path( &$index, $attr = [] ) {
		global $post;

		$in_editor = Thrive_Utils::is_inner_frame();

		$items = [];

		if ( is_singular() ) {
			$post_type = get_post_type();

			/**
			 * Taxonomies that can be displayed in the breadcrumbs
			 *
			 * @param array  $allowed_taxonomies - by default we only display the category
			 * @param string $post_type
			 *
			 * @return array
			 */
			$allowed_taxonomies = apply_filters( 'thrive_theme_allowed_taxonomies_in_breadcrumbs', [ 'category' ], $post_type );

			$taxonomies_in_breadcrumbs = array_intersect( $allowed_taxonomies, get_object_taxonomies( $post_type ) );

			foreach ( $taxonomies_in_breadcrumbs as $taxonomy ) {

				$terms = get_the_terms( $post->ID, $taxonomy );
				/* check if the option to show categories is toggled */
				$show_terms = empty( $attr['show-categories'] ) ? false : $attr['show-categories'];

				if ( ! empty( $terms ) && isset( $terms[0] ) ) {
					$link  = get_term_link( $terms[0], $taxonomy );
					$class = [ 'thrive-breadcrumb-category' ];

					if ( $show_terms === 'show' ) {
						$category_item = static::create_item( $index, $terms[0]->name, $link, $class );
					} elseif ( $in_editor || TCB_Utils::is_rest() ) {
						$class [] = 'thrive-hidden-element';

						$category_item = static::create_item( $index, $terms[0]->name, $link, $class );
					}

					if ( ! empty( $category_item ) ) {
						$items[] = $category_item;
					}
				}
			}

			/* add all the parents of this page as breadcrumbs */
			if ( $post->post_parent ) {
				$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
				foreach ( $ancestors as $ancestor ) {
					if ( get_post( $ancestor ) ) {
						/**
						 * Filters the href of a breadcrumb post link
						 *
						 * @param string $href
						 * @param int    $ancestor the ID of current breadcrumb post item
						 */
						$href    = apply_filters( 'thrive_breadcrumb_post_link', get_page_link( $ancestor ), $ancestor );
						$items[] = static::create_item( $index, get_the_title( $ancestor ), $href );
					}
				}
			}

			/* add the post itself as the breadcrumb leaf */
			$items[] = static::create_item( $index, get_the_title() );
		} else {
			$title = static::get_archive_title();

			if ( ! empty( $title ) ) {
				$classes = [];
				if ( $in_editor ) {
					$classes[] = Woo::is_shop() ? 'shop-label' : 'archive-label';
				}

				$items[] = static::create_item( $index, $title, '', $classes );
			}
		}

		return $items;
	}

	/**
	 * Customized method for getting the archive title
	 *
	 * @return mixed|void
	 */
	public static function get_archive_title() {

		/**
		 * Allows shortcutting the conditional logic from below, if anything else other than NULL is returned
		 *
		 * @param null|string $title
		 */
		$title = apply_filters( 'thrive_theme_breadcrumbs_archive_title', null );

		if ( $title !== null ) {
			return $title;
		}

		$title = '';

		if ( Woo::is_shop() ) {
			$title = static::$labels['shop'];
		} elseif ( is_search() ) {
			$title = static::$labels['search'];
		} elseif ( is_author() ) {
			$title = static::$labels['author'] . '<span class="vcard">' . get_the_author() . '</span>';
		} elseif ( is_category() ) {
			$title = esc_html__( 'Category', 'thrive-theme' ) . ': ' . single_cat_title( '', false );
		} elseif ( is_tag() ) {
			$title = esc_html__( 'Tag', 'thrive-theme' ) . ': ' . single_tag_title( '', false );
		} elseif ( is_year() ) {
			$title = esc_html__( 'Year', 'thrive-theme' ) . ': ' . get_the_date( _x( 'Y', 'yearly archives date format' ) );
		} elseif ( is_month() ) {
			$title = esc_html__( 'Month', 'thrive-theme' ) . ': ' . get_the_date( _x( 'F Y', 'monthly archives date format' ) );
		} elseif ( is_day() ) {
			$title = esc_html__( 'Day', 'thrive-theme' ) . ': ' . get_the_date( _x( 'F j, Y', 'daily archives date format' ) );
		} elseif ( is_post_type_archive() ) {
			$title = static::$labels['archive'] . post_type_archive_title( '', false );
		} elseif ( is_tax() ) {
			$queried_object = get_queried_object();
			if ( $queried_object ) {
				$tax = get_taxonomy( $queried_object->taxonomy );
				/* translators: Taxonomy term archive title. 1: Taxonomy singular name, 2: Current taxonomy term. */
				$title = sprintf( __( '%1$s: %2$s' ), $tax->labels->singular_name, single_term_title( '', false ) );
			}
		} elseif ( is_home() && ! is_front_page() ) {
			$title = static::$labels['blog'];
		}

		return apply_filters( 'get_the_archive_title', $title );
	}

	/**
	 * Default labels for breadcrumbs
	 *
	 * @return array
	 */
	public static function get_default_labels() {
		$labels = [
			'home'    => __( 'Home', 'thrive-theme' ),
			'blog'    => __( 'Blog', 'thrive-theme' ),
			'search'  => __( 'Search', 'thrive-theme' ),
			'author'  => __( 'Author: ', 'thrive-theme' ),
			'archive' => __( 'Archives: ', 'thrive-theme' ),
		];

		$labels = apply_filters( 'thrive_theme_breadcrumbs_labels', $labels );

		return $labels;
	}
}
