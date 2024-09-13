<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-ab-page-testing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

$tar_name             = 'Thrive Architect';
$tar_required_version = Thrive_AB_Checker::get_tar_required_version();
?>

<div class="notice notice-error">
	<p>
		<?php echo __( sprintf( 'Version of %s is not the required one: %s! Please update it before.', $tar_name, $tar_required_version ), 'thrive-ab-page-testing' ) ?>
	</p>
</div>
