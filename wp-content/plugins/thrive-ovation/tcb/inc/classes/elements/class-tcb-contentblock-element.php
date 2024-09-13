<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 2/7/2019
 * Time: 9:40 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Contentblock_Element
 *
 * Element Class
 */
class TCB_Contentblock_Element extends TCB_Cloud_Template_Element_Abstract {
	/**
	 * TCB_Contentblock_Element constructor.
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_alter_cloud_template_meta', [ $this, 'alter_tpl_meta' ], 10, 2 );

		add_filter( 'tcb_filter_cloud_template_data', [ $this, 'filter_tpl_data' ], 10, 2 );
	}

	/**
	 * Either to display or not the element in the sidebar menu
	 *
	 * @return bool
	 */
	public function hide() {
		return true;
	}

	/**
	 * Modifies the template meta for the content block element
	 *
	 * Works both in storing the meta values inside DB or outputing the values to the user
	 *
	 * Used in inc/classes/content-templates/class-tcb-content-templates-api.php
	 *
	 * @param array $return
	 * @param array $template_data
	 *
	 * @return array
	 */
	public function alter_tpl_meta( $return = [], $template_data = [] ) {

		if ( $template_data['type'] === $this->_tag ) {
			$return['pack'] = $template_data['pack'];
		}

		return $return;
	}

	/**
	 * Filters the data that comes from the user database for content block element
	 *
	 * Returns only the templates and ignores the packs and categories
	 *
	 * @param array  $return
	 * @param string $tag
	 *
	 * @return array
	 */
	public function filter_tpl_data( $return = [], $tag = '' ) {
		if ( $tag === $this->_tag ) {
			$return = $return['tpls'];
		}

		return $return;
	}

	/**
	 * Returns the landing page cloud content blocks
	 *
	 * @param array $args
	 *
	 * @return array|WP_Error
	 */
	public function get_lp_cloud_templates( $args = [] ) {
		$args   = wp_parse_args( $args, [
			'nocache' => false,
		] );
		$return = [];


		if ( empty( $args['lp_set'] ) ) {
			return $return;
		}

		$pack = sanitize_title( 'lp-set-' . (string) $args['lp_set'] );

		$do_not_use_cache = ( defined( 'TCB_TEMPLATE_DEBUG' ) && TCB_TEMPLATE_DEBUG ) || $args['nocache'];

		$transient_tpls       = 'tcb_cloud_templates_' . $this->tag() . '_tpls_from_lp_set_' . $pack;
		$transient_packs      = 'tcb_cloud_templates_' . $this->tag() . '_packs_from_lp_set_' . $pack;
		$transient_categories = 'tcb_cloud_templates_' . $this->tag() . '_categories_from_lp_set_' . $pack;

		$return['tpls']       = get_transient( $transient_tpls );
		$return['packs']      = get_transient( $transient_packs );
		$return['categories'] = get_transient( $transient_categories );

		if ( $do_not_use_cache || empty( $return['tpls'] ) || empty( $return['packs'] ) || empty( $return['categories'] ) ) {
			require_once plugin_dir_path( __DIR__ ) . 'content-templates/class-tcb-content-templates-api.php';

			try {
				$return = tcb_content_templates_api()->get_all( $this->tag(), [ 'pack' => $pack ] );

				if ( is_wp_error( $return ) ) {
					return new WP_Error( 'tcb_error', $return->getMessage(), 501 );
				}

				if ( is_array( $return ) && ! empty( $return['tpls'] ) && ! empty( $return['packs'] ) && ! empty( $return['categories'] ) ) {
					set_transient( $transient_tpls, $return['tpls'], 8 * HOUR_IN_SECONDS );
					set_transient( $transient_packs, $return['packs'], 8 * HOUR_IN_SECONDS );
					set_transient( $transient_categories, $return['categories'], 8 * HOUR_IN_SECONDS );
				}

			} catch ( Exception $e ) {
				return new WP_Error( 'tcb_error', $e->getMessage(), 501 );
			}
		}

		return $return;
	}

	/**
	 * Return all combined blocks
	 *
	 * @param bool $nocache
	 *
	 * @return array
	 */
	public function get_blocks( $nocache = false ) {
		$blocks = [
			'packs' => [],
			'tpls'  => [],
		];

		$special_block_set = apply_filters( 'tcb_get_special_blocks_set', '' );

		if ( ! empty( $special_block_set ) ) {
			$special_blocks = $this->get_lp_cloud_templates( [
				'nocache' => $nocache,
				'lp_set'  => $special_block_set,
			] );

			if ( ! empty( $special_blocks ) && is_array( $special_blocks ) && ! empty( $special_blocks['packs'] ) && ! empty( $special_blocks['tpls'] ) ) {
				$blocks['packs'] = array_merge( $blocks['packs'], $special_blocks['packs'] );
				$blocks['tpls']  = array_merge( $blocks['tpls'], $special_blocks['tpls'] );
			}
		}

		$content_blocks = $this->get_all_contentblocks_templates( [
			'nocache' => $nocache,
		] );

		if ( is_wp_error( $content_blocks ) ) {
			return new WP_Error( 'tcb_api_error', $content_blocks->get_error_message() );
		}

		$blocks['tpls'] = array_merge( $blocks['tpls'], $content_blocks );
		if ( empty( $blocks['packs'] ) ) {
			$blocks['packs'][] = [ 'name' => 'Content Blocks' ];
		}

		return $blocks;
	}

