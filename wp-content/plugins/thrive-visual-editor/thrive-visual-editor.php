<?php

/*
Plugin Name: Thrive Architect
Plugin URI: https://thrivethemes.com
Version: 3.30
Author: <a href="https://thrivethemes.com">Thrive Themes</a>
Description: Live front end editor for your WordPress content
Text domain: thrive-cb
Domain Path: /languages
*/

defined( 'TVE_EDITOR_URL' ) || define( 'TVE_EDITOR_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'TVE_TCB_CORE_INCLUDED' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'plugin-core.php';
}

if ( ! defined( 'TVE_PLUGIN_FILE' ) ) {
	define( 'TVE_PLUGIN_FILE', __FILE__ );
}

defined( 'TVE_IN_ARCHITECT' ) || define( 'TVE_IN_ARCHITECT', true );

/**
 * Classes that should only be available when TCB is used stand-alone
 */
if ( ! class_exists( 'TCB_Post' ) ) {
	/**
	 * Case: TL v1, TCB v2
	 */
	require_once plugin_dir_path( __FILE__ ) . 'inc/classes/class-tcb-post.php';
}

/**
 * Init the UpdateCheck at init action because
 * Dashboard loads its class at plugins_loaded
 */
add_action( 'init', 'tve_update_checker' );

/**
 * admin licensing menu link
 */
add_action( 'wp_enqueue_scripts', 'tve_frontend_enqueue_scripts' );

// add filter for including the TCB meta into the search functionality - this is only required on the TCB editor
add_filter( 'posts_clauses', 'tve_process_search_clauses', null, 2 );

add_filter( 'get_the_content_limit', 'tve_genesis_get_post_excerpt', 10, 4 );

// automatically modify lightbox title if the title of the associated landing page is modified - applies ony to TCB
add_action( 'save_post', 'tve_save_post_callback' );

/* filter that allows adding custom icon packs to the "Choose icon" lightbox in the TCB editor */
add_filter( 'tcb_get_extra_icons', 'tve_landing_page_extra_icon_packs', 10, 2 );

/* filter that allows adding custom fonts to the "choose custom font" menu item */
add_filter( 'tcb_extra_custom_fonts', 'tve_get_extra_custom_fonts', 10, 2 );

/* action that fires when the custom fonts css should be included in the page */
add_action( 'tcb_extra_fonts_css', 'tve_output_extra_custom_fonts_css' );

/** fires when all plugins are loaded - used for intermediate filter setup / plugin overrides */
add_action( 'plugins_loaded', 'tve_plugins_loaded_hook' );

//after the plugin is loaded load the dashboard version file
add_action( 'plugins_loaded', 'tve_load_dash_version' );

add_action( 'wp_head', 'tve_load_custom_css', 100, 0 );

/**
 * Architect Product must be included only if Architect is active and needs to be added all the time to be able to check external capabilities for access manager
 */
add_filter( 'tve_dash_installed_products', 'tcb_add_to_dashboard_list' );

add_filter( 'tve_dash_email_data', 'tve_extend_email_tags', 10, 2 );

/**
 * Add email-tags if it is necessary for email sending
 *
 * @param array $data
 * @param array $arguments
 *
 * @return array
 */
function tve_extend_email_tags( $data, $arguments ) {
	$required_tags = [ 'page_slug', 'form_identifier' ];

	$tags = array();
	foreach ( $required_tags as $tag ) {
		if ( ! empty( $arguments[ $tag ] ) ) {
			$tags[] = $arguments[ $tag ];
		}
	}

	$data['email_tags'] = $tags;

	return $data;
}

/**
 * include the TCB saved meta into query search fields
 *
 * WordPress actually allows inserting post META fields in the search query,
 * but it will always build the clauses with AND (between post content and post meta) e.g.:
 *  WHERE (posts.title LIKE '%xx%' OR posts.post_content) AND (postsmeta.meta_key = 'tve_save_post' AND postsmeta.meta_value LIKE '%xx%')
 *
 * - we cannot use this, so we hook into the final pieces of the built SQL query - we need a solution like this:
 *  WHERE ( (posts.title LIKE '%xx%' OR posts.post_content OR (postsmeta.meta_key = 'tve_save_post' AND postsmeta.meta_value LIKE '%xx%') )
 *
 * @param array    $pieces
 * @param WP_Query $wp_query
 *
 * @return array
 */
