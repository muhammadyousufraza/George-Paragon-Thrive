<?php use TCB\Notifications\Main;

/* By default, the notification should be in the 'success' state */
$state      = 'success';
$is_preview = isset( $_GET['notification-state'] );
if ( $is_preview ) {
	$state = $_GET['notification-state'];
}
?>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<title>
		<?php wp_title( '' ); ?><?php echo wp_title( '', false ) ? ' :' : ''; ?><?php bloginfo( 'name' ); ?>
	</title>
	<meta name="description" content="<?php bloginfo( 'description' ); ?>">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?> style="overflow: unset;">

<div class="notifications-wrapper">
	<div class="notifications-info">
		<div class="info-text">
			<?php echo __( 'Note that notifications display over the top of the page content where they are triggered. This page has no background properties.', 'thrive-cb' ) ?>
		</div>
	</div>
	<div class="notifications-editor-wrapper">
		<div id="tve_editor">
			<?php echo Main::get_notification_content( $is_preview, $state, false , true); ?>
		</div>
	</div>
</div>
<?php do_action( 'get_footer' ); ?>
<?php wp_footer(); ?>
</body>