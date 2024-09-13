<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$plugin_name = 'Thrive Optimize';
$tar_name    = 'Thrive Architect';

?>

<div class="notice notice-error">
	<p>
		<?php echo __( sprintf( '%s is not active on your site and you cannot use %s', $tar_name, $plugin_name ), 'thrive-ab-page-testing' ) ?>
	</p>
</div>
