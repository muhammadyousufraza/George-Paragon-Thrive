<?php

class TVA_Menu extends WP_Nav_Menu_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		WP_Widget::__construct(
			'tva_menu_widget', // Base ID
			__( 'Thrive Apprentice Menu', 'thrive-apprentice' ), // Name
			array(
				'description' => __( 'Thrive Apprentice Menu widget', 'thrive-apprentice' ),
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

		$title = apply_filters( 'widget_title', isset( $instance['title'] ) ? $instance['title'] : '' );

		echo $args['before_widget'];

		include TVA_Const::plugin_path( 'inc/menu.php' );

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
		if ( ! empty( $new_instance['nav_menu_logged_in'] ) ) {
			$instance['nav_menu_logged_in'] = (int) $new_instance['nav_menu_logged_in'];
		}

		if ( ! empty( $new_instance['nav_menu_logged_out'] ) ) {
			$instance['nav_menu_logged_out'] = (int) $new_instance['nav_menu_logged_out'];
		}

		return $instance;
	}

	/**
	 * Outputs the settings form for the Custom Menu widget.
	 *
	 * @param array $instance
	 *
	 */
	public function form( $instance ) {
		global $wp_customize;
		$title               = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu_logged_in  = isset( $instance['nav_menu_logged_in'] ) ? $instance['nav_menu_logged_in'] : '';
		$nav_menu_logged_out = isset( $instance['nav_menu_logged_out'] ) ? $instance['nav_menu_logged_out'] : '';

		// Get menus
		$menus = wp_get_nav_menus();
		// If no menus exists, direct the user to go and create some.
		?>
		<p class="nav-menu-widget-no-menus-message" <?php if ( ! empty( $menus ) ) {
			echo ' style="display:none" ';
		} ?> >
			<?php
			if ( $wp_customize instanceof WP_Customize_Manager ) {
				$url = 'javascript: wp.customize.panel( "nav_menus" ).focus();';
			} else {
				$url = admin_url( 'nav-menus.php' );
			}
			?>
			<?php echo sprintf( __( 'No menus have been created yet. <a href="%s">Create a menu</a>.' ), esc_attr( $url ) ); ?>
		</p>
		<div class="nav-menu-widget-form-controls" <?php if ( empty( $menus ) ) {
			echo ' style="display:none" ';
		} ?>>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ) ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>"
					   value="<?php echo esc_attr( $title ); ?>"/>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'nav_menu_logged_in' ); ?>"><?php _e( 'Select Logged In Menu:' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'nav_menu_logged_in' ); ?>" name="<?php echo $this->get_field_name( 'nav_menu_logged_in' ); ?>">
					<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
					<?php foreach ( $menus as $menu ) : ?>
						<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $nav_menu_logged_in, $menu->term_id ); ?>>
							<?php echo esc_html( $menu->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'nav_menu_logged_out' ); ?>"><?php _e( 'Select Logged Out Menu:' ); ?></label>
				<select id="<?php echo $this->get_field_id( 'nav_menu_logged_out' ); ?>" name="<?php echo $this->get_field_name( 'nav_menu_logged_out' ); ?>">
					<option value="0"><?php _e( '&mdash; Select &mdash;' ); ?></option>
					<?php foreach ( $menus as $menu ) : ?>
						<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $nav_menu_logged_out, $menu->term_id ); ?>>
							<?php echo esc_html( $menu->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</p>
		</div>
		<?php
	}
}
