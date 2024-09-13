<?php

use TCB\VideoReporting\Video;
use TVA\Access\History_Table;
use TVA\Architect\Dynamic_Actions;
use TVA\Assessments\TVA_User_Assessment;
use TVA\Drip\Campaign;
use TVA\Drip\Schedule\Utils;
use TVA\Drip\Trigger\Time_After_Purchase;
use TVA\Product;
use TVA\Reporting\EventFields\Course_Id;
use TVA\Reporting\Events\All_Free_Lessons_Completed;
use TVA\Reporting\Events\Course_Finish;
use TVA\Reporting\Events\Course_Start;
use TVA\Reporting\Events\Drip_Unlocked_For_User;
use TVA\Reporting\Events\Free_Lesson_Complete;
use TVA\Reporting\Events\Lesson_Complete;
use TVA\Reporting\Events\Lesson_Start;
use TVA\Reporting\Events\Module_Finish;
use TVA\Reporting\Events\Module_Start;
use TVA\Reporting\Events\Video_Completed;
use TVA\Reporting\Events\Video_Data;
use TVA\Reporting\Events\Video_Start;
use TVE\Reporting\EventFields\Event_Type;
use TVE\Reporting\EventFields\Post_Id;
use TVE\Reporting\EventFields\User_Id;
use TVE\Reporting\Logs;

if ( ! trait_exists( '\TVA\Drip\Schedule\Utils', false ) ) {
	require_once TVA_Const::plugin_path() . '/inc/drip/schedule/trait-utils.php';
}

/**
 * Class TVA_Customer Model
 * - WP_User wrapper which has specific properties for TA
 * - ThriveCart Customer
 * - SendOwl Customer
 *
 * @property array  course_begin_timestamps    Holds an array with timestamps for the first time the first lesson is accessed from a course
 *                                               It is used in a specific drip trigger
 * @property array  purchased_item_ids
 * @property array  learned_lessons
 * @property array  courses
 * @property array  progress                   array of progress in courses
 * @property string enrolled                   last enrollment date
 * @property string last_seen                  last activity date
 * @property array  items_bypassed             array of modules and lessons ids where dip settings will be bypassed
 * @property array  locked_status              array of lessons that are locked by drip
 * @property array  modules_and_lessons_status array of modules and lessons that are in progress or completed
 * @property array  enrollment_dates           array of dates when enrolled in a course
 * @property array  activity                   array of dates when user was active in a course
 * @property int    courses_count              number of courses user has access to
 * @property int    published_courses_count    number of published courses user has access to
 * @property array  courses_certificates       array of certificate numbers for courses
 * @property array  learned_items
 *
 */
class TVA_Customer implements JsonSerializable {

	use Utils;

	/** @var WP_User */
	protected $_user;

	/** @var string */
	static protected $_admin_url;

	/** @var array */
	protected $_data = [];

	/** @var array $defaults default values */
	protected $defaults = array(
		'purchased_item_ids'         => [],
		'learned_lessons'            => [],
		'learned_items'              => [],
		'course_begin_timestamps'    => [],
		'courses'                    => [],
		'progress'                   => [],
		'items_bypassed'             => [],
		'locked_status'              => [],
		'modules_and_lessons_status' => [],
		'enrollment_dates'           => [],
		'activity'                   => [],
		'courses_certificates'       => [],
		'last_seen'                  => null,
		'enrolled'                   => null,
		'courses_count'              => 0,
		'published_courses_count'    => 0,
	);

	/**
	 * TVA_Customer constructor.
	 *
	 * @param int|WP_User $data
	 */
	public function __construct( $data ) {
		if ( is_numeric( $data ) ) {
			$this->_user = new WP_User( (int) $data );

			$this->_data = $this->defaults;
		} elseif ( $data instanceof WP_User ) {
			$this->_user = $data;
		} elseif ( ! empty( $data['ID'] ) ) {
			$this->_user = new WP_User( (int) $data['ID'] );

			$this->_data = array_merge( $this->defaults, (array) $data );
		} else {
			$this->_user = new WP_User( get_current_user_id() );
		}
	}

	/**
	 * Gets $key from _data or _wp_term
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		$value = null;

		if ( isset( $this->_data[ $key ] ) ) {
			$value = $this->_data[ $key ];
		} elseif ( $this->_user instanceof WP_User && isset( $this->_user->$key ) ) {
			$value = $this->_user->$key;
		}

		return $value;
	}

	/**
	 * @param $property
	 */
	public function __set( $property, $value ) {
		if ( property_exists( $this, $property ) ) {
			$this->$property = $value;
		} else {
			$this->_data[ $property ] = $value;
		}
	}

	public function get_meta( $key ) {
		return get_user_meta( $this->get_id(), $key, true );
	}

	/**
	 * Returns the logged in customer ID
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->_user->ID;
	}

	/**
	 * Returns the WP_User object
	 *
	 * @return WP_User
	 */
	public function get_user() {
		return $this->_user;
	}

	/**
	 * Return a list of customers based on a set of filters
	 *
	 * @param array $filters
	 *
	 * @return TVA_Customer[]
	 */
	public static function get_customers( $filters = [] ) {
		$items = [];
		/**
		 * Returns a list of users that satisfy the filters
		 */
		$users = get_users( $filters );

		foreach ( $users as $user ) {
			$items[] = new self( $user );
		}

		return $items;
	}

	/**
	 * Progress Labels
	 *
	 * @var array
	 */
	private $progress_labels = [];

	/**
	 * Returns the progress labels
	 *
	 * @return array
	 */
	public function get_progress_labels() {

		if ( empty( $this->progress_labels ) ) {
			$this->set_progress_labels();
		}

		return $this->progress_labels;
	}

	/**
	 * Sets the progress labels
	 */
	private function set_progress_labels() {
		$labels = TVA_Dynamic_Labels::get( 'course_progress' );

		$this->progress_labels = array(
			TVA_Const::TVA_COURSE_PROGRESS_NOT_STARTED => $labels['not_started']['title'],
			TVA_Const::TVA_COURSE_PROGRESS_COMPLETED   => $labels['finished']['title'],
			TVA_Const::TVA_COURSE_PROGRESS_IN_PROGRESS => $labels['in_progress']['title'],
			TVA_Const::TVA_COURSE_PROGRESS_NO_ACCESS   => $labels['not_started']['title'],
		);
	}

