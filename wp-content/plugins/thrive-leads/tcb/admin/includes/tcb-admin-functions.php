<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 3/6/2017
 * Time: 11:10 AM
 */

/**
 * @return array
 */
function tcb_admin_get_localization() {
	/** @var TCB_Symbols_Taxonomy $tcb_symbol_taxonomy */
	global $tcb_symbol_taxonomy;
	$terms = get_terms( [ 'slug' => [ 'headers', 'footers' ] ] );
	$terms = array_map( function ( $term ) {
		return $term->term_id;
	}, $terms );

	return array(
		'admin_nonce'        => wp_create_nonce( TCB_Admin_Ajax::NONCE ),
		'dash_url'           => admin_url( 'admin.php?page=tve_dash_section' ),
		't'                  => include tcb_admin()->admin_path( 'includes/i18n.php' ),
		'symbols_logo'       => tcb_admin()->admin_url( 'assets/images/admin-logo.png' ),
		'rest_routes'        => array(
			'symbols'            => tcb_admin()->tcm_get_route_url( 'symbols' ),
			'symbols_terms'      => rest_url( sprintf( '%s/%s', 'wp/v2', TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY ) ),
			'symbols_short_path' => TCB_Admin::TCB_REST_NAMESPACE . '/symbols',
		),
		'notifications'      => TCB\Notifications\Main::get_localized_data(),
		'nonce'              => TCB_Utils::create_nonce(),
		'symbols_tax'        => TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
		'symbols_tax_terms'  => $tcb_symbol_taxonomy->get_symbols_tax_terms(),
		'sections_tax_terms' => $tcb_symbol_taxonomy->get_symbols_tax_terms( true ),
		'default_terms'      => $tcb_symbol_taxonomy->get_default_terms(),
		'symbols_number'     => count( tcb_elements()->element_factory( 'symbol' )->get_all( [ 'category__not_in' => $terms ] ) ),
		'symbols_dash'       => admin_url( 'admin.php?page=tcb_admin_dashboard&tab_selected=symbol#templatessymbols' ),
	);
}

/**
 * @param array $templates
 *
 * @return array
 * todo: we will not need this after we move the category grouping logic to JS ( see the comments in admin-ajax )
 */
function tcb_admin_get_category_templates( $templates = [] ) {
	$template_categories = [];
	$no_preview_img      = TCB_Utils::get_placeholder_url();

	foreach ( $templates as $template ) {
		if ( empty( $template['image_url'] ) ) {
			$template['image_url'] = $no_preview_img;
		}

		if ( isset( $template['id_category'] ) && is_numeric( $template['id_category'] ) ) {
			$category_id = $template['id_category'];

			/* @var \TCB\UserTemplates\Category */
			$category_instance = \TCB\UserTemplates\Category::get_instance_with_id( $category_id );

			switch ( $category_instance->get_meta( 'type' ) ) {
				case 'uncategorized':
					$group = 'uncategorized';
					break;
				case 'page_template':
					$group = \TCB\UserTemplates\Category::PAGE_TEMPLATE_IDENTIFIER;
					break;
				default:
					$group = $category_id;
					break;
			}

			if ( empty( $template_categories[ $group ] ) ) {
				$template_categories[ $group ] = [];
			}

			$template_categories[ $group ][] = $template;
		}
	}

	return $template_categories;
}

/**
 * Filter content templates by their name
 *
 * @param array  $templates
 * @param string $search
 *
 * @return array
 */
function tcb_filter_templates( $templates, $search ) {
	$result = [];

	foreach ( $templates as $template ) {
		if ( stripos( $template['name'], $search ) !== false ) {
			$result[] = $template;
		}
	}

	return $result;
}

/**
 * Displays an icon using svg format
 *
 * @param string $icon
 * @param bool   $return whether to return the icon as a string or to output it directly
 *
 * @return string|void
 */
function tcb_admin_icon( $icon, $return = false ) {
	$html = '<svg class="tcb-admin-icon tcb-admin-icon-' . $icon . '"><use xlink:href="#icon-' . $icon . '"></use></svg>';

	if ( false !== $return ) {
		return $html;
	}

	echo $html; // phpcs:ignore
}
