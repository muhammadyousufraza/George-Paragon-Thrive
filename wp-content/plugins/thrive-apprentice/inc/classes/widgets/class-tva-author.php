<?php

class TVA_Author_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'tva_author_widget', // Base ID
			__( 'Thrive Apprentice Teacher', 'thrive-apprentice' ), // Name
			array(
				'description' => __( 'Thrive Apprentice Teacher Widget', 'thrive-apprentice' ),
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

		include TVA_Const::plugin_path( 'inc/author.php' );

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
		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
		}

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
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		?>
		<div class="nav-menu-widget-form-controls">
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ) ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>"
					   value="<?php echo esc_attr( $title ); ?>"/>
			</p>
		</div>
		<?php

		return;
	}
}
