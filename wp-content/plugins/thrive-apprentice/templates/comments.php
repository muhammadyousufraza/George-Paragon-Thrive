<?php
global $is_thrive_theme;
$obj            = get_queried_object();
$comment_status = $obj->taxonomy
	? get_term_meta( $obj->term_id, 'tva_comment_status', true )
	: $obj->comment_status;

if ( 'closed' === $comment_status ) {
	return;
}

do_action( 'tva_on_fb_comments' );

if ( $is_thrive_theme ) {
	$fb_app_id          = thrive_get_theme_options( 'fb_app_id' );
	$enable_fb_comments = thrive_get_theme_options( 'enable_fb_comments' );

	if ( 'only_fb' === $enable_fb_comments ) {
		return;
	}
}
?>

<div class="clearfix tva-comments-wrapper">

	<div class="tva-comments-form">
		<h3><?php echo __( 'Leave a comment', 'thrive-apprentice' ); ?></h3>

		<textarea placeholder="<?php echo __( 'Enter your comment...', 'thrive-apprentice' ); ?>" class="tva-comment-content"></textarea>

		<?php if ( ! is_user_logged_in() ) : ?>
			<p class="tva-guest"><?php echo __( 'Comment as a guest:', 'thrive-apprentice' ); ?></p>
			<div class="tva-required-fileds tva-guest-fileds">

				<span><?php echo __( 'Name', 'thrive-apprentice' ); ?>
					<?php if ( get_option( 'require_name_email' ) == 1 ) { ?>
						*
					<?php } ?>
				</span>
				<input type="text" class="tva-comment-input" name="name"/>

				<span><?php echo __( 'E-Mail', 'thrive-apprentice' ); ?>
					<?php if ( get_option( 'require_name_email' ) == 1 ) { ?>
						*
					<?php } ?>
				</span>
				<input type="text" class="tva-comment-input" name="email"/>

				<div class="clear"></div>

				<span><?php echo __( 'Website', 'thrive-apprentice' ); ?></span>
				<input type="text" class="tva-comment-input" name="url"/>

			</div>
		<?php endif; ?>
		<button id="tva-submit-comment"><?php echo __( 'Submit comment', 'thrive-apprentice' ); ?></button>

	</div>

	<div class="tva-comments" id="tva-comments-list"></div>
</div>

