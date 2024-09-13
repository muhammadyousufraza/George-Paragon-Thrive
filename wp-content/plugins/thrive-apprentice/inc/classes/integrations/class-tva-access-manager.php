<?php
/**
 * Created by PhpStorm.
 * User: dan bilauca
 * Date: 22-Apr-19
 * Time: 01:46 PM
 */

use Thrive\Theme\Integrations\WooCommerce\Helpers;
use TVA\Access\Expiry\Base as Expiry_Base;
use TVA\Drip\Campaign;
use TVA\Product;
use TVD\Content_Sets\Set;
use TVD\Content_Sets\Utils;

/**
 * Class TVA_Access_Manager
 * - for content
 */
class TVA_Access_Manager {

	/**
	 * @var WP_Post
	 */
	private $_post;

	/**
	 * @var Product
	 */
	private $product;

	/**
	 * @var WP_User
	 */
	private $_user;

	/**
	 * @var null|bool
	 */
	private $_access_allowed = null;

	/**
	 * Logged in WP_User
	 *
	 * @var TVA_User
	 */
	private $_tva_user;

	/**
	 * @var TVA_Integrations_Manager
	 */
	protected $_integration_manager;

	/**
	 * If a rule of any integration is allowed then save this integration in here
	 *
	 * @var TVA_Integration
	 */
	protected $_allowed_integration;

	/**
	 * Cache product from set on request
	 * The cache form is [CONTENT_TYPE.'_'.CONTENT_ID] = VALUE
	 *
	 * @var array
	 */
	public static $ACCESS_TO_OBJECT_CACHE = array();

	/**
	 * Holds the products instances the active user has haccess to
	 *
	 * @var array
	 */
	public static $ACCESS_TO_PRODUCTS_CACHE = array();

	/**
	 * Cache locked on current request
	 * The cache form is [CONTENT_TYPE.'_'.CONTENT_ID] = VALUE
	 *
	 * @var array
	 */
	public static $OBJECT_LOCKED_CACHE = array();

	/**
	 * TVA_Access_Manager constructor.
	 *
	 * @param TVA_Integrations_Manager $integration_manager
	 *
	 * @throws Exception
	 */
	public function __construct( $integration_manager ) {

		if ( false === $integration_manager instanceof TVA_Integrations_Manager ) {
			throw new Exception( 'Invalid integration manager provided' );
		}

		$this->_integration_manager = $integration_manager;

		$this->hooks();
	}

	public function hooks() {

		/**
		 * Initialize this at this hook cos now we have the global $post
		 */
		add_action( 'wp', array( $this, 'init' ) );

		/**
		 * Now it's time to decide if we render the content
		 * or let other plugins to do their logic: redirect
		 *
		 * UPDATE: since we have redirection settings directly at course level, we need to take over
		 * the template_redirect hook and apply the redirections setup by the user.
		 * Changing this to have priority "0" to be executed before the equivalent hooks from membership plugins.
		 */
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 0 );

		/**
		 * Outputs the restrict content view if the current object is protected
		 */
		add_filter( 'the_content', array( $this, 'restrict_content' ), PHP_INT_MAX );

		/**
		 * Hooks into theme section HTML and modifies it if the post has restricted access
		 */
		add_filter( 'thrive_theme_section_html', [ $this, 'restrict_theme_section_content' ], PHP_INT_MAX, 2 );

