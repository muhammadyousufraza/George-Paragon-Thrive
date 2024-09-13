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
 * Class Thrive_HF_Section
 */
class Thrive_HF_Section {

	use Thrive_Post_Meta;

	/**
	 * @var int
	 */
	public $ID;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var mixed|string
	 */
	protected $content = '';

	/**
	 * @var Thrive_Noop|Thrive_Template
	 */
	public $template;

	/**
	 * Folder name where the hf previews are kept ( = symbol folder, but I like making new constants )
	 */
	const THUMBNAIL_FOLDER = TCB_Symbols_Post_Type::SYMBOL_THUMBS_FOLDER;

	/**
	 * Keeps the default placeholder url and sizes
	 */
	const THUMBNAIL_PLACEHOLDER = [
		'url' => THEME_URL . '/inc/assets/images/featured_image.png',
		'w'   => 743,
		'h'   => 385,
	];
	/**
	 * Query to fetch only the headers and footers
	 */
	const POST_QUERY = [
		'post_type'      => TCB_Symbols_Post_Type::SYMBOL_POST_TYPE,
		'posts_per_page' => - 1,
		'post_status'    => 'publish',
		'tax_query'      => [
			'relation' => 'OR',
			[
				'taxonomy' => TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
				'field'    => 'slug',
				'terms'    => 'headers',
			],
			[
				'taxonomy' => TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY,
				'field'    => 'slug',
				'terms'    => 'footers',
			],
		],
	];

	/**
	 * @var bool
	 */
	private $is_inner_frame;

	public static $meta_fields = [
		'content' => '',
		'type'    => '',
	];

	/**
	 * Thrive_HF_Section constructor.
	 *
	 * @param int    $id
	 * @param string $type
	 * @param array  $meta_input
	 * @param int    $template_id
	 */
	public function __construct( $id, $type = '', $meta_input = [], $template_id = 0 ) {
		$this->type = $type;

		/*  no content and ( no ID / invalid ID ) -> get the default header/footer */
		if ( empty( $meta_input['content'] ) && ( empty( $id ) || ! static::is_valid( $id ) ) ) {
			$meta_input = Thrive_Theme_Default_Data::default_symbol_values( $type );

			$id = $meta_input['id'];
		}

		$this->ID = $id;

		$this->template       = $template_id ? new Thrive_Template( $template_id ) : thrive_template();
		$this->is_inner_frame = Thrive_Utils::is_inner_frame() || Thrive_Utils::during_ajax();

		if ( $this->is_dynamic() ) {
			$this->content = get_post_meta( $id, 'tve_updated_post', true );
		} else {
			$this->meta    = $meta_input;
			$this->content = $meta_input['content'];
		}
	}

	/**
	 * Actions that should be done when we want to remove a section
	 *
	 * @return false|WP_Post|null
	 */
	public function delete() {
		$this->unlink_from_templates();

		return wp_delete_post( $this->ID );
	}

	/**
	 * Get section type
	 *
	 * @return mixed
	 */
	public function type() {
		return $this->type;
	}

	/**
	 * Returns true if the ( dynamic!! ) header/footer is 'healthy', false if not.
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public static function is_valid( $id ) {
		$post = get_post( $id );

		return
			! empty( $post ) &&
			! empty( get_post_meta( $id, 'tve_updated_post', true ) ) &&
			$post->post_status === 'publish' &&
			get_post_type( $id ) === TCB_Symbols_Post_Type::SYMBOL_POST_TYPE;
	}

	/**
	 * @param bool $visibility_check whether or not to perform visibility check or consider it as visible
	 *
	 * @return string
	 */
	public function render( $visibility_check = true ) {
		$is_visible = $visibility_check ? Thrive_Utils::get_section_visibility( $this->type() ) : true;

		/* if the hf section is not visible and we're not inside the theme editor or the architect editor, show nothing. */
		if ( ! $is_visible && ! $this->is_inner_frame && ! Thrive_Utils::is_architect_editor() ) {
			return '';
		}
		$css     = '';
		$content = $this->content();
		$class   = $this->class_attr( $is_visible );
		if ( ! empty( $this->meta['extraClass'] ) && is_array( $this->meta['extraClass'] ) ) {
			$class .= ' ' . implode( ' ', $this->meta['extraClass'] );
		}
		$attributes = $this->get_attr();

		$content = TCB_Post_List_Shortcodes::check_dynamic_links( $content );

		if ( ! $this->is_inner_frame ) {
			$css = tve_get_shared_styles( $content );
		}

		$content = TCB_Utils::wrap_content( $css . $content, $this->type(), 'thrive-' . $this->type(), $class, $attributes );

		$content = tve_thrive_shortcodes( $content, $this->is_inner_frame );

		return $content;
	}