	/**
	 * Called in this instance it has to be serialized
	 *
	 * @return array
	 */
	#[ReturnTypeWillChange]
	public function jsonSerialize() {
		return array_merge( $this->get_main_info(), [
			'user_login'                 => $this->_user->user_login,
			'edit_url'                   => $this->get_edit_url(),
			'courses'                    => $this->courses,
			'user_registered'            => $this->_user->user_registered,
			'courses_count'              => $this->courses_count,
			'lessons_completed'          => $this->learned_lessons,
			'items_completed'            => $this->learned_items,
			'items_bypassed'             => $this->items_bypassed,
			'courses_progress'           => $this->progress,
			'locked_status'              => $this->locked_status,
			'modules_and_lessons_status' => $this->modules_and_lessons_status,
			'enrollment_dates'           => $this->enrollment_dates,
			'activity'                   => $this->activity,
			'courses_certificates'       => $this->courses_certificates,
		] );
	}

	/**
	 * Returns customer main info.
	 * Used in frontend to display customer list view
	 *
	 * @return array
	 */
	public function get_main_info() {
		return [
			'ID'                      => $this->_user->ID,
			'display_name'            => $this->_user->display_name,
			'user_email'              => $this->_user->user_email,
			'avatar_url'              => get_avatar_url( $this->_user->ID ),
			'enrolled'                => $this->enrolled,
			'published_courses_count' => $this->get_published_courses_count(),
			'last_seen'               => $this->get_last_seen(),
		];
	}

	/**
	 * Retrieve the WP_CURRENT_USER learned lessons (used in frontend)
	 *
	 * @return array
	 */
	public function get_learned_lessons() {
		if ( empty( $this->learned_lessons ) ) {
			$this->_data['learned_lessons'] = tva_get_learned_lessons();
		}

		return $this->learned_lessons;
	}

	/**
	 * Returns a list of course IDs with progress
	 *
	 * @return array
	 */
	public function get_learned_courses() {
		$learned_lessons = $this->get_learned_lessons();
		if ( ! empty( $learned_lessons ) && is_array( $learned_lessons ) ) {
			return array_keys( $learned_lessons );
		}

		return [];
	}

	public function get_learned_items() {
		if ( empty( $this->learned_items ) ) {
			$this->_data['learned_items'] = array_replace_recursive( tva_get_submitted_assessments(), tva_get_learned_lessons() );
		}

		return $this->learned_items;
	}

	/**
	 * Get the learned lessons array for this student
	 */
	public function get_learned_lessons_for_student() {
		if ( ! isset( $this->learned_lessons ) && $this->get_id() ) {
			$lessons                        = $this->get_meta( 'tva_learned_lessons' );
			$this->_data['learned_lessons'] = $lessons ? $lessons : [];
		}

		return $this->learned_lessons;
	}

	/**
	 * Get the learned lessons array of this student of the course
	 *
	 * @param int $course_id
	 *
	 * @return array
	 */
	public function get_course_completed_lessons_for_student( $course_id ) {
		$this->get_learned_lessons_for_student();

		return isset( $this->learned_lessons[ $course_id ] ) ? $this->learned_lessons[ $course_id ] : [];
	}

	/**
	 * Update the learned lessons array of a student with a new lesson
	 */
	public function set_learned_lesson_for_student( $course_id, $lesson_id ) {
		$lessons                                   = $this->get_learned_lessons_for_student();
		$lessons[ $course_id ][ (int) $lesson_id ] = 1;
		$this->_data['learned_lessons']            = update_user_meta( $this->get_id(), 'tva_learned_lessons', $lessons );

		return $this->learned_lessons;
	}

	/**
	 * Returns the timestamps when the customer accessed first lesson in the course
	 * The return array is with the following form
	 *          COURSE_ID => TIMESTAMP
	 *
	 * @return array
	 */
	private function get_timestamps_for_begin_course() {
		if ( ! isset( $this->course_begin_timestamps ) ) {
			$timestamps = $this->get_meta( 'tva_course_begin_timestamp' );

			$this->course_begin_timestamps = ! empty( $timestamps ) ? $timestamps : [];
		}

		return $this->course_begin_timestamps;
	}

	/**
	 * For a specific course, returns the timestamp its first lesson it accessed
	 *
	 * @param integer $course_id
	 *
	 * @return false|DateTime|DateTimeImmutable
	 */
	public function get_begin_course_timestamp( $course_id ) {
		$timestamps = $this->get_timestamps_for_begin_course();

		return ! empty( $timestamps[ $course_id ] ) ? static::get_datetime( '@' . $timestamps[ $course_id ] ) : false;
	}

	/**
	 * Saves the timestamps in the DB and updates the timestamps cache
	 *
	 * @param integer $course_id
	 */
	public function set_begin_course_timestamp( $course_id ) {
		$this->_data['course_begin_timestamps']               = $this->get_timestamps_for_begin_course();
		$this->_data['course_begin_timestamps'][ $course_id ] = current_datetime()->getTimestamp();

		update_user_meta( $this->_user->ID, 'tva_course_begin_timestamp', $this->_data['course_begin_timestamps'] );
	}

	/**
	 * Populates the drip content unlocked meta with an additional post
	 *
	 * @param int $post_id
	 */
	public function set_drip_content_unlocked( $post_id ) {
		$this->_data['_drip_content_unlocked'] = $this->get_drip_content_unlocked();

		if ( ! in_array( $post_id, $this->_drip_content_unlocked ) ) {
			$this->_data['_drip_content_unlocked'][] = $post_id;
		}

		update_user_meta( $this->_user->ID, 'tva_drip_content_unlocked', $this->_drip_content_unlocked );
	}

	/**
	 * Returns the drip content unlocked meta
	 *
	 * @return array
	 */
	public function get_drip_content_unlocked() {
		if ( ! isset( $this->_drip_content_unlocked ) ) {
			$unlocked = $this->get_meta( 'tva_drip_content_unlocked' );

			$this->_drip_content_unlocked = ! empty( $unlocked ) ? $unlocked : [];
		}

		return $this->_drip_content_unlocked;
	}

	/**
	 * Retrieve the WP_CURRENT_USER learned lessons from a course (used in frontend)
	 *
	 * @param {integer} $course_id
	 *
	 * @return array|null
	 */
	public function get_course_learned_lessons( $course_id ) {

		$this->get_learned_lessons();

		return isset( $this->learned_lessons[ $course_id ] ) ? $this->learned_lessons[ $course_id ] : [];
	}

	public function get_course_learned_items( $course_id ) {
		$this->get_learned_items();

		return isset( $this->learned_items[ $course_id ] ) ? $this->learned_items[ $course_id ] : [];
	}

