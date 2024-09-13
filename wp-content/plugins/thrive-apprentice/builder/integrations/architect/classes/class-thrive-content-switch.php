<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Thrive_Content_Switch
 */
class Thrive_Content_Switch {

	/**
	 * Use general singleton methods
	 */
	use Thrive_Singleton;

	/* template types for the current id */
	private $primary;
	private $secondary;


	/**
	 * @var mixed|string|null
	 */
	private $search;
	/**
	 * @var mixed|null
	 */
	private $number_of_items;

	/* regular names and 'pretty' names for the custom archives */
	const DEFAULT_ARCHIVES
		= [
			'category' => 'Categories',
			'post_tag' => 'Tags',
			'author'   => 'Authors',
			'date'     => 'Dates',
		];

	/**
	 * Thrive_Content_Switch constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * Registers the route for getting new content.
	 */
	public function register_routes() {
		register_rest_route( TTB_REST_NAMESPACE, '/content', [
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'template_content' ],
				'permission_callback' => [ $this, 'route_permission' ],
			],
		] );
	}

	/**
	 * Localize data for the current template.
	 *
	 * @return array
	 */
	public function get_localized_data() {
		$site_url = get_post_type_archive_link( 'post' );

		$data = [
			'data'             => $this->template_content(),
			'items_to_load'    => CONTENT_SWITCH_ITEMS_TO_LOAD,
			'content_name'     => $this->get_content_name(),
			'site_url'         => $this->build_edit_url( $site_url ),
			'site_url_preview' => $this->build_preview_url( $site_url ),
		];

		/* add data specific to the archive templates only when we're on an archive template */
		if ( $this->primary === THRIVE_ARCHIVE_TEMPLATE ) {
			$data['custom_archive'] = [
				'names'  => $this::DEFAULT_ARCHIVES,
				'counts' => $this->get_custom_archive_counts(),
			];
		}

		return $data;
	}

	/**
	 * @param WP_REST_Request|null $request
	 */
	private function prepare_params( $request = null ) {

		$this->search = '';

		if ( ! is_null( $request ) ) {
			$this->search          = $request->get_param( 'search' );
			$this->number_of_items = $request->get_param( 'number_of_items' );

			$this->primary   = $request->get_param( THRIVE_PRIMARY_TEMPLATE );
			$this->secondary = $request->get_param( THRIVE_SECONDARY_TEMPLATE );

			$template_id = $request->get_param( 'template_id' );
			thrive_template( $template_id );

		} else {
			$thrive_template = thrive_template();
			$this->primary   = $thrive_template->meta( THRIVE_PRIMARY_TEMPLATE );
			$this->secondary = $thrive_template->meta( THRIVE_SECONDARY_TEMPLATE );
		}
	}

	/**
	 * Dynamically call the functions according to the primary template type. ( get_content_singular(), get_content_archive(), etc.)
	 *
	 * @param WP_REST_Request|null $request
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function template_content( $request = null ) {
		$this->prepare_params( $request );

		/* build the function name, then call it */
		$fn = 'get_content_' . $this->primary;

		$content = [];
		if ( method_exists( $this, $fn ) ) {
			$content = $this->$fn();
		}

		if ( $request !== null ) {
			$content = new WP_REST_Response( $content, 200 );
		}

		return $content;
	}

	/*
	 * For the search template, content = new search url
	 *
	 * @return array
	 */
	private function get_content_search() {
		$search_arg = [ 's' => $this->search ];

		$url         = home_url();
		$preview_url = $this->build_preview_url( $url, $search_arg );

		$url = $this->build_edit_url( $url, $search_arg );
		$url = empty( $url ) || is_wp_error( $url ) ? '' : $url;

		return [
			'url'         => $url,
			'preview_url' => $preview_url,
		];
	}

	/**
	 * Get content for singular.
	 *
	 * @return array
	 */
	private function get_content_singular() {
		$post_type = empty( $this->secondary ) ? THRIVE_POST_TEMPLATE : $this->secondary;

		$args = [
			'numberposts' => CONTENT_SWITCH_ITEMS_TO_LOAD,
			'post_status' => [ 'draft', 'publish' ],
			'post_type'   => $post_type,
			/* make sure we don't return landing pages */
			'meta_query'  => Thrive_Utils::meta_query_no_landing_pages(),
		];

		/* if we're on a post template, only load posts for the current format */
		if ( $post_type === THRIVE_POST_TEMPLATE ) {
			$args['tax_query'] = Thrive_Utils::get_post_format_tax_query( thrive_template()->meta( 'format' ) );
		}

		if ( ! empty( $this->search ) ) {
			$args['s'] = $this->search;
		}

		if ( ! empty( $this->number_of_items ) ) {
			$args['offset'] = $this->number_of_items;
		}

		/* If a page is set as Posts page from reading settings it shouldn't appear in the content switch options */
		$page_for_posts = (int) get_option( 'page_for_posts' );
		if ( 'page' === get_option( 'show_on_front' ) && $page_for_posts ) {
			$args['exclude'] = [ $page_for_posts ];
		}

		$content = [];

		$args = Thrive_Utils::filter_default_get_posts_args( $args, 'content_switch' );

		foreach ( get_posts( $args ) as $post ) {
			$url      = get_permalink( $post );
			$edit_url = $this->build_edit_url( $url );

			$content [] = [
				'id'           => $post->ID,
				'title'        => $post->post_title,
				'url'          => empty( $edit_url ) || is_wp_error( $edit_url ) ? '' : $edit_url,
				'preview_url'  => $this->build_preview_url( $url ),
				'tar_edit_url' => tcb_get_editor_url( $post->ID ),
			];
		}

		/**
		 * Filter the list of items that are rendered in the `Content Switch` control
		 *
		 * @param array $content list of items
		 * @param array $args    list of prepared arguments for `get_posts()`
		 */
		return apply_filters( 'thrive_theme_content_switch_items', $content, $args );
	}

	/**
	 * Dynamically call the archive functions according to the secondary template type. ( get_archive_author(), get_archive_category(), etc.)
	 *
	 * @return array
	 */
	private function get_content_archive() {
		$content = [];

		/* if we don't have a secondary template set, then we're on the base archive template */
		if ( empty( $this->secondary ) ) {
			/* for the default archive, include every custom archive type */
			foreach ( $this::DEFAULT_ARCHIVES as $key => $value ) {
				$archive_fn = 'get_archive_' . $key;

				if ( method_exists( $this, $archive_fn ) ) {
					$content[ $key ] = $this->$archive_fn();
				}
			}
		} else {
			$archive_fn = 'get_archive_' . $this->secondary;

			/* if the method exists and the secondary template is valid */
			if ( method_exists( $this, $archive_fn ) && in_array( $this->secondary, array_keys( $this::DEFAULT_ARCHIVES ) ) ) {
				$content = $this->$archive_fn();
			}
		}

		/**
		 * Allow other plugins that uses ThemeBuilder logic to hook here and return archive pieces of content.
		 * Used in Thrive Apprentice
		 */
		$content = apply_filters( 'thrive_theme_get_content_archive', $content, $this->secondary );

		return $content;
	}

	/**
	 * @return array
	 */
	private function get_archive_author() {
		$content = [];

		$args = [
			'orderby' => 'id',
			'order'   => 'ASC',
			'number'  => CONTENT_SWITCH_ITEMS_TO_LOAD,
			'echo '   => false,
			'html'    => false,
		];
		if ( ! empty( $this->search ) ) {
			$args['search'] = $this->search;
		}
		if ( ! empty( $this->number_of_items ) ) {
			$args['offset'] = $this->number_of_items;
		}

		$users = get_users( $args );

		foreach ( $users as $user ) {
			$url         = get_author_posts_url( $user->ID );
			$preview_url = $this->build_preview_url( $url );
			$url         = $this->build_edit_url( $url );

			$content [] = [
				'id'           => $user->ID,
				'title'        => $user->display_name,
				'url'          => $url,
				'preview_url'  => $preview_url,
				'count'        => (int) count_user_posts( $user->ID ),
				'has_template' => $this->template_exists( $this->primary, 'author', $user->ID ),
			];
		}

		return $content;
	}

	/**
	 * @return array
	 */
	private function get_archive_post_tag() {
		$content = [];

		$args = [
			'taxonomy'   => 'post_tag',
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => CONTENT_SWITCH_ITEMS_TO_LOAD,
		];

		if ( ! empty( $this->search ) ) {
			$args['search'] = $this->search;
		}

		if ( ! empty( $this->number_of_items ) ) {
			$args['offset'] = $this->number_of_items;
		}

		$tags = get_terms( $args );

		foreach ( $tags as $tag ) {

			$url         = get_term_link( $tag );
			$preview_url = $this->build_preview_url( $url );
			$url         = $this->build_edit_url( $url );

			$content [] = [
				'id'           => $tag->term_id,
				'title'        => $tag->name,
				'url'          => $url,
				'preview_url'  => $preview_url,
				'count'        => $tag->count,
				'has_template' => $this->template_exists( $this->primary, 'post_tag', $tag->term_id ),
			];
		}

		return $content;
	}

	/**
	 * @return array
	 */
	private function get_archive_category() {
		$content = [];

		$args = [
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => CONTENT_SWITCH_ITEMS_TO_LOAD,
		];

		if ( ! empty( $this->search ) ) {
			$args['search'] = $this->search;
		}

		if ( ! empty( $this->number_of_items ) ) {
			$args['offset'] = $this->number_of_items;
		}

		$categories = get_terms( $args );

		foreach ( $categories as $category ) {
			$url         = get_term_link( $category );
			$preview_url = $this->build_preview_url( $url );
			$url         = $this->build_edit_url( $url );

			$content [] = [
				'id'           => $category->term_id,
				'title'        => $category->name,
				'url'          => $url,
				'preview_url'  => $preview_url,
				'count'        => $category->count,
				'has_template' => $this->template_exists( $this->primary, 'category', $category->term_id ),
			];
		}

		return $content;
	}

	/**
	 * @return array
	 */
	private function get_archive_date() {
		$content = [];

		$last_x_months = CONTENT_SWITCH_ITEMS_TO_LOAD;

		/* increase amount of months returned (in case of load more) */
		$offset = ! empty( $this->number_of_items ) ? $this->number_of_items : 0;

		/* display the last x months with posts (optionally starting from $offset) */
		$months_years = Thrive_Utils::get_the_last_x_months( $last_x_months, $offset );

		foreach ( $months_years as $key => $value ) {
			$year  = $value['year'];
			$month = $value['month'];

			/* count the number of posts for this month and year */
			$args = [
				'posts_per_page' => - 1,
				'post_type'      => THRIVE_POST_TEMPLATE,
				'post_status'    => 'publish',
				'year'           => $year,
				'monthnum'       => $month,
			];

			$posts = query_posts( $args );

			/* build url */
			$url = get_month_link( $year, $month );

			$content [] = [
				'title'        => date( 'F Y', strtotime( $year . '-' . $month ) ),
				'plain_url'    => $url,
				'url'          => $this->build_edit_url( $url ),
				'preview_url'  => $this->build_preview_url( $url ),
				'count'        => count( $posts ),
				'has_template' => false,
			];
		}

		return $content;
	}

	/**
	 * Get the number of each custom archive type (number of tags, number of categories).
	 *
	 * @return array
	 */
	private function get_custom_archive_counts() {

		/* get user count statistics */
		$user_stats = count_users();
		/* get total user count */
		$user_count = $user_stats['total_users'];

		/* get date of first post */
		$args = [
			'numberposts' => 1,
			'post_status' => 'publish',
			'order'       => 'ASC',
		];

		$posts              = get_posts( $args );
		$date_of_first_post = $posts[0]->post_date;

		/* count how many months are since the first post was published */
		$months_since_first_post = ( time() - strtotime( $date_of_first_post ) ) / ( 60 * 60 * 24 * 30 );

		$counts = [];
		foreach ( self::DEFAULT_ARCHIVES as $key => $value ) {
			switch ( $key ) {
				case 'category':
				case 'post_tag':
					$count = (int) wp_count_terms( $key );
					break;
				case 'author':
					$count = $user_count;
					break;
				case 'data':
					$count = (int) $months_since_first_post;
					break;
				default:
					$count = 0;
					break;
			}
			$counts[ $key ] = [
				'count'        => $count,
				'has_template' => $this->template_exists( $this->primary, $key ),
			];
		}

		return $counts;
	}

	/**
	 * Check if a template exists for this specific post / archive
	 *
	 * @param string $primary
	 * @param string $secondary
	 * @param string $variable
	 *
	 * @return bool
	 */
	public function template_exists( $primary = '', $secondary = '', $variable = '' ) {
		if ( empty( $this->templates ) ) {
			$args = [
				'posts_per_page' => - 1,
				'post_type'      => THRIVE_TEMPLATE,
				'tax_query'      => [ thrive_skin()->build_skin_query_params() ],
				'order'          => 'ASC',
				'fields'         => 'ids',
				'meta_query'     => [
					[
						'key'   => 'default',
						'value' => '1',
					],
					[
						'key'   => THRIVE_PRIMARY_TEMPLATE,
						'value' => $primary,
					],
					[
						'key'     => THRIVE_SECONDARY_TEMPLATE,
						'compare' => 'EXISTS',
					],
					[
						'key'     => THRIVE_VARIABLE_TEMPLATE,
						'compare' => 'EXISTS',
					],
				],
			];

			$this->templates = get_posts( $args );
		}

		$found = false;
		foreach ( $this->templates as $template_id ) {
			$thrive_template = new Thrive_Template( $template_id );
			if ( $thrive_template->get_secondary() === $secondary && $thrive_template->get_variable() === (string) ( $variable ) ) {
				$found = true;
				break;
			}
		}

		return $found;
	}

	/**
	 * Build query URL
	 *
	 * @param       $url
	 * @param array $args
	 *
	 * @return string
	 */
	public function build_edit_url( $url, $args = [] ) {
		$args[ TVE_EDITOR_FLAG ]   = 'true';
		$args[ THRIVE_THEME_FLAG ] = thrive_template()->ID;
		$args[ TVE_FRAME_FLAG ]    = wp_create_nonce( TVE_FRAME_FLAG );

		/**
		 * Allow other functionality to be injected here.
		 * Used in TA Visual Builder
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'thrive_theme_switch_content_build_edit_url', $args );

		return add_query_arg( $args, $url );
	}

	/**
	 * This is done in order to update the Preview option inside the editor.
	 * The preview url needs the template id and '_preview=true';
	 *
	 * @param       $url
	 * @param array $args
	 *
	 * @return string
	 */
	public function build_preview_url( $url, $args = [] ) {
		$args[ THRIVE_THEME_FLAG ] = thrive_template()->ID;
		/* add _preview = true */
		$args[ THRIVE_PREVIEW_FLAG ] = 'true';

		/**
		 * Allow other functionality to be injected here.
		 * Used in TA Visual Builder
		 *
		 * @param array $args
		 */
		$args = apply_filters( 'thrive_theme_switch_content_build_preview_url', $args );

		return add_query_arg( $args, $url );
	}

	/**
	 * Return the templates after loading template information from cookies. If nothing is found, the templates are returned in the same state they were found.
	 *
	 * @param Thrive_Template $template
	 *
	 * @return array|mixed|object
	 */
	public function get_existing_data( $template ) {
		$primary_template   = $template->meta( THRIVE_PRIMARY_TEMPLATE );
		$secondary_template = $template->meta( THRIVE_SECONDARY_TEMPLATE );
		$variable_template  = $template->meta( THRIVE_VARIABLE_TEMPLATE );

		/* get the cookie data */
		$cookie = $this->get_cookie_data( $template, $primary_template, $secondary_template, $variable_template );

		if ( ! empty( $cookie ) ) {
			/* set the secondary and variable template from the URL */
			if ( isset( $cookie[ THRIVE_SECONDARY_TEMPLATE ] ) && ( empty( $secondary_template ) || $cookie[ THRIVE_SECONDARY_TEMPLATE ] === Thrive_Demo_Content::POST_TYPE ) ) {
				$secondary_template = $cookie[ THRIVE_SECONDARY_TEMPLATE ];
			}

			if ( isset( $cookie[ THRIVE_VARIABLE_TEMPLATE ] ) && empty( $variable_template ) ) {
				$variable_template = $cookie[ THRIVE_VARIABLE_TEMPLATE ];
			}

			/* for archive list, set the primary template to 'archive', but only if we're not displaying demo content data */
			if ( $secondary_template !== Thrive_Demo_Content::POST_TYPE && ! $template->is_singular() && ! $template->is_search() ) {
				$primary_template = THRIVE_ARCHIVE_TEMPLATE;
			}

			if ( $template->is_search() ) {
				$primary_template = THRIVE_SEARCH_TEMPLATE;
			}
		}

		return [ $primary_template, $secondary_template, $variable_template ];
	}

	/**
	 * Check if we should load the content switch data, and if there is data to load.
	 * Return the data if something is found.
	 *
	 * @param Thrive_Template $template
	 * @param                 $primary_template
	 * @param                 $secondary_template
	 * @param                 $variable_template
	 *
	 * @return array|mixed|object
	 */
	public function get_cookie_data( $template, $primary_template, $secondary_template, $variable_template ) {
		$data = [];

		if ( $this->can_load_from_cookie( $template, $primary_template, $secondary_template, $variable_template ) ) {
			/* decode the json to an associative array*/
			$data = json_decode( wp_unslash( $_COOKIE[ THRIVE_THEME_SWITCHED_CONTENT ] ), true );

			/* generate a key to identify existing data in the cookies */
			$key = $primary_template . $secondary_template;

			/* concatenate the post format key */
			$key .= $this->get_post_format_key( $template );

			/* check if we have something stored for this key */
			$data = isset( $data[ $key ] ) ? $data[ $key ] : [];
		}

		return $data;
	}

	/**
	 * Get a string 'key' for post formats ( 'audio' returns 'audio', 'video' returns 'video', etc, but 'standard' returns empty ).
	 *
	 * @param $template
	 *
	 * @return string
	 */
	private function get_post_format_key( $template ) {
		$format = $template->meta( 'format' );

		return empty( $format ) || ( $format === THRIVE_STANDARD_POST_FORMAT ) ? '' : $format;
	}

	/**
	 * Check if we can/should load existing data from cookies.
	 *
	 * @param $template           Thrive_Template
	 * @param $primary_template   String
	 * @param $secondary_template String
	 * @param $variable_template  String
	 *
	 * @return bool
	 */
	private function can_load_from_cookie( $template, $primary_template, $secondary_template, $variable_template ) {
		return
			empty( $variable_template ) && /* stop if this is a specific custom archive (if $variable_template exists) */
			isset( $_COOKIE[ THRIVE_THEME_SWITCHED_CONTENT ] ) && /* if the cookie is set */
			! $template->is404() && /* is not 404 */
			! ( $primary_template === THRIVE_HOMEPAGE_TEMPLATE && $secondary_template === THRIVE_BLOG_TEMPLATE ); /* is not a normal blog template */
	}

	/**
	 * Get the name of the content that is loaded from cookies.
	 *
	 * @return mixed|string
	 */
	private function get_content_name() {
		$template           = thrive_template();
		$primary_template   = $template->meta( THRIVE_PRIMARY_TEMPLATE );
		$secondary_template = $template->meta( THRIVE_SECONDARY_TEMPLATE );
		$variable_template  = $template->meta( THRIVE_VARIABLE_TEMPLATE );

		$data = $this->get_cookie_data( $template, $primary_template, $secondary_template, $variable_template );

		/* get the content name */
		$content_name = isset( $data['name'] ) ? $data['name'] : '';

		$post_id = empty( $data['variable_template'] ) ? 0 : $data['variable_template'];

		/**
		 * Check if the variable post exists in the system
		 */
		$variable_post = get_post( $post_id );

		/* If the variable template stored in the cookie became a landing page, we will not load that in the template, so the name should be empty */
		/* If the post has been deleted from the system, we show empty string */
		if ( ! empty( $post_id ) && ( tve_post_is_landing_page( $post_id ) || empty( $variable_post ) ) ) {
			$content_name = '';
		} else if ( ! empty( $post_id ) && ! empty( $variable_post ) && $variable_post instanceof WP_Post ) {
			/**
			 * Fixed the case where the post has been renamed, and the old name was loaded from cache -> see $data['name']
			 */
			$content_name = $variable_post->post_title;
		}

		/* If the user comes from TAR we need to load the name from the initial post */
		$from_tar = Thrive_Utils::from_tar();
		if ( $from_tar ) {
			$post         = get_post( $from_tar );
			$content_name = empty( $post ) ? '' : $post->post_title;
		}

		/**
		 * Filter the returned content name string.
		 *
		 * @param string          $content_name
		 * @param Thrive_Template $template template being edited
		 * @param array           $data     cookie data
		 */
		return apply_filters( 'thrive_theme_switch_content_name', $content_name, $template, $data );
	}

	/**
	 * Check if a given request has access to route
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function route_permission( $request ) {
		return current_user_can( 'manage_options' );
	}
}

/**
 * @return Thrive_Content_Switch
 */
function thrive_content_switch() {
	return Thrive_Content_Switch::instance();
}

thrive_content_switch();
