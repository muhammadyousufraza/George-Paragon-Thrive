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
 * Class Thrive_Transfer_Skin
 */
class Thrive_Transfer_Skin extends Thrive_Transfer_Base {

	/**
	 * Allows dynamic implementation of Skin transfers
	 *
	 * @var string
	 */
	protected $skin_class = Thrive_Skin::class;

	/**
	 * Json filename where to keep the data for the templates
	 *
	 * @var string
	 */
	public static $file = 'skins.json';

	/**
	 * Element key in the archive
	 *
	 * @var string
	 */
	protected $tag = 'skin';

	/**
	 * Skin object to handle skin related actions
	 *
	 * @var Thrive_Skin
	 */
	protected $skin;

	/**
	 * Read the skin data and all that is related with the skin
	 *
	 * @param int $id
	 *
	 * @return $this
	 */
	public function read( $id ) {
		$this->skin = thrive_skin( $id );
		$this->item = $this->skin->export();

		$content = '';
		$this->controller
			->process_templates( $this->get_templates() )
			->process_sections( $this->get_sections() )
			->process_typographies( $this->skin->get_typographies( 'object' ) )
			->process_global_styles( $this->skin->get_global_styles() )
			->process_symbols( $content )
			->process_palettes();

		$this->replace_dynamic_items();

		return $this;
	}

	/**
	 * Get templates for export
	 *
	 * @return mixed|void
	 */
	public function get_templates() {
		return apply_filters(
			'ttb_skin_export_templates',
			$this->skin->get_templates( 'ids', false, [], [
				'order'   => 'ASC',
				'orderby' => 'ID',
			] ) // make sure templates will have the same order when imported
		);
	}

	/**
	 * Get skin sections ids
	 *
	 * @return array
	 */
	public function get_sections() {
		return ( defined( 'THRIVE_THEME_SKIN_SECTIONS' ) && THRIVE_THEME_SKIN_SECTIONS ) ? thrive_skin()->get_sections( [], 'ids' ) : [];
	}

	/**
	 * Replace ids from the skin data with the corresponding hash
	 */
	public function replace_dynamic_items() {
		$layouts = $this->archive_data['layout'];

		foreach ( $layouts as $hash => $layout ) {
			if ( $this->item['term_meta']['default_layout'] === $layout['ID'] ) {
				$this->item['term_meta']['default_layout'] = $hash;
			}
		}
	}

	/**
	 * Add skin to the archive data
	 */
	public function add() {
		$this->archive_data['skin'] = $this->item;
	}

	/**
	 * Import skin from the archive
	 *
	 * @throws Exception
	 */
	public function import() {
		$this->save();

		$this->controller
			->import_palettes()
			->import_templates()
			->import_typographies()
			->import_global_styles()
			->import_symbols();

		$skin_term = $this->replace_data();

		if ( $skin_term && $skin_term->is_active ) {
			/* if we import a skin that becomes active, we also generate the css file */
			$this->new_skin( $skin_term->term_id )->generate_style_file();
		}

		return $skin_term;
	}

	/**
	 * Instantiate a new skin and return it. Uses $this->skin_class to instantiate
	 *
	 * @param int $id
	 *
	 * @return Thrive_Skin
	 */
	protected function new_skin( $id ) {
		return new $this->skin_class( $id );
	}

	/**
	 * Replace dynamic data at import
	 *
	 * @return array|false|Thrive_Skin|WP_Term
	 */
	public function replace_data() {
		/* Symbols include headers & footers */
		$items = array_merge( $this->archive_data['symbols'], $this->archive_data['layout'] );

		/** @var Thrive_Skin $imported_skin */
		$skin = $this->new_skin( $this->archive_data['skin_id'] );
		foreach ( $this->skin_static_call( 'meta_fields' ) as $meta_key => $default_value ) {
			$default_meta = $skin->get_meta( $meta_key );

			if ( ! empty( $default_meta ) ) {

				if ( is_array( $default_meta ) ) {
					$skin->set_meta( $meta_key, $default_meta );
				} elseif ( ! empty( $items[ $default_meta ] ) ) {
					$skin->set_meta( $meta_key, $items[ $default_meta ] );
				}
			}
		}

		$imported_skin              = Thrive_Skin_Taxonomy::get_skin_by_id( $this->archive_data['skin_id'] );
		$imported_skin->is_imported = true;
		$imported_skin->is_active   = get_term_meta( $this->archive_data['skin_id'], Thrive_Skin::SKIN_META_ACTIVE, true );

		$imported_skin->preview_url = add_query_arg( [
			THRIVE_NO_BAR       => 1,
			THRIVE_SKIN_PREVIEW => $this->archive_data['skin_id'],
		], home_url() );

		if ( ! empty( $this->data['term_meta']['tag'] ) ) {
			$imported_skin->tag = $this->data['term_meta']['tag'];
		}

		return $imported_skin;
	}

	/**
	 * Save skin in the db
	 *
	 * @throws Exception
	 */
	public function save() {
		if ( ! empty( $this->data ) ) {
			$name = $this->skin_static_call( 'generate_unique_name', $this->data['name'] );
			/**
			 * Change imported skin name before saving as template
			 */
			$name          = apply_filters( 'thrive_theme_imported_skin_name', $name );
			$new_skin_term = wp_insert_term( $name, SKIN_TAXONOMY );

			if ( is_wp_error( $new_skin_term ) ) {
				throw new Exception( 'The theme import failed' );
			}

			$skin_id  = $new_skin_term['term_id'];
			$new_skin = $this->new_skin( $skin_id );

			foreach ( $this->skin_static_call( 'meta_fields' ) as $meta_key => $default_value ) {
				if ( ! empty( $this->data['term_meta'][ $meta_key ] ) ) {
					$new_skin->set_meta( $meta_key, $this->data['term_meta'][ $meta_key ] );
				}
			}

			if ( empty( thrive_palettes()->get_master_hsl() ) && ! empty( $this->data['term_meta']['palettes_v2'] ) ) {
				$active_id = $this->data['term_meta']['palettes_v2']['active_id'];
				thrive_palettes()->update_master_hsl( $this->data['term_meta']['palettes_v2']['palettes'][ $active_id ]['modified_hsl'] );
			}

			$this->maybe_make_active( $new_skin );

		} else {
			$active_skin = thrive_skin();
			if ( ! empty( $active_skin ) ) {
				$skin_id = $active_skin->ID;
			}
		}

		$this->archive_data['skin_id'] = $skin_id;
	}

	/**
	 * Check to see if the imported skin needs to be set as active and set it
	 *
	 * @param Thrive_Skin $skin
	 */
	protected function maybe_make_active( $skin ) {
		//At first is_active is 0 when a skin is just imported
		$is_active = 0;

		//If the active skin is the default one -> then we will make the just imported skin to be active
		if ( thrive_skin()->get_tag() === 'default' ) {
			thrive_skin()->set_meta( Thrive_Skin::SKIN_META_ACTIVE, 0 );
			$is_active = 1;
		}

		$skin->set_meta( Thrive_Skin::SKIN_META_ACTIVE, $is_active );
	}

	/**
	 * Forward a static call to this objects' skin class. Implemented like this because in php5.6 you cannot use the following:
	 *      $this->skin_class::meta_fields() - this will only work in php7 (uniform variable syntax).
	 *
	 * @param string $method_name static method to be called
	 * @param mixed  ...$params   optional parameters
	 *
	 * @return mixed
	 */
	protected function skin_static_call( $method_name, ...$params ) {
		return forward_static_call_array( [ $this->skin_class, $method_name ], $params );
	}
}
