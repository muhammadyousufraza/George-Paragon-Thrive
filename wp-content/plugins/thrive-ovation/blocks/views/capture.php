<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/**
 * Custom view for the block html
 */
$block_id             = get_the_ID();
$content              = get_post_meta( intval( $block_id ), 'tve_updated_post', true );
$editor_content       = TVO_Block::get_content( $block_id );
$is_gutenberg_preview = isset( $_GET['tve_block_preview'] );
?>

<!doctype html>
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
<?php if ( empty( $content ) && $is_gutenberg_preview ) { ?>
	<style>
        .tvo-new-block-container {
            background-color: #f1f1f1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 15px;
            font-family: Roboto, sans-serif;
            font-size: 16px !important;
            color: #565a5f !important;
            letter-spacing: -0.12px;
            border: 15px solid #fff;
        }

        .tvo-block-title h2 {
            font-size: 24px !important;
            color: #171b1b !important;
            opacity: 0.5;
            font-family: Roboto, sans-serif;
        }

        .tvo-new-block-description {
            color: #565a5f;
            font-family: Roboto, sans-serif;
            line-height: 1.5;
        }

        #tvo-capture-logo {
            opacity: 0.1;
            max-width: 80px;
            max-height: 80px;
        }

        .mb-10 {
            margin-bottom: 10px;
        }

        body {
            border: 1px solid #ebebeb;
        }
	</style>
	<div class="tvo-new-block-container">
		<svg viewBox="0 0 32 32" id="tvo-capture-logo" class="mb-10">
			<path fill="#333333"
				  d="M32 15.3c-0.363-8.265-6.944-14.882-15.162-15.298l-0.038-0.002h-1.5c-8.286 0.396-14.904 7.014-15.299 15.264l-0.001 0.036v1.5c0.418 8.256 7.035 14.837 15.266 15.199l0.034 0.001h1.5c8.223-0.414 14.786-6.977 15.198-15.162l0.002-0.038v-1.5zM16.8 30.5v-4.5h-1.5v4.5c-7.405-0.437-13.308-6.306-13.798-13.656l-0.002-0.044h4.5v-1.5h-4.5c0.447-7.435 6.365-13.353 13.759-13.798l0.041-0.002v4.5h1.5v-4.5c7.395 0.41 13.29 6.305 13.698 13.662l0.002 0.038h-4.5v1.5h4.5c-0.437 7.405-6.306 13.308-13.656 13.798l-0.044 0.002z"></path>
			<path fill="#333333" d="M11.3 19.6v-3.6l1.5-3.6h2l-1.1 3.4h1.7v3.7h-4.1v0.1z"></path>
			<path fill="#333333" d="M16.7 19.6v-3.6l1.5-3.6h2l-1.2 3.4h1.7v3.7h-4v0.1z"></path>
		</svg>
		<div class="tvo-block-title mb-10"><h2 class="mb-10"><?php echo __( 'Capture Testimonial', 'thrive-ovation' ); ?></h2></div>
		<div class="tvo-new-block-description">
			<?php echo __( 'Currently this block has no content.', 'thrive-ovation' ); ?>
		</div>
		<div class="tvo-new-block-description mb-10"><?php echo __( 'It will update once your block has been saved in Architect.', 'thrive-ovation' ); ?></div>
	</div>
<?php } else { ?>
<div class="tve-block-container">
	<div class="tve_flt" id="tve_flt">
		<div id="tve_editor">
			<?php if ( empty( $editor_content ) ) { ?>
				<div class="thrv_wrapper thrv_tvo_capture_testimonials tcb-elem-placeholder">
					<span class="tcb-inline-placeholder-action with-icon">
						<?php tcb_icon( 'add', false, 'editor' ); ?>
						<?php echo __( 'Select Template', 'thrive-ovation' ); ?>
					</span>
				</div>
			<?php } else { ?>
				<?php echo $editor_content; ?>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<?php if ( $is_gutenberg_preview ) { ?>

		<style type="text/css">#wpadminbar {
                display: none !important;
            }

            html {
                margin: 0 !important;
            }
		</style>
		<script>
			document.addEventListener( "DOMContentLoaded", () => {
				if ( window.TVE_Dash ) {
					TVE_Dash.forceImageLoad( document );
				}
			} );
		</script>
	<?php } ?>
	<?php do_action( 'get_footer' ) ?>
	<?php wp_footer() ?>

</body>
</html>
