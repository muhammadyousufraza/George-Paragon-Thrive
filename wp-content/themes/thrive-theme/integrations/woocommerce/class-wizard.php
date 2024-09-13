<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

namespace Thrive\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Wizard
 */
class Wizard {

	/**
	 * WooCommerce wizard init
	 */
	public static function init() {
		add_filter( 'thrive_theme_wizard_structure', [ __CLASS__, 'wizard_structure' ] );
		add_filter( 'thrive_theme_wizard_active_steps', [ __CLASS__, 'wizard_active_steps' ] );
		add_filter( 'thrive_theme_wizard_urls', [ __CLASS__, 'wizard_urls' ] );
		add_filter( 'thrive_theme_wizard_templates_filters', [ __CLASS__, 'wizard_templates_filters' ], 10, 2 );
		add_filter( 'thrive_theme_wizard_templates', [ __CLASS__, 'wizard_templates' ], 10, 2 );
		add_filter( 'thrive_theme_wizard_fetch_template', [ __CLASS__, 'wizard_template' ], 10, 4 );
		add_filter( 'thrive_theme_wizard_save_response', [ __CLASS__, 'wizard_save' ], 10, 3 );
		add_filter( 'thrive_theme_wizard_render_structure', [ __CLASS__, 'should_render_structure' ], 10, 2 );
		add_filter( 'thrive_theme_wizard_body_classes', [ __CLASS__, 'body_class' ], 10, 2 );
	}

	/**
	 * Add active steps for WooCommerce inside the wizard
	 *
	 * @param array $steps
	 *
	 * @return array
	 */
	public static function wizard_active_steps( $steps ) {
		array_push( $steps, Main::HEADER, Main::FOOTER, Main::SHOP_TEMPLATE, Main::POST_TYPE, Main::CART_TEMPLATE, Main::CHECKOUT_TEMPLATE );

		return $steps;
	}

	/**
	 * Add WooCommerce wizard step and section
	 *
	 * @param array $structure
	 *
	 * @return mixed
	 */
	public static function wizard_structure( $structure ) {
		$woo_steps = [
			[
				'id'                    => Main::HEADER,
				'title'                 => __( 'Shop Header', 'thrive-theme' ),
				'sidebarLabel'          => __( 'Shop Header', 'thrive-theme' ),
				'section'               => 'woocommerce',
				'hasTopMenu'            => true,
				'selector'              => [
					'label' => __( 'Select a Shop Header', 'thrive-theme' ),
				],
				'popupMessage'          => 'You can change the <strong>Header</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
				'completedPopupMessage' => 'You can change the <strong>Header</strong> from the dropdown',
			],
			[
				'id'                    => Main::FOOTER,
				'title'                 => __( 'Shop Footer', 'thrive-theme' ),
				'sidebarLabel'          => __( 'Shop Footer', 'thrive-theme' ),
				'section'               => 'woocommerce',
				'hasTopMenu'            => true,
				'selector'              => [
					'label' => __( 'Select a Shop Footer', 'thrive-theme' ),
				],
				'popupMessage'          => 'You can change the <strong>Footer</strong> from the top dropdown or<br>by pressing the arrow keys &lt; &gt;<br>When you are done click the <strong>Choose and Continue</strong> button.',
				'completedPopupMessage' => 'You can change the <strong>Footer</strong> from the dropdown',
			],
			[
				'id'           => 'shop',
				'title'        => __( 'WooCommerce Shop', 'thrive-theme' ),
				'sidebarLabel' => __( 'Shop Homepage', 'thrive-theme' ),
				'section'      => 'woocommerce',
				'hasTopMenu'   => true,
				'selector'     => [
					'label' => __( 'Select a Shop Template', 'thrive-theme' ),
				],
				'previewMode'  => 'iframe',
			],
			[
				'id'           => 'product',
				'title'        => __( 'WooCommerce Product', 'thrive-theme' ),
				'sidebarLabel' => __( 'Single Product', 'thrive-theme' ),
				'section'      => 'woocommerce',
				'hasTopMenu'   => true,
				'selector'     => [
					'label' => __( 'Select a Product Template', 'thrive-theme' ),
				],
				'previewMode'  => 'iframe',
			],
			[
				'id'           => 'cart',
				'title'        => __( 'WooCommerce Cart', 'thrive-theme' ),
				'sidebarLabel' => __( 'Cart', 'thrive-theme' ),
				'section'      => 'woocommerce',
				'hasTopMenu'   => true,
				'selector'     => [
					'label' => __( 'Select a Cart Template', 'thrive-theme' ),
				],
				'previewMode'  => 'iframe',
			],
			[
				'id'           => 'checkout',
				'title'        => __( 'WooCommerce Checkout', 'thrive-theme' ),
				'sidebarLabel' => __( 'Checkout', 'thrive-theme' ),
				'section'      => 'woocommerce',
				'hasTopMenu'   => true,
				'selector'     => [
					'label' => __( 'Select a Checkout Template', 'thrive-theme' ),
				],
				'previewMode'  => 'iframe',
			],
		];

		$structure['steps']      = array_merge( $structure['steps'], $woo_steps );
		$structure['sections'][] = [
			'id'           => 'woocommerce',
			'sidebarLabel' => 'WooCommerce',
		];

		return $structure;
	}

