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

        #tvo-display-logo {
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
		<svg viewBox="0 0 41 32" id="tvo-display-logo" class="mb-10">
			<path fill="#333333"
				  d="M37.806 0h-34.323c-0.035-0.001-0.075-0.002-0.116-0.002-1.856 0-3.362 1.502-3.368 3.356v19.614c0 0.004-0 0.008-0 0.013 0 1.917 1.554 3.471 3.471 3.471 0.005 0 0.009 0 0.014-0h16.386v3.484h-7.484c-0.004-0-0.008-0-0.013-0-0.563 0-1.019 0.456-1.019 1.019 0 0.005 0 0.009 0 0.014v-0.001c-0.002 0.024-0.003 0.052-0.003 0.081 0 0.527 0.428 0.955 0.955 0.955 0.028 0 0.057-0.001 0.084-0.004l-0.004 0h16.903c0.004 0 0.008 0 0.013 0 0.563 0 1.019-0.456 1.019-1.019 0-0.005-0-0.009-0-0.014v0.001c-0.012-0.575-0.481-1.037-1.058-1.037-0.036 0-0.072 0.002-0.108 0.005l0.004-0h-7.484v-3.613h16.129c0.004 0 0.008 0 0.013 0 1.917 0 3.471-1.554 3.471-3.471 0-0.005 0-0.009-0-0.014v0.001-19.484c-0.006-1.855-1.512-3.357-3.368-3.357-0.041 0-0.082 0.001-0.122 0.002l0.006-0zM39.355 22.968c-0.014 0.849-0.699 1.534-1.547 1.548l-0.001 0h-34.323c-0.849-0.014-1.534-0.699-1.548-1.547l-0-0.001v-19.613c0.014-0.849 0.699-1.534 1.547-1.548l0.001-0h34.452c0.849 0.014 1.534 0.699 1.548 1.547l0 0.001v19.613h-0.129z"></path>
			<path fill="#333333" d="M33.677 16.129h-26.065c-0.004 0-0.008 0-0.013 0-0.563 0-1.019-0.456-1.019-1.019 0-0.005 0-0.009 0-0.014v0.001c-0.002-0.024-0.003-0.052-0.003-0.081 0-0.527 0.428-0.955 0.955-0.955 0.028 0 0.057 0.001 0.084 0.004l-0.004-0h26.194c0.004-0 0.008-0 0.013-0 0.563 0 1.019 0.456 1.019 1.019 0 0.005-0 0.009-0 0.014v-0.001c-0.012 0.575-0.481 1.037-1.058 1.037-0.036 0-0.072-0.002-0.108-0.005l0.004 0z"></path>
			<path fill="#333333" d="M16.645 12v-2.968l1.29-3.097h1.677l-1.032 2.839h1.419v3.097h-3.355v0.129z"></path>
			<path fill="#333333" d="M21.161 12v-2.968l1.29-3.097h1.677l-0.903 2.839h1.419v3.097h-3.484v0.129z"></path>
			<path fill="#333333" d="M33.677 20h-26.065c-0.004 0-0.008 0-0.013 0-0.563 0-1.019-0.456-1.019-1.019 0-0.005 0-0.009 0-0.014v0.001c-0.002-0.024-0.003-0.052-0.003-0.081 0-0.527 0.428-0.955 0.955-0.955 0.028 0 0.057 0.001 0.084 0.004l-0.004-0h26.194c0.004-0 0.008-0 0.013-0 0.563 0 1.019 0.456 1.019 1.019 0 0.005-0 0.009-0 0.014v-0.001c-0.012 0.575-0.481 1.037-1.058 1.037-0.036 0-0.072-0.002-0.108-0.005l0.004 0z"></path>
		</svg>
		<div class="tvo-block-title mb-10"><h2 class="mb-10"><?php echo __( 'Display Testimonial', 'thrive-ovation' ); ?></h2></div>
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
				<div class="thrv_wrapper thrv_tvo_display_testimonials tcb-elem-placeholder">
						<span class="tcb-inline-placeholder-action with-icon">
							<?php tcb_icon( 'add', false, 'editor' ); ?>
							<?php echo __( 'Select Testimonial', 'thrive-ovation' ); ?>
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
