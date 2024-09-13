<?php

class Thrive_Leads_Widget extends WP_Widget {
	/**
	 * Constructor for the widget
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'widget_thrive_leads',
			'description' => __( 'Simple widget to control the placement of thrive leads forms in the widget areas.', 'thrive-leads' ),
		);
		parent::__construct( 'widget_thrive_leads', 'Thrive Leads Widget', $widget_ops );

		add_action( 'save_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'deleted_post', array( &$this, 'flush_widget_cache' ) );
		add_action( 'switch_theme', array( &$this, 'flush_widget_cache' ) );
		add_action( 'dynamic_sidebar', array( $this, 'before_dynamic_sidebar' ) );
	}

	/**
	 * Call this before the widget is rendered
	 */
	public function before_dynamic_sidebar() {
		$instances = $this->get_settings();
		if ( array_key_exists( $this->number, $instances ) ) {
			$instance = $instances[ $this->number ];
		}

		if ( ! empty( $instance['lead_group'][0] ) && is_editor_page_raw( true ) ) {
			$this->before_widget_render( $instance['lead_group'][0] );
		}
	}

	/**
	 * Call function before a widget is rendered with ajax
	 *
	 * @param string|int $group_id
	 */
	public function before_widget_render( $group_id ) {
		$group_id = (int) $group_id;
		$groups   = tve_leads_get_groups(
			array(
				'full_data'       => false,
				'tracking_data'   => false,
				'active_tests'    => false,
				'completed_tests' => false,
			)
		);
		$groups   = array_filter( $groups, static function ( $group ) use ( $group_id ) {
			return $group->ID === $group_id;
		} );
		$groups   = array_values( $groups );

		if ( empty( $groups ) ) {
			return;
		}
		global $tve_lead_group;
		$tve_lead_group = $groups[0];

		if ( empty( $tve_lead_group ) ) {
			return;
		}

		/* only get form types for a single group - the one that is matched by the current request */
		$tve_lead_group->form_types = tve_leads_get_form_types( array(
			'lead_group_id'  => $tve_lead_group->ID,
			'tracking_data'  => false,
			'get_variations' => true,
			'no_content'     => false,
		) );

		tve_leads_register_group();
		if ( ! empty( $GLOBALS['tve_lead_forms']['widget']['variation'] ) ) {
			$GLOBALS['tve_lead_forms']['widget']['form_output'] = tve_editor_custom_content( $GLOBALS['tve_lead_forms']['widget']['variation'] );
		}
	}

	/**
	 * Frontend part of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {

		if ( empty( $GLOBALS['tve_lead_forms']['widget'] ) || empty( $GLOBALS['tve_lead_forms']['widget']['form_output'] ) || empty( $instance['lead_group'] ) ) {
			return;
		}

		$content = '';

		if ( in_array( $GLOBALS['tve_lead_forms']['widget']['form_type']->post_parent, $instance['lead_group'] ) ) {
			if ( ! empty( $GLOBALS['tve_lead_forms']['widget']['placeholder'] ) ) {
				$args['before_widget'] = tve_leads_get_form_placeholder( 'before_widget' ) . $args['before_widget'];
				$args['after_widget']  .= '</div>';
			}

			/**
			 * this is not empty when AJAX loading of forms is enabled
			 */
			if ( ! empty( $GLOBALS['tve_lead_forms']['widget']['placeholder'] ) ) {
				$content = $GLOBALS['tve_lead_forms']['widget']['form_output'];
			} else {
				echo $args['before_widget'];
				$content = tve_leads_display_form_widget();
				echo $args['after_widget'];
			}
		}

		if ( empty( $content ) ) {
			return;
		}

		echo $args['before_widget'] . $content . $args['after_widget'];
	}

	/**
	 * Update function of the widget
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance               = $old_instance;
		$instance['lead_group'] = $new_instance['lead_group'];
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset( $alloptions['widget_thrive_leads'] ) ) {
			delete_option( 'widget_thrive_leads' );
		}

		return $instance;
	}

	function flush_widget_cache() {
		wp_cache_delete( 'widget_thrive_leads', 'widget' );
	}

	/**
	 * Admin form of the widget. Get all lead groups and display them in a select.
	 *
	 * @param array $instance
	 */
	function form( $instance ) {
		$lead_group_ids = ! empty( $instance['lead_group'] ) ? $instance['lead_group'] : array();
		if ( ! is_array( $lead_group_ids ) ) {
			$lead_group_ids = array( $lead_group_ids );
		}

		$lead_groups = tve_leads_get_groups( array(
			'full_data'       => false,
			'tracking_data'   => false,
			'completed_tests' => false,
			'active_tests'    => false,
		) );
		?>

		<p>
		<?php echo __( 'Choose Lead Group:', 'thrive-leads' ); ?>

		<?php foreach ( $lead_groups as $group ): ?>
			<div>
				<label>
					<input type="checkbox" value="<?php echo $group->ID; ?>"
						   name="<?php echo esc_attr( $this->get_field_name( 'lead_group' ) ); ?>[]"
						<?php echo in_array( $group->ID, $lead_group_ids ) ? 'checked' : '' ?>/>
					<?php echo $group->post_title; ?>
				</label>
			</div>
		<?php endforeach; ?>
		</p>

		<?php
	}

}

