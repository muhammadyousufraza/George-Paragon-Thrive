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

[thrive_blog_list]<?php echo tcb_template( 'elements/post-list-article.php', null, true ); ?>[/thrive_blog_list]

[tcb_pagination data-type="numeric" data-list="#main"]
