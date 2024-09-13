<?php

namespace TVD\Dashboard\Access_Manager;
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class Main {
	public static function init() {
		static::hooks();
	}

	public static function hooks() {
		add_action( 'current_screen', array( __CLASS__, 'conditional_hooks' ) );
	}

	/**
	 * Getting site's roles and the url to their users
	 *
	 * @return array
	 */
	public static function get_roles_url() {
		global $wp_roles;
		$all_roles = array();

		foreach ( $wp_roles->roles as $role_tag => $role ) {
			$role['url']            = add_query_arg( 'role', $role['name'], admin_url( 'users.php' ) );
			$role['tag']            = $role_tag;
			$role['can_edit_posts'] = isset( $role['capabilities']['edit_posts'] );
			unset( $role['capabilities'] );
			$all_roles[ $role['tag'] ] = $role;
		}

		return $all_roles;
	}

	/**
	 * Get all the products that should be included in the AM
	 *
	 * @return array
	 */
	public static function get_products() {
		$all_products = array();

		$all_products['td'] = array(
			'name'            => 'Thrive Dashboard Settings',
			'tag'             => 'td',
			'logo'            => TVE_DASH_IMAGES_URL . '/dash-logo-icon-small.png',
			'prod_capability' => TVE_DASH_CAPABILITY,
		);

		foreach ( tve_dash_get_products( false ) as $product ) {
			/* Skip old themes */
			if ( $product->get_type() === 'theme' ) {
				continue;
			}
			$tag                  = $product->get_tag();
			$all_products[ $tag ] = array(
				'name'            => $product->get_title(),
				'tag'             => $tag,
				'logo'            => $product->get_logo(),
				'prod_capability' => $product->get_cap(),
			);
		}

		return $all_products;
	}

	/**
	 * Getting plugin capabilities for each of the existing roles
	 *
	 * @param $all_roles
	 *
	 * @return array
	 */
	public static function get_roles_capabilities( $all_roles ) {
		foreach ( static::get_products() as $product ) {
			foreach ( $all_roles as $role ) {
				$wp_role = get_role( $role['tag'] );

				$all_roles[ $role['tag'] ]['products'][ $product['tag'] ] = array(
					'name'            => $product['name'],
					'tag'             => $product['tag'],
					'logo'            => $product['logo'],
					'can_use'         => $wp_role ? $wp_role->has_cap( $product['prod_capability'] ) : false,
					'is_editable'     => $product['tag'] === 'td' ? $role['tag'] !== 'administrator' && $role['can_edit_posts'] : $role['can_edit_posts'],
					'prod_capability' => $product['prod_capability'],
				);
			}
		}

		return $all_roles;
	}

	/**
	 * Hook based on the current screen
	 */
	public static function conditional_hooks() {
		if ( tve_get_current_screen_key() === 'admin_page_tve_dash_access_manager' ) {
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'tve_dash_access_manager_include_scripts' ) );
			add_action( 'admin_print_footer_scripts', array( __CLASS__, 'render_backbone_templates' ) );
		}
	}

	public static function render_backbone_templates() {
		$templates = tve_dash_get_backbone_templates( TVE_DASH_PATH . '/inc/access-manager/includes/templates', 'templates' );
		tve_dash_output_backbone_templates( $templates, '' );
	}

	public static function tve_dash_access_manager_include_scripts() {
		tve_dash_enqueue();
		include TVE_DASH_PATH . '/inc/access-manager/includes/assets/css/am-icons.svg';
		tve_dash_enqueue_style( 'tve-dash-access-manager-css', TVE_DASH_URL . '/inc/access-manager/includes/assets/css/style.css' );
		tve_dash_enqueue_script( 'tve-dash-access-manager-js', TVE_DASH_URL . '/inc/access-manager/includes/assets/dist/admin.min.js', array(
			'tve-dash-main-js',
			'jquery',
			'backbone',
		), false, true );

		wp_localize_script( 'tve-dash-access-manager-js', 'TVD_AM_CONST', array(
			'roles_properties' => static::get_roles_functionalities( static::get_roles_capabilities( static::get_roles_url() ) ),
			'baseUrl'          => get_rest_url( get_current_blog_id(), 'wp/v2/pages/' ),
		) );
	}

	/**
	 * Getting functionalities for each of the existing roles
	 *
	 * @param $all_roles
	 *
	 * @return array
	 */
	public static function get_roles_functionalities( $all_roles ) {
		$functionalities = static::get_all_functionalities();
		foreach ( $all_roles as $role ) {
			foreach ( $functionalities as $functionality ) {
				$roles_functionalities = $functionality::get_properties( $role['tag'] );

				$all_roles[ $role['tag'] ]['functionalities'][ $roles_functionalities['tag'] ] = $roles_functionalities;
			}
		}

		return $all_roles;
	}

	/**
	 * Get all functionalities from the Access Manager
	 *
	 * @return string[]
	 */
	public static function get_all_functionalities( $functionality_tag = null ) {
		$functionalities = array(
			new \TVD\Dashboard\Access_Manager\Admin_Bar_Visibility(),
			new \TVD\Dashboard\Access_Manager\Login_Redirect()
		);

		if ( $functionality_tag ) {
			$functionalities = current( array_filter( $functionalities,
				static function ( $functionality ) use ( $functionality_tag ) {
					return $functionality::get_tag() === $functionality_tag;
				} ) );
		}

		return $functionalities;
	}
}
