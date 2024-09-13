<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

use function TVD\Cache\content_set_cache;
use function TVD\Cache\meta_cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVD_Content_Sets
 *
 * @project: thrive-dashboard
 */
class TVD_Content_Sets {
	/**
	 * Nonce that should be checked for meta boxes request
	 */
	const META_BOX_NONCE = 'thrive-dashboard-sets-meta-box-nonce';

	/**
	 * Singleton
	 */
	use TD_Singleton;

	/**
	 * TVD_Content_Sets constructor.
	 */
	private function __construct() {

		//Maybe do something like on smart site  add check before enqueue & output templates
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'admin_print_footer_scripts', array( $this, 'backbone_templates' ) );

		add_action( 'rest_api_init', array( $this, 'admin_create_rest_routes' ) );

		/* cache-related actions */
		add_action( 'save_post', array( $this, 'on_save_post_clear_cache' ), 10, 2 );
		add_action( 'saved_term', array( $this, 'on_save_term_clear_cache' ) );
		add_action( 'delete_post', array( $this, 'on_delete_post_clear_cache' ), 10, 2 );

		if ( $this->show_ui() ) {
			add_action( 'wp_after_insert_post', array( $this, 'after_save_post' ), 10, 2 );

			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

			add_action( 'admin_print_scripts-post-new.php', array( $this, 'edit_post_admin_script' ) );
			add_action( 'admin_print_scripts-post.php', array( $this, 'edit_post_admin_script' ) );
		}

		require_once __DIR__ . '/content-set/class-utils.php';
		require_once __DIR__ . '/content-set/class-set.php';
		require_once __DIR__ . '/content-set/class-rule.php';
		require_once __DIR__ . '/content-set/class-post-rule.php';
		require_once __DIR__ . '/content-set/class-term-rule.php';
		require_once __DIR__ . '/content-set/class-blog-rule.php';
		require_once __DIR__ . '/content-set/class-search-result-rule.php';
		require_once __DIR__ . '/content-set/class-archive-rule.php';

