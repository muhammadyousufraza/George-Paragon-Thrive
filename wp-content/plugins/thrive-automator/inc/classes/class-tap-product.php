<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-automator
 */

namespace Thrive\Automator;

use TVE_Dash_Product_Abstract;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class TAP_Product extends TVE_Dash_Product_Abstract {
	protected $tag = TAP_TAG;

	protected $slug = TAP_DOMAIN;

	protected $title = TAP_PLUGIN_NAME;

	protected $productIds = array();

	protected $version = TAP_VERSION;

	protected $type = 'plugin';

	protected $activated = true; // its always active cuz it's free

	public function __construct( $data = array() ) {
		parent::__construct( $data );

		$this->logoUrl      = TAP_PLUGIN_URL . 'icons/logo-icon.png';
		$this->logoUrlWhite = TAP_PLUGIN_URL . 'icons/logo-icon.png';

		$this->description = __( 'Create smart automations that integrate your website with your favourite apps and plugins ', TAP_DOMAIN );

		$this->button = array(
			'active' => true,
			'url'    => admin_url( 'admin.php?page=thrive_automator' ),
			'label'  => __( 'Automator Dashboard', TAP_DOMAIN ),
		);

		$this->moreLinks = array(
			'tutorials' => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-graduation-cap',
				'href'       => 'https://help.thrivethemes.com/en/collections/3055941-thrive-automator',
				'target'     => '_blank',
				'text'       => __( 'Tutorials', TAP_DOMAIN ),
			),
			'support'   => array(
				'class'      => '',
				'icon_class' => 'tvd-icon-life-bouy',
				'href'       => 'https://thrivethemes.com/support/',
				'target'     => '_blank',
				'text'       => __( 'Support', TAP_DOMAIN ),
			),
		);

	}

	public static function reset_plugin() {
		Thrive_Reset::factory_reset();
	}
}
