<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator\Items;
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Comment_Data extends Data_Object {

	public static function get_id() {
		return 'comment_data';
	}

	public static function get_nice_name() {
		return __( 'Comment', 'thrive-automator' );
	}

	public static function get_fields() {
		return [
			Comment_Author_Data_Field::get_id(),
			Comment_Author_Email_Data_Field::get_id(),
			Comment_Author_Url_Data_Field::get_id(),
			Comment_Content_Data_Field::get_id(),
			Comment_Date_Data_Field::get_id(),
			Comment_Date_Gmt_Data_Field::get_id(),
			Comment_Type_Data_Field::get_id(),
			Comment_Parent_Data_Field::get_id(),
			Comment_Post_ID_Data_Field::get_id(),
			User_Id_Data_Field::get_id(),
			Comment_Agent_Data_Field::get_id(),
			Comment_Author_IP_Data_Field::get_id(),
		];
	}

	public static function create_object( $param ) {
		$comment = null;
		if ( is_a( $param, 'WP_Comment' ) ) {
			$comment = $param;
		} elseif ( is_numeric( $param ) ) {
			$comment = get_comment( $param );
		} elseif ( ! empty( $param['comment_id'] ) && is_numeric( $param['comment_id'] ) ) {
			$comment = get_comment( $param['comment_id'] );
		} elseif ( is_array( $param ) ) {
			$comment = get_comment( $param[0] );
		}

		if ( $comment ) {
			return [
				'comment_author'       => $comment->comment_author,
				'comment_author_email' => $comment->comment_author_email,
				'comment_author_url'   => $comment->comment_author_url,
				'comment_content'      => $comment->comment_content,
				'comment_date'         => $comment->comment_date,
				'comment_date_gmt'     => $comment->comment_date_gmt,
				'comment_type'         => $comment->comment_type,
				'comment_parent'       => $comment->comment_parent,
				'comment_post_ID'      => $comment->comment_post_ID,
				'user_id'              => $comment->user_id,
				'comment_agent'        => $comment->comment_agent,
				'comment_author_IP'    => $comment->comment_author_IP,
			];

		}

		return $comment;
	}

	public function can_provide_email() {
		return true;
	}

	public function get_provided_email() {
		return $this->get_value( 'comment_author_email' );
	}
}
