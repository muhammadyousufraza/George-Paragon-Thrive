<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-apprentice
 */
if ( defined( 'TVA_DEBUG' ) && TVA_DEBUG ) {
	$data = json_decode(
		file_get_contents( 'php://input' ),
		true
	);

	$data = array_merge( is_array( $data ) ? $data : array(), is_array( $_SERVER ) ? $_SERVER : array() );

	file_put_contents(
		dirname( __FILE__ ) . '/log.log',
		var_export(
			$data,
			true
		)
	);
}

$path = '../../../wp-load.php';

if ( ! file_exists( $path ) && is_file( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' ) ) {
	$path = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
}

require_once( $path );

/**
 * Class TVA_Payment_Service_Factory
 */
final class TVA_Payment_Service {

	/**
	 * THe user agent used
	 *
	 * @var string
	 */
	private static $user_agent;

	/**
	 * The classname that's being instantiated
	 *
	 * @var string
	 */
	private static $classname;

	/**
	 * @return mixed
	 */
	public static function create() {
		self::$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if ( self::$user_agent && stripos( self::$user_agent, 'sendowl' ) !== false && class_exists( 'TVA_SendOwl_Payment_Gateway' ) ) {
			self::$classname = 'TVA_SendOwl_Payment_Gateway';

			return new self::$classname();
		}

		return false;
	}
}

/** @var TVA_Payment_Gateway_Abstract $instance */
$instance = TVA_Payment_Service::create();

if ( ! $instance ) {
	TVA_Logger::set_type( 'REQUEST Error' );
	TVA_Logger::log(
		'Missing Instance',
		array(
			'failed' => "Payment Service doesn't exist",
		),
		true,
		null,
		'Request Factory'
	);
	header( ':', true, 400 );
	exit;
}

$response = $instance->get_response();

if ( $response['code'] >= 400 ) {
	header( ':', true, $response['code'] );
	exit;
}

echo $response['message'];