function tve_process_search_clauses( $pieces, $wp_query ) {
	if ( empty( $pieces ) || is_admin() || ! $wp_query->is_search() ) {
		return $pieces;
	}
	/** @var wpdb $wpdb */
	global $wpdb;

	$query = '';
	$q     = $wp_query->query_vars;
	$n     = ! empty( $q['exact'] ) ? '' : '%';
	if ( ! empty( $q['search_terms'] ) ) {
		foreach ( $q['search_terms'] as $term ) {
			$term = $wpdb->esc_like( $term );

			$like  = $n . $term . $n;
			$query .= "((tve_pm.meta_key = 'tve_updated_post')";
			$query .= $wpdb->prepare( ' AND (tve_pm.meta_value LIKE %s)) OR ', $like );
		}
	}

	if ( ! empty( $query ) ) {
		// add to where clause
		$pieces['where'] = str_replace( "((({$wpdb->posts}.post_title LIKE '{$n}", "( {$query} (({$wpdb->posts}.post_title LIKE '{$n}", $pieces['where'] );

		$pieces['join'] .= " LEFT JOIN {$wpdb->postmeta} AS tve_pm ON ({$wpdb->posts}.ID = tve_pm.post_id)";

		if ( empty( $pieces['groupby'] ) ) {
			$pieces['groupby'] = "{$wpdb->posts}.ID";
		}
	}

	return ( $pieces );
}

/**
 * Handler for "get_the_content_limit" action applied by genesis themes
 *
 * Called on pages with posts list
 * If posts was created with TCB the more_element link is searched. If it is found the content before it is returned.
 * If more_element is not found the post's content added from admin is appended with TCB content then truncation is applied
 *
 * @param string $output         Truncated content post by genesis
 * @param string $content        the stripped and truncated genesis content
 * @param string $link           the read more link
 * @param int    $max_characters the maximum number of characters to truncate to
 *
 * @return string $content
 */
function tve_genesis_get_post_excerpt( $output, $content, $link, $max_characters ) {
	global $post;
	$post_id = get_the_ID();

	if ( ! tve_check_in_loop( $post_id ) ) {
		tve_load_custom_css( $post_id );
	}

	if ( ! is_singular() ) {
		$more_found          = tve_get_post_meta( get_the_ID(), 'tve_content_more_found', true );
		$content_before_more = tve_get_post_meta( get_the_ID(), 'tve_content_before_more', true );
		if ( ! empty( $content_before_more ) && $more_found ) {
			$more_link = apply_filters( 'the_content_more_link', '<a href="' . get_permalink() . '#more-' . $post->ID . '" class="more-link">' . __( 'Continue Reading', 'thrive-cb' ) . '</a>', __( 'Continue Reading', 'thrive-cb' ) );
			$content   = '<div id="tve_editor" class="tve_shortcode_editor">' . stripslashes( $content_before_more ) . $more_link . '</div>';

			return tve_restore_script_tags( tve_do_wp_shortcodes( $content ) );
		}

		$tcb_content = tve_restore_script_tags( tve_do_wp_shortcodes( stripslashes( tve_get_post_meta( get_the_ID(), 'tve_updated_post', true ) ) ) );
		if ( ! $tcb_content ) {
			return $output;
		}

		/**
		 * inherited from genesis logic
		 */
		$tcb_content = strip_tags( strip_shortcodes( $tcb_content ), apply_filters( 'get_the_content_limit_allowedtags', '<script>,<style>' ) );

		$tcb_content = trim( preg_replace( '#<(s(cript|tyle)).*?</\1>#si', '', $tcb_content ) );

		// append the original genesis content
		$tcb_content .= $content;
		$tcb_content = genesis_truncate_phrase( $tcb_content, $max_characters );
		$tcb_content = sprintf( '<p>%s %s</p>', $tcb_content, $link );

		return $tcb_content;
	}

	return $output;
}

/**
 * integration with WordPress SEO for page analysis.
 *
 * @param string $content WP post_content
 *
 * @return string $content
 */
