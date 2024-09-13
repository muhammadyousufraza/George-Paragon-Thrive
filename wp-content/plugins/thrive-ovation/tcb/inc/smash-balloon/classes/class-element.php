<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Integrations\SmashBalloon;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Element
 *
 * @package TCB\Integrations\SmashBalloon
 */
class Element extends \TCB_Element_Abstract {
	/**
	 * @return string
	 */
	public function name() {
		return __( 'Smash Balloon Social Feed', 'thrive-cb' );
	}

	/**
	 * @return string
	 */
	public function icon() {
		return 'smash-balloon';
	}

	/**
	 *
	 * Get element alternate
	 *
	 * These are the different keywords to use on the elements' search bar.
	 *
	 * @return string
	 */
	public function alternate() {
		return 'smash, balloon, feed, feeds, instagram, facebook, youtube, reviews';
	}

	/**
	 * @return string
	 */
	public function identifier() {
		return Main::IDENTIFIER;
	}

	/**
	 * @return array
	 */
	public function own_components() {
		$list_feeds = [];

		$components = array(
			'smash-balloon-options'       => array(
				'config' => array(
					'SmashType' => array(
						'config'  => [
							'default' => '',
							'name'    => __( 'Type', 'thrive-cb' ),
							'options' => Main::sb_available_plugins(),
						],
						'extends' => 'Select',
					),
					'SmashFeed' => array(
						'config'  => [
							'default' => '',
							'name'    => __( 'Feed', 'thrive-cb' ),
							'options' => [],
						],
						'extends' => 'Select',
					),
				),
			),
			'layout'           => [ 'hidden' => true ],
			'responsive'       => [ 'hidden' => true ],
			'background'       => [ 'hidden' => true ],
			'typography'       => [ 'hidden' => true ],
			'borders'          => [ 'hidden' => true ],
			'animation'        => [ 'hidden' => true ],
			'shadow'           => [ 'hidden' => true ],
			'styles-templates' => [ 'hidden' => true ],
		);

		return $components;
	}

	/**
	 * Element category that will be displayed in the sidebar
	 *
	 * @return string
	 */
	public function category() {
		return 'Integrations';
	}

	/**
	 * Whether or not this element can be edited while under :hover state
	 *
	 * @return bool
	 */
	public function has_hover_state() {
		return false;
	}
}

return new Element( 'smash-balloon' );
