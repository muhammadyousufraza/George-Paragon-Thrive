<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ultimatum
 */

use TCB\Lightspeed\Css;
use TCB\Lightspeed\JS;
use TCB\Lightspeed\Main;
use TCB\Lightspeed\Woocommerce;

const TU_LIGHTSPEED_FORM_HEIGHT = 'form-height';
const TU_LIGHTSPEED_VERSION     = 1;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

add_filter( 'tcb_lightspeed_optimize_localize_data', 'tve_ultimatum_lightspeed_localize_data' );

add_filter( 'tcb_lightspeed_requires_architect_assets', 'tve_ultimatum_requires_architect_assets', 10, 2 );

add_filter( 'tve_lightspeed_items_to_optimize', 'tve_ultimatum_lightspeed_items_to_optimize' );

add_action( 'tcb_lightspeed_item_optimized', 'tve_ultimatum_lightspeed_item_optimized', 10, 3 );

add_action( 'wp_enqueue_scripts', 'tve_ultimatum_lightspeed_enqueue_flat', 9 );

add_filter( 'tve_ultimatum_ajax_load_forms', 'tve_ultimatum_lightspeed_ajax_load_forms' );

function tve_ultimatum_lightspeed_enqueue_flat() {
	if ( is_tu_post_type() ) {
		/* flat is the default style for TU designs */
		tve_enqueue_style( 'tve_style_family_tve_flt', tve_editor_css() . '/thrive_flat.css' );

		if ( Main::is_optimizing() ) {
			tve_dash_enqueue_script( 'tu-lightspeed-optimize', TVE_Ult_Const::plugin_url( 'js/dist/lightspeed.min.js' ), array( 'jquery' ) );

			add_filter( 'tcb_lightspeed_front_optimize_dependencies', static function ( $deps ) {
				$deps[] = 'tu-lightspeed-optimize';

				return $deps;
			} );
		}
	}
}

/**
 * @param $data
 *
 * @return mixed
 */
function tve_ultimatum_lightspeed_localize_data( $data ) {
	$post_type = get_post_type( $data['post'] );

	if ( $post_type === TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) {
		$data['key'] = isset( $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] ) ? $_GET[ TVE_Ult_Const::DESIGN_QUERY_KEY_NAME ] : '';
	}

	return $data;
}

/**
 * Return placeholder height style for a form that has been optimized
 *
 * @param int $campaign_id
 * @param int $key
 *
 * @return string
 */
function tve_ultimatum_get_lightspeed_placeholder( $campaign_id = 0, $key = 0 ) {
	return 'display:none';

	$placeholder_style = '';

	$form_height = get_post_meta( $campaign_id, TU_LIGHTSPEED_FORM_HEIGHT . '-' . $key, true );
	if ( ! empty( $form_height ) && is_array( $form_height ) ) {
		foreach ( $form_height as $device => $height ) {
			$placeholder_style .= "--tu-placeholder-height-$device:{$height}px;";
		}
	} else {
		$placeholder_style = 'display:none';
	}

	return $placeholder_style;
}

/**
 * The campaign post type needs architect scripts and styles
 *
 * @param $requires
 * @param $post_id
 *
 * @return bool|mixed
 */
function tve_ultimatum_requires_architect_assets( $requires, $post_id ) {
	if ( is_tu_post_type( get_post_type( $post_id ) ) ) {
		$requires = true;
	}

	return $requires;
}

/**
 * @param string $post_type
 *
 * @return bool
 */
function is_tu_post_type( $post_type = '' ) {
	if ( empty( $post_type ) ) {
		$post_type = get_post_type();
	}

	return $post_type === TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN;
}

/**
 * @param array $groups
 *
 * @return array|mixed
 */
function tve_ultimatum_lightspeed_items_to_optimize( $groups = array() ) {

	$campaigns = get_posts( array(
		'posts_per_page' => - 1,
		'post_type'      => TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN,
	) );

	foreach ( $campaigns as $campaign ) {
		$designs = tve_ult_get_designs( $campaign->ID );

		foreach ( $designs as $design ) {
			if ( empty( $groups[ 'tu-' . $design['post_type'] ] ) ) {
				$groups[ 'tu-' . $design['post_type'] ] = array(
					'type'  => $design['post_type'],
					'label' => 'Thrive Ultimatum - ' . $design['type_nice_name'],
					'items' => array(),
				);
			}

			$groups[ 'tu-' . $design['post_type'] ]['items'][] = array(
				'id'        => $campaign->ID,
				'name'      => $design['post_title'],
				'optimized' => (int) get_post_meta( $campaign->ID, Main::OPTIMIZATION_VERSION_META . '_' . $design['id'], true ) === TU_LIGHTSPEED_VERSION ? 1 : 0,
				'url'       => tve_ult_get_preview_url( $campaign->ID, $design['id'], false ),
				'key'       => $design['id'],
			);
		}
	}

	return $groups;
}

