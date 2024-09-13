<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<?php $is_gutenberg_preview = isset( $_GET['tve_block_preview'] ); ?>
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
<?php if ( $is_gutenberg_preview ) { ?>
	<style type="text/css">#wpadminbar, .symbol-extra-info {
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
<div class="sym-new-container">
