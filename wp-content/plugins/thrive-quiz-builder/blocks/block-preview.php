<?php
/**
 * Used to preview the quiz in gutenberg
 */

$quiz_id = ! empty( $_REQUEST['quiz_id'] ) ? sanitize_text_field( $_REQUEST['quiz_id'] ) : null;
$data    = array( 'quiz_id' => $quiz_id );
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Quiz Block Preview</title>
	<?php add_filter( 'tve_dash_enqueue_frontend', '__return_true' ); ?>

	<?php tqb_enqueue_default_scripts(); ?>
	<?php tve_dash_frontend_enqueue(); ?>
	<?php tqb_enqueue_script(
		'tqb-frontend',
		tqb()->plugin_url( 'assets/js/dist/tqb-frontend.min.js' ),
		array( 'tve-dash-frontend', 'backbone' )
	);

	wp_localize_script(
		'tqb-frontend',
		'TQB_Front',
		array(
			'nonce'        => wp_create_nonce( 'tqb_frontend_ajax_request' ),
			'ajax_url'     => admin_url( 'admin-ajax.php' ) . '?action=tqb_frontend_ajax_controller',
			'is_preview'   => TQB_Product::has_access(),
			'post_id'      => $quiz_id,
			'settings'     => Thrive_Quiz_Builder::get_settings(),
			'quiz_options' => array(),
			't'            => array(
				'chars' => __( 'Characters', 'thrive-quiz-builder' ),
			),
		)
	);
	?>
	<?php wp_print_styles(); ?>
	<?php wp_print_scripts(); ?>
	<script>
		document.addEventListener( "DOMContentLoaded", () => {
			if ( window.TVE_Dash ) {
				TVE_Dash.forceImageLoad( document );
			}
		} );
	</script>
</head>
<body>
<div class="container">
	<?php echo TQB_Shortcodes::render_quiz_shortcode( $data ) ?>
</div>
</body>

<footer>
	<?php TQB_Shortcodes::render_backbone_templates(); ?>
</footer>
</html>
