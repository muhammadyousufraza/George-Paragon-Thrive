<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

if ( ! class_exists( 'TCB_Post_List_Element' ) ) {
	require_once TVE_TCB_ROOT_PATH . 'inc/classes/elements/class-tcb-post-list-element.php';
}

/**
 * Class TCB_Course_List_Element
 *
 * @project  : thrive-apprentice
 */
class TCB_Course_List_Element extends TCB_Post_List_Element {

	/**
	 * @var string
	 */
	protected $_tag = 'course_list';

	/**
	 * TCB_Course_List_Element constructor
	 *
	 * @param string $tag
	 */
	public function __construct( $tag = '' ) {
		parent::__construct( $tag );

		add_filter( 'tcb_categories_order', array( $this, 'add_category_to_order' ) );

		add_action( 'tcb_before_get_content_template', array( $this, 'before_content_template' ), 10, 2 );
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Course List', 'thrive-apprentice' );
	}

	/**
	 * Element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return TVA\Architect\Course_List\Main::IDENTIFIER;
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'course-list';
	}

	/**
	 * Decide when to hide the Course List Element
	 *
	 * @return bool
	 */
	public function hide() {

		if ( Thrive_Utils::is_theme_template() ) {
			return ! tva_is_apprentice_template();
		}

		return parent::hide();
	}

	/**
	 * TODO: this should be removed later on in the process
	 *
	 * @return string
	 */
	public function html() {
		/**
		 * Allows the system to ignore the cloud default template for apprentice and always render the empty template
		 *
		 * - Used in Template Builder WebSite to start a new template from the default one
		 */
		if ( apply_filters( 'tva_get_cloud_default_template', '__return_true' ) ) {
			return parent::html();
		}

		return \TVA\Architect\Course_List\tcb_course_list_shortcode()->render();
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return tva_is_apprentice_template() ? \TVA\Architect\Visual_Builder\tcb_tva_visual_builder()->get_elements_category() : $this->get_thrive_integrations_label();
	}

	/**
	 * This is only a placeholder element
	 *
	 * @return bool
	 */
	public function is_placeholder() {
		return false;
	}

	/**
	 * Returns Elements Group Label
	 *
	 * @return string
	 */
	public function elements_group_label() {
		return __( 'Course List Elements', 'thrive-apprentice' );
	}

	/**
	 * Before the course content template gets applied modify the post_content to include the custom element data
	 * Include the custom query, pagination text, pagination type and other info that the user has inserted previously
	 *
	 * @param WP_Post $post
	 * @param array   $meta
	 */
	public function before_content_template( $post, $meta ) {
		if ( is_array( $meta ) && $meta['type'] === 'course_list' ) {

			$extra_params = array(
				'query'           => ! empty( $_REQUEST['query'] ) ? stripslashes( $_REQUEST['query'] ) : '',
				'no_posts_text'   => ! empty( $_REQUEST['no_posts_text'] ) ? esc_attr( $_REQUEST['no_posts_text'] ) : '',
				'pagination_type' => ! empty( $_REQUEST['pagination_type'] ) ? $_REQUEST['pagination_type'] : '',
				'posts_per_page'  => ! empty( $_REQUEST['posts_per_page'] ) ? (int) $_REQUEST['posts_per_page'] : 0,
			);

			$replace_string = '[tva_course_list ';
			foreach ( $extra_params as $key => $value ) {
				if ( ! empty( $value ) ) {
					$replace_string .= "$key=\"$value\" ";
				}
			}

			$post->post_content = str_replace( '[tva_course_list ', $replace_string, $post->post_content );
		}
	}

	/**
	 * Components that apply only to this
	 *
	 * @return array
	 */
	public function own_components() {
		$components = parent::own_components();

		/* re-use the post-list component */
		$components['course_list'] = $components['post_list'];


		$components['course_list']['config'] = array_merge( $components['course_list']['config'], array(
			'TopicFilter'  => array(
				'config'  => array(
					'name'  => '',
					'label' => __( 'Display topic filter', 'thrive-apprentice' ),
				),
				'extends' => 'Switch',
			),
			'CourseSearch' => array(
				'config'  => array(
					'name'  => '',
					'label' => __( 'Display course search', 'thrive-apprentice' ),
				),
				'extends' => 'Switch',
			),
			'MessageColor' => array(
				'config'  => array(
					'default' => '#999999',
					'label'   => 'Color',
					'options' => array(
						'output' => 'object',
					),
				),
				'extends' => 'ColorPicker',
			),
		) );

		//Change some labels
		$components['course_list']['config']['Linker']['config']['label'] = __( 'Link entire item to course', 'thrive-apprentice' );

		unset(
			$components['post_list'],
			$components['course_list']['config']['Featured'],
			$components['course_list']['config']['ContentSize'],
			$components['course_list']['config']['WordsTrim'],
			$components['course_list']['config']['ReadMoreText']
		);

		return $components;
	}

	/**
	 * Called from tcb_categories_order filter
	 *
	 * Adds elements_group_label category to order array
	 *
	 * @param array $order
	 */
	public function add_category_to_order( $order = array() ) {
		$order[4] = $this->elements_group_label();

		return $order;
	}

	/**
	 * Element info - disabled, Course List has no article yet
	 *
	 * @return string|string[][]
	 */
	public function info() {
		return array();
	}

	/**
	 * Override the parent implementation of this method in order to add the 'specific-modal' attribute.
	 *
	 * Returns the HTML placeholder for an element (contains a wrapper, and a button with icon + element name)
	 *
	 * @param string $title Optional. Defaults to the name of the current element
	 *
	 * @return string
	 */
	public function html_placeholder( $title = null ) {
		if ( empty( $title ) ) {
			$title = $this->name();
		}

		$post_list_args = TCB_Post_List::default_args();

		$attr = array(
			'query'          => $post_list_args['query'],
			'ct'             => $this->tag() . '-0',
			'tcb-elem-type'  => $this->tag(),
			'element-name'   => esc_attr( $this->name() ),
			'specific-modal' => 'course-list',
		);

		$extra_attr = '';

		foreach ( $attr as $key => $value ) {
			$extra_attr .= 'data-' . $key . '="' . $value . '" ';
		}

		return tcb_template( 'elements/element-placeholder', array(
			'icon'       => $this->icon(),
			'class'      => 'tcb-ct-placeholder tcb-compact-element',
			'title'      => $title,
			'extra_attr' => $extra_attr,
		), true );
	}
}

return new TCB_Course_List_Element();
