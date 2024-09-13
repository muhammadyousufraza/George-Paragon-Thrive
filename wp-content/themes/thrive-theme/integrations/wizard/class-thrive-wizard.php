<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Wizard {
	const KEY = 'ttb-wizard';
	/**
	 * Stores all active wizard steps
	 *
	 * @var array
	 */
	public static $active_steps
		= [
			'logo',
			'color',
			'header',
			'footer',
			'homepage',
			'post',
			'blog',
			'page',
			'menu',
		];

	/* wizard request data */
	protected $request = [];

	/**
	 * Wizard completion data
	 *
	 * @var array
	 */
	protected $completion_data = [];

	public function __construct() {
		if ( static::is_frontend() ) {
			$this->init_frontend();
		}
	}

	/**
	 * Get request data
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return string|array
	 */
	public function request( $key, $default = '' ) {
		return isset( $this->request[ $key ] ) ? $this->request[ $key ] : $default;
	}

	/**
	 * Return the active steps from the wizard
	 *
	 * @return mixed|void
	 */
	public static function get_active_steps() {
		return apply_filters( 'thrive_theme_wizard_active_steps', static::$active_steps );
	}

	/**
	 * Get current step
	 *
	 * @return string
	 */
	public function step() {
		return $this->request( 'step' );
	}

	/**
	 * Checks if current request is for a wizard preview step
	 *
	 * @return bool
	 */
	public static function is_frontend() {
		return ! empty( $_REQUEST[ static::KEY ] );
	}

	/**
	 * Whether or not the current request is for a template preview
	 *
	 * @return bool
	 */
	public function is_template_preview() {
		return static::is_frontend() && $this->request( 'template_id' ) && ! in_array( $this->step(), [ THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ] );
	}

	/**
	 * Inits the frontend request for a wizard step preview
	 */
	public function init_frontend() {

		show_admin_bar( false );
		add_filter( 'show_admin_bar', '__return_false' );
		$this->request = $_REQUEST[ static::KEY ];

		/* register filters */
		add_filter( 'thrive_hf_section', [ $this, 'maybe_render_custom_hf' ], 10, 2 );

		/* conditional rendering */
		add_filter( 'thrive_template_render_header', [ $this, 'should_render_header' ] );
		add_filter( 'thrive_template_render_footer', [ $this, 'should_render_footer' ] );
		add_filter( 'thrive_template_render_structure', [ $this, 'should_render_structure' ] );

		/* output placeholders during wizard preview steps */
		add_action( 'thrive_template_header_before', [ $this, 'header_placeholder' ] );
		add_action( 'thrive_template_footer_before', [ $this, 'structure_footer_placeholder' ] );

		add_filter( 'thrive_html_class', function ( $classes ) {
			$step       = str_replace( 'woo_', '', $this->step() );
			$classes [] = "ttb-wizard ttb-wizard--{$step}";
			if ( $this->step() === 'homepage' ) {
				$classes [] = 'home--' . ( $this->request( 'type' ) ?: 'splash' );
			}

			return $classes;
		} );

		add_filter( 'body_class', function ( $classes ) {
			return apply_filters( 'thrive_theme_wizard_body_classes', $classes, $this->step() );
		} );

		add_filter( 'thrive_template_style', [ $this, 'filter_css' ] );
		add_filter( 'thrive_dashboard_show_recaptcha', [ $this, 'show_recaptcha' ] );

		if ( $this->is_template_preview() ) {
			add_filter( 'thrive_template_default_id', function () {
				return (int) $this->request( 'template_id' );
			} );
		}

		/* handles homepage previews */
		if ( $this->step() === 'homepage' ) {
			$homepage_type = $this->request( 'type' );
			$page_id       = $this->request( 'page_id' );
			$template_id   = $this->request( 'template_id' );
			$uid           = $this->request( 'uid' ); // cloud ID of the LP template

			add_filter( 'pre_option_show_on_front', static function () use ( $homepage_type ) {
				/* use blog by default, and modify that accordingly */
				return $homepage_type && $homepage_type !== 'blog' ? 'page' : 'posts';
			} );
			add_filter( 'pre_option_page_on_front', static function () use ( $homepage_type, $page_id ) {
				if ( ! empty( $GLOBALS['thrive_during_post_insert'] ) ) {
					return false; // don't cause an infinite loop!
				}
				$value = false;
				if ( $homepage_type === 'page' && $page_id ) {
					$value = $page_id;
				}

				return $value;
			} );

			if ( $homepage_type === 'template' && $template_id ) {

				/**
				 * displays a custom post type on which a landing page has been applied
				 */
				add_action( 'wp', static function () use ( $template_id, $uid ) {
					$post = get_post();
					if ( strpos( $template_id, 'user-saved-template' ) !== false ) {
						/* apply a TCB saved LP on this page */
						$lp_id = (int) str_replace( 'user-saved-template-', '', $template_id );
						tcb_landing_page( $post->ID )->change_template( $template_id, $lp_id );
					} else if ( method_exists( TCB_Landing_Page::class, 'apply_cloud_template' ) ) {
						/* apply a TCB cloud LP on this page */
						TCB_Landing_Page::apply_cloud_template( $post->ID, $template_id, $uid );

						update_post_meta( $post->ID, '_tve_header', thrive_skin()->get_default_data( THRIVE_HEADER_SECTION ) );
						update_post_meta( $post->ID, '_tve_footer', thrive_skin()->get_default_data( THRIVE_FOOTER_SECTION ) );
						delete_post_meta( $post->ID, 'tve_disable_theme_dependency' );

					}
				}, 11 );
			}
		}

		add_filter( 'thrive_template_structure', static function ( $structure ) {
			return TCB_Utils::wrap_content( $structure, 'div', '', 'content-wrap' );
		} );

		add_filter( 'thrive_theme_display_css', static function ( $display ) {
			/* We do not need to show the theme css on the wizard setup complete page */
			if ( ! empty( $_REQUEST[ static::KEY ]['step'] ) && $_REQUEST[ static::KEY ]['step'] === '__welcome' ) {
				$display = false;
			}

			return $display;
		} );

		add_action( 'wp_enqueue_scripts', [ $this, 'dequeue_conflicting_scripts' ], PHP_INT_MAX );
	}

	/**
	 * If a different header / footer should be rendered, output that instead
	 *
	 * @param array  $hf_data
	 * @param string $type
	 *
	 * @return array
	 */
	public function maybe_render_custom_hf( $hf_data, $type ) {
		if ( strpos( $this->step(), $type ) !== false && $this->request( 'template_id' ) ) {
			/* get the default html / css content from the cloud */
			$content = $this->get_hf_preview_content( $type, $this->request( 'template_id' ) );

			if ( $content ) {
				$hf_data['id']      = '';
				$hf_data['content'] = $content;
			}
		}

		return $hf_data;
	}

	/**
	 * Get Css + Content from a cloud or a local template
	 *
	 * @param string $type
	 * @param string $id
	 * @param string $source
	 *
	 * @return string
	 */
	public function get_hf_preview_content( $type, $id, $source = 'cloud' ) {
		//TODO maybe refactor this part
		if ( $source === 'cloud' ) {
			$template = tve_get_cloud_template_data( $type, [
				'id'   => $id,
				'type' => $type,
			] );

			if ( ! is_array( $template ) ) {
				return '';
			}

			$content = $template['content'];
			$css     = str_replace( '|TEMPLATE_ID|', $id, $template['head_css'] );
		} else {
			$symbol = get_posts( [
				'include'   => [ $id ],
				'post_type' => TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
			] );

			if ( empty( $symbol ) ) {
				return '';
			}

			$content = TCB_Symbol_Template::content( $id );
			$css     = TCB_Symbol_Template::css( $id );

			// enqueue custom icons in preview too
			if ( tve_get_post_meta( $id, 'thrive_icon_pack' ) ) {
				$content = '<link rel="stylesheet" id="thrive_icon_pack-css" href="' . TCB_Icon_Manager::enqueue_icon_pack() . '" type="text/css" media="all">' . $content;
			}
		}

		/**
		 * Add CSS classes so that styles are applied
		 */
		add_filter( 'thrive_hf_class', function ( $classes ) use ( $id ) {
			$classes [] = 'thrv_symbol_' . $id;

			return $classes;
		} );

		return '<style type="text/css">' . $css . '</style>' . $content;
	}

	/**
	 * Hide the header in some steps of the wizard
	 *
	 * @param bool $show
	 *
	 * @return bool
	 */
	public function should_render_header( $show ) {
		switch ( $this->step() ) {
			case '__welcome':
			case THRIVE_HEADER_SECTION:
				/* in the "Choose header" step, only render header if the user is currently previewing a header */
				$show = (bool) $this->request( 'template_id', false );
				break;
			case THRIVE_FOOTER_SECTION:
			case 'logo':
			case 'color':
			case 'menu':
			case 'woo':
				$show = false;
				break;
			case 'homepage':
				$show = (bool) $this->request( 'type' );
				break;
			case 'post':
			case 'page':
			case 'blog':
			default:
				$show = (bool) thrive_skin()->get_default_data( THRIVE_HEADER_SECTION );
				break;
		}

		return $show;
	}

	/**
	 * Hide the footer in some steps of the wizard
	 *
	 * @param bool $show
	 *
	 * @return bool
	 */
	public function should_render_footer( $show ) {
		switch ( $this->step() ) {
			case '__welcome':
			case THRIVE_HEADER_SECTION:
			case 'logo':
			case 'color':
			case 'menu':
			case 'woo':
				$show = false;
				break;
			case THRIVE_FOOTER_SECTION:
				/* in the "Choose footer" step, only render if the user is currently previewing a footer */
				$show = (bool) $this->request( 'template_id', false );
				break;
			case 'homepage':
				$show = (bool) $this->request( 'type' );
				break;
			default:
				break;
		}

		return $show;
	}

	/**
	 * Append default header and footer to the content
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public static function append_header_footer_to_lp( $content ) {
		$header_id = thrive_skin()->get_default_data( THRIVE_HEADER_SECTION );
		$footer_id = thrive_skin()->get_default_data( THRIVE_FOOTER_SECTION );

		if ( ! empty( $header_id ) ) {
			$header = ( new Thrive_HF_Section( $header_id, THRIVE_HEADER_SECTION ) )->render();
		} else {
			$header = '';
		}

		if ( ! empty( $footer_id ) ) {
			$footer = ( new Thrive_HF_Section( $footer_id, THRIVE_FOOTER_SECTION ) )->render();
		} else {
			$footer = '';
		}

		return $header . $content . $footer;
	}

	/**
	 * Don't render page structure during some steps of the wizard
	 *
	 * @param bool $show
	 *
	 * @return bool
	 */
	public function should_render_structure( $show ) {
		$step = $this->step();

		switch ( $step ) {
			case '__welcome':
			case THRIVE_HEADER_SECTION:
			case THRIVE_FOOTER_SECTION:
			case 'logo':
			case 'color':
			case 'menu':
				$show = false;
				break;
			case 'post':
			case 'blog':
			case 'page':
				$show = (bool) $this->request( 'template_id', false );
				break;
			case 'homepage':
				$show = (bool) $this->request( 'type' );
				break;
			default:
				break;
		}

		return apply_filters( 'thrive_theme_wizard_render_structure', $show, $step );
	}

	/**
	 * Output header placeholder
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function header_placeholder( $content = '' ) {
		if ( ! $this->should_render_header( true ) ) {
			/* output a header placeholder */
			$content .= Thrive_Utils::return_part( '/integrations/wizard/views/placeholder-header.php' );
		}

		return $content;
	}

	/**
	 * Output structure and footer placeholders
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function structure_footer_placeholder( $content = '' ) {
		if ( ! $this->should_render_structure( true ) ) {
			/* output a structure placeholder */
			/* first, try custom structure */
			switch ( $this->step() ) {
				case 'post':
					$content .= Thrive_Utils::return_part( '/integrations/wizard/views/placeholder-structure-post.php' );
					break;
				case 'blog':
					$content .= Thrive_Utils::return_part( '/integrations/wizard/views/placeholder-structure-blog.php' );
					break;

				default:
					$content .= Thrive_Utils::return_part( '/integrations/wizard/views/placeholder-structure.php' );
					break;
			}
		}
		if ( ! $this->should_render_footer( true ) ) {
			/* output a footer placeholder */
			$content .= Thrive_Utils::return_part( '/integrations/wizard/views/placeholder-footer.php' );
		}

		return $content;
	}

	/**
	 * Filter current template's CSS based on current wizard step
	 * Removes CSS that should not be displayed when previewing a header / footer template (those come with their own styles)
	 *
	 * @param string $css CSS style node
	 *
	 * @return string
	 */
	public function filter_css( $css ) {
		$step = $this->step();

		if ( $step === THRIVE_HEADER_SECTION || $step === THRIVE_FOOTER_SECTION ) {
			$css = str_replace( ".thrv_{$step}", '.thrv_header_pending', $css );
		}

		return $css;
	}

	/**
	 * Do not show recaptcha inside the theme iframes
	 *
	 * @param bool $show
	 *
	 * @return false
	 */
	public function show_recaptcha( $show ) {
		if ( Thrive_Utils::is_iframe() ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Frontend wizard page localization
	 *
	 * @return array
	 */
	public function localize_frontend() {
		$data = [ 'step' => $this->step() ];

		/* get next and previous pages when previewing a page as homepage */
		$current_page_id = $this->request( 'page_id' );
		if ( $current_page_id && $data['step'] === 'homepage' ) {
			$data['next_page'] = Thrive_Theme_DB::instance()->get_wizard_adjacent_page( $current_page_id, 'next' );
			$data['prev_page'] = Thrive_Theme_DB::instance()->get_wizard_adjacent_page( $current_page_id, 'prev' );
		}

		return $data;
	}

	/**
	 * Get the ID of the first post with post type $post_type. If nothing found, it generates a demo post and returns its ID.
	 *
	 * @param string $post_type
	 * @param array  $query_args allows more control over the searched posts. if 'post_type' is sent, it will overwrite the $post_type variable
	 *
	 * @return int
	 */
	public static function get_post_or_demo_content_id( $post_type = 'post', $query_args = [], $char_limit = 1500 ) {
		$query_args = wp_parse_args( $query_args, [
			'post_type'        => $post_type,
			'post_status'      => 'publish',
			'tax_query'        => Thrive_Utils::get_post_format_tax_query( '' ),
			'meta_query'       => Thrive_Utils::meta_query_no_landing_pages(),
			'exclude'          => [
				get_option( 'page_for_posts' ),
				get_option( 'page_on_front' ),
				Thrive_Defaults::get_default_post_id( 'blog' ),
				Thrive_Defaults::get_default_post_id( 'homepage', 'Generated Homepage' ),
			],
			'numberposts'      => 1,
			'suppress_filters' => false,
		] );

		$query_args['fields'] = 'ids'; // always get IDs

		if ( $char_limit ) {
			Thrive_Theme_DB::add_post_content_length_filter( $post_type, $char_limit );
		} else {
			remove_all_filters( 'posts_where' );
		}

		$post_ids = get_posts( $query_args );

		if ( empty( $post_ids ) && ! empty( $char_limit ) ) {
			/* search again, but without the post content limitation */
			$post_id = static::get_post_or_demo_content_id( $post_type, $query_args, 0 );
		} else if ( ! empty( $post_ids ) ) {
			$post_id = $post_ids[0];
		} else {
			$post    = Thrive_Demo_Content::get_one();
			$post_id = $post ? $post->ID : 0;
		}

		return $post_id;
	}

	/**
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function localize_admin() {
		/* pages that are automatically suggested for the homepage "PAGE" option */
		$suggested_pages = static::autocomplete_pages();

		/**
		 * Preload these templates to allow faster wizard navigation
		 */
		$templates       = [
			'post' => static::get_templates( 'post' ),
			'blog' => static::get_templates( 'blog' ),
			'page' => static::get_templates( 'page' ),
		];
		$completion_data = static::get_completion_data();

		/**
		 * Fill in a default template ID so that the preview is loaded directly in the wizard, without loading a placeholder page
		 */
		foreach ( $templates as $type => $template_list ) {
			if ( ! empty( $template_list ) && ! is_wp_error( $template_list ) ) {
				/* synchronize wizard selected IDs with the currently selected default templates - by default take the first template */
				$default_template_id = $template_list[0]['id'];

				/* get the ID of the default template */
				$completion_data['settings'][ $type ]['template_id'] = array_reduce( $template_list, static function ( $current_id, $template ) {
					if ( $template['default'] && 'standard' === $template['format'] ) {
						$current_id = $template['id'];
					}

					return $current_id;
				}, $default_template_id );
			}
		}

		/**
		 * Fill in a default page_id/title/url for the homepage "PAGE" option so that the preview is loaded immediately
		 */
		if ( empty( $completion_data['settings']['homepage']['page_id'] ) && ! empty( $suggested_pages ) ) {
			$page = reset( $suggested_pages );

			$completion_data['settings']['homepage'] += [
				'page_id'    => $page['id'],
				'page_url'   => $page['url'],
				'page_title' => $page['label'],
			];
		}

		$wizard_structure = include THEME_PATH . '/integrations/wizard/structure.php';

		return [
			'data'             => $completion_data,
			'suggest_pages'    => $suggested_pages,
			'urls'             => static::get_wizard_urls(),
			'templates'        => $templates,
			'is_completed'     => ! empty( $completion_data['done'] ) && count( $completion_data['done'] ) === count( static::get_active_steps() ),
			'is_skipped'       => (int) thrive_skin()->get_meta( Thrive_Skin::IS_WIZARD_SKIPPED ) === 1,
			'active_steps'     => static::get_active_steps(),
			'structure'        => apply_filters( 'thrive_theme_wizard_structure', $wizard_structure ),
			'is_fresh_install' => static::is_fresh_install(),
		];
	}

	/**
	 * @return bool
	 */
	public static function is_fresh_install() {
		$is_fresh_install = false;

		if (
			class_exists( 'TPM_Product_Theme_Builder', false ) &&
			method_exists( 'TPM_Product_Theme_Builder', 'is_fresh_install' ) ) {
			$is_fresh_install = TPM_Product_Theme_Builder::is_fresh_install();
		}

		return $is_fresh_install;
	}

	private static function get_wizard_urls() {
		$empty_blog    = empty( get_posts( [ 'posts_per_page' => 1 ] ) );

		$demo_blog     = Thrive_Demo_Content::has_demo_blog_generated() ? get_post_type_archive_link( Thrive_Demo_Content::POST_TYPE ) : '';
		$demo_post     = Thrive_Demo_Content::get_one();
		$demo_post_url = $demo_post ? get_permalink( $demo_post ) : '';

		if ( $empty_blog ) {
			$blog_url = $demo_blog;
		} else if ( get_option( 'show_on_front' ) === 'page' ) {
			$page_for_posts = get_option( 'page_for_posts' );

			if ( ! $page_for_posts ) {
				/* ensure that a "Blog" page exists */
				$page_for_posts = Thrive_Defaults::get_default_post_id( 'blog' );
				update_option( 'page_for_posts', $page_for_posts );
			}

			$blog_url = empty( $page_for_posts ) ? '' : get_permalink( $page_for_posts );
		} else {
			$blog_url = get_home_url();
		}

		$urls = [
			'home'               => home_url( '/', is_ssl() ? 'https' : null ),
			'post'               => get_permalink( static::get_post_or_demo_content_id() ),
			'demo-post'          => $demo_post_url,
			'blog'               => Thrive_Utils::ensure_https( $blog_url ),
			'demo-blog'          => $demo_blog,
			'page'               => get_permalink( static::get_post_or_demo_content_id( 'page' ) ),
			'demo-page'          => $demo_post_url,
			'draft_homepage'     => get_permalink( Thrive_Defaults::get_default_post_id( 'homepage_draft', 'Generated Homepage (Draft)', Thrive_Demo_Content::PAGE_TYPE, true ) ),
			'homepage_architect' => tcb_get_editor_url( get_option( 'page_on_front' ) ),
		];

		$urls = array_map( static function ( $url ) {
			return Thrive_Utils::ensure_https( $url );
		}, $urls );

		return apply_filters( 'thrive_theme_wizard_urls', $urls );
	}

	/**
	 * Get autocomplete search results for pages (used in homepage wizard step)
	 *
	 * @param array $query_args WP's get_posts()-compatible arguments array
	 *
	 * @return array
	 */
	public static function autocomplete_pages( $query_args = [] ) {

		$is_search = ! empty( $query_args['s'] );
		$exclude   = ! empty( $query_args['exclude'] ) ? $query_args['exclude'] : [];

		$defaults = [
			'post_status' => 'publish',
			'post_type'   => 'page',
			'numberposts' => 10,
			'offset'      => 0,
			'order'       => $is_search ? 'ASC' : 'DESC',
			'orderby'     => $is_search
				? 'relevance'
				: [
					'post_modified' => 'DESC',
					'ID'            => 'DESC',
				],
			's'           => '',
			'exclude'     => array_merge(
				$exclude,
				[
					get_option( 'page_for_posts' ),
					Thrive_Defaults::get_default_post_id( 'blog' ),
					Thrive_Defaults::get_default_post_id( 'homepage', 'Generated Homepage' ),
				]
			),
		];

		$query_args = wp_parse_args( $query_args, $defaults );

		/**
		 * Exclude various pages from the query
		 */
		$query_args = Thrive_Utils::filter_default_get_posts_args( $query_args, 'wizard_pages' );

		return array_map( static function ( $page ) {
			return [
				'id'    => $page->ID,
				'value' => $page->ID,
				'label' => $page->post_title,
				'lp'    => (bool) $page->tve_landing_page,
				'url'   => Thrive_Utils::ensure_https( get_permalink( $page ) ),
			];
		}, get_posts( $query_args ) );
	}

	/**
	 * Get completion data for the current skin.
	 * Makes sure settings are up-to-date. (e.g. homepage options are correlated with the wordpress ones)
	 *
	 * @return array|stdClass
	 */
	public static function get_completion_data() {
		$wizard_data = thrive_skin()->get_meta( 'ttb_wizard' );

		if ( empty( $wizard_data ) || ! is_array( $wizard_data ) ) {
			$wizard_data = [];
		}

		/* make sure there's no residual data here */
		unset( $wizard_data['data'] );

		/* make sure some keys exist in the settings */
		if ( ! isset( $wizard_data['settings']['homepage'] ) ) {
			$wizard_data['settings']['homepage'] = [
				'type' => '',
			];
		}

		$wizard_data = static::existing_homepage_setup( $wizard_data );

		if ( isset( $wizard_data['settings'] ) ) {
			$settings = &$wizard_data['settings'];
		}

		if ( ! empty( $settings['homepage']['type'] ) || ! empty( $settings['homepage']['has_homepage_set_from_skin'] ) ) {
			/* align with WP options */
			$show_on_front = get_option( 'show_on_front' );
			$page_id       = get_option( 'page_on_front' );

			if ( 'posts' === $show_on_front || empty( $page_id ) ) {
				$settings['homepage']['type'] = 'blog'; // no extra setting needed here.
			} else {
				if ( isset( $settings['homepage']['template_id'] ) ) {
					$settings['homepage']['type'] = 'template';
				} else {
					$settings['homepage']['type'] = 'page';
				}
				$id   = get_option( 'page_on_front' );
				$page = get_post( $id );
				if ( $id && $page ) {
					$settings['homepage']['page_id']         = $id;
					$settings['homepage']['page_title']      = $page->post_title;
					$settings['homepage']['page_url']        = get_permalink( $page );
					$settings['homepage']['is_landing_page'] = (bool) tve_post_is_landing_page( $id );
				} else {
					unset( $settings['homepage']['page_id'], $settings['homepage']['page_title'], $settings['homepage']['is_landing_page'] );
				}
			}

			if ( $settings['homepage']['type'] === 'template' ) {
				unset( $settings['homepage']['page_id'], $settings['homepage']['page_title'], $settings['homepage']['page_url'], $settings['homepage']['is_landing_page'] );
			} elseif ( $settings['homepage']['type'] === 'page' ) {
				unset( $settings['homepage']['template_id'] );
			}
		}

		/* make sure the "done" steps are always valid */
		if ( isset( $wizard_data['done'] ) ) {
			$wizard_data['done'] = array_filter( $wizard_data['done'] );
		}

		$wizard_data['activeIndex'] = isset( $wizard_data['activeIndex'] ) ? (int) $wizard_data['activeIndex'] : - 1;

		/* Backwards compat for the menu settings - un-inspired name "id", we need to change that to "header", because it applies to headers */
		if ( isset( $wizard_data['settings']['menu']['id'] ) ) {
			$wizard_data['settings']['menu'] = [
				'header' => $wizard_data['settings']['menu']['id'],
			];
		}

		return $wizard_data;
	}

	/**
	 * If a homepage template already exists ( this happens if the user switched from a skin where he set a homepage template ), load it in the wizard data.
	 * Also do this if a homepage was set from the wizard at some point on the site
	 *
	 * @param array $wizard_data
	 *
	 * @return array
	 */
	public static function existing_homepage_setup( $wizard_data ) {

		if ( static::is_homepage_set_from_skin() || get_option( THRIVE_THEME_HOMEPAGE_SET_FROM_WIZARD, 0 ) ) {
			/* if nothing is set in the current skin but we have something set from another skin, set the flag to 1 */
			if ( empty( $wizard_data['settings']['homepage']['type'] ) ) {
				$wizard_data['settings']['homepage']['has_homepage_set_from_skin'] = 1;
			}

			$global_homepage_options = Thrive_Utils::get_homepage_options();

			/* check if this is a generated homepage ( aka ready-made homepage template ) */
			if (
				(int) Thrive_Defaults::get_default_post_id( 'homepage', 'Generated Homepage' ) === (int) $global_homepage_options['page_on_front'] &&
				! empty( $global_homepage_options['is_landing_page'] )
			) {
				/* if the saved homepage is a ready-made landing page but not user saved one, store the template_id */
				if ( ! isset( $wizard_data['settings']['homepage']['template_id'] ) || strpos( $wizard_data['settings']['homepage']['template_id'], 'user-saved-template' ) === false ) {
					$wizard_data['settings']['homepage']['template_id'] = $global_homepage_options['is_landing_page'];
				}
			} else {
				/* in every other case, make sure 'template_id' is unset */
				unset( $wizard_data['settings']['homepage']['template_id'] );
			}
		}

		return $wizard_data;
	}

	/**
	 * Check if at least one other skin has homepage settings
	 *
	 * @return bool
	 */
	public static function is_homepage_set_from_skin() {
		$is_homepage_set_from_skin = false;

		foreach ( Thrive_Skin_Taxonomy::get_all( 'ids' ) as $skin_id ) {
			/* skip the current skin */
			if ( ( new Thrive_Skin( $skin_id ) )->is_active() ) {
				continue;
			}

			$wizard_data = get_term_meta( $skin_id, 'ttb_wizard', true );

			if ( ! empty( $wizard_data['done'] ) && in_array( 'homepage', $wizard_data['done'] ) ) {
				$is_homepage_set_from_skin = true;
				break;
			}
		}

		return $is_homepage_set_from_skin;
	}

	/**
	 * Get a filter array for a template type
	 *
	 * @param string $type
	 *
	 * @return array
	 */
	public static function get_template_filters_for_type( $type ) {
		switch ( $type ) {
			case 'homepage':
				$filters = [
					'primary'   => THRIVE_HOMEPAGE_TEMPLATE,
					'secondary' => THRIVE_PAGE_TEMPLATE,
				];
				break;
			case 'post':
				$filters = [
					'primary'   => THRIVE_SINGULAR_TEMPLATE,
					'secondary' => THRIVE_POST_TEMPLATE,
				];
				break;
			case 'blog':
				$filters = [
					'primary'   => THRIVE_HOMEPAGE_TEMPLATE,
					'secondary' => THRIVE_BLOG_TEMPLATE,
				];
				break;
			case 'page':
				$filters = [
					'primary'   => THRIVE_SINGULAR_TEMPLATE,
					'secondary' => THRIVE_PAGE_TEMPLATE,
				];
				break;
			default:
				$filters = [];
				break;
		}

		return $filters;
	}

	/**
	 * Get templates for a step in the wizard
	 *
	 * @param string $type wizard step (type)
	 * @param array  $args filter arguments. allows finer control
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function get_templates( $type, $args = [] ) {
		$filters = static::get_template_filters_for_type( $type );
		/* fetch cloud templates just so that we can use the thumbnails from them */
		$filters['skin_tag'] = thrive_skin()->get_tag();
		/* allow controlling the filters directly from the $args array */
		$filters = wp_parse_args( $args, $filters );

		/**
		 * Change the filters before the cloud request
		 *
		 * @param array  $filters
		 * @param string $type
		 */
		$filters = apply_filters( 'thrive_theme_wizard_templates_filters', $filters, $type );
		/* get template preview images from cloud */
		try {
			$cloud_data = Thrive_Theme_Cloud_Api_Factory::build( 'templates' )->get_items( [ 'filters' => $filters ] );
		} catch ( Exception $e ) {
			return new WP_Error( 'tcb_api_error', $e->getMessage() );
		}

		$local_templates = array_values( array_filter( array_map( static function ( $template ) use ( &$cloud_data ) {
			$thumb = isset( $cloud_data[ $template->tag ]['thumb'] ) && $cloud_data[ $template->tag ]['thumb']['w'] ? $cloud_data[ $template->tag ]['thumb'] : $template->thumbnail();

			if ( ! empty( $thumb['url'] ) ) {
				$thumb['url'] = Thrive_Utils::ensure_https( $thumb['url'] );
				unset( $cloud_data[ $template->tag ] ); //unset this to not have it twice when we will merge the local and cloud templates
			}

			/** @var Thrive_Template $template */
			return [
				'id'         => $template->ID,
				'post_title' => $template->post_title,
				'thumb'      => $thumb,
				'default'    => (bool) $template->default,
				'format'     => $template->format,
				'source'     => 'local',
			];

		}, static::get_local_templates( $filters ) ) ) );

		$cloud_templates = array_values( array_map( static function ( $template ) {
			return [
				'id'         => $template['id'],
				'post_title' => $template['post_title'],
				'thumb'      => $template['thumb'],
				'default'    => 0,
				'format'     => empty( $template['type'] ) ? 'standard' : $template['type'],
				'source'     => 'cloud',
			];
		}, $cloud_data ) );

		/**
		 * Merge local and cloud templates in order for the user to have them all
		 */
		return array_merge( $local_templates, $cloud_templates );
	}

	/**
	 * Get a list of local templates for a skin
	 *
	 * @param      $filters
	 *
	 * @return array
	 */
	public static function get_local_templates( $filters ) {
		$skin_id = empty( $filters['skin_id'] ) ? null : $filters['skin_id'];
		unset( $filters['skin_id'] );

		return thrive_skin( $skin_id )->get_templates( 'object', false, $filters );
	}

	/**
	 * Get local headers or footers for wizard
	 *
	 * @param string $type
	 *
	 * @return array[]
	 */
	public static function get_local_hf( $type ) {

		$sections = Thrive_HF_Section::get_all( [
			'tax_query' => [
				'relation' => 'OR',
				[
					'taxonomy' => TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
					'field'    => 'slug',
					'terms'    => $type . 's',
				],
			],
		] );

		unset( $sections[ THRIVE_HEADER_SECTION ], $sections[ THRIVE_FOOTER_SECTION ] );

		return array_map( static function ( $section ) {
			return [
				'id'         => $section['id'],
				'post_title' => $section['name'],
				'thumb'      => $section['thumb'],
				'skin_tag'   => thrive_skin()->get_tag(),
			];
		}, $sections );
	}

	/**
	 * Return a template based on a tag
	 * If the template doesn't exists locally, we will download it from the cloud and save it locally
	 *
	 * @param string $tag
	 *
	 * @return Thrive_Template
	 * @throws Exception
	 */
	public function get_template_by_tag( $tag ) {
		$posts = get_posts(
			[
				'post_status' => [ 'publish', 'draft' ],
				'post_type'   => THRIVE_TEMPLATE,
				'meta_key'    => 'tag',
				'meta_value'  => $tag,
				'tax_query'   => [ thrive_skin()->build_skin_query_params() ],
				'numberposts' => 1,
			]
		);

		/* If the template doesn't exist, just download and save it from the cloud */
		if ( empty( $posts ) ) {
			$id = Thrive_Theme_Cloud_Api_Factory::build( 'templates' )->download_item( $tag );

			/* set templates that we download from cloud as draft so they won't display in the list */
			wp_update_post( [
				'ID'          => $id,
				'post_status' => 'draft',
			] );
		} else {
			$id = $posts[0];
		}

		return new Thrive_Template( $id );
	}

	/**
	 * Dequeue various javascript that's preventing the wizard iframe from functioning properly
	 */
	public function dequeue_conflicting_scripts() {
		/**
		 * Membermouse scripts doing some unspeakable stuff in frontend
		 */
		wp_dequeue_script( 'membermouse-blockUI' );
		wp_deregister_script( 'membermouse-blockUI' );
		wp_dequeue_script( 'mm-common-core.js' );
		wp_deregister_script( 'mm-common-core.js' );
		wp_dequeue_script( 'mm-preview.js' );
		wp_deregister_script( 'mm-preview.js' );
		wp_dequeue_script( 'membermouse-socialLogin' );
		wp_deregister_script( 'membermouse-socialLogin' );

		/**
		 * Dequeue some RankMath scripts that are not needed in the wizard
		 */
		wp_deregister_script( 'rank-math-analytics-stats' );
		wp_dequeue_script( 'rank-math-analytics-stats' );
	}
}

/**
 * Get wizard instance
 *
 * @return Thrive_Wizard
 */
function thrive_wizard() {

	static $thrive_wizard;
	if ( null === $thrive_wizard ) {
		/**
		 * Allows dynamically instantiating a wizard instance
		 *
		 * @param Thrive_Wizard $wizard
		 */
		$thrive_wizard = apply_filters( 'thrive_wizard_instance', new Thrive_Wizard() );
	}

	return $thrive_wizard;
}

/* always instantiate */
add_action( 'init', 'thrive_wizard' );
