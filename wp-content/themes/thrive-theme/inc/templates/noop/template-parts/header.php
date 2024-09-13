<?php
/**
 * Minimal header section for the "noop" theme
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
$logo = get_option( 'tcb_logo_data', [] );
if ( isset( $logo[0]['src'] ) ) { // seems this one is the Dark version.. ?
	$logo = $logo[0]['src']; // seems logo width / height are not saved, cannot use them
}
?>
<header style="padding: 2rem 0 4rem; display:flex;justify-content: flex-start">
	<h1 title="<?php esc_attr_e( get_bloginfo( 'name' ) ); ?>">
		<a href="<?php echo home_url(); ?>">
			<?php
			if ( $logo ) {
				?><img src="<?php esc_attr_e( $logo ); ?>" id="logo" style="max-width: 18rem"> <?php
			} else {
				bloginfo( 'name' );
			} ?>
		</a>
	</h1>
</header>