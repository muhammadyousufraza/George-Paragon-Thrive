<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

?>
<!doctype html>
<html <?php language_attributes(); ?> style="overflow: unset;">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<meta name="description" content="<?php bloginfo( 'description' ); ?>">

		<?php wp_head(); ?>
		<style>
            body {
                overflow: unset;
                background: white;
            }

            #typography-elements {
                max-width: 1000px;
                margin: 50px auto;
            }

            /* just so we can read it when we enter hover state */
            a.tve-state-hover {
                text-decoration: underline;
            }

            /* some custom styles for admin previews - added here so we don't create and load an extra CSS resource */
			<?php if (thrive_typography()->is_admin_preview()) : ?>
            body {
                background: #f2f4f4 !important;
            }

            #typography-elements {
                flex: 0 0 800px;
                margin: 0;
                box-sizing: border-box;
                padding: 0 20px;
            }

            html {
                margin-top: 0 !important;
            }

            /* Heading links have by default some settings that are inherited and not from the actual link */
            #typography-elements .thrv_text_element h5 a:not(.tcb-button-link) {
                color: inherit;
                line-height: inherit;
                font: inherit;
                letter-spacing: inherit;
            }

            /* hide the MemberMouse top bar */
            #mm-preview-settings-bar {
                display: none !important;
            }

			<?php endif; ?>
		</style>
	</head>

	<body <?php body_class( '' ); ?>>

		<div id="typography-elements" class="tcb-post-content tcb-style-wrap">
			<?php tcb_template( 'typography.phtml', null, false, 'backbone' ); ?>
		</div>
		<?php do_action( 'get_footer' ); ?>
		<?php wp_footer(); ?>
		<?php if ( thrive_typography()->is_admin_preview() ) : ?>
			<script type="text/javascript">
				jQuery( function () {
					/* on DOMready, notify parent frame about this frame's height */
					if ( window.parent && window.parent.TTD ) {
						window.parent.TTD.objects.collections.typography.trigger( 'frame-height-update', tve_frontend_options.post_id, jQuery( '#tcb-typography' ).outerHeight() );
					}
					/* prevent click on links */
					jQuery( '#typography-elements' ).on( 'click', 'a', () => {
						return false;
					} );

					const evt = new window.parent.CustomEvent( 'typographyloaded', {detail: {frame_height: jQuery( '#tcb-typography' ).outerHeight()}} );
					window.parent.dispatchEvent( evt );
				} );
			</script>
		<?php endif; ?>
	</body>
</html>
