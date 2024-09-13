<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP\Parsers;

use Thrive\Theme\AMP\Parser;

use Thrive_DOM_Helper as DOM_Helper;

use TCB_Utils;
use TCB_Post_List_Shortcodes as Post_List_Shortcodes;
use TCB_Post_List_Content as Post_List_Content;

use DOMDocument;
use DOMElement;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Post_List {

	const IDENTIFIER_CLASS = 'tcb-post-list';

	/* Hook into the early parse action. Called dynamically from class-parser.php */
	public static function init() {
		add_action( Parser::EARLY_PARSE_HOOK, [ __CLASS__, 'parse_post_lists' ] );
	}

	/**
	 * @param DOMDocument $dom
	 */
	public static function parse_post_lists( $dom ) {

		foreach ( $dom->getElementsByTagName( 'div' ) as $node ) {
			/* @var DOMElement $node */
			if ( static::is_post_list( $node ) ) {
				static::replace_post_list( $node, $dom );
			}
		}
	}

	/**
	 * Replace the given post list with a simple AMP-compatible version
	 *
	 * @param DOMElement  $node
	 * @param DOMDocument $dom
	 */
	public static function replace_post_list( $node, $dom ) {
		$article_ids = static::parse_articles( $node );

		if ( ! empty( $article_ids ) ) {
			$new_list_html = static::build_list_from_article_ids( $article_ids );

			DOM_Helper::replace_node_with_string( $node, $new_list_html, $dom );
		}
	}

	/**
	 * Gather the post IDs from the articles and return them in an array
	 *
	 * @param DOMElement $node
	 *
	 * @return array
	 */
	public static function parse_articles( $node ) {
		$article_ids = [];

		foreach ( $node->getElementsByTagName( 'article' ) as $article_node ) {
			/* @var DOMElement $article_node */
			$id_string = $article_node->getAttribute( 'id' );
			/* $id_string has the form: 'post-456' and we extract the number */
			if ( ! empty( $id_string ) && preg_match( '/post-(\d+)/m', $id_string, $m ) ) {
				if ( ! empty( $m[1] ) ) {
					$article_ids[] = (int) $m[1];
				}
			}
		}

		return $article_ids;
	}

	/**
	 * Simulate a loop and create the new list out of simple articles
	 *
	 * @param array $article_ids
	 *
	 * @return string
	 */
	public static function build_list_from_article_ids( $article_ids ) {
		global $post;

		/* save a reference to the current global $post so we can restore it afterwards */
		$current_post = $post;

		$articles = '';

		foreach ( $article_ids as $id ) {
			$post = get_post( $id );

			$articles .= static::render_article();
		}

		$post = $current_post;

		return TCB_Utils::wrap_content( $articles, 'div', 'tcb-post-list' );
	}

	/**
	 * @param $post
	 *
	 * @return string
	 */
	public static function render_article() {
		$post_title = Post_List_Shortcodes::the_title( [ 'inline' => 1, 'url' => 1 ] );
		$post_title = TCB_Utils::wrap_content( $post_title, 'h2' );

		$featured_image = Post_List_Shortcodes::post_thumbnail( [
			'type-url' => 'post_url',
			'size'     => 'medium_large',
		] );

		$tcb_read_more_link = TCB_Utils::wrap_content(
			__( 'Read More', 'thrive-theme' ),
			'a',
			'',
			'more-link',
			[ 'href' => get_the_permalink() . '#more-' . get_the_ID() ]
		);

		$excerpt = html_entity_decode( Post_List_Content::get_excerpt( $tcb_read_more_link ) );

		/* remove the dots because we add our own read more link */
		$excerpt = str_replace( '[…]', '', $excerpt );

		return TCB_Utils::wrap_content( $featured_image . $post_title . $excerpt . '<hr/>', 'article' );
	}

	/**
	 * Check if the classes of this node contain the post list class
	 *
	 * @param DOMElement $node
	 *
	 * @return bool
	 */
	public static function is_post_list( $node ) {
		return DOM_Helper::has_class( $node, static::IDENTIFIER_CLASS );
	}
}
