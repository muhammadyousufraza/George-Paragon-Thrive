<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}
/** @var array $attr */ ?>
<style>
    #wpbody {
        position: static
    }

    ul#adminmenu a.wp-has-current-submenu::after {
        border-right-color: transparent !important; /* hides the small white arrow from the selected menu item */
    }
</style>
<div class="architect-incompatible">
	<div class="content">
		<div class="content-icons">
			<span class="ttb-icon"></span>
			<span class="plus-icon"></span>
			<span class="tar-icon"></span>
		</div>
		<p>
			<?php echo sprintf( __( "It looks like you have Thrive Architect installed, but it's not compatible with this version of Thrive Theme Builder. Thrive Theme Builder uses Thrive Architect to edit various pieces of content. To be able to use these features, please make sure you have the latest versions by clicking on the following link and updating <strong>%s</strong>:", 'thrive-theme' ), $attr['needs_update'] === 'theme' ? 'Thrive Theme Builder' : 'Thrive Architect' ); ?>
		</p>
		<a href="<?php echo admin_url( 'update-core.php?force-check=1' ); ?>">WordPress Updates</a>
	</div>
</div>