		\TVD\Content_Sets\Set::init();
	}

	/**
	 * Adds the admin meta boxes
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'tvd_content_sets',
			esc_html__( 'Thrive Content Sets', 'thrive-dash' ),
			array( $this, 'content_sets_meta_box' ),
			null,
			'advanced',
			'high'
		);
	}

	/**
	 * Callback for adding meta boxes for content sets
	 *
	 * @return mixed
	 */
	public function content_sets_meta_box() {
		return require dirname( __FILE__ ) . '/meta-boxes/content-sets.php';
	}

	/**
	 * Edit post - scripts + styles
	 */
	public function edit_post_admin_script() {
		$matches = \TVD\Content_Sets\Set::get_items_that_static_match( get_post() );

		tve_dash_enqueue_style( 'thrive-admin-dashboard-edit-post', TVD_Smart_Const::url( 'assets/admin/css/styles-edit-post.css' ) );
		tve_dash_enqueue_script( 'thrive-admin-dashboard-edit-post', TVD_Smart_Const::url( 'assets/admin/js/dist/edit-post.min.js' ), array( 'jquery' ), false, true );
		wp_localize_script( 'thrive-admin-dashboard-edit-post', 'TD', array(
			'sets'    => \TVD\Content_Sets\Set::get_items_for_dropdown(),
			'matches' => \TVD\Content_Sets\Set::get_items_for_dropdown( $matches ),
		) );
	}

	/**
	 * Enqueue Content Sets scripts & styles
	 */
	public function enqueue_scripts() {
		if ( $this->allow_enqueue_scripts( tve_get_current_screen_key() ) ) {

			tve_dash_enqueue_style( 'tvd-content-sets-admin', TVD_Smart_Const::url( 'assets/admin/css/styles-content-sets.css' ) );

			tve_dash_enqueue_script( 'tvd-content-sets-admin', TVD_Smart_Const::url( 'assets/admin/js/dist/content-sets-admin.min.js' ), array(
				'jquery',
				'backbone',
				'tve-dash-main-js',
			), false, true );

			$public_post_types = \TVD\Content_Sets\Post_Rule::get_content_types();

			wp_localize_script( 'tvd-content-sets-admin', 'TD_SETS', array(
				'routes'     => array(
					'base' => get_rest_url() . 'tss/v1/content-sets',
				),
				'post_types' => array_keys( $public_post_types ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'fields'     => array(
					'title'          => \TVD\Content_Sets\Rule::FIELD_TITLE,
					'tag'            => \TVD\Content_Sets\Rule::FIELD_TAG,
					'category'       => \TVD\Content_Sets\Rule::FIELD_CATEGORY,
					'published_date' => \TVD\Content_Sets\Rule::FIELD_PUBLISHED_DATE,
					'topic'          => \TVD\Content_Sets\Rule::FIELD_TOPIC,
					'difficulty'     => \TVD\Content_Sets\Rule::FIELD_DIFFICULTY,
					'label'          => \TVD\Content_Sets\Rule::FIELD_LABEL,
					'author'         => \TVD\Content_Sets\Rule::FIELD_AUTHOR,
				),
				'operators'  => array(
					'is'           => \TVD\Content_Sets\Rule::OPERATOR_IS,
					'not_is'       => \TVD\Content_Sets\Rule::OPERATOR_NOT_IS,
					'grater_equal' => \TVD\Content_Sets\Rule::OPERATOR_GRATER_EQUAL,
					'lower_equal'  => \TVD\Content_Sets\Rule::OPERATOR_LOWER_EQUAL,
					'within_last'  => \TVD\Content_Sets\Rule::OPERATOR_WITHIN_LAST,
				),
				'options'    => array(
					'general'    => array(
						'content'  => $this->localize_content_values( $public_post_types ),
						'field'    => array(
							array(
								'value'    => '',
								'disabled' => true,
								'label'    => __( 'Select your field or taxonomy', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_ALL,
								'label' => __( 'All', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_TITLE,
								'label' => __( 'Title', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_TAG,
								'label' => __( 'Tag', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_CATEGORY,
								'label' => __( 'Category', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_PUBLISHED_DATE,
								'label' => __( 'Published', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_AUTHOR,
								'label' => __( 'Author', 'thrive-dash' ),
							),
						),
						'operator' => array(
							array(
								'value'    => '',
								'disabled' => true,
								'label'    => __( 'Choose your condition', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::OPERATOR_IS,
								'label' => __( 'Is', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::OPERATOR_NOT_IS,
								'label' => __( 'Is not', 'thrive-dash' ),
							),
						),
					),
					/**
					 * Allow Protected files to hook here and modify the exceptions list
					 *
					 * @param array $exceptions
					 */
					'exceptions' => apply_filters( 'tvd_content_sets_get_content_types_exceptions', array(
						'tva_courses'    => array(
							array(
								'value'    => '',
								'disabled' => true,
								'label'    => __( 'Select your field or taxonomy', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_ALL,
								'label' => __( 'All', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_TITLE,
								'label' => __( 'Title', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_TOPIC,
								'label' => __( 'Topic', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_DIFFICULTY,
								'label' => __( 'Difficulty', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::FIELD_LABEL,
								'label' => __( 'Label', 'thrive-dash' ),
							),
						),
						'published_date' => array(
							array(
								'value'    => '',
								'disabled' => true,
								'label'    => __( 'Choose your condition', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::OPERATOR_LOWER_EQUAL,
								'label' => __( 'On or before', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::OPERATOR_GRATER_EQUAL,
								'label' => __( 'On or after', 'thrive-dash' ),
							),
							array(
								'value' => \TVD\Content_Sets\Rule::OPERATOR_WITHIN_LAST,
								'label' => __( 'Within the last', 'thrive-dash' ),
							),
						),
					) ),
				),
				'sets'       => \TVD\Content_Sets\Set::get_items(),
			) );
		}
	}

	/**
	 * Add backbone templates
	 */
	public function backbone_templates() {
		if ( $this->allow_enqueue_scripts( tve_get_current_screen_key() ) ) {
			$templates = tve_dash_get_backbone_templates( TVD_Smart_Const::path( 'views/admin/content-sets-templates' ), 'content-sets-templates' );
			tve_dash_output_backbone_templates( $templates );
		}
	}

	/**
	 * Register the rest route
	 */
	public function admin_create_rest_routes() {
		require_once __DIR__ . '/endpoints/class-tvd-content-sets-controller.php';

		$controller = new TVD_Content_Sets_Controller();
		$controller->register_routes();
	}

	/**
	 * @param array $public_post_types
	 *
	 * @return array[]
	 */
	private function localize_content_values( $public_post_types = array() ) {
		$values = array(
			array(
				'value'    => '',
				'disabled' => true,
				'label'    => __( 'Choose content type', 'thrive-dash' ),
			),
		);

		foreach ( $public_post_types as $key => $name ) {
			$values[] = array(
				'value' => $key,
				'label' => $name,
			);
		}

		$values[] = array(
			'value' => 'tva_courses',
			'label' => __( 'Apprentice Course', 'thrive-dash' ),
		);

		$values[] = array(
			'value' => \TVD\Content_Sets\Rule::HIDDEN_POST_TYPE_BLOG,
			'label' => __( 'Blog Page', 'thrive-dash' ),
		);

		$values[] = array(
			'value' => \TVD\Content_Sets\Rule::HIDDEN_POST_TYPE_SEARCH_RESULTS,
			'label' => __( 'Search Results Page', 'thrive-dash' ),
		);

		$values[] = array(
			'value' => 'archive',
			'label' => __( 'Archive', 'thrive-dash' ),
		);

		return $values;
	}

	/**
	 * Checks if the system allows to include the content sets scripts
	 * Calls a filter that is implemented in other plugins (ex: Thrive Apprentice)
	 *
	 * @param string $screen_id
	 *
	 * @return boolean
	 */
	private function allow_enqueue_scripts( $screen_id ) {
		return apply_filters( 'tvd_content_sets_allow_enqueue_scripts', $screen_id === 'admin_page_tve_dash_smart_site', $screen_id );
	}

	/**
	 * When a post is saved, clear the content sets cache associated with it
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public function on_save_post_clear_cache( $post_id, $post ) {
		if ( $post->post_type === \TVD\Content_Sets\Set::POST_TYPE ) {
			// need to clear everything
			content_set_cache( 'term' )->clear(); // delete any stored term meta
			content_set_cache( 'post' )->clear(); // delete any stored post meta
		} else {
			content_set_cache( $post )->clear( $post_id );
		}
	}

	/**
	 * When a term is saved, clear the content sets cache associated with it
	 *
	 * @param int $term_id
	 */
	public function on_save_term_clear_cache( $term_id ) {
		content_set_cache( 'term' )->clear( $term_id ); // delete any stored term meta
	}

	/**
	 * When a content set is deleted, clear content set cache
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public function on_delete_post_clear_cache( $post_id, $post ) {
		if ( $post->post_type === \TVD\Content_Sets\Set::POST_TYPE ) {
			content_set_cache( 'term' )->clear();
			content_set_cache( 'post' )->clear();
		}
	}

	/**
	 * After the post is saved, we update the content sets that have been modified from /wp-admin/post.php UI
	 *
	 * @param int     $post_id
	 * @param WP_Post $post
	 */
	public function after_save_post( $post_id, $post ) {
		// Security check: verify meta box nonce
		if ( ! isset( $_POST['tvd_content_sets_meta_box'] ) || ! wp_verify_nonce( $_POST['tvd_content_sets_meta_box'], self::META_BOX_NONCE ) ) {
			return;
		}

		$content_sets_ids = isset( $_POST['tvd_matched_content_sets_id'] ) ? $_POST['tvd_matched_content_sets_id'] : '';

		\TVD\Content_Sets\Set::toggle_object_to_set_static_rules( $post, array_filter( explode( ',', $content_sets_ids ) ) );
	}

	/**
	 * Returns true if the system can show the content sets UI
	 *
	 * @return bool
	 */
	public function show_ui() {
		return defined( 'TVD_CONTENT_SETS_SHOW_UI' ) && TVD_CONTENT_SETS_SHOW_UI;
	}
}

/**
 * @return TVD_Content_Sets
 */
function tvd_content_sets() {
	return TVD_Content_Sets::get_instance();
}

add_action( 'init', 'tvd_content_sets', 8 );
