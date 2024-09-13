<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-leads
 */

use TCB\Lightspeed\Css;
use TCB\Lightspeed\JS;
use TCB\Lightspeed\Main;
use TCB\Lightspeed\Woocommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/* lightspeed version for thrive leads items */
const TL_LIGHTSPEED_VERSION = 2;

/**
 * Check if this is post type used in thrive leads
 *
 * @param string $post_type
 *
 * @return bool
 */
function is_tve_leads_post_type( $post_type = '' ) {
	if ( empty( $post_type ) ) {
		$post_type = get_post_type();
	}

	return in_array( $post_type, array( TVE_LEADS_POST_FORM_TYPE, TVE_LEADS_POST_GROUP_TYPE, TVE_LEADS_POST_SHORTCODE_TYPE, TVE_LEADS_POST_TWO_STEP_LIGHTBOX ), true );
}

add_filter( 'tve_lightspeed_items_to_optimize', 'tve_leads_lightspeed_items_to_optimize' );

add_filter( 'tcb_lightspeed_requires_architect_assets', 'tve_leads_requires_architect_assets', 10, 2 );

add_action( 'tcb_lightspeed_item_optimized', 'tve_leads_lightspeed_item_optimized', 10, 3 );

add_action( 'tcb_lightspeed_optimize_localize_data', 'tve_leads_lightspeed_optimize_localize_data' );

add_action( 'wp_enqueue_scripts', 'tve_leads_lightspeed_enqueue_flat', 9 );

add_filter( 'tve_leads_ajax_load_forms', 'tve_leads_lightspeed_ajax_load_forms' );

function tve_leads_lightspeed_enqueue_flat() {
	if ( is_tve_leads_post_type() ) {
		/* flat is the default style for TL designs */
		tve_enqueue_style( 'tve_style_family_tve_flt', Css::get_flat_url( false ) );

		if ( Main::is_optimizing() ) {
			tve_dash_enqueue_script( 'tl-lightspeed-optimize', TVE_LEADS_URL . 'js/lightspeed-optimize.min.js', array( 'jquery' ) );

			add_filter( 'tcb_lightspeed_front_optimize_dependencies', static function ( $deps ) {
				$deps[] = 'tl-lightspeed-optimize';

				return $deps;
			} );
		}
	}
}

/**
 * Save form height and version when user optimizes forms
 *
 * @param int             $post_id
 * @param int             $variation_key
 * @param WP_REST_Request $request
 */
function tve_leads_lightspeed_item_optimized( $post_id, $variation_key, $request ) {
	if ( is_tve_leads_post_type( get_post_type( $post_id ) ) ) {
		update_post_meta( $post_id, Main::OPTIMIZATION_VERSION_META . "_$variation_key", TL_LIGHTSPEED_VERSION );

		$extra_data = $request->get_param( 'extra_data' );

		if ( ! empty( $extra_data[ TVE_LEADS_FIELD_FORM_HEIGHT ] ) ) {
			global $tvedb;
			$variation = $tvedb->get_form_variation( $variation_key );

			$variation[ TVE_LEADS_FIELD_FORM_HEIGHT ] = $extra_data[ TVE_LEADS_FIELD_FORM_HEIGHT ];

			tve_leads_save_form_variation( $variation );
		}
	}
}

/**
 * Add form types, shortcodes and lead boxes to the list to optimize
 *
 * @param array $groups
 *
 * @return array|mixed
 */