	/**
	 * Computes the course progress
	 *
	 * @param TVA_Course_V2|int $course
	 *
	 * @return int
	 */
	public function get_course_progress_status( $course ) {
		if ( is_int( $course ) ) {
			$course = new TVA_Course_V2( $course );
		}

		if ( ! $course->has_access() ) {
			return TVA_Const::TVA_COURSE_PROGRESS_NO_ACCESS;
		}

		$completed_items = count( $this->get_course_learned_items( $course->get_id() ) );


		$items_number = $course->count_course_items( [ 'post_status' => 'publish' ] );

		if ( 0 === $items_number || 0 === $completed_items ) {
			$status = TVA_Const::TVA_COURSE_PROGRESS_NOT_STARTED;
		} elseif ( $completed_items >= $items_number ) {
			$status = TVA_Const::TVA_COURSE_PROGRESS_COMPLETED;
		} else {
			$status = TVA_Const::TVA_COURSE_PROGRESS_IN_PROGRESS;
		}

		return $status;
	}

	/**
	 * Returns true if the active customer has completed the course
	 *
	 * @param TVA_Course_V2|int $course
	 *
	 * @return bool
	 */
	public function has_completed_course( $course ) {
		return $this->get_course_progress_status( $course ) === TVA_Const::TVA_COURSE_PROGRESS_COMPLETED;
	}

	/**
	 * Returns the course progress label
	 *
	 * @param TVA_Course_V2|int $course
	 *
	 * @return string
	 */
	public function get_course_progress_label( $course ) {
		$status = $this->get_course_progress_status( $course );
		$labels = $this->get_progress_labels();
		$label  = '';

		if ( isset( $labels[ $status ] ) ) {
			$label = $labels[ $status ];
		}

		return $label;
	}

	/**
	 * Returns a list of vendor item ids
	 *
	 * @param bool $force where to fetch them again from DB
	 *
	 * @return integer[]|null
	 */
	protected function _get_purchased_item_ids( $force = false ) {

		if ( null === $this->purchased_item_ids || true === $force ) {
			$this->purchased_item_ids = TVA_Order_Item::get_purchased_items(
				array(
					'user_id' => (int) $this->_user->ID,
				)
			);
		}

		return $this->purchased_item_ids;
	}

	/**
	 * List of course IDs user has access to for buying a ThriveCart product(s)
	 *
	 * @return integer[]
	 */
	public function get_thrivecart_courses() {

		return TVA_Order_Item::get_purchased_items(
			array(
				'user_id' => $this->_user->ID,
				'gateway' => TVA_Const::THRIVECART_GATEWAY,
			)
		);
	}

	/**
	 * All SendOwl Simple Product IDs user bought
	 *
	 * @return integer[]
	 */
	public function get_sendowl_products() {

		$intersect = array_intersect( $this->_get_purchased_item_ids(), TVA_SendOwl::get_products_ids() );

		return array_values( $intersect );
	}

	/**
	 * All SendOwl Bundle Product IDs user bought
	 *
	 * @return integer[]
	 */
	public function get_sendowl_bundles() {

		$intersect = array_intersect( $this->_get_purchased_item_ids(), TVA_SendOwl::get_bundle_ids() );

		return array_values( $intersect );
	}

	/**
	 * Gets a unique list of purchased/assigned bundles
	 */
	public function get_course_bundles() {

		$intersect = array_intersect( $this->_get_purchased_item_ids(), TVA_Course_Bundles_Manager::get_all_bundle_numbers() );

		return array_values( $intersect );
	}

	/**
	 * Returns the edit user for current user
	 *
	 * @return string
	 */
	public function get_edit_url() {

		$admin_url = $this->_get_admin_url();

		return add_query_arg( 'user_id', $this->_user->ID, $admin_url );
	}

	/**
	 * Computes certificate file name for a customer
	 *  - Generates a code for the certificate
	 *  - Saves data for the certificate in wp_options
	 *
	 * @param TVA_Course_Certificate $certificate
	 *
	 * @return string
	 */
	public function compute_certificate_file_name( $certificate ) {

		$meta_key            = static::get_certificate_meta_key( $certificate->ID );
		$customer_code       = get_user_meta( $this->get_id(), $meta_key, true );
		$certificate->number = $customer_code;

		if ( empty( $customer_code ) ) {

			$customer_code = $certificate->generate_code( $this->get_id() );

			update_user_meta( $this->get_id(), $meta_key, $customer_code );

			if ( $customer_code ) {
				update_option(
					'tva_certificate_' . $customer_code,
					$certificate->get_data( $this->get_id() ),
					false
				);
			}
		}

		return TVA_Course_Certificate::FILE_NAME_PREFIX . '-' . $certificate->ID . '-' . $customer_code;
	}

	/**
	 * Returns certificate user meta key
	 *
	 * @param int $certificate_id
	 *
	 * @return string
	 */
	public static function get_certificate_meta_key( $certificate_id ) {
		return 'tva_certificate_' . $certificate_id;
	}

	/**
	 * Lazy loading for admin user
	 *
	 * @return string
	 */
	protected function _get_admin_url() {

		if ( ! self::$_admin_url ) {
			self::$_admin_url = self_admin_url( 'user-edit.php' );
		}

		return self::$_admin_url;
	}

	/**
	 * Fetches a list of users from DB based on orders
	 * - usually users are being made by ThriveCart and SendOwl
	 *
	 * @param array $args
	 * @param bool  $count
	 *
	 * @return TVA_Customer[]|int
	 */
	public static function get_list( $args = [], $count = false ) {

		global $wpdb;

		$defaults = array(
			'offset' => 0,
			'limit'  => class_exists( 'TVA_Admin', false ) ? TVA_Admin::ITEMS_PER_PAGE : 10,
		);

		$args = array_merge( $defaults, $args );

		$offset            = (int) $args['offset'];
		$limit             = (int) $args['limit'];
		$users_table       = $wpdb->base_prefix . 'users';
		$orders_table      = $wpdb->prefix . 'tva_orders';
		$order_items_table = $wpdb->prefix . 'tva_order_items';
		$usermeta_table    = $wpdb->base_prefix . 'usermeta';
		$join              = '';

		$params = [];

		if ( empty( $args['product_id'] ) ) {
			$where = 'WHERE orders.status IS NOT NULL';
		} else {
			$where    = 'WHERE orders.status = %d';
			$params[] = TVA_Const::STATUS_COMPLETED;
		}

		if ( ! empty( $args['s'] ) ) {
			$where .= " AND ( users.display_name LIKE '%%%s%%' OR users.user_email LIKE '%%%s%%' ) ";

			$params[] = $args['s'];
			$params[] = $args['s'];
		}

		if ( ! empty( $args['product_id'] ) ) {
			if ( is_array( $args['product_id'] ) ) {
				$ids = [];
				foreach ( $args['product_id'] as $id ) {
					$ids[]    = '%d';
					$params[] = $id;
				}
				$where .= ' AND order_items.product_id IN(' . implode( ',', $ids ) . ')';
			} else {
				$where .= ' AND order_items.product_id = %d';

				$params[] = (int) $args['product_id'];
			}
		}

		if ( is_multisite() ) {
			$where .= " AND $usermeta_table.meta_key = '$wpdb->prefix" . 'capabilities\'';
			$join  .= "INNER JOIN $usermeta_table ON users.ID = $usermeta_table.user_id";
		}

		$sql = 'SELECT ' . ( $count ? 'count(DISTINCT users.ID) as count' : 'DISTINCT users.ID' ) . " FROM $orders_table AS orders
        		INNER JOIN $users_table AS users ON users.ID = orders.user_id
        		LEFT JOIN $order_items_table AS order_items ON orders.ID = order_items.order_id
        		$join
				$where
				ORDER BY users.ID DESC
				";

		$limit_sql = '';

		if ( ! $count ) {
			$limit_sql = 'LIMIT %d , %d';
			$params[]  = $offset;
			$params[]  = $limit;
		}

		$sql .= $limit_sql;

		$results = $wpdb->get_results( empty( $params ) ? $sql : $wpdb->prepare( $sql, $params ) );

		if ( $count ) {
			return (int) $results[0]->count;
		}

		$users = [];
		foreach ( $results as $item ) {
			$user = new TVA_Customer( $item->ID );
			$user->prepare_data_for_student_list();

			$users[] = $user;
		}

		return $users;
	}

