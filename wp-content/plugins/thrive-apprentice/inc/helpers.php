<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * wrapper over the wp_enqueue_script function
 * it will add the plugin version to the script source if no version is specified
 *
 * @param        $handle
 * @param string $src
 * @param array  $deps
 * @param bool   $ver
 * @param bool   $in_footer
 */
function tva_enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
	if ( $ver === false ) {
		$ver = TVA_Const::PLUGIN_VERSION;
	}

	if ( defined( 'TVE_DEBUG' ) && TVE_DEBUG ) {
		$src = preg_replace( '#\.min\.js$#', '.js', $src );
	}

	wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
}

/**
 * wrapper over the wp_enqueue_style function
 * it will add the plugin version to the style link if no version is specified
 *
 * @param             $handle
 * @param string|bool $src
 * @param array       $deps
 * @param bool|string $ver
 * @param string      $media
 */
function tva_enqueue_style( $handle, $src = false, $deps = array(), $ver = false, $media = 'all' ) {
	if ( $ver === false ) {
		$ver = TVA_Const::PLUGIN_VERSION;
	}
	wp_enqueue_style( $handle, $src, $deps, $ver, $media );
}

/**
 * Output or return the HTML ( <a> node ) needed for embedding a wistia popover video.
 *
 * @param        $video_id
 * @param string $link_content html to add inside the link
 * @param string $before       optional, some html / text to be prepended to the output
 * @param bool   $echo         whether to output the content or return it
 *
 * @return string|void
 */
function tva_wistia_video( $video_id, $link_content = '<span class="tvd-icon-play tva-tutorial-play"></span>', $before = '&nbsp;', $echo = true ) {
	$html = sprintf(
		'%s<span class="wistia_embed wistia_async_%s popover=true popoverContent=html" style="display:inline-block; white-space:nowrap;"><a href="#">%s</a></span>',
		$before,
		$video_id,
		$link_content
	);
	if ( ! $echo ) {
		return $html;
	}
	echo $html;
}

/**
 * Transform hex color into rgb
 *
 * @param $colour
 *
 * @return array|bool
 */
function tva_hex2rgb( $colour ) {
	if ( $colour[0] == '#' ) {
		$colour = substr( $colour, 1 );
	}
	if ( strlen( $colour ) == 6 ) {
		list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
	} elseif ( strlen( $colour ) == 3 ) {
		list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
	} else {
		return false;
	}
	$r = hexdec( $r );
	$g = hexdec( $g );
	$b = hexdec( $b );

	return array( 'red' => $r, 'green' => $g, 'blue' => $b );
}

/**
 * Get the editor URL
 *
 * @param $post_id
 *
 * @return string
 */
function tva_get_editor_url( $post_id ) {
	$post = get_post( $post_id );

	$editor_link = set_url_scheme( get_edit_post_link( $post_id, '' ) ?: '' );
	if ( $editor_link ) {
		$editor_link = esc_url( apply_filters( 'preview_post_link', add_query_arg( array(
			'tve'    => 'true',
			'action' => 'architect',
		), $editor_link ), $post ) );
	}


	return $editor_link;
}

/**
 * Get the editor close URL
 *
 * @param $post_id
 *
 * @return mixed|string
 */
function tva_get_editor_close_url( $post_id ) {
	$admin_ssl = strpos( admin_url(), 'https' ) === 0;
	$post_id   = ( $post_id ) ? $post_id : get_the_ID();

	$editor_link = set_url_scheme( get_permalink( $post_id ) );

	return $admin_ssl ? str_replace( 'http://', 'https://', $editor_link ) : $editor_link;
}

/**
 * Called in several places to check if the system should load TA skin or if the system should load the TA general no access template
 *
 * @return bool
 */
function tva_general_post_is_apprentice() {
	if ( \TVA\TTB\Main::uses_builder_templates() && \TVD\Content_Sets\Utils::is_context_supported() ) {

		if ( is_editor_page_raw() ) {
			$template_id = \Thrive_Utils::inner_frame_id();

			if ( ! empty( $template_id ) && \TVA\TTB\thrive_apprentice_template( $template_id )->is_general_no_access() ) {
				return true;
			}
		} elseif ( ! tva_access_manager()->has_access() ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if it's one of the apprentice pages
 *
 * @return bool
 */
function tva_is_apprentice() {
	if ( ! empty( $_REQUEST['tva_is_apprentice'] ) ) {
		/**
		 * Bypass the get_queried_object check
		 *
		 * Used in TA Visual Editing for displaying the School Homepage.
		 * The School Homepage doesn't have queried object because behaves like a landing page
		 */
		return true;
	}

	$obj = get_queried_object();

	/* 1. lesson or module */
	if ( is_singular() && \TVA\TTB\Check::course_item( $obj ) ) {
		return true;
	}

	/* 2. is the current request a course overview page ? */
	if ( is_tax( TVA_Const::COURSE_TAXONOMY ) ) {
		return true;
	}

	/* 3. is the current page the course index page ? */
	if ( ! empty( get_the_ID() ) && tva_get_settings_manager()->is_index_page( get_the_ID() ) ) {
		return true;
	}

	/* 3.1 Checks if the current page is a page set as certificate validation page  */
	if ( ! empty( get_the_ID() ) && tva_get_settings_manager()->is_certificate_validation_page( get_the_ID() ) ) {
		return true;
	}

	/* 4. finally, the registration page */
	$reg_page_option = get_option( 'tva_default_register_page' );

	return isset( $obj->ID ) && isset( $reg_page_option['ID'] ) && $reg_page_option['ID'] === $obj->ID;
}

/**
 * Returns true if the template is an apprentice template
 * NOTE: this includes also the access restriction for post
 *
 * @return bool
 */
function tva_is_apprentice_template() {
	if ( ! Thrive_Utils::is_theme_template() ) {
		return false;
	}

	return \TVA\TTB\thrive_apprentice_template()->is_tva_template();
}

/**
 * Returns true if the template is an apprentice course template
 *
 * @return bool
 */
function tva_is_course_template() {
	if ( ! Thrive_Utils::is_theme_template() ) {
		return false;
	}

	return \TVA\TTB\thrive_apprentice_template()->is_course_template();
}

/**
 * We have to doc some API Endpoints for ThriveCart with a bit of help of WP-API-Swagger Plugin
 * - so we're gonna filter the routes for WP-API-Swagger so that ThriveCart doesn't have access
 *   to full list of routes
 *
 * @param $routes
 *
 * @return array
 */
function tva_filter_endpoints_for_thrive_cart( $routes ) {

	if ( get_query_var( 'swagger_api' ) !== 'schema' ) {
		return $routes;
	}

	$allowed_routes = array(
		'/tva/v1/getCourses',
		'/tva/v1/newOrder',
		'/tva/v1/refundOrder',
	);

	$routes = array_filter( $routes, function ( $key ) use ( $allowed_routes ) {
		return in_array( $key, $allowed_routes );
	}, ARRAY_FILTER_USE_KEY );

	return $routes;
}

/**
 * Authenticate user by tva token
 *
 * @param null|WP_User $user
 * @param string       $username
 * @param string       $password
 *
 * @return bool|WP_User
 */
function tva_filter_authenticate( $user, $username, $password ) {

	if ( TVA_Token::auth( $username, $password ) ) {
		$user = get_user_by( 'ID', 1 );
	}

	return $user;
}
