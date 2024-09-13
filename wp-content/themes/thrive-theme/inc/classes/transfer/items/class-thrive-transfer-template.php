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
 * Class Thrive_Transfer_Template
 */
class Thrive_Transfer_Template extends Thrive_Transfer_Base {

	/**
	 * Json filename where to keep the data for the templates
	 *
	 * @var string
	 */
	public static $file = 'templates.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag = 'template';

	/**
	 * Prepare templates for export
	 *
	 * @param int $id
	 *
	 * @return $this
	 */
	public function read( $id ) {
		/* Set the template that we want to export */
		$this->set_item( $id );

		/* Firstly we process the layout, sections and h&f from the template, because we need to have the hash id of those in the template */
		$this->controller
			->process_sections( $this->get_sections() )
			->process_layout( $this )
			->process_headers_footers( $this->get_header_footer() );

		$content = json_encode( $this->item );

		/* and then all the rest of the information */
		$this->controller
			->process_symbols( $content )
			->process_global_colors( $content )
			->process_images( $content )
			->process_global_gradients( $content )
			->process_thumbnail( $id );

		Thrive_Transfer_Utils::replace_content_ids( $content );

		$content = Thrive_Transfer_Utils::replace_site_url( $content );

		$this->item = json_decode( $content, true );

		return $this;
	}

	/**
	 * Change section id inside the template meta
	 *
	 * @param string $type
	 * @param int    $section_id
	 */
	public function set_section( $type, $section_id ) {
		$this->item['meta_input']['sections'][ $type ]['id'] = md5( $section_id );
	}

	/**
	 * Change layout id inside the template meta
	 *
	 * @param int $layout_id
	 */
	public function set_layout( $layout_id ) {
		if ( $this->item['meta_input']['layout'] === $layout_id ) {
			$this->item['meta_input']['layout'] = md5( $layout_id );
		}
	}

	/**
	 * Set template data for export
	 *
	 * @param mixed $id
	 */
	public function set_item( $id ) {
		$template = new Thrive_Template( $id );

		/* Backwards compatibility at export for the templates that do not have tag created */
		if ( ! $template->meta( 'tag' ) ) {
			$template->meta_tag = uniqid();
		}

		$item = $template->export();

		/**
		 * Remove lightspeed meta from the export so on import the Lightspeed needs a re-run to properly generate the CSS
		 */
		$item['meta_input'] = array_merge( $item['meta_input'], [
			'_tve_js_modules'         => '',
			'_tve_lightspeed_version' => '',
			'_tve_base_inline_css'    => '',
		] );

		$this->item = $item;
	}

	/**
	 * Add template to the archive
	 */
	public function add() {
		$templates                      = $this->archive_data['template'];
		$templates[]                    = $this->item;
		$this->archive_data['template'] = $templates;
	}

	/**
	 * Get all the saved sections assigned to this template
	 *
	 * @return array|int
	 */
	public function get_sections() {
		$ids      = [];
		$sections = array_filter( $this->item['meta_input']['sections'], function ( $value, $key ) {
			/* We need to have an actual value. We already handled the headers and footers, so we don't need them anymore */
			return ! empty( $value ) && ! empty( $value['id'] ) && ! in_array( $key, [ THRIVE_HEADER_SECTION, THRIVE_FOOTER_SECTION ] );
		}, ARRAY_FILTER_USE_BOTH );

		foreach ( $sections as $type => $section ) {
			$this->set_section( $type, $section['id'] );
			$ids[] = $section['id'];
		}

		return $ids;
	}

	/**
	 * Get the layout for a template
	 *
	 * @return int
	 */
	public function get_layout_id() {
		return empty( $this->item['meta_input']['layout'] ) ? 0 : $this->item['meta_input']['layout'];
	}

	/**
	 * Get header and footer id for this template
	 *
	 * @return array
	 */
	public function get_header_footer() {
		$hf = [];

		if ( ! empty( $this->item['meta_input']['sections'] ) ) {
			$header = empty( $this->item['meta_input']['sections'][ THRIVE_HEADER_SECTION ] ) ? [ 'id' => 0 ] : $this->item['meta_input']['sections'][ THRIVE_HEADER_SECTION ];
			$footer = empty( $this->item['meta_input']['sections'][ THRIVE_FOOTER_SECTION ] ) ? [ 'id' => 0 ] : $this->item['meta_input']['sections'][ THRIVE_FOOTER_SECTION ];

			/* Set the hash id for header and footer meta - only if these are linked. if they are unlinked, leave the id as it is (zero) */
			if ( ! empty( $header['id'] ) ) {
				$this->set_section( THRIVE_HEADER_SECTION, $header['id'] );
			}
			if ( ! empty( $footer['id'] ) ) {
				$this->set_section( THRIVE_FOOTER_SECTION, $footer['id'] );
			}
			$hf = [
				THRIVE_HEADER_SECTION => $header,
				THRIVE_FOOTER_SECTION => $footer,
			];
		}

		return $hf;
	}