	/**
	 * Returns all contentblocks templates
	 * Used for improved LP BLocks + Content Templates Lightbox
	 *
	 * @param array $args
	 *
	 * @return array|WP_Error
	 */
	public function get_all_contentblocks_templates( $args = [] ) {
		$args = wp_parse_args( $args, [
			'nocache' => false,
		] );

		$do_not_use_cache    = ( defined( 'TCB_TEMPLATE_DEBUG' ) && TCB_TEMPLATE_DEBUG ) || $args['nocache'];
		$templates_transient = 'tcb_cloud_templates_' . $this->tag() . '_all_templates';

		$templates = get_transient( $templates_transient );

		if ( $do_not_use_cache || empty( $templates ) ) {

			require_once plugin_dir_path( __DIR__ ) . 'content-templates/class-tcb-content-templates-api.php';

			try {
				$templates = tcb_content_templates_api()->get_all( $this->tag(), [ 'pack' => 'tcb.get.all.c.blocks.templates' ] );

				set_transient( $templates_transient, $templates, 8 * HOUR_IN_SECONDS );
			} catch ( Exception $exception ) {
				return new WP_Error( 'tcb_error', $exception->getMessage(), 501 );
			}
		}

		return $templates;
	}

	/**
	 * Fetches a list of cloud templates for an element
	 *
	 * @param array $args allows controlling aspects of the method:
	 *                    $nocache - do not use caching (transients)
	 *
	 * @return array|WP_Error
	 * @deprecated available only for backwards compatible: people who have content blocks saved in the content
	 */
	public function get_cloud_templates( $args = [] ) {
		$args = wp_parse_args( $args, [
			'nocache' => false,
		] );

		$pack_id = isset( $_GET['pack'] ) ? sanitize_text_field( $_GET['pack'] ) : '';
		$return  = [];

		$do_not_use_cache = ( defined( 'TCB_TEMPLATE_DEBUG' ) && TCB_TEMPLATE_DEBUG ) || $args['nocache'];

		$transient_tpls       = 'tcb_cloud_templates_' . $this->tag() . '_tpls_from_pack_' . ( ! empty( $pack_id ) ? $pack_id : '' );
		$transient_packs      = 'tcb_cloud_templates_' . $this->tag() . '_packs';
		$transient_categories = 'tcb_cloud_templates_' . $this->tag() . '_categories';

		$return['tpls']       = get_transient( $transient_tpls );
		$return['packs']      = get_transient( $transient_packs );
		$return['categories'] = get_transient( $transient_categories );


		if ( $do_not_use_cache || empty( $return['tpls'] ) || empty( $return['packs'] ) || empty( $return['categories'] ) || empty( $pack_id ) ) {

			require_once plugin_dir_path( __DIR__ ) . 'content-templates/class-tcb-content-templates-api.php';

			try {
				$return = tcb_content_templates_api()->get_all( $this->tag(), [ 'pack' => $pack_id ] );

				if ( empty( $pack_id ) ) {
					$transient_tpls .= $return['from_pack'];
					$pack_id        = $return['from_pack'];
				}

				set_transient( $transient_tpls, $return['tpls'], 8 * HOUR_IN_SECONDS );
				set_transient( $transient_packs, $return['packs'], 8 * HOUR_IN_SECONDS );
				set_transient( $transient_categories, $return['categories'], 8 * HOUR_IN_SECONDS );

			} catch ( Exception $e ) {
				return new WP_Error( 'tcb_error', $e->getMessage(), 501 );
			}
		}

		/**
		 * Favorites Blocks
		 */
		$favorites = get_option( 'thrv_fav_content_blocks', [] );
		foreach ( $return['tpls'] as $index => $tpl ) {
			$return['tpls'][ $index ]['fav'] = (int) ( ! empty( $favorites[ $pack_id ] ) && is_array( $favorites[ $pack_id ] ) && in_array( $tpl['id'], $favorites[ $pack_id ] ) );
		}

		return $return;
	}


	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Content Block', 'thrive-cb' );
	}

	/**
	 * Get element alternate
	 *
	 * @return string
	 */
	public function alternate() {
		return 'content block';
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'content_block';
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv-content-block';
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return self::get_thrive_advanced_label();
	}

	/**
	 * HTML layout of the element for when it's dragged in the canvas
	 *
	 * @return string
	 */
	protected function html() {
		return tcb_template( 'elements/' . $this->tag() . '.php', $this, true );
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		$contentblock = array(
			'contentblock' => array(
				'config' => array(
					'ModalPicker' => array(
						'config' => array(
							'label' => __( 'Template', 'thrive-cb' ),
						),
					),
				),
			),
		);

		return array_merge( $contentblock, $this->group_component() );
	}

	public function is_placeholder() {
		return false;
	}
}