/**
 * Update optimization version after saving styles
 *
 * @param int             $post_id
 * @param int             $design_id
 * @param WP_REST_Request $request
 */
function tve_ultimatum_lightspeed_item_optimized( $post_id, $design_id, $request ) {
	$post_type = get_post_type( $post_id );

	if ( $post_type === TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN ) {
		update_post_meta( $post_id, Main::OPTIMIZATION_VERSION_META . "_$design_id", TU_LIGHTSPEED_VERSION );

		$extra_data = $request->get_param( 'extra_data' );

		if ( isset( $extra_data[ TU_LIGHTSPEED_FORM_HEIGHT ] ) ) {
			update_post_meta( $post_id, TU_LIGHTSPEED_FORM_HEIGHT . '-' . $design_id, $extra_data[ TU_LIGHTSPEED_FORM_HEIGHT ] );
		}
	}
}

/**
 * Add optimized assets to ajax requests for ultimatum designs
 *
 * @param $response
 *
 * @return array
 */
function tve_ultimatum_lightspeed_ajax_load_forms( $response ) {

	foreach ( $response as $campaign_id => $campaign ) {
		if ( is_numeric( $campaign_id ) && ! empty( $campaign['designs'] ) ) {
			$response['lightspeed'] = [
				'js'  => [],
				'css' => [
					'files'  => [],
					'inline' => [],
				],
			];
			foreach ( $campaign['designs'] as $design_id ) {
				$lightspeed_css = Css::get_instance( $campaign_id );
				$key            = 'base_' . $design_id;

				if ( ! Main::is_enabled() || empty( $lightspeed_css->get_inline_css( $key ) ) ) {
					$response['lightspeed']['css']['files']['flat'] = Css::get_flat_url();
				} else if ( empty( $response['lightspeed']['css']['files']['flat'] ) ) {
					/* if flat is going to be loaded, no need to get anything else */
					$response['lightspeed']['css']['inline'][] = $lightspeed_css->get_optimized_styles( 'inline', $key, false );
				}

				if ( \TCB\Integrations\WooCommerce\Main::active() && class_exists( 'TCB\Lightspeed\Woocommerce' ) && method_exists( 'TCB\Lightspeed\Woocommerce', 'get_modules' ) && Woocommerce::get_modules( $campaign_id, '_' . $design_id ) ) {
					$response['lightspeed']['js']           = array_merge( $response['lightspeed']['js'], Woocommerce::get_woo_js_modules() );
					$response['lightspeed']['css']['files'] = array_merge( $response['lightspeed']['css']['files'], Woocommerce::get_woo_styles() );
				}

				$response['lightspeed']['js'] = array_merge( $response['lightspeed']['js'], JS::get_instance( $campaign_id, '_' . $design_id )->get_modules_urls() );
			}
		}
	}

	return $response;
}

/**
 * Save styles and js for a variation
 *
 * @param $post_id
 * @param $design_id
 */
function tve_ultimatum_save_optimized_assets( $post_id, $design_id ) {
	if ( tve_ultimatum_has_lightspeed() ) {
		Css::get_instance( $post_id )->save_optimized_css( "base_$design_id", isset( $_POST['optimized_styles'] ) ? $_POST['optimized_styles'] : '' );
		JS::get_instance( $post_id, "_$design_id" )->save_js_modules( isset( $_POST['js_modules'] ) ? $_POST['js_modules'] : array() );

		Main::optimized_advanced_assets( $post_id, $_POST, '_' . $design_id );

		update_post_meta( $post_id, Main::OPTIMIZATION_VERSION_META . "_$design_id", TU_LIGHTSPEED_VERSION );

		if ( isset( $_POST[ TU_LIGHTSPEED_FORM_HEIGHT ] ) ) {
			update_post_meta( $post_id, TU_LIGHTSPEED_FORM_HEIGHT . '-' . $design_id, $_POST[ TU_LIGHTSPEED_FORM_HEIGHT ] );
		}
	}
}

/**
 * @return bool
 */
function tve_ultimatum_has_lightspeed() {
	return class_exists( 'TCB\Lightspeed\Main', false );
}
