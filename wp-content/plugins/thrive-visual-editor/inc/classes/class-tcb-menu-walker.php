<?php

/**
 * Custom walker for the Menu element
 *
 * Class TCB_Menu_Walker
 */
class TCB_Menu_Walker extends Walker_Nav_Menu {
	/**
	 * Menu descriptions for Mega Menus
	 *
	 * @var string
	 */
	public static $mega_description_template = '<div class="thrv_text_element tve-no-drop">%s</div>';

	/**
	 * MegaMenu images - raw template
	 *
	 * @var string
	 */
	public static $mega_image_template = '<span class="tcb-mm-image menu-item-{ITEM_ID}-img tve_editable{CLS}" {DYNAMIC_ATTR} style=\'background-image:{IMG}\'></span>';
	/**
	 * Regular Menu images - raw template
	 *
	 * @var string
	 */
	public static $menu_item_image_template = '<span class="tcb-menu-item-image menu-item-{ITEM_ID}-img tve_editable{CLS}" {DYNAMIC_ATTR} style=\'background-image:{IMG}\'></span>';

	/**
	 * unlinked selector: <LI> element
	 */
	const UNLINKED_LI = '.menu-item-{ID}';

	/**
	 * unlinked selector: <UL> element
	 */
	const UNLINKED_UL = '.menu-item-{ID}-ul';

	/**
	 * unlinked identifier: <a> (megamenu item)
	 */
	const UNLINKED_A = '.menu-item-{ID}-a';

	/**
	 * unlinked identifier: megamenu dropdown column
	 */
	const UNLINKED_COL = '.menu-item-{ID}.lvl-1';

	/**
	 * unlinked identifier: megamenu dropdown
	 */
	const UNLINKED_DROP = '.menu-item-{ID}-drop';

	/**
	 * unlinked identifier: megamenu image
	 */
	const UNLINKED_IMG = '.menu-item-{ID}-img';
	/**
	 * CSS class to add to unlinked items
	 */
	const CLS_UNLINKED = 'tcb-excluded-from-group-item';

	/**
	 * Active state for menu items
	 */
	const CLS_ACTIVE = 'tve-state-active';

	/**
	 * flag indicating where or not this is a editor page
	 *
	 * @var boolean
	 */
	protected $is_editor_page;

	/**
	 * @var WP_Post current menu item
	 */
	protected $current_item;

	/**
	 * Stores icon data
	 *
	 * @var array
	 */
	protected $icons = [];

	protected $positional_selectors = false;

	/**
	 * Cached version of a placeholder HTML for an image
	 *
	 * @var string
	 */
	protected $image_placeholder = '';

	/**
	 * Holds the index of the current item as rendered in the <ul> parent
	 *
	 * @var int
	 */
	protected $current_item_index = 0;

	public function __construct() {
		$icons                      = $this->get_config( 'icon', [] );
		$this->positional_selectors = tcb_custom_menu_positional_selectors();

		$template = tcb_template( 'elements/menu-item-icon.phtml', null, true, 'backbone' );
		foreach ( (array) $icons as $k => $icon_id ) {
			if ( $icon_id ) {
				$this->icons[ $k ] = str_replace( '_ID_', $icon_id, $template );
			}
		}
	}

	/**
	 * Gets HTML for an icon corresponding to a <li>
	 *
	 * @param WP_Post $item
	 * @param int     $current_level
	 *
	 * @return string
	 */
	protected function icon( $item, $current_level ) {
		$parent_field = $this->db_fields['parent'];

		/* unlinked id */
		$id = '.menu-item-' . $item->ID;
		if ( $this->positional_selectors && ! empty( $item->_tcb_pos_selector ) ) {
			/* try unlinked positional selectors */
			$id = $item->_tcb_pos_selector;
		}

		if ( isset( $this->icons[ $id ] ) ) {
			return $this->icons[ $id ];
		}

		if ( $this->get_menu_type() === 'mega' && isset( $this->icons["{$id}-a"] ) ) {
			return $this->icons["{$id}-a"];
		}

		/* check top level */
		if ( empty( $item->$parent_field ) ) {
			return isset( $this->icons['top'] ) ? $this->icons['top'] : '';
		}

		/* check for mega menu icons */
		if ( $this->get_menu_type() === 'mega' && $current_level > 0 ) {
			$key = 1 === $current_level ? 'mega_main' : 'mega_sub';

			return isset( $this->icons[ $key ] ) ? $this->icons[ $key ] : '';
		}

		/**
		 * default : submenu item
		 */
		return isset( $this->icons['sub'] ) ? $this->icons['sub'] : '';
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass $args   An object of wp_nav_menu() arguments.
	 *
	 * @see   Walker::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
			$n = '';
		} else {
			$t = "\t";
			$n = "\n";
		}
		$indent = str_repeat( $t, $depth );

