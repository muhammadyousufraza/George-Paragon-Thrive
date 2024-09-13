<?php
/**
 * The AMP header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package thrive-theme
 */

use Thrive\Theme\AMP\Main as AMP_Main;
use Thrive\Theme\AMP\Settings as AMP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>
<!doctype html>
<html ⚡ <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">

	<script async src="https://cdn.ampproject.org/v0.js"></script>

	<link rel="canonical" href="<?php echo AMP_Main::get_canonical_url(); ?>">

	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

	<?php AMP_Main::initialize_content(); ?>

	<?php echo AMP_Main::get_amp_default_styles(); ?>

	<?php echo AMP_Main::get_scripts(); ?>

	<?php echo AMP_Main::get_fonts(); ?>

	<style amp-custom>
		<?php echo AMP_Main::get_styles(); ?>
	</style>

	<link rel="profile" href="http://gmpg.org/xfn/11">
	<meta name="description" content="<?php bloginfo( 'description' ); ?>">
	<title>
		<?php echo wp_get_document_title(); ?>
	</title>
</head>

<body <?php body_class(); ?>>

<?php echo AMP_Settings::get_analytics(); ?>

<?php if ( isset( $_GET['thrive_debug'] ) ) : ?>
	<div>
		<?php echo 'CSS size counter: ' . strlen( AMP_Main::get_styles() ) / 1000 . 'KB / 75KB'; ?>
	</div>
<?php endif; ?>
