<?php
/**
 * FileName  class-thrive-comment-conversion.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

/**
 * Class TCM_Comment_Helper
 *
 * Helper with the comment CRUD
 */
class Thrive_Comments_Conversion {

	/**
	 * The single instance of the class.
	 *
	 * @var Thrive_Comments_Conversion singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main Thrive Comments Instance.
	 * Ensures only one instance of Thrive Comments Helper is loaded or can be loaded.
	 *
	 * @return Thrive_Comments_Conversion
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Return the number of times an user has commented at a post
	 *
	 * @param int    $post_id
	 * @param string $email
	 *
	 * @return int
	 */
	public function get_email_count( $post_id, $email ) {

		$args = array(
			'post_id'      => $post_id,
			'author_email' => $email,
		);

		$comments_query = new WP_Comment_Query;
		$comments       = $comments_query->query( $args );

		return count( $comments );
	}

	/**
	 * Return what conversion type will be used
	 *
	 * @param int $post_id
	 * @param int $email
	 *
	 * @return string
	 */
	public function show_after_save( $post_id, $email ) {
		$email_count           = $this->get_email_count( $post_id, $email );
		$session_comment_count = $this->get_session_comment_count( $post_id, $email );

		//when an admin posts a comment don't show him anything
		if ( tcah()->can_see_moderation() ) {
			return false;
		}


		// the case when it's the first comment on the post and in the session
		if ( $email_count + $session_comment_count == 1 ) {
			setcookie( 'tcm_commenter_first', 1, time() + ( 60 * 30 ), '/' );

			return 'first_time';
		}

		// the case when the user has commented more than once but the first time in the current session
		if ( $email_count + $session_comment_count == $email_count ) {
			setcookie( 'tcm_commenter_first', 0, time() + ( 60 * 30 ), '/' );

			return 'second_time';
		}

		return 'live_update';
	}

	/**
	 * Return how many comments where submitted by an user in the same session
	 *
	 * @param int    $post_id
	 * @param string $email
	 *
	 * @return int
	 */
	public function get_session_comment_count( $post_id, $email ) {
		$count = 0;

		$email = sha1( $email );

		$cookie_data = ( ! empty( $_COOKIE['tcm_commenters'] ) ) ? json_decode( stripslashes( $_COOKIE['tcm_commenters'] ), true ) : '';

		if ( ! empty( $cookie_data ) && isset( $cookie_data[ $post_id ] ) ) {

			$counts = array_count_values( $cookie_data[ $post_id ] );
			$count  = isset( $counts[ $email ] ) ? $counts[ $email ] : 0;

		}

		return $count;
	}

	/**
	 * Sets / updates cookie which stores how many times a user commented on a specific post
	 *
	 * @param WP_Comment $comment
	 */
	public function cookie_counts_comments( $comment ) {

		$commenters_cookie = ( isset( $_COOKIE['tcm_commenters'] ) ) ? json_decode( stripslashes( $_COOKIE['tcm_commenters'] ), true ) : array();

		if ( isset( $commenters_cookie[ $comment->comment_post_ID ] ) ) {
			$commenters_cookie[ $comment->comment_post_ID ][] = sha1( $comment->comment_author_email );
		} else {
			$commenters_cookie[ $comment->comment_post_ID ] = array( sha1( $comment->comment_author_email ) );
		}

		setcookie( 'tcm_commenters', json_encode( $commenters_cookie ), time() + ( 60 * 30 ), '/' );
	}

	/**
	 * Used for determining wich message to show ( from live update ) when the user inserts more than one comment per session
	 *
	 * @return string
	 */
	public function get_commenter_first() {
		if ( isset( $_COOKIE['tcm_commenter_first'] ) ) {
			return $_COOKIE['tcm_commenter_first'];
		}

		return '';
	}

	/**
	 * Return related posts
	 *
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	public function get_related_posts( $related_count, $args ) {
		$post = $this->tc_get_post();

		//maybe at some point we need to change the order by parameter. The default is date
		$args = wp_parse_args( (array) $args, array(
			'orderby' => 'date',
		) );

		//first get the categories that contain the post ( by  category id )
		$terms = get_the_terms( $post->ID, 'category' );
		if ( empty( $terms ) ) {
			$terms = array();
		}
		$term_list = wp_list_pluck( $terms, 'term_id' );

		//get the tags that are set on the post
		$tags = get_the_terms( $post->ID, 'post_tag' );
		if ( empty( $tags ) ) {
			$tags = array();
		}
		$tags_list = wp_list_pluck( $tags, 'term_id' );

		//arguments to take all the other posts that are in the same category or have the same tag
		$related_args = array(
			'post_type'      => $post->post_type,
			'posts_per_page' => $related_count,
			'post_status'    => 'publish',
			'post__not_in'   => array( $post->ID ),
			'orderby'        => $args['orderby'],
			'tax_query'      => array(
				'relation' => 'OR',
				array(
					'taxonomy' => 'category',
					'fields'   => 'term_id',
					'terms'    => $term_list,
				),
				array(
					'taxonomy' => 'post_tag',
					'fields'   => 'term_id',
					'terms'    => $tags_list,
				),
			),
		);

		$related_query = new WP_Query( $related_args );

		if ( $related_query->found_posts < $related_count ) {
			$related_posts = $this->get_posts_from_query_result( $related_query );
			$exclude_posts = wp_list_pluck( $related_posts, 'post_id' );

			/**
			 * If there are no posts in the same category, we will take the newest posts with the same post type from the website and fill in the gaps
			 */
			$new_posts = $this->get_newest_posts( $related_count - $related_query->found_posts, $exclude_posts, $post );

