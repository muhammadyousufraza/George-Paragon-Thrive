<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TCB_Widget_Element
 */
class TCB_Widget_Element extends TCB_Element_Abstract {

	/**
	 * @var WP_Widget
	 */
	private $widget;

	/**
	 * TCB_Widget_Element constructor.
	 *
	 * @param $widget WP_Widget
	 *
	 * @throws Exception
	 */
	public function __construct( $widget ) {

		if ( $widget instanceof WP_Widget ) {

			$tag = 'widget_' . $widget->id_base;

			$this->widget = $widget;

			if ( method_exists( $widget, 'enqueue_admin_scripts' ) ) {
				$widget->enqueue_admin_scripts();
			}

			if ( method_exists( $widget, 'render_control_template_scripts' ) ) {
				$widget->render_control_template_scripts();
			}

			parent::__construct( $tag );
		} else {
			throw new Exception( 'Constructor argument should be a WP_Widget instance' );
		}
	}

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		$name = $this->widget->name;

		/* some widgets already have 'Widget' at the end of their name */
		if ( strpos( $name, 'Widget' ) === false ) {
			$name .= ' ' . __( 'Widget', 'thrive-cb' );
		}

		return $name;
	}

	/**
	 * Set alternate for widget so we can better find him with search
	 *
	 * @return mixed|string|string[]|null
	 */
	public function alternate() {
		return $this->widget->id_base;
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		/**
		 * Filter widget icon in case we want something special
		 *
		 * @param string    $icon   by default it's the wordpress icon
		 * @param WP_Widget $widget the current widget
		 *
		 * @return string
		 */
		return apply_filters( 'tcb_widget_element_icon', 'wordpress', $this->widget );
	}

	/**
	 * Button element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.thrv_' . $this->widget->id_base;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		/**
		 * Filter widget category label in case we want to group widgets differently
		 *
		 * @param string    $icon   by default we have 'Widgets'
		 * @param WP_Widget $widget the current widget
		 *
		 * @return string
		 */
		return apply_filters( 'tcb_widget_element_category', static::get_widgets_label(), $this->widget );
	}

	/**
	 * HTML of the current widget
	 *
	 * @return string
	 */
	public function html() {
		return '<div class="thrv_wrapper thrv_widget thrv_' . $this->widget->id_base . ' tcb-empty-widget tcb-elem-placeholder">
					<span class="tcb-inline-placeholder-action with-icon">' . tcb_icon( 'wordpress', true, 'editor' ) . $this->name() . '</span>
				</div>';
	}

	/**
	 * Component and control config
	 *
	 * @return array
	 */
	public function own_components() {
		return array(
			'widget'           => [
				'config' => [],
			],
			'styles-templates' => [ 'hidden' => true ],
			'typography'       => [ 'hidden' => true ],
			'animation'        => array(
				'disabled_controls' =>
					array( '.btn-inline.anim-link', '.btn-inline.anim-popup' ),
			),
			'layout'           => [
				'disabled_controls' => [],
			],
			'borders'          => [
				'config' => [
					'Borders' => [
						'important' => true,
					],
					'Corners' => [
						'important' => true,
					],
				],
			],
		);
	}
}
