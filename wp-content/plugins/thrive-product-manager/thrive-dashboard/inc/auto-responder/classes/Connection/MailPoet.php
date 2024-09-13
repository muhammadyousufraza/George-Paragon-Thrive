<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

use MailPoet\API\API;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class Thrive_Dash_List_Connection_MailPoet extends Thrive_Dash_List_Connection_Abstract {
	/**
	 * Return the connection type
	 *
	 * @return String
	 */
	public static function get_type() {
		return 'autoresponder';
	}

	/**
	 * Return the connection email merge tag
	 *
	 * @return String
	 */
	public static function get_email_merge_tag() {
		/**
		 * Mailpoet 3 changed the shortcodes from user:email to subscriber:email
		 */
		if ( defined( 'MAILPOET_VERSION' ) && version_compare( MAILPOET_VERSION, '3', '>' ) ) {
			return '[subscriber:email]';
		}

		return '[user:email]';
	}

	/**
	 * @return string
	 */
	public function get_title() {
		return 'MailPoet';
	}

	/**
	 * check whether or not the MailPoet plugin is installed
	 */
	public function pluginInstalled() {
		$installed = array();
		if ( class_exists( 'MailPoet\Config\Initializer', false ) ) {
			$installed[] = 3;
		}

		if ( class_exists( 'WYSIJA' ) ) {
			$installed[] = 2;
		}

		return $installed;
	}

	/**
	 * output the setup form html
	 *
	 * @return void
	 */
	public function output_setup_form() {
		$this->output_controls_html( 'mailpoet' );
	}

	/**
	 * just save the key in the database
	 *
	 * @return mixed|void
	 */
	public function read_credentials() {
		if ( ! $this->pluginInstalled() ) {
			return $this->error( __( 'MailPoet plugin must be installed and activated.', 'thrive-dash' ) );
		}

		$this->set_credentials( $this->post( 'connection' ) );

		$result = $this->test_connection();

		if ( $result !== true ) {
			return $this->error( '<strong>' . $result . '</strong>)' );
		}
		/**
		 * finally, save the connection details
		 */
		$this->save();

		return true;
	}

	/**
	 * test if a connection can be made to the service using the stored credentials
	 *
	 * @return bool|string true for success or error message for failure
	 */
	public function test_connection() {
		if ( ! $this->pluginInstalled() ) {
			return __( 'At least one MailPoet plugin must be installed and activated.', 'thrive-dash' );
		}

		return true;
	}

	/**
	 * add a contact to a list
	 *
	 * @param mixed $list_identifier
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public function add_subscriber( $list_identifier, $arguments ) {
		if ( ! $this->pluginInstalled() ) {
			return __( 'MailPoet plugin is not installed / activated', 'thrive-dash' );
		}

		list( $firstname, $lastname ) = $this->get_name_parts( $arguments['name'] );

		$credentials = $this->get_credentials();

		if ( ! isset( $credentials['version'] ) || $credentials['version'] == 2 ) {
			$user_data = array(
				'email'     => $arguments['email'],
				'firstname' => $firstname,
				'lastname'  => $lastname,
			);

			$data_subscriber = array(
				'user'      => $user_data,
				'user_list' => array( 'list_ids' => array( $list_identifier ) ),
			);

			/** @var WYSIJA_help_user $user_helper */
			$user_helper = WYSIJA::get( 'user', 'helper' );
			$result      = $user_helper->addSubscriber( $data_subscriber );
			if ( $result === false ) {
				$messages = $user_helper->getMsgs();
				if ( isset( $messages['xdetailed-errors'] ) ) {
					return implode( '<br><br>', $messages['xdetailed-errors'] );
				} elseif ( isset( $messages['error'] ) ) {
					return implode( '<br><br>', $messages['error'] );
				}

				return __( 'Subscriber could not be saved', 'thrive-dash' );
			}
		} else {
			$user_data = array(
				'email'      => $arguments['email'],
				'first_name' => $firstname,
				'last_name'  => $lastname,
			);

			// Compatibility with latest version 3.21.0+
			if ( class_exists( 'MailPoet\API\API' ) ) {
				$mailpoet   = new API();
				$errors     = array();
				$subscriber = array();

				// Get subscriber
				try {
					$subscriber = $mailpoet::MP( 'v1' )->getSubscriber( $arguments['email'] );
				} catch ( Exception $exception ) {
					$errors[] = $exception->getMessage();
				}

				// Create subscriber if not exists
				if ( is_array( $subscriber ) && empty( $subscriber['id'] ) ) {
					try {
						$subscriber = $mailpoet::MP( 'v1' )->addSubscriber( $user_data, array(), array() );
					} catch ( Exception $exception ) {
						$errors[] = $exception->getMessage();
					}
				}

				// Add subscriber to list
				if ( is_array( $subscriber ) && ! empty( $subscriber['email'] ) ) {
					try {
						$mailpoet::MP( 'v1' )->subscribeToList( $subscriber['email'], $list_identifier, $user_data );

						return true;
					} catch ( Exception $exception ) {
						return $exception->getMessage();
					}
				}

				if ( $errors ) {
					return implode( '<br/>', $errors );
				}

				return false;
			}

			if ( ! class_exists( 'MailPoet\Models\Subscriber' ) ) {
				$this->_error = __( 'No MailPoet plugin could be found.', 'thrive-dash' );

				return false;
			}

			$result = call_user_func( array(
				'MailPoet\Models\Subscriber',
				'subscribe'
			), $user_data, array( $list_identifier ) );

			if ( $result->getErrors() ) {
				return implode( '<br><br>', $result->getErrors() );
			}
		}

		return true;

	}

	/**
	 * instantiate the API code required for this connection
	 *
	 * @return mixed
	 */
	protected function get_api_instance() {
		// no API instance needed here
		return null;
	}

	/**
	 * get all Subscriber Lists from this API service
	 *
	 * @return array|bool
	 */
	protected function _get_lists() {
		if ( ! $this->pluginInstalled() ) {
			$this->_error = __( 'No MailPoet plugin could be found.', 'thrive-dash' );

			return false;
		}

		$lists = array();

		$credentials = $this->get_credentials();

		// Version 2 check [DB option] that uses different classes
		if ( ! isset( $credentials['version'] ) || 2 === (int) $credentials['version'] ) {
			$model_list = WYSIJA::get( 'list', 'model' );
			$lists      = $model_list->get( array( 'name', 'list_id' ), array( 'is_enabled' => 1 ) );
			foreach ( $lists as $i => $list ) {
				$lists[ $i ]['id'] = $list['list_id'];
			}
		} else {

			// Compatibility with latest version 3.21.0+
			if ( class_exists( 'MailPoet\API\API' ) ) {

				$mailpoet           = new API();
				$subscription_lists = $mailpoet::MP( 'v1' )->getLists();

				if ( is_array( $subscription_lists ) && ! empty( $subscription_lists ) ) {
					foreach ( $subscription_lists as $list ) {
						$lists [] = array(
							'id'   => $list['id'],
							'name' => $list['name'],
						);
					}
				}

				return $lists;
			}

			if ( ! class_exists( 'MailPoet\Models\Segment' ) ) {
				$this->_error = __( 'No MailPoet plugin could be found.', 'thrive-dash' );

				return false;
			}

			$segments = call_user_func( array(
				'MailPoet\Models\Segment',
				'getSegmentsWithSubscriberCount'
			), 'default' );

			if ( ! empty( $segments ) ) {
				foreach ( $segments as $segment ) {
					$lists [] = array(
						'id'   => $segment['id'],
						'name' => $segment['name'],
					);
				}
			}
		}

		return $lists;
	}
}
