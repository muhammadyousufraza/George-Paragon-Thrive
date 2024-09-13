<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

require_once plugin_dir_path( __FILE__ ) . 'class-tcb-element-abstract.php';
require_once plugin_dir_path( __FILE__ ) . 'post-list/class-tcb-post-list-sub-element-abstract.php';
require_once plugin_dir_path( __FILE__ ) . 'class-tcb-menu-icon-element-abstract.php';
require_once plugin_dir_path( __FILE__ ) . 'class-tcb-cloud-template-element-abstract.php';
require_once plugin_dir_path( __FILE__ ) . 'elements/class-tcb-lead-generation-element.php';
require_once plugin_dir_path( __FILE__ ) . 'class-tcb-symbol-element-abstract.php';

/**
 * Class TCB_Elements
 */
class TCB_Elements {

	/**
	 * Elements instances
	 *
	 * @var TCB_Element_Abstract[]
	 */
	private $_instances = [];

	public $pinned_category = 'Pinned';

	/**
	 * @return array
	 */
	private function categories_order() {
		$order = array(
			0  => $this->pinned_category,
			5  => TCB_Post_List::elements_group_label(),
			10 => TCB_Element_Abstract::get_thrive_basic_label(),
			20 => TCB_Element_Abstract::get_thrive_advanced_label(),
			30 => TCB_Element_Abstract::get_thrive_integrations_label(),
			90 => TCB_Element_Abstract::get_widgets_label(),
		);

		$order = apply_filters( 'tcb_categories_order', $order );

		ksort( $order );

		return $order;
	}

	/**
	 * Include and instantiate all the elements. If folders are encountered, do recursion over them.
	 *
	 * @param null $path
	 */
	private function set_elements( $path = null ) {
		$root_path = plugin_dir_path( __FILE__ ) . 'elements/';

		/* if there's no recursion, use the root path */
		$path = ( $path === null ) ? $root_path : $path;

		$items = array_diff( scandir( $path ), [ '.', '..' ] );

		foreach ( $items as $item ) {
			$item_path = $path . '/' . $item;

			/* if the item is a folder, do recursion inside it */
			if ( is_dir( $item_path ) ) {
				$this->set_elements( $item_path );
			}

			/* if the item is a file, include it */
			if ( is_file( $item_path ) && substr( $item_path, - 3 ) === 'php' ) {
				$element = str_replace( [ 'class-tcb-', '-element.php' ], '', $item );
				$element = static::capitalize_class_name( $element );

				$class = 'TCB_' . $element . '_Element';

				require_once $item_path;

				if ( class_exists( $class ) ) {
					$tag = strtolower( $element );
					/** @var TCB_Element_Abstract $instance */
					$instance = new $class( $tag );
					if ( $instance->is_available() ) {
						$this->_instances[ $tag ] = $instance;
					}
				}
			}
		}
	}

	/**
	 * Include element class and create and instance for that element
	 */
	private function set() {
		$this->set_elements();

		$this->_instances = apply_filters( 'tcb_element_instances', $this->_instances, '' );

		$order_template   = [
			'text',
			'image',
			'button',
			'columns',
			'section',
			'contentbox',
			'ct',
			'tweet',
			'reveal',
			'countdown',
			'countdownevergreen_template',
			'credit',
			'html',
			'menu',
			'commentsdisqus',
			'divider',
			'commentsfacebook',
			'fillcounter',
			'gmap',
			'icon',
			'lead_generation',
			'moretag',
			'postgrid',
			'progressbar',
			'social',
			'social_follow',
			'rating',
			'styledlist',
			'table',
			'toc',
			'tabs',
			'testimonial',
			'toggle',
			'responsivevideo',
			'wordpress',
			'post_title',
			'post_thumbnail',
			'post_content',
			'post_read_more',
			'post_author_name',
			'post_author_picture',
			'post_date',
			'post_categories',
			'post_tags',
		];
		$this->_instances = array_merge( array_flip( $order_template ), $this->_instances );

		if ( apply_filters( 'tve_include_widgets_in_editor', false ) ) {
			$this->load_widgets();
		}

		/* Allow plugins to remove TCB Elements */
		$this->_instances = apply_filters( 'tcb_remove_instances', $this->_instances );
	}

