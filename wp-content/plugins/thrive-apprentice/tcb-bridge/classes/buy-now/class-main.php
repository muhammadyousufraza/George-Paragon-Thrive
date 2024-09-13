<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Architect\Buy_now;

use TVA\Architect\Buy_Now_Button_Element;
use TVA\Architect\Utils;
use TVA\Product;
use TVA\Stripe\Request;
use TVA\TTB\Check;
use TVA_Dynamic_Labels;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {

	/**
	 * @var Main
	 */
	private static $instance;

	/**
	 * Contains the List of Shortcodes
	 *
	 * @var array
	 */
	private $shortcodes = [
		'tva_product_buy_now_link'  => 'buy_now_link',
		'tva_product_buy_now_label' => 'buy_now_label',
		'tva_stripe_url'            => 'stripe_url',
	];

	/**
	 * @var bool
	 */
	public static $is_editor_page = false;

	private $allow;

	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		$this->hooks();

		foreach ( $this->shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, [ $this, $function ] );
		}

		static::$is_editor_page = is_editor_page_raw( true );
	}

	public static function init() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Main();
		}
	}

	/**
	 * @return array
	 */
	public static function get_dynamic_links() {
		$products = Product::get_items( [
			'limit' => - 1,
		] );

		$product_links = array_map( function ( $product ) {
			return [
				'name'         => $product->name,
				'show'         => true,
				'id'           => $product->id,
				'integrations' => $product->get_valid_buy_integrations(),
			];
		}, $products );

		return array_values( array_filter( $product_links, function ( $product ) {
			return ! empty( $product['integrations'] );
		} ) );
	}

	public function hooks() {
		add_filter( 'tcb_content_allowed_shortcodes', [ $this, 'content_allowed_shortcodes_filter' ] );

		add_filter( 'tcb_element_instances', [ $this, 'tcb_element_instances' ] );

		// add_filter( 'tcb_dynamiclink_data', [ $this, 'tcb_dynamic_link_data' ] );

		add_filter( 'tcb_inline_shortcodes', [ $this, 'inline_shortcodes' ] );
	}

	/**
	 * Inline shortcodes
	 *
	 * @param array $shortcodes
	 *
	 * @return array
	 */
	public function inline_shortcodes( $shortcodes = [] ) {
		return array_merge_recursive( [
			'Apprentice product' => [
				[
					'option' => __( 'Buy now label', 'thrive-apprentice' ),
					'value'  => 'tva_product_buy_now_label',
				],
			],
		], $shortcodes );
	}

	public function tcb_dynamic_link_data( $data ) {
		if ( $this->allow_buy_now_link() ) {
			$data['Apprentice product'] = [
				'links'     => [],
				'shortcode' => 'tva_product_buy_now_link',
			];
		}

		$data['Stripe'] = [
			'links'     => [
				[
					[
						'name'  => __( 'Customer portal', 'thrive-apprentice' ),
						'label' => __( 'Customer portal', 'thrive-apprentice' ),
						'url'   => '',
						'show'  => true,
						'id'    => 'customer_portal',
					],
				],
			],
			'shortcode' => 'tva_stripe_url',
		];

		return $data;
	}


	/**
	 * Allow shortcodes inside the editor
	 *
	 * @param $shortcodes
	 *
	 * @return array|mixed
	 */
	public function content_allowed_shortcodes_filter( $shortcodes = [] ) {

		if ( static::$is_editor_page ) {
			$shortcodes = array_merge(
				$shortcodes,
				array_keys( $this->shortcodes )
			);
		}

		return $shortcodes;
	}

	/**
	 * Buy button instance
	 *
	 * @param $instances
	 *
	 * @return mixed
	 */
	public function tcb_element_instances( $instances ) {
		if ( static::$is_editor_page && $this->allow_buy_now_link() ) {
			require_once Utils::get_integration_path( 'editor-elements/class-buy-now-button-element.php' );
			$instances['product_buy_now_button'] = new Buy_Now_Button_Element( 'product_buy_now_button' );
		}

		return $instances;
	}

	public function buy_now_label() {
		return TVA_Dynamic_Labels::get_cta_label( 'buy_now' );
	}

	public function stripe_url( $attr ) {
		$url = '';

		if ( isset( $attr['id'] ) ) {
			if ( $attr['id'] === 'customer_portal' && is_user_logged_in() ) {
				$url = Request::get_customer_portal();
			}
		}

		return $url;
	}

	public function buy_now_link( $attr ) {
		if ( static::$is_editor_page ) {
			return '';
		}
		$product_id = isset( $attr['id'] ) ? $attr['id'] : 0;
		$provider   = isset( $attr['buy-provider'] ) ? $attr['buy-provider'] : '';

		return ( new Product( $product_id ) )->get_buy_link( $provider );
	}

	public function allow_buy_now_link() {
		if ( ! isset( $this->allow ) ) {
			$this->allow = ! tva_is_apprentice() && ! Check::course_item();
		}

		return $this->allow;
	}
}