function tve_leads_lightspeed_items_to_optimize( $groups = array() ) {
	global $tvedb;

	$lead_groups = get_posts( array(
		'posts_per_page' => - 1,
		'post_type'      => TVE_LEADS_POST_GROUP_TYPE,
	) );

	foreach ( $lead_groups as $group ) {
		$form_types = tve_leads_get_form_types( array(
			'lead_group_id'  => $group->ID,
			'tracking_data'  => false,
			'get_variations' => true,
			'no_content'     => true,
		) );

		foreach ( $form_types as $form_type ) {
			if ( $form_type instanceof WP_Post && ! empty( $form_type->variations ) ) {
				$form_type_key = 'tl-' . $form_type->tve_form_type;
				if ( empty( $groups[ $form_type_key ] ) ) {
					$groups[ $form_type_key ] = array(
						'type'  => $form_type->tve_form_type,
						'label' => 'Thrive Leads - ' . $form_type->post_title,
						'items' => array(),
					);
				}

				$groups[ $form_type_key ]['items'] = array_merge( $groups[ $form_type_key ]['items'], tve_leads_lightspeed_prepare_variations( $form_type ) );
			}
		}
	}

	$shortcodes = tve_leads_get_shortcodes( array(
		'active_test'    => false,
		'tracking_data'  => false,
		'get_variations' => true,
	) );

	foreach ( $shortcodes as $shortcode ) {
		if ( ! empty( $shortcode->variations ) ) {
			if ( empty( $groups[ $shortcode->post_type ] ) ) {
				$groups[ $shortcode->post_type ] = array(
					'type'  => $shortcode->post_type,
					'label' => 'Leads Shortcodes',
					'items' => array(),
				);
			}

			$groups[ $shortcode->post_type ]['items'] = array_merge( $groups[ $shortcode->post_type ]['items'], tve_leads_lightspeed_prepare_variations( $shortcode ) );
		}
	}

	$thrive_boxes = tve_leads_get_two_step_lightboxes( array(
		'active_test'    => true,
		'tracking_data'  => true,
		'get_variations' => true,
	) );

	foreach ( $thrive_boxes as $box ) {
		if ( ! empty( $box->variations ) ) {
			if ( empty( $groups[ $box->post_type ] ) ) {
				$groups[ $box->post_type ] = array(
					'type'  => $box->post_type,
					'label' => 'ThriveBoxes',
					'items' => array(),
				);
			}

			$groups[ $box->post_type ]['items'] = array_merge( $groups[ $box->post_type ]['items'], tve_leads_lightspeed_prepare_variations( $box ) );
		}
	}

	return $groups;
}

/**
 * Read variations and states for form type
 *
 * @param $form
 *
 * @return array
 */
function tve_leads_lightspeed_prepare_variations( $form ) {
	$variations = [];

	if ( ! empty( $form->variations ) ) {
		foreach ( $form->variations as $variation ) {
			$variations[ $variation['key'] ] = array(
				'id'        => $form->ID,
				'name'      => $variation['post_title'],
				'optimized' => (int) get_post_meta( $form->ID, Main::OPTIMIZATION_VERSION_META . '_' . $variation['key'], true ) === TL_LIGHTSPEED_VERSION ? 1 : 0,
				'url'       => tve_leads_get_preview_url( $form->ID, $variation['key'], false ),
				'key'       => $variation['key'],
			);

			$states = tve_leads_get_form_related_states( $variation );

			foreach ( $states as $state ) {
				if ( empty( $variations[ $state['key'] ] ) ) {
					$variations[ $state['key'] ] = array(
						'id'        => $form->ID,
						'name'      => $state['post_title'],
						'optimized' => (int) get_post_meta( $form->ID, Main::OPTIMIZATION_VERSION_META . '_' . $state['key'], true ) === TL_LIGHTSPEED_VERSION ? 1 : 0,
						'url'       => tve_leads_get_preview_url( $form->ID, $state['key'], false ),
						'key'       => $state['key'],
					);
				}
			}
		}
	}

	return array_values( $variations );
}

/**
 * TL post types need architect scripts and styles
 *
 * @param $requires
 * @param $post_id
 *
 * @return bool|mixed
 */
function tve_leads_requires_architect_assets( $requires, $post_id ) {
	if ( is_tve_leads_post_type( get_post_type( $post_id ) ) ) {
		$requires = true;
	}

	return $requires;
}

/**
 * Set the key for thrive leads variations
 *
 * @param array $localize_object
 *
 * @return array|mixed
 */
function tve_leads_lightspeed_optimize_localize_data( $localize_object = array() ) {
	if ( is_tve_leads_post_type() && isset( $_GET['_key'] ) ) {
		$localize_object['key'] = $_GET['_key'];
	}

	return $localize_object;
}

/**
 * Save styles and js for a variation
 *
 * @param $post_id
 * @param $variation_key
 */
