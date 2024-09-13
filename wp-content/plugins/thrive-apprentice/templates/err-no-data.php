<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}
if ( isset( $_REQUEST['s'] ) && ! empty ( $_REQUEST['s'] ) ) {
	$tva_search_crit = sanitize_text_field( $_REQUEST['s'] );
}
if ( isset( $_REQUEST['tvas'] ) && ! empty ( $_REQUEST['tvas'] ) ) {
	$tva_search_crit = sanitize_text_field( $_REQUEST['tvas'] );
}
?>

<div class="tva_ob_error">
	<?php echo '<span class="tva-err-text">' . __( 'There are no courses matching your criteria: ', 'thrive-apprentice' ) . '</span>'; ?>
	<?php echo '<span class="tva-strong-text">' . $tva_search_crit . '</span>'; ?>
	<?php echo '<span class="tva-err-text">' . __( 'Please change or delete the search criteria.', 'thrive-apprentice' ) . '</span>'; ?>
</div>
