<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\TTB;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Legacy_Skin
 *
 * Represents the legacy skin/design in a similar manner
 *
 * @package TVA\TTB
 *
 */
class Legacy_Skin extends Skin {

	public function __construct() {
		// nothing needed here
	}

	/**
	 * Always instantiate and return
	 *
	 * @return static
	 */
	public static function instance() {
		return new static();
	}

	/**
	 * Serialization needed for admin CRUD
	 *
	 * @return array|mixed
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		$active = Main::has_legacy_design() && ! Main::uses_builder_templates();

		return [
			'term_id'             => - 1,
			'name'                => 'Legacy',
			'description'         => '',
			'parent'              => 0,
			'is_active'           => $active,
			'legacy'              => 1,
			'is_assessment_ready' => 1,
			'thumb'               => \TVA_Const::plugin_url( 'img/default-skin.jpg' ),
			'inherit_typography'  => true,
		];
	}
}
