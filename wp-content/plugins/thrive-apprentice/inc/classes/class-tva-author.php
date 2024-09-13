<?php

/**
 * Class TVA_Author
 */
class TVA_Author implements JsonSerializable {

	/**
	 * Course meta term name
	 */
	const COURSE_TERM_NAME = 'tva_author';

	/**
	 * @var WP_User
	 */
	private $_user;

	/**
	 * @var int
	 */
	private $_course_id;

	/**
	 * @var array for default author's props
	 */
	private $_default_data = array(
		'ID'               => null,
		'biography_type'   => 'wordpress_bio',
		'custom_biography' => '',
		'avatar_url'       => null,
		'name'             => null,
		'username'         => null,
		'email'            => null,
	);

	/**
	 * Array of user's data for specific course
	 * - value of term meta for course author
	 *
	 * @var array
	 */
	private $_details = array();

	/**
	 * TVA_Author constructor.
	 *
	 * @param null|int|WP_User $user
	 * @param int              $course_id
	 */
	public function __construct( $user = null, $course_id = null ) {

		if ( $course_id ) {
			$this->_course_id = $course_id;
		}

		if ( ! $user ) { //no user has been sent then read it from meta
			$details = $this->get_details();
			$user    = ! empty( $details['ID'] ) ? (int) $details['ID'] : 0;
		}

		if ( $user instanceof WP_User ) {
			$this->_user = $user;
		}

		if ( ! $this->_user ) {
			$this->_user = get_user_by( 'ID', $user );
		}
	}

	/**
	 * Reads the term meta value for course author
	 *
	 * @return array
	 */
	public function get_details() {

		if ( empty( $this->_details ) ) {

			$data = get_term_meta( $this->_course_id, self::COURSE_TERM_NAME, true );

			if ( ! empty( $data['ID'] ) ) {
				$this->_details['ID'] = $data['ID'];
			}

			if ( ! empty( $data['url'] ) ) {
				$this->_details['avatar_url'] = $data['url'];
			}

			if ( isset( $data['avatar_url'] ) ) {
				$this->_details['avatar_url'] = $data['avatar_url'];
			}

			if ( ! empty( $data['biography_type'] ) ) {
				$this->_details['biography_type'] = $data['biography_type'];
			}

			if ( ! empty( $data['custom_biography'] ) ) {
				$this->_details['custom_biography'] = $data['custom_biography'];
			}
		}

		return $this->_details;
	}

	/**
	 * Set details which would be saved on course term meta
	 *
	 * @param $details
	 */
	public function set_details( $details ) {

		$this->_details = array_merge( $this->get_details(), $details );
	}

	/**
	 * Gets avatar URL
	 * - empty string might be returned
	 *
	 * @return string
	 */
	public function get_avatar() {

		return ! empty( $this->_details['avatar_url'] ) ? tva_fix_gravatar_url( $this->_details['avatar_url'] ) : '';
	}

	/**
	 * Gets wp user instance
	 *
	 * @return WP_User
	 */
	public function get_user() {

		return $this->_user instanceof WP_User ? $this->_user : new WP_User( 0 );
	}

	/**
	 * @return string
	 */
	public function get_bio() {

		if ( $this->has_custom_bio() ) {
			$bio = ! empty( $this->_details['custom_biography'] ) ? $this->_details['custom_biography'] : '';
		} else {
			$bio = $this->_user instanceof WP_User ? $this->_user->description : '';
		}

		return $bio;
	}

	/**
	 * @return bool
	 */
	public function has_custom_bio() {

		return $this->_get_bio_type() === 'custom_bio';
	}

	/**
	 * Gets what bio type current author has
	 * - custom type
	 * - wp type
	 * - default wp type is returned
	 *
	 * @return string
	 */
	protected function _get_bio_type() {

		return ! empty( $this->_details['biography_type'] ) ? $this->_details['biography_type'] : 'wordpress_bio';
	}

	/**
	 * @return array
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {

		$user_data = array();

		if ( true === $this->_user instanceof WP_User ) {
			$user_data = array(
				'ID'         => $this->_user->ID,
				'avatar_url' => get_avatar_url( $this->_user->ID, array( 'size' => 256 ) ),
				'name'       => $this->_user->display_name,
				'username'   => $this->_user->user_email,
				'email'      => $this->_user->user_login,
			);
		}

		return array_merge( $this->_default_data, $user_data, $this->get_details() );
	}
}
