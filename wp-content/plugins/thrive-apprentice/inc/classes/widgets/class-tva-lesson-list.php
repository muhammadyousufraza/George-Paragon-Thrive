<?php

class TVA_Lesson_List_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		parent::__construct(
			'tva_lesson_list_widget', // Base ID
			__( 'Thrive Apprentice Lessons List', 'thrive-apprentice' ), // Name
			array(
				'description' => __( 'Thrive Apprentice Lessons List Widget', 'thrive-apprentice' ),
			) // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return mixed
	 */
	public function widget( $args, $instance ) {

		$obj = get_queried_object();

		if ( ( is_page() && tva_get_settings_manager()->is_index_page( $obj->ID ) ) ) {
			return;
		}

		echo $args['before_widget'];

		include dirname( __FILE__ ) . '/templates/lesson-list.php';

		echo $args['after_widget'];
	}

	/**
	 * Handles updating settings for the current Custom Menu widget instance.
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 *
	 * @return array Updated settings to save.
	 * @since  3.0.0
	 * @access public
	 *
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title'] = sanitize_text_field( $new_instance['title'] );

		$instance['collapse']             = array();
		$instance['collapse']['modules']  = ! empty( $new_instance['collapse'] ) && ! empty( $new_instance['collapse']['modules'] ) ? 1 : 0;
		$instance['collapse']['chapters'] = ! empty( $new_instance['collapse'] ) && ! empty( $new_instance['collapse']['chapters'] ) ? 1 : 0;

		return $instance;
	}

	/**
	 * Outputs the settings form for the Custom Menu widget.
	 *
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form( $instance ) {

		$instance = wp_parse_args( $instance, array(
			'title'    => '',
			'collapse' => array(
				'modules'  => false,
				'chapters' => false,
			),
		) );

		$modules_checked  = (bool) ( $instance['collapse']['modules'] );
		$chapters_checked = (bool) ( $instance['collapse']['chapters'] );
		?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>"/>
		</p>
		<p><?php echo __( 'Display Settings:', 'thrive-apprentice' ); ?></p>
		<p>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'collapse[modules]' ); ?>" name="<?php echo $this->get_field_name( 'collapse[modules]' ); ?>" <?php checked( $modules_checked ); ?>>
			<label for="<?php echo $this->get_field_id( 'collapse[modules]' ); ?>"><?php _e( 'Collapse Modules', 'thrive-apprentice' ); ?></label>
			<br>
			<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'collapse[chapters]' ); ?>" name="<?php echo $this->get_field_name( 'collapse[chapters]' ); ?>" <?php checked( $chapters_checked ); ?>>
			<label for="<?php echo $this->get_field_id( 'collapse[chapters]' ); ?>"><?php _e( 'Collapse Chapters', 'thrive-apprentice' ); ?></label>
		</p>

		<?php
	}
}
