<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

namespace TVD\Content_Sets;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Rule
 *
 * @package TVD\Content_Sets
 * @project : thrive-dashboard
 */
class Rule implements \JsonSerializable {

	const HIDDEN_POST_TYPE_SEARCH_RESULTS = 'tvd_search_result_page';
	const HIDDEN_POST_TYPE_BLOG           = 'tvd_blog_page';

	const OPERATOR_IS           = '===';
	const OPERATOR_NOT_IS       = '!==';
	const OPERATOR_GRATER_EQUAL = '>=';
	const OPERATOR_LOWER_EQUAL  = '<=';
	const OPERATOR_WITHIN_LAST  = 'within_last';

	const FIELD_ALL            = '-1';
	const FIELD_TITLE          = 'title';
	const FIELD_TAG            = 'tag';
	const FIELD_CATEGORY       = 'category';
	const FIELD_PUBLISHED_DATE = 'published_date';
	const FIELD_TOPIC          = 'topic';
	const FIELD_DIFFICULTY     = 'difficulty';
	const FIELD_LABEL          = 'label';
	const FIELD_AUTHOR         = 'author';

	/**
	 * @var string Query String needed to for post search
	 */
	protected $query_string = '';
	protected $paged        = false;
	protected $per_page     = 15;
	protected $user_fields  = array(
		self::FIELD_AUTHOR,
	);

	/**
	 * @var mixed
	 */
	public $type;
	/**
	 * @var mixed
	 */
	public $value;
	/**
	 * @var mixed
	 */
	public $content;
	/**
	 * @var mixed
	 */
	public $field;
	/**
	 * @var mixed
	 */
	public $operator;

	/**
	 * Rule constructor.
	 *
	 * @param array $data
	 */
	public function __construct( $data ) {
		$this->type     = $data['content_type'];
		$this->content  = $data['content'];
		$this->field    = $data['field'];
		$this->operator = $data['operator'];
		$this->value    = $data['value'];
	}

	/**
	 * Factory for Rule Classes
	 *
	 * @param array $data
	 *
	 * @return Term_Rule|Post_Rule
	 */
	public static function factory( $data ) {


		switch ( $data['content_type'] ) {
			case 'term':
				$class_name = 'Term_Rule';
				break;
			case self::HIDDEN_POST_TYPE_SEARCH_RESULTS:
				$class_name = 'Search_Result_Rule';
				break;
			case self::HIDDEN_POST_TYPE_BLOG:
				$class_name = 'Blog_Rule';
				break;
			case 'archive':
				$class_name = 'Archive_Rule';
				break;
			default:
				$class_name = 'Post_Rule';
				break;
		}

		$class_name = __NAMESPACE__ . '\\' . $class_name;

		return new $class_name( $data );
	}

	/**
	 * Returns true if the rule is valid
	 *
	 * @return bool
	 */
	public function is_valid() {
		return ! empty( $this->type ) && in_array( $this->type, array( 'post', 'term', 'archive' ) ) &&
		       ! empty( $this->content ) &&
		       ! empty( $this->field ) &&
		       ( (int) $this->field === - 1 || ( ! empty( $this->operator ) && ( is_array( $this->value ) || ! empty( $this->value ) || is_numeric( $this->value ) ) ) );
	}

	/**
	 * Return true if the active rule is equal to the rule provided as parameter
	 *
	 * @param Rule $rule
	 *
	 * @return boolean
	 */
	public function is_equal_to( $rule ) {
		return serialize( $rule->jsonSerialize( false ) ) === serialize( $this->jsonSerialize( false ) );
	}