	/**
	 * Get hf section content
	 *
	 * @return string
	 */
	public function content() {
		if ( $this->is_dynamic() ) {
			$content = TCB_Symbol_Template::symbol_render_shortcode( [ 'id' => $this->ID ] );
			/* remove @import rules from style if they have already been added in the head section */
			if ( tcb_should_print_unified_styles() ) {
				foreach ( $this->get_css_imports() as $import ) {
					$content = str_replace( $import, '', $content );
				}
			}

			$GLOBALS['symbol_id'] = $this->ID;
			$content              = do_shortcode( $content );
			unset( $GLOBALS['symbol_id'] );
		} else {
			$attr = [];

			if ( ! empty( $this->meta['sticky'] ) ) {
				$attr['data-tve-scroll'] = $this->meta['sticky'];
			}

			$content = Thrive_Section::parse( $this->content );
			$content = TCB_Utils::wrap_content( $content, 'div', '', 'thrive-symbol-shortcode', $attr );
		}

		$content = tve_do_wp_shortcodes( $content, is_editor_page_raw( true ) );

		return $content;
	}

	/**
	 * HF Section classes
	 *
	 * @param $is_visible
	 *
	 * @return string
	 */
	public function class_attr( $is_visible ) {
		$class = [
			THRIVE_WRAPPER_CLASS,
			'thrv_symbol',
			'thrv_' . $this->type(),
		];

		$extra_classes = $this->get_meta( 'tve_extra_class' );

		//preserve classes saved on the element
		if ( ! empty( $extra_classes ) ) {
			$extra_classes = explode( ' ', $extra_classes );
			$class         = array_merge( $class, $extra_classes );
		}

		if ( ! $is_visible && ( $this->is_inner_frame || Thrive_Utils::is_architect_editor() ) ) {
			/* in the inner frame we just hide the section, but we insert the html */
			$class[] = 'hide-section';
		}

		if ( $this->is_dynamic() ) {
			$class[] = 'thrv_symbol_' . $this->ID;
		}

		if ( $this->type() === 'header' ) {
			$class[] = 'tve-default-state';
		}

		/**
		 * Allows dynamically modifying a header / footer CSS class
		 *
		 * @param array  $class current classes
		 * @param string $type  current type - header / footer
		 *
		 * @return array
		 */
		$class = apply_filters( 'thrive_hf_class', $class, $this->type() );

		return implode( ' ', $class );
	}

	/**
	 * @return array
	 */
	public function get_attr() {
		$attr = [
			'role' => $this->type() === THRIVE_HEADER_SECTION ? 'banner' : 'contentinfo',
		];

		if ( $this->is_dynamic() ) {
			if ( $this->is_inner_frame ) {
				$attr['data-section']      = $this->type();
				$attr['data-id']           = $this->ID;
				$attr['data-section-name'] = get_the_title( $this->ID );
			}
		} elseif ( $this->is_inner_frame ) {
			$attr['data-section'] = $this->type();
		}

		return $attr;
	}

	/**
	 * Return all the local hf sections, grouped according to their type.
	 * todo: this will be probably also be done on TAr, so delete this after the HF/symbol functionality is integrated there
	 *
	 * @return array
	 */
	public static function get_all( $query = [] ) {
		$headers_footers = [];

		$args = wp_parse_args( $query, static::POST_QUERY );

		foreach ( get_posts( $args ) as $hf ) {
			$is_header = has_term( 'headers', TCB_Symbols_Taxonomy::SYMBOLS_TAXONOMY, $hf );

			$headers_footers[ $hf->ID ] = [
				'id'      => $hf->ID,
				'default' => get_post_meta( $hf->ID, 'default', true ),
				'type'    => $is_header ? THRIVE_HEADER_SECTION : THRIVE_FOOTER_SECTION,
				'name'    => $hf->post_title,
				'thumb'   => TCB_Utils::get_thumb_data( $hf->ID, THEME_UPLOADS_PREVIEW_SUB_DIR, static::THUMBNAIL_PLACEHOLDER ),
			];
		}

		return $headers_footers;
	}

	/**
	 * Section selector
	 *
	 * @return string
	 */
	public function selector() {
		return $this->is_dynamic() ? $this->get_dynamic_selector() : $this->get_static_selector();
	}

	/**
	 * @return string
	 */
	public function get_dynamic_selector() {
		return '.thrv_symbol_' . $this->ID;
	}

	/**
	 * @return string
	 */
	public function get_static_selector() {
		return '.thrv_' . $this->type();
	}

	/**
	 * Tells us if we have a saved section or just the html
	 *
	 * @return bool
	 */
	public function is_dynamic() {
		return ! empty( $this->ID );
	}

