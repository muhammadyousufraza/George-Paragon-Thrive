<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>"/>
	<meta name="robots" content="noindex, nofollow"/>
	<title>
		<?php /* Genesis wraps the meta title into another <title> tag using this hook: genesis_doctitle_wrap. the following line makes sure this isn't called */ ?>
		<?php /* What if they change the priority at which this hook is registered ? :D */ ?>
		<?php remove_filter( 'wp_title', 'genesis_doctitle_wrap', 20 ) ?>
		<?php wp_title( '' ); ?>
	</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<?php
	wp_head();
	tve_leads_output_custom_css( $variation );
	?>
</head>