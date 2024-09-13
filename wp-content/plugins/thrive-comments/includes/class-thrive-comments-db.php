<?php
/**
 * Handles database operations
 * FileName  class-tcm-db.php.
 *
 * @project  : thrive-comments
 * @developer: Dragos Petcu
 * @company  : BitStone
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden.
}

global $tcmdb;

/**
 * Encapsulates the global $wpdb object
 *
 * Class Tho_Db
 */
class Thrive_Comments_Database {
	/**
	 * @var $wpdb wpdb
	 */
	protected $wpdb = null;

	/**
	 * The single instance of the class.
	 *
	 * @var Thrive_Comments_Database singleton instance.
	 */
	protected static $_instance = null;

	/**
	 * Main Thrive Comments Instance.
	 * Ensures only one instance of Thrive Comments is loaded or can be loaded.
	 *
	 * @return Thrive_Comments_Database
	 */
	public static function instance() {
		if ( empty( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Class constructor
	 */
	public function __construct() {
		global $wpdb;

		$this->wpdb = $wpdb;
	}

	/**
	 * Forward the call to the $wpdb object
	 *
	 * @param string $method_name Method Name.
	 * @param array  $args        Additional arguments.
	 *
	 * @return mixed
	 */
	public function __call( $method_name, $args ) {
		return call_user_func_array( array( $this->wpdb, $method_name ), $args );
	}

	/**
	 * Update/Save comment
	 *
	 * @param array $comment Comment Data.
	 *
	 * @return array|bool|null|WP_Comment
	 */
	public function save_comment( $comment = array() ) {

		$comment_id = wp_new_comment( $comment );
		add_comment_meta( $comment_id, 'comment_author_picture', $comment['comment_author_picture'] );
		if ( $comment_id ) {
			$comment = get_comment( $comment_id );
			$comment = $this->after_save( $comment );
			$this->update_meta_after_save( $comment );


			return $comment;
		}

		return false;
	}

	/**
	 * Update meta values for comment or comment parent
	 *
	 * @param $comment
	 */
	public function update_meta_after_save( $comment ) {

		if ( $comment->is_moderator && intval( $comment->comment_parent ) !== 0 ) {
			$needs_reply = get_comment_meta( $comment->comment_parent, 'tcm_needs_reply', true );

			//if the comments needs reply, now it means that it received one from a moderator
			if ( intval( $needs_reply ) === 1 ) {
				update_comment_meta( $comment->comment_parent, 'tcm_needs_reply', 0 );
			}
		}
	}

	/**
	 * Format comment fields after save
	 *
	 * @param WP_Comment $comment
	 *
	 * @return WP_Comment $comment
	 * */
	public function after_save( $comment ) {
		tcmh()->populate_default_picture_url( $comment );

		$comment->formatted_date       = tcmh()->format_comment_date( $comment->comment_ID );
		$comment->email_hash           = md5( $comment->comment_author_email );
		$comment->comment_content      = tcmh()->filter_comment( $comment->comment_content, $comment );
		$comment->show_after_save      = tcmc()->show_after_save( $comment->comment_post_ID, $comment->comment_author_email );
		$comment->commenter_first      = tcmc()->get_commenter_first();
		$comment->conversion_settings  = tcms()->tcm_get_setting_by_name( 'tcm_conversion' );
		$comment->replace_keyword      = tcmh()->tcm_replace_keywords( $comment->user_id );
		$comment->user_achieved_badges = tcmh()->get_badges( $comment->comment_ID );
		$comment->is_moderator         = 0 !== $comment->user_id && tcmh()->is_user_moderator( $comment->user_id );
		$comment->display_name         = tcmh()->get_comment_display_name( $comment->comment_ID, $comment->user_id );
		$comment->downvote             = tcmh()->get_downvotes( $comment->comment_ID );
		$comment->upvote               = tcmh()->get_upvotes( $comment->comment_ID );


		/**
		 * Filter comment object
		 */
		return apply_filters( 'tcm_comment_after_save', $comment );
	}

	/**
	 * Get a log column from logs table.
	 * Search after record id or user email
	 *
	 * @param $column_name
	 * @param $filters
	 *
	 * @return array|mixed|null|object
	 */
	public function get_log( $columns, $filters ) {
		if ( ! is_array( $filters ) || ! is_array( $columns ) ) {
			return null;
		}
		$where = $this->construct_where_clause_with_and( $filters );
		$sql   = 'SELECT ';
		foreach ( $columns as $column ) {
			$sql .= $column;
			if ( end( $columns ) !== $column ) {
				$sql .= ',';
			}
		}
		$sql .= ' FROM ' . $this->tcm_table_name( 'logs' ) . $where;
		$sql = $this->prepare( $sql, $filters );

		return json_decode( $this->wpdb->get_var( $sql ), true );
	}

	/**
	 * Get a log column from email_hash table.
	 * Search after record id or user email
	 *
	 * @param string $column  column name.
	 * @param array  $filters filters.
	 *
	 * @return array|mixed|null|object
	 */
	public function get_email_hash( $column, $filters ) {
		if ( ! is_array( $filters ) || empty( $column ) ) {
			return null;
		}
		$where = $this->construct_where_clause_with_and( $filters );
		$sql   = 'SELECT ' . $column . ' FROM ' . $this->tcm_table_name( 'email_hash' ) . $where;
		$sql   = $this->prepare( $sql, $filters );

		return $this->wpdb->get_var( $sql );
	}

	/**
	 * Insert $params
	 *
	 * @param $params
	 *
	 * @return false|int
	 */
	public function insert_log( $params ) {
		return $this->wpdb->insert( $this->tcm_table_name( 'logs' ), $params );
	}

	/**
	 * Insert $params
	 *
	 * @param $params
	 *
	 * @return false|int
	 */
	public function insert_email_hash( $params ) {
		return $this->wpdb->insert( $this->tcm_table_name( 'email_hash' ), $params );
	}

	/**
	 * Update $params
	 *
	 * @param $params
	 * @param $filters
	 *
	 * @return false|int
	 */
	public function update_log( $params, $filters ) {
		return $this->wpdb->update( $this->tcm_table_name( 'logs' ), $params, $filters );
	}

	/**
	 * Update $params
	 *
	 * @param $params
	 * @param $filters
	 *
	 * @return false|int
	 */
	public function update_email_hash( $params, $filters ) {
		return $this->wpdb->update( $this->tcm_table_name( 'email_hash' ), $params, $filters );
	}

	/**
	 * Construct a where clause with and between parameters
	 *
	 * @param $filters
	 *
	 * @return bool|string
	 */
	private function construct_where_clause_with_and( $filters, $operator = 'AND' ) {
		$where = ' WHERE ';
		if ( ! is_array( $filters ) ) {
			return false;
		}
		foreach ( $filters as $key => $value ) {
			$where .= $key . ' = ';
			$where .= ( is_string( $value ) ) ? ' %s ' : ' %d ';
			if ( end( $filters ) !== $value ) {
				$where .= $operator;
			}
		}

		return $where;
	}

	/**
	 *
	 * replace table names in form of {table_name} with the prefixed version
	 *
	 * @param $sql
	 * @param $params
	 *
	 * @return false|null|string
	 */
	public function prepare( $sql, $params ) {
		$prefix = $this->tcm_table_name( '' );
		$sql    = preg_replace( '/\{(.+?)\}/', '`' . $prefix . '$1' . '`', $sql );

		if ( strpos( $sql, '%' ) === false ) {
			return $sql;
		}

		return $this->wpdb->prepare( $sql, $params );
	}

	/** Add prefix to table name
	 *
	 * @param $table
	 *
	 * @return string
	 */
	public function tcm_table_name( $table ) {
		global $wpdb;

		return $wpdb->prefix . Thrive_Comments_Constants::DB_PREFIX . $table;
	}

	/**
	 * Get the most active Commenters between a given date.
	 *
	 * @param int      $amount             number of top commenters.
	 * @param DateTime $begin_date         contains begin adn end dates.
	 * @param DateTime $end_date           contains begin adn end dates.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array|null|object
	 */
	public function top_comment_authors( $amount, $begin_date, $end_date, $include_moderators ) {
		global $wpdb;
		$begin = '"' . $begin_date->format( 'Y-m-d' ) . '"';
		$end   = '"' . $end_date->format( 'Y-m-d' ) . '"';

		$moderator_cond = '';
		if ( ! $include_moderators ) {
			$moderators = tcamh()->tcm_get_moderator_ids();
			if ( ! empty( $moderators ) ) {
				$moderator_cond = ' AND user_id NOT IN ( ' . implode( ', ', $moderators ) . ' )';
			}
		}

		$results = $wpdb->get_results( 'SELECT COUNT(comment_author_email) AS comments_count, comment_author_email, comment_author, comment_author_url
    									FROM ' . $wpdb->comments . ' WHERE comment_author_email != "" AND comment_type = "" AND comment_approved = 1
    									AND cast(comment_date as date) BETWEEN ' . $begin . ' AND ' . $end . $moderator_cond .
		                               ' GROUP BY comment_author_email ORDER BY comments_count DESC, comment_author ASC LIMIT ' . $amount );

		return $results;
	}

	/**
	 * Get the most active Commenters between a given date.
	 *
	 * @param int      $amount             number of top commenters.
	 * @param DateTime $begin_date         contains begin adn end dates.
	 * @param DateTime $end_date           contains begin adn end dates.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array|null|object
	 */
	public function most_upvoted_comments( $begin_date, $end_date, $include_moderators ) {
		global $wpdb;
		$begin = '"' . $begin_date->format( 'Y-m-d' ) . '"';
		$end   = '"' . $end_date->format( 'Y-m-d' ) . '"';

		$moderator_cond = '';
		if ( ! $include_moderators ) {
			$moderators = tcamh()->tcm_get_moderator_ids();
			if ( ! empty( $moderators ) ) {
				$moderator_cond = ' AND user_id NOT IN ( ' . implode( ', ', $moderators ) . ' )';
			}
		}

		$results = $wpdb->get_results( 'SELECT c.comment_ID, c.comment_author_email, c.comment_author, c.comment_content, c.comment_post_ID, cm.meta_key, cm.meta_value
											   FROM ' . $wpdb->comments . ' AS c
											   INNER JOIN ' . $wpdb->commentmeta . " AS cm ON c.comment_ID = cm.comment_ID
											   WHERE (cm.meta_key = 'upvote' OR cm.meta_key = 'downvote' )" . '
											   AND cast(c.comment_date as date) BETWEEN ' . $begin . ' AND ' . $end . $moderator_cond . '
											   ORDER BY cm.meta_value DESC' );

		return $results;
	}

	/**
	 * Get all the data between the 2 dates selected.
	 *
	 * @param string   $graph_source       post_id.
	 * @param string   $graph_interval     time interval.
	 * @param DateTime $begin_date         contains begin adn end dates.
	 * @param DateTime $end_date           contains begin adn end dates.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array|null|object
	 */
	public function get_comment_reports_query( $graph_source, $graph_interval, $begin_date, $end_date, $include_moderators, $request = false ) {
		global $wpdb;
		$data           = array();
		$begin          = '"' . $begin_date->format( 'Y-m-d' ) . '"';
		$end            = '"' . $end_date->format( 'Y-m-d' ) . '"';
		$date_interval  = ' WHERE cast(comment_date as date) BETWEEN ' . $begin . ' AND ' . $end;
		$extra_query    = '';
		$featured_query = '';

		$moderator_cond = '';
		if ( ! $include_moderators ) {
			$moderators = tcamh()->tcm_get_moderator_ids();
			if ( ! empty( $moderators ) ) {
				$moderator_cond = ' AND user_id NOT IN ( ' . implode( ', ', $moderators ) . ' )';
			}
		}

		if ( ! empty( $graph_source ) ) {
			$graph_source         = apply_filters( 'tcm_reports_post_filter', $graph_source, $request );
			$post_filter          = ' AND comment_post_ID = ' . $graph_source;
			$featured_post_filter = 'AND c.comment_post_ID = ' . $graph_source;
		} else {
			$post_filter          = '';
			$featured_post_filter = '';
		}

		switch ( $graph_interval ) {
			case '1 week':
				$query = "SELECT CONCAT(YEAR(comment_date), '-', WEEK(comment_date)) as 'date', ";
				break;
			case '1 month':
				$query = "SELECT DATE_FORMAT(comment_date, '%Y-%m') as 'date', ";
				break;
			default:
				$query = "SELECT DATE_FORMAT(comment_date, '%Y-%m-%d') as 'date', ";
				break;
		}
		$select1 = $select2 = $select3 = $query;

		/**
		 * Added for compatibility with TA
		 *
		 * Expand the query for featured comments
		 */
		$featured_query = apply_filters( 'tcm_reports_featured_query', $featured_query, $request );

		/**
		 * Added for compatibility with TA
		 *
		 * Expend main query
		 */
		$extra_query = apply_filters( 'tcm_reports_extra_filter', $extra_query, $request );

		$select1 .= "comment_approved, COUNT(*) as 'count' FROM " . $wpdb->comments . $extra_query . $date_interval . $post_filter . $moderator_cond . ' GROUP BY date, comment_approved ';
		$select2 .= "'tcm_unreplied' AS comment_parent, COUNT(*) AS 'count' FROM " . $wpdb->comments . $date_interval . $post_filter . ' AND comment_parent = 0' . $moderator_cond . ' GROUP BY date ';
		$select3 .= "'tcm_featured', COUNT(*) AS 'count' FROM " . $wpdb->comments . ' AS c INNER JOIN ' . $wpdb->commentmeta . ' AS cm ON c.comment_ID = cm.comment_ID' . $featured_query . $date_interval . " AND cm.meta_key = 'tcm_featured' AND cm.meta_value = 1 " . $featured_post_filter . $moderator_cond . ' GROUP BY date ';

		$query   = $select1 . 'UNION ' . $select2 . 'UNION ' . $select3 . 'ORDER BY `date`  ASC';
		$results = $wpdb->get_results( $query );

		foreach ( $results as $result ) {
			$data[ $result->date ][ $result->comment_approved ] = $result->count;
		}

		return $data;
	}

	/**
	 * Get all the votes for the comments between the 2 dates selected.
	 *
	 * @param string   $graph_source       post_id.
	 * @param string   $graph_interval     time interval.
	 * @param DateTime $begin_date         contains begin adn end dates.
	 * @param DateTime $end_date           contains begin adn end dates.
	 * @param int      $include_moderators include_moderators.
	 *
	 * @return array|null|object
	 */
	public function get_votes_reports_query( $graph_source, $graph_interval, $begin_date, $end_date, $include_moderators, $request ) {
		global $wpdb;
		$graph_source  = apply_filters( 'tcm_reports_post_filter', $graph_source, $request );
		$data          = array();
		$begin         = '"' . $begin_date->format( 'Y-m-d' ) . '"';
		$end           = '"' . $end_date->format( 'Y-m-d' ) . '"';
		$date_interval = ' WHERE cast(comment_date as date) BETWEEN ' . $begin . ' AND ' . $end;
		$post_filter   = ( ! empty( $graph_source ) ) ? ' AND comment_post_ID = ' . $graph_source : '';
		$extra_query   = '';

		$moderator_cond = '';
		if ( ! $include_moderators ) {
			$moderators = tcamh()->tcm_get_moderator_ids();
			if ( ! empty( $moderators ) ) {
				$moderator_cond = ' AND user_id NOT IN ( ' . implode( ', ', $moderators ) . ' )';
			}
		}

		switch ( $graph_interval ) {
			case '1 week':
				$query = "SELECT CONCAT(YEAR(comment_date), '-', WEEK(comment_date,1)) as 'date', cm.meta_key, cm.meta_value ";
				break;
			case '1 month':
				$query = "SELECT DATE_FORMAT(comment_date, '%Y-%m') as 'date', cm.meta_key, cm.meta_value ";
				break;
			default:
				$query = "SELECT DATE_FORMAT(comment_date, '%Y-%m-%d') as 'date', cm.meta_key, cm.meta_value ";
				break;
		}

		/**
		 * Added for compatibility with TA
		 *
		 * Allow extending the query
		 */
		$extra_query = apply_filters( 'tcm_reports_votes_extra_filter', $extra_query, $request );

		$query .= 'FROM ' . $wpdb->comments . ' AS c INNER JOIN ' . $wpdb->commentmeta . ' AS cm ON c.comment_ID = cm.comment_ID' . $extra_query . $date_interval . " AND ( cm.meta_key = 'upvote' OR cm.meta_key = 'downvote' )" . $post_filter . $moderator_cond . ' ORDER BY `date` ASC';

		$results = $wpdb->get_results( $query );

		foreach ( $results as $result ) {
			if ( ! empty( $data[ $result->date ][ $result->meta_key ] ) ) {
				$data[ $result->date ][ $result->meta_key ] += intval( $result->meta_value );
			} else {
				$data[ $result->date ][ $result->meta_key ] = intval( $result->meta_value );
			}
		}

		return $data;
	}
}

/**
 *  Main instance of Thrive Comments Db.
 *
 * @return Thrive_Comments_Database
 */
function tcmdb() {
	return Thrive_Comments_Database::instance();
}

tcmdb();