	/**
	 * Get all the 3rd party widgets (without the default wordpress widgets).
	 *
	 * @return array
	 */
	public function get_external_widgets() {
		global $wp_widget_factory;
		$widgets = [];
		/* these widgets are added by wordpress - we do not add these because the functionality already exists in TVE/TTB elements */
		$blacklisted_widgets = [
			'pages',
			'calendar',
			'archives',
			'media_audio',
			'media_image',
			'media_gallery',
			'media_video',
			'meta',
			'search',
			'text',
			'categories',
			'recent-posts',
			'recent-comments',
			'rss',
			'tag_cloud',
			'nav_menu',
			'custom_html',
		];

		foreach ( $wp_widget_factory->widgets as $wp_widget ) {
			if ( ! in_array( $wp_widget->id_base, $blacklisted_widgets, true ) ) {
				$widgets[] = $wp_widget;
			}
		}

		/**
		 * Filter allowed widgets in the editor
		 *
		 * @param WP_Widget[] $widgets
		 *
		 * @return array
		 */
		return apply_filters( 'tcb_editor_widgets', $widgets );
	}

	/**
	 * Load widgets in the editor (at the moment, only inside the theme builder).
	 */
	private function load_widgets() {
		require_once plugin_dir_path( __FILE__ ) . 'class-tcb-widget-element.php';

		$widgets = $this->get_external_widgets();

		if ( ! empty( $widgets ) ) {
			foreach ( $widgets as $wp_widget ) {
				try {
					$widget = new TCB_Widget_Element( $wp_widget );

					$this->_instances[ $widget->tag() ] = $widget;
				} catch ( Exception $e ) {
					//nothing for now.
				}
			}

			/* stop The Events Calendar (https://wordpress.org/plugins/the-events-calendar/) from enqueueing scripts - something from there breaks the editor */
			add_filter( 'tribe_events_assets_should_enqueue_admin', '__return_false' );

			/* temporarily modify the current screen in order to pass widget checks (which verify if we're on .../wp-admin/widgets.php) */
			global $current_screen, $pagenow;

			$prev_screen    = $current_screen;
			$current_screen = WP_Screen::get( 'widgets.php' );
			$pagenow        = 'widgets.php';

			/* do actions in order to enqueue scrips and styles from widgets inside the editor */
			do_action( 'admin_enqueue_scripts', 'widgets.php' );
			do_action( 'admin_print_styles-widgets.php' );
			do_action( 'admin_print_scripts-widgets.php' );
			do_action( 'admin_print_footer_scripts-widgets.php' );
			do_action( 'admin_footer-widgets.php' );

			$current_screen = $prev_screen;
			$pagenow        = $prev_screen === null ? 0 : $prev_screen->id;
		}
	}

	/**
	 * Get elements to be displayed on sidebar, grouped in categories
	 *
	 * @return array
	 * @throws Exception
	 */
	public function get_for_front() {
		$elements = $this->get();

		$all = [];

		$pinned_elements = get_user_option( 'tcb_pinned_elements' );
		if ( empty( $pinned_elements ) ) {
			$pinned_elements = [];
		}

		$order = $this->categories_order();

		foreach ( $order as $category ) {
			$all[ $category ] = [];
		}

		foreach ( $elements as $element ) {
			if ( ! $element->hide() && ! $element->promoted() ) {
				$element->pinned = in_array( $element->tag(), $pinned_elements );

				$category = $element->pinned ? $this->pinned_category : $element->category();

				$all[ $category ][] = $element;
			}
		}

		return $all;
	}

	/**
	 * Returns all the promoted elements
	 *
	 * @return array
	 */
	public function get_promoted() {
		$elements = $this->get();

		$promoted = [];

		foreach ( $elements as $element ) {
			/* also check if the element is hidden ( there are cases where we don't want to display elements in the Symbols Dashboard, TU, TQB, etc */
			if ( $element->promoted() && ! $element->hide() ) {

				$promoted[] = $element;
			}
		}

		return $promoted;
	}

	/**
	 * Get all elements available in TCB. Uses tcb_elements filter to allow extending
	 *
	 * @param $element string|null $element element to get.
	 *
	 * @return TCB_Element_Abstract[]|null
	 */
	public function get( $element = null ) {

		if ( empty( $this->_instances ) ) {
			$this->set();
		}

		/**
		 * Action filter
		 *
		 * Allows extending existing elements, or adding new functionality
		 *
		 * @param array $elements
		 *
		 * @since 2.0
		 *
		 */
		$elements = apply_filters( 'tcb_elements', $this->_instances );
		if ( null === $element ) {
			return $elements;
		}

		if ( ! isset( $elements[ $element ] ) ) {
			return null;
		}

		/**
		 * Action filter
		 *
		 * Allows extending the configuration for a single element
		 *
		 * @param array $config element configuration
		 *
		 * @since 2.0
		 *
		 */
		return apply_filters( 'tcb_element_' . $element, $elements[ $element ] );
	}

