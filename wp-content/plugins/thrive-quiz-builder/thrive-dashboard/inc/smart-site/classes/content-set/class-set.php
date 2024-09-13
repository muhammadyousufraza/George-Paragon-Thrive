<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Content_Sets;

use TVD\Cache\Cache_Exception;
use function TVD\Cache\content_set_cache;
use function TVD\Cache\meta_cache;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Set
 *
 * @property int    ID
 * @property string $post_title
 * @property array  $post_content
 *
 * @package TVD\Content_Sets
 * @project : thrive-dashboard
 */
class Set implements \JsonSerializable {

	/**
	 * Stores the identified content set
	 *
	 * @var array
	 */
	private static $matched;

	use \TD_Magic_Methods;

	/**
	 * @var string Post type name
	 */
	const POST_TYPE = 'tvd_content_set';

	/**
	 * default properties for a TA Course
	 *
	 * @var array
	 */
	protected $_defaults
		= array(
			'ID'           => 0,
			'post_title'   => '',
			'post_content' => array(),
		);

	private $rules = array();

	/**
	 * Set constructor.
	 *
	 * @param int|array $data
	 */
	public function __construct( $data ) {
		if ( is_int( $data ) ) {
			$data                 = get_post( (int) $data, ARRAY_A );
			$data['post_content'] = thrive_safe_unserialize( $data['post_content'] );
		} elseif ( is_array( $data ) && ! empty( $data['post_content'] ) ) {
			$data['post_content'] = thrive_safe_unserialize( $data['post_content'] );
		}

		$this->_data = array_merge( $this->_defaults, (array) $data );

		foreach ( $data['post_content'] as $rule_data ) {
			$this->rules[] = Rule::factory( $rule_data );
		}
	}

	/**
	 * Register post type
	 */
	public static function init() {

		register_post_type( static::POST_TYPE, array(
			'labels'              => array(
				'name' => 'Content Set',
			),
			'publicly_queryable'  => true, //Needs to be queryable on front-end for products
			'public'              => false,
			'query_var'           => false,
			'rewrite'             => false,
			'show_in_nav_menus'   => false,
			'show_in_menu'        => false,
			'show_ui'             => false,
			'exclude_from_search' => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'map_meta_cap'        => true,
		) );
	}

	/**
	 * @param array $args
	 *
	 * @return Set[]
	 */
	public static function get_items( $args = array() ) {
		$posts = get_posts( array_merge( array(
			'posts_per_page' => - 1,
			'post_type'      => static::POST_TYPE,
		), $args ) );

		$sets = array();
		foreach ( $posts as $post ) {
			$sets[] = new Set( $post->to_array() );
		}

		return $sets;
	}

	/**
	 * Return all content sets as key => name
	 * Used for adding into a select element
	 *
	 * @param array|null $sets
	 *
	 * @return array
	 */
	public static function get_items_for_dropdown( $sets = null ) {
		$dropdown = array();

		if ( ! is_array( $sets ) ) {
			$sets = static::get_items();
		}

		foreach ( $sets as $set ) {
			$dropdown[] = array( 'id' => $set->ID, 'text' => $set->post_title );
		}

		return $dropdown;
	}

	/**
	 * @param \WP_Post|\WP_Term $post_or_term
	 * @param string            $return_type can be objects or ids
	 *
	 * @return array
	 */
	public static function get_items_that_static_match( $post_or_term, $return_type = 'objects' ) {
		$return = array();

		foreach ( static::get_items() as $set ) {
			if ( $set->has_matching_static_rules( $post_or_term ) ) {
				$return[] = $return_type === 'ids' ? $set->ID : $set;
			}
		}

		return $return;
	}