	public function trigger_product_received_access( $products ) {
		foreach ( $products as $product ) {
			do_action( 'tva_user_receives_product_access', $this->_user, $product );
		}
	}

	/**
	 * Triggered when a user makes a Thrive Apprentice purchase
	 *
	 * @param $order
	 */
	public function trigger_purchase( $order ) {
		if ( is_a( $order, 'TVA_Order' ) ) {
			foreach ( $order->get_order_items() as $order_item ) {
				do_action( 'tva_purchase', $this->_user, $order_item, $order );
			}
		} elseif ( is_a( $order, 'TVA_Order_Item' ) ) {
			do_action( 'tva_purchase', $this->_user, $order, new TVA_Order( $order->get_order_id() ) );
		}
	}

	/**
	 * Fires a do_action tva_user_course_purchase when a user buys access to a course
	 * - SendOwl orders
	 * - WooCommerce orders
	 * - ThriveCart orders
	 *
	 * TODO: we need to refactor this logic.
	 * - remove course logic from this function
	 *
	 * @param TVA_Order $order
	 * @param string    $initiator
	 */
	public function trigger_course_purchase( $order, $initiator = '' ) {

		/**
		 * @deprecated This is deprecated. We do not have course logic anymore
		 *             If this is no longer user we should remove this hook
		 */
		do_action( 'tva_user_course_purchase', $this->_user, $order, $initiator );

		foreach ( $order->get_order_items() as $order_item ) {
			/**
			 * Special case for sendowl
			 *
			 * Sendowl product IDs can link to different apprentice products,
			 * Therefore we need this loop here.
			 */
			if ( $order->is_sendowl() ) {
				$products = TVA_Sendowl_Manager::get_products_that_have_protection( (int) $order_item->get_product_id() );
			} else if ( $order->is_stripe() ) {
				/**
				 * Stripe ids are actually price ids so we need to get the products
				 */
				$products = TVA_Stripe_Integration::get_all_products_for_identifier( $order_item->get_product_id() );
			} else {
				$products = [ new Product( (int) $order_item->get_product_id() ) ];
			}

			foreach ( $products as $product ) {
				$courses   = $product->get_courses();
				$campaigns = [];

				foreach ( $courses as $course ) {
					$campaign = $product->get_drip_campaign_for_course( $course );

					if ( $campaign instanceof Campaign ) {
						$campaigns[] = $campaign;
					}
				}

				foreach ( $campaigns as $campaign ) {
					$post_ids = $campaign->get_posts_with_trigger( Time_After_Purchase::NAME );
					foreach ( $post_ids as $post_id ) {
						$trigger = $campaign->get_trigger_for_post( $post_id, Time_After_Purchase::NAME );
						if ( $trigger ) {
							$trigger->schedule_event( $product->get_id(), $post_id, $order->get_user_id() ); // no $from_date parameter - the purchase event is occurring right now
						}
					}
				}
			}
		}
	}

	/**
	 * On user register check the courses that the user might get access to
	 * based on WordPress rules and trigger a user enrolment action
	 *
	 * @param int $user_id
	 *
	 * @return array
	 */
	public static function on_user_register( $user_id ) {

		$user = get_user_by( 'ID', (int) $user_id );

		if ( false === $user instanceof WP_User ) {
			return array();
		}

		$wp_protected_products = TVA\Product::get_protected_products_by_integration( 'wordpress' );

		$products = array();

		/** @var Product $product */
		foreach ( $wp_protected_products as $product ) {

			$rule = $product->get_rules_by_integration( 'wordpress' );
			$rule = array_pop( $rule );

			$matched_roles = array_filter(
				$rule['items'],
				function ( $rule_item ) use ( $user ) {

					return ! empty( $rule_item['id'] ) && in_array( $rule_item['id'], $user->roles, true );
				}
			);

			if ( ! empty( $matched_roles ) ) {
				$products[] = $product->get_id();
			}
		}

		$customer = new TVA_Customer( $user->ID );

		if ( ! empty( $products ) ) {
			$customer->trigger_product_received_access( $products );
		}

		return $products;
	}

	/**
	 * Gives user access to a product
	 *
	 * @param int       $user_id user has to exist
	 * @param int|array $product_id
	 *
	 * @return bool whether the user got access
	 */
	public static function enrol_user_to_product( $user_id, $product_id ) {

		$user     = get_user_by( 'ID', (int) $user_id );
		$tva_user = new TVA_User( $user_id );

		if ( false === $user instanceof WP_User || empty( $product_id ) ) {
			return false;
		}

		if ( ! is_array( $product_id ) ) {
			$product_id = array( $product_id );
		}

		$new_products = [];
		/**
		 * Enroll only in new courses
		 */
		foreach ( $product_id as $product ) {
			if ( ! $tva_user->has_bought( $product ) ) {
				/* check if product exists */
				$instance = new Product( $product );
				if ( $instance->get_id() ) {
					$new_products[] = $product;
				}
			}
		}

		if ( ! empty( $new_products ) ) {
			TVA_Customer_Manager::create_order_for_customer(
				$user,
				'course_ids',
				$new_products,
				array(
					'gateway' => TVA_Const::MANUAL_GATEWAY,
				)
			);
		}

		return true;
	}

