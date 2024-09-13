<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12/7/2017
 * Time: 20:12
 */

class TVA_Recent_Comments extends WP_Widget_Recent_Comments {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'tva_recent_comments', // Base ID
			__( 'Recent Comments', 'thrive-apprentice' ), // Name
			array(
				'description' => __( 'Recent comments widget', 'thrive-apprentice' ),
			) // Args
		);
	}

	/**
	 * Overwrite default widget so we can display comments added on courses
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		$output = '';

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Comments' );

		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5;
		if ( ! $number ) {
			$number = 5;
		}

		$comments = get_comments( apply_filters( 'widget_comments_args', array(
			'number'      => $number,
			'status'      => 'approve',
			'post_status' => 'publish'
		), $instance ) );

		$output .= $args['before_widget'];
		if ( $title ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}

		$output .= '<ul id="recentcomments">';
		if ( is_array( $comments ) && $comments ) {
			// Prime cache for associated posts. (Prime post term cache if we need it for permalinks.)
			$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
			_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

			foreach ( (array) $comments as $comment ) {
				$hidden_post_id = get_option( 'tva_course_hidden_post_id' );

				$output .= '<li class="recentcomments">';
				/* translators: comments widget: 1: comment author, 2: post link */

				if ( $comment->comment_post_ID == $hidden_post_id ) {
					$term_id = get_comment_meta( $comment->comment_ID, 'tva_course_comment_term_id', true );
					$term    = get_term( $term_id, TVA_Const::COURSE_TAXONOMY );

					if ( $term !== null ) {
						$output .= sprintf( _x( '%1$s on %2$s', 'widgets' ),
							'<span class="comment-author-link">' . get_comment_author_link( $comment ) . '</span>',
							'<a href="' . esc_url( get_term_link( $term, TVA_Const::COURSE_TAXONOMY ) ) . '">' . $term->name . '</a>'
						);
					}
				} else {
					$output .= sprintf( _x( '%1$s on %2$s', 'widgets' ),
						'<span class="comment-author-link">' . get_comment_author_link( $comment ) . '</span>',
						'<a href="' . esc_url( get_comment_link( $comment ) ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a>'
					);
				}

				$output .= '</li>';
			}
		}
		$output .= '</ul>';
		$output .= $args['after_widget'];

		echo $output;
	}


}

