<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$widgets = tcb_elements()->get_external_widgets();

?>
<div id="tve-widget-component" class="tve-component" data-view="Widget">
	<div class="dropdown-header" data-prop="docked">
		<?php echo esc_html__( 'Widget', 'thrive-cb' ); ?>
		<i></i>
	</div>
	<div class="dropdown-content">
		<div class="tcb-text-center mb-10 mr-5 ml-5">
			<button class="tve-button orange click" data-fn="editWidget">
				<?php echo __( 'Edit design', 'thrive-cb' ); ?>
			</button>
		</div>
		<?php foreach ( $widgets as $widget ) : ?>
			<div id="<?php echo esc_attr( 'widget_' . $widget->id_base ); ?>" class="widget-form" data-name="<?php echo esc_attr( $widget->name ); ?>">
				<?php
				echo tcb_template( 'widget-form.php', [ // phpcs:ignore
					'widget'    => $widget,
					'form_data' => [],
				], true );
				?>
			</div>
		<?php endforeach; ?>
	</div>
</div>
