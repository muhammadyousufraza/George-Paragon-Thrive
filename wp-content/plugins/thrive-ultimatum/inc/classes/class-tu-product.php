<?php

class TU_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tu';

	protected $title = 'Thrive Ultimatum';

	protected $slug = 'thrive-ultimatum';

	protected $productIds = array();

	protected $type = 'plugin';

	protected $version = TVE_Ult_Const::PLUGIN_VERSION;

	protected $needs_architect = true;

	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = TVE_Ult_Const::plugin_url( 'admin/img/logo_90x90.png' );
		$this->logoUrlWhite = TVE_Ult_Const::plugin_url( 'admin/img/logo_90x90-white.png' );

		$this->incompatible_architect_version = ! tve_ult_check_tcb_version();

		$this->description = __( 'Ultimate scarcity plugin for WordPress', 'thrive-ult' );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=tve_ult_dashboard' ),
			'label'  => __( 'Ultimatum Dashboard', 'thrive-ult' ),
		);

		$this->moreLinks = array(
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-ultimatum-tutorials/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', 'thrive-cb' ),
			),
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/support/',
				'target'     => '_blank',
				'text'       => __( 'Support', 'thrive-ult' ),
			),
		);
	}

	public static function reset_plugin() {
		global $wpdb;

		$query    = new WP_Query( array(
				'post_type'      => array(
					TVE_Ult_Const::POST_TYPE_NAME_FOR_SCHEDULE,
					TVE_Ult_Const::POST_TYPE_NAME_FOR_CAMPAIGN,
				),
				'fields'         => 'ids',
				'posts_per_page' => '-1',
			)
		);
		$post_ids = $query->posts;
		foreach ( $post_ids as $id ) {
			wp_delete_post( $id, true );
		}


		$tables = array(
			'event_log',
			'events',
			'emails',
			'designs',
			'settings_campaign',
			'settings_templates',
		);
		foreach ( $tables as $table ) {
			$table_name = tve_ult_table_name( $table );
			$sql        = "TRUNCATE TABLE $table_name";
			$wpdb->query( $sql );
		}

		$wpdb->query(
			"DELETE FROM $wpdb->options WHERE 
						`option_name` LIKE '%tve_ult%';"
		);

	}
}
