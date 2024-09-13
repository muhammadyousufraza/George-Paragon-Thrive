<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 4/9/2019
 * Time: 13:20
 */

/**
 * Wishlist Member plugin adds a filter on `list_term_exclusions` which causes huge database load.
 * It executes sql queries for each category on each get_terms() call.
 * Apprentice (and TTB) use a lot of get_terms() function calls which in turn cause big performance hits
 *
 * We hook to the `list_term_exclusions` filter earlier, and make sure WLM does not execute its code if the get_terms() refers to a taxonomy used by TA / TTB
 */
add_filter( 'list_terms_exclusions', static function ( $exclusions, $args, $taxonomies ) {
	/**
	 * If the init action has not been executed yet, we do not need to do anything, as this will trigger a fatal error thrive dashboard is not loaded
	 */
	if ( ! did_action( 'init' ) ) {
		return $exclusions;
	}
	global $WishListMemberInstance;
	if ( ! empty( $WishListMemberInstance ) && array_intersect( $taxonomies, [ TVA_Const::COURSE_TAXONOMY, 'thrive_skin_tax', 'thrive_demo_tag', 'thrive_demo_category', \TVA\Product::TAXONOMY_NAME, TVA_Const::OLD_POST_TAXONOMY ] ) ) {
		/* this makes sure WLM does not execute all those sql queries */
		add_filter( 'wishlistmember_pre_get_option_only_show_content_for_level', '__return_zero' );

		/* Hook into the next available filter and remove the added __return_zero hook */
		add_filter( 'terms_clauses', static function ( $clauses ) {
			remove_filter( 'wishlistmember_pre_get_option_only_show_content_for_level', '__return_zero' );

			return $clauses;
		} );
	}

	return $exclusions;
}, 0, 4 );

/**
 * RankMath - https://rankmath.com/
 *
 * Filter URL entry before it gets added to the sitemap.
 *
 * @param array                   $url  Array of URL parts.
 * @param string                  $type URL type.
 * @param WP_Term|WP_Post|boolean $user Data object for the URL.
 */
add_filter( 'rank_math/sitemap/entry', static function ( $url, $type, $object ) {

	if ( $type === 'term' && $object instanceof WP_Term && get_term_meta( $object->term_id, 'tva_status', true ) === 'private' ) {
		$url = false;
	} elseif ( $type === 'post' && ! empty( $object->ID ) && (int) get_post_meta( $object->ID, 'tva_is_demo', true ) === 1 ) {
		/**
		 * $object is not a WP_Post. It is a simple object in this case
		 */
		$url = false;
	}

	return $url;
}, 10, 3 );


/**
 * RankMath - https://rankmath.com/
 *
 * Changing the list of accessible post types.
 *
 * @var  array $accessible_post_types The post types.
 */
add_filter( 'rank_math/excluded_post_types', static function ( $accessible_post_types ) {
	unset( $accessible_post_types[ TVA_Course_Overview_Post::POST_TYPE ] );
	unset( $accessible_post_types[ TVA_Const::CHAPTER_POST_TYPE ] );
	unset( $accessible_post_types[ TVA_Access_Restriction::POST_TYPE ] );

	return $accessible_post_types;
}, 10, 2 );


/**
 * FIX issue with WPML and Apprentice skin wizard
 * If WPML is active the wizard requests returns 301 - redirect status code.
 *
 * @param WP_Query $q
 */
add_filter( 'wpml_pre_parse_query', static function ( $q ) {
	if ( ! empty( $_REQUEST['tva_skin_id'] ) ) {
		$q = new WP_Query();
	}

	return $q;
} );
