<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

$home_url = rtrim( home_url(), '/' );
?>
<script>
	window.opener.postMessage( {tpm_success: true}, '<?php echo esc_url( $home_url ); ?>' );
</script>
