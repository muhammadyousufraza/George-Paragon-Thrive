<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class TVD_Smart_Shortcodes
 */
final class TVD_Smart_Shortcodes {

	/**
	 * Database instance for Smart Site
	 *
	 * @var TVD_Smart_DB
	 */
	private $db;

	public static $smart_shortcodes = array(
		TVD_Smart_Site::GLOBAL_FIELDS_SHORTCODE     => 'tvd_tss_smart_fields',
		TVD_Smart_Site::GLOBAL_FIELDS_SHORTCODE_URL => 'tvd_tss_smart_url',
	);

	/**
	 * TVD_Smart_Shortcodes constructor.
	 */
	public function __construct() {
		$this->db = new TVD_Smart_DB();

		foreach ( static::$smart_shortcodes as $shortcode => $func ) {
			$function = array( $this, $func );
			add_shortcode( $shortcode, static function ( $attr ) use ( $function ) {
				$output = call_user_func_array( $function, func_get_args() );

				return TVD_Global_Shortcodes::maybe_link_wrap( $output, $attr );
			} );
		}
	}

	/**
	 * Execute smart fields shortcode
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function tvd_tss_smart_fields( $args ) {
		$data = '';
		if ( $args['id'] ) {
			$field = $this->db->get_fields( array(), $args['id'] );

			if ( ! empty( $field ) ) {
				$groups = $this->db->get_groups( $field['group_id'], false );
				$group  = array_pop( $groups );

				$field['group_name'] = $group['name'];
				$field_data          = maybe_unserialize( $field['data'] );
				$data                = TVD_Smart_DB::format_field_data( $field_data, $field, $args );
			}

		}

		return $data;
	}

	/**
	 * Execute smart url shortcode
	 *
	 * @param $args
	 *
	 * @return string
	 */
	public function tvd_tss_smart_url( $args ) {
		$data = '';
		if ( ! empty( $args['id'] ) ) {
			$field = $this->db->get_fields( array(), $args['id'] );

			if ( ! empty( $field['data'] ) ) {
				$field_data = maybe_unserialize( $field['data'] );
				if ( isset( $field_data['phone'] ) ) {
					$data = 'tel:' . $field_data['phone'];
				} elseif ( isset( $field_data['email'] ) ) {
					$data = 'mailto:' . $field_data['email'];
				} else {
					$data = $field_data['url'];
				}

			}
		}

		return ( ! empty( $field_data ) ) ? $data : '';
	}

	/**
	 * Decode the link settings attributes into an array
	 *
	 * @param $link_attr
	 *
	 * @return array|mixed
	 */
	public static function tvd_decode_link_attributes( $link_attr ) {
		$data = [];

		if ( ! empty( $link_attr['static-link'] ) ) {
			$data = json_decode( htmlspecialchars_decode( $link_attr['static-link'] ), true );
		}

		return $data;
	}

}
