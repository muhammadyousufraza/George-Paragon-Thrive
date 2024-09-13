<?php

/**
 * Created by PhpStorm.
 * User: Aurelian Pop
 * Date: 09-Dec-15
 * Time: 7:06 PM
 */
class TL_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tl';

	protected $slug = 'thrive-leads';

	protected $title = 'Thrive Leads';

	protected $version = TVE_LEADS_VERSION;

	protected $productIds = array();

	protected $type = 'plugin';

	protected $needs_architect = true;

	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = TVE_LEADS_ADMIN_URL . 'img/thrive-leads-logo.png';
		$this->logoUrlWhite = TVE_LEADS_ADMIN_URL . 'img/thrive-leads-logo-white.png';

		$this->description = __( 'Create and manage opt-in forms, keep track of your email list building and more.', 'thrive-leads' );

		$this->incompatible_architect_version = ! tve_leads_check_tcb_version();

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=thrive_leads_dashboard' ),
			'label'  => __( 'Thrive Leads Dashboard', 'thrive-leads' ),
		);

		$this->moreLinks = array(
			'reporting' => array(
				'class'      => 'tve-leads-reporting',
				'icon_class' => 'tvd-icon-line-chart',
				'href'       => admin_url( 'admin.php?page=thrive_leads_reporting' ),
				'text'       => __( 'Reporting', 'thrive-leads' ),
			),
			'asset'     => array(
				'class'      => 'tve-leads-asset',
				'icon_class' => 'tvd-icon-cloud-download',
				'href'       => admin_url( 'admin.php?page=thrive_leads_asset_delivery' ),
				'text'       => __( 'Asset Delivery', 'thrive-leads' ),
			),
			'export'    => array(
				'class'      => 'tve-leads-export',
				'icon_class' => 'tvd-icon-group',
				'href'       => admin_url( 'admin.php?page=thrive_leads_contacts' ),
				'text'       => __( 'Lead Export', 'thrive-leads' ),
			),
			'tutorials' => array(
				'class'      => 'tve-leads-tutorials',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-leads-tutorials/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', 'thrive-leads' ),
			),
			'support'   => array(
				'class'      => 'tve-leads-tutorials',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/support/',
				'target'     => '_blank',
				'text'       => __( 'Support', 'thrive-leads' ),
			),
		);
	}


	/**
	 * Reset all TL data
	 *
	 * @return bool|void
	 */
	public static function reset_plugin() {
		global $wpdb;

		$query    = new WP_Query( array(
				'post_type'      => array(
					TVE_LEADS_POST_GROUP_TYPE,
					TVE_LEADS_POST_SHORTCODE_TYPE,
					TVE_LEADS_POST_TWO_STEP_LIGHTBOX,
					TVE_LEADS_POST_ONE_CLICK_SIGNUP,
					TVE_LEADS_POST_FORM_TYPE,
				),
				'posts_per_page' => '-1',
				'fields'         => 'ids',
			)
		);
		$post_ids = $query->posts;
		foreach ( $post_ids as $id ) {
			wp_delete_post( $id, true );
		}

		$tables = array(
			'event_log',
			'split_test',
			'split_test_items',
			'form_variations',
			'contacts',
			'contact_download',
			'form_summary',
			'saved_group_options',
			'group_options',
		);
		foreach ( $tables as $table ) {
			$table_name = tve_leads_table_name( $table );
			$sql        = "TRUNCATE TABLE $table_name";
			$wpdb->query( $sql );
		}

		$wpdb->query(
			"DELETE FROM $wpdb->options WHERE 
						`option_name` LIKE '%tve_lead%';"
		);
	}
}
