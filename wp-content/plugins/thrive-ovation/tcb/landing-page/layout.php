<?php
if ( ! isset( $tcb_landing_page ) ) {
	$tcb_landing_page = $GLOBALS['tcb_landing_page'];
}
global $post;
setup_postdata( $post );
if ( ! isset( $lp_template ) ) {
	$lp_template = $GLOBALS['tcb_lp_template'];
}
?><!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?><?php echo is_editor_page() ? ' class="tcb-editor tcb"' : 'class="tcb"'; ?>>
<!--<![endif]-->
<head>
	<?php if ( function_exists( 'tha_head_top' ) ) : tha_head_top(); endif; ?>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<?php /* Genesis wraps the meta title into another <title> tag using this hook: genesis_doctitle_wrap. the following line makes sure this isn't called */ ?>
	<?php remove_filter( 'wp_title', 'genesis_doctitle_wrap', 20 ) ?>
	<?php /* Catalyst, an older version of Genesis, seems to do the same thing. */ ?>
	<?php remove_filter( 'wp_title', 'catalyst_site_title_wrap', 20 ) ?>

	<?php /* only output the title if the theme does not have support for title-tag and block-templates */ ?>
	<?php if ( ! get_theme_support( 'title-tag' ) && ! get_theme_support( 'block-templates' ) ) : ?>
		<title><?php wp_title( '|', true, 'right' ); ?></title>
	<?php endif ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<?php $tcb_landing_page->head(); ?>

	<style>
        html {
            height: auto;
        }

            html.tcb-editor {
                overflow-y: initial;
            }

            body:before, body:after {
                height: 0 !important;
            }

            .thrv_page_section .out {
                max-width: none
            }

            .tve_wrap_all {
                position: relative;
            }

            /* Content Width - inherit Content Width directly from LP settings */
            .thrv-page-section[data-inherit-lp-settings="1"] .tve-page-section-in {
                max-width: 1080px !important;
                max-width: var(--page-section-max-width) !important;
            }
			/* set the max-width also for over content settings */
            body.tcb-full-header .thrv_header,
            body.tcb-full-footer .thrv_footer {
                width: 100vw;
				max-width: 100vw;
                left: 50%;
                right: 50%;
                margin-left: -50vw !important;
                margin-right: -50vw !important;
            }
		</style>

	</head>
	<?php
	$css_data = $tcb_landing_page->get_css_data();
	remove_all_filters( 'body_class' ); // strip out any custom classes added by the theme for the <body> tag
	?>
	<body <?php body_class( $css_data['class'] ); ?>style="<?php echo esc_attr( $css_data['css'] ); ?>"<?php echo $css_data['custom_color']; ?>> <?php // phpcs:ignore ?>
		<?php $tcb_landing_page->after_body_open(); ?>
		<div class="wrp cnt bSe" style="display: none">
			<div class="awr"></div>
		</div>
		<div class="tve_wrap_all" id="tcb_landing_page">
			<div class="tve_post_lp tve_lp_<?php echo esc_attr( $lp_template ); ?> tve_lp_template_wrapper" style="<?php echo esc_attr( $css_data['main_area']['css'] ); ?>">
				<?php
				/**
				 * we re-add the_content filter here, it was removed inside the tcb_custom_editable_content to prevent applying inside the <head> section
				 * WP SEO uses get_the_excerpt to put in some meta tags, which was messing up some 3rd party shortcodes
				 */
				if ( ! empty( $GLOBALS['tcb_landing_page_needs_filter'] ) ) {
					add_filter( 'the_content', 'tve_editor_content' );
				}
				?>
				<?php echo apply_filters( 'tve_landing_page_content', '' ); //phpcs:ignore ?>
			</div>
			<?php /* to inherit the default styles from the landing page typography */ ?>
			<div class="fr-dropdown-holder tcb-style-wrap"></div>
		</div>
		<?php $tcb_landing_page->footer(); ?>
		<?php do_action( 'get_footer' ); ?>
		<?php wp_footer(); ?>
		<?php $tcb_landing_page->before_body_end(); ?>
	</body>
</html>
