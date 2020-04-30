<?php
/**
 * @package EcalypseRentalStarter
 */
/*
  Plugin Name: Ecalypse Rental Starter
  Plugin URI: http://ecalypse.com/wordpressecalypse-rental/
  Description: Ecalypse Rental Starter enables complete rental management of cars, bikes and other equipment.
  Version: 4.0.20
  Author: Ecalypse s.r.o.
  Author URI: http://ecalypse.com/
  License: GPLv3
  Text Domain: ecalypse-rental
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

if (!function_exists('add_action')) {
	echo __('I\'m just a plugin, not much I can do when called directly.', 'ecalypse-rental');
	exit;
}

if (class_exists('EcalypseRental')) {

	// show a message inside the dashboard
	if (is_admin()) {

		function ecalypse_rental_starter_plugin_notice() {
			?>
			<div class="error below-h2">
				<p>
					<?php
					_e('Detected other instance of Ecalypse Rental plugin. Please delete all other (older versions) versions of the plugin.');
					?>
				</p>
			</div>
			<?php
		}

		add_action('admin_notices', 'ecalypse_rental_starter_plugin_notice');
	}
	
	register_activation_hook(__FILE__, function(){
		deactivate_plugins( basename( __FILE__ ) );
		wp_die('Ecalypse Rental Plugin is present and activated. Before installing the Starter version, please delete the Full Rental Plugin. <a href="'.admin_url( 'plugins.php').'">Go Back</a>',  array( 'response'=>200, 'back_link'=>TRUE ) );
		return false;
	});

	// stop here and do nothing further
	return;
} elseif (version_compare(PHP_VERSION, '5.3') < 0) {

	// show a message inside the dashboard
	if (is_admin()) {

		function ecalypse_rental_starter_plugin_notice() {
			?>
			<div class="error below-h2">
				<p>
					<?php
					printf(__('The Ecalypse Rental plugin requires at least PHP 5.3. You have %s'), PHP_VERSION);
					?>
				</p>
			</div>
			<?php
		}

		add_action('admin_notices', 'ecalypse_rental_starter_plugin_notice');
	}

	// stop here and do nothing further
	return;
} else {

	define('ECALYPSERENTALSTARTER_VERSION', '4.0.20');
	define('ECALYPSERENTALSTARTER__MINIMUM_WP_VERSION', '3.9');
	define('ECALYPSERENTALSTARTER__PLUGIN_URL', plugin_dir_url(__FILE__));
	define('ECALYPSERENTALSTARTER__PLUGIN_DIR', plugin_dir_path(__FILE__));
	define('ECALYPSERENTALSTARTER_UPDATE_URL', 'http://ecalypse.com/?page=ecalypse-rental-admin&k');
	if (isset($_GET['ecalypse_debug_mode'])) {
		if ($_GET['ecalypse_debug_mode'] == 1) {
			define('DEBUG_MODE', true);
			setcookie('ecalypse_debug_mode', 1, time()+3600*24*100, '/');
		} else {
			setcookie('ecalypse_debug_mode', 1, time()-10, '/');
			define('DEBUG_MODE', false);
		}
	} else {
		if (isset($_COOKIE['ecalypse_debug_mode'])) {
			define('DEBUG_MODE', true);
		} else {
			define('DEBUG_MODE', false);
		}
	}
	
	// Only include the functionality if it's not pre-defined.
	if ( ! class_exists( 'EcalypseRentalSession' ) ) {
		include ECALYPSERENTALSTARTER__PLUGIN_DIR.'/includes/class-ecalypse-rental-session.php';
		EcalypseRentalSession::init();
	}
	
	global $ecalypse_rental_db, $wpdb;
	$ecalypse_rental_db = array('branch' => $wpdb->prefix . 'ecalypse_rental_branches',
		'branch_hours' => $wpdb->prefix . 'ecalypse_rental_branches_hours',
		'extras' => $wpdb->prefix . 'ecalypse_rental_extras',
		'extras_pricing' => $wpdb->prefix . 'ecalypse_rental_extras_pricing',
		'fleet' => $wpdb->prefix . 'ecalypse_rental_fleet',
		'fleet_pricing' => $wpdb->prefix . 'ecalypse_rental_fleet_pricing',
		'vehicle_categories' => $wpdb->prefix . 'ecalypse_rental_vehicle_categories',
		'fleet_extras' => $wpdb->prefix . 'ecalypse_rental_fleet_extras',
		'pricing' => $wpdb->prefix . 'ecalypse_rental_pricing',
		'pricing_ranges' => $wpdb->prefix . 'ecalypse_rental_pricing_ranges',
		'booking' => $wpdb->prefix . 'ecalypse_rental_booking',
		'booking_drivers' => $wpdb->prefix . 'ecalypse_rental_booking_drivers',
		'booking_prices' => $wpdb->prefix . 'ecalypse_rental_booking_prices',
		'translations' => $wpdb->prefix . 'ecalypse_rental_translations',
	);

	register_activation_hook(__FILE__, array('EcalypseRental', 'plugin_activation'));
	//register_deactivation_hook( __FILE__, array( 'EcalypseRental', 'plugin_deactivation' ) );

	require_once( ECALYPSERENTALSTARTER__PLUGIN_DIR . 'class.ecalypse-rental.php' );
	require_once( ECALYPSERENTALSTARTER__PLUGIN_DIR . 'class.ecalypse-rental-widget.php' );

	add_action('init', array('EcalypseRental', 'init'));
	add_filter('query_vars', array( 'EcalypseRental', 'query_vars' ) );
	
	add_action('template_redirect', array('EcalypseRental', 'template_redirect')); 
	add_action('parse_request', array('EcalypseRental', 'parse_request')); 
	// Add dropdown to widgets
	add_action('in_widget_form', array('EcalypseRental', 'ecalypse_rental_widget_dropdown'), 10, 3);

	// Update dropdown value on widget update
	add_filter('widget_update_callback', array('EcalypseRental', 'ecalypse_rental_widget_update'), 10, 4);

	// Filter widgets by language
	add_filter('widget_display_callback', array('EcalypseRental', 'ecalypse_rental_display_widget'), 10, 3);
	
	// Test if it is front-end AJAX call
	$fe_ajax = false;
	if (defined('DOING_AJAX') && DOING_AJAX) {
		if (isset($_REQUEST['fe_ajax'])) {
			$fe_ajax = true;
		}
	}
	
	if (is_admin() && !$fe_ajax) {
		require_once( ECALYPSERENTALSTARTER__PLUGIN_DIR . 'class.ecalypse-rental-admin.php' );
		add_action('init', array('EcalypseRental_Admin', 'init'));
	}

	if (isset($_GET['page']) == 'ecalypse-rental' || isset($_POST['page']) == 'ecalypse-rental') {

		// Confirm reservation
		if (isset($_POST['confirm_reservation'])) {
			add_action('template_include', array('EcalypseRental', 'ecalypse_rental_confirm_reservation'));
		}

		// Manage booking
		if (isset($_POST['manage_booking'])) {
			add_action('template_include', array('EcalypseRental', 'ecalypse_rental_manage_booking'));
		}

		// Terms and conditions
		if (isset($_GET['terms'])) {
			add_action('template_include', array('EcalypseRental', 'ecalypse_rental_terms_conditions'));
		}

		// Booking 4/4
		if (isset($_GET['summary'])) {
			add_action('template_include', array('EcalypseRental', 'ecalypse_rental_summary'));

			// Booking 3/4
		} elseif (isset($_GET['id_car'])) {
			
			if (isset($_GET['subpage']) && $_GET['subpage'] == 'extras') {
				add_action('template_include', array('EcalypseRental', 'ecalypse_rental_extras_book'));
			} else {
				add_action('template_include', array('EcalypseRental', 'ecalypse_rental_services_book'));
			}

			// Booking 2/4
		} elseif (isset($_GET['book_now'])) {
			add_action('template_include', array('EcalypseRental', 'ecalypse_rental_choose_car'));
		}
		
		// Change currency
		if (isset($_GET['change_currency']) && !empty($_GET['currency']) && strlen($_GET['currency']) == 3) {
			EcalypseRentalSession::$session['ecalypse_rental_currency'] = trim($_GET['currency']);
			Header('Location: ' . $_SERVER['HTTP_REFERER']);
			Exit;
		}

		// Change language
		if (isset($_GET['change_language']) && !empty($_GET['language']) && strlen($_GET['language']) == 5) {
			EcalypseRentalSession::$session['ecalypse_rental_language'] = trim(sanitize_text_field($_GET['language']));
			unset(EcalypseRentalSession::$session['ecalypse_rental_translations']);
			
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			$urlLanguages = array();
			include dirname(realpath(__FILE__)) . '/languages.php';
			$urlLanguages['en_GB'] = 'en';
			foreach ($available_languages as $lng_key => $lng) {
				$l = explode('-', $ecalypse_rental_languages[$lng_key]['lang-www']);
				$urlLanguages[$lng_key] = $l[0];
			}
			$lng = '';
			$primary_language = 'en_GB';
			$user_set_language = get_option('ecalypse_rental_primary_language');

			if ($user_set_language && !empty($user_set_language)) {
				$primary_language = $user_set_language;
			}
			
			if (EcalypseRentalSession::$session['ecalypse_rental_language'] != $primary_language && isset($urlLanguages[EcalypseRentalSession::$session['ecalypse_rental_language']])) {
				$lng = '/'.$urlLanguages[EcalypseRentalSession::$session['ecalypse_rental_language']];
			}
						
			$map_string = array('el=', 'id_car=', 'summary=', home_url().'/detail/');
			$our_page = false;
			
			 foreach($map_string as $s) {
				if (stripos($_SERVER['HTTP_REFERER'],$s) !== false) {
					$our_page = true;
					break;
				}
			}
			
			// test if it is ecalypse-rental page or other page
			if ($our_page) {
				Header('Location: ' . $_SERVER['HTTP_REFERER']);
			} else {
				Header('Location: ' . home_url().$lng);
			}
			Exit;
		}
	}
}