			$posts_to_show = array_merge( $related_posts, $new_posts );
		} else {
			$posts_to_show = $this->get_posts_from_query_result( $related_query );
		}

		return $posts_to_show;
	}

	/**
	 * Get newest posts for the related posts list
	 *
	 * @param int     $posts_count How many new posts should we show.
	 * @param array   $exclude     The posts that will not be included in the list.
	 * @param WP_Post $post        The initial post object
	 *
	 * @return array
	 */
	public function get_newest_posts( $posts_count, $exclude, $post ) {
		$new_posts = array();

		$args = array(
			'post_type'      => $post->post_type,
			'posts_per_page' => $posts_count,
			'post_status'    => 'publish',
			'post__not_in'   => array_merge( $exclude, array( $post->ID ) ),
			'orderby'        => 'date',
		);

		$new_posts_query = new WP_Query( $args );

		if ( $new_posts_query->have_posts() ) {
			$new_posts = $this->get_posts_from_query_result( $new_posts_query );
		}

		return $new_posts;
	}

	/**
	 * Return the posts from the query with only the fields that we nedd
	 *
	 * @param WP_Query $posts_query
	 *
	 * @return array
	 */
	public function get_posts_from_query_result( $posts_query ) {
		$related = array();

		if ( $posts_query->found_posts ) {
			foreach ( $posts_query->posts as $post ) {
				$related[] = array(
					'post_id'        => $post->ID,
					'post_title'     => $post->post_title,
					'guid'           => $post->guid,
					'featured_image' => has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post ) : '',
				);
			}
		}

		return $related;
	}

	/**
	 * Get all active thrive boxes
	 *
	 * @return array
	 */
	public function get_thrive_boxes() {
		$two_steps = array();

		//check if leads is active
		if ( ! defined( 'TVE_LEADS_URL' ) ) {
			return array();
		}
		if ( function_exists( 'tve_leads_get_two_step_lightboxes' ) ) {
			$two_steps = tve_leads_get_two_step_lightboxes( array(
				'active_test' => true,
			) );
		}

		return $two_steps;
	}

	/**
	 * Adds to the content of the page the thrive box shortcodes
	 *
	 * @return string
	 */
	public function get_thrive_boxes_shortcodes() {

		$conversion_settings = tcms()->tcm_get_setting_by_name( 'tcm_conversion' );

		//if neither first time and second time the user does not want a thrive box, than return empty string because there is nothing to add to the page
		if ( $conversion_settings['first_time']['active'] !== Thrive_Comments_Constants::TCM_THRIVEBOX && $conversion_settings['second_time']['active'] !== Thrive_Comments_Constants::TCM_THRIVEBOX ) {
			return '<div></div>';
		}

		$first_shortcode  = $this->get_thrivebox_shortcode( $conversion_settings['tcm_thrivebox']['first_time']['thrivebox_id'] );
		$second_shortcode = $this->get_thrivebox_shortcode( $conversion_settings['tcm_thrivebox']['second_time']['thrivebox_id'] );

		return $first_shortcode . $second_shortcode;
	}

	/**
	 * Return thrive box shortcode based on id
	 *
	 * @param int $thrivebox_id
	 *
	 * @return string
	 */
	public function get_thrivebox_shortcode( $thrivebox_id ) {
		return ( ! empty( $thrivebox_id ) ) ? sprintf( "<span class='tl-placeholder-f-type-two_step_%d'></span>", $thrivebox_id ) : '';
	}

	/**
	 *  Add twitter metas to the page
	 */
	public function add_twitter_cards() {

		if ( ! get_option( 'tcm_meta_tags' ) ) {
			return false;
		}

		$post = $this->tc_get_post();

		if ( ! $post || ( '' === get_option( 'share_individual_comments' ) ) ) {
			return false;
		}

		$tc_image       = isset( $post->featured_image ) ? $post->featured_image : '';
		$tc_title       = isset( $post->post_title ) ? $post->post_title : '';
		$tc_description = isset( $post->post_excerpt ) ? $post->post_excerpt : '';
		if ( empty( $tc_description ) ) {
			$tc_description = $tc_title;
		}
		ob_start();
		include tcm()->plugin_path( 'includes/frontend/views/social/twitter-meta.php' );
		$content = ob_get_contents();
		ob_end_clean();
		echo $content;
	}

	/**
	 *  Add facebook metas to the page
	 */
	public function add_facebook_cards() {

		if ( ! get_option( 'tcm_meta_tags' ) ) {
			return false;
		}

		$post = $this->tc_get_post();

		if ( ! $post || ( '' === get_option( 'share_individual_comments' ) ) ) {
			return false;
		}

		$tc_url         = get_site_url() . '?p=' . $post->ID;
		$tc_image       = ( isset( $post->featured_image ) ) ? $post->featured_image : '';
		$tc_title       = ( isset( $post->title ) ) ? $post->post_title : '';
		$tc_description = ( isset( $post->post_excerpt ) ) ? $post->post_excerpt : '';
		if ( empty( $tc_description ) ) {
			$tc_description = $tc_title;
		}

		ob_start();
		include tcm()->plugin_path( 'includes/frontend/views/social/facebook-meta.php' );
		$content = ob_get_contents();
		ob_end_clean();
		echo $content;

	}


	/**
	 * Get the post and the fatured imaged
	 */
	public function tc_get_post() {

		$post = get_post();

		if ( ! $post ) {
			return null;
		}

		$post->featured_image = has_post_thumbnail( $post ) ? get_the_post_thumbnail_url( $post ) : '';
		$post->permalink      = get_permalink( $post );

		return apply_filters( 'tcm_get_post', $post );
	}
}

/**
 *  Main instance of Thrive Comments Helpers
 *
 * @return Thrive_Comments_Conversion
 */
function tcmc() {
	return Thrive_Comments_Conversion::instance();
}

tcmc();