	/**
	 * @param       $post_or_term
	 * @param array $sets_ids_for_object
	 *
	 * @return string|void
	 */
	public static function toggle_object_to_set_static_rules( $post_or_term, $sets_ids_for_object = array() ) {

		list( $content, $value ) = Utils::get_post_or_term_parts( $post_or_term );

		if ( $post_or_term instanceof \WP_Post && ! array_key_exists( $content, Post_Rule::get_content_types() ) ) {
			return;
		}

		$current_matches = static::get_items_that_static_match( $post_or_term, 'ids' );

		sort( $current_matches );
		sort( $sets_ids_for_object );

		if ( $current_matches == $sets_ids_for_object ) {
			//No modifications have been done to the content sets$ty
			return;
		}

		$rule = Rule::factory( array(
			'content_type' => 'post',
			'content'      => $content,
			'field'        => Rule::FIELD_TITLE,
			'operator'     => Rule::OPERATOR_IS,
			'value'        => array( $value ),
		) );

		//Remove rule from content set
		$sets_to_remove = array_diff( $current_matches, $sets_ids_for_object );
		foreach ( $sets_to_remove as $id ) {
			$set = new Set( (int) $id );
			$set->remove_rule( $rule )->remove_rule_value( $value )->update();
		}

		//Add rule to content set
		$sets_to_add = array_diff( $sets_ids_for_object, $current_matches );
		foreach ( $sets_to_add as $id ) {
			$set = new Set( (int) $id );
			$set->add_rule( $rule )->update();
		}
	}

	/**
	 * @param \WP_Post|\WP_Term|\WP_User $post_or_term
	 *
	 * @return bool
	 */
	public function has_matching_rules( $post_or_term ) {

		$return = false;

		/**
		 * @var Rule $rule
		 */
		foreach ( $this->rules as $rule ) {
			if ( $rule->matches( $post_or_term ) ) {
				$return = true;
				break;
			}
		}

		return $return;
	}

	/**
	 * @param \WP_Post|\WP_Term $post_or_term
	 *
	 * @return bool
	 */
	public function has_matching_static_rules( $post_or_term ) {
		$return = false;

		/**
		 * @var Rule $rule
		 */
		foreach ( $this->rules as $rule ) {
			if ( $rule->matches_static_value( $post_or_term ) ) {
				$return = true;
				break;
			}
		}

		return $return;
	}

	/**
	 * @param Rule $rule
	 *
	 * @return $this
	 */
	public function remove_rule( $rule ) {

		$index_to_remove = null;

		/**
		 * @var Rule $r
		 */
		foreach ( $this->rules as $index => $r ) {
			if ( $r->is_equal_to( $rule ) ) {
				$index_to_remove = $index;
				break;
			}
		}

		if ( ! is_numeric( $index_to_remove ) ) {
			return $this;
		}

		unset( $this->rules[ $index_to_remove ] );

		return $this;
	}

	/**
	 * Remove value from the rules
	 *
	 * @param scalar $value
	 *
	 * @return $this
	 */
	public function remove_rule_value( $value ) {

		if ( empty( $value ) ) {
			return $this;
		}

		/**
		 * @var Rule $r
		 */
		foreach ( $this->rules as $index => $r ) {
			if ( $r->has_value( $value ) ) {
				if ( is_array( $r->value ) && ( $key = array_search( $value, $r->value ) ) !== false ) {
					unset( $this->rules[ $index ]->value[ $key ] );

					//Reset the indexes
					$this->rules[ $index ]->value = array_values( $this->rules[ $index ]->value );
				} elseif ( is_scalar( $r->value ) ) {
					$this->rules[ $index ]->value = '';
				}
			}
		}

		return $this;
	}

	/**
	 * @param Rule $rule
	 *
	 * @return $this
	 */
	public function add_static_rule( $rule ) {
		if ( ! $rule->is_valid() ) {
			return $this;
		}

		$child_added = false;

		/**
		 * @var Rule $r
		 */
		foreach ( $this->rules as $r ) {
			if ( $rule->get_content() === $r->get_content() && $rule->field === Rule::FIELD_TITLE && $r->field === Rule::FIELD_TITLE ) {
				if ( is_array( $rule->value ) && is_array( $r->value ) ) {
					$r->value    = array_merge( $rule->value, $r->value );
					$child_added = true;
					break;
				}
			}
		}

		if ( ! $child_added ) {
			$this->rules[] = $rule;
		}

		return $this;
	}

