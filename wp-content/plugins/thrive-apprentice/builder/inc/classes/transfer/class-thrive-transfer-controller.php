<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Transfer_Controller {
	/**
	 * @var Thrive_Transfer_Images
	 */
	private $images;

	/**
	 * @var Thrive_Transfer_Colors
	 */
	private $global_colors;

	/**
	 * @var Thrive_Transfer_Template
	 */
	private $template;

	/**
	 * @var Thrive_Transfer_Styles
	 */
	private $global_styles;

	/**
	 * @var Thrive_Transfer_Gradient
	 */
	private $global_gradient;

	/**
	 * @var Thrive_Transfer_Typography
	 */
	private $typography;

	/**
	 * @var Thrive_Transfer_Symbols
	 */
	private $symbols;

	/**
	 * @var Thrive_Transfer_Section
	 */
	private $section;

	/**
	 * @var Thrive_Transfer_Layout
	 */
	private $layout;

	/**
	 * @var ZipArchive
	 */
	public $zip;

	/**
	 * Thrive_Transfer_Controller constructor.
	 *
	 * @param $zip
	 */
	public function __construct( $zip ) {
		$this->zip = $zip;

		$this->images          = new Thrive_Transfer_Images( $this );
		$this->global_colors   = new Thrive_Transfer_Colors( $this );
		$this->template        = new Thrive_Transfer_Template( $this );
		$this->global_styles   = new Thrive_Transfer_Styles( $this );
		$this->global_gradient = new Thrive_Transfer_Gradient( $this );
		$this->typography      = new Thrive_Transfer_Typography( $this );
		$this->symbols         = new Thrive_Transfer_Symbols( $this );
		$this->section         = new Thrive_Transfer_Section( $this );
		$this->layout          = new Thrive_Transfer_Layout( $this );
		$this->palettes        = new Thrive_Transfer_Palettes( $this );
	}

	/**
	 * Process layout for export
	 *
	 * @param Thrive_Transfer_Template $template
	 *
	 * @return $this
	 */
	public function process_layout( $template ) {
		$layout_id = $template->get_layout_id();

		/* Set the layout id hash on each template */
		//TODO check if this works with another layout on each template
		$template->set_layout( $layout_id );

		/* Process the layout only if it wasn't processed before */
		if ( ! $this->layout->exists( $layout_id ) ) {
			$this->layout->read( $layout_id )->add();
		}

		return $this;
	}

	/**
	 * Process all the linked sections from a template
	 *
	 * @param array $ids
	 *
	 * @return $this
	 */
	public function process_sections( $ids ) {
		foreach ( $ids as $section_id ) {
			/* Process the section only if it wasn't processed before */
			if ( ! $this->section->exists( $section_id ) ) {
				$this->section->read( $section_id, [ 'linked' => true ] )->add();
			}
		}

		return $this;
	}

	/**
	 * Process symbols from the exported item
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function process_symbols( &$content ) {
		$this->symbols
			->read_symbols( $content )
			->parse_dynamic_data()
			->add();

		return $this;
	}

	public function process_palettes() {
		$this->palettes->add();
	}

	/**
	 * Process headers and footers
	 *
	 * @param array $headers_footers
	 *
	 * @return $this
	 */
	public function process_headers_footers( $headers_footers ) {
		$this->symbols
			->read_hf( $headers_footers )
			->parse_dynamic_data()
			->add();

		return $this;
	}

	/**
	 * Process typographies
	 *
	 * @param array $typographies
	 *
	 * @return $this
	 */
	public function process_typographies( $typographies ) {
		$this->typography->read( $typographies );

		return $this;
	}

	/**
	 * Prepare templates for export
	 *
	 * @param array $ids
	 *
	 * @return $this
	 */
	public function process_templates( $ids ) {
		foreach ( $ids as $id ) {
			$this->template->read( $id )->add();
		}

		return $this;
	}

	/**
	 * Prepare global styles for export
	 *
	 * @param array $styles
	 *
	 * @return $this
	 */
	public function process_global_styles( $styles ) {
		$this->global_styles->read( $styles )->add();

		return $this;
	}

	/**
	 * Prepare images for export
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function process_images( &$content ) {
		$this->images->read( $content )->add();

		return $this;
	}

	/**
	 * Prepare section/template thumbnails for export.
	 *
	 * @param int $id
	 * @param string folder
	 */
	public function process_thumbnail( $id, $folder = THEME_UPLOADS_PREVIEW_SUB_DIR ) {
		$this->images->export_thumbnail( $id, $folder )->add();
	}

	/**
	 * Prepare global gradients for export
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function process_global_gradients( &$content ) {
		$this->global_gradient->read( $content )->add();

		return $this;
	}

	/**
	 * Prepare global colors for export
	 *
	 * @param string $content
	 *
	 * @return $this
	 */
	public function process_global_colors( &$content ) {
		$this->global_colors->read( $content )->add();

		return $this;
	}

	/**
	 * Import Templates
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_templates() {
		$this->template->validate()->import();

		return $this;
	}

	/**
	 * Import Layout
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_layout() {
		$this->layout->validate()->import();

		return $this;
	}

	/**
	 * Import sections
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_sections() {
		$this->section->validate()->import( [ 'linked' => true ] );

		return $this;
	}

	/**
	 * Import symbols
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_symbols() {
		$this->symbols->validate()->import();

		return $this;
	}

	/**
	 * Import images
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_images() {
		$this->images->validate()->import();

		return $this;
	}

	/**
	 * Import global colors from the archive
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_global_colors() {
		$this->global_colors->validate()->import();

		return $this;
	}

	/**
	 * Import Global Gradients
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_global_gradients() {
		$this->global_gradient->validate()->import();

		return $this;
	}

	/**
	 * Import typographies
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_typographies() {
		$this->typography->validate()->import();

		return $this;
	}

	/**
	 * @return $this
	 * @throws Exception
	 */
	public function import_palettes() {
		$this->palettes->validate()->import();

		return $this;
	}

	/**
	 * Import global styles
	 *
	 * @return $this
	 * @throws Exception
	 */
	public function import_global_styles() {
		$this->global_styles->validate()->import();

		return $this;
	}
}
