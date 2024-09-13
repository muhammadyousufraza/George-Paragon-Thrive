<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class ApiVideos {

	/**
	 * @var string
	 */
	private $_ttw_api_url_transient = 'ttw_api_urls';

	/**
	 * @var string
	 */
	private $_ttw_url;

	/**
	 * Transient lifetime [1 day in seconds]
	 *
	 * @var int
	 */
	protected $_cache_life_time = 86400;

	/**
	 * Used for obfuscation
	 *
	 * @var array
	 */
	private $_randomize
		= array(
			'a'  => 'F',
			'b'  => 'g',
			'c'  => 'R',
			'd'  => '6j',
			'e'  => 'k9t',
			'f'  => '#U',
			'g'  => 'x',
			'h'  => 'E',
			'i'  => '_',
			'j'  => '6',
			'k'  => '^',
			'l'  => 'Y',
			'm'  => '7hI',
			'n'  => 'm',
			'o'  => 'pI',
			'u'  => '5',
			'1'  => '2W7',
			'2'  => 'g',
			'9'  => 'T',
			'5'  => '3',
			':'  => 'p',
			'/'  => 'u',
			't'  => 'I',
			'p'  => 'o',
			'x'  => 'a',
			'y'  => 'e',
			'z'  => 'i',
			'w'  => 'u',
			'6'  => 'h',
			'U'  => 't',
			'89' => '/',
		);

	/**
	 * Used in case of TTW API call failed [/api_videos endpoint]
	 *
	 * @var array
	 */
	private $_fallback_urls
		= array(
			'mailchimp'        => 'ndkVpoJCffU',
			'aweber'           => 'lBybVnifWw4',
			'getresponse'      => 'G0IMbKP1Otw',
			'mailpoet'         => 'bkVO6nqyClA',
			'wordpress'        => 'KMqwr6OT3DA',
			'ontraport'        => '6AwBXF8w85o',
			'everwebinar'      => 'Oy7AutRjWHE',
			'icontact'         => 'sjWGb3UdvN4',
			'convertkit'       => 'nPDX_a7_pAM',
			'activecampaign'   => 'z27CqJQtrvQ',
			'sendy'            => 'jHP6u3rqoF0',
			'drip'             => 'MnujttxYH-M',
			'constantcontact'  => 'a1y_GJcUwO4',
			'madmimi'          => 'OXQzK9uSzsA',
			'webinarjamstudio' => 'y7Rz0l794DE',
			'gotowebinar'      => '2NkRXez97p0',
			'hubspot'          => 'gqjp4-hTJdc',
			'postmark'         => 'k8l-KeObrwk',
			'infusionsoft'     => 'Ak4tLh29aC4',
			'recaptcha'        => '4LM0cIIhOVA',
			'sparkpost'        => 'HCsuzWcYq4I',
			'mailgun'          => 'DBNW6hGWYyc',
			'awsses'           => 'eC35eUXqlHw',
			'mailerlite'       => 'OKigVCbG0YE',
			'campaignmonitor'  => 'wbPX2bXUNxA',
			'facebook'         => 'jR4tBDEuwE4',
			'google'           => 'YZ2eeWBJQ5w',
			'twitter'          => '9_pkwfTrTPc',
			'mailrelay'        => 'gLlRZ1wdIjM',
			'sendgrid'         => 'sLfWAgEE_fo',
			'sendinblue'       => 'tZ8Pp7WJnzk',
			'sgautorepondeur'  => 'N3zqX5dprUc',
			'sendlane'         => 'aqtKcGSJaog',
			'google_drive'     => 'LNCiLKxo7V4',
			'dropbox'          => '-zYbSNvp2JQ',
			'fluentcrm'        => 'AFfuQrv--S4',
			'slack'            => 'IeRaTyBLu9s',
			'sendowl'          => 'cN1UGZ3Vblo',
			'zapier'           => 'iD2-RsTflPU',
			'sendfox'          => 'VOQCwapziqs',
			'zoho'             => '50j3THWW7sQ',
			'klicktipp'        => 'MBzZNteFSz4',
		);

	/**
	 * ApiVideos constructor.
	 */
	public function __construct() {

		// URLs based on env
		$this->_set_urls();

		// Check and set api videos URLs transient and call TTW API for them
		$this->_check_videos_transient();
	}

	/**
	 * URLs setter
	 */
	private function _set_urls() {

		$this->_ttw_url = esc_url( defined( 'THRV_ENV' ) && is_string( THRV_ENV ) ? THRV_ENV : 'https://thrivethemes.com' );
	}

	/**
	 * Obfuscation
	 *
	 * @param      $string
	 * @param bool $flip
	 *
	 * @return string
	 */
	protected function _obfuscate( $string, $flip = false ) {

		if ( $flip ) {
			$this->_randomize = array_flip( $this->_randomize );
		}

		return (string) str_replace( array_keys( $this->_randomize ), $this->_randomize, $string );
	}

	/**
	 * @return bool
	 */
	protected function _build_videos_transient() {

		$headers = array(
			'Content-Type' => 'application/json',
			'website'      => get_site_url(),
			'tpm'          => 'no',
		);

		$tpm_data = get_option( 'tpm_connection', array() );

		// Build auth header for users with TPM [token received from TTW API]
		if ( ! empty( $tpm_data ) && ! empty( $tpm_data['ttw_salt'] ) ) {

			$headers['Authorization'] = $tpm_data['ttw_salt'];
			$headers['userid']        = ! empty( $tpm_data['ttw_id'] ) ? $tpm_data['ttw_id'] : '';
			$headers['tpm']           = 'yes';
		}

		// Build auth header for users without TPM
		if ( empty( $headers['Authorization'] ) ) {

			$headers['Authorization'] = $this->_obfuscate( get_site_url() );
			$headers['userid']        = $this->_obfuscate( get_site_url() . md5( date( 'Y-m-d' ) ), true );
		}

		$args = array(
			'headers'   => $headers,
			'sslverify' => false,
			'timeout'   => 20,
		);

		$request = wp_remote_get( $this->_ttw_url . '/api/v1/public/api_videos', $args );
		$body    = json_decode( wp_remote_retrieve_body( $request ) );

		if ( $body && ! empty( $body->urls ) ) {
			/* TODO re-add this back before relase */
//			return set_transient( $this->_ttw_api_url_transient, (array) $body->urls, $this->_cache_life_time );
		}

		return set_transient( $this->_ttw_api_url_transient, (array) $this->_fallback_urls, $this->_cache_life_time );
	}

	/**
	 * Verify is transient is set or set it
	 */
	protected function _check_videos_transient() {

		$video_urls_transient = get_transient( $this->_ttw_api_url_transient );

		if ( ! $video_urls_transient ) {
			$this->_build_videos_transient();
		}
	}
}
