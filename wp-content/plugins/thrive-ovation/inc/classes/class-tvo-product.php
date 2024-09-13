<?php

// @codingStandardsIgnoreFile

/**
 * Class TVO_Product
 */
class TVO_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tvo';

	protected $slug = 'thrive-ovation';

	protected $version = TVO_VERSION;

	protected $title = 'Thrive Ovation';

	protected $productIds = array();

	protected $type = 'plugin';

	protected $needs_architect = true;

	/**
	 * TVO_Product constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = TVO_ADMIN_URL . '/img/tvo-logo-icon.png';
		$this->logoUrlWhite = TVO_ADMIN_URL . '/img/tvo-logo-icon-white.png';

		$this->description = __( 'Thrive Ovation is a testimonial management plugin.', 'thrive-ovation' );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=tvo_admin_dashboard' ),
			'label'  => __( 'Thrive Ovation', 'thrive-ovation' ),
		);

		$this->moreLinks = array(
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-ovation-tutorials/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', 'thrive-ovation' ),
			),
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/support/',
				'target'     => '_blank',
				'text'       => __( 'Support', 'thrive-ovation' ),
			),
		);
	}


	public static function reset_plugin() {
		global $wpdb;

		$query    = new WP_Query( array(
				'post_type'      => array(
					TVO_SHORTCODE_POST_TYPE,
					TVO_TESTIMONIAL_POST_TYPE,
				),
				'posts_per_page' => '-1',
				'fields'         => 'ids',
			)
		);
		$post_ids = $query->posts;
		foreach ( $post_ids as $id ) {
			wp_delete_post( $id, true );
		}

		$table_name = tvo_table_name( 'activity_log' );

		$wpdb->query( "TRUNCATE TABLE $table_name" );

		$wpdb->query(
			"DELETE FROM $wpdb->options WHERE 
						`option_name` LIKE '%tvo_%' or '%thrive_ovation%';"
		);
	}
}