	/**
	 * Validate templates from the archive
	 *
	 * @return $this|Thrive_Transfer_Base
	 * @throws Exception
	 */
	public function validate() {
		parent::validate();

		foreach ( $this->data as $template ) {
			if ( empty( $template['meta_input'][ THRIVE_PRIMARY_TEMPLATE ] ) ) {
				/*  We need to make sure that each template is valid */
				throw new Exception( __( 'Invalid archive, unknown template found!', 'thrive-theme' ) );
			}
		}

		return $this;
	}

	/**
	 * Import templates from archive
	 *
	 * @param array $options
	 *
	 * @return array|Thrive_Template
	 * @throws Exception
	 */
	public function import( $options = [] ) {

		if ( empty( $options['update'] ) ) {
			/* don't import layout on template update */
			$this->controller->import_layout();
		}

		$this->controller
			->import_sections()
			->import_symbols()
			->import_images()
			->import_global_colors()
			->import_global_gradients()
			->import_global_styles();

		$this->data = Thrive_Transfer_Utils::prepare_content( $this->data, $this->archive_data->get_data() );

		$template = empty( $options['update'] ) ? $this->save() : $this->update( $options['update'] );

		/**
		 * Allow the builder website to modify the template after import
		 *
		 * @param Thrive_Template          $template
		 * @param array                    $options
		 * @param Thrive_Transfer_Template $this
		 */
		do_action( 'theme_transfer_template_after_import', $template, $options, $this );

		return $template;
	}

	/**
	 * Update existing template with the data from the archive
	 *
	 * @param int $template_id
	 *
	 * @return Thrive_Template
	 */
	public function update( $template_id = 0 ) {
		$item = $this->data[0];

		if ( is_numeric( $template_id ) ) {
			$template = new Thrive_Template( $template_id );
		}

		if ( ! empty( $template->ID ) ) {
			$old_id_hash = md5( $item['ID'] );

			$item['meta_input']['sections'] = $this->replace_sections_ids( $item['meta_input']['sections'] );

			unset(
				/* template already has settings for being default or not. */
				$item['meta_input']['default'],
				/* don't update layout, we keep the same one */
				$item['meta_input']['layout'],
				/* don't change template type on update */
				$item['meta_input'][ THRIVE_PRIMARY_TEMPLATE ], $item['meta_input'][ THRIVE_SECONDARY_TEMPLATE ], $item['meta_input'][ THRIVE_VARIABLE_TEMPLATE ]
			);


			/* Update the template with the values from the cloud archive */
			$template->update( $item['meta_input'] );

			/* We need to also update / reset the thumbnail for the template */
			$archive_data = $this->archive_data->get_data();

			if ( ! empty( $archive_data['images'][ $old_id_hash ] ) ) {
				Thrive_Transfer_Utils::save_thumbnail( $archive_data['images'][ $old_id_hash ], $template->ID );
			}

			Thrive_Transfer_Utils::replace_template_id( $template->ID );
		}

		return $template;
	}

	/**
	 * Save templates from the archive in the db
	 */
	public function save() {
		$layouts         = $this->archive_data['layout'];
		$saved_templates = [];

		foreach ( $this->data as $template ) {
			$old_id_hash = md5( $template['ID'] );
			unset( $template['ID'] );

			$template['post_status']            = 'publish';
			$template['meta_input']['layout']   = empty( $layouts[ $template['meta_input']['layout'] ] ) ? 0 : $layouts[ $template['meta_input']['layout'] ];
			$template['meta_input']['sections'] = $this->replace_sections_ids( $template['meta_input']['sections'] );

			/* Backwards compatibility for the templates which don't have tag -  Maybe we can remove this at some point*/
			if ( empty( $template['meta_input']['tag'] ) ) {
				$template['meta_input']['tag'] = uniqid();
			}

			/* Load all js modules for templates that do not have any js modules specified */
			if ( isset( $template['meta_input']['_tve_js_modules'] ) && empty( $template['meta_input']['_tve_js_modules'] ) ) {
				unset( $template['meta_input']['_tve_js_modules'] );
			}

			$template_id       = wp_insert_post( $template );
			$saved_templates[] = $template_id;

			$archive_data = $this->archive_data->get_data();

			if ( $template_id ) {
				$skin_id = empty( $archive_data['skin_id'] ) ? thrive_skin()->ID : $archive_data['skin_id'];
				wp_set_object_terms( $template_id, $skin_id, SKIN_TAXONOMY );
			}

			/* We need to also set the thumbnail for the template when it's imported */
			if ( ! empty( $archive_data['images'][ $old_id_hash ] ) ) {
				Thrive_Transfer_Utils::save_thumbnail( $archive_data['images'][ $old_id_hash ], $template_id );
			}

			Thrive_Transfer_Utils::replace_template_id( $template_id );
		}

		return $saved_templates;
	}

	/**
	 * Replace section ids from the templates meta with the new ones
	 *
	 * @param array $sections_content
	 *
	 * @return array|mixed|object
	 */
	public function replace_sections_ids( $sections_content ) {
		$content = json_encode( $sections_content );
		/* Symbols include headers & footers */
		$sections = array_merge( $this->archive_data['sections'], $this->archive_data['symbols'] );

		foreach ( $sections as $hash => $value ) {
			$content = str_replace( $hash, $value, $content );
		}

		return json_decode( $content, true );
	}
}
