<?php

namespace TVA\Architect\Resources;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Main {
	/**
	 * @var \TVA\Architect\Course\Main
	 */
	private static $instance;

	/**
	 * Contains the List of Shortcodes
	 *
	 * @var array
	 */
	private $shortcodes = array(
		'tva_lesson_resources'      => 'lesson_resources',
		'tva_resource_item'         => 'resource_item',
		'tva_resource_icon'         => 'resource_icon',
		'tva_resource_title'        => 'resource_title',
		'tva_resource_description'  => 'resource_description',
		'tva_resource_button_label' => 'resource_button_label',
	);

	/**
	 * @var bool
	 */
	public static $is_editor_page = false;

	/**
	 * Hooks constructor.
	 */
	public function __construct() {
		$this->hooks();

		foreach ( $this->shortcodes as $shortcode => $function ) {
			add_shortcode( $shortcode, array( $this, $function ) );
		}

		self::$is_editor_page = is_editor_page_raw( true );
	}

	/**
	 * Singleton implementation
	 *
	 * @return Main
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function hooks() {
		add_filter( 'tcb_content_allowed_shortcodes', array( $this, 'content_allowed_shortcodes_filter' ) );

		add_filter( 'tcb_menu_path_resources', array( $this, 'include_resources_menu' ), 10, 1 );

		add_filter( 'tcb_menu_path_resources_label', array( $this, 'include_resources_label_menu' ), 10, 1 );

		add_filter( 'tcb_element_instances', array( $this, 'tcb_element_instances' ) );

		add_filter( 'tcb_filter_cloud_template_data_args', array( $this, 'cloud_template_args' ), 10, 2 );
	}

	public function cloud_template_args( $args, $tag ) {

		if ( ! empty( $args['type'] ) && $args['type'] === 'resources' ) {
			$args['skip_do_shortcode'] = true;
		}

		return $args;
	}

	public function content_allowed_shortcodes_filter( $shortcodes = array() ) {

		if ( self::$is_editor_page ) {
			$shortcodes = array_merge(
				$shortcodes,
				array_keys( $this->shortcodes )
			);
		}

		return $shortcodes;
	}


	public function tcb_element_instances( $elements ) {
		$root_path = \TVA\Architect\Utils::get_integration_path( 'editor-elements/resources' );

		return array_merge( $elements, \TVA\Architect\Utils::get_tcb_elements( $root_path ) );
	}

	public function include_resources_menu() {
		return \TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/menus/resources.php' );
	}

	public function include_resources_label_menu() {
		return \TVA_Const::plugin_path( 'tcb-bridge/editor-layouts/menus/resources_label.php' );
	}

	public function resource_icon( $attr, $content ) {
		/**
		 * @var \TVA_Resource
		 */
		global $tva_resource;

