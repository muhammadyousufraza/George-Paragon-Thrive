<div class="tva-menu-box">
	<?php
	if ( ! empty( $title ) ) :
		echo $args['before_title'] . $title . $args['after_title'];
	endif;

	$key       = is_user_logged_in() ? 'nav_menu_logged_in' : 'nav_menu_logged_out';
	$menu      = isset( $instance[ $key ] ) ? $instance[ $key ] : '';
	$menu_args = array(
		'menu'       => $menu,
		'menu_class' => 'tva-menu',
	);
	wp_nav_menu( $menu_args );
	?>
</div>