	/**
	 * Add WooCommerce url for the wizard
	 *
	 * @param array $urls
	 *
	 * @return array
	 */
	public static function wizard_urls( $urls ) {
		return array_merge(
			$urls,
			[
				'shop'     => \Thrive_Utils::ensure_https( Main::get_shop_url() ),
				'product'  => \Thrive_Utils::ensure_https( get_permalink( \Thrive_Wizard::get_post_or_demo_content_id( 'product' ) ) ),
				'cart'     => \Thrive_Utils::ensure_https( Main::get_cart_url() ),
				'checkout' => \Thrive_Utils::ensure_https( Main::get_checkout_url() ),
			]
		);
	}

	/**
	 * Change filters when we are getting a WooCommerce template from the cloud
	 *
	 * @param array  $filters
	 * @param string $type
	 *
	 * @return array
	 */
	public static function wizard_templates_filters( $filters, $type ) {
		$woo_filters = [];

		if ( in_array( $type, Main::ALL_TEMPLATES, true ) ) {
			switch ( $type ) {
				case 'product':
					$woo_filters = [
						'primary'   => THRIVE_SINGULAR_TEMPLATE,
						'secondary' => Main::POST_TYPE,
					];
					break;
				case 'cart':
					$woo_filters = [
						'primary'   => THRIVE_SINGULAR_TEMPLATE,
						'secondary' => Main::CART_TEMPLATE,
					];
					break;
				case 'shop':
					$woo_filters = [
						'primary'   => THRIVE_ARCHIVE_TEMPLATE,
						'secondary' => Main::POST_TYPE,
					];
					break;
				case 'checkout':
					$woo_filters = [
						'primary'   => THRIVE_SINGULAR_TEMPLATE,
						'secondary' => Main::CHECKOUT_TEMPLATE,
					];
					break;
				default:
					$woo_filters = [];
					break;
			}
		}

		return array_merge( $filters, $woo_filters );
	}

	/**
	 * Handle wizard templates for woo steps
	 *
	 * @param array            $templates
	 * @param \WP_REST_Request $request
	 *
	 * @return array
	 * @throws \Exception
	 */
	public static function wizard_templates( $templates, $request ) {
		$type = $request->get_param( 'type' );

		switch ( $type ) {
			case Main::HEADER:
			case Main::FOOTER:
				$section_rest = new \Thrive_Section_REST();
				$type         = str_replace( 'woo_', '', $type );
				$request->set_param( 'type', $type );
				/* remove "blank" templates for now and we will have to bring only the woo headers and footers */

				$response = $section_rest->get_cloud_sections( $request );
				if ( ! is_wp_error( $response ) ) {
					$cloud_templates = array_values( array_filter( $response->get_data()['data'], static function ( $template
					) {
						return strpos( $template['post_title'], 'Blank' ) !== 0;
					} ) );
				} else {
					return new \WP_Error( 'tcb_api_error', $response->get_error_message() );
				}
				/* get also the local templates */
				$local_templates = \Thrive_Wizard::get_local_hf( $type );

				$templates = array_map( static function ( $template ) {
					$template['source'] = isset( $template['from_cloud'] ) ? 'cloud' : 'local';

					return $template;
				}, array_merge( $cloud_templates, $local_templates ) );

				break;
			case Main::POST_TYPE:
			case Main::CART_TEMPLATE:
			case Main::CHECKOUT_TEMPLATE:
			case Main::SHOP_TEMPLATE:
				$templates = \Thrive_Wizard::get_templates( $type );
				break;
			default:
				break;
		}

		return $templates;
	}