		// Default class.
		$classes = $this->get_menu_type() === 'regular' ? [ 'sub-menu' ] : [];

		/**
		 * Filters the CSS class(es) applied to a menu list element.
		 *
		 * @param array    $classes The CSS classes that are applied to the menu `<ul>` element.
		 * @param stdClass $args    An object of `wp_nav_menu()` arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 *
		 * @since 4.8.0
		 *
		 */
		$classes    = apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth );
		$classes [] = 'menu-item-' . $this->current_item->ID . '-ul';
		if ( $this->is_out_of_group_editing( $this->current_item->ID, self::UNLINKED_UL ) ) {
			$classes [] = self::CLS_UNLINKED;
		}
		$wrap_start = '';

		if ( 0 === $depth && $this->get_menu_type() === 'mega' ) {
			$drop_classes = [
				'tcb-mega-drop-inner',
				'thrv_wrapper',
				'menu-item-' . $this->current_item->ID . '-drop',
			];
			if ( $this->is_out_of_group_editing( $this->current_item->ID, self::UNLINKED_DROP ) ) {
				$drop_classes [] = self::CLS_UNLINKED;
			}
			$wrap_start = '<div class="tcb-mega-drop"><div class="' . implode( ' ', $drop_classes ) . '">';

			/* check if this dropdown has masonry */
			/**
			 * masonry if:
			 *    unlinked and specific masonry set on the unlinked config
			 * OR masonry specified on the default config
			 */
			if ( $this->get_config( 'layout/default' ) === 'masonry' && $this->get_config( "layout/drop-{$this->current_item->ID}" ) !== 'grid' ) {
				$classes [] = 'tcb-masonry';
			}
		}

		$class_names = ' class="' . esc_attr( join( ' ', $classes ) ) . '"';

		$output .= "{$wrap_start}{$n}{$indent}<ul$class_names>{$n}";
	}

	public function end_lvl( &$output, $depth = 0, $args = [] ) {
		parent::end_lvl( $output, $depth, $args );
		if ( 0 === $depth && $this->get_menu_type() === 'mega' ) {
			$output .= '</div></div>';
		}
	}

	/**
	 *
	 * Checks if an element has been unlocked from group editing ( is edited separately )
	 * If item ID can't be retrieved, it is assumed to be linked
	 * Spec can be any of the self::UNLINKED_* constants
	 *
	 * @param WP_Post|string $item
	 * @param string         $spec
	 *
	 * @return bool
	 * @see self::UNLINKED_* constants
	 *
	 */
	public function is_out_of_group_editing( $item, $spec ) {

		if ( is_numeric( $item ) ) {
			$item_id = $item;
		} elseif ( ! empty( $item->ID ) ) {
			$item_id = $item->ID;
		} else {
			return false;
		}

		if ( $this->positional_selectors && $spec === self::UNLINKED_LI && isset( $item->_tcb_pos_selector ) ) {
			return $this->get_config( "unlinked/{$item->_tcb_pos_selector}" );
		}
		$unlinked_class = str_replace( '{ID}', $item_id, $spec );

		return $this->get_config( "unlinked/{$unlinked_class}" ) !== null;
	}

	/**
	 * @inheritDoc
	 */
	public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
		if ( $depth === 0 ) {
			$this->current_item_index ++;
		}
		if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
			$t = '';
		} else {
			$t = "\t";
		}
		$menu_type = $this->get_menu_type();

		/**
		 * Render the logo before rendering the menu item thats after it
		 */

		if ( $depth === 0 && $menu_type === 'regular' && ( $this->current_item_index === $this->get_logo_split_breakpoint() + 1 ) ) {
			$output .= $this->get_logo_html();
		}

		$indent       = ( $depth ) ? str_repeat( $t, $depth ) : '';
		$classes      = empty( $item->classes ) ? [] : (array) $item->classes;
		$link_attr    = [];
		$link_classes = 'mega' === $menu_type && $depth > 0 ? [ "menu-item menu-item-{$item->ID} menu-item-{$item->ID}-a" ] : [];
		if ( 0 !== $depth && 'mega' === $menu_type && $this->is_editor_page() ) {
			$link_classes[] = 'thrv_wrapper';
		}
		if ( $this->is_out_of_group_editing( $item->ID, self::UNLINKED_A ) ) {
			$link_classes [] = self::CLS_UNLINKED;
		}
		/* Add the responsive classes on the menu item */
		$responsive_attributes = (array) $this->get_config( 'responsive_attributes', [] );

		$parent_field = $this->db_fields['parent'];
		$item_id      = '.menu-item-' . $item->ID;
		$itemKey      = '';
		/* Get the item key of this item */
		if ( ! empty( $responsive_attributes[ $item_id ] ) ) {
			$itemKey = $item_id;
		} else if ( $menu_type === 'mega' && ! empty( $responsive_attributes["{$item_id}-a"] ) ) {
			$itemKey = "{$item_id}-a";
		} else if ( empty( $item->$parent_field ) ) {
			$itemKey = 'top';
		} else if ( $menu_type === 'mega' && $depth > 0 ) {
			$itemKey = 1 === $depth ? 'mega_main' : 'mega_sub';
		} else {
			$itemKey = 'sub';
		}

		$responsive_attrs = '';
		/* Build the responsive data attributes */
		if ( ! empty( $responsive_attributes[ $itemKey ] ) ) {
			$devices = [ 'desktop', 'tablet', 'mobile' ];
			foreach ( $devices as $device ) {
				if ( ! empty( $responsive_attributes[ $itemKey ][ $device ] ) ) {
					$responsive_attrs .= 'data-item-display-' . $device . '="' . $responsive_attributes[ $itemKey ][ $device ] . '"';
				}
			}
		}

		/* handle link classes for menu images */
		if ( 'mega' === $menu_type || 'regular' === $menu_type ) {
			$item_image        = $this->get_config( "images/{$item->ID}", [] );
			$image_placeholder = (array) $this->get_config( 'img_settings', [] );
			$container_class   = 'mega' === $menu_type && 1 === $depth ? 'tcb-mm-container' : 'tcb-menu-item-container';
			$place             = $this->get_image_placement( $item, $depth );
			if ( ! empty( $item_image ) ) {
				$link_classes [] = $container_class;
				$link_classes [] = isset( $item_image['o'] ) ? "tcb--{$item_image['o']}" : 'tcb--row';
			} elseif ( 1 === $depth && 'mega' === $menu_type && ! empty( $image_placeholder['enabled'] ) ) {
				$link_classes [] = $container_class . ' ' . ( isset( $image_placeholder['o'] ) ? "tcb--{$image_placeholder['o']}" : 'tcb--row' );
				$item_image      = array(
					'placeholder' => true,
					'o'           => isset( $image_placeholder['o'] ) ? $image_placeholder['o'] : '',
				);
			} elseif ( ! empty( $image_placeholder[ $place ] ) && isset( $image_placeholder[ $place ]['enabled'] ) && $image_placeholder[ $place ]['enabled'] ) {
				$link_classes [] = $container_class . ' ' . ( isset( $image_placeholder[ $place ]['o'] ) ? "tcb--{$image_placeholder[ $place ]['o']}" : 'tcb--row' );
				$item_image      = array(
					'placeholder' => true,
					'o'           => isset( $image_placeholder[ $place ]['o'] ) ? $image_placeholder[ $place ]['o'] : '',
				);
			}
		}


		/**
		 * Filters the arguments for a single nav menu item.
		 *
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param WP_Post  $item  Menu item data object.
		 * @param int      $depth Depth of menu item. Used for padding.
		 *
		 * @since 4.4.0
		 *
		 */
		$args = apply_filters( 'nav_menu_item_args', $args, $item, $depth );

		/**
		 * Filters the CSS class(es) applied to a menu item's list item element.
		 *
		 * @param array    $classes The CSS classes that are applied to the menu item's `<li>` element.
		 * @param WP_Post  $item    The current menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 *
		 * @since 3.0.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 */
		$classes = apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth );

		// make sure these are always included
		$classes[] = 'menu-item-' . $item->ID;
		$classes[] = 'lvl-' . $depth;
		if ( ! empty( $GLOBALS['tve_menu_font_class'] ) ) {
			$classes[] = $GLOBALS['tve_menu_font_class'];
		}
		if ( $this->is_editor_page() && ( 0 === $depth || $menu_type === 'regular' ) ) {
			$classes[] = 'thrv_wrapper';
		}
		if ( $this->is_out_of_group_editing( $item, self::UNLINKED_LI ) || $this->is_out_of_group_editing( $item->ID, self::UNLINKED_COL ) ) {
			$classes [] = self::CLS_UNLINKED;
		}

		$top_cls = (array) $this->get_config( 'top_cls', [] );
		if ( ! empty( $top_cls ) ) {
			$unlinked_key = ! empty( $item->_tcb_pos_selector ) ? $item->_tcb_pos_selector : '.menu-item-' . $item->ID;
			$is_unlinked  = ! empty( $this->get_config( "unlinked/$unlinked_key", [] ) );

			if ( isset( $top_cls[ $unlinked_key ] ) && $is_unlinked ) {
				$classes [] = $top_cls[ $unlinked_key ];
			} elseif ( ! empty( $top_cls['main'] ) ) {
				$classes [] = $top_cls['main'];
			}
		}
		if ( ! $this->is_editor_page() && in_array( 'current-menu-item', $classes ) ) {
			$classes []      = self::CLS_ACTIVE;
			$link_classes [] = self::CLS_ACTIVE;
		}

		/* event actions */
		$events = $this->get_config( "actions/{$item->ID}" );
		if ( empty( $events ) ) {
			$events = ! empty( $item->thrive_events ) ? $item->thrive_events : '';
		}

		if ( $events ) {
			$link_classes [] = 'tve_evt_manager_listen tve_et_click';
		}

		$class_names = ' class="' . esc_attr( join( ' ', $classes ) ) . '"';

		/**
		 * Filters the ID applied to a menu item's list item element.
		 *
		 * @param string   $menu_id The ID that is applied to the menu item's `<li>` element.
		 * @param WP_Post  $item    The current menu item.
		 * @param stdClass $args    An object of wp_nav_menu() arguments.
		 * @param int      $depth   Depth of menu item. Used for padding.
		 *
		 * @since 3.0.1
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 */
		$id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth );
		$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

		$output .= $indent . '<li' . $id . $class_names . ' data-id="' . $item->ID . '" ' . $responsive_attrs . ' >';

		$link_attr['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
		$link_attr['target'] = ! empty( $item->target ) ? $item->target : '';
		$link_attr['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
		$link_attr['href']   = ! empty( $item->url ) ? $item->url : '';

		if ( 0 !== $depth && $menu_type !== 'regular' && $this->is_editor_page() ) {
			$link_classes[] = 'thrv_wrapper';
		}
		if ( $this->is_out_of_group_editing( $item->ID, self::UNLINKED_A ) ) {
			$link_classes [] = self::CLS_UNLINKED;
		}

		/**
		 * Filters the HTML attributes applied to a menu item's anchor element.
		 *
		 * @param array    $link_attr {
		 *                            The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
		 *
		 * @type string    $title     Title attribute.
		 * @type string    $target    Target attribute.
		 * @type string    $rel       The rel attribute.
		 * @type string    $href      The href attribute.
		 *                            }
		 *
		 * @param WP_Post  $item      The current menu item.
		 * @param stdClass $args      An object of wp_nav_menu() arguments.
		 * @param int      $depth     Depth of menu item. Used for padding.
		 *
		 * @since 3.6.0
		 * @since 4.1.0 The `$depth` parameter was added.
		 *
		 */
		$link_attr = apply_filters( 'nav_menu_link_attributes', $link_attr, $item, $args, $depth );

		$link_attr['class'] = isset( $link_attr['class'] ) ? $link_attr['class'] : '';
		$link_attr['class'] .= ( $link_attr['class'] ? ' ' : '' ) . implode( ' ', $link_classes );
		if ( $events ) {
			$link_attr['data-tcb-events'] = $events;
		}

		$attributes = '';
		foreach ( $link_attr as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value      = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		if ( $depth > 0 && $menu_type === 'mega' ) {
			$attributes .= ' ' . $responsive_attrs . ' ';
		}

		/** This filter is documented in wp-includes/post-template.php */
		$title = apply_filters( 'the_title', $item->title, $item->ID );

		/**
		 * Filters a menu item's title.
		 *
		 * @param string   $title The menu item's title.
		 * @param WP_Post  $item  The current menu item.
		 * @param stdClass $args  An object of wp_nav_menu() arguments.
		 * @param int      $depth Depth of menu item. Used for padding.
		 *
		 * @since 4.4.0
		 *
		 */
		$title       = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );
		$item_output = $args->before . '<a' . $attributes . '>';
		if ( ! empty( $item_image ) ) {
			$item_output .= $this->build_image( $item_image, $item, $depth );

			if ( 1 === $depth && 'mega' === $menu_type ) {
				$item_output .= '<span class="tcb-mm-text">';
			}
			$args->link_after = $args->link_after . '</span>';
		}
		$item_output .= $this->icon( $item, $depth );
		$item_output .= $args->link_before . $title . $args->link_after;
		$item_output .= '</a>';
		$item_output .= $args->after;

		/**
		 * Append megamenu descriptions stored in config
		 */
		if ( 1 === $depth && $menu_type === 'mega' ) {
			$mega_description = $this->get_config( 'mega_desc' );
			if ( $mega_description ) {
				$mega_description = json_decode( base64_decode( $mega_description ), true );
				$mega_description = isset( $mega_description[ $item->ID ] ) ? $mega_description[ $item->ID ] : '';

				$item_output .= ! empty( $mega_description ) ? sprintf( static::$mega_description_template, $mega_description ) : '';
			}
		}

		/**
		 * Filters a menu item's starting output.
		 *
		 * The menu item's starting output only includes `$args->before`, the opening `<a>`,
		 * the menu item's title, the closing `</a>`, and `$args->after`. Currently, there is
		 * no filter for modifying the opening and closing `<li>` for a menu item.
		 *
		 * @param string   $item_output The menu item's starting HTML output.
		 * @param WP_Post  $item        Menu item data object.
		 * @param int      $depth       Depth of menu item. Used for padding.
		 * @param stdClass $args        An object of wp_nav_menu() arguments.
		 *
		 * @since 3.0.0
		 *
		 */
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );

		/* keep a reference to the current menu itemID */
		$this->current_item = $item;
	}

	/**
	 * Checks if the current page is the editor page
	 * Also handles the case where the CM is rendered via ajax
	 *
	 * @return boolean
	 */
	protected function is_editor_page() {
		if ( ! isset( $this->is_editor_page ) ) {
			$this->is_editor_page = ( ( wp_doing_ajax() && ! empty( $_REQUEST['action'] ) && 'tcb_editor_ajax' === $_REQUEST['action'] ) || is_editor_page() );
		}

		return $this->is_editor_page;
	}

	/**
	 * Get the menu type. empty means regular WP menu
	 *
	 * @return string
	 */
	protected function get_menu_type() {
		return $this->get_config( 'type', 'regular' );
	}

	/**
	 * @param string $key allows "/" to split fields
	 * @param null   $default
	 *
	 * @return mixed
	 */
	protected function get_config( $key, $default = null ) {
		$fields = explode( '/', $key );
		$target = $GLOBALS['tcb_wp_menu'];

		while ( $fields ) {
			/* make sure this is always an array */
			$target = (array) $target;
			$field  = array_shift( $fields );
			if ( ! isset( $target[ $field ] ) ) {
				return $default;
			}
			$target = $target[ $field ];
		}

		return $target;
	}

	/**
	 * Get a group configuration value ( "default" is stored for all linked items, $spec is stored for unlinked items )
	 * Example specification:
	 * ".img-{ID}
	 *
	 * @param string             $key
	 * @param WP_Post|string|int $item
	 * @param mixed              $default
	 *
	 * @return mixed
	 */
	protected function get_group_config( $key, $item, $default = null ) {
		if ( is_object( $item ) && $item->ID ) {
			$item = $item->ID;
		}

		return $this->get_config( "{$key}/{$item}", $this->get_config( "{$key}/default", $default ) );
	}

	/**
	 * Output the selected image for a menu item
	 *
	 * @param array   $image
	 * @param WP_Post $item
	 *
	 * @return string
	 */
	protected function build_image( $image, $item, $depth ) {
		if ( empty( $image ) ) {
			return '';
		}

		$dynamic_attr = '';

		/* If the image is dynamic, we change the url accordingly */
		if ( ! empty( $image['d'] ) ) {
			switch ( $image['d'] ) {
				case 'user':
					$image['i'] = 'url("' . tcb_dynamic_user_image_instance( get_current_user_id() )->user_avatar() . '")';
					break;
				case 'author':
					$image['i'] = 'url("' . TCB_Post_List_Author_Image::author_avatar() . '")';
					TCB_Post_List_Author_Image::author_avatar();
					break;
				case 'featured':
					/* In editor we add the placeholder, in front-end we leave the link empty*/
					$featured_img = get_the_post_thumbnail_url( get_the_ID() );
					if ( empty( $featured_img ) && is_editor_page() ) {
						$featured_img = TCB_Post_List_Featured_Image::get_default_url( get_the_ID() );
					}
					$image['i'] = 'url("' . $featured_img . '")';
					break;
				default:
					break;
			}

			$dynamic_attr = 'data-d-f="' . $image['d'] . '"';
		}

		$menu_type        = $this->get_menu_type();
		$template         = 'mega' === $menu_type && 1 === $depth ? static::$mega_image_template : static::$menu_item_image_template;
		$containter_class = 'mega' === $menu_type && 1 === $depth ? 'tcb-mm-image' : 'tcb-menu-item-image';

		$template = empty( $image['placeholder'] ) ? $template : $this->get_image_placeholder();
		$classes  = $this->is_out_of_group_editing( $item->ID, self::UNLINKED_IMG ) ? ' ' . self::CLS_UNLINKED : '';

		$background = str_replace( "'", '"', isset( $image['i'] ) ? $image['i'] : '' );

		if ( ! empty( $image['cfVariable'] ) ) {
			$background .= '; --tcb-background-custom-field:' . str_replace( "'", '"', $image['cfVariable'] );
		}

		if ( ! empty( $image['d'] ) ) {
			$classes .= ' tcb-dynamic-field-source';
		}

		if ( ! empty( $image['cf'] ) ) {
			$classes      .= ' tcb-custom-field-source';
			$dynamic_attr .= ' data-c-f-id="' . $image['d'] . '"';
		}

		if ( ! empty( $image['cfAttr'] ) ) {
			foreach ( $image['cfAttr'] as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$dynamic_attr .= 'data-' . $attr . '="' . $value . '"';
				}
			}
		}

		return str_replace(
			array( '{ITEM_ID}', '{CLS}', '{IMG}', '{ITEM_TYPE_CLS}', '{DYNAMIC_ATTR}' ),
			array( $item->ID, $classes, stripslashes( $background ), $containter_class, $dynamic_attr ),
			$template
		);
	}

	/**
	 * Get the image placeholder for a megamenu item
	 *
	 * @return string
	 */
	protected function get_image_placeholder() {
		if ( ! $this->image_placeholder ) {
			$this->image_placeholder = tcb_template( 'elements/menu-image-placeholder.phtml', null, true, 'backbone' );
		}

		return $this->image_placeholder;
	}

	/**
	 * Render the menu-logo-split, if configured
	 *
	 * @return string
	 */
	public function get_logo_html() {
		$html = '';
		$logo = $this->get_config( 'logo' );

		if ( $logo && is_array( $logo ) ) {
			$html = sprintf(
				'<li class="tcb-menu-logo-wrap %s tcb-selector-no_highlight menu-item--1" data-id="-1">%s</li>',
				static::CLS_UNLINKED,
				TCB_Logo::render_logo( $logo )
			);
		}

		return $html;
	}

	/**
	 * Get the split-point (index after the logo item should be rendered in case of Split Logo functionality
	 *
	 * @return false|float
	 */
	public function get_logo_split_breakpoint() {
		return (int) floor( $GLOBALS['tcb_wp_menu']['top_level_count'] / 2 );
	}

	public function get_image_placement( $item, $current_level ) {
		$parent_field = $this->db_fields['parent'];
		$key          = '';

		/* check top level */
		if ( empty( $item->$parent_field ) ) {
			$key = 'top';
		} else if ( $this->get_menu_type() === 'regular' ) {
			$key = 'sub';
		}
		/* check for mega menu */
		if ( $this->get_menu_type() === 'mega' && $current_level > 0 ) {
			$key = $current_level === 1 ? 'mega_main' : 'mega_sub';
		}

		return $key;
	}
}
