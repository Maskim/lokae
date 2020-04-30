<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

class EcalypseRentalSession {
	public static $session = null;
	
	public static function init() {
		// let users change the session cookie name
		if( ! defined( 'WP_SESSION_COOKIE' ) ) {
			define( 'WP_SESSION_COOKIE', '_wp_session' );
		}

		if ( ! class_exists( 'Recursive_ArrayAccess' ) ) {
			include ECALYPSERENTALSTARTER__PLUGIN_DIR.'/includes/class-recursive-arrayaccess.php';
		}

		// Only include the functionality if it's not pre-defined.
		if ( ! class_exists( 'WP_Session' ) ) {
			include ECALYPSERENTALSTARTER__PLUGIN_DIR.'/includes/class-wp-session.php';
			include ECALYPSERENTALSTARTER__PLUGIN_DIR.'/includes/wp-session.php';
		}
		self::$session = WP_Session::get_instance();
	}
}