	/**
	 * Removes user access from a product
	 *
	 * @param int|WP_User $user
	 * @param int|Product $course
	 *
	 * @return bool
	 */
	public static function remove_user_from_product( $user, $product ) {

		if ( false === $user instanceof WP_User ) {
			$user = get_user_by( 'ID', (int) $user );
		}

		if ( false === $product instanceof Product ) {
			$product = new Product( (int) $product );
		}

		if ( ! $user || ! $product ) {
			return false;
		}

		tva_access_manager()
			->set_tva_user( $user )
			->set_user( $user )
			->set_product( $product );

		$has_access = tva_access_manager()->check_rules();

		//while user still has access to the course, try to disable all orders
		while ( $has_access ) {

			$integration = tva_access_manager()->get_allowed_integration();

			//if user has a specific role and the course is protected by this specific role
			//the user cannot be removed from the course
			if ( true === $integration instanceof TVA_WP_Integration ) {
				$has_access = false;
				continue;
			}

			//deactivate order item
			$order_item = $integration->get_order_item();
			if ( true === $order_item instanceof TVA_Order_Item ) {
				$order_item->set_status( 0 )->save();
			}

			$order          = $integration->get_order();
			$disabled_items = 0;

			if ( true === $order instanceof TVA_Order ) {
				foreach ( $order->get_order_items() as $item ) {
					if ( 0 === $item->get_status() ) {
						$disabled_items ++;
					}
				}
				if ( count( $order->get_order_items() ) <= $disabled_items ) {
					$order->set_status( TVA_Const::STATUS_EMPTY )->save( false );
				}
			}

			$has_access = tva_access_manager()->check_rules();
		}

		return true;
	}

	/**
	 * Returns activity of a student in all of his courses
	 *
	 * @return array|null
	 */
	public function get_activity() {
		if ( empty( $this->_data['activity'] ) ) {
			$this->_data['activity'] = [];

			$data = Lesson_Start::get_data( [
				'event_type' => [
					Lesson_Complete::key(),
					Lesson_Start::key(),
					Course_Start::key(),
					Course_Finish::key(),
				],
				'fields'     => [
					'MAX(DATE(created)) as date',
					Course_Id::key(),
				],
				'group_by'   => [
					Course_Id::key(),
				],
				'filters'    => [
					User_Id::key() => $this->get_id(),
				],
			] );

			foreach ( $data['items'] as $item ) {
				$course_id = $item[ Course_Id::key() ];

				$this->_data['activity'][ $course_id ] = date( 'd.m.Y', strtotime( $item['date'] ) );
			}
		}

		return $this->_data['activity'];
	}

	/**
	 * Get the completion date of lessons and modules
	 *
	 * @return array|null
	 */
	public function get_modules_and_lessons_status() {
		if ( empty( $this->_data['modules_and_lessons_status'] ) ) {
			$this->_data['modules_and_lessons_status'] = [];

			$data = Lesson_Start::get_data( [
				'event_type' => [
					Lesson_Complete::key(),
					Lesson_Start::key(),
					Module_Start::key(),
					Module_Finish::key(),
				],
				'fields'     => [
					'MAX(DATE(created)) as date',
					Post_Id::key(),
					Course_Id::key(),
					Event_Type::key(),
				],
				'group_by'   => [
					Post_Id::key(),
					Event_Type::key(),
				],
				'filters'    => [
					User_Id::key()   => $this->get_id(),
					Course_Id::key() => $this->get_course_ids(),
				],
			] );

			foreach ( $data['items'] as $item ) {
				$course_id  = $item[ Course_Id::key() ];
				$post_id    = $item[ Post_Id::key() ];
				$event_type = $item[ Event_Type::key() ];

				$this->_data['modules_and_lessons_status'] [ $course_id ][ $post_id ][ $event_type ] = date( 'd.m.Y', strtotime( $item['date'] ) );
			}
		}

		return $this->_data['modules_and_lessons_status'];
	}

	/**
	 * @return array|null
	 */
	public function get_items_bypassed() {
		if ( empty( $this->items_bypassed ) ) {
			$tva_bypass_drip               = $this->get_meta( 'tva_bypass_drip' );
			$this->_data['items_bypassed'] = empty( $tva_bypass_drip ) ? [] : $tva_bypass_drip;
		}

		return $this->items_bypassed;
	}

	/**
	 * @param array|null $items_bypassed
	 */
	public function set_items_bypassed( $items_bypassed ) {
		$this->_data['items_bypassed'] = $items_bypassed;

		update_user_meta( $this->get_id(), 'tva_bypass_drip', $items_bypassed );
	}

	/**
	 * @return string|null
	 */
	public function get_last_seen() {
		if ( empty( $this->last_seen ) ) {
			$last_online = $this->get_meta( 'tve_last_online' );
			if ( empty( $last_online ) ) {
				$last_online = $this->get_meta( 'tve_last_login' );
			}

			if ( ! empty( $last_online ) ) {
				$this->last_seen = human_time_diff( $last_online );
			} else {
				$this->last_seen = 'never';
			}
		}

		return $this->last_seen;
	}

	/**
	 * Return a list of students based on a set of filters
	 *
	 * @param array $filters
	 *
	 * @return array ['items' => TVA_Customer[], 'total' => number]
	 */
	public static function get_students( $filters = [] ) {
		$students = History_Table::get_instance()->get_all_students( $filters );
		$total    = History_Table::get_instance()->get_total_students( $filters['filters'] );
		$items    = [];

		foreach ( $students as $student ) {
			$items[] = new static( $student );
		}

		return array(
			'items' => $items,
			'total' => $total,
		);
	}

	/**
	 * Prepares all the course related data for a student for student list
	 */
	public function prepare_courses_data() {
		$this->get_learned_lessons_for_student();
		$this->get_items_bypassed();
		$this->prepare_data();
		$this->get_modules_and_lessons_status();
		$this->get_enrollment_dates();
		$this->get_activity();
		$this->get_course_certificates();
	}

	/**
	 * Bulk complete lessons
	 */
	public function bulk_complete_lessons( $items_ids, $course_id ) {
		$lessons     = $this->get_learned_lessons_for_student();
		$lessons_ids = [];
		if ( ! empty( $lessons[ $course_id ] ) ) {
			$lessons_ids = array_map( 'intval', array_keys( $lessons[ $course_id ] ) );
		}

		$remained_lessons = array_diff( $items_ids, $lessons_ids );

		foreach ( $remained_lessons as $item_id ) {
			$post = get_post( $item_id );

			if ( $post->post_type === TVA_Const::LESSON_POST_TYPE ) {

				// we need to set selected lesson each time as we check the array in the function below
				$this->set_learned_lesson_for_student( $course_id, $item_id );
				tva_send_hooks_for_item( (int) $item_id, 'start', $this->get_id() );
				tva_send_hooks_for_item( (int) $item_id, 'end', $this->get_id() );
			}
		}
	}