	/**
	 * @param Rule $rule
	 *
	 * @return $this
	 */
	public function add_rule( $rule ) {

		if ( ! $rule->is_valid() ) {
			return $this;
		}

		/**
		 * @var Rule $r
		 */
		foreach ( $this->rules as $r ) {
			if ( $r->is_valid() && $r->is_equal_to( $rule ) ) {
				return $this;
			}
		}

		$this->rules[] = $rule;

		return $this;
	}

	/**
	 * Identify all the sets that contain the given object
	 *
	 * @param \WP_Post|\WP_Term $post_or_term
	 * @param string            $return_type what to return - objects or IDs
	 *
	 * @return array
	 */
	public static function identify_from_object( $post_or_term, $return_type = 'objects' ) {
		$sets = array();

		if ( ! $post_or_term instanceof \WP_Post && ! $post_or_term instanceof \WP_Term ) {
			return $sets;
		}

		/**
		 * @var Set $set
		 */
		foreach ( static::get_items() as $set ) {
			if ( $set->has_matching_rules( $post_or_term ) ) {
				$sets[] = $return_type === 'objects' ? $set : $set->ID;
			}
		}

		return $sets;
	}

	/**
	 * @return int|\WP_Error
	 */
	public function create() {

		$rules = $this->prepare_rules_for_db();

		$valid = ! empty( $this->post_title ) && is_array( $rules );

		if ( ! $valid ) {
			return 0;
		}

		/**
		 * We need to make sure that fire_after_hooks is false because other plugins also call the create method on save_post hooks
		 */
		return wp_insert_post( array(
			'post_title'   => $this->post_title,
			'post_content' => serialize( $rules ),
			'post_type'    => static::POST_TYPE,
			'post_status'  => 'publish',
		), false, false );
	}

	/**
	 * @return array|false|\WP_Post|null
	 */
	public function delete() {
		/**
		 * Fired before completely deleting a content set from the database.
		 *
		 * @param Set $instance the content set instance
		 */
		do_action( 'tvd_content_set_before_delete', $this );

		return wp_delete_post( $this->ID, true );
	}

	/**
	 * @return int|\WP_Error
	 */
	public function update() {
		/**
		 * Triggered before a content set is updated
		 *
		 * @param Set $instance the content set instance
		 */
		do_action( 'tvd_content_set_before_update', new static( $this->ID ) );

		$rules = $this->prepare_rules_for_db();
		$valid = ! empty( $this->post_title ) && is_array( $rules );

		if ( ! $valid ) {
			return 0;
		}

		/**
		 * We need to make sure that fire_after_hooks is false because other plugins also call the update method on save_post hooks
		 */
		$result = wp_update_post( array(
			'ID'           => $this->ID,
			'post_title'   => $this->post_title,
			'post_content' => serialize( $rules ),
		), false, false );

		if ( ! is_wp_error( $result ) ) {
			/**
			 * Triggered before a content set is updated
			 *
			 * @param Set $instance the content set instance
			 */
			do_action( 'tvd_content_set_after_update', $this );
		}

		return $result;
	}

	/**
	 * Returns the rules if the rules are valid or false otherwise
	 * Prepares the rules for database
	 *
	 * @return bool|array
	 */
	private function prepare_rules_for_db() {
		$valid = true;
		$rules = array();

		/**
		 * @var Post_Rule|Term_Rule $rule
		 */
		foreach ( $this->rules as $rule ) {
			if ( ! $rule->is_valid() ) {
				$valid = false;
				break;
			}

			$rules[] = $rule->jsonSerialize( false );
		}

		if ( $valid ) {
			return $rules;
		}

		return false;
	}

