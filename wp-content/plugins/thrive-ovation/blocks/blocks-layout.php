<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$has_no_content = empty( get_post_meta( (int) $post_id, 'tve_updated_post', true ) );

$is_gutenberg_preview = isset( $_GET['tve_block_preview'] );

if ( $post_type === TVO_DISPLAY_POST_TYPE ) {
	require_once TVO_PATH . '/tcb-bridge/classes/display-testimonials/elements/class-tcb-display-testimonials-element.php';
	$element = new \TVO\DisplayTestimonials\TCB_Display_Testimonials( 'display_testimonials' );
} else {
	require_once TVO_PATH . '/tcb-bridge/classes/class-tcb-capture-testimonials-v2.php';
	$element = new TCB_Capture_Testimonials_v2( 'capture_testimonials_v2' );
}
?>
<html <?php language_attributes(); ?> style="overflow: unset;">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<title>
			<?php wp_title( '' ); ?><?php echo wp_title( '', false ) ? ' :' : ''; ?><?php bloginfo( 'name' ); ?>
		</title>
		<meta name="description" content="<?php bloginfo( 'description' ); ?>">

		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?> style="overflow: unset;">
		<?php
		if ( $has_no_content && $is_gutenberg_preview ) {
			include TVO_PATH . '/tcb-bridge/css/icons.svg';
			include __DIR__ . '/views/gutenberg-no-content.php';
		} else {
			?>
			<div class="tve-block-container" style="margin: 20px">
				<div class="tve_flt" id="tve_flt">
					<div id="tve_editor">
						<?php echo $has_no_content ? $element->html_placeholder() : TVO_Block::get_content( $post_id ); ?>
					</div>
				</div>
			</div>
		<?php } ?>
		<?php if ( $is_gutenberg_preview ) { ?>
			<style>
                #wpadminbar {
                    display: none !important;
                }

                html {
                    margin: 0 !important;
                }
			</style>
			<script>
				document.addEventListener( 'DOMContentLoaded', () => {
					if ( window.TVE_Dash ) {
						TVE_Dash.forceImageLoad( document );
					}
				} );
			</script>
		<?php } ?>
		<?php
		remove_action( 'wp_footer', 'woocommerce_demo_store' );
		wp_footer();
		?>
	</body>
</html>
