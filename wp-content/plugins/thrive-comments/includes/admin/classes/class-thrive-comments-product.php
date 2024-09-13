<?php

/**
 * Class TCM_Product
 */
class TCM_Product extends TVE_Dash_Product_Abstract {

	/**
	 * TCM tag
	 *
	 * @var string
	 */
	protected $tag = 'tcm';

	protected $slug = 'thrive-comments';

	protected $version = Thrive_Comments_Constants::PLUGIN_VERSION;

	/**
	 * Plugin title
	 *
	 * @var string
	 */
	protected $title = 'Thrive Comments';

	/**
	 * All product ids
	 *
	 * @var array
	 */
	protected $productIds = array();

	/**
	 * Type of product
	 *
	 * @var string
	 */
	protected $type = 'plugin';

	/**
	 * TCM_Product constructor.
	 *
	 * @param array $data additional data.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = tcm()->plugin_url( 'assets/images/tcm-logo-icon.svg' );
		$this->logoUrlWhite = tcm()->plugin_url( 'assets/images/tcm-logo-icon-white.png' );


		$this->description = __( 'Increase engagement on your website and interact with your audience', Thrive_Comments_Constants::T );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=tcm_admin_dashboard' ),
			'label'  => __( 'Thrive Comments', Thrive_Comments_Constants::T ),
		);

		$this->moreLinks = array(
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-comments-tutorials/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', Thrive_Comments_Constants::T ),
			),
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/support/',
				'target'     => '_blank',
				'text'       => __( 'Support', Thrive_Comments_Constants::T ),
			),
		);
	}

	public static function reset_plugin() {
		global $wpdb;

		$tables = array(
			'logs',
			'email_hash',
		);
		foreach ( $tables as $table ) {
			$table_name = $wpdb->prefix . Thrive_Comments_Constants::DB_PREFIX . $table;
			$sql        = "TRUNCATE TABLE $table_name";
			$wpdb->query( $sql );
		}

		$tables = array(
			'comments',
			'commentmeta',
		);
		foreach ( $tables as $table ) {
			$table_name = $wpdb->prefix . $table;
			$sql        = "TRUNCATE TABLE $table_name";
			$wpdb->query( $sql );
		}

		$wpdb->query(
			"DELETE FROM $wpdb->options WHERE 
						`option_name` LIKE '%tcm_%';"
		);

		$defaults = Thrive_Comments_Constants::get_defaults();

		foreach ( $defaults as $setting => $setting_value ) {
			delete_option( $setting );
		}

		/**
		 * Set default comments order
		 */
		update_option( 'comment_order', 'asc' );
	}
}