	/**
	 * For all the templates that use this HF section, manually unlink the section and copy the data to the template meta.
	 */
	public function unlink_from_templates() {

		$section_data = [
			/* set the ID to 0 to mark the section as static */
			'id'      => 0,
			'content' => $this->content,
		];

		$type = $this->type();

		foreach ( thrive_skin()->get_templates( 'object' ) as $template ) {
			/* @var $template Thrive_Template */
			$template_section = $template->get_section( $type );

			if ( ! empty( $template_section['id'] ) && (int) $this->ID === (int) $template_section['id'] ) {
				$template_sections = $template->meta( 'sections' );

				/* set the new data for this section type */
				$template_sections[ $type ] = array_merge( $template_sections[ $type ], $section_data );

				/* get the section style and convert the css from dynamic to static */
				$section_style = $this->get_converted_styles_for_template( $template );

				$template->update( [
					'sections' => $template_sections,
					'style'    => Thrive_Css_Helper::merge_styles( $template->meta( 'style' ), $section_style ),
				] );
			}
		}
	}

	/**
	 * Replace '.thrv_symbol_{ID}' with {body_class} .thrv_{type} ( conversion from dynamic to static ).
	 * Also convert the string to a structured array with css grouped by medias and fonts.
	 *
	 * @param Thrive_Template $template
	 *
	 * @return array|mixed|object
	 */
	public function get_converted_styles_for_template( $template ) {
		$style = $this->get_meta( 'tve_custom_css' );

		$old_selector   = $this->get_dynamic_selector();
		$template_class = $template->body_class( false, 'string' );
		$new_selector   = $template_class . ' ' . $this->get_static_selector();

		$style = str_replace( $old_selector, $new_selector, $style );

		$style = Thrive_Css_Helper::get_style_array_from_string( $style );

		return $style;
	}

	/**
	 * Get all used @import rules
	 *
	 * @return array
	 */
	public function get_css_imports() {
		return Thrive_Css_Helper::get_fonts_from_string( $this->get_meta( 'tve_custom_css' ) );
	}

	/**
	 * Creates and / or populates a symbol post instance with a cloud template
	 *
	 * @param string             $cloud_id   cloud ID of template that should be used
	 * @param string             $type       header / footer
	 * @param string             $post_title post title to use when creating a new post
	 * @param WP_Post|array|null $post       symbol post id to populate. Will create one if null
	 *
	 * @return int|WP_Error post ID or error
	 */
	public static function populate_from_cloud_template( $cloud_id, $type, $post_title, $post = null ) {
		/* get template data */
		$template = tve_get_cloud_template_data( $type, [
			'id'                => $cloud_id,
			'type'              => $type,
			'skip_do_shortcode' => true,
		] );

		if ( ! is_array( $template ) || is_wp_error( $template ) ) {
			return $template;
		}

		/** @var TCB_Symbol_Element $symbol_element */
		$symbol_element = tcb_elements()->element_factory( 'symbol' );

		$symbol_data = [
			'content'      => $template['content'],
			'css'          => $template['head_css'],
			'term_id'      => TCB_Symbols_Taxonomy::get_term_id( $type . 's' ), // ... :(
			'tve_globals'  => empty( $template['tve_globals'] ) ? [] : $template['tve_globals'],
			'symbol_title' => $post_title ?: $template['name'],
		];

		if ( ! $post ) {
			/* case: user deleted / reset skin, and has re-installed it. Symbol already exists, search it by post_title */
			$post = get_page_by_title( $post_title, OBJECT, TCB_Symbols_Post_Type::SYMBOL_POST_TYPE );
		}

		if ( ! $post ) {
			/* create a new symbol */
			$symbol    = $symbol_element->create_symbol( $symbol_data );
			$symbol_id = $symbol['id'];
		} else {
			/* make sure the post is always published */
			if ( $post->post_status !== 'publish' ) {
				wp_update_post( [
					'ID'          => $post->ID,
					'post_status' => 'publish',
				] );
			}

			$symbol_data['id'] = $post->ID;
			/* update symbol */
			$symbol_element->edit_symbol( $symbol_data );
			$symbol_id = $post->ID;
		}
		if ( ! empty( $template['thumb'] ) ) {
			TCB_Utils::save_thumbnail_data( $symbol_id, $template['thumb'] );
		}

		return $symbol_id;
	}

	/**
	 * Replace the current menu from the section with another one
	 *
	 * @param $menu_id
	 *
	 * @return int
	 * @throws Exception
	 */
	public function replace_menu( $menu_id ) {

		/* Replace the menu with the selected one */
		$changed_html = Thrive_Utils::replace_menu_in_html( $menu_id, $this->content );

		/* Do not allow to save empty string */
		if ( empty( $changed_html ) ) {
			throw new Exception( 'Something went wrong with the menu replacement' );
		}

		if ( $this->is_dynamic() ) {
			$this->set_meta( 'tve_updated_post', $changed_html );
		} else {
			$sections                = $this->template->meta( 'sections' );
			$section                 = $this->template->get_section( $this->type );
			$section['content']      = $changed_html;
			$sections[ $this->type ] = $section;

			$this->template->update( [
				'sections' => $sections,
			] );
		}

		return $this->ID;
	}
}
