<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

use function TVA\Architect\Course\tcb_course_shortcode;
use function TVA\Architect\Dynamic_Actions\tcb_tva_dynamic_actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Course_Rest_Controller
 *
 * @project: thrive-apprentice
 */
class TCB_Course_Rest_Controller {

	public static $route = '/course_element';

	public function __construct() {
		$this->register_routes();
	}

	public function register_routes() {
		register_rest_route( TVA_Const::REST_NAMESPACE, self::$route . '/html', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_html' ),
				'permission_callback' => array( $this, 'has_access' ),
				'args'                => array(
					'id'      => array(
						'type'     => 'number',
						'required' => true,
					),
					'post_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			),
		) );

		register_rest_route( TVA_Const::REST_NAMESPACE, self::$route . '/structure', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_structure' ),
				'permission_callback' => array( $this, 'has_access' ),
				'args'                => array(
					'id'      => array(
						'type'     => 'number',
						'required' => true,
					),
					'post_id' => array(
						'type'     => 'integer',
						'required' => true,
					),
				),
			),
		) );
	}

	/**
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_html( $request ) {
		$id      = (int) $request->get_param( 'id' );
		$post_id = (int) $request->get_param( 'post_id' );

		if ( ! empty( $id ) ) {

			$parsed_id = $id;

			if ( $id === - 1 ) {
				$parsed_id = TVA_Course_V2::get_active_course_id( $post_id );
			}

			$display_level = $request->get_param( 'display_level' );
			if ( (int) $display_level === - 1 ) {
				$display_level = $post_id;
			}

			$cloud_data = $this->get_cloud_data( $parsed_id, array(
				'display_level' => $display_level,
				'template_id'   => $request->get_param( 'template_id' ),
				'hide_module'   => $request->get_param( 'hide_module' ),
			) );

			return new WP_REST_Response( array(
				'html'  => $cloud_data['content'],
				'css'   => $cloud_data['css'],
				'data'  => $this->get_course_data( $parsed_id, true ),
				'id'    => $id,
				'level' => $request->get_param( 'display_level' ),
			), 200 );
		} else {
			return new WP_REST_Response( array( 'message' => 'Invalid Course ID' ), 401 );
		}
	}

	/**
	 * Returns the Course Structure (Lessons, Chapters, Modules)
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function get_structure( $request ) {
		$id = (int) $request->get_param( 'id' );

		if ( $id === - 1 ) {
			$id = TVA_Course_V2::get_active_course_id( (int) $request->get_param( 'post_id' ) );
		}

		return new WP_REST_Response( $this->get_course_data( $id ), 200 );
	}

	/**
	 * Check if the user has permissions to do API calls
	 *
	 * @return bool
	 */
	public function has_access() {
		return TVA_Product::has_access();
	}

	/**
	 * Returns the course data
	 *
	 * @param integer $id
	 * @param boolean $include_structure
	 *
	 * @return array
	 */
	private function get_course_data( $id, $include_structure = false ) {
		$course      = new TVA_Course_V2( $id );
		$all_content = TVA_Manager::get_all_content( $course->get_wp_term() );

		/**
		 * @var $post WP_Post
		 */
		foreach ( $all_content as $key => $post ) {
			$courseItem = TVA_Post::factory( $post );
			$lessons    = $courseItem->get_lessons();

			$all_content[ $key ]->tva_course_title                     = $post->post_title;
			$all_content[ $key ]->tva_course_description               = strip_tags( $post->post_excerpt );
			$all_content[ $key ]->tva_course_children_count            = count( $lessons );
			$all_content[ $key ]->tva_course_children_completed        = tva_count_completed_items( $lessons );
			$all_content[ $key ]->tva_permalink                        = get_permalink( $post->ID );
			$all_content[ $key ]->tva_course_status                    = $courseItem->is_completed() ? __( 'Completed', 'thrive-apprentice' ) : __( 'Not Completed', 'thrive-apprentice' );
			$all_content[ $key ]->tva_course_type                      = $courseItem->get_type();
			$all_content[ $key ]->tva_course_type_svg                  = tcb_course_shortcode()->get_type_icon( $courseItem );
			$all_content[ $key ]->tva_course_children_count_with_label = tcb_tva_dynamic_actions()->get_children_count_with_label( $courseItem );
			$all_content[ $key ]->tva_course_restriction_label         = tcb_course_shortcode()->get_course_label( $course );
		}

		$return = array(
			'tva_permalink'          => get_term_link( $course->get_id() ),
			'tva_course_title'       => $course->name,
			'tva_course_description' => strip_tags( $course->description ),
			'tva_course_difficulty'  => $course->get_difficulty()->name,
			'tva_course_topic'       => $course->get_topic()->title,
			'tva_course_type'        => $course->type_label,
			'content'                => $all_content,
		);

		if ( $include_structure ) {
			$return['structure'] = $course->load_structure();
		}

		return $return;
	}

	/**
	 * Returns the course cloud data
	 *
	 * @param int   $course_id
	 * @param array $config
	 *
	 * @return array
	 */
	private function get_cloud_data( $course_id, $config = array() ) {
		/**
		 * Allows the system to ignore the cloud default template for apprentice and alawys render the empty template
		 *
		 * - Used in Template Builder WebSite to start a new template from the default one
		 */
		$get_cloud_template = apply_filters( 'tva_get_cloud_default_template', true );

		if ( $get_cloud_template ) {

			$default_template_data = tve_get_cloud_template_data(
				'course',
				array(
					'skip_do_shortcode' => true,
					'id'                => ! empty( $config['template_id'] ) && ! defined( 'TCB_CLOUD_API_LOCAL' ) ? $config['template_id'] : 'default',
					'type'              => 'course',
				)
			);
		}

		if ( empty( $default_template_data ) || $default_template_data instanceof WP_Error ) {

			$content = tcb_course_shortcode()->render( array( 'id' => $course_id ) );
			$css     = '';

		} else {
			$data_ct      = $default_template_data['type'] . '-' . $default_template_data['id'];
			$data_ct_name = esc_attr( $default_template_data['name'] );

			$search  = '[tva_course ';
			$replace = "[tva_course id='$course_id' ct='$data_ct' ct-name='$data_ct_name' ";

			if ( ! empty( $config['display_level'] ) ) {
				$replace .= "display-level='" . $config['display_level'] . "' ";
			}

			if ( ! empty( $config['hide_module'] ) ) {
				$replace .= "hide-module='1' ";
			}

			$content = str_replace( $search, $replace, $default_template_data['content'] );


			$content = do_shortcode( $content );

			$css = $default_template_data['head_css'];
		}

		return array( 'content' => $content, 'css' => $css );
	}
}
