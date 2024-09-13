<?php

/**
 * Class TVA_Nav_Menu
 * - adds a new box for menus
 * - handles specific TA menu items
 */
class TVA_Nav_Menu {

	/**
	 * @var TVA_Nav_Menu
	 */
	protected static $instance;

	private function __construct() {

		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'add_dropdown_to_menu_item' ), 11, 4 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_nav_menu' ) );
		add_action( 'admin_init', array( $this, 'add_apprentice_panel' ) );
		add_action( 'wp_update_nav_menu_item', array( $this, 'add_link_to_post_meta' ), 9, 3 );//Before TAr
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'add_link_to_menu_item' ) );
		add_filter( 'wp_nav_menu_objects', array( $this, 'adjust_menu_items_link' ), 11, 2 );//after TAr
	}

	/**
	 * Add the 'Link To' dropdown to apprentice menu items in the case of legacy themes only
	 *
	 * @param        $item_id
	 * @param object $item - menu item
	 */
	public function add_dropdown_to_menu_item( $item_id, $item ) {
		if ( in_array( 'tva-menu-item', $item->classes ) ) {
			$page = $item->classes[1] === 'loginlogout' ? 'login' : $item->classes[1];

			ob_start();
			?>
			<?php if ( ! in_array( 'profile', $item->classes ) && ! in_array( 'logout', $item->classes ) ) : ?>
				<p class="link-to">
					<label for="edit-menu-item-link-to-<?php echo $item_id; ?>">
						<?php echo esc_html__( 'Link to', 'thrive-apprentice' ); ?><br>
						<select class="tva-link-to" name="tva_link_to[<?php echo $item_id; ?>]" id="edit-menu-item-link-to-<?php echo $item_id; ?>">
							<option <?php echo empty( $item->tva_link_to ) || $item->tva_link_to === 'page' ? 'selected="selected"' : ''; ?> value="page"> Apprentice <?php echo $page; ?> page</option>
							<option <?php echo ! empty( $item->tva_link_to ) && $item->tva_link_to === 'custom' ? 'selected="selected"' : ''; ?> value="custom"> Custom</option>
						</select>
					</label>
				</p>
				<div class="notice notice-error notice-alt inline hidden-field">
					<p></p>
				</div>
			<?php endif; ?>
			<?php
			echo ob_get_clean();
		}
	}

	/**
	 * Adds the apprentice panel with it's menu items as an option to the Add menu items accordion
	 */
	public function add_apprentice_panel() {
		add_meta_box(
			'tva_account_links',
			'Thrive Apprentice account',
			array( $this, 'show_meta_box' ),
			'nav-menus',
			'side',
			'low'
		);
	}

	/**
	 * Callback for add_meta_box function
	 */
	public function show_meta_box() {
		$tva_links = $this->_get_links();
		include dirname( __FILE__ ) . '/../views/ta-menu-list.phtml';
	}

	/**
	 * Generates default links for the apprentice pages
	 *
	 * @return array[]
	 */
	private function _get_links() {

		$home_link = home_url();
		global $wp;
		global $wp_filter;
		$member_mouse_filter     = '';
		$member_mouse_filter_key = '';

		/* Member Mouse 'logout_url' hook needed to be removed because it interfered with our functionality,
		   resulting in logout buttons having empty 'redirect_to' tags */
		if ( class_exists( 'MM_UserHooks', false ) && ! empty( $wp_filter['logout_url']->callbacks[1] ) ) {
			foreach ( $wp_filter['logout_url']->callbacks[1] as $cb_key => $callback ) {
				if ( is_array( $callback['function'] ) && $callback['function'][0] instanceof MM_UserHooks ) {
					$member_mouse_filter     = $wp_filter['logout_url']->callbacks[1][ $cb_key ];
					$member_mouse_filter_key = $cb_key;
					unset( $wp_filter['logout_url']->callbacks[1][ $cb_key ] );
				}
			}
		}

		$logout_link = wp_logout_url( home_url( add_query_arg( array(), $wp->request ) . '?ta-logout=success' ) );

		/* Re-add the Member Mouse hook */
		if ( class_exists( 'MM_UserHooks', false ) && ! empty( $member_mouse_filter ) ) {
			$wp_filter['logout_url']->callbacks[1][ $member_mouse_filter_key ] = $member_mouse_filter;
		}

		$login_page        = tva_get_settings_manager()->factory( 'login_page' )->get_link();
		$login_page_url    = ! empty( $login_page ) ? $login_page . '#tcb-login' : $home_link;
		$register_page_url = ! empty( $login_page ) ? $login_page . '#tcb-register' : $home_link;

		return array(
			'login'       => array(
				'slug'  => 'login',
				'link'  => $login_page_url,
				'label' => esc_html__( 'Log In' ),
				'page'  => $login_page_url,
			),
			'logout'      => array(
				'slug'  => 'logout',
				'link'  => $logout_link,
				'label' => esc_html__( 'Log Out' ),
			),
			'loginlogout' => array(
				'slug'  => 'loginlogout',
				'link'  => $logout_link,
				'label' => esc_html__( 'Log In' ) . ' | ' . esc_html__( 'Log Out' ),
				'page'  => $login_page_url,
			),
			'register'    => array(
				'slug'  => 'register',
				'link'  => $register_page_url,
				'label' => esc_html__( 'Register' ),
				'page'  => $register_page_url,
			),
		);
	}

	/**
	 * Singleton
	 *
	 * @return TVA_Nav_Menu
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Adds the link to option from post meta to the menu item and sets the shown when option
	 *
	 * @param $menu_item
	 *
	 * @return mixed
	 */
	public function add_link_to_menu_item( $menu_item ) {

		$menu_item->tva_link_to = get_post_meta( $menu_item->ID, '_menu_item_tva_link_to', true );

		return $menu_item;
	}

	/**
	 * Adds link-to option to post meta
	 *
	 * @param $menu_id
	 * @param $menu_item_id
	 * @param $args
	 */
	public function add_link_to_post_meta( $menu_id, $menu_item_id, $args ) {

		if ( ! get_post_meta( $menu_item_id, '_menu_item_tcb_show_when', true ) ) {
			if ( $args['menu-item-title'] === 'Log In' || $args['menu-item-title'] === 'Register' ) {
				update_post_meta( $menu_item_id, '_menu_item_tcb_show_when', 'loggedout' );
			} elseif ( $args['menu-item-title'] === 'Log Out' || $args['menu-item-title'] === 'Profile' ) {
				update_post_meta( $menu_item_id, '_menu_item_tcb_show_when', 'loggedin' );
			}
		}

		$value = 'link';

		if ( ! empty( $_REQUEST['tva_link_to'][ $menu_item_id ] ) ) {
			$value = sanitize_text_field( $_REQUEST['tva_link_to'][ $menu_item_id ] );
		}

		update_post_meta( $menu_item_id, '_menu_item_tva_link_to', $value );
	}

	/**
	 * Load specific js on menus page
	 *
	 * @param string $current_page
	 */
	public function enqueue_nav_menu( $current_page ) {

		if ( $current_page === 'nav-menus.php' ) {
			$apprentice_js_file = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? 'nav-menu.js' : 'nav-menu.min.js';
			wp_enqueue_script( 'TVA_Nav_Menu', TVA_Const::plugin_url( 'admin/includes/dist/' . $apprentice_js_file ) );
			wp_localize_script( 'TVA_Nav_Menu', 'links', array(
				$this->_get_links(),
				'home_url'     => home_url(),
				'legacy_theme' => class_exists( 'thrive_admin_custom_menu_walker', false ),
			) );
		}
	}

	/**
	 * Changes menu item url based on selection and handles title for Log In | Log Out option
	 *
	 * @param array  $menu_items
	 * @param object $args
	 *
	 * @return array of nav menu items
	 */
	public function adjust_menu_items_link( $menu_items, $args ) {

		$links = $this->_get_links();

		$slugs = array(
			'login_logout',
			'loginlogout',
			'login',
			'logout',
		);

		foreach ( $menu_items as $menu_item ) {

			if ( ! empty( $menu_item->tva_link_to ) ) {

				if ( ! empty( array_intersect( $slugs, $menu_item->classes ) ) ) { //menu item does exists in menu
					/**
					 * Enqueue required scripts if specific items in menu are loaded
					 */
					tva_enqueue_style( 'tva-logout-message-css', TVA_Const::plugin_url( 'css/logout_message.css' ) );

					$apprentice_js_file = defined( 'TVE_DEBUG' ) && TVE_DEBUG ? 'tva-menu-item-messages.js' : 'tva-menu-item-messages.min.js';
					tva_enqueue_script( 'tva-menu-item-messages', TVA_Const::plugin_url( 'js/dist/' . $apprentice_js_file ), array( 'jquery' ), false, false );
				}

				if ( ! empty( array_intersect( array( 'login_logout', 'loginlogout' ), $menu_item->classes ) ) ) {

					$titles           = explode( '|', strip_tags( $menu_item->title ) );
					$logged_in_title  = empty ( $titles ) ? '' : $titles[0];
					$titles_length    = count( $titles );
					$logged_out_title = $titles_length > 1 ? '' : $titles[0];
					$i                = 1;

					while ( $i < $titles_length ) {
						$logged_out_title .= $logged_out_title ? '|' . $titles[ $i ] : $titles[ $i ];
						$i ++;
					}

					/* Wrapp the title in the span because is lost on strip tags */
					$menu_item->title = '<span class="tve-disabled-text-inner">' . ( is_user_logged_in() ? esc_html__( trim( $logged_out_title ), 'thrive-apprentice' ) : esc_html__( trim( $logged_in_title ), 'thrive-apprentice' ) ) . '</span>';

					if ( $menu_item->tva_link_to !== 'custom' ) {
						$menu_item->url = is_user_logged_in() ? $links['loginlogout']['link'] : $links['loginlogout'][ $menu_item->tva_link_to ];
					} else {
						$menu_item->url = is_user_logged_in() ? $links['loginlogout']['link'] : $menu_item->url;
					}
				} elseif ( ! empty ( $links[ $menu_item->classes[1] ][ $menu_item->tva_link_to ] ) && $menu_item->tva_link_to !== 'custom' ) {

					$menu_item->url = $links[ $menu_item->classes[1] ][ $menu_item->tva_link_to ];
				}
			}
		}

		return $menu_items;
	}
}