	/**
	 * Returns true if rule has value equal to parameter or value in array of values
	 *
	 * @param scalar $value
	 *
	 * @return boolean
	 */
	public function has_value( $value ) {
		if ( $this->value === $value || ( is_array( $this->value ) && in_array( $value, $this->value, true ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Should be extended in child classes
	 *
	 * @param string   $query_string
	 * @param bool|int $paged    if non-false, it will return limited results
	 * @param int      $per_page number of results per page. ignored if $paged = false
	 *
	 * @return array
	 */
	public function get_items( $query_string = '', $paged = false, $per_page = 15 ) {
		return array();
	}

	/**
	 * Test if a rule matches the given params
	 *
	 * @param \WP_Post|\WP_Term $post_or_term
	 *
	 * @return bool
	 */
	public function matches( $post_or_term ) {

		list( $content, $value ) = Utils::get_post_or_term_parts( $post_or_term );

		if ( ! $this->match_content( $content ) ) {
			return false;
		}

		if ( (int) $this->field === - 1 ) {
			return true;
		}

		return $this->match_value( $value, $post_or_term );
	}

	/**
	 * @param \WP_Post|\WP_Term $post_or_term
	 *
	 * @used in Thrive Apprentice - edit-post view
	 * @return bool
	 */
	public function matches_static_value( $post_or_term ) {
		list( $content, $value ) = Utils::get_post_or_term_parts( $post_or_term );

		if ( ! $this->match_content( $content ) ) {
			return false;
		}

		if ( $this->field !== self::FIELD_TITLE ) {
			return false;
		}

		return $this->match_value( $value, $post_or_term );
	}

	/**
	 * @param string $content
	 *
	 * @return bool
	 */
	public function match_content( $content ) {
		return $this->content === $content;
	}

	/**
	 * @param string|array               $value
	 * @param \WP_Post|\WP_Term|\WP_User $post_or_term
	 *
	 * @return bool
	 */
	public function match_value( $value, $post_or_term ) {

		if ( is_array( $this->value ) ) {
			if ( $this->operator === self::OPERATOR_IS ) {
				return in_array( $value, $this->value );
			}

			if ( $this->operator === self::OPERATOR_NOT_IS ) {
				return ! in_array( $value, $this->value );
			}
		}

		return $value === $this->value;
	}

	/**
	 * Constructs the item needed for front-end
	 * Needs to be extended in child classes
	 *
	 * @param int $item
	 *
	 * @return array
	 */
	public function get_frontend_item( $item ) {
		if ( $this->should_search_users() ) {
			return $this->get_frontend_user( $item );
		}

		return $this->get_frontend_term( $item );
	}

	/**
	 * Constructs the item from a term, needed for front-end
	 *
	 * @param int $item
	 *
	 * @return array
	 */
	public function get_frontend_user( $item ) {
		$user = get_user_by( 'id', $item );

		if ( empty( $user ) || ! $user instanceof \WP_User ) {
			return array();
		}

		return array(
			'id'   => (int) $item,
			'text' => $user->display_name,
		);
	}

	/**
	 * Constructs the item from a term, needed for front-end
	 *
	 * @param int $item
	 *
	 * @return array
	 */
	public function get_frontend_term( $item ) {
		$term = get_term( $item );

		if ( empty( $term ) ) {
			return array();
		}

		return array(
			'id'   => $term->term_id,
			'text' => $term->name,
		);
	}

	/**
	 * @return string
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * @return array
	 */
	public function get_value() {
		return $this->value;
	}

	/**
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize( $frontend = true ) {

		$value = $this->value;

		if ( is_array( $this->value ) ) {
			if ( $frontend ) {
				$value = array_map( function ( $item ) {
					if ( isset( $item ) && is_int( $item ) && $front_item = $this->get_frontend_item( $item ) ) {
						return $front_item;
					}
				}, $this->value );

				//Remove empty values for UI
				//Solves the case when we have a content set that contains a course or a post and that post is no longer available
				//Also reset the indexes of the array
				$value = array_values( array_filter( $value ) );
			} else {
				$value = $this->value = array_map( static function ( $item ) {
					if ( is_numeric( $item ) ) {
						return (int) $item;
					}

					if ( ! empty( $item['id'] ) || is_numeric( $item['id'] ) ) {
						return (int) $item['id'];
					}
				}, $this->value );
			}
		}

		$return = array(
			'content_type' => $this->type,
			'content'      => $this->content,
			'field'        => $this->field,
			'operator'     => $this->operator,
			'value'        => $value,
		);

		if ( $frontend ) {
			$return['content_label'] = array(
				'singular' => 'Post',
				'plural'   => 'Posts',
			);

			if ( $return['content_type'] === 'term' ) {

				/**
				 * @var \WP_Taxonomy
				 */
				$taxonomy = get_taxonomy( $return['content'] );

				if ( $taxonomy instanceof \WP_Taxonomy ) {
					$return['content_label']['singular'] = $taxonomy->labels->singular_name;
					$return['content_label']['plural']   = $taxonomy->labels->name;
				}
			} elseif ( $return['content_type'] === 'post' ) {
				/**
				 * @var \WP_Post_Type
				 */
				$postType = get_post_type_object( $return['content'] );

				if ( $postType instanceof \WP_Post_Type ) {
					$return['content_label']['singular'] = $postType->labels->singular_name;
					$return['content_label']['plural']   = $postType->labels->name;
				}
			} elseif ( $return['content_type'] === self::HIDDEN_POST_TYPE_SEARCH_RESULTS ) {
				$return['content_label']['plural'] = $return['content_label']['singular'] = 'Search result page';
			} elseif ( $return['content_type'] === self::HIDDEN_POST_TYPE_BLOG ) {
				$return['content_label']['plural'] = $return['content_label']['singular'] = 'Blog page';
			} elseif ( $return['content_type'] === 'archive' ) {
				$return['content_label']['plural'] = $return['content_label']['singular'] = 'Archive page';
			}
		}

		return $return;
	}

	/**
	 * Alter the title that is shown in the UI depending on the status
	 *
	 * @param string $title
	 * @param string $status
	 *
	 * @return string
	 */
	protected function alter_frontend_title( $title, $status = 'publish' ) {
		if ( $status !== 'publish' ) {
			$title .= ' [' . $status . ']';
		}

		return $title;
	}

	/**
	 * @return array
	 */
	public function search_users() {
		$search_string = esc_attr( trim( $this->query_string ) );
		$response      = array();

		$users = new \WP_User_Query( array(
				'search'         => '*' . $search_string . '*',
				'search_columns' => array(
					'display_name',
				),
				'number'         => $this->paged !== false ? $this->per_page : - 1,
				'offset'         => $this->paged !== false ? ( $this->paged - 1 ) * $this->per_page : 0,
			)
		);

		/**
		 * @var \WP_User $user
		 */
		foreach ( $users->get_results() as $user ) {
			$response[] = array(
				'id'   => (int) $user->data->ID,
				'text' => (string) $user->data->display_name,
			);
		}

		return $response;
	}

	/**
	 * Returns true if the system should search in user tables for values
	 *
	 * @return bool
	 */
	protected function should_search_users() {
		return ! empty( $this->user_fields ) && in_array( $this->field, $this->user_fields );
	}
}