	/**
	 * Bulk reset lessons and modules
	 * Removes the completed lessons from the 'tva_learned_lessons' user meta
	 * Removes the bypassed lessons and modules from the tva_bypass_drip user meta
	 *
	 * @param $items_ids array of lesson and module ids
	 * @param $course_id
	 */
	public function bulk_reset_items( $items_ids, $course_id ) {

		$this->bulk_reset_completed_lessons( $items_ids, $course_id );
		$this->bulk_reset_bypassed_items( $items_ids, $course_id );
		$this->bulk_reset_video_progress( $items_ids );

		foreach ( $items_ids as $item_id ) {
			$post = get_post( $item_id );

			if ( $post->post_type === TVA_Const::LESSON_POST_TYPE ) {
				$this->delete_logs( $item_id, $course_id );
			} elseif ( $post->post_type === TVA_Const::MODULE_POST_TYPE ) {
				$this->bulk_delete_assessments( $post, $course_id );
			}
		}
	}

	/**
	 * Reset assessment from a module
	 *
	 * @param $module
	 *
	 * @return void
	 */
	public function bulk_delete_assessments( $module, $course_id ) {
		$assessments           = TVA_Manager::get_all_module_items( $module, [
			'post_type' => TVA_Const::ASSESSMENT_POST_TYPE,
			'fields'    => 'ids',
		] );
		$submitted_assessments = tva_get_submitted_assessments();

		foreach ( $assessments as $assessment ) {
			TVA_User_Assessment::delete_user_submissions( $assessment, $this->get_id() );
			unset( $submitted_assessments[ $course_id ][ $assessment ] );
		}

		/**
		 * Remove the assessments that are not anymore from the submitted assessments
		 */
		foreach ( $submitted_assessments[ $course_id ] as $id => $value ) {
			$post = get_post( $id );
			if ( ! $post || ( $post instanceof WP_Post && $post->post_type !== TVA_Const::ASSESSMENT_POST_TYPE ) ) {
				unset( $submitted_assessments[ $course_id ][ $id ] );
			}
		}

		update_user_meta( $this->get_id(), TVA_User_Assessment::SUBMITTED_CACHE_KEY, $submitted_assessments );
	}

	/**
	 * Removes the items received as param from the 'tva_bypass_drip' user meta so they will respect the drip conditions set
	 *
	 * @param array $items_ids Items can be lessons and modules as well
	 * @param int   $course_id
	 *
	 * @return void
	 */
	public function bulk_reset_bypassed_items( $items_ids, $course_id ) {
		$bypassed_items        = $this->get_items_bypassed();
		$course_bypassed_items = $bypassed_items && $bypassed_items[ $course_id ] ? $bypassed_items[ $course_id ] : [];

		$course_bypassed_items = static::filter_items( $course_bypassed_items, $items_ids );

		$bypassed_items[ $course_id ] = $course_bypassed_items;
		$this->set_items_bypassed( $bypassed_items );
	}


	/**
	 * Removes the lessons received as param from the 'tva_learned_lessons' user meta
	 * In other words marks lessons from a course as not yet completed
	 *
	 * @param array $items_ids Items can be lessons and modules as well, but only the lessons are taken into consideration
	 * @param int   $course_id
	 *
	 * @return void
	 */
	public function bulk_reset_completed_lessons( $items_ids, $course_id ) {
		$learned_lessons        = $this->get_learned_lessons_for_student();
		$course_learned_lessons = $learned_lessons && $learned_lessons[ $course_id ] ? $learned_lessons[ $course_id ] : [];

		$course_learned_lessons = static::filter_items( $course_learned_lessons, $items_ids );

		$learned_lessons[ $course_id ] = $course_learned_lessons;
		update_user_meta( $this->get_id(), 'tva_learned_lessons', $learned_lessons );
	}

	/**
	 * Removes video progress details if the lessons are of video type
	 *
	 * @param array $items_ids
	 *
	 * @return void
	 */
	public function bulk_reset_video_progress( $items_ids ) {
		$reporting = TVE\Reporting\Logs::get_instance();

		foreach ( $items_ids as $lesson_id ) {
			$lesson = new TVA_Lesson( $lesson_id );
			if ( 'video' === get_post_meta( $lesson_id, 'tva_lesson_type', true ) ) {
				$video_id = Video::get_post_id_by_video_url( $lesson->get_video()->source );
				$reporting->delete( [
					'event_type' => [ Video_Start::key(), Video_Data::key(), Video_Completed::key() ],
					'item_id'    => $video_id,
					'post_id'    => $lesson_id,
					'user_id'    => $this->get_id(),
				] );
			}
		}
	}

