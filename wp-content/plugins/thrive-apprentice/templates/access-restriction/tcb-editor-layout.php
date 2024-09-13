<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php wp_head(); ?>
</head>

<body <?php body_class( '' ); ?>>
<div class="tva-access-restriction-editor">
	<?php the_post(); ?>
	<?php the_content(); ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