function tve_yoast_seo_integration( $content ) {
	$post_id = get_the_ID();
	if ( $post_id && ! tve_is_post_type_editable( get_post_type( $post_id ) ) ) {
		return $content;
	}

	/**
	 * if the post is actually a Landing Page, we need to reset all previously saved content, as TCB content is the only one shown
	 */
	if ( tve_post_is_landing_page( $post_id ) ) {
		$content = '';
	}

	$tve_saved_content = tve_get_post_meta( get_the_ID(), "tve_updated_post" );

	$tve_saved_content = preg_replace( '#<p(.*?)>(.*?)</p>#s', '<p>$2</p>', $tve_saved_content );
	$tve_saved_content = str_replace( '<p></p>', '', $tve_saved_content );

	$content = $tve_saved_content . ' ' . $content;

	return $content;
}

/**
 * add TCB content images to the sitemap
 *
 * @param array $images
 * @param       $post_id
 *
 * @return array
 */
function tve_yoast_sitemap_images( $images, $post_id ) {
	$post_type = get_post_type( $post_id );
	$p         = get_post( $post_id );

	if ( ! tve_is_post_type_editable( $post_type, $post_id ) ) {
		return $images;
	}
	$home_url    = home_url();
	$parsed_home = parse_url( $home_url );
	$host        = '';
	$scheme      = 'http';
	if ( isset( $parsed_home['host'] ) && ! empty( $parsed_home['host'] ) ) {
		$host = str_replace( 'www.', '', $parsed_home['host'] );
	}
	if ( isset( $parsed_home['scheme'] ) && ! empty( $parsed_home['scheme'] ) ) {
		$scheme = $parsed_home['scheme'];
	}

	/**
	 * if the post is actually a Landing Page, we need to reset all other images and return just the ones setup in the landing page
	 */
	if ( tve_post_is_landing_page( $post_id ) ) {
		$images = [];
	}
	$content = tve_get_post_meta( $post_id, 'tve_updated_post' );

	if ( preg_match_all( '`<img [^>]+>`', $content, $matches ) ) {

		foreach ( $matches[0] as $img ) {
			if ( preg_match( '`src=["\']([^"\']+)["\']`', $img, $match ) ) {
				$src = $match[1];
				if ( WPSEO_Utils::is_url_relative( $src ) === true ) {
					if ( $src[0] !== '/' ) {
						continue;
					} else {
						// The URL is relative, we'll have to make it absolute
						$src = $home_url . $src;
					}
				} elseif ( strpos( $src, 'http' ) !== 0 ) {
					// Protocol relative url, we add the scheme as the standard requires a protocol
					$src = $scheme . ':' . $src;

				}

				if ( strpos( $src, $host ) === false ) {
					continue;
				}

				if ( $src != esc_url( $src ) ) {
					continue;
				}

				$image = array(
					'src' => apply_filters( 'wpseo_xml_sitemap_img_src', $src, $p ),
				);

				if ( preg_match( '`title=["\']([^"\']+)["\']`', $img, $title_match ) ) {
					$image['title'] = str_replace( [ '-', '_' ], ' ', $title_match[1] );
				}
				unset( $title_match );

				if ( preg_match( '`alt=["\']([^"\']+)["\']`', $img, $alt_match ) ) {
					$image['alt'] = str_replace( [ '-', '_' ], ' ', $alt_match[1] );
				}
				unset( $alt_match );

				$image = apply_filters( 'wpseo_xml_sitemap_img', $image, $p );

				//search in images for the $image
				$exists = false;
				foreach ( $images as $item ) {
					if ( $item['src'] === $image['src'] ) {
						$exists = true;
						break;
					}
				}

				//if already exists do not add it
				if ( $exists === false ) {
					$images[] = $image;
				}
			}
			unset( $match, $src );
		}
	}

	return $images;
}

/**
 * checks if any extra icons are attached to the page, and include those also the $icons array
 *
 * @param array $icons
 * @param int   $post_id
 *
 * @return array
 */
function tve_landing_page_extra_icon_packs( $icons, $post_id ) {
	if ( empty( $post_id ) ) {
		return $icons;
	}

	$globals = tve_get_post_meta( $post_id, 'tve_globals' );

	if ( empty( $globals['extra_icons'] ) ) {
		return $icons;
	}

	foreach ( $globals['extra_icons'] as $icon_pack ) {
		if ( empty( $icon_pack['icons'] ) ) {
			continue;
		}
		$icons = array_merge( $icons, $icon_pack['icons'] );
	}

	return $icons;

}

