<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\AMP\Parsers;

use Thrive\Theme\AMP\Main;
use Thrive\Theme\AMP\Parser;

use TCB_Utils;
use Thrive_DOM_Helper as DOM_Helper;

use DOMDocument;
use DOMElement;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Menu {

	/* Hook into the main parse action. Called dynamically from class-parser.php */
	public static function init() {
		add_action( Parser::PARSE_HOOK, [ __CLASS__, 'parse_menus' ] );
	}

	/**
	 * Iterate and find all the menus in the content, parse each of them, and use the results to replace the menus with AMP-compatible ones
	 *
	 * @param DOMDocument $dom
	 */
	public static function parse_menus( $dom ) {
		foreach ( $dom->getElementsByTagName( 'div' ) as $index => $node ) {
			/* @var DOMElement $node */
			if ( static::is_menu( $node ) ) {
				static::replace_menu( $node, $dom, $index );
			}
		}
	}

	/**
	 * Replace the given menu with an AMP-compatible version ( a button that opens a sidebar menu )
	 *
	 * @param DOMElement  $menu_node
	 * @param DOMDocument $dom
	 * @param int         $index
	 */
	public static function replace_menu( $menu_node, $dom, $index ) {
		$new_menu_items = static::parse_menu_items( $menu_node );

		if ( ! empty( $new_menu_items ) ) {
			$new_menu = static::build_menu_from_array( $new_menu_items, $index );

			/* replace the former menu with a button that will open the menu sidebar */
			DOM_Helper::replace_node_with_string( $menu_node, $new_menu['button'], $dom );

			$sidebar_node = DOM_Helper::create_node_from_string( $new_menu['sidebar'], $dom );

			/* @var DOMElement $body_node */
			$body_node = $dom->getElementsByTagName( 'body' )->item( 0 );

			/* append the newly created sidebar menu to <body> or <wrapper> ( AMP sidebar requirement ) */
			$body_node->insertBefore( $sidebar_node, $body_node->firstChild );
		}
	}

	/**
	 * Parse the given menu element and build an array of menu items out of it, which we're going to use to rebuild this the AMP way
	 *
	 * Structure:
	 * [
	 *   0 => [ 'href' => 'x', 'text'  => 'item 1' ],
	 *   1 =>
	 *     [
	 *       'text' => 'item 2',
	 *       'items' =>
	 *           [
	 *            0 => [ 'href' => 'z', 'text'  => 'sub-item 1' ],
	 *            1 => [ 'href' => 't', 'text'  => 'sub-item 2' ],
	 *            2 => [ 'items' => [...] ],
	 *           ]
	 *     ],
	 *   2 => [ 'href' => 'y', 'text'  => 'item 3' ],
	 * ]
	 *
	 * @param DOMElement $menu_node
	 *
	 * @return array
	 */
	public static function parse_menu_items( $menu_node ) {
		$menu_items = [];

		$first_ul_node = $menu_node->getElementsByTagName( 'ul' )->item( 0 );

		if ( $first_ul_node === null ) {
			return $menu_items;
		}

		/* @var DOMElement $list_node */
		foreach ( $menu_node->getElementsByTagName( 'li' ) as $list_node ) {
			/* always iterate only over the 'first level' of items, recursive calls will take care of the other levels */
			if ( $list_node->parentNode === $first_ul_node ) {
				$class = $list_node->getAttribute( 'class' );

				if ( strpos( $class, 'menu-item-has-children' ) === false ) {
					$menu_items[] = static::get_item_attr( $list_node );
				} else {
					$menu_items[] = [
						'text'  => static::get_text( $list_node ),
						'items' => static::parse_menu_items( $list_node ),
					];
				}
			}
		}

		return $menu_items;
	}

	/**
	 * Return the text from this node.
	 *
	 * @param DOMElement $list_node
	 *
	 * @return string
	 */
	public static function get_text( $list_node ) {
		/* @var DOMElement $title_node */
		$title_node = $list_node->getElementsByTagName( 'a' )->item( 0 );

		/* @var DOMElement $text_node */
		$text_nodes = $title_node->getElementsByTagName( 'span' );

		if ( $text_nodes->length === 0 ) {
			$text = $title_node->textContent;
		} else {
			$text = $text_nodes->item( 0 )->textContent;
		}

		return $text;
	}

	/**
	 * Get some attributes from this node - link and text
	 *
	 * @param DOMElement $list_node
	 *
	 * @return array
	 */
	public static function get_item_attr( $list_node ) {
		/* @var DOMElement $link_node */
		$link_node = $list_node->getElementsByTagName( 'a' )->item( 0 );

		return [
			'text' => static::get_text( $list_node ),
			'href' => $link_node->getAttribute( 'href' ),
		];
	}

	/**
	 * For a given array of items, build a new AMP-compatible menu recursively
	 *
	 * @param array $items
	 * @param int   $index
	 *
	 * @return array
	 *
	 * For the structure of the array, @see parse_menu_items from this class
	 */
	public static function build_menu_from_array( $items, $index ) {

		$item_list = static::build_item_list_html( $items );

		$id   = "thrive-amp-sidebar-$index";
		$ul   = TCB_Utils::wrap_content( $item_list, 'ul' );
		$menu = TCB_Utils::wrap_content( static::get_close_button( $id ) . $ul, 'amp-nested-menu', '', '', [ 'layout' => 'fill' ] );

		$sidebar = TCB_Utils::wrap_content( $menu, 'amp-sidebar', $id, '', [
			'layout' => 'nodisplay',
			'style'  => 'width:200px',
			'side'   => 'right',
		] );

		$icon_html = Main::get_amp_file( 'templates/menu-icon.php' );

		$button = TCB_Utils::wrap_content( $icon_html, 'button', '', 'horizontal-menu-icon', [ 'on' => "tap:$id.open" ] );

		return [
			'button'  => $button,
			'sidebar' => $sidebar,
		];
	}

	/**
	 * @param $sidebar_id
	 *
	 * @return string
	 */
	public static function get_close_button( $sidebar_id ) {
		return TCB_Utils::wrap_content( Main::get_amp_file( 'templates/menu-close-icon.php' ),
			'button',
			'',
			'amp-nested-menu-close',
			[ 'on' => "tap:$sidebar_id.close" ]
		);
	}

	/**
	 * Generate and return a 'Back' menu item
	 *
	 * @return string
	 */
	public static function get_back_button() {
		$back_button = TCB_Utils::wrap_content( __( 'Back', 'thrive-theme' ), 'h6', '', '', [ 'amp-nested-submenu-close' => 1 ] );

		return TCB_Utils::wrap_content( $back_button, 'li' );
	}

	/**
	 * Recursive function that builds the HTML of a list of menu items
	 *
	 * @param $items
	 *
	 * @return string
	 */
	public static function build_item_list_html( $items ) {
		$content = '';

		foreach ( $items as $item ) {
			if ( empty( $item['items'] ) ) {
				$content .= static::build_item_html( $item );
			} else {
				$list_html = static::get_back_button() . static::build_item_list_html( $item['items'] ); /* call the list building function again */

				$list_html = TCB_Utils::wrap_content( $list_html, 'ul' );
				$list_html = TCB_Utils::wrap_content( $list_html, 'div', '', '', [ 'amp-nested-submenu' => null ] );

				$text_html = TCB_Utils::wrap_content( $item['text'], 'h5', '', '', [ 'amp-nested-submenu-open' => 1 ] );

				$content .= TCB_Utils::wrap_content( $text_html . $list_html, 'li' );
			}
		}

		return $content;
	}

	/**
	 * Builds a single AMP menu item
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public static function build_item_html( $item ) {
		$item_html = $item['text'];

		if ( ! empty( $item['href'] ) ) {
			$item_html = TCB_Utils::wrap_content( $item_html, 'a', '', '', [ 'href' => $item['href'] ] );
		}

		return TCB_Utils::wrap_content( TCB_Utils::wrap_content( $item_html, 'h5' ), 'li' );
	}

	/**
	 * Check if the classes of this node contain the menu classes
	 *
	 * @param DOMElement $node
	 *
	 * @return bool
	 */
	public static function is_menu( $node ) {
		return DOM_Helper::has_class( $node, 'thrv_widget_menu' ) || DOM_Helper::has_class( $node, 'thrive-theme-wp-menu' );
	}
}
