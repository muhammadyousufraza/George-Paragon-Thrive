<?php

/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */

namespace TVA\Assessments;

use Thrive_Dash_List_Connection_Abstract;
use Thrive_Dash_List_Manager;
use TVA\Assessments\Grading\Category as Grading_Category;
use TVA\TVA_Singleton_Interface;
use TVA_Const;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Class TVA_Assessment_Settings
 *
 * @property string upload_connection_key
 * @property string folder_id
 */
class TVA_Assessment_Settings implements TVA_Singleton_Interface {

	/**
	 * @var self
	 */
	protected static $_instance;

	/**
	 * Holds settings data
	 *
	 * @var array
	 */
	protected $_data = [];

	protected $_defaults = [
		'upload_create_folders' => false,
		'upload_filename'       => 'assessment_{course}_{assessment}_{date}_{time}',
		'upload_connection_key' => '',
	];

	private function __construct() {

		$this->set_data( $this->_get_option() );

		$this->_hooks();
	}

	/**
	 * Gets $key from local $_data
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {

		$value = null;

		if ( isset( $this->_data[ $key ] ) ) {
			$value = $this->_data[ $key ];
		}

		return $value;
	}

	/**
	 * Set value at key in local $_data
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public function __set( $key, $value ) {

		$this->_data[ $key ] = $value;
	}

	/**
	 * Singleton instance
	 *
	 * @return TVA_Assessment_Settings
	 */
	public static function get_instance() {

		if ( ! self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Actions and filters should be placed here
	 *
	 * @return void
	 */
	public function _hooks() {
		add_filter( 'tva_admin_localize', [ $this, 'get_admin_data_localization' ] );
		add_filter( 'tve_dash_disconnect_' . $this->upload_connection_key, [ $this, 'unset_upload_connection' ] );
	}

	/**
	 * Removes saved key if connection is deleted
	 *
	 * @return true
	 */
	public function unset_upload_connection() {
		$this->upload_connection_key = '';
		$this->save();

		return true;
	}

	/**
	 * Returns an instance of a connection selected by the user
	 *
	 * @return Thrive_Dash_List_Connection_Abstract|null
	 */
	public function get_upload_connection() {

		return Thrive_Dash_List_Manager::connection_instance( $this->upload_connection_key );
	}

	/**
	 * Returns key value array of connected storage services
	 *
	 * @return array
	 */
	public function connected_services() {
		$services = [];

		foreach ( Thrive_Dash_List_Manager::get_available_apis( true, [ 'include_types' => [ 'storage' ] ] ) as $key => $connection ) {
			$credentials = $connection->get_credentials();

			$services[ $key ] = [
				'name'      => $connection->get_title(),
				'client_id' => isset( $credentials['client_id'] ) ? $credentials['client_id'] : '',
			];
		}

		return $services;
	}

	/**
	 * Localizes settings data for admin
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function get_admin_data_localization( $data = [] ) {

		$data['assessments'] = [
			'settings'           => $this->_data,
			'upload_connections' => $this->connected_services(),
			'can_upload'         => $this->can_upload_assessments(),
			'statuses'           => [
				'pending' => TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT,
				'passed'  => TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED,
				'failed'  => TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED,
			],
			'status_label'       => [
				TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT  => __( 'Pending', 'thrive-apprentice' ),
				TVA_Const::ASSESSMENT_STATUS_COMPLETED_PASSED    => __( 'Passed', 'thrive-apprentice' ),
				TVA_Const::ASSESSMENT_STATUS_COMPLETED_FAILED    => __( 'Failed', 'thrive-apprentice' ),
				TVA_Const::ASSESSMENT_STATUS_REPLACED_ASSESSMENT => __( 'Replaced', 'thrive-apprentice' ),
			],
			'has_submissions'    => TVA_User_Assessment::has_submissions(),
			'default_categories' => Grading_Category::get_default_categories(),
			'pending_count'      => Inbox::get_assessment_count(
				[
					'assessment_status' => TVA_Const::ASSESSMENT_STATUS_PENDING_ASSESSMENT,
				]
			),
		];

		return $data;
	}

	/**
	 * @return array
	 */
	protected function _get_option() {
		return get_option( 'tva_assessment_settings', $this->_defaults );
	}

	/**
	 * Set data from array
	 *
	 * @param array $data
	 */
	public function set_data( $data ) {

		if ( is_array( $data ) ) {
			foreach ( $data as $field => $value ) {
				$this->{$field} = $value;
			}
		}

		return $this;
	}

	/**
	 * Returns true if the system allows assessments to be uploaded to storage API connections
	 * Example: dropbox
	 *
	 * @return bool
	 */
	public function can_upload_assessments() {
		return ! empty( $this->_data['upload_connection_key'] ) && ! empty( $this->_data['folder_id'] );
	}

	/**
	 * Returns the upload file name with masks.
	 * Needed when uploading a file in front-end
	 *
	 * @return string
	 */
	public function get_upload_filename() {
		return $this->_data['upload_filename'];
	}

	/**
	 * @return bool
	 */
	public function save() {
		return update_option( 'tva_assessment_settings', $this->_data );
	}
}