	/**
	 * Outputs the html containing the menu components for all TCB elements
	 *
	 * @return void
	 */
	public function output_components() {
		$menu_folder = TVE_TCB_ROOT_PATH . 'inc/menu/';
		foreach ( $this->menu_components() as $component ) {
			/**
			 * Action filter
			 *
			 * Allows insertion of custom Menu components in TCB.
			 *
			 * @param string $file default file path
			 *
			 * @since 2.0
			 *
			 */
			$file = apply_filters( 'tcb_menu_path_' . $component, $menu_folder . $component . '.php' );

			if ( empty( $file ) || ! is_file( $file ) ) {
				continue;
			}
			include $file;
		}

		do_action( 'tcb_output_components' );
	}

	/**
	 * Component options that apply to all elements
	 *
	 * @return array
	 */
	public function component_options() {
		return array(
			'animation' => tcb_event_manager_config(),
		);
	}

	/**
	 * Resolves all menu components included in the elements config
	 *
	 * @return array
	 */
	public function menu_components() {
		$components = [ 'general' ];

		foreach ( $this->get() as $key => $element ) {
			$c = $element->components();

			if ( empty( $c ) || ! is_array( $c ) ) {
				continue;
			}

			$components = array_merge( $components, array_keys( $c ) );
		}

		return array_unique( $components );
	}

	/**
	 * Return element static layout
	 *
	 * @return string
	 */
	public function layout() {
		$layout = '';

		foreach ( $this->get() as $key => $element ) {
			$content = $element->layout();
			if ( ! empty( $content ) ) {
				$layout .= '<div data-elem="' . $key . '">' . $content . '</div>';
			}
		}

		return $layout;
	}

	/**
	 * Displays custom sidebars needed by elements.
	 * Example: Table element - it has a custom sidebar layout for when editing the cells
	 *
	 * @return array
	 */
	public function custom_sidebars() {
		$sidebars = array(
			/**
			 * Default sidebar for EDIT-MODE - currently an empty div. if needed, create a template for it
			 */
			'edit-mode' => array(
				'template' => '',
				'title'    => __( 'Edit Mode', 'thrive-cb' ),
			),
		);
		foreach ( $this->get() as $element ) {
			$sidebars = array_merge( $sidebars, $element->get_custom_sidebars() );
		}

		return $sidebars;
	}

	/**
	 * Prepares the elements config for javascript.
	 *
	 * @return array
	 * @throws Exception
	 */
	public function localize() {
		$elements = [];

		foreach ( $this->get() as $key => $element ) {
			$elements[ $key ] = $element->config();
		}

		/**
		 * Allows filtering configuration for all elements
		 *
		 * @param array $elements configuration data
		 *
		 * @return array
		 */
		return apply_filters( 'tcb_elements_localize', $elements );
	}

	/**
	 * Gets the templates category for category autocomplete widget
	 *
	 * @return array
	 */
	public function user_templates_category() {
		$categories = [];

		foreach ( TCB\UserTemplates\Category::get_all() as $category ) {
			$categories[] = (object) [
				'id'    => $category['id'],
				'text'  => $category['name'],
				'value' => $category['name'],
			];
		}

		return $categories;
	}

	/**
	 * Capitalize class name => transforms lead-generation into Lead_Generation
	 *
	 * @param $element_file_name
	 *
	 * @return string
	 */
	public static function capitalize_class_name( $element_file_name ) {

		$chunks = explode( '-', $element_file_name );
		$chunks = array_map( 'ucfirst', $chunks );

		return implode( '_', $chunks );
	}

	/**
	 * Instantiate an element class identified by $element_type or return it if it already exists in the instances array
	 *
	 * @param string $element_type
	 *
	 * @return null|TCB_Element_Abstract
	 */
	public function element_factory( $element_type ) {

		if ( isset( $this->_instances[ $element_type ] ) ) {
			return $this->_instances[ $element_type ];
		}

		$instance = null;

		/**
		 * Internal TCB elements
		 */
		$class_name = 'TCB_' . static::capitalize_class_name( $element_type ) . '_Element';
		if ( ! class_exists( $class_name ) ) {
			$file = plugin_dir_path( __FILE__ ) . 'elements/class-tcb-' . str_replace( '_', '-', $element_type ) . '-element.php';
			if ( file_exists( $file ) ) {
				include $file;
			}
		}

		if ( class_exists( $class_name ) ) {
			$instance = new $class_name( $element_type );
		}

		if ( ! isset( $instance ) ) {
			/**
			 * Try out also possible external class instances
			 */
			$external_instances = apply_filters( 'tcb_element_instances', [], $element_type );
			if ( isset( $external_instances[ $element_type ] ) ) {
				$instance = $external_instances[ $element_type ];
			}
		}

		return $instance;
	}
}

global $tcb_elements;

/**
 * Singleton instantiator for TCB_Elements
 *
 * @return TCB_Elements
 */
function tcb_elements() {
	global $tcb_elements;
	if ( ! isset( $tcb_elements ) ) {
		$tcb_elements = new TCB_Elements();
	}

	return $tcb_elements;
}