	/**
	 * Filter items array
	 * items can be modules and lessons
	 *
	 * @param array $items Array of lessons and modules
	 * @param array $ids_to_remove
	 *
	 * @return array of items without the ones with the ids, received in the $ids_to_remove parameter
	 */
	public static function filter_items( $items, $ids_to_remove ) {
		$excluded_items = array_diff( array_keys( $items ), $ids_to_remove );

		return array_filter(
			$items,
			function ( $item ) use ( $excluded_items ) {
				return in_array( $item, $excluded_items );
			}, ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Delete log from database
	 */
	public function delete_logs( $lesson_id, $course_id ) {
		$post = get_post( $lesson_id );

		if ( ! isset( $post ) ) {
			return;
		}

		$learned_lessons = $this->get_learned_lessons_for_student();
		$lesson          = new TVA_Lesson( $post );
		$course          = $lesson->get_course_v2();
		$module          = $lesson->get_module();

		$this->delete_log( [ Lesson_Start::key(), Lesson_Complete::key(), Drip_Unlocked_For_User::key() ], $lesson_id );

		$excluded_lesson_ids = $course->get_excluded_lessons( true );

		if ( $lesson->is_free_for_all() ) {

			$this->delete_log( [ Free_Lesson_Complete::key() ], $lesson_id );

			$learned_lessons_ids = [];
			if ( ! empty( $learned_lessons[ $course_id ] ) ) {
				$learned_lessons_ids = array_map( 'intval', array_keys( $learned_lessons[ $course_id ] ) );
			}
			$completed_lessons = array_intersect( $excluded_lesson_ids, $learned_lessons_ids ); //what lessons have been completed
			$remained_lessons  = empty( array_diff( $excluded_lesson_ids, $completed_lessons ) );

			if ( ! $remained_lessons ) {

				$this->delete_log( [ All_Free_Lessons_Completed::key() ], $course_id );
			}
		}

		if ( $module ) {
			if ( $this->get_count_module_completed_lessons( $course_id, $module ) === 0 ) {

				$this->delete_log( [ Module_Start::key(), Drip_Unlocked_For_User::key() ], $module->ID );
			}

			if ( $this->get_count_module_completed_lessons( $course_id, $module ) !== $module->get_published_lessons_count() ) {

				$this->delete_log( [ Module_Finish::key() ], $module->ID );
			}
		}

		if ( $this->get_count_course_completed_lessons( $course_id ) === 0 ) {

			$this->delete_log( [ Course_Start::key() ], $course_id );

			$timestamps = $this->get_timestamps_for_begin_course();

			if ( ! empty( $timestamps[ $course_id ] ) ) {
				unset( $timestamps[ $course_id ] );

				update_user_meta( $this->get_id(), 'tva_course_begin_timestamp', $timestamps );
			}
		}

		if ( $this->get_count_course_completed_lessons( $course_id ) !== $course->get_published_lessons_count() ) {

			$this->delete_log( [ Course_Finish::key() ], $course_id );
		}
	}

	/**
	 * Returns an array of lesson ids that are completed from the module
	 *
	 * @param int        $course_id
	 * @param TVA_Module $module
	 *
	 * @return array
	 */
	public function get_module_completed_lessons( $course_id, $module ) {
		$learned_lessons = $this->get_learned_lessons_for_student();
		$ids             = [];

		foreach ( $module->get_published_lessons() as $lesson ) {
			$ids[] = $lesson->ID;
		}

		$learned_lessons_ids = [];
		if ( ! empty( $learned_lessons ) && ! empty ( $learned_lessons[ $course_id ] ) ) {
			$learned_lessons_ids = array_map( 'intval', array_keys( $learned_lessons[ $course_id ] ) );
		}

		return array_intersect( $ids, $learned_lessons_ids );
	}

	/**
	 * Count the number of completed lessons in a module
	 *
	 * @param int        $course_id
	 * @param TVA_Module $module
	 *
	 * @return int
	 */
	public function get_count_module_completed_lessons( $course_id, $module ) {
		return count( $this->get_module_completed_lessons( $course_id, $module ) );
	}

	/**
	 * Count the number of completed lessons in a course
	 *
	 * @param int $course_id
	 *
	 * @return int
	 */
	public function get_count_course_completed_lessons( $course_id ) {
		return count( $this->get_course_completed_lessons_for_student( $course_id ) );
	}

	/**
	 * Delete a log from database
	 *
	 * @param $event_type array
	 * @param $id         int
	 *
	 * @return void
	 */
	public function delete_log( $event_type, $id ) {
		Logs::get_instance()->delete( [
			'event_type' => $event_type,
			'item_id'    => $id,
			'user_id'    => $this->get_id(),
		] );
	}


	/**
	 * @param TVA_Course_V2 $course
	 *
	 * @return bool
	 */
	public function should_restrict_access_to_course( $course ) {

		if ( ! is_user_logged_in() ) {
			//For now we do nothing here

			return false;
		}

		if ( $course->get_status() === 'archived' ) {
			return true;
		}

		return false;
	}


	/**
	 * Gets the courses a user has access to
	 * Calculates the progress in them
	 * Prepares an array with locked lessons
	 */

	public function prepare_data() {
		$products = Product::get_items_from_cache();
		$admin_id = get_current_user_id();

		$courses_progress = $this->progress;
		$locked_status    = $this->locked_status;

		tva_access_manager()->set_tva_user( $this->_user );
		wp_set_current_user( $this->get_id() );

		/** @var Product $product */
		foreach ( $products as $product ) {

			$allowed = tva_access_manager()->set_product( $product )->check_rules();

			if ( $allowed ) {
				foreach ( $product->get_courses() as $course ) {

					if ( $this->should_restrict_access_to_course( $course ) ) {
						continue;
					}

					if ( ! $this->is_course_in_array( $course->ID ) ) {
						$this->_data['courses'][] = $course;

						$course->load_structure();

						$locked_status[ $course->get_id() ] = $this->calculate_locked_status( $product, $course );

						$courses_progress[ $course->get_id() ] = $this->calculate_progress( $course );
					}
				}
			}
		}

		$this->_data['progress']      = $courses_progress;
		$this->_data['locked_status'] = $locked_status;

		wp_set_current_user( $admin_id );
	}

	/**
	 * Sets the list of courses the user has access to
	 *
	 * @return TVA_Course_V2[] The list of courses the customer has access to
	 */
	public function get_courses() {
		if ( ! empty( $this->_data['courses'] ) ) {
			return $this->courses;
		}

		$products = Product::get_items_from_cache();
		$admin_id = get_current_user_id();

		tva_access_manager()->set_tva_user( $this->_user );
		wp_set_current_user( $this->get_id() );

		foreach ( $products as $product ) {
			$has_access = tva_access_manager()->set_product( $product )->check_rules();

			if ( $has_access ) {
				foreach ( $product->get_courses() as $course ) {
					if ( ! $this->is_course_in_array( $course->ID ) ) {
						$this->_data['courses'][] = $course;
					}
				}
			}
		}

		wp_set_current_user( $admin_id );

		return $this->_data['courses'];
	}

	/**
	 * Calculates the published courses a user has access to
	 */
	public function get_published_courses_count() {
		$products = Product::get_items_from_cache();
		$admin_id = get_current_user_id();

		$published = $this->published_courses_count;

		if ( ! empty( $this->_data['courses'] ) && is_array( $this->_data['courses'] ) ) {
			foreach ( $this->_data['courses'] as $course ) {
				if ( $course->get_status() === 'publish' ) {
					$published ++;
				}
			}
		} else {
			tva_access_manager()->set_tva_user( $this->_user );
			tva_access_manager()->set_user( $this->_user );
			wp_set_current_user( $this->get_id() );

			foreach ( $products as $product ) {

				$allowed = tva_access_manager()->set_product( $product )->check_rules();

				if ( $allowed ) {
					foreach ( $product->get_courses_from_cache() as $course ) {
						if ( ! $this->is_course_in_array( $course->ID ) ) {
							$this->_data['courses'][] = $course;

							if ( $course->get_status() === 'publish' ) {
								$published ++;
							}
						}
					}
				}
			}

			wp_set_current_user( $admin_id );
		}

		$this->_data['published_courses_count'] = $published;

		return $this->published_courses_count;
	}

	/**
	 * Calculates the progress of a course based on the learned lessons
	 *
	 * @param TVA_Course_V2 $course
	 */
	public function calculate_progress( $course ) {
		$course_progress   = '0%';
		$lessons_completed = $this->get_meta( 'tva_learned_lessons' );
		$lessons_completed = is_array( $lessons_completed ) && array_key_exists( $course->get_id(), $lessons_completed ) ? $lessons_completed[ $course->get_id() ] : [];

		if ( ! empty ( $lessons_completed ) ) {
			$completed = count( $lessons_completed );
			$total     = $course->get_published_lessons_count();

			$course_progress = Dynamic_Actions\tcb_tva_dynamic_actions()->get_progress_by_type( 'course', $completed, $total );
		}

		return array(
			'progress' => $course_progress,
			'modules'  => $this->calculate_modules_progress( $course ),
		);
	}

	/**
	 * Calculate progress in each module
	 *
	 * @param TVA_Course_V2 $course
	 */
	public function calculate_modules_progress( $course ) {
		$modules          = $course->get_published_modules();
		$modules_progress = [];

		if ( $modules ) {
			foreach ( $modules as $module ) {
				$completed = 0;
				$total     = $module->get_published_lessons_count();

				foreach ( $module->get_published_lessons() as $lesson ) {
					if ( $lesson->is_completed( $this->get_id() ) ) {
						$completed ++;
					}
				}

				$modules_progress[ $module->ID ] = Dynamic_Actions\tcb_tva_dynamic_actions()->get_progress_by_type( 'module', $completed, $total );
			}
		}

		return $modules_progress;
	}

	/**
	 * Gets the locked lessons from a course
	 *
	 * @param TVA\Product   $product
	 * @param TVA_Course_V2 $course
	 */
	private function calculate_locked_status( $product, $course ) {
		$campaign = $product->get_drip_campaign_for_course( $course );

		if ( ! $campaign instanceof Campaign ) {
			return [];
		}

		$campaign->set_customer( $this );
		$locked_status = [ 'campaign_type' => $campaign->content_type ];

		$locked_status['course_locked'] = $campaign->trigger === 'datetime' && Campaign::get_datetime( $campaign->unlock_date ) > current_datetime();

		$locked_status += $this->get_lessons_locked_status( $course, $campaign, $product );

		return $locked_status;
	}

	/**
	 * If campaign is set on module the modules should appear as locked and can be unlocked
	 * Lessons should appear as locked and can't be unlocked
	 * Free for everyone lessons should stay free, free for logged in should respect drip settings
	 *
	 * @param TVA_Course_V2 $course
	 * @param Campaign      $campaign
	 * @param Product       $product
	 */
	public function get_lessons_locked_status( $course, $campaign, $product ) {
		$locked_lessons = [];

		if ( $course instanceof TVA_Course_V2 ) {

			$modules = $course->get_published_modules();

			if ( ! empty( $modules ) ) {
				foreach ( $modules as $module ) {
					$locked_lessons[ $module->ID ]['module_locked'] = ! $campaign->should_unlock( $product->get_id(), $module->ID );

					foreach ( $module->get_published_lessons() as $lesson ) {
						$locked_lessons[ $module->ID ]['locked_lessons'][ $lesson->ID ] = ! $campaign->should_unlock_after_module( $product->get_id(), $lesson->ID );
					}
				}
			} else {
				foreach ( $course->get_published_lessons() as $lesson ) {
					$locked_lessons['locked_lessons'][ $lesson->ID ] = ! $campaign->should_unlock( $product->get_id(), $lesson->ID );
				}
			}
		}

		return $locked_lessons;
	}

	/**
	 * Get the ids of the courses the user has access to
	 */
	public function get_course_ids() {
		$course_ids = [];

		foreach ( $this->courses as $course ) {
			$course_ids[] = $course->get_id();
		}

		return $course_ids;
	}

	/**
	 * Gets the enrollment dates to courses from history table
	 */
	public function get_enrollment_dates() {
		if ( empty( $this->enrollment_dates ) ) {

			$dates = History_Table::get_instance()->get_course_enrollment_dates( [ 'user_id' => [ $this->get_id() ] ] );

			$this->_data['enrollment_dates'] = [];

			foreach ( $dates as $item ) {
				$this->_data['enrollment_dates'][ $item['course_id'] ] = date( 'd.m.Y', strtotime( $item['created'] ) );
			}
		}

		return $this->enrollment_dates;
	}

	/**
	 * Add an exception to drip for a user
	 * With this set, a user won't have to respect the drip constraints for that lesson, so it will be unlocked for him
	 *
	 * @param int  $item_id
	 * @param null $course_id
	 *
	 * @return array|null
	 */
	public function add_bypassed_item( $item_id, $course_id = null ) {
		$post = get_post( $item_id );

		if ( empty( $post ) ) {
			return null;
		} else {
			$bypassed_items = $this->get_items_bypassed();

			$bypassed_items[ $course_id ][ $item_id ] = array(
				'post_type' => $post->post_type,
				'unlocked'  => true,
			);

			$this->set_items_bypassed( $bypassed_items );

			/**
			 * Triggered when content is unlocked for a specific user
			 *
			 * @param WP_User $user     User object for which content is unlocked
			 * @param WP_Post $post     The post object that is unlocked
			 * @param WP_Term $product  The product term that the campaign belongs to, which in this case is not any,
			 *                          because it was unlocked no matter the campaign
			 */
			do_action( 'tva_drip_content_unlocked_for_specific_user', $this->get_user(), $post, null );

			return $bypassed_items;
		}
	}

	/**
	 * Gets the enrolment date, last seen and course count for a user
	 */
	public function prepare_data_for_student_list() {
		$student = History_Table::get_instance()->get_student( $this->get_id() );

		if ( ! empty( $student ) ) {
			$this->_data['courses_count'] = $student['courses_count'];
			$this->_data['enrolled']      = $student['enrolled'];
		}
	}

	/**
	 * @param int $course_id
	 *
	 * @return bool
	 */
	public function is_course_in_array( $course_id ) {
		if ( ! empty( $this->_data['courses'] ) && is_array( $this->_data['courses'] ) ) {
			foreach ( $this->_data['courses'] as $course ) {
				if ( $course->ID === $course_id ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Gets the certificate numbers and urls to courses
	 */
	public function get_course_certificates() {
		$courses = is_array( $this->courses ) ? $this->courses : array();

		$this->_data['courses_certificates'] = [];

		foreach ( $courses as $course ) {
			if ( $course->has_certificate() ) {
				$certificate        = $course->get_certificate();
				$meta_key           = static::get_certificate_meta_key( $certificate->ID );
				$certificate_number = get_user_meta( $this->get_id(), $meta_key, true );
				$certificate_url    = $certificate->get_public_url( $this->get_id() );
				if ( empty( $certificate_number ) && $this->calculate_progress( $course )['progress'] === '100%' ) {
					$certificate_number = $certificate->generate_code( $this->get_id() );

					update_user_meta( $this->get_id(), $meta_key, $certificate_number );

					if ( $certificate_number ) {
						update_option(
							'tva_certificate_' . $certificate_number,
							$certificate->get_data( $this->get_id() ),
							false
						);
					}
				}

				$this->_data['courses_certificates'][ $course->id ] = array(
					'number' => $certificate_number,
					'url'    => $certificate_url,
				);
			}
		}

		return $this->_data['courses_certificates'];
	}
}
