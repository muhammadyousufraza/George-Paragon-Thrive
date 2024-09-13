<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 12/21/2017
 * Time: 2:24 PM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

/**
 * Class Thrive_AB_Product
 */
class Thrive_AB_Product extends TVE_Dash_Product_Abstract {

	/**
	 * Tag of the product
	 *
	 * @var string tag.
	 */
	protected $tag = 'tab';

	/**
	 * Slug
	 *
	 * @var string
	 */
	protected $slug = 'thrive-ab-page-testing';
	/**
	 * Version
	 *
	 * @var string
	 */
	protected $version = Thrive_AB::V;

	/**
	 * Name of the product displayed in Dashboard
	 *
	 * @var string title
	 */
	protected $title = 'Thrive Optimize';

	/**
	 * Type of product
	 *
	 * @var string type of the product
	 */
	protected $type = 'plugin';

	/**
	 * Thrive_AB_Product constructor.
	 *
	 * @param array $data info used in dashboard.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = thrive_ab()->url( 'assets/images/tab-logo.png' );
		$this->logoUrlWhite = thrive_ab()->url( 'assets/images/tab-logo-white.png' );
		$this->productIds   = array();

		$this->description = __( 'Boost Conversion Rates by testing two or more variations of a page.', 'thrive-ab-page-testing' );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=tab_admin_dashboard' ),
			'label'  => __( 'Thrive Optimize', 'thrive-ab-page-testing' ),
		);

		$this->moreLinks = array(
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-optimize-tutorials/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', 'thrive-ab-page-testing' ),
			),
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/support/',
				'target'     => '_blank',
				'text'       => __( 'Support', 'thrive-ab-page-testing' ),
			),
		);
	}

	/**
	 * In optimize we need to override the dash product functions just in case the dash is not loaded yet
	 */
	/**
	 * Check if the current has access to the product
	 *
	 * @return bool
	 */
	public static function has_access() {
		return current_user_can( 'tve-use-tab' );
	}

	public static function cap() {
		return 'tve-use-tab';
	}

	/**
	 * Reset plugin to default data
	 */
	public static function reset_plugin() {
		$query = new WP_Query( array(
				'post_type'      => array(
					Thrive_AB_Post_Types::VARIATION,
				),
				'fields'         => 'ids',
				'posts_per_page' => '-1',
			)
		);

		$post_ids = $query->posts;
		foreach ( $post_ids as $id ) {
			wp_delete_post( $id, true );
		}


		global $wpdb;
		$wpdb->query(
			"DELETE FROM $wpdb->postmeta WHERE 
						`meta_key` LIKE '%thrive_ab%';"
		);

		$tables = array(
			'event_log',
			'tests',
			'test_items',

		);
		foreach ( $tables as $table ) {
			$table_name = thrive_ab()->table_name( $table );
			$sql        = "TRUNCATE TABLE $table_name";
			$wpdb->query( $sql );
		}

		$wpdb->query(
			"DELETE FROM $wpdb->options WHERE 
						`option_name` LIKE '%thrive_ab%' OR '%is_control%';"
		);
	}
}
