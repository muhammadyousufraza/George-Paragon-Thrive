<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>

<script type="text/javascript">
	if ( window.parent && window.parent.tvd_auth_check ) {
		if ( window.parent.tvd_auth_check.userkey === <?php echo json_encode( $user_key ) ?> ) {
			window.parent.tvd_after_auth( <?php echo json_encode( $data ) ?> );
		}
	}
</script>

