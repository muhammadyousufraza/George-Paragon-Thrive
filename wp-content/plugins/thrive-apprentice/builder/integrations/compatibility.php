<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * This contains various functionality that addresses conflicts or incompatibilities with 3rd party products
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Filters to keep theme scripts and styles when in a CartFlow page
 */
add_filter( 'cartflows_remove_theme_scripts', 'thrive_theme_cartflows_keep_assets' );
add_filter( 'cartflows_remove_theme_styles', 'thrive_theme_cartflows_keep_assets' );

/**
 * CartFlows plugin removes ALL styles and scripts from the theme,
 * unless the current theme is not one of Divi or Flatsome ( it seems they hardcoded these )
 *
 * Fortunately, this can be controlled with a filter
 *
 * @param boolean $to_remove
 *
 * @return false
 */
function thrive_theme_cartflows_keep_assets( $to_remove ) {
	/* keep theme styles or scripts on editor page */
	if ( is_editor_page_raw() ) {
		$to_remove = false;
	}

	return $to_remove;
}

/**
 * Integrate CartFlows pages with landing pages and top / bottom sections
 */
add_filter( 'thrive_body_class', static function ( $class, $post ) {
	if ( $post->get( 'post_type' ) === 'cartflows_step' ) {
		$class = '.postid-' . $post->ID;
	}

	return $class;
}, 10, 2 );


/**
 * In some cases we're rendering the template earlier and the Smart Slider 3 Plugin disables his slider shortcode.
 * We overwrite the shortcode and set it to normal mode so it can be rendered.
 */
if ( class_exists( N2SS3Shortcode::class, false ) ) {
	add_action( 'before_theme_builder_template_render', [ N2SS3Shortcode::class, 'shortcodeModeToNormal' ] );
}

/**
 * Compatibility with eLearnCommerce plugin - remove their template_include hooks so we can display our own templates.
 */
if ( class_exists( WPEP\Controller::class, false ) ) {
	add_action( 'wp', static function () {
		if ( has_action( 'template_include', [ WPEP\Controller::instance()->template, 'load' ] ) ) {
			if ( get_post_type() === 'courses' ) {
				add_action( 'wpep_before_main_content', static function () {
					echo '<div id="wrapper">' . thrive_template()->render_theme_hf_section( THRIVE_HEADER_SECTION ) . '<div id="content"><div class="main-container thrv_wrapper">';
				}, 1 );

				add_action( 'wpep_after_main_content', static function () {
					echo '</div></div>' . thrive_template()->render_theme_hf_section( THRIVE_FOOTER_SECTION ) . '</div>';
				}, PHP_INT_MAX );

				add_filter( 'thrive_theme_do_the_post', '__return_false' );
			} else {
				remove_action( 'template_include', [ WPEP\Controller::instance()->template, 'load' ], 50 );
			}
		}
	} );
}

/**
 * Compatibility with MEC - Modern Events Calendar plugin - add programmatically the header and footer based on their actions
 */

if ( class_exists( MEC::class, false ) ) {
	add_action( 'get_header', static function ( $name ) {
		if ( $name === 'mec' ) {
			add_action( 'mec_after_main_content', static function () {
				echo '</div></div>' . thrive_template()->render_theme_hf_section( THRIVE_FOOTER_SECTION ) . '</div>';
			}, PHP_INT_MAX );

			echo '<div id="wrapper">' . thrive_template()->render_theme_hf_section( THRIVE_HEADER_SECTION ) . '<div id="content"><div class="main-container">';
		}
	} );
}

/**
 * Compatibility with relevanssi - when we render the blog list we need to let the plugin to do his search
 */
if ( function_exists( 'relevanssi_query' ) ) {
	add_action( 'theme_before_render_blog_list', static function () {
		global $relevanssi_active;
		$relevanssi_active = false;
	} );
}

/**
 * Compatibility with Optimole WP - image optimization plugin.
 * This makes sure the CSS style file is being generated with all the CSS background image URLs replaced with their optimole CDN equivalents
 */
if ( class_exists( 'Optml_Main', false ) ) {
	add_filter( 'thrive_css_file_content', static function ( $style ) {
		/* only replace if current request is a rest api ajax request ... */
		$should_replace_urls = ! empty( $_REQUEST['tar_editor_page'] ) && defined( 'REST_REQUEST' ) && REST_REQUEST;
		/* ... and it's the one that saves a Theme template  */
		$should_replace_urls = $should_replace_urls && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'update_template';

		/* first, check to see if optimole is configured / connected */
		if ( $should_replace_urls && thrive_optimole_wp()->is_registered() ) {
			/* setup everything necessary. optimole does not allow force-registering their hooks on a custom request because of an incorrectly coded filter */
			do_action( 'optml_replacer_setup' );

			$style = Optml_Main::instance()->manager->process_urls_from_content( $style );
		}

		return $style;
	} );
}

/**
 * Compatibility with MemberPress
 * Their login page redirects to a list page, so we don't want that to be loaded inside our templates
 */
if ( class_exists( 'MeprOptions', false ) && method_exists( 'MeprOptions', 'fetch' ) ) {
	$mepr_options = MeprOptions::fetch();
	if ( ! empty( $mepr_options->login_page_id ) ) {
		add_filter( 'thrive_theme_get_posts_args', static function ( $args ) use ( $mepr_options ) {
			$args['exclude'][] = $mepr_options->login_page_id;

			return $args;
		} );
	}
}
add_filter( 'pre_site_option_loginpress_review_dismiss', static function ( $value ) {
	/* If we are in the theme dashbaord dismiss the loginpress review */
	if ( Thrive_Utils::in_theme_dashboard() ) {
		$value = true;
	}

	return $value;
} );

/**
 * Compatibility with MyBookTable - don't enter 'pre_get_posts' when MBT archives are detected.
 */
add_filter( 'thrive_theme_should_filter_pre_get_posts', static function ( $should_filter, $query ) {
	if (
		$query->is_post_type_archive( 'mbt_book' ) ||
		(
			! empty( $query->queried_object ) &&
			property_exists( $query->queried_object, 'taxonomy' ) &&
			in_array( $query->queried_object->taxonomy, [ 'mbt_author', 'mbt_genre', 'mbt_tag' ], true )
		)
	) {
		$should_filter = false;
	}

	return $should_filter;
}, 10, 2 );

add_filter( 'thrive_theme_ignore_post_types', static function ( $post_types ) {

	/* Remove some memberpress custom post types for which there is no use case to create theme templates */
	$post_types[] = 'memberpressproduct';
	$post_types[] = 'memberpressgroup';

	return $post_types;
} );

/**
 * Compatibility with WishList Member plugin
 * They have a page which should not be used in the iframe from the page template
 */
global $WishListMemberInstance;
if ( ! empty( $WishListMemberInstance ) && is_object( $WishListMemberInstance ) && method_exists( $WishListMemberInstance, 'MagicPage' ) ) {
	$page_id = $WishListMemberInstance->MagicPage( false );

	if ( ! empty( $page_id ) ) {
		add_filter( 'thrive_theme_get_posts_args', static function ( $args ) use ( $page_id ) {
			$args['exclude'][] = $page_id;

			return $args;
		} );
	}
}