/**
 *
 * check if the current post / page has extra custom fonts associated and output the css needed for each
 * the extra custom fonts are enqueued from tve_enqueue_extra_resources()
 *
 * @param int $post_id
 *
 * @see tve_enqueue_extra_resources
 *
 */
function tve_output_extra_custom_fonts_css( $post_id = null ) {
	$fonts = apply_filters( 'tcb_extra_custom_fonts', [], $post_id );

	if ( empty( $fonts ) ) {
		return;
	}

	tve_output_custom_font_css( $fonts );

}

/**
 *
 * action filter that adds the custom fonts to the $fonts array for a landing page / lightbox
 *
 * @param      $fonts
 * @param null $post_id
 *
 * @return array
 */
function tve_get_extra_custom_fonts( $fonts, $post_id = null ) {
	if ( empty( $post_id ) ) {
		$post_id = get_the_ID();
	}

	if ( empty( $post_id ) ) {
		return $fonts;
	}
	$globals = tve_get_post_meta( $post_id, 'tve_globals' );
	if ( empty( $globals['extra_fonts'] ) ) {
		return $fonts;
	}

	return array_merge( $fonts, $globals['extra_fonts'] );
}

/**
 * called on the 'plugins_loaded' hook
 */
function tve_plugins_loaded_hook() {
	if ( defined( 'WPSEO_VERSION' ) ) {
		// integration with YOAST SEO
		/* version 3 removed this filter completely - this is handled from javascript from version 3.0 onwards */
		if ( version_compare( WPSEO_VERSION, '3.0', '<' ) === true ) {
			add_filter( 'wpseo_pre_analysis_post_content', 'tve_yoast_seo_integration' );
		}

		// YOAST sitemaps - add image links
		add_filter( 'wpseo_sitemap_urlimages', 'tve_yoast_sitemap_images', 10, 2 );
	}
}

/**
 * resets all stored metadata for downloaded templates
 * this can be used if some of the template files have been deleted
 */
function tve_reset_cloud_templates_meta() {
	tve_save_downloaded_templates( [] );
}

/**
 * Just initialize the PluginUpdateChecker included from dash
 */
function tve_update_checker() {
	/** plugin updates script **/
	new TVE_PluginUpdateChecker(
		'https://service-api.thrivethemes.com/plugin/update',
		__FILE__,
		'thrive-visual-editor',
		12,
		'',
		'content_builder'
	);
	/**
	 * Adding icon of the product for update-core page
	 */
	add_filter( 'puc_request_info_result-thrive-visual-editor', 'architect_set_product_icon' );
}


/**
 * Adding the product icon for the update core page
 *
 * @param $info
 *
 * @return mixed
 */

function architect_set_product_icon( $info ) {
	$info->icons['1x'] = tve_editor_css( 'images/thrive-architect-logo.png' );

	return $info;
}

/**
 * Called on plugin activation.
 * Check for minimum required WordPress version
 */
function tcb_activation_hook() {
	if ( tcb_wordpress_version_check() ) {
		if ( method_exists( \TCB\Lightspeed\Main::class, 'first_time_enable_lightspeed' ) ) {
			\TCB\Lightspeed\Main::first_time_enable_lightspeed();
		}
	} else {
		/**
		 * Dashboard not loaded yet, force it to load here
		 */
		if ( ! function_exists( 'tve_dash_show_activation_error' ) ) {
			/* Load the dashboard included in this plugin */
			tve_load_dash_version();
			tve_dash_load();
		}

		tve_dash_show_activation_error( 'wp_version', 'Thrive Architect', TCB_MIN_WP_VERSION );
	}
}

register_activation_hook( __FILE__, 'tcb_activation_hook' );

/**
 * Enables dashboard features during the ajax REST API request
 *
 * @param array $features
 *
 * @return array
 */
function tar_enable_dashboard_features( $features ) {
	/**
	 * Script manager is only active if TAr is available as a standalone plugin for the current user
	 */
	if ( TCB_Product::has_access() ) {
		$features['script_manager']      = true;
		$features['thrive_design_packs'] = true;
	}

	$features['coming-soon'] = true;

	return $features;
}

add_filter( 'tve_dash_features', 'tar_enable_dashboard_features' );

/**
 * always include TD script manager if TAr is active as a stand-alone plugin
 */
add_filter( 'td_include_script_manager', '__return_true' );

/**
 * Show blocks only if TAR is activated
 */
add_action( 'init', 'TCB_Symbols_Block::init' );
