<?php

if ( post_password_required() ) {
	return;
}
?>

<?php comment_form(); ?>

<?php if ( have_comments() ) : ?>

	<?php the_comments_navigation(); ?>

	<ol class="comment-list">
		<?php wp_list_comments( [  'style' => 'ol', 'short_ping' => true, 'avatar_size' => 100  ] ); ?>
	</ol>

	<?php the_comments_navigation(); ?>

	<?php if ( ! comments_open() ) : ?>
		<?php echo apply_filters( 'comment_form_closed_comments', __( 'Comments are closed.', 'thrive-theme' ) ); ?>
	<?php endif; ?>
<?php endif; ?>
