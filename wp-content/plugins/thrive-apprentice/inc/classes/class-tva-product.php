<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-university
 */

use TVA\Assessments\TVA_User_Assessment;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

class TVA_Product extends TVE_Dash_Product_Abstract {
	protected $tag = 'tva';

	protected $title = 'Thrive Apprentice';

	protected $slug = 'thrive-apprentice';

	protected $version = TVA_Const::PLUGIN_VERSION;

	protected $productIds = array();

	protected $type = 'plugin';

	protected $needs_architect = true;

	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = TVA_Const::plugin_url( 'admin/img/thrive-apprentice-dashboard.png' );
		$this->logoUrlWhite = TVA_Const::plugin_url( 'admin/img/thrive-apprentice-dashboard.png' );

		$this->incompatible_architect_version = ! tva_check_tcb_version();

		$this->description = __( 'Create online courses in minutes to share your skills, knowledge and expertise', 'thrive-apprentice' );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=thrive_apprentice' ),
			'label'  => __( 'Apprentice Dashboard', 'thrive-apprentice' ),
		);

		$this->moreLinks = array(
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://thrivethemes.com/thrive-apprentice-tutorials/',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', 'thrive-apprentice' ),
			),
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/support/',
				'target'     => '_blank',
				'text'       => __( 'Support', 'thrive-apprentice' ),
			),
		);
	}

	/**
	 * Delete plugin's data & settings
	 * like a fresh install
	 */
	public static function reset_plugin() {
		global $wpdb;

		$courses = tva_get_courses();

		foreach ( $courses as $course ) {
			$term_id = $course->ID;
			$args    = array(
				'posts_per_page' => - 1,
				'post_type'      => array(
					TVA_Const::LESSON_POST_TYPE,
					TVA_Const::CHAPTER_POST_TYPE,
					TVA_Const::MODULE_POST_TYPE,
					TVA_Const::ASSESSMENT_POST_TYPE,
				),
				'post_status'    => TVA_Post::$accepted_statuses,
				'tax_query'      => array(
					array(
						'taxonomy' => TVA_Const::COURSE_TAXONOMY,
						'field'    => 'term_id',
						'terms'    => array( $term_id ),
						'operator' => 'IN',
					),
				),
			);

			$posts = get_posts( $args );
			wp_delete_term( $term_id, TVA_Const::COURSE_TAXONOMY );
			if ( ! empty( $posts ) ) {
				foreach ( $posts as $post ) {
					wp_delete_post( $post->ID, true );
				}
			}
		}

		$posts = get_posts(
			[
				'posts_per_page' => - 1,
				'post_type'      => [
					TVA_User_Assessment::POST_TYPE,
				],
				'post_status'    => TVA_Post::$accepted_statuses,
				'fields'         => 'ids',
			]
		);

		foreach ( $posts as $post_id ) {
			wp_delete_post( $post_id, true );
		}

		$tables = array(
			'tva_ipn_log',
			'tva_orders',
			'tva_order_items',
			'tva_tokens',
			'tva_transactions',
			'thrive_debug',
		);

		foreach ( $tables as $table ) {
			$table = $wpdb->prefix . $table;
			$sql   = "TRUNCATE TABLE $table";
			$wpdb->query( $sql );
		}


		$wpdb->query(
			"DELETE FROM $wpdb->options WHERE 
						`option_name` LIKE '%tva_%';"
		);

		$wpdb->query(
			"DELETE FROM $wpdb->usermeta WHERE 
						`meta_key` LIKE '%tva_%';"
		);
	}
}