		/**
		 * Used to hide the theme elements markup in case the content is restricted
		 */
		add_filter( 'thrive_theme_dynamic_video', array( $this, 'restrict_theme_element_content' ) );
		add_filter( 'thrive_theme_dynamic_audio', array( $this, 'restrict_theme_element_content' ) );
		add_filter( 'thrive_theme_comments_content', array( $this, 'restrict_theme_element_content' ) );
	}

	/**
	 * Tries to read the current post from which is determined the current course
	 */
	public function init() {

		global $post;

		if ( $post instanceof WP_Post ) {
			$this->_post = $post;
		}
	}

	/**
	 * @return Product
	 */
	public function get_product() {

		if ( false === $this->product instanceof Product ) {
			$product = Product::get_from_set();

			if ( $product instanceof Product ) {
				$this->product = $product;
			}
		}

		return $this->product;
	}

	/**
	 * @return null|Product
	 */
	public function get_current_product() {
		return $this->product;
	}

	/**
	 * Set a course, this time Product
	 * - used to check if a user has access to product
	 *
	 * @param Product|WP_Term $product
	 *
	 * @return $this
	 */
	public function set_product( $product ) {

		if ( $product instanceof WP_Term ) {
			$this->product = new Product( $product );
		} else if ( $product instanceof Product ) {
			$this->product = $product;
		}

		return $this;
	}

	/**
	 * Returns the logged in user returned by _init_user()
	 * or the user set calling the set_user()
	 *
	 * @return WP_User
	 */
	public function get_logged_in_user() {

		if ( false === $this->_user instanceof WP_User ) {
			$this->_init_user();
		}

		return $this->_user;
	}

	/**
	 * If a user is logged in gets its date and set it for later use
	 */
	private function _init_user() {

		$user_id = get_current_user_id();

		if ( $user_id ) {
			$this->_user = get_userdata( $user_id );
		}
	}

	/**
	 * Set user for later use
	 * - thi actually allows to set a outer user rather than a logged in user
	 *
	 * @param WP_User $user
	 *
	 * @return TVA_Access_Manager
	 */
	public function set_user( $user ) {

		if ( true === $user instanceof WP_User ) {
			$this->_user = $user;
		}

		return $this;
	}

	/**
	 * In case the user doens't have access to the content we need to hide come elements used in the template
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	public function restrict_theme_element_content( $html = '' ) {

		if ( ! $this->has_access() ) {
			$html = '';
		}

		return $html;
	}

	/**
	 * If current user does not have access to current course then
	 * let integration manager apply its logic
	 */
	public function template_redirect() {

		if ( true === is_search() || get_post_type() === TVA_Course_Completed::POST_TYPE ) {
			/**
			 * I am not sure if this should be here:
			 * I pasted this code because a conflict I wad with dev  SUPP-12916
			 */
			return;
		}

		/**
		 * If a product has been detected and the user doesn't have access we apply the redirect
		 */
		if ( ! $this->has_access() ) {
			/**
			 * If user doesn't get access, but he would have access from freemium if he would log in
			 * then redirect him to the page set in site settings
			 */
			if ( $this->has_access_if_logged() ) {
				tva_access_restriction_settings()->template_redirect();

			} else if ( $this->is_object_locked( get_queried_object() ) && $this->has_access_to_object( get_queried_object() ) ) {
				/**
				 * If a product has been detected, the user has access, but the content is locked
				 */
				$this->get_product()->get_access_restrictions()->template_redirect( 'locked' );
			} else {
				/**
				 * If a product has been detected and the user doesn't have access we apply the redirect
				 */
				$product = $this->get_product();
				if ( $product instanceof Product ) {
					$product->get_access_restrictions()->template_redirect();
				}
			}
		}
	}

	/**
	 * @return mixed|string|void
	 */
	public function get_login_redirect_url() {

		$login_url = get_home_url();

		if ( false === $this->has_access() && $this->get_product() instanceof Product && Utils::is_context_supported() ) {
			$queried_object_id = get_queried_object_id();
			if ( get_queried_object() instanceof WP_Post ) {
				$login_url = get_permalink( $queried_object_id );
			} else {
				$login_url = get_term_link( $queried_object_id );
			}

			$login_url = apply_filters( 'tva_access_manager_get_login_redirect_url', $login_url );
		}

		return $login_url;
	}

	/**
	 * For WOO only, if the woo post is protected by a product we show the placeholder instead of the content
	 *
	 * @param $section_html
	 * @param $theme_section
	 *
	 * @return string
	 */
	public function restrict_theme_section_content( $section_html, $theme_section ) {

		if ( ! is_editor_page_raw( true ) && Thrive_Theme::is_active() && Helpers::is_woo_template() && $theme_section->type() === 'content' && $this->should_restrict_content() ) {

			/**
			 * Backwards compatible for theme
			 */
			if ( ! is_callable( [ $theme_section, 'class_attr' ] ) || ! is_callable( [ $theme_section, 'generate_attributes' ] ) || ! is_callable( [ $theme_section, 'get_background' ] ) ) {
				return $section_html;
			}

			$section_html = TCB_Utils::wrap_content(
				$theme_section->get_background() . $this->restrict_content( $section_html ),
				'div',
				'theme-' . $theme_section->type() . '-section',
				$theme_section->class_attr( true ),
				$theme_section->generate_attributes()
			);
		}

		return $section_html;
	}

	/**
	 * Returns true if the system should restrict the user in seeing the content
	 *
	 * @return bool
	 */
	public function should_restrict_content() {
		return ! $this->has_access() || ( $this->has_access_if_logged() && empty( $this->get_product() ) );
	}

	/**
	 * If the user doesn't have access, shows the restricted access view
	 *
	 * @param string $content Content of the current post.
	 */
	public function restrict_content( $content ) {

		$product = $this->get_product();

		if ( get_post_type() === 'tcb_lightbox' ) {
			/**
			 * Special case for Thrive Lightbox
			 * Solved the issue when Thrive Lightbox is set to open when button form Restricted Content is pressed
			 */
			$product = null;
			$sets    = Set::get_for_object( get_post(), get_the_ID() );

			if ( is_array( $sets ) && count( $sets ) > 0 ) {
				$product = Product::get_from_set( $sets );
			}
		}

		/**
		 * If a course is free, but the content is free for only logged in users
		 * then display the content set in site settings
		 */
		if ( $this->has_access_if_logged() && empty( $product ) ) {
			$content = tva_access_restriction_settings()->output_restricted_access( false );
		} else if ( ! $this->has_access() && $product instanceof Product ) {
			if ( $this->is_object_locked( get_queried_object() ) && $this->has_access_to_object( get_queried_object() ) ) {
				//content is locked
				$content = $product->get_access_restrictions()->output_restricted_access( false, 'locked' );
			} else {
				//user doesn't have access
				$content = $product->get_access_restrictions()->output_restricted_access( false, 'not_purchased' );
			}
		}

		/**
		 * Modify the content that is returned after the restrict content logic is applied
		 *
		 * @param string           $content
		 * @param TVA_Product|null $product
		 */
		return apply_filters( 'tva_access_restrict_content', $content, $product );
	}

	/**
	 * Public method to decide if logged in user has access for current request
	 *
	 * @return null|bool
	 */
	public function has_access() {

		if ( $this->_access_allowed === null ) {

			/**
			 * If user is admin, access is allowed
			 */
			if ( TVA_Product::has_access() ) {
				$this->_access_allowed = true;
			} else {
				$allowed = $this->has_access_to_object( get_queried_object() );
				/**
				 * Allows exceptions to be added here fon non admin users
				 *
				 * Ex: the course overview page should always be accessible
				 *
				 * @param bool $has_access
				 */
				$this->_access_allowed = apply_filters( 'tva_access_manager_allow_access', $allowed );
			}
		}

		return $this->_access_allowed;
	}

	/**
	 * @param null|WP_Post $object
	 *
	 * @return bool
	 */
	public function has_freemium_access( $object = null ) {

		if ( empty( $object ) ) {
			$object = get_queried_object();
		}

		/**
		 * For the moment we support only posts to be freemium
		 */
		if ( $object instanceof WP_Post ) {
			$tva_post = TVA_Post::factory( $object );

			return $tva_post->is_free_for_all() || ( $tva_post->is_free_for_logged() && is_user_logged_in() );
		}

		return false;
	}

	/**
	 * @param null|WP_Post $post_or_term
	 *
	 * @return bool
	 */
	public function has_access_based_on_status( $post_or_term = null ) {
		if ( empty( $post_or_term ) ) {
			$post_or_term = get_queried_object();
		}

		if ( $post_or_term instanceof WP_Post ) {
			$terms = wp_get_object_terms( $post_or_term->ID, TVA_Const::COURSE_TAXONOMY );

			if ( ! empty( $terms ) && is_array( $terms ) ) {
				$course = new TVA_Course_V2( $terms[0] );

				if ( $course->is_archived() && ! in_array( $course->get_id(), tva_customer()->get_learned_courses() ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks if user should have access to the content but is not logged in
	 *
	 * @param WP_Post|WP_Term|null $post_or_term
	 *
	 * @return bool
	 */
	public function has_access_if_logged( $post_or_term = null ) {

		if ( empty( $post_or_term ) ) {
			$post_or_term = get_queried_object();
		}

		return $post_or_term instanceof WP_Post && TVA_Post::factory( $post_or_term )->is_free_for_logged() && ! is_user_logged_in();
	}

	/**
	 * Checks all TVA_Integrations_Manager's rules for one which applies
	 * - rules for the current course
	 *
	 * @return bool
	 */
	public function check_rules() {
		$product = $this->get_product();
		$allow   = false;

		if ( Expiry_Base::access_has_expired( get_current_user_id(), $product ) ) {
			/**
			 * Access expiry check
			 * If access has expired, return false
			 *
			 * TODO: maybe improve this a bit by checking if this logic is called without the current user
			 */
			return $allow;
		}

		$this->_allowed_integration = null;
		$rules                      = $this->_integration_manager->get_rules( $product );

		foreach ( $rules as $rule ) {
			$integration = $this->_integration_manager->get_integration( $rule['integration'] );

			if ( false === $integration instanceof TVA_Integration || $integration instanceof TVA_Custom_Payment_Integration ) {
				continue;
			}
			$integration->set_post( $this->_post );

			$allow = $integration->is_rule_applied( $rule );

			if ( true === $allow ) {
				$this->_allowed_integration = $integration;
				break;
			}
		}

		if ( false === $allow ) {
			foreach ( [ 'thrivecart', 'manual', 'course_bundle' ] as $name ) {
				$integration = $this->_integration_manager->get_integration( $name );
				if ( false === $integration instanceof TVA_Integration ) {
					continue;
				}
				$allow = $integration->is_rule_applied( array() );
				if ( $allow ) {
					$this->_allowed_integration = $integration;
					break;
				}
			}
		}

		return $allow;
	}

	/**
	 * Gets a TVA_User if was set
	 * - contains SendOwl orders
	 *
	 * @return TVA_User|TVA_Customer|null
	 */
	public function get_tva_user() {

		if ( false === $this->_tva_user instanceof TVA_User ) {
			$this->_init_tva_user();
		}

		return $this->_tva_user;
	}

	/**
	 * Set a user
	 *
	 * @param WP_User|TVA_User $user
	 *
	 * @retun TVA_Access_Manager
	 */
	public function set_tva_user( $user ) {

		if ( false === $user instanceof WP_User && false === $user instanceof TVA_User ) {
			return $this;
		}

		if ( $user instanceof WP_User ) {
			$user = new TVA_User( $user->ID );
		}

		$this->_tva_user = $user;

		return $this;
	}

	/**
	 * If there is a logged in user then init a TVA_User for it
	 */
	private function _init_tva_user() {

		$user = $this->get_logged_in_user();

		if ( $user instanceof WP_User ) {
			$this->_tva_user = new TVA_User( $user->ID );
		}
	}

	/**
	 * @param WP_Post|WP_Term $post_or_term
	 */
	public function has_access_to_object( $post_or_term ) {

		list( $content_type, $id ) = Utils::get_post_or_term_parts( $post_or_term );

		$key = $content_type . '_' . $id;

		if ( isset( static::$ACCESS_TO_OBJECT_CACHE[ $key ] ) ) {
			return static::$ACCESS_TO_OBJECT_CACHE[ $key ];
		}

		$has_access = true;

		if ( TVA_Product::has_access() || $this->has_freemium_access( $post_or_term ) ) {
			$has_access = true;
		} else {
			$sets = empty( $id ) ? Set::get_for_non_object() : Set::get_for_object( $post_or_term, $id );

			$product = Product::get_from_set( $sets, [], $post_or_term );

			if ( $product instanceof Product ) {
				$this->set_product( $product );

				$has_access = $this->check_rules();

				$this->product = null;
			}

			/**
			 * Freemium is set on content level so it should be the last check if user gets access or not
			 */
			if ( $has_access && $this->has_access_if_logged( $post_or_term ) ) {
				$has_access = false;
			}

			/**
			 * Checks if the system should allow access based on post status
			 */
			if ( $has_access && ! $this->has_access_based_on_status( $post_or_term ) ) {
				$has_access = false;
			}
		}

		static::$ACCESS_TO_OBJECT_CACHE[ $key ] = $has_access;

		return static::$ACCESS_TO_OBJECT_CACHE[ $key ];
	}

	/**
	 * Returns all products with access for the active user
	 *
	 * @return Product[]
	 */
	public function get_products_with_access() {

		if ( ! empty( static::$ACCESS_TO_PRODUCTS_CACHE ) ) {
			return static::$ACCESS_TO_PRODUCTS_CACHE;
		}

		static::$ACCESS_TO_PRODUCTS_CACHE = [];

		$original_product = $this->get_current_product();

		foreach ( Product::get_items( [ 'status' => 'publish' ] ) as $product ) {
			$this->set_product( $product );
			if ( $this->check_rules() ) {
				static::$ACCESS_TO_PRODUCTS_CACHE[] = $product;
			}
		}

		$this->set_product( $original_product );

		return static::$ACCESS_TO_PRODUCTS_CACHE;
	}

	/**
	 * Returns true if the object is locked
	 * An object is locked if there exists a campaign associated with that object and the campaign triggers are invalid
	 *
	 * @param WP_Post|WP_Term $post_or_term
	 *
	 * @return bool
	 */
	public function is_object_locked( $post_or_term ) {

		list( $content_type, $id ) = Utils::get_post_or_term_parts( $post_or_term );

		$key = $content_type . '_' . $id;

		if ( isset( static::$OBJECT_LOCKED_CACHE[ $key ] ) ) {
			return static::$OBJECT_LOCKED_CACHE[ $key ];
		}

		$is_locked = false;

		if ( is_user_logged_in() && ! TVA_Product::has_access() ) {
			/**
			 * For now we support the locked feature only for courses
			 */
			$course_id = 0;

			if ( $post_or_term instanceof WP_Term && $post_or_term->taxonomy === TVA_Const::COURSE_TAXONOMY ) {
				$course_id = $post_or_term->term_id;
			} elseif ( $post_or_term instanceof WP_Post ) {
				$terms = get_the_terms( $post_or_term, TVA_Const::COURSE_TAXONOMY );
				if ( ! empty( $terms ) ) {
					$course_id = $terms[0]->term_id;
				}
			}

			if ( ! empty( $course_id ) ) {
				$options  = array( 'return_all_bought' => true );
				$products = Product::get_from_set( Set::get_for_object( $post_or_term, $id ), $options, $post_or_term );

				if ( $products instanceof Product ) {
					$campaign = $products->get_drip_campaign_for_course( $course_id );
					if ( $campaign instanceof Campaign && ! $campaign->should_unlock( $products->get_id(), $id ) ) {
						$is_locked = true;
					}
				} else if ( is_array( $products ) ) {
					$is_locked = true;
					foreach ( $products as $product ) {
						$campaign = $product->get_drip_campaign_for_course( $course_id );
						if ( empty( $campaign ) || ( $campaign instanceof Campaign && $campaign->should_unlock( $product->get_id(), $id ) ) ) {
							$is_locked = false;
							break;
						}
					}
				}
			}
		}

		static::$OBJECT_LOCKED_CACHE[ $key ] = $is_locked;

		return static::$OBJECT_LOCKED_CACHE[ $key ];
	}

	/**
	 * Returns first integration from rules set which allowed user access
	 *
	 * @return TVA_Integration|null
	 */
	public function get_allowed_integration() {
		return $this->_allowed_integration;
	}
}

