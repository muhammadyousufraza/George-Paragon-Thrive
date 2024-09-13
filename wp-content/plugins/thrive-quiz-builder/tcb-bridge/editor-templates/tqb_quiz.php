<?php
/**
 * Created by PhpStorm.
 * User: Ovidiu
 * Date: 7/24/2017
 * Time: 9:50 AM
 */


$post_id = ! empty( $_GET['tqb_redirect_post_id'] ) ? (int) $_GET['tqb_redirect_post_id'] : 0;
$quiz_id = ! empty( $_GET['tqb_quiz_id'] ) ? (int) $_GET['tqb_quiz_id'] : 0;

if ( empty( $post_id ) || empty( $quiz_id ) ) {
	exit( 'Invalid Post' );
}

$image_url   = sanitize_text_field( $_GET['image_url'] );
$description = rawurlencode( sanitize_text_field( $_GET['description'] ) );

$site_url        = site_url( '?post_type=' . Thrive_Quiz_Builder::SHORTCODE_NAME . '&tqb_quiz_id=' . $quiz_id . '&tqb_redirect_post_id=' . $post_id . '&image_url=' . $image_url . '&description=' . $description );
$facebook_app_id = get_option( 'tve_social_fb_app_id', '' );
?>

<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta name="robots" content="noindex, nofollow"/>
	<title>Quiz Page</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<meta property="og:url" content="<?php echo $site_url; ?>"/>
	<meta property="og:type" content="article"/>
	<meta property="og:title" content="<?php echo get_the_title( $post_id ); ?>"/>
	<meta property="og:description" content="<?php echo esc_attr( rawurldecode( $description ) ); ?>"/>
	<meta property="og:image" content="<?php echo esc_attr( rawurldecode( $image_url ) ); ?>"/>
	<meta property="og:image:width" content="620"/>
	<meta property="og:image:height" content="541"/>
	<meta property="fb:app_id" content="<?php echo $facebook_app_id; ?>"/>

	<?php //wp_head(); BECAUSE OF YOAST ?>
</head>
<body>
<?php do_action( 'get_footer' ); ?>
<?php wp_footer(); ?>
</body>
</html>