	/**
	 * Fetch wizard template for WooCommerce templates, headers, footers
	 *
	 * @param array  $data
	 * @param string $type
	 * @param int    $id
	 * @param string $source
	 *
	 * @return array
	 */
	public static function wizard_template( $data, $type, $id, $source ) {
		switch ( $type ) {
			case Main::HEADER:
			case Main::FOOTER:
				/* get the HTML / CSS for a cloud template */
				$type    = str_replace( 'woo_', '', $type );
				$content = thrive_wizard()->get_hf_preview_content( $type, $id, $source );

				$data['id']   = $id;
				$data['html'] = ( new \Thrive_HF_Section( 0, $type, [ 'content' => $content ] ) )->render();
				break;
			case Main::POST_TYPE:
			case Main::CART_TEMPLATE:
			case Main::CHECKOUT_TEMPLATE:
			case Main::SHOP_TEMPLATE:
				try {

					$template = thrive_wizard()->get_template_by_tag( $id );
					$template->set_header_footer( 'header', thrive_skin()->get_default_data( 'woo_header' ) );
					$template->set_header_footer( 'footer', thrive_skin()->get_default_data( 'woo_footer' ) );

					$data['id'] = $template ? $template->ID : 0;
				} catch ( \Exception $ex ) {
					$data = [
						'id'   => 0,
						'html' => '',
					];
				}
				break;

		}

		return $data;
	}

	/**
	 * Save wizard steps
	 *
	 * @param array $response
	 * @param array $wizard
	 * @param string $step
	 *
	 * @return mixed
	 */
	public static function wizard_save( $response, $wizard, $step ) {
		$template_id = isset( $wizard['settings'][ $step ]['template_id'] ) ? $wizard['settings'][ $step ]['template_id'] : 0;
		$source      = isset( $wizard['settings'][ $step ]['source'] ) ? $wizard['settings'][ $step ]['source'] : 'cloud';

		switch ( $step ) {
			case Main::HEADER:
			case Main::FOOTER:
				$id   = thrive_skin()->get_default_data( $step );
				$type = str_replace( 'woo_', '', $step );

				/* go through all the woo templates and set the same header / footer */
				if ( $source === 'cloud' ) {
					$symbol_id = \Thrive_HF_Section::populate_from_cloud_template(
						$template_id,
						$type,
						'Default ' . ucfirst( $type ) . ' for WooCommerce',
						get_post( $id )
					);
				} else {
					$symbol = get_posts( [
						'include'   => [ $template_id ],
						'post_type' => \TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
					] );

					$symbol_id = empty( $symbol ) ? new WP_Error( 'symbol_not_found', __( 'Template could not be found', 'thrive-theme' ) ) : $template_id;
				}

				if ( ! is_wp_error( $symbol_id ) ) {
					/* make sure this is the default H/F section for the woo templates from the skin */
					thrive_skin()->set_default_data( $step, $symbol_id );

					$response['id'] = $symbol_id;

					$woo_templates = Helpers::get_templates();
					foreach ( $woo_templates as $template ) {
						/** @var \Thrive_Template $template */
						$template->set_header_footer( $type, $symbol_id );
					}
				} else {
					$response['success'] = false;
					$response['message'] = $symbol_id->get_error_message();
				}

				break;
			case Main::POST_TYPE:
			case Main::CART_TEMPLATE:
			case Main::CHECKOUT_TEMPLATE:
			case Main::SHOP_TEMPLATE:
				$template = new \Thrive_Template( $wizard['settings'][ $step ]['template_id'] );
				$template->make_default();
				break;
			default:
				break;
		}

		return $response;
	}

	/**
	 * For certain woo steps we don't want to render the wizard structure
	 *
	 * @param boolean $show
	 * @param string $step
	 *
	 * @return bool
	 */
	public static function should_render_structure( $show, $step ) {
		if ( $step === Main::HEADER || $step === Main::FOOTER || $step === 'woo' ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * make sure that woocommerce class is added for woo steps because there is default css for woo elements
	 *
	 * @param $classes
	 * @param $step
	 *
	 * @return mixed
	 */
	public static function body_class( $classes, $step ) {
		if ( $step === Main::HEADER || $step === Main::FOOTER || $step === 'woo' ) {
			$classes[] = 'woocommerce';
		}

		return $classes;
	}
}