function tve_leads_save_optimized_assets( $post_id, $variation_key ) {
	if ( tve_leads_has_lightspeed() ) {
		Css::get_instance( $post_id )->save_optimized_css( "base_$variation_key", isset( $_POST['optimized_styles'] ) ? $_POST['optimized_styles'] : '' );
		JS::get_instance( $post_id, "_$variation_key" )->save_js_modules( isset( $_POST['js_modules'] ) ? $_POST['js_modules'] : array() );

		if ( class_exists( 'TCB\Lightspeed\Main' ) && method_exists( 'TCB\Lightspeed\Main', 'optimized_advanced_assets' ) ) {
			Main::optimized_advanced_assets( $post_id, $_POST, '_' . $variation_key );
		}

		update_post_meta( $post_id, Main::OPTIMIZATION_VERSION_META . "_$variation_key", TL_LIGHTSPEED_VERSION );
	}
}

/**
 * Add lightspeed assets to ajax request
 *
 * @param $response
 *
 * @return mixed
 */
function tve_leads_lightspeed_ajax_load_forms( $response ) {
	if ( ! empty( $response['variations'] && tve_leads_has_lightspeed() ) ) {
		$response['lightspeed'] = [
			'js'  => [],
			'css' => [
				'files'  => [],
				'inline' => [],
			],
		];

		foreach ( $response['variations'] as $form_id => $variations ) {
			if ( is_array( $variations ) ) {
				foreach ( array_unique( $variations ) as $variation_key ) {
					$lightspeed_css = Css::get_instance( $form_id );
					$key            = 'base_' . $variation_key;

					if ( ! Main::is_enabled() || empty( $lightspeed_css->get_inline_css( $key ) ) ) {
						$response['lightspeed']['css']['files']['flat'] = Css::get_flat_url();
					} else if ( empty( $response['lightspeed']['css']['files']['flat'] ) ) {
						/* if flat is going to be loaded, no need to get anything else */
						$response['lightspeed']['css']['inline'][] = $lightspeed_css->get_optimized_styles( 'inline', $key, false );
					}

					$response['lightspeed']['js'] = array_merge( $response['lightspeed']['js'], JS::get_instance( $form_id, '_' . $variation_key )->get_modules_urls() );

					if ( tve_has_advanced_optimization( $form_id, $variation_key ) ) {
						$response['lightspeed']['js']           = array_merge( $response['lightspeed']['js'], Woocommerce::get_woo_js_modules() );
						$response['lightspeed']['css']['files'] = array_merge( $response['lightspeed']['css']['files'], Woocommerce::get_woo_styles() );
					}
				}
			}
		}

		unset( $response['variations'] );
	}

	return $response;
}

/**
 * Load variation assets
 *
 * @param $form_id
 * @param $variation_key
 */
function tve_leads_lightspeed_enqueue_variation_assets( $form_id, $variation_key ) {
	$is_rest = defined( 'REST_REQUEST' ) && REST_REQUEST;
	if ( ! wp_doing_ajax() && ! is_tve_leads_post_type() && ! $is_rest ) {
		if ( tve_leads_has_lightspeed() ) {
			TCB\Lightspeed\Css::get_instance( $form_id )->load_optimized_style( 'base_' . $variation_key );
			JS::get_instance( $form_id, '_' . $variation_key )->load_modules();
		}
		if ( tve_has_advanced_optimization( $form_id, $variation_key ) ) {
			$GLOBALS['optimized_advanced_assets'] = true;
		}
	}
}

/**
 * Check if we have the code for lightspeed
 *
 * @return bool
 */
function tve_leads_has_lightspeed() {
	return class_exists( 'TCB\Lightspeed\Main', false );
}

/**
 * Checks if it has advanced optimization
 *
 * @return bool
 */
function tve_has_advanced_optimization( $form_id, $variation_key ) {
	return \TCB\Integrations\WooCommerce\Main::active() && class_exists( 'TCB\Lightspeed\Woocommerce' ) && method_exists( 'TCB\Lightspeed\Woocommerce', 'get_modules' ) && Woocommerce::get_modules( $form_id, '_' . $variation_key );
}