		return $tva_resource->icon_html();
	}

	public function resource_title( $attr, $content ) {
		/**
		 * @var \TVA_Resource
		 */
		global $tva_resource;

		$text = '<h5>' . $tva_resource->title . '</h5>';

		return \TCB_Utils::wrap_content( $text, 'div', '', 'tva-resource-title-text', [ 'contenteditable' => 'false' ] );
	}

	public function resource_description( $attr, $content ) {
		/**
		 * @var \TVA_Resource
		 */
		global $tva_resource;

		$text = '<p>' . $tva_resource->content . '</p>';

		return \TCB_Utils::wrap_content( $text, 'div', '', 'tva-resource-description-text', [ 'contenteditable' => 'false' ] );
	}

	public function resource_item( $attr, $content ) {
		/**
		 * @var \TVA_Resource
		 */
		global $tva_shortcode_lesson_resources;

		$html              = '';
		$shortcode_classes = '';
		/**
		 * Prevent YOAST doing bad shit
		 */
		if ( empty( $attr ) ) {
			$attr = [];
		}
		if ( ! empty( $attr['class'] ) ) {
			$shortcode_classes = $attr['class'];
			unset( $attr['class'] );
		}
		/**
		 * Render based on resources type
		 */
		if ( empty( $tva_shortcode_lesson_resources ) ) {
			$html = $this->render_no_resources_shortcode( $content, $shortcode_classes, $attr );
		} else {
			global $tva_resource;

			foreach ( $tva_shortcode_lesson_resources as $tva_resource ) {
				$classes = $tva_resource->is_downloadable() ? 'tva-item-download ' : '';

				if ( ! $tva_resource->is_openable() ) {
					$classes .= 'tva-item-no-open ';
				}

				if ( strlen( $tva_resource->content ) === 0 ) {
					$classes .= 'tva-item-no-description ';
				}

				if ( ! empty( $shortcode_classes ) ) {
					$classes .= str_replace( [ 'tva-item-download', 'tva-item-no-description', 'tva-item-no-open' ], [ '', '', '' ], $shortcode_classes );
				}
				$item_content = str_replace(
					[
						'{{tva_resource_open}}',
						'{{tva_resource_download}}',
					], [
					$tva_resource->get_url(),
					$tva_resource->get_download_url(),
				], $content );
				$html         .= \TCB_Utils::wrap_content( do_shortcode( $item_content ), 'div', '', $classes, $attr );
			}
			$tva_resource = null;
		}

		return $html;
	}

	public function lesson_resources( $attr, $content ) {
		$html = '';

		$should_render      = false;
		$is_lesson          = get_post_type() === \TVA_Const::LESSON_POST_TYPE;
		$is_dynamic         = ! empty( $attr['data-dynamic'] );
		$is_inside_template = ! empty( $attr['data-inside-template'] );


		/**
		 * If dynamic and current post is lesson then
		 * renders resources from current lesson
		 */
		if ( $is_dynamic && $is_lesson ) {
			$attr['data-lesson'] = get_the_ID();
		}

		if ( ! empty( $attr['data-lesson'] ) ) {
			$lesson = new \TVA_Lesson( (int) $attr['data-lesson'] );

			if ( $is_inside_template && $lesson->has_resources_in_content() ) {

				if ( ! self::$is_editor_page ) {
					return $html;
				} elseif ( empty( \Thrive_Utils::inner_frame_id() ) ) {
					$attr['class'] .= ' tcb-permanently-hidden';
				}
			}

			$course = $lesson->get_course_v2();

			if ( ! empty( $course ) ) {
				$attr['data-course'] = $course->get_id();

				/* check if the user can see the resources */
				$course_published = $course->is_published();
				$lesson_published = $lesson->is_published();
				$has_access       = ( $course_published && $lesson_published && tva_access_manager()->has_access_to_object( $lesson->get_the_post() ) ) || tva_is_preview();

				global $tva_shortcode_lesson_resources;

				$tva_shortcode_lesson_resources = $lesson->get_resources();

				if ( ( $has_access && ! empty( $tva_shortcode_lesson_resources ) ) || self::$is_editor_page || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
					$should_render = true;
					if ( self::$is_editor_page ) {
						if ( ! $lesson_published ) {
							$attr['class'] .= ' tva-lesson-unpublished';
						} elseif ( ! $course_published ) {
							$attr['class'] .= ' tva-course-unpublished';
						}
						if ( empty( $tva_shortcode_lesson_resources ) ) {
							$attr['class'] .= ' tva-no-resources';
						}
					}
				}
			}
		}

		/**
		 * Allow forcing the rendering
		 */
		if ( apply_filters( 'tva_render_resources_shortcode', $should_render ) ) {
			$html = \TCB_Utils::wrap_content( do_shortcode( $content ), 'div', '', '', $attr );
		}

		return $html;
	}

	/**
	 * Applies dynamic labels for resource buttons set by the user
	 * - keep this for backwards compatibility
	 *
	 * @param $attr
	 * @param $content
	 *
	 * @return mixed|string
	 * @deprecated
	 */
	public function resource_button_label( $attr, $content ) {
		$labels      = \TVA_Dynamic_Labels::get( 'course_structure' );
		$button_type = 'resources_' . $attr['type'];

		$label = ! empty( $labels[ $button_type ]['singular'] ) ? $labels[ $button_type ]['singular'] : ucfirst( $attr['label'] );

		return '<span class="thrive-inline-shortcode" contenteditable="false">
					<span class="thrive-shortcode-content" contenteditable="false" data-extra_key="" data-option-inline="1" data-shortcode="tva_dynamic_actions_resources_' . $attr['type'] . '_label" data-shortcode-name="Resource ' . $attr['type'] . ' label" style="" contenteditable="false">
						' . $label . '
					</span>
				</span>';
	}

	/**
	 * Render a Resources section containing a list of resources that belong to the lesson sent in $attr['id'], or the global post
	 *
	 * @param array $attr
	 *
	 * @return string
	 */
	public function default_lesson_resources( $attr = [] ) {
		$lesson_id = empty( $attr['id'] ) ? get_the_ID() : $attr['id'];

		$lesson    = new \TVA_Lesson( (int) $lesson_id );
		$classes   = '';
		$course    = $lesson->get_course_v2();
		$course_id = 0;

		if ( ! empty( $course ) ) {
			$course_id = $course->get_id();
			if ( ! $lesson->is_published() ) {
				$classes .= 'tva-lesson-unpublished';
			} elseif ( ! $course->is_published() ) {
				$classes .= 'tva-course-unpublished';
			}
		}

		$path = 'tcb-bridge/editor-layouts/elements/resources/shortcode.phtml';

		$resources = $lesson->get_resources();

		$template_settings       = tva_get_setting( 'template' );
		$resources_label         = isset( $template_settings['resources_label'] ) ? $template_settings['resources_label'] : 'Resources';
		$course_structure_labels = \TVA_Dynamic_Labels::get( 'course_structure' );
		$resources_label         = isset( $course_structure_labels['course_resources']['plural'] ) ? $course_structure_labels['course_resources']['plural'] : $resources_label;
		$resources_open          = isset( $template_settings['resources_open'] ) ? $template_settings['resources_open'] : 'Open';
		$resources_open          = isset( $course_structure_labels['resources_open']['singular'] ) ? $course_structure_labels['resources_open']['singular'] : $resources_open;
		$resources_download      = isset( $template_settings['resources_download'] ) ? $template_settings['resources_download'] : 'Download';
		$resources_download      = isset( $course_structure_labels['resources_download']['singular'] ) ? $course_structure_labels['resources_download']['singular'] : $resources_download;

		if ( empty( $resources ) ) {
			$path      = 'tcb-bridge/editor-layouts/elements/resources/no-resources.phtml';
			$resources = $this->get_default_resources();

			$classes .= ' tva-no-resources';
		}

		return tva_get_file_contents(
			$path,
			array(
				'resources'       => $resources,
				'resources_label' => $resources_label,
				'lesson_id'       => $lesson_id,
				'course_id'       => $course_id,
				'classes'         => $classes,
				'dynamic'         => ! empty( $attr['dynamic'] ),
				'download_label'  => $resources_download,
				'open_label'      => $resources_open,
			)
		);
	}

	/**
	 * Default data if the lesson doesnt have resources attached
	 *
	 * @return array[]
	 */
	public function get_default_resources() {
		$content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua';

		return [
			[
				'title'        => 'Resource 1',
				'icon'         => \TVA_Resource::$icons['video'],
				'content'      => $content,
				'downloadable' => true,
			],
			[
				'title'        => 'Resource 2',
				'icon'         => \TVA_Resource::$icons['audio'],
				'content'      => $content,
				'downloadable' => true,
			],
			[
				'title'        => 'Resource 3',
				'icon'         => \TVA_Resource::$icons['pdf'],
				'content'      => $content,
				'downloadable' => true,
			],
			[
				'title'        => 'Resource 4',
				'icon'         => \TVA_Resource::$icons['url'],
				'content'      => $content,
				'downloadable' => false,
			],
		];
	}

	/**
	 * Preserve element styling in editor if the lesson still doesnt have resources
	 *
	 * @param $content
	 * @param $shortcode_classes
	 * @param $attr
	 *
	 * @return string
	 */
	public function render_no_resources_shortcode( $content, $shortcode_classes, $attr ) {
		$html = '';

		$search = [
			'[tva_resource_icon]',
			'[tva_resource_title]',
			'[tva_resource_description]',
			'[tva_resource_open]',
			'[tva_resource_download]',
		];
		foreach ( $this->get_default_resources() as $resource ) {
			$classes = $resource['downloadable'] ? 'tva-item-download ' : '';

			if ( ! empty( $shortcode_classes ) ) {
				$classes .= str_replace( 'tva-item-download', '', $shortcode_classes );
			}
			$replace = [
				$resource['icon'],
				\TCB_Utils::wrap_content( '<h5>' . $resource['title'] . '</h5>', 'div', '', 'tva-resource-title-text', [ 'contenteditable' => 'false' ] ),
				\TCB_Utils::wrap_content( '<p>' . $resource['content'] . '</p>', 'div', '', 'tva-resource-description-text', [ 'contenteditable' => 'false' ] ),
				'',
				'',
			];

			$html .= \TCB_Utils::wrap_content( str_replace( $search, $replace, $content ), 'div', '', $classes, $attr );
		}

		return $html;
	}
}


/**
 * Returns the instance of the Course Shortcode
 *
 * @return Main
 */
function tva_resource_content() {
	return Main::get_instance();
}
