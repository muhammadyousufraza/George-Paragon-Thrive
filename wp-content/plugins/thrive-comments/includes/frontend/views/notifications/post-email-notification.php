<table style="border: 1px solid #e5e5e5; padding: 20px;">
	<tr>
		<td colspan="2"><p style="color: #393939; font-size: 18px; margin: 0 0 5px;"><?php echo $labels['post_content_title']['text'] ?></p></td>
	</tr>
	<tr>
		<td colspan="2"><p style="color: #898989; font-size: 16px; margin: 0;"><?php echo $labels['post_comment_posted']['text'] ?></p></td>
	</tr>
	<tr>
		<td style="width: 50px; vertical-align: top;"><img style="width: 40px; border-radius: 50%; margin: 20px 0 0;" src="<?php echo $commenter_avatar; ?>"></td>
		<td>
			<p style="color: #595959; font-size: 14px; margin: 20px 0 0;"><?php echo $comment->comment_author ?></p>
			<span style="color: #b3b3b3; font-size: 14px;"><?php echo $comment->comment_date ?></span>
			<div style="color: #393939; font-size: 16px;"><?php echo implode( ' ', array_slice( explode( ' ', $comment->comment_content ), 0, 30 ) ); ?></div>
			<a style="font-size: 16px; background-color: <?php echo $color_picker_value ?>; padding: 5px 15px; text-decoration: none; color: #fff; display: inline-block; margin: 0 0 30px;" href="<?php echo $post_url . '#comments-' . $comment->comment_ID ?>"><?php echo $labels['post_reply_to']['text'] ?></a>
		</td>
	</tr>
	<tr>
		<td colspan="2"><p style="margin: 0; font-size: 14px; color: #898989;"><?php echo $labels['post_signed_up']['text'] ?></p></td>
	</tr>
	<tr>
		<td colspan="2"><p style="font-size: 14px; color: #898989;"><?php echo $labels['post_unsubscribe']['text'] ?></p></td>
	</tr>
</table>
