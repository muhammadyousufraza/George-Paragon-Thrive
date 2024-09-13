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
 * Class Thrive_Transfer_Section
 */
class Thrive_Transfer_Section extends Thrive_Transfer_Base {
	/**
	 * Json filename where to keep the data for the sections
	 *
	 * @var string
	 */
	public static $file = 'sections.json';

	/**
	 * @var string
	 */
	public static $css_export_placeholder = '{{css_export_placeholder}}';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag = 'section';

	/**
	 * Section id
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Sections map used at import in order to replace the identifiers in html / css
	 *
	 * @var array
	 */
	private $map = [];

	/**
	 * Read the sections to be exported
	 *
	 * @param int   $section_id
	 * @param array $options
	 *
	 * @return $this
	 */
	public function read( $section_id, $options = [] ) {
		$this
			->set_options( $options )
			->set_item( $section_id )
			->set_id( $section_id );

		$content = json_encode( $this->item );

		$this->controller
			->process_symbols( $content )
			->process_images( $content )
			->process_global_colors( $content )
			->process_global_gradients( $content )
			->process_thumbnail( $this->id );

		$content = $this->replace_section_data( $content );

		$this->item = json_decode( $content, true );

		return $this;
	}

	/**
	 * Replace section id with a hash
	 * Used at export
	 *
	 * @param string $content
	 *
	 * @return mixed
	 */
	public function replace_section_data( $content ) {

		$content = Thrive_Transfer_Utils::replace_site_url( $content );

		//handle the case when we want the section to be unlinked
		if ( empty( $this->options['linked'] ) ) {
			$section = new Thrive_Section( $this->id );

			//mimic the behaviour of a section when it's unlinked. Replace the selector with a css placeholder which at import will be replaced with the body class
			$content = str_replace( $section->selector(), static::$css_export_placeholder, $content );
		} else {
			//when we want the section linked, we just replace the id with a hash and this will be replaced with the new id at import
			$hash    = md5( $this->id );
			$content = str_replace( ".thrive-section-{$this->id}", ".thrive-section-{$hash}", $content );
		}

		return $content;
	}

	/**
	 * Set section data for export
	 *
	 * @param int $section_id
	 *
	 * @return $this
	 */
	public function set_item( $section_id ) {
		$section = new Thrive_Section( $section_id );

		$this->item = $section->export();

		return $this;
	}

	/**
	 * Set section id
	 *
	 * @param int $id
	 *
	 * @return $this
	 */
	public function set_id( $id ) {
		$this->id = $id;

		return $this;
	}

	/**
	 * Add section to the archive data
	 */
	public function add() {
		$sections                         = $this->archive_data[ $this->tag ];
		$sections[ md5( $this->id ) ]     = $this->item;
		$this->archive_data[ $this->tag ] = $sections;

		return $this;
	}

	/**
	 * Import sections
	 *
	 * @throws Exception
	 */

	/**
	 * Import sections
	 *
	 * @param array $options
	 *
	 * @return array|Thrive_Section
	 * @throws Exception
	 */
	public function import( $options = [] ) {
		$imported_section = [];
		$this->set_options( $options );
		if ( ! empty( $this->data ) ) {
			$this->controller
				->import_symbols()
				->import_images()
				->import_global_colors()
				->import_global_gradients()
				->import_global_styles();

			$this->data       = Thrive_Transfer_Utils::prepare_content( $this->data, $this->archive_data->get_data() );
			$imported_section = $this->save();
		}

		$this->archive_data['sections'] = $this->map;

		return $imported_section;
	}

	/**
	 * Save section from import
	 *
	 * @return Thrive_Section|null
	 */
	public function save() {
		$new_section  = null;
		$archive_data = $this->archive_data->get_data();

		foreach ( $this->data as $id_hash => $section ) {
			if ( ! $this->already_saved( $id_hash ) ) {
				$section_id = $section['ID'];

				//handle the case where we want the section to be unlinked after import
				if ( empty( $this->options['linked'] ) ) {
					/* store these in the section in order to have them for unlinked sections */
					if ( ! empty( $archive_data['images'][ $id_hash ] ) ) {
						$section['meta_input']['cloud_thumbnail'] = $archive_data['images'][ $id_hash ];
					}
					$section['meta_input']['cloud_id_hash'] = $id_hash;

					$new_section = new Thrive_Section( 0, $section['meta_input'] );

					if ( empty( $this->options['original_css'] ) ) {
						$body_class       = $new_section->template->body_class( false, 'string', true );
						$section_selector = $new_section->selector();

						$new_section->replace_data_ids( static::$css_export_placeholder, $body_class . ' ' . $section_selector );
					}
				} else {
					unset( $section['ID'] );
					$section_id = wp_insert_post( $section );

					$new_section = new Thrive_Section( $section_id );

					$new_section->replace_data_ids( $id_hash, $section_id );
					$new_section->set_meta( THRIVE_EXPORT_ID, Thrive_Utils::get_unique_id() );
					/* also replace the placeholders, all selectors contain `static::$css_export_placeholder` at this point */
					$new_section->replace_data_ids( static::$css_export_placeholder, $new_section->selector() );

					/* We need to also set the thumbnail for the section when it's imported */
					if ( ! empty( $archive_data['images'][ $id_hash ] ) ) {
						Thrive_Transfer_Utils::save_thumbnail( $archive_data['images'][ $id_hash ], $section_id );
					}

					/* let's make sure we have a skin id in the archive and than assign section to skin */
					if ( isset( $archive_data['skin_id'] ) ) {
						wp_set_object_terms( $section_id, $archive_data['skin_id'], SKIN_TAXONOMY );
					}
				}

				$this->map[ $id_hash ] = $section_id;
			}
		}

		return $new_section;
	}
}
