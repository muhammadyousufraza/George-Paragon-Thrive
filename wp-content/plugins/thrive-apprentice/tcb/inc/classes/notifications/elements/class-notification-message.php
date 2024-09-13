<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-visual-editor
 */

namespace TCB\Notifications;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class Notification_Message
 *
 * @package TCB\Notifications
 */
class Notification_Message extends \TCB_Element_Abstract {

	/**
	 * Name of the element
	 *
	 * @return string
	 */
	public function name() {
		return __( 'Notification message', 'thrive-cb' );
	}

	/**
	 * WordPress element identifier
	 *
	 * @return string
	 */
	public function identifier() {
		return '.notifications-content .thrv-notification_message';
	}

	public function own_components() {
		$components = $this->general_components();

		/* Remove suffix so that the settings apply on the correct element*/
		foreach ( $components['typography']['config'] as $control => $config ) {
			if ( in_array( $control, [ 'css_suffix', 'css_prefix' ] ) ) {
				continue;
			}

			$components['typography']['config'][ $control ]['css_suffix'] = [ '' ];
		}

		$components['notification_message'] = $components['typography'];
		$components['typography']           = [ 'hidden' => true ];

		return $components;
	}

	public function category() {
		return static::get_thrive_advanced_label();
	}

	public function has_hover_state() {
		return true;
	}

	/**
	 * Return icon class needed for display in menu
	 *
	 * @return string
	 */
	public function icon() {
		return 'notification-message';
	}
}

return new Notification_Message( 'notification_message' );
