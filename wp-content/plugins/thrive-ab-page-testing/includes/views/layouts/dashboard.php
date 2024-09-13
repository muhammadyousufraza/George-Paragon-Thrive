<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * This file has to be included at the beginning of all editor layouts
 *
 * @package thrive-ab-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
global $post;
nocache_headers();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head><?php wp_head(); ?></head>
<body>

<div id="tab-dashboard-wrapper"></div>

<?php wp_footer() ?>
<?php include thrive_ab()->path( '/assets/fonts/icons.svg' ); ?>
</body>
</html>