	public function get_tva_courses_ids() {

		$id_pairs = array();

		foreach ( $this->rules as $rule ) {
			if ( true === $rule instanceof Term_Rule && $rule->get_content() === 'tva_courses' ) {
				if ( $rule->field === Rule::FIELD_TITLE ) {
					$entries = $rule->get_value();
				} else {
					//Dynamic stuff
					//Fetching courses based on dynamic properties -> such as difficulty, label, topic or author
					$course_terms = get_terms( [
						'taxonomy'   => 'tva_courses',
						'hide_empty' => false,
						'meta_query' => [
							'tva_status' => [
								'key'     => 'tva_status',
								'value'   => 'private',
								'compare' => '!=',
							],
						],
					] );
					$entries      = [];
					foreach ( $course_terms as $course_term ) {
						if ( $rule->matches( $course_term ) ) {
							$entries[] = [ 'id' => $course_term->term_id ];
						}
					}
				}

				if ( ! empty( $entries ) && is_array( $entries ) && is_array( $entries[0] ) && array_key_exists( 'id', $entries[0] ) ) {
					$entries = array_column( $entries, 'id' );
				}

				$id_pairs [] = empty( $entries ) || ! is_array( $entries ) ? [] : $entries;
			}
		}

		if ( empty( $id_pairs ) ) {
			return array();
		}

		return array_merge( ...$id_pairs );
	}

	/**
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		return array(
			'ID'           => $this->ID,
			'post_title'   => $this->post_title,
			'post_content' => $this->rules,
		);
	}

	/**
	 * Identify the content sets that have rules matching the current request and store it in a local cache for further calls during this request
	 *
	 * @return int[]
	 */
	public static function get_for_request() {
		if ( ! did_action( 'parse_query' ) && ! wp_doing_ajax() ) {
			trigger_error( 'Content Sets: get_for_request() called incorrectly. It must be called after the `parse_query` hook', E_USER_WARNING );

			return [];
		}

		if ( wp_doing_ajax() ) {
			// TODO find a way to reliably match the main request
		}

		/* search in local cache first */
		if ( static::$matched !== null ) {
			return static::$matched;
		}

		/* nothing in the local cache, compute it */
		static::$matched = [];

		/* currently, only supports taxonomy terms and posts */
		if ( Utils::is_context_supported() ) {
			$queried_object = get_queried_object();

			static::$matched = empty( $queried_object ) ? static::get_for_non_object() : static::get_for_object( $queried_object );
		}

		return static::$matched;
	}

	/**
	 * @param \WP_Post|\WP_Term $object
	 * @param int|null          $id
	 */
	public static function get_for_object( $object, $id = null ) {
		if ( $id === null ) {
			$id = get_queried_object_id();
		}

		try {
			$cache = content_set_cache( $object );
		} catch ( Cache_Exception $e ) {
			/* when receiving invalid data, make sure the execution continues */
			return [];
		}

		$matched = $cache->get_or_store( $id, static function () use ( $object ) {
			return static::identify_from_object( $object, 'ids' );
		} );

		if ( $cache->hit() ) {
			/**
			 * This generates unused queries
			 * The purpose of this functionality is to fetch content sets with dynamic data - such as pusblied_date
			 * This functionality was removed from the initial content sets release
			 */
//			/**
//			 * We need to find all sets that are matched and the rule contains the time related rules
//			 */
//			$sets = static::get_items( array( 'post__in' => $matched, 's' => Rule::FIELD_PUBLISHED_DATE ) );
//
//			/**
//			 * For time related rules we apply again the match logic
//			 *
//			 * @var $set Set
//			 */
//			foreach ( $sets as $set ) {
//				if ( ! $set->has_matching_rules( $object ) ) {
//					/**
//					 * If the rules has no matches, we exclude it from the matched list
//					 */
//					$index = array_search( $set->ID, $matched );
//					unset( $matched[ $index ] );
//				}
//			}

			$matched = array_values( $matched ); //We need this to reset the indexes
		}

		return $matched;
	}

	/**
	 * Returns sets with dynamic contexts
	 * Ex: sets that have Search Result Page or Blog Page as rules.
	 *
	 * @return array
	 */
	public static function get_for_non_object() {
		$sets = array();

		/**
		 * @var null|\WP_User $maybe_user
		 */
		$maybe_user = null;

		if ( is_author() ) {
			$maybe_user = get_user_by( 'slug', get_query_var( 'author_name' ) );
		}

		foreach ( static::get_items() as $set ) {
			if ( $set->has_matching_rules( $maybe_user ) ) {
				$sets[] = $set->ID;
			}
		}

		return $sets;
	}
}
