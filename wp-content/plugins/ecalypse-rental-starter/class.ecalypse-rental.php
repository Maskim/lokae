<?php

/*
  Version: 3.0.3

  @created: 2015-12-16
  @todo: ---

 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

class EcalypseRental {

	private static $initiated = false;
	private static $title = '';
	private static $all_fleet_parameters;
	public static $db = array();
	public static $hash_salt = '9D9D051447F79094306A54E8B28CFFD0C74DB5A6';
	public static $seo = null;
	public static $compatible_theme = true;
	public static $url_languages = array();
	public static $is_hp = false;

	public static function init() {
		global $wpdb, $ecalypse_rental_db;

		self::$db = $ecalypse_rental_db;

		if (!self::$initiated) {
			self::init_hooks();
			self::rewrite_rules();
		}

		if (isset($_GET['ecalypse-rental-listener']) && $_GET['ecalypse-rental-listener'] == 'ipn_paypal') {
			self::paypal_ipn();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		load_plugin_textdomain('ecalypse-rental', false, dirname(plugin_basename(__FILE__)) . '/languages/');

		add_action('wp_ajax_ecalypse_rental_available_cars', array('EcalypseRental', 'ajax_available_cars'));
		add_action('wp_ajax_nopriv_ecalypse_rental_available_cars', array('EcalypseRental', 'ajax_available_cars'));
		add_action('wp_ajax_ecalypse_rental_book_now_check', array('EcalypseRental', 'ajax_book_now_check'));
		add_action('wp_ajax_nopriv_ecalypse_rental_book_now_check', array('EcalypseRental', 'ajax_book_now_check'));
		add_filter('cron_schedules', array('EcalypseRental', 'create_cron_schedule'));

		// Schedule an action if it's not already scheduled
		if (!wp_next_scheduled('ecalypse_email_reminder')) {
			wp_schedule_event(time(), 'daily', 'ecalypse_email_reminder');
		}
		
		if (!wp_next_scheduled('ecalypse_webhook_resend')) {
			wp_schedule_event(time(), 'hourly', 'ecalypse_cron_webhook_resend');
		}

		// Hook into that action that'll fire weekly
		add_action('ecalypse_email_reminder', array('EcalypseRental', 'cron_email_reminder'));
		add_action('ecalypse_cron_webhook_resend', array('EcalypseRental', 'cron_webhook_resend'));

		$compatible_theme = get_option('ecalypse_rental_compatible_theme');
		if ($compatible_theme == 'no') {
			self::$compatible_theme = false;
			require_once ECALYPSERENTALSTARTER__PLUGIN_DIR . '/class.ecalypse-rental-theme.php';
			EcalypseRentalTheme::init();
		}

		$primary_language = 'en_GB';
		$user_set_language = get_option('ecalypse_rental_primary_language');
		if ($user_set_language && !empty($user_set_language)) {
			$primary_language = $user_set_language;
		}

		if (!isset(EcalypseRentalSession::$session['ecalypse_rental_language'])) {
			EcalypseRentalSession::$session['ecalypse_rental_language'] = $primary_language;
		}

		if (isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !isset(EcalypseRentalSession::$session['ecalypse_rental_translations'])) {
			EcalypseRentalSession::$session['ecalypse_rental_translations'] = self::load_translations(EcalypseRentalSession::$session['ecalypse_rental_language']);
		}

		if ($compatible_theme == 'yes') {
			add_filter('language_attributes', array('EcalypseRental', 'language_html_attributes'));
		}

		/* if (is_home()) {
		  self::get_seo();
		  } */

		self::$initiated = true;
	}

	public static function create_cron_schedule($schedules) {
		$schedules['ecalypse_email_reminder'] = array(
			'interval' => 86400, // 1 day in seconds
			'display' => __('Ecalypse daily email reminder'),
		);
		
		$schedules['ecalypse_webhook_resend'] = array(
			'interval' => 3600, // 1 hour in seconds
			'display' => __('Ecalypse hourly webhook resend'),
		);

		return $schedules;
	}
	
	/**
	 * Cron function for automatic email reminder
	 * Send reminder email X days before order enter date.
	 */
	public static function cron_email_reminder() {
		global $wpdb;
		$automatic_reminder = get_option('ecalypse_rental_reminder_days');
		if ((int) $automatic_reminder > 0) {
			$data_set = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE DATE(`enter_date`) = %s AND `deleted` IS NULL AND `status` = 1', date('Y-m-d', strtotime('+' . (int) $automatic_reminder . ' day')), ARRAY_A));

			$sent_mails = 0;
			foreach ($data_set as $data) {
				$data = (array) $data;

				EcalypseRental::send_emails($data['id_booking'], 'ecalypse_rental_reminder_email', false);
				$sent_mails++;
			}
			echo date('Y-m-d H:i:s') . ' - Cron status: OK, sent emails (automatic reminder): ' . $sent_mails . "\n";
		} else {
			echo date('Y-m-d H:i:s') . ' - Reminder set to 0 or less days. Exit.' . "\n";
		}


		$ty_days = get_option('ecalypse_rental_thank_you_days');
		if ((int) $ty_days > 0) {
			$data_set = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE DATE(`return_date`) = %s AND `deleted` IS NULL AND `status` = 1', date('Y-m-d', strtotime('-' . (int) $ty_days . ' day')), ARRAY_A));

			$sent_mails = 0;
			foreach ($data_set as $data) {
				$data = (array) $data;

				EcalypseRental::send_emails($data['id_booking'], 'ecalypse_rental_thank_you_email', false);
				$sent_mails++;
			}
			echo date('Y-m-d H:i:s') . ' - Cron status: OK, sent emails (thank you): ' . $sent_mails . "\n";
		} else {
			echo date('Y-m-d H:i:s') . ' - Thank you email days set to 0 or less days. Exit.' . "\n";
		}
	}
	
	/**
	 * Resend webhook if last one failed
	 */
	public static function cron_webhook_resend() {
		global $wpdb;
		$data = $wpdb->get_results('SELECT * FROM `'.$wpdb->prefix. 'ecalypse_rental_webhook_queue` GROUP BY id_booking ORDER BY date LIMIT 15', ARRAY_A);
		foreach ($data as $d) {
			$done = EcalypseRental::webhook_send($d['id_booking'], false);
			if ($done) {		
				$wpdb->delete($wpdb->prefix. 'ecalypse_rental_webhook_queue', array('id_booking' => $d['id_booking']), array('%d'));
			} else {
				$wpdb->update($wpdb->prefix. 'ecalypse_rental_webhook_queue', array('date' => date('Y-m-d H:i:s')), array('id_booking' => $d['id_booking']), array('%s'));
			}
		}
	}

	/**
	 * Callback for "language_attributes" filter, return attributes with current ecalypse language. Called only with compatible themes.
	 * @param type $lng_attr
	 * @return string
	 */
	public static function language_html_attributes($lng_attr) {
		if (!isset(EcalypseRentalSession::$session['ecalypse_rental_language'])) {
			return $lng_attr;
		}
		include dirname(realpath(__FILE__)) . '/languages.php';
		return 'lang="' . $ecalypse_rental_languages[EcalypseRentalSession::$session['ecalypse_rental_language']]['lang-www'] . '"';
	}

	public static function get_seo() {
		$seo = unserialize(get_option('ecalypse_rental_seo'));
		if (empty($seo)) {
			$seo = array();
		}
		$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
		$lang = strtolower(end(explode('_', $lang)));
		if (isset($seo['title'][$lang])) {
			self::title($seo['title'][$lang]);
		}
		self::$seo = $seo;

		add_action('wp_head', array('EcalypseRental', 'get_seo_meta'));
	}

	public static function get_seo_meta() {

		if (is_null(self::$seo)) {
			$seo = unserialize(get_option('ecalypse_rental_seo'));
			if (empty($seo)) {
				$seo = array();
			}
			self::$seo = $seo;
		}

		$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
		$lang = strtolower(end(explode('_', $lang)));

		if (isset(self::$seo['description'][$lang]) && !empty(self::$seo['description'][$lang])) {
			echo '<meta name="description" content="' . self::$seo['description'][$lang] . '">' . "\n";
		}
		if (isset(self::$seo['keywords'][$lang]) && !empty(self::$seo['keywords'][$lang])) {
			echo '<meta name="keywords" content="' . self::$seo['keywords'][$lang] . '">';
		}
	}

	public static function rewrite_rules() {
		global $wp_rewrite;
		$flush = false;

		if (self::$compatible_theme) {
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			include dirname(realpath(__FILE__)) . '/languages.php';
			$urlLanguages = array();
			$urlLanguages[] = 'en';
			foreach ($available_languages as $lng_key => $lng) {
				$l = explode('-', $ecalypse_rental_languages[$lng_key]['lang-www']);
				$urlLanguages[] = $l[0];
			}

			$rewrite_rules_array = (array) $wp_rewrite;
			if (!isset($rewrite_rules_array['(' . implode('|', $urlLanguages) . ')/?$'])) {
				$flush = true;
			}
			$lng_rule = '(' . implode('|', $urlLanguages) . ')/';
			//add_rewrite_rule('('.implode('|', $urlLanguages).')/([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$','index.php?year=$matches[2]&monthnum=$matches[3]&day=$matches[4]&lng=$matches[1]','top');
			//add_rewrite_rule('('.implode('|', $urlLanguages).')(/?(.+?))?(/[0-9]+)?/?$','index.php?pagename=$matches[2]&page=$matches[3]&lng=$matches[1]','top');
			add_rewrite_rule('(' . implode('|', $urlLanguages) . ')/?$', 'index.php?lng=$matches[1]', 'top');
		}

		$rewrite_rules_array = (array) $wp_rewrite;
		//print_r($rewrite_rules_array);
		if (!isset($rewrite_rules_array['detail/([0-9]{1,3})-(.+?)/?$'])) {
			$flush = true;
		}
		add_rewrite_rule('detail/([0-9]{1,3})-(.+?)/?$', 'index.php?detail_id=$matches[1]&detail_url=$matches[2]', 'top');

		if (!empty($lng_rule)) {
			if (!isset($rewrite_rules_array[$lng_rule . 'detail/([0-9]{1,3})-(.+?)/?$'])) {
				$flush = true;
			}
			add_rewrite_rule($lng_rule . 'detail/([0-9]{1,3})-(.+?)/?$', 'index.php?detail_id=$matches[2]&detail_url=$matches[3]&lng=$matches[1]', 'top');
		}

		if ($flush) {
			$wp_rewrite->flush_rules(false);
		}
	}

	public static function template_redirect() {
		global $wp_query;
		if (isset($wp_query->query['detail_id']) && !isset($_GET['id_car'])) {
			if (isset($_GET['terms'])) {
				return;
			}
			self::fleet_detail($wp_query->query['detail_id']);
			exit;
		}
	}

	public static function parse_request(&$wp) {
		if (empty($wp->query_vars)) {
			self::$is_hp = true;
		}

		if (self::$compatible_theme) {

			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			$urlLanguages = array('en' => 'en_GB');
			include dirname(realpath(__FILE__)) . '/languages.php';
			foreach ($available_languages as $lng_key => $lng) {
				$l = explode('-', $ecalypse_rental_languages[$lng_key]['lang-www']);
				$urlLanguages[$l[0]] = $lng_key;
			}
			self::$url_languages = array_flip($urlLanguages);

			if (isset($wp->query_vars['lng']) && isset($urlLanguages[$wp->query_vars['lng']])) {
				EcalypseRentalSession::$session['ecalypse_rental_language'] = $urlLanguages[$wp->query_vars['lng']];
				EcalypseRentalSession::$session['ecalypse_rental_translations'] = self::load_translations(EcalypseRentalSession::$session['ecalypse_rental_language']);
			} else {
				if (!isset(EcalypseRentalSession::$session['ecalypse_rental_language']) || self::$is_hp) {
					$primary_language = 'en_GB';
					$user_set_language = get_option('ecalypse_rental_primary_language');

					if ($user_set_language && !empty($user_set_language)) {
						$primary_language = $user_set_language;
					}

					EcalypseRentalSession::$session['ecalypse_rental_language'] = $primary_language;

					EcalypseRentalSession::$session['ecalypse_rental_translations'] = self::load_translations(EcalypseRentalSession::$session['ecalypse_rental_language']);
				}
			}
		} /* else {
		  unset(EcalypseRentalSession::$session['ecalypse_rental_language']);
		  unset(EcalypseRentalSession::$session['ecalypse_rental_translations']);
		  } */

		/* if (isset($wp->query_vars['detail_id']) && !isset($_GET['id_car'])) {
		  self::fleet_detail($wp->query_vars['detail_id']);
		  exit;
		  } */
	}

	public static function query_vars($public_query_vars) {
		$public_query_vars[] = "lng";
		$public_query_vars[] = "detail_id";
		$public_query_vars[] = "detail_url";
		return $public_query_vars;
	}

	public static function t($string) {

		if (isset(EcalypseRentalSession::$session['ecalypse_rental_translations']) && isset(EcalypseRentalSession::$session['ecalypse_rental_translations'][mb_strtolower($string)]) && !empty(EcalypseRentalSession::$session['ecalypse_rental_translations'][mb_strtolower($string)])) {
			return stripslashes(EcalypseRentalSession::$session['ecalypse_rental_translations'][mb_strtolower($string)]);
		}

		return $string;
	}

	/**
	 * Return date difference like 1 week, 4 days, 24 hours
	 * @param type $from datetime
	 * @param type $to datetime
	 */
	public static function time_diff($from, $to) {
		$diff = strtotime($to) - strtotime($from);
		$days_diff = $diff / (3600 * 24);

		$weeks = floor($days_diff / 7);
		$days = floor($days_diff - $weeks * 7);
		$hours = round(24 * ($days_diff - ($weeks * 7) - $days), 1);

		$return = '';
		if ($weeks > 0) {
			$return .= $weeks . ' ' . ($weeks != 1 ? EcalypseRental::t('weeks') : EcalypseRental::t('week'));
		}
		$return .= ($return != '' ? ', ' : '') . $days . ' ' . ($days != 1 ? EcalypseRental::t('days') : EcalypseRental::t('day'));
		if ($hours > 0) {
			$return .= ($return != '' ? ', ' : '') . $hours . ' ' . ($hours != 1 ? EcalypseRental::t('hours') : EcalypseRental::t('hour'));
		}

		return $return;
	}

	/**
	 * Detail of fleet
	 * @param type $detail_id
	 */
	public static function fleet_detail($detail_id) {
		global $wp_query;
		global $wpdb;

		// test if car exists
		$vehicle = self::get_vehicle($detail_id);
		if (!$vehicle) {
			header("HTTP/1.0 404 Not Found - Archive Empty");
			$wp_query->set_404();
			require TEMPLATEPATH . '/404.php';
			exit;
		}

		// Visual composer shortcodes init
		if (class_exists('WPBMap') && method_exists('WPBMap', 'addAllMappedShortcodes')) {
			WPBMap::addAllMappedShortcodes();
		}

		if (!function_exists('is_plugin_active')) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		// Fix for Visual Composer not loading CSS file(s)
		if (is_plugin_active('js_composer/js_composer.php')) {
			if (file_exists(WP_PLUGIN_DIR . '/js_composer/assets/css/js_composer.min.css')) {
				wp_enqueue_style('js-composer', plugins_url() . '/js_composer/assets/css/js_composer.min.css');
			} elseif (file_exists(WP_PLUGIN_DIR . '/js_composer/assets/css/js_composer.css')) {
				wp_enqueue_style('js-composer', plugins_url() . '/js_composer/assets/css/js_composer.css');
			}

			// JS
			if (file_exists(WP_PLUGIN_DIR . '/js_composer/assets/js/dist/js_composer_front.min.js')) {
				wp_enqueue_script('js-app-front', plugins_url() . '/js_composer/assets/js/dist/js_composer_front.min.js', array());
			}
		}

		// Locations + business hours
		$locations = self::get_locations();
		$vehicle_cats = self::get_vehicle_categories();
		$vehicle_names = self::get_vehicle_names();
		$vehicle = self::get_vehicle_detail($detail_id, $_GET);
		$extras = self::get_vehicle_extras($detail_id, $_GET);
		$similar_cars = self::get_similar_cars($vehicle);
		$fleet_parameters_values = self::get_fleet_parameters_values($detail_id);
		$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');

		$ranges = array();
		if ($vehicle->global_pricing_scheme > 0) {
			// get pricing scheme
			if ($time_pricing_type === 'half_day') {
				$ranges = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['pricing'] . '` p 
																									 WHERE p.`id_pricing` = %d
																									 LIMIT 1', $vehicle->global_pricing_scheme));
			} else {
				$ranges = self::get_pricing_ranges($vehicle->global_pricing_scheme);
			}
			$vehicle->pricing_scheme = $wpdb->get_row($wpdb->prepare('SELECT p.*
																									 FROM `' . EcalypseRental::$db['pricing'] . '` p
																									 WHERE p.`id_pricing` = %d
																									 LIMIT 1', $vehicle->global_pricing_scheme));
		}

		$american_pricing = false;
		if (defined('ECALYPSERENTALSTARTER_AMERICAN_PRICING_VERSION')) {
			if (self::is_plugin('ecalypse-rental-american-pricing/ecalypse-rental-american-pricing.php')) {
				$american_pricing = true;
				$ranges = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['pricing'] . '` p 
																									 WHERE p.`id_pricing` = %d
																									 LIMIT 1', $vehicle->global_pricing_scheme));
			}
		}


		wp_register_style('jquery-ui.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
		wp_enqueue_style('jquery-ui.css');

		self::title($vehicle->name);
		self::template('detail', array('locations' => $locations,
			'vehicle_cats' => $vehicle_cats,
			'vehicle_names' => $vehicle_names,
			'vehicle' => $vehicle,
			'fleet_parameters_values' => $fleet_parameters_values,
			'ranges' => $ranges,
			'similar_cars' => $similar_cars,
			'american_pricing' => $american_pricing,
			'extras' => $extras));
	}

	/**
	 * Get day ranges for specific pricing scheme
	 * @global type $wpdb
	 * @param type $id_pricing
	 * @return type
	 */
	public static function get_pricing_ranges($id_pricing) {
		global $wpdb;

		try {

			// Days and hours
			$ranges = $wpdb->get_results($wpdb->prepare('SELECT pr.*, p.`currency`
																									 FROM `' . EcalypseRental::$db['pricing_ranges'] . '` pr
																									 LEFT JOIN `' . EcalypseRental::$db['pricing'] . '` p ON p.`id_pricing` = pr.`id_pricing` 
																									 WHERE pr.`id_pricing` = %d
																									 ORDER BY pr.`type`, pr.`no_from`', $id_pricing));
			return $ranges;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Do cool URL of fleet
	 * @param type $id
	 * @param type $title
	 * @return type
	 */
	public static function get_fleet_url($id, $title) {
		$params = '';
		foreach ($_GET as $k => $v) {
			$params.= $k . '=' . $v . '&';
		}
		return home_url() . self::return_current_lng_url() . '/detail/' . $id . '-' . sanitize_title($title) . ($params != '' ? '?' . $params : '');
	}

	/**
	 * Returns URL string with current selected language or empty if selected language is default
	 */
	public static function return_current_lng_url() {
		if (!isset(EcalypseRentalSession::$session['ecalypse_rental_language'])) {
			return '';
		}

		if (!self::$compatible_theme) {
			return '';
		}

		$primary_language = 'en_GB';
		$user_set_language = get_option('ecalypse_rental_primary_language');

		if ($user_set_language && !empty($user_set_language)) {
			$primary_language = $user_set_language;
		}

		if (EcalypseRentalSession::$session['ecalypse_rental_language'] == $primary_language) {
			return '';
		}

		if (isset(self::$url_languages[EcalypseRentalSession::$session['ecalypse_rental_language']])) {
			return '/' . self::$url_languages[EcalypseRentalSession::$session['ecalypse_rental_language']];
		}
		return '';
	}

	/**
	 * Change language
	 * @param type $lng
	 */
	public static function change_lng($lng) {
		if (empty(self::$url_languages)) {
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			$urlLanguages = array('en' => 'en_GB');
			include dirname(realpath(__FILE__)) . '/languages.php';
			foreach ($available_languages as $lng_key => $lang) {
				$l = explode('-', $ecalypse_rental_languages[$lng_key]['lang-www']);
				$urlLanguages[$l[0]] = $lng_key;
			}
			self::$url_languages = array_flip($urlLanguages);
		}

		$lngs = array_flip(self::$url_languages);
		if (isset($lngs[$lng])) {
			$new_lng = $lngs[$lng];
			if ($lngs[$lng] != EcalypseRentalSession::$session['ecalypse_rental_language']) {
				EcalypseRentalSession::$session['ecalypse_rental_language'] = $new_lng;
				unset(EcalypseRentalSession::$session['ecalypse_rental_translations']);
				EcalypseRentalSession::$session['ecalypse_rental_translations'] = self::load_translations(EcalypseRentalSession::$session['ecalypse_rental_language']);
			}
		}
	}

	/**
	 * Render header alternate lng links
	 */
	public static function header_lng_links() {
		if (empty(self::$url_languages)) {
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			$urlLanguages = array('en' => 'en_GB');
			include dirname(realpath(__FILE__)) . '/languages.php';
			foreach ($available_languages as $lng_key => $lang) {
				$l = explode('-', $ecalypse_rental_languages[$lng_key]['lang-www']);
				$urlLanguages[$l[0]] = $lng_key;
			}
			self::$url_languages = array_flip($urlLanguages);
		}

		$primary_language = 'en_GB';
		$user_set_language = get_option('ecalypse_rental_primary_language');
		if ($user_set_language && !empty($user_set_language)) {
			$primary_language = $user_set_language;
		}

		//$lngs = array_flip(self::$url_languages);
		foreach (self::$url_languages as $k => $v) {
			echo '<link rel="alternate" href="' . home_url($k == $primary_language ? '' : $v) . '" hreflang="' . $v . '" />' . "\n";
		}
	}

	/**
	 * Set page title and call wp filter
	 * @param type $title
	 */
	private static function title($title) {
		self::$title = $title;
		add_filter('wp_title', array('EcalypseRental', 'set_title'), 20);
		//add_filter('the_title',  array( 'EcalypseRental', 'set_title' ), 20);
	}

	/**
	 * wp filter callback function for generating page title
	 * @param type $title
	 * @return type
	 */
	public static function set_title($title) {
		return self::$title;
	}

	/**
	 * 	Terms and conditions (in AJAX window)
	 */
	public function ecalypse_rental_terms_conditions() {
		try {

			$lang = 'en_GB';
			if (!empty(EcalypseRentalSession::$session['ecalypse_rental_language']) && strlen(EcalypseRentalSession::$session['ecalypse_rental_language']) == 5) {
				$lang = EcalypseRentalSession::$session['ecalypse_rental_language'];
			}

			$terms = get_option('ecalypse_rental_terms_conditions_' . $lang);
			if (!$terms || empty($terms)) {
				$terms = get_option('ecalypse_rental_terms_conditions_en_GB');
			}

			print(nl2br(stripslashes($terms)));
			exit;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * AJAX call for check available of car when book now button clicked
	 */
	public static function ajax_book_now_check() {
		$filters = array();
		$filters['fd'] = $_POST['pickup_date'];
		$filters['fh'] = self::valid_time($_POST['pickup_time']);
		$filters['td'] = $_POST['return_date'];
		$filters['th'] = self::valid_time($_POST['return_time']);
		$filters['el'] = (int) $_POST['enter_location'];
		$filters['car_id'] = (int) $_POST['car_id'];
		if (isset($_POST['period'])) {
			$filters['p'] = sanitize_key($_POST['period']);
		}
		$cars = self::get_vehicles($filters);
		$available = false;
		if ($cars && isset($cars['results']) && isset($cars['results'][0])) {
			$val = $cars['results'][0];
			if (isset($val->prices) && !empty($val->prices)) {
				$available = true;
			}
		}
		echo $available ? '1' : '0';
		exit;
	}

	public static function ajax_available_cars() {
		global $wpdb;
		$overbooking = get_option('ecalypse_rental_overbooking');
		$return = array();
		$date_from = date("Y-m-1", strtotime((int) $_POST['year'] . '-' . (int) $_POST['month'] . '-15'));
		$date_to = date("Y-m-t", strtotime('+3 month', strtotime((int) $_POST['year'] . '-' . (int) $_POST['month'] . '-15')));
		if ($overbooking && $overbooking == 'no') {
			$number_vehicles = $wpdb->get_results($wpdb->prepare('SELECT f.`number_vehicles` FROM `' . EcalypseRental::$db['fleet'] . '` f WHERE `id_fleet` = %d LIMIT 1', (int) $_POST['car_id']), ARRAY_A);
			$number_vehicles = $number_vehicles[0]['number_vehicles'];

			$data = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE `deleted` IS NULL AND `vehicle_id` = %d AND ((DATE(`enter_date`) >= %s && DATE(`enter_date`) <= %s) OR (DATE(`return_date`) >= %s && DATE(`return_date`) <= %s)) ORDER BY `enter_date` ASC', (int) $_POST['car_id'], $date_from, $date_to, $date_from, $date_to), ARRAY_A);
			$booking_data = array();
			foreach ($data as $book) {
				$from_timestamp = strtotime($book['enter_date']);
				$to_timestamp = strtotime($book['return_date']);
				while ($from_timestamp <= $to_timestamp) {
					$key = date('Y-m-d', $from_timestamp);
					if (!isset($booking_data[$key])) {
						$booking_data[$key] = 0;
					}
					$booking_data[$key] ++;
					$from_timestamp = strtotime('+1 day', $from_timestamp);
				}
			}

			$from_timestamp = strtotime($date_from);
			$to_timestamp = strtotime($date_to);
			while ($from_timestamp <= $to_timestamp) {
				$key = (int) $_POST['car_id'] . '-' . date('Y-m', $from_timestamp);
				if (!isset($return[$key])) {
					$return[$key] = array();
				}
				$key2 = (int) date('d', $from_timestamp);
				if (isset($booking_data[date('Y-m-d', $from_timestamp)])) {
					$return[$key][$key2] = $booking_data[date('Y-m-d', $from_timestamp)] >= $number_vehicles ? true : false;
				}
				$from_timestamp = strtotime('+1 day', $from_timestamp);
			}
		} else {
			$from_timestamp = strtotime($date_from);
			$to_timestamp = strtotime($date_to);
			for ($i = 0; $i <= 3; $i++) {
				$key = (int) $_POST['car_id'] . '-' . date('Y-m', $from_timestamp);
				if (!isset($return[$key])) {
					$return[$key] = array();
				}
				$from_timestamp = strtotime('+1 month', $from_timestamp);
			}
		}
		echo json_encode($return);
		exit;
	}

	/**
	 * 	Manage booking
	 */
	public static function ecalypse_rental_manage_booking() {
		global $wpdb;

		try {

			$id_order = str_replace('#', '', sanitize_text_field($_POST['id_order']));
			// Check if order exists in database
			$exists = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '`
																							 WHERE `id_order` = %s LIMIT 1', $id_order));

			if ($exists) {
				$order_hash = self::generate_hash($id_order, $exists->email);
				Header('Location: ' . home_url() . '?page=ecalypse-rental&summary=' . $order_hash);
				Exit;
			} else {
				EcalypseRentalSession::$session['ecalypse_rental_flash_msg'] = array('status' => 'danger', 'msg' => 'noexist');
				EcalypseRentalSession::$session['ecalypse_rental_flash_manage_booking'] = array('status' => false, 'email' => sanitize_email($_POST['email']), 'id_order' => sanitize_text_field($_POST['id_order']));
				if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
					Header('Location: ' . $_SERVER['HTTP_REFERER']);
					Exit;
				} else {
					Header('Location: ' . home_url());
					Exit;
				}
			}
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Summary
	 */
	public static function ecalypse_rental_summary() {
		global $wpdb;

		$summary = self::get_order_summary($_GET['summary']);
		if ($summary['info']->goal_sent == 0) {
			$wpdb->update(EcalypseRental::$db['booking'], array('goal_sent' => 1), array('id_booking' => $summary['info']->id_booking));
		}
		$locations = self::get_locations();
		self::template('summary', array('summary' => $summary, 'locations' => $locations));
	}

	/**
	 * 	Booking 4/4
	 */
	public static function ecalypse_rental_confirm_reservation() {

		$order_hash = self::save_booking($_POST);

		if ((int) $_POST['paypal'] == 1 && (float) $_POST['total_rental'] > 0) {
			$paypal = get_option('ecalypse_rental_paypal');
			$available_payments = unserialize(get_option('ecalypse_rental_available_payments'));
			$days_before_rental = (int) floor((strtotime($_POST['fd']) - strtotime(date('Y-m-d'))) / 86400);
			$payment_full_days = isset($available_payments['ecalypse_rental_online_payment_full_days']) ? (int) $available_payments['ecalypse_rental_online_payment_full_days'] : 0;

			if (isset($available_payments) && isset($available_payments['ecalypse-rental-paypal-security-deposit'])) {
				// if paypal security deposit is set
				if ($payment_full_days == 0 || $payment_full_days < $days_before_rental) {
					if (isset($available_payments['ecalypse-rental-paypal-security-deposit-type']) && $available_payments['ecalypse-rental-paypal-security-deposit-type'] == 'amount') {
						$_POST['total_rental'] = (float) $available_payments['ecalypse-rental-paypal-security-deposit-amount'];
					} else {
						$_POST['total_rental'] = (float) $_POST['total_rental'] * ((float) $available_payments['ecalypse-rental-paypal-security-deposit'] / 100);
						if (isset($available_payments['ecalypse-rental-paypal-security-deposit-round'])) {
							if ($available_payments['ecalypse-rental-paypal-security-deposit-round'] == 'up') {
								$_POST['total_rental'] = ceil((float) $_POST['total_rental']);
							} elseif ($available_payments['ecalypse-rental-paypal-security-deposit-round'] == 'down') {
								$_POST['total_rental'] = floor($_POST['total_rental']);
							} else {
								$_POST['total_rental'] = round($_POST['total_rental'], 2);
							}
						} else {
							$_POST['total_rental'] = round($_POST['total_rental'], 2);
						}
					}
				}
			}

			// Redirect to PayPal
			$query = array();
			$query['cmd'] = '_xclick';
			$query['business'] = $paypal;
			$query['email'] = sanitize_email($_POST['email']);
			$query['item_name'] = self::t('Car Rental Reservation #') . $order_hash;
			$query['quantity'] = 1;
			$query['return'] = home_url() . '?page=ecalypse-rental&payment=paypal&summary=' . $order_hash;
			$query['item_number'] = $order_hash;
			$query['custom'] = (isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB';
			$query['notify_url'] = trailingslashit(home_url()) . '?ecalypse-rental-listener=ipn_paypal';
			$query['cancel_return'] = $_SERVER['HTTP_REFERER'] . '&paymentError=1';
			$query['amount'] = number_format((float) $_POST['total_rental'], 2, '.', '');
			$query['currency_code'] = sanitize_text_field($_POST['currency_code']);

			// Prepare query string
			$query_string = http_build_query($query);
			if ($paypal == 'test@ecalypse.com') {
				Header('Location: https://www.sandbox.paypal.com/cgi-bin/webscr?' . $query_string);
			} else {
				Header('Location: https://www.paypal.com/cgi-bin/webscr?' . $query_string);
			}
			return;
		}

		do_action('ecalypse_rental_after_save_booking', $order_hash);
		Header('Location: ' . home_url() . '?page=ecalypse-rental&summary=' . $order_hash);
		Exit;
	}

	/**
	 * 	Booking 3/4
	 */
	public static function ecalypse_rental_services_book() {

		// Locations + business hours
		$locations = self::get_locations();
		$vehicle_cats = self::get_vehicle_categories();
		$vehicle_names = self::get_vehicle_names();
		$vehicle = self::get_vehicle_detail((int) $_GET['id_car'], $_GET);
		$extras = self::get_vehicle_extras((int) $_GET['id_car'], $_GET);

		$american_pricing = false;
		if (defined('ECALYPSERENTALSTARTER_AMERICAN_PRICING_VERSION')) {
			if (self::is_plugin('ecalypse-rental-american-pricing/ecalypse-rental-american-pricing.php')) {
				$american_pricing = true;
			}
		}

		wp_register_style('jquery-ui.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
		wp_enqueue_style('jquery-ui.css');

		self::title(self::t(__('Complete reservation', 'ecalypse-rental')) . ' - ' . $vehicle->name);

		self::template('services-book', array('locations' => $locations,
			'vehicle_cats' => $vehicle_cats,
			'vehicle_names' => $vehicle_names,
			'vehicle' => $vehicle,
			'extras' => $extras,
			'american_pricing' => $american_pricing));
	}

	/**
	 * 	Booking 3/5
	 */
	public static function ecalypse_rental_extras_book() {

		$extras = self::get_vehicle_extras((int) $_GET['id_car'], $_GET);
		$vehicle = self::get_vehicle_detail((int) $_GET['id_car'], $_GET);

		if (!($extras || empty($extras)) && (!isset($vehicle->additional_vehicles) || empty($vehicle->additional_vehicles))) {
			// no extras available - redi
			self::ecalypse_rental_services_book();
			exit;
		}

		// Locations + business hours
		$locations = self::get_locations();
		$vehicle_cats = self::get_vehicle_categories();
		$vehicle_names = self::get_vehicle_names();

		wp_register_style('jquery-ui.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
		wp_enqueue_style('jquery-ui.css');

		self::title(self::t(__('Available extras', 'ecalypse-rental')) . ' - ' . $vehicle->name);

		self::template('extras-book', array('locations' => $locations,
			'vehicle_cats' => $vehicle_cats,
			'vehicle_names' => $vehicle_names,
			'vehicle' => $vehicle,
			'extras' => $extras));
	}

	/**
	 * 	Booking 2/4
	 */
	public static function ecalypse_rental_choose_car() {
		// Locations + business hours
		$locations = self::get_locations();
		$vehicle_cats = self::get_vehicle_categories();
		$vehicle_names = self::get_vehicle_names();
		$vehicles = self::get_vehicles($_GET);
		$fleet_parameters = self::get_fleet_parameters(true);

		wp_register_style('jquery-ui.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
		wp_enqueue_style('jquery-ui.css');

		self::title(self::t(__('Our cars', 'ecalypse-rental')));

		self::template('choose-car', array('locations' => $locations,
			'vehicle_cats' => $vehicle_cats,
			'vehicle_names' => $vehicle_names,
			'vehicles' => $vehicles,
			'fleet_parameters' => $fleet_parameters));
	}

	/**
	 * Get just one location
	 * @global type $wpdb
	 * @global type $ecalypse_rental_db
	 * @param type $location_id
	 * @return type
	 */
	public static function get_location($location_id) {
		global $wpdb;
		global $ecalypse_rental_db;
		if (!self::$db) {
			self::$db = $ecalypse_rental_db;
		}

		try {
			$branch = $wpdb->get_row('SELECT * 
																		 	FROM `' . self::$db['branch'] . '`
																		 	WHERE `id_branch` = ' . (int) $location_id . ' AND `deleted` IS NULL
																 	 			AND `active` = 1 LIMIT 1');
			//$branch->branch_tax
			$branch->translations = unserialize($branch->translations);
			if ($lang != 'gb' && isset($branch->translations['name']) && isset($branch->translations['name'][$lang]) && $branch->translations['name'][$lang] != '') {
				$branch->name = $branch->translations['name'][$lang];
			}
			if ($lang != 'gb' && isset($branch->translations['street']) && isset($branch->translations['street'][$lang]) && $branch->translations['street'][$lang] != '') {
				$branch->street = $branch->translations['street'][$lang];
			}
			if ($lang != 'gb' && isset($branch->translations['city']) && isset($branch->translations['city'][$lang]) && $branch->translations['city'][$lang] != '') {
				$branch->city = $branch->translations['city'][$lang];
			}
			if ($lang != 'gb' && isset($branch->translations['state']) && isset($branch->translations['state'][$lang]) && $branch->translations['state'][$lang] != '') {
				$branch->state = $branch->translations['state'][$lang];
			}

			$branch->branch_tax = unserialize($branch->branch_tax);
			$branch->enter_hours = unserialize($branch->enter_hours);
			$branch->return_hours = unserialize($branch->return_hours);
			$branch->hours = $wpdb->get_results(
				$wpdb->prepare('SELECT * FROM `' . self::$db['branch_hours'] . '`
																												 	 WHERE `id_branch` = %d ORDER BY `day` ASC', $branch->id_branch));

			return $branch;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public static function get_locations($is_location_page = false) {
		global $wpdb;
		global $ecalypse_rental_db;
		if (!self::$db) {
			self::$db = $ecalypse_rental_db;
		}

		try {
			$branches = $wpdb->get_results('SELECT * 
																		 	FROM `' . self::$db['branch'] . '`
																		 	WHERE `deleted` IS NULL
																 	 			AND `active` = 1' . ($is_location_page ? ' AND `show_location` = 1' : '') . '
																		 	ORDER BY `ordering` DESC');

			$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
			$lang = strtolower(end(explode('_', $lang)));
			$data = array();
			if ($branches && !empty($branches)) {
				foreach ($branches as $key => $val) {
					$data[$val->id_branch] = $val;
					$data[$val->id_branch]->translations = unserialize($data[$val->id_branch]->translations);
					if ($lang != 'gb' && isset($data[$val->id_branch]->translations['name']) && isset($data[$val->id_branch]->translations['name'][$lang]) && $data[$val->id_branch]->translations['name'][$lang] != '') {
						$data[$val->id_branch]->name = $data[$val->id_branch]->translations['name'][$lang];
					}
					if ($lang != 'gb' && isset($data[$val->id_branch]->translations['street']) && isset($data[$val->id_branch]->translations['street'][$lang]) && $data[$val->id_branch]->translations['street'][$lang] != '') {
						$data[$val->id_branch]->street = $data[$val->id_branch]->translations['street'][$lang];
					}
					if ($lang != 'gb' && isset($data[$val->id_branch]->translations['city']) && isset($data[$val->id_branch]->translations['city'][$lang]) && $data[$val->id_branch]->translations['city'][$lang] != '') {
						$data[$val->id_branch]->city = $data[$val->id_branch]->translations['city'][$lang];
					}
					if ($lang != 'gb' && isset($data[$val->id_branch]->translations['state']) && isset($data[$val->id_branch]->translations['state'][$lang]) && $data[$val->id_branch]->translations['state'][$lang] != '') {
						$data[$val->id_branch]->state = $data[$val->id_branch]->translations['state'][$lang];
					}

					$data[$val->id_branch]->branch_tax = unserialize($val->branch_tax);
					$data[$val->id_branch]->enter_hours = unserialize($val->enter_hours);
					$data[$val->id_branch]->return_hours = unserialize($val->return_hours);
					$data[$val->id_branch]->hours = $wpdb->get_results(
						$wpdb->prepare('SELECT * FROM `' . self::$db['branch_hours'] . '`
																												 	 WHERE `id_branch` = %d ORDER BY `day` ASC', $val->id_branch));
				}
			}

			return $data;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get location name
	 */
	public function get_location_name($id_branch) {
		global $wpdb;

		try {
			return $wpdb->get_var($wpdb->prepare('SELECT `name` FROM `' . EcalypseRental::$db['branch'] . '` WHERE `id_branch` = %d', $id_branch));
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get location ID
	 */
	public function get_location_id($id_branch) {
		global $wpdb;

		try {
			return $wpdb->get_var($wpdb->prepare('SELECT `bid` FROM `' . EcalypseRental::$db['branch'] . '` WHERE `id_branch` = %d', $id_branch));
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get vehicle categories
	 */
	public function get_vehicle_categories() {
		global $wpdb;

		try {
			$return = $wpdb->get_results('SELECT vc.*,
																	 (SELECT COUNT(*) FROM `' . EcalypseRental::$db['fleet'] . '` f
																	  WHERE f.`id_category` = vc.`id_category` AND f.`deleted` IS NULL) as `no_vehicles`
																 FROM `' . EcalypseRental::$db['vehicle_categories'] . '` vc
																 WHERE `deleted` IS NULL
																 ORDER BY `id_category` ASC');
			$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
			$lang = strtolower(end(explode('_', $lang)));

			if ($lang != 'gb') {
				foreach ($return as &$val) {
					if (isset($val->name_translations)) {
						$val->name_translations = unserialize($val->name_translations);
						if (isset($val->name_translations[$lang]) && $val->name_translations[$lang] != '') {
							$val->name = $val->name_translations[$lang];
						}
					}
				}
			}

			return $return;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get vehicle names
	 */
	public function get_vehicle_names() {
		global $wpdb;

		try {

			return $wpdb->get_results('SELECT f.`name`, COUNT(*) as `count`
																 FROM `' . EcalypseRental::$db['fleet'] . '` f
																 WHERE `deleted` IS NULL
																 GROUP BY f.`name`
																 ORDER BY f.`name` ASC');
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get vehicles (filters)
	 */
	public function get_vehicles($filters) {
		global $wpdb;

		try {

			$limit = 200;
			if (isset($filters['limit']) && (int) $filters['limit'] > 0) {
				$limit = (int) $filters['limit'];
			}
			$page = ((isset($filters['page']) && (int) $filters['page'] > 0) ? (int) $filters['page'] : 1);
			$start = $limit * ($page - 1);
			$order = 'f.`name` ASC';

			// Apply filters
			$where = ' f.`deleted` IS NULL ';

			$is_default_date = false;
			// Date filters
			if (!isset($filters['fd']) || empty($filters['fd'])) {
				$filters['fd'] = Date('Y-m-d');
				$is_default_date = true;
			}
			if (!isset($filters['fh']) || empty($filters['fh'])) {
				$filters['fh'] = '12:00';
			}
			if (!isset($filters['td']) || empty($filters['td'])) {
				$filters['td'] = Date('Y-m-d', strtotime("+1 day"));
			}
			if (!isset($filters['th']) || empty($filters['th'])) {
				$filters['th'] = '12:00';
			}
			$filters['th'] = self::valid_time($filters['th']);
			$filters['fh'] = self::valid_time($filters['fh']);

			$date_from = date('Y-m-d', strtotime($filters['fd'])) . ' ' . $filters['fh'];
			$date_to = date('Y-m-d', strtotime($filters['td'])) . ' ' . $filters['th'];

			// Promocode
			$promocode = ((isset($filters['promo']) && !empty($filters['promo'])) ? sanitize_key($filters['promo']) : NULL);

			// Any location search
			$anylocation = get_option('ecalypse_rental_any_location_search');
			if ($anylocation && $anylocation == 'no' && !empty($filters['el'])) {
				$where .= ' AND f.`id_branch` = ' . (int) $filters['el'] . ' ';
			}

			// Allow car overbooking
			$overbooking = get_option('ecalypse_rental_overbooking');
			$sql_select = '';
			$sql_having = '';
			if ($overbooking && $overbooking == 'no' && !$is_default_date) {

				// Check reservations for cars
				$sql_select = ', (SELECT count(*) FROM
									(
									SELECT  @lastR as lr,
											tct.`vehicle_id`,
										  @lastR := tct.return_date,
										  @lastV as lv, 
										  @lastV := tct.vehicle_id, 
										  tct.enter_date 
										  FROM ( select @lastR := \'2100-01-01\', @lastV := 0 ) as tmpvars, (SELECT bi.`vehicle_id`, b.return_date, b.enter_date FROM `' . $wpdb->prefix . 'ecalypse_rental_booking_items` bi 
										  INNER JOIN `' . EcalypseRental::$db['booking'] . '` b ON b.`id_booking` = bi.`id_booking`
										  WHERE b.`deleted` IS NULL 
										  AND b.`status` <> 2
										  AND ((b.`enter_date` <= "' . $wpdb->escape($date_from) . '" AND b.`return_date` >= "' . $wpdb->escape($date_from) . '") OR
																	 (b.`enter_date` <= "' . $wpdb->escape($date_to) . '" AND b.`return_date` >= "' . $wpdb->escape($date_to) . '") OR
																	 (b.`enter_date` >= "' . $wpdb->escape($date_from) . '" AND b.`return_date` <= "' . $wpdb->escape($date_to) . '"))
									order by bi.vehicle_id, b.enter_date) as tct
									HAVING lr > tct.enter_date OR tct.vehicle_id <> lv
									) as count_table WHERE  count_table.`vehicle_id` = f.`id_fleet` ) as `rented_cars`';
				$sql_having = ' HAVING `rented_cars`  < f.`number_vehicles` ';
			}

			if (!$is_default_date) {
				// min_rental_time controll
				$clear_time = (strtotime($date_to) - strtotime($date_from)) / 3600;
				$where .= ' AND (f.`min_rental_time` = 0 OR f.`min_rental_time` <= ' . (float) $clear_time . ') ';
			}

			// order by ordering
			$theme_options = unserialize(get_option('ecalypse_rental_theme_options'));
			if (isset($theme_options['default_sort_by']) && $theme_options['default_sort_by'] == 'name') {
				$order = 'f.`name` ASC';
			} else {
				$order = 'f.`ordering` DESC';
			}

			if (isset($filters['car_id'])) {
				$where .= ' AND f.`id_fleet` = ' . (int) $filters['car_id'];
			}

			if (isset($filters['order']) && $filters['order'] == 'name') {
				$order = 'f.`name` ASC';
			}

			// Additional filters
			$flt = array();

			if (isset($_GET['flt']) && !empty($_GET['flt'])) {
				foreach (explode('|', $_GET['flt']) as $kD => $vD) {
					list($key, $val) = explode(':', $vD);
					$flt[sanitize_key($key)] = sanitize_key($val);
				}
			}

			// add from main filter
			if (isset($filters['cats']) && !isset($flt['cats'])) {
				$flt['cats'] = $filters['cats'];
			}

			// Filter: extras
			if (isset($flt['ac']) && (int) $flt['ac'] == 1 && (!isset($flt['nac']) || $flt['nac'] == 0)) {
				$where .= ' AND f.`ac` = 1 ';
			} elseif (isset($flt['nac']) && (int) $flt['nac'] == 1 && (!isset($flt['ac']) || $flt['ac'] == 0)) {
				$where .= ' AND f.`ac` = 0 ';
			}

			// Filter: fuel
			if (isset($flt['pl']) && (int) $flt['pl'] == 1 && (!isset($flt['dl']) || $flt['dl'] == 0)) {
				$where .= ' AND f.`fuel` = 1 ';
			} elseif (isset($flt['dl']) && (int) $flt['dl'] == 1 && (!isset($flt['pl']) || $flt['pl'] == 0)) {
				$where .= ' AND f.`fuel` = 2 ';
			}

			// Filter: passengers
			if (isset($flt['sp']) && isset($flt['ep']) && (int) $flt['ep'] > 0) {
				$where .= ' AND f.`seats` >= ' . (int) $flt['sp'] . ' ';
				$where .= ' AND f.`seats` <= ' . (int) $flt['ep'] . ' ';
			}

			// Filter: category
			if (isset($flt['cats']) && !empty($flt['cats'])) {
				$cats = explode(',', $flt['cats']);
				foreach ($cats as $key => $val) {
					$cats[$key] = (int) $val;
				}
				$where .= ' AND f.`id_category` IN (' . implode(',', $cats) . ') ';
			}

			// Filter: vehicle names
			if (isset($flt['vh']) && !empty($flt['vh'])) {
				$vh = explode(',', $flt['vh']);
				foreach ($vh as $key => $val) {
					$vh[$key] = $wpdb->escape($val);
				}
				$where .= ' AND f.`name` IN ("' . implode('","', $vh) . '") ';
			}

			// custom parameters
			if (isset($flt) && is_array($flt)) {
				$join = '';
				$found_count = 0;
				$custom_parameters = array();
				$custom_parameters_range = array();
				foreach ($flt as $k => $v) {
					if (substr($k, 0, 2) == 'cp') {
						$key = substr($k, 3);
						if (strpos($key, '-') !== false) {
							// range parameter
							$key = substr($key, 0, strpos($key, '-'));
							$values = explode('-', $v);
							$custom_parameters_range[(int) $key] = array('from' => $values[0], 'to' => $values[1]);
							//OR ((`fp`.`fleet_parameters_id` = 2 AND `fp`.`value` BETWEEN 100 AND 200))  
							if (isset($values[0]) && isset($values[1])) {
								$join .= '(`fp`.`fleet_parameters_id` = ' . (int) $key . ' AND `fp`.`value` BETWEEN ' . (int) $values[0] . ' AND ' . (int) $values[1] . ') OR ';
								$found_count++;
							}
						} else {
							// values parameter
							if (!isset($custom_parameters[(int) $key])) {
								$custom_parameters[(int) $key] = array();
							}
							$values = explode(',', $v);
							foreach ($values as $value) {
								if ((int) $value > 0) {
									$custom_parameters[(int) $key][] = (int) $value;
								}
							}
							if (count($custom_parameters[(int) $key]) > 0) {
								$join .= '(`fp`.`fleet_parameters_id` = ' . (int) $key . ' AND `fp`.`value` IN (' . implode(',', $custom_parameters[(int) $key]) . ')) OR ';
								$found_count++;
							}
						}
					}
				}
				if ($join != '') {
					$join = 'LEFT JOIN 
							(SELECT `fleet_id`, count(*) AS `found_count` 
								FROM `' . $wpdb->prefix . 'ecalypse_rental_fleet_parameters_values` as `fp` 
								WHERE ' . trim($join, ' OR ') . ' GROUP BY `fleet_id`
							) as `fpv` ON `fpv`.`fleet_id` = `f`.`id_fleet`';
					$where .= ' AND `found_count` = ' . (int) $found_count;
				}
			}

			$data = array();
			$sql = 'SELECT SQL_CALC_FOUND_ROWS f.*' . $sql_select . '
						  FROM `' . EcalypseRental::$db['fleet'] . '` f
						  ' . $join . '	
						  WHERE ' . $where . '
					      GROUP BY `f`.`id_fleet`
						  ' . $sql_having . '
						  ORDER BY ' . $order . '
							LIMIT ' . $start . ', ' . $limit;

			//echo $sql;
			$data['results'] = $wpdb->get_results($sql);
			$data['count'] = $wpdb->get_var("SELECT FOUND_ROWS();");

			if (count($data['results']) == 0) {
				$data['count'] = 0;
			}

			$global_currency = get_option('ecalypse_rental_global_currency');
			$av_currencies = unserialize(get_option('ecalypse_rental_available_currencies'));
			if (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_currency']) && isset($av_currencies[EcalypseRentalSession::$session['ecalypse_rental_currency']])) {
				$current_currency = EcalypseRentalSession::$session['ecalypse_rental_currency'];
			} else {
				$current_currency = $global_currency;
			}

			// Get prices for results
			if ($data['results'] && !empty($data['results'])) {
				foreach ($data['results'] as $key => $val) {
					if ($is_default_date && isset($val->price_from) && $val->price_from > 0) {
						$data['results'][$key]->prices = array(
							'price' => $val->price_from,
							'total_rental' => $val->price_from,
							'pr_type' => 1,
							'currency' => $current_currency,
							'cc_before' => self::get_currency_symbol('before', $current_currency),
							'cc_after' => self::get_currency_symbol('after', $current_currency),
							'diff_days' => 1
						);
						if ($current_currency != $global_currency && isset($av_currencies[$current_currency])) {
							$rate = $av_currencies[$current_currency]; //round($av_currencies[$global_currency] / $av_currencies[$current_currency], 2);
							$data['results'][$key]->prices['price'] /= $rate;
							$data['results'][$key]->prices['total_rental'] /= $rate;
						}
					} else {
						$data['results'][$key]->prices = self::get_prices('fleet', $val->id_fleet, $date_from, $date_to, $promocode, (isset($filters['el']) ? (int) $filters['el'] : false), (isset($filters['rl']) ? (int) $filters['rl'] : false), (isset($filters['dl']) ? true : false), (isset($filters['p']) ? $filters['p'] : false));
					}

					$data['results'][$key]->free_distance_total = self::get_vehicle_free_distance($data['results'][$key]);

					// Filter: price range
					if (isset($flt['spr']) && isset($flt['epr']) && (int) $flt['epr'] > 0) {
						if ((int) $flt['spr'] > $data['results'][$key]->prices['price'] ||
							(int) $flt['epr'] < $data['results'][$key]->prices['price']) {
							unset($data['results'][$key]);
							--$data['count'];
						}
					}
				}
			}

			// if order is not set, get theme settings for default sort by value
			if (!isset($filters['order']) || empty($filters['order'])) {
				$theme_options = unserialize(get_option('ecalypse_rental_theme_options'));
				if (isset($theme_options['default_sort_by']) && $theme_options['default_sort_by'] == 'price') {
					$filters['order'] = 'price';
				}
			}
			// Sort by price
			if (isset($filters['order']) && !empty($filters['order']) && !empty($data['results'])) {
				if ($filters['order'] == 'price') {

					$prices = array();
					foreach ($data['results'] as $key => $val) {
						// 999999 because we want to move NA price down
						$prices[$key] = (int) $val->prices['price'] < 1 ? 99999999 : $val->prices['price'];
					}
					array_multisort($prices, SORT_NUMERIC, SORT_ASC, $data['results']);
				}
			}


			return $data;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get vehicle by his ID
	 */
	public function get_vehicle($id, $filters = false) {
		global $wpdb;

		try {
			// Apply filters
			$where = ' f.`deleted` IS NULL ';
			$where .= ' AND f.`id_fleet` = ' . (int) $id;

			$data = array();
			$sql = 'SELECT SQL_CALC_FOUND_ROWS f.*
						  FROM `' . EcalypseRental::$db['fleet'] . '` f
						  WHERE ' . $where . '
							LIMIT 1';

			$data = $wpdb->get_row($sql);

			$global_currency = get_option('ecalypse_rental_global_currency');
			$av_currencies = unserialize(get_option('ecalypse_rental_available_currencies'));
			if (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_currency']) && isset($av_currencies[EcalypseRentalSession::$session['ecalypse_rental_currency']])) {
				$current_currency = EcalypseRentalSession::$session['ecalypse_rental_currency'];
			} else {
				$current_currency = $global_currency;
			}

			// Get prices for results
			if ($data && !empty($data)) {
				if (isset($val->price_from) && $val->price_from > 0) {
					$data->prices = array(
						'price' => $data->price_from,
						'total_rental' => $data->price_from,
						'pr_type' => 1,
						'currency' => $current_currency,
						'cc_before' => self::get_currency_symbol('before', $current_currency),
						'cc_after' => self::get_currency_symbol('after', $current_currency)
					);
					if ($current_currency != $global_currency && isset($av_currencies[$current_currency])) {
						$rate = $av_currencies[$current_currency]; //round($av_currencies[$global_currency] / $av_currencies[$current_currency], 2);
						$data->prices['price'] /= $rate;
						$data->prices['total_rental'] /= $rate;
					}
				} else {
					$data->prices = self::get_prices('fleet', $data->id_fleet, date('Y-m-d') . ' 12:00', Date('Y-m-d', strtotime("+1 day")) . ' 12:00', '', false, false, false, false);
				}
				$data->free_distance_total = self::get_vehicle_free_distance($data);
			}

			return $data;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function get_vehicle_free_distance($data) {
		$free_distance_total = 0;
		if (!isset($data->prices)) {
			return $data->free_distance;
		}

		$use_free_hour_km = get_option('ecalypse_rental_use_free_hour_km', 'yes');

		if ($data->prices['pr_type'] == 2) {
			if ($use_free_hour_km == 'yes') {
				if ($data->free_distance_hour > 0) {
					$free_distance_total = $data->prices['diff_hours'] * $data->free_distance_hour;
				} else {
					$free_distance_total = 0;
				}
			} else {
				if ($data->free_distance > 0) {
					$free_distance_total = $data->free_distance;
				} else {
					$free_distance_total = 0;
				}
			}
		} else {
			if ($data->free_distance > 0) {
				$free_distance_total = (isset($data->prices) ? (int) $data->prices['diff_days'] : 1) * $data->free_distance;

				if ($use_free_hour_km == 'yes') {
					if (isset($data->prices['extra_hours_count'])) {
						$free_distance_total += $data->free_distance_hour * $data->prices['extra_hours_count'];
					}
				}
			} else {
				$free_distance_total = 0;
			}
		}

		return $free_distance_total;
	}

	public function get_vat_settings() {
		$vat_settings = unserialize(get_option('ecalypse_rental_vat_settings'));
		if (!isset($vat_settings['vat'])) {
			$vat_settings['vat'] = 0;
		}
		if (!isset($vat_settings['vat_2'])) {
			$vat_settings['vat_2'] = 0;
		}
		if (!isset($vat_settings['vat_3'])) {
			$vat_settings['vat_3'] = 0;
		}
		if (!isset($vat_settings['vat_calculation'])) {
			$vat_settings['vat_calculation'] = 1;
		}
		return $vat_settings;
	}

	/**
	 * Return price with VAT by vat settings
	 * @param float $base - base price
	 * @param array $vat_settings - vat settings from option ecalypse_rental_vat_settings
	 */
	public function price_with_vat($base, $vat_settings = false, $vat_rates = array()) {
		if (!$vat_settings) {
			$vat_settings = self::get_vat_settings();
		}

		if (isset($vat_rates['vat'])) {
			$vat_rates['vat_1'] = $vat_rates['vat'];
		}

		$return = $base;
		if ($vat_settings['vat_calculation'] == 2) {
			if (isset($vat_rates['vat_1']) && $vat_rates['vat_1'] == 1) {
				$return *= (1 + ((float) $vat_settings['vat'] / 100));
			}
			if (isset($vat_rates['vat_2']) && $vat_rates['vat_2'] == 1) {
				$return *= (1 + ((float) $vat_settings['vat_2'] / 100));
			}
			if (isset($vat_rates['vat_3']) && $vat_rates['vat_3'] == 1) {
				$return *= (1 + ((float) $vat_settings['vat_3'] / 100));
			}
		} else {
			$percent = 0;
			if (isset($vat_rates['vat_1']) && $vat_rates['vat_1'] == 1) {
				$percent += (float) $vat_settings['vat'];
			}
			if (isset($vat_rates['vat_2']) && $vat_rates['vat_2'] == 1) {
				$percent += (float) $vat_settings['vat_2'];
			}
			if (isset($vat_rates['vat_3']) && $vat_rates['vat_3'] == 1) {
				$percent += (float) $vat_settings['vat_3'];
			}
			$return = $return * (1 + $percent / 100);
		}
		return round($return, 2);
	}

	/**
	 * Returns scheme for one day
	 * @param int $current_day
	 * @param array $schemes
	 * @param array $active_days
	 */
	public static function get_pricing_scheme_for_day($current_day, $schemes, $active_days = array()) {

		if (DEBUG_MODE) {
			// DEBUG-MODE: 
			echo '<br>Sezona, den ' . date('Y-m-d', $current_day);
		}
		$date_from_only_day = $date_to_only_day = $current_day;

		foreach ($schemes as $s) {
			if (DEBUG_MODE) {
				// DEBUG-MODE: 
				echo '<br>Testuju schema ' . $s->name . ' = ';
			}

			/* if ($s->repeat == 1) {
			  // repeat scheme every year
			  if (date('Y', strtotime($s->valid_from)) != date('Y', $date_from_only_day)) {

			  if (strtotime($s->valid_to) < $date_to_only_day) {
			  // increment year to selected date
			  $s->valid_from = date('Y-m-d', strtotime(date('Y', $date_from_only_day) . '-' . date('m-d', strtotime($s->valid_from))));
			  $s->valid_to = date('Y-m-d', strtotime((1 + date('Y', strtotime($s->valid_from))) . '-' . date('m-d', strtotime($s->valid_to))));

			  if (DEBUG_MODE) {
			  // DEBUG-MODE:
			  echo '<br>Uplatnen repeat, data posunuty na FROM: ' . $s->valid_from . ' a TO: ' . $s->valid_to;
			  }
			  }
			  }
			  } */

			if ($s->repeat == 1) {
				// repeat scheme every year
				if (date('Y', strtotime($s->valid_from)) != date('Y', $date_from_only_day)) {
					if (strtotime($s->valid_to) < $date_to_only_day) {
						// is it same year ?
						if (date('Y', strtotime($s->valid_from)) == date('Y', strtotime($s->valid_to))) {
							// increment year to selected date 
							$s->valid_from = date('Y-m-d', strtotime(date('Y', $date_from_only_day) . '-' . date('m-d', strtotime($s->valid_from))));
							$s->valid_to = date('Y-m-d', strtotime((date('Y', $date_from_only_day)) . '-' . date('m-d', strtotime($s->valid_to))));
						} else {
							if (date('md', strtotime($s->valid_from)) > date('md', $date_from_only_day)) {
								$s->valid_from = date('Y-m-d', strtotime((date('Y', $date_from_only_day) - 1) . '-' . date('m-d', strtotime($s->valid_from))));
								$s->valid_to = date('Y-m-d', strtotime(date('Y', $date_from_only_day) . '-' . date('m-d', strtotime($s->valid_to))));
							} else {
								$s->valid_from = date('Y-m-d', strtotime(date('Y', $date_from_only_day) . '-' . date('m-d', strtotime($s->valid_from))));
								$s->valid_to = date('Y-m-d', strtotime((1 + date('Y', strtotime($s->valid_from))) . '-' . date('m-d', strtotime($s->valid_to))));
							}
						}
						if (DEBUG_MODE) {
							// DEBUG-MODE:
							echo '<br>Uplatnen repeat, data posunuty na FROM: ' . $s->valid_from . ' a TO: ' . $s->valid_to;
						}
					}
				}
			}

			// if it is promo schema, then validate from and to date
			if ($s->promocode != '') {
				if (strtotime($s->valid_from) > $date_from_only_day || ($s->valid_to != '0000-00-00' && strtotime($s->valid_to) < $date_to_only_day)) {
					if (DEBUG_MODE) {
						// DEBUG-MODE: 
						echo 'Nevleze se cele do promocode';
					}
					continue;
				}
			}

			// if schema is not for all days, then all days must be in schema days
			if ($s->active_days != '1;2;3;4;5;6;0' && $s->active_days != '') {
				$db_active_days = explode(';', $s->active_days);
				foreach ($active_days as $a) {
					if (!in_array($a, $db_active_days)) {
						if (DEBUG_MODE) {
							// DEBUG-MODE: 
							echo 'Vsechny dny nejsou v povolenych';
						}
						continue 2;
					}
				}
			}

			if ($s->valid_to != '0000-00-00' && strtotime($s->valid_to) < $date_from_only_day) {
				if (DEBUG_MODE) {
					// DEBUG-MODE: 
					echo 'Schema konci driv nez zacatek rezervace.';
				}
				continue;
			}

			if (strtotime($s->valid_from) > $date_to_only_day) {
				if (DEBUG_MODE) {
					// DEBUG-MODE: 
					echo 'Schema zacina pozdeji nez posledni datum rezervace.';
				}
				continue;
			}

			/* if (strtotime($s->valid_from) > $date_from_only_day) {
			  if (DEBUG_MODE) {
			  // DEBUG-MODE:
			  echo 'Schema zacina pozdeji nez prvni datum rezervace.';
			  }
			  continue;
			  } */

			if (DEBUG_MODE) {
				// DEBUG-MODE: 
				echo '-- Pro datum den ' . date('d.m.Y', $date_from_only_day) . ' Vybrano schema: ' . $s->name;
			}
			$scheme = $s;
			break;
		}
		return isset($scheme) ? $scheme : false;
	}

	/**
	 * 	Get price for the vehicle
	 */
	public function get_price($type = 'fleet', $id, $date_from, $date_to, $promocode = NULL, $el = false, $rl = false, $dl = false, $add_day = true, $period = false) {
		global $wpdb;

		try {
			$data = array();
			$vat_settings = self::get_vat_settings();

			$date_diff = abs(strtotime($date_to) - strtotime($date_from));
			$diff_days = intval($date_diff / 86400);
			$diff_hours = intval(($date_diff % 86400) / 3600);
			$diff_minutes = intval(((($date_diff % 86400) / 3600) - floor(($date_diff % 86400) / 3600)) * 60);

			if ($diff_minutes > 0) {
				$diff_hours++;
			}

			$data['day_added'] = false;
			$data['extra_hours'] = false;
			if ($diff_days >= 1 && ($diff_hours > 0 || $diff_minutes > 0)) {
				if ($add_day) {
					++$diff_days; // If you pass by 30 minutes and more, it 1 day more
					$data['day_added'] = true;
				} else {
					$data['extra_hours'] = true;
				}
			}
			
			$pr_type = (($diff_days == 0 && $diff_hours > 0) ? 2 : 1); // 1 - days, 2 - hours
			$pr_value = (($pr_type == 2) ? $diff_hours : $diff_days);

			$db_name = (($type == 'extras') ? EcalypseRental::$db['extras_pricing'] : EcalypseRental::$db['fleet_pricing']);
			$db_name_global = (($type == 'extras') ? EcalypseRental::$db['extras'] : EcalypseRental::$db['fleet']);
			$id_column = (($type == 'extras') ? 'id_extras' : 'id_fleet');

			$data['outside_booking_return'] = 0;
			$data['outside_booking_enter'] = 0;

			$data['diff_days'] = $diff_days;
			$data['diff_hours'] = $diff_hours;
			$data['pr_type'] = $pr_type; // 1 - days, 2 - hours
			$data['pr_value'] = $pr_value; // days/hours
			$data['promocode'] = $promocode;

			$active_days = array();
			$day_from = Date('w', strtotime($date_from));
			$day_to = Date('w', strtotime($date_to));

			$active_days[] = $day_from;
			$counter = 1;
			while ($next_day < strtotime($date_to)) {
				//$next_day = mktime(0,0,0, Date('m', strtotime($date_from)), Date('d', strtotime($date_from)) + $counter, Date('Y', strtotime($date_from)));
				$next_day = strtotime('+' . $counter . ' day', strtotime($date_from));
				if (!in_array(Date('w', $next_day), $active_days)) {
					$active_days[] = Date('w', $next_day);
				}
				++$counter;
			}

			$standard_pricing = true;
			if (defined('ECALYPSERENTALSTARTER_FIXED_DATES_VERSION') && self::is_plugin('ecalypse-rental-fixed-dates/ecalypse-rental-fixed-dates.php') && $type == 'fleet') {
				$date_from_sql = date('Y-m-d', strtotime($date_from));
				$date_to_sql = date('Y-m-d', strtotime($date_to));
				$fixed_date_pricing = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . $wpdb->prefix . "ecalypse_rental_fixed_dates" . '` WHERE ((`date_from` <= %s AND `date_to` >=%s) OR (`date_from` <= %s AND `date_to` >=%s)) AND `active` = 1 LIMIT 1', $date_from_sql, $date_to_sql, $date_from_sql, $date_to_sql));
				if ($fixed_date_pricing) {
					$standard_pricing = false;
					$data['total_rental'] = $fixed_date_pricing->price;
					$data['price'] = $data['total_rental'] / $diff_days;
					//$data['vat'] = $fixed_date_pricing->vat;
					if (DEBUG_MODE) {
						echo '<br><br>Vybrano fixed dates schema ID ' . $fixed_date_pricing->fixed_dates_id;
					}
				} else {
					if (DEBUG_MODE) {
						echo '<br><br>Nemame zadne fixed dates schema.';
					}
				}
			}

			if ($standard_pricing) {
				// try find pricing scheme
				$scheme = false;

				$date_from_only_day = strtotime(date('Y-m-d', strtotime($date_from)) . ' 00:00:00');
				$date_to_only_day = strtotime(date('Y-m-d', strtotime($date_to)) . ' 00:00:00');

				if (DEBUG_MODE) {
					// DEBUG-MODE: 
					echo '<br><br>Vypocet cen pro ' . ($type == 'fleet' ? 'auto' : 'extra') . ' ID ' . $id;
				}


				if ($scheme && !empty($scheme)) {

					// Get global price scheme
				} else {
					if (DEBUG_MODE) {
						// DEBUG-MODE: 
						echo '-- Pro datum od ' . date('d.m.Y', $date_from_only_day) . ' do ' . date('d.m.Y', $date_to_only_day) . ' Vybrano defaultni schema.';
					}

					$sql = 'SELECT p.tax_rates, p.`id_pricing`, p.`type`, p.`maxprice`, p.`min_price`, p.`currency`, p.`onetime_price`, pr.`price`, p.`active_days`, p.`rate_id`
											FROM `' . $db_name_global . '` f
											LEFT JOIN `' . EcalypseRental::$db['pricing'] . '` p ON p.`id_pricing` = f.`global_pricing_scheme`
											LEFT JOIN `' . EcalypseRental::$db['pricing_ranges'] . '` pr ON pr.`id_pricing` = p.`id_pricing`
											WHERE f.`' . $id_column . '` = %d
												AND p.`deleted` IS NULL
												AND p.`active` = 1
												AND (p.`promocode` = %s OR p.`promocode` = "")
												AND ((p.`type` = 2 AND pr.`type` = %d AND pr.`no_from` <= %d AND (pr.`no_to` >= %d || pr.`no_to` = 0)) OR 
														 (p.`type` = 1 AND p.`onetime_price` >= 0))
											LIMIT 1';
					$scheme = $wpdb->get_row($wpdb->prepare($sql, $id, $promocode, $data['pr_type'], $data['pr_value'], $data['pr_value']));



					if ($scheme && !empty($scheme)) {
						if (DEBUG_MODE) {
							echo 'Global: ' . $id . ' => ';
							var_dump($scheme);
							echo '<br /><br />';
						}
						/* DEBUG echo 'Global: ' . $id . ' => '; var_dump($scheme); echo '<br /><br />'; /* */
					} else {
						if (DEBUG_MODE) {
							echo 'Nastal problem s vyberem globalniho schematu.<br /><br />';
						}
						return false;
					}
				}

				// Get scheme
				$data['id_pricing'] = $scheme->id_pricing;
				$data['rate_id'] = $scheme->rate_id;
				$data['vat'] = $vat_settings['vat'];
				$data['vat_2'] = $vat_settings['vat_2'];
				$data['vat_3'] = $vat_settings['vat_3'];
				$data['tax_rates'] = unserialize($scheme->tax_rates);
				$data['type'] = $scheme->type;
				$data['currency'] = $scheme->currency;
				$data['active_days'] = $scheme->active_days;

				// One-time price
				if ($data['type'] == 1) {
					$data['price'] = $scheme->onetime_price;
					$data['total_rental'] = $data['price'];

					// Time based
				} else {
					$data['price'] = $scheme->price; // Price per day/hour
					$data['total_rental'] = $data['price'] * $data['pr_value']; // Price * number of days/hours


					$id_current_pricing = $scheme->id_pricing;

					if ($data['extra_hours']) {
						// add extra hours price
						$range_hours = $wpdb->get_row($wpdb->prepare('SELECT pr.*
													 FROM `' . EcalypseRental::$db['pricing_ranges'] . '` pr
													 WHERE pr.`id_pricing` = %d
														AND pr.`type` = 2
														AND (pr.`no_from` <= %d AND (pr.`no_to` >= %d || pr.`no_to` = 0))
														', $id_current_pricing, $diff_hours, $diff_hours));
						if (!$range_hours) {
							$data['no_range_hours'] = true;
						} else {
							$data['total_rental'] += $diff_hours * $range_hours->price;
							$data['total_rental_clear'] += $diff_hours * $range_hours->price;
							$data['extra_hours_count'] = $diff_hours;
						}
					}
				}

				$data['maxprice_reached'] = ((isset($scheme->maxprice) && (float) $scheme->maxprice > 0 && $data['total_rental'] > (float) $scheme->maxprice) ? true : false);

				if ($data['maxprice_reached'] == true) {
					$data['total_rental'] = $scheme->maxprice;
				}

				if ((int) $scheme->min_price > $data['total_rental']) {
					$data['total_rental'] = $scheme->min_price;
					if ($data['type'] == 1) {
						$data['price'] = $data['total_rental'];
					} else {
						$data['price'] = $data['total_rental'] / $data['pr_value'];
					}
				}
			}

			$data['total_rental_clear'] = $data['total_rental'];
			$data['summary'] = array();

			$include_branch_delivery_fees = true;
			$is_detail = false;
			if (isset($_GET['id_car']) && (int) $_GET['id_car'] > 0) {
				$is_detail = true;
			}
			$branches_distance_global_settings = unserialize(get_option('ecalypse_rental_branch_distance_global_settings'));
			if (!empty($branches_distance_global_settings) && is_array($branches_distance_global_settings) && isset($branches_distance_global_settings['exclude_fees'])) {
				if ($branches_distance_global_settings['exclude_fees'] == 1 && !$is_detail) {
					$include_branch_delivery_fees = false;
				}
			}

			// returning on different location
			if ($el && $rl && $el != $rl && $include_branch_delivery_fees) {
				// if branch delivery pricing plugin is installed

				if (defined('ECALYPSERENTALSTARTER_BRANCH_DELIVERY_PRICING__PLUGIN_DIR') && ECALYPSERENTALSTARTER_BRANCH_DELIVERY_PRICING__PLUGIN_DIR != '' && self::is_plugin('ecalypse-rental-branch-delivery-pricing/ecalypse-rental-branch-delivery-pricing.php')) {
					$price_branch_distance = $wpdb->get_row($wpdb->prepare('SELECT `price` FROM `' . $wpdb->prefix . 'ecalypse_rental_branch_delivery_pricing` WHERE `id_branch_from` = %d AND `id_branch_to` = %d LIMIT 1', $el, $rl), ARRAY_A);
					if ($price_branch_distance && isset($price_branch_distance['price']) && (int) $price_branch_distance['price'] > 0) {
						$data['total_rental'] += (int) $price_branch_distance['price'];
						$data['summary']['branch_distance_price'] = (int) $price_branch_distance['price'];
					}
				}
			}

			// extra price for pick-up location
			if (defined('ECALYPSERENTALSTARTER_BRANCH_DELIVERY_PRICING__PLUGIN_DIR') && ECALYPSERENTALSTARTER_BRANCH_DELIVERY_PRICING__PLUGIN_DIR != '' && self::is_plugin('ecalypse-rental-branch-delivery-pricing/ecalypse-rental-branch-delivery-pricing.php')) {
				$branch_pickup_prices = get_option('ecalypse_rental_branch_pickup_prices');
				if ($branch_pickup_prices && $el && $include_branch_delivery_fees) {
					$branch_pickup_prices = unserialize($branch_pickup_prices);
					if (isset($branch_pickup_prices[$el])) {
						$data['total_rental'] += (float) $branch_pickup_prices[$el];
						$data['summary']['branch_pick_up_price'] = (float) $branch_pickup_prices[$el];
					}
				}
			}

			// extra price for returning location
			if (defined('ECALYPSERENTALSTARTER_BRANCH_DELIVERY_PRICING__PLUGIN_DIR') && ECALYPSERENTALSTARTER_BRANCH_DELIVERY_PRICING__PLUGIN_DIR != '' && self::is_plugin('ecalypse-rental-branch-delivery-pricing/ecalypse-rental-branch-delivery-pricing.php')) {
				$branch_returning_prices = get_option('ecalypse_rental_branch_returning_prices');
				$return_loc_for_extra_price = ($rl && $el != $rl) ? $rl : ($el ? $el : false);
				if ($branch_returning_prices && $return_loc_for_extra_price && $include_branch_delivery_fees) {
					$branch_returning_prices = unserialize($branch_returning_prices);
					if (isset($branch_returning_prices[$return_loc_for_extra_price])) {
						$data['total_rental'] += (float) $branch_returning_prices[$return_loc_for_extra_price];
						$data['summary']['branch_returning_price'] = (float) $branch_returning_prices[$return_loc_for_extra_price];
					}
				}
			}

			// Get currently set currency
			$global_currency = get_option('ecalypse_rental_global_currency');
			$av_currencies = unserialize(get_option('ecalypse_rental_available_currencies'));
			if (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_currency']) && isset($av_currencies[EcalypseRentalSession::$session['ecalypse_rental_currency']])) {
				$current_currency = EcalypseRentalSession::$session['ecalypse_rental_currency'];
			} else {
				$current_currency = $global_currency;
			}

			// Apply currency settings
			if (isset($data['currency'])) {
				$scheme_currency = $data['currency'];
			} else {
				$scheme_currency = $data['currency'] = $global_currency;
			}
			if ($scheme_currency != $current_currency) {

				if ($current_currency == $global_currency && isset($av_currencies[$scheme_currency])) {

					$rate = $av_currencies[$scheme_currency];
					$data['price'] *= $rate;
					$data['total_rental'] *= $rate;
					$data['total_rental_clear'] *= $rate;
					$data['outside_booking_return'] *= $rate;
					$data['outside_booking_enter'] *= $rate;
					foreach ($data['summary'] as $s_key => $s_price) {
						$data['summary'][$s_key] *= $rate;
					}
					// echo 'Price:' . $scheme->price * $rate . "<br />"; // DEBUG
				} elseif ($scheme_currency != $global_currency) {

					$rate = round($av_currencies[$scheme_currency] / $av_currencies[$current_currency], 2);
					$data['price'] *= $rate;
					$data['total_rental'] *= $rate;
					$data['total_rental_clear'] *= $rate;
					$data['outside_booking_return'] *= $rate;
					$data['outside_booking_enter'] *= $rate;
					foreach ($data['summary'] as $s_key => $s_price) {
						$data['summary'][$s_key] *= $rate;
					}
					//echo "Rate:" . $rate . "<br />"; // DEBUG
					//echo 'Price:' . $scheme->price * $rate . "<br />"; // DEBUG
				} elseif (isset($av_currencies[$current_currency])) {

					$rate = $av_currencies[$current_currency];
					$data['price'] /= $rate;
					$data['total_rental'] /= $rate;
					$data['total_rental_clear'] /= $rate;
					$data['outside_booking_return'] /= $rate;
					$data['outside_booking_enter'] /= $rate;
					foreach ($data['summary'] as $s_key => $s_price) {
						$data['summary'][$s_key] /= $rate;
					}
					//echo "Rate:" . $rate . "<br />"; // DEBUG
					//echo 'Price:' . $scheme->price / $rate . "<br />"; // DEBUG
				}
			}

			$data['tax_price'] = $data['tax_total_rental'] = 0;
			$data['price_with_tax'] = $data['price'];
			$data['total_price_with_tax'] = $data['total_rental'];

			if ((float) $data['vat'] > 0 && isset($data['tax_rates']['vat'])) {
				if (DEBUG_MODE) {
					// DEBUG-MODE: 
					echo '<br>Pripoctena taxa VAT ' . $data['vat'] . ' %.';
				}
				$data['tax_price'] = $data['price'] * ((float) $data['vat'] / 100);
				$data['tax_total_rental'] = $data['total_rental'] * ((float) $data['vat'] / 100);
				$data['tax_total_rental_clear'] = $data['total_rental_clear'] * ((float) $data['vat'] / 100);
				foreach ($data['summary'] as $s_key => $s_price) {
					$data['summary']['tax_' . $s_key] = $data['summary'][$s_key] * ((float) $data['vat'] / 100);
				}
				$data['price_with_tax'] += $data['tax_price'];
				$data['total_price_with_tax'] += $data['tax_total_rental'];
			}

			if ((float) $data['vat_2'] > 0 && isset($data['tax_rates']['vat_2'])) {
				if (DEBUG_MODE) {
					// DEBUG-MODE: 
					echo '<br>Pripoctena taxa VAT ' . $data['vat_2'] . ' %.';
				}
				if ($vat_settings['vat_calculation'] == 2) {
					$data['tax_price_2'] = $data['price_with_tax'] * ((float) $data['vat_2'] / 100);
					$data['tax_total_rental_2'] = $data['total_price_with_tax'] * ((float) $data['vat_2'] / 100);
					$data['tax_total_rental_clear_2'] = $data['total_price_with_tax'] * ((float) $data['vat_2'] / 100);
					foreach ($data['summary'] as $s_key => $s_price) {
						$data['summary']['tax_2_' . $s_key] = $data['summary'][$s_key] * ((float) $data['vat'] / 100) * ((float) $data['vat_2'] / 100);
					}
				} else {
					$data['tax_price_2'] = $data['price'] * ((float) $data['vat_2'] / 100);
					$data['tax_total_rental_2'] = $data['total_rental'] * ((float) $data['vat_2'] / 100);
					$data['tax_total_rental_clear_2'] = $data['total_rental_clear'] * ((float) $data['vat_2'] / 100);
					foreach ($data['summary'] as $s_key => $s_price) {
						$data['summary']['tax_2_' . $s_key] = $data['summary'][$s_key] * ((float) $data['vat_2'] / 100);
					}
				}
				$data['price_with_tax'] += $data['tax_price_2'];
				$data['total_price_with_tax'] += $data['tax_total_rental_2'];
			}

			if ((float) $data['vat_3'] > 0 && isset($data['tax_rates']['vat_3'])) {
				if (DEBUG_MODE) {
					// DEBUG-MODE: 
					echo '<br>Pripoctena taxa VAT ' . $data['vat_3'] . ' %.';
				}
				if ($vat_settings['vat_calculation'] == 2) {
					$data['tax_price_3'] = $data['price_with_tax'] * ((float) $data['vat_3'] / 100);
					$data['tax_total_rental_3'] = $data['total_price_with_tax'] * ((float) $data['vat_3'] / 100);
					$data['tax_total_rental_clear_3'] = $data['total_price_with_tax'] * ((float) $data['vat_3'] / 100);
					foreach ($data['summary'] as $s_key => $s_price) {
						$data['summary']['tax_3_' . $s_key] = $data['summary'][$s_key] * ((float) $data['vat'] / 100) * ((float) $data['vat_3'] / 100);
					}
				} else {
					$data['tax_price_3'] = $data['price'] * ((float) $data['vat_3'] / 100);
					$data['tax_total_rental_3'] = $data['total_rental'] * ((float) $data['vat_3'] / 100);
					$data['tax_total_rental_clear_3'] = $data['total_rental_clear'] * ((float) $data['vat_3'] / 100);
					foreach ($data['summary'] as $s_key => $s_price) {
						$data['summary']['tax_3_' . $s_key] = $data['summary'][$s_key] * ((float) $data['vat_3'] / 100);
					}
				}
				$data['price_with_tax'] += $data['tax_price_3'];
				$data['total_price_with_tax'] += $data['tax_total_rental_3'];
			}
			/*
			  if ($el) {

			  $locations = self::get_locations();
			  if (isset($locations[$el]) && isset($locations[$el]->branch_tax)) {
			  if (!empty($locations[$el]->branch_tax)) {
			  $data['summary']['branch_specific_tax'] = array();
			  $lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
			  $lang = strtolower(end(explode('_', $lang)));

			  foreach ($locations[$el]->branch_tax as $k => $v) {
			  $price = $data['price'] * ((float) $v['tax'] / 100);
			  $tax_name = $v['name'];
			  if ($lang != 'gb') {
			  if (isset($v['name_translations'][$lang]) && $v['name_translations'][$lang] != '') {
			  $tax_name = $v['name_translations'][$lang];
			  }
			  }
			  $data['summary']['branch_specific_tax'][$k] = array('price' => $price, 'text' => $v['tax'].'% '.$tax_name);
			  $data['price_with_tax'] += $price;
			  $data['total_price_with_tax'] += $price;
			  }
			  }
			  }
			  } */

			$data['currency'] = $current_currency;
			$data['cc_before'] = self::get_currency_symbol('before', $data['currency']);
			$data['cc_after'] = self::get_currency_symbol('after', $data['currency']);
			if ($period == 'am' || $period == 'pm') {
				$data['pr_type'] = 0;
			}
			//print_r($data);
			return $data;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get price for the vehicle
	 */
	public function get_prices($type = 'fleet', $id, $date_from, $date_to, $promocode = NULL, $el = false, $rl = false, $dl = false, $period = false) {
		global $wpdb;

		if (defined('ECALYPSERENTALSTARTER_AMERICAN_PRICING_VERSION')) {
			if (self::is_plugin('ecalypse-rental-american-pricing/ecalypse-rental-american-pricing.php')) {
				return EcalypseRental_American_Pricing::get_american_prices($type, $id, $date_from, $date_to, $promocode, $el, $rl, $dl);
			}
		}

		try {
			$vat_settings = self::get_vat_settings();

			$date_diff = abs(strtotime($date_to) - strtotime($date_from));
			$diff_days = intval($date_diff / 86400);
			$diff_hours = intval(($date_diff % 86400) / 3600);
			$diff_minutes = intval(($date_diff % 86400) / 60);

			$hour_pricing_after_day = get_option('ecalypse_rental_hour_pricing_after_day');
			$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
			if (($hour_pricing_after_day == 'yes' && $diff_days > 0) && $time_pricing_type != 'half_day') {
				// compare two method pricing
				$data_hour = self::get_price($type, $id, $date_from, $date_to, sanitize_key($promocode), (int) $el, (int) $rl, $dl, false);
				$data_day = self::get_price($type, $id, $date_from, $date_to, sanitize_key($promocode), (int) $el, (int) $rl, $dl, true);
				if ($data_hour['total_price_with_tax'] > $data_day['total_price_with_tax']) {
					$data = $data_day;
				} else {
					$data = $data_hour;
				}
				if (isset($data_hour['no_range_hours'])) {
					$data = $data_day;
				}
			} else {
				$data = self::get_price($type, $id, $date_from, $date_to, sanitize_key($promocode), (int) $el, (int) $rl, $dl, true, sanitize_key($period));
			}

			return $data;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function get_delivery_price() {
		global $wpdb;

		try {

			$price = get_option('ecalypse_rental_delivery_price');

			// Get currently set currency
			$global_currency = get_option('ecalypse_rental_global_currency');
			$av_currencies = unserialize(get_option('ecalypse_rental_available_currencies'));
			if (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_currency']) && isset($av_currencies[EcalypseRentalSession::$session['ecalypse_rental_currency']])) {
				$current_currency = EcalypseRentalSession::$session['ecalypse_rental_currency'];
			} else {
				$current_currency = $global_currency;
			}

			// Apply currency settings
			if ($global_currency != $current_currency && isset($av_currencies[$current_currency])) {
				$rate = $av_currencies[$current_currency];
				$price /= $rate;
			}

			return $price;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get vehicle detail
	 */
	public function get_vehicle_detail($id_fleet, $filters) {
		global $wpdb;

		try {

			// Apply filters
			// Date filters
			if (!isset($filters['fd']) || empty($filters['fd'])) {
				$filters['fd'] = Date('Y-m-d');
			}
			if (!isset($filters['fh']) || empty($filters['fh'])) {
				$filters['fh'] = '12:00';
			}
			if (!isset($filters['td']) || empty($filters['td'])) {
				$filters['td'] = Date('Y-m-d', strtotime("+1 day"));
			}
			if (!isset($filters['th']) || empty($filters['th'])) {
				$filters['th'] = '12:00';
			}

			$date_from = date('Y-m-d', strtotime($filters['fd'])) . ' ' . self::valid_time($filters['fh']);
			$date_to = date('Y-m-d', strtotime($filters['td'])) . ' ' . self::valid_time($filters['th']);

			// Promocode
			$promocode = ((isset($filters['promo']) && !empty($filters['promo'])) ? sanitize_key($filters['promo']) : NULL);


			$overbooking = get_option('ecalypse_rental_overbooking');
			$sql_having = '';
			if ($overbooking && $overbooking == 'no') {

				$sql = 'SELECT f.*, (SELECT count(*) FROM
									(
									SELECT  @lastR as lr,
											tct.`vehicle_id`,
										  @lastR := tct.return_date,
										  @lastV as lv, 
										  @lastV := tct.vehicle_id, 
										  tct.enter_date 
										  FROM ( select @lastR := \'2100-01-01\', @lastV := 0 ) as tmpvars, (SELECT bi.`vehicle_id`, b.return_date, b.enter_date FROM `' . $wpdb->prefix . 'ecalypse_rental_booking_items` bi 
										  INNER JOIN `' . EcalypseRental::$db['booking'] . '` b ON b.`id_booking` = bi.`id_booking`
										  WHERE b.`deleted` IS NULL 
										  AND b.`status` <> 2
										  AND ((b.`enter_date` <= "' . $wpdb->escape($date_from) . '" AND b.`return_date` >= "' . $wpdb->escape($date_from) . '") OR
																	 (b.`enter_date` <= "' . $wpdb->escape($date_to) . '" AND b.`return_date` >= "' . $wpdb->escape($date_to) . '") OR
																	 (b.`enter_date` >= "' . $wpdb->escape($date_from) . '" AND b.`return_date` <= "' . $wpdb->escape($date_to) . '"))
									order by bi.vehicle_id, b.enter_date) as tct
									HAVING lr > tct.enter_date OR tct.vehicle_id <> lv
									) as count_table WHERE  count_table.`vehicle_id` = f.`id_fleet` ) as `booked` 
														FROM `' . EcalypseRental::$db['fleet'] . '` f WHERE f.`id_fleet` = %d AND f.`deleted` IS NULL';
				//$wpdb->query('SELECT ');
				//$sql_having = ' HAVING `rented_cars`  < f.`number_vehicles` ';
			} else {
				$sql = 'SELECT f.*, 0 as `booked` FROM `' . EcalypseRental::$db['fleet'] . '` f WHERE f.`id_fleet` = %d AND f.`deleted` IS NULL';
			}

			$vehicle = $wpdb->get_row($wpdb->prepare($sql.$sql_having, $id_fleet));
			if (DEBUG_MODE) {
				echo 'debug mode SQL: ';
				echo $wpdb->last_query;
				//print_r($vehicle);die();
			}

			// Prices
			if ($vehicle && ($vehicle->number_vehicles > $vehicle->booked || ($overbooking && $overbooking == 'yes'))) {
				
				// get additional vehicles
				$vehicle->additional_vehicles = self::get_vehicles($_GET);
				
				if ($vehicle->additional_vehicles && isset($vehicle->additional_vehicles['results'])) {
					$vehicle->additional_vehicles = $vehicle->additional_vehicles['results'];
				}
				$vehicle->prices = self::get_prices('fleet', $vehicle->id_fleet, $date_from, $date_to, $promocode, (isset($filters['el']) ? (int) $filters['el'] : false), (isset($filters['rl']) ? (int) $filters['rl'] : false), (isset($filters['dl']) ? true : false), (isset($filters['p']) ? $filters['p'] : false));
				// Free km
				$vehicle->free_distance_total = self::get_vehicle_free_distance($vehicle);
			}

			return $vehicle;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get vehicle parameters
	 */
	public function get_vehicle_parameters($id_vehicle) {
		global $wpdb;
		try {

			return $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['fleet'] . '` WHERE `id_fleet` = %d', $id_vehicle));
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Return all fleet parameters values
	 * @param type $id_vehicle
	 */
	public static function get_fleet_parameters_values($id_vehicle) {
		global $wpdb;
		return $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . $wpdb->prefix . 'ecalypse_rental_fleet_parameters_values` WHERE `fleet_id` = %d', $id_vehicle));
	}

	/**
	 * Return name of parameter and his value
	 * @param type $id_parameter
	 * @param type $value
	 * @return type
	 */
	public static function return_parameter_value($id_parameter, $value, $before = '', $after = '') {
		if (!isset(self::$all_fleet_parameters)) {
			$all_fleet_parameters = array();
			$fleet_parameters = self::get_fleet_parameters(true);
			foreach ($fleet_parameters as $p) {
				$all_fleet_parameters[$p->id_fleet_parameter] = (array) $p;
				$all_fleet_parameters[$p->id_fleet_parameter]['name'] = unserialize($all_fleet_parameters[$p->id_fleet_parameter]['name']);
				$all_fleet_parameters[$p->id_fleet_parameter]['values'] = unserialize($all_fleet_parameters[$p->id_fleet_parameter]['values']);
			}
			self::$all_fleet_parameters = $all_fleet_parameters;
		}

		if (!isset(self::$all_fleet_parameters[$id_parameter])) {
			return '';
		}

		$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
		$lang = strtolower(end(explode('_', $lang)));

		$return = $before;
		$name = isset(self::$all_fleet_parameters[$id_parameter]['name'][$lang]) ? self::$all_fleet_parameters[$id_parameter]['name'][$lang] : self::$all_fleet_parameters[$id_parameter]['name']['gb'];
		$return .= '<strong>' . $name . '</strong>: ';
		if (self::$all_fleet_parameters[$id_parameter]['type'] == 1) {
			// range
			$return .= $value;
		} else {
			// value			
			$return .= isset(self::$all_fleet_parameters[$id_parameter]['values'][$lang][(int) $value]) ? self::$all_fleet_parameters[$id_parameter]['values'][$lang][$value] : 'neni';
		}
		return $return . $after;
	}

	public static function get_fleet_parameters($only_active = false) {
		global $wpdb;

		try {

			$params = $wpdb->get_results('SELECT *
												 FROM `' . $wpdb->prefix . 'ecalypse_rental_fleet_parameters`' . ($only_active ? ' WHERE `active` = 1' : '')
			);
			return $params;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get extras parameters
	 */
	public function get_extras_parameters($id_extras) {
		global $wpdb;

		try {

			return $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['extras'] . '` WHERE `id_extras` = %d', $id_extras));
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get vehicle extras
	 */
	public function get_vehicle_extras($id_fleet, $filters) {
		global $wpdb;

		try {

			// Apply filters
			// Date filters
			if (!isset($filters['fd']) || empty($filters['fd'])) {
				$filters['fd'] = Date('Y-m-d');
			}
			if (!isset($filters['fh']) || empty($filters['fh'])) {
				$filters['fh'] = '12:00';
			}
			if (!isset($filters['td']) || empty($filters['td'])) {
				$filters['td'] = Date('Y-m-d', strtotime("+1 day"));
			}
			if (!isset($filters['th']) || empty($filters['th'])) {
				$filters['th'] = '12:00';
			}

			$date_from = date('Y-m-d', strtotime($filters['fd'])) . ' ' . $filters['fh'];
			$date_to = date('Y-m-d', strtotime($filters['td'])) . ' ' . $filters['th'];

			// Promocode
			$promocode = ((isset($filters['promo']) && !empty($filters['promo'])) ? $filters['promo'] : NULL);

			$sql = 'SELECT e.*
							FROM `' . EcalypseRental::$db['fleet_extras'] . '` fe
							LEFT JOIN `' . EcalypseRental::$db['extras'] . '` e ON e.`id_extras` = fe.`id_extras`
							WHERE fe.`id_fleet` = %d AND e.`deleted` IS NULL';

			$extras = $wpdb->get_results($wpdb->prepare($sql, $id_fleet));

			// Get prices for results
			if ($extras && !empty($extras)) {
				foreach ($extras as $key => $val) {
					$extras[$key]->prices = self::get_prices('extras', $val->id_extras, $date_from, $date_to, $promocode, false, false, false, (isset($filters['p']) ? $filters['p'] : false));
				}
			}

			return $extras;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Save booking to database
	 */
	function save_booking($data) {
		global $wpdb;

		try {
			$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
			$disable_time = get_option('ecalypse_rental_disable_time');
			if ($disable_time == 'yes') {
				$disable_time = true;
			} else {
				$disable_time = false;
			}
			$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
			if ($time_pricing_type == 'half_day') {
				$disable_time = true;
			}
			$id_order = self::generate_unique_order_id();

			// Get location details
			$enter_loc_id = $return_loc_id = (int) $data['el'];
			$enter_loc = $return_loc = self::get_location_name($enter_loc_id);
			if (!empty($data['rl']) && (int) $data['rl'] > 0) {
				$return_loc_id = (int) $data['rl'];
				$return_loc = self::get_location_name((int) $data['rl']);
			}

			// Get vehicle details
			$vehicle = self::get_vehicle_parameters((int) $data['id_car']);
			$consumption_metric = get_option('ecalypse_rental_consumption');
			$currency = get_option('ecalypse_rental_global_currency');
			$distance_metric = get_option('ecalypse_rental_distance_metric');

			$date_from = Date('Y-m-d H:i:s', strtotime($data['fd'] . ' ' . $data['fh']));
			$date_to = Date('Y-m-d H:i:s', strtotime($data['td'] . ' ' . $data['th']));
			if ($time_pricing_type == 'half_day' && $data['p'] !== 'day') {
				$data['th'] = $data['fh'];
				$data['td'] = $data['fd'];
				$date_to = $date_from;
			}

			$date_diff = strtotime($date_to) - strtotime($date_from);
			if ($date_diff < 0) {
				if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
					Header('Location: ' . $_SERVER['HTTP_REFERER']);
					Exit;
				} else {
					Header('Location: ' . home_url());
					Exit;
				}
				exit;
			}
			$date_diff = abs($date_diff);
			$diff_days = intval($date_diff / 86400);
			$diff_hours = intval(($date_diff % 86400) / 3600);
			$diff_minutes = intval(($date_diff % 86400) / 60);

			if ($diff_days >= 1 && ($diff_hours > 0 || $diff_minutes > 0)) {
				++$diff_days; // If you pass by 30 minutes and more, it 1 day more
			}

			// get vehicle price
			$main_price = self::get_prices('fleet', (int) $data['id_car'], $date_from, $date_to, $data['promo'], (isset($data['el']) ? $data['el'] : false), (isset($data['rl']) ? $data['rl'] : false), (isset($data['dl']) ? true : false), (isset($data['p']) ? $data['p'] : false));

			/*
			 * TSDweb integration (activated by another plugin)
			 */
			try {
				$tsd = unserialize(get_option('ecalypse_rental_tsdweb'));
				if ($tsd && !empty($tsd) && is_array($tsd)) {
					if (defined('ECALYPSERENTALSTARTER_TSDWEB__PLUGIN_DIR') && ECALYPSERENTALSTARTER_TSDWEB__PLUGIN_DIR != '') {
						if (file_exists(ECALYPSERENTALSTARTER_TSDWEB__PLUGIN_DIR . DIRECTORY_SEPARATOR . 'class.ecalypse-rental-tsdweb.php')) {
							require_once ECALYPSERENTALSTARTER_TSDWEB__PLUGIN_DIR . DIRECTORY_SEPARATOR . 'class.ecalypse-rental-tsdweb.php';

							$data['bid_enter'] = $data['bid_return'] = self::get_location_id((int) $data['el']);
							if (!empty($data['rl']) && (int) $data['rl'] > 0 && $_GET['dl'] == 'on') {
								$data['bid_return'] = self::get_location_id((int) $data['rl']);
							}

							$data['class_code'] = $vehicle->class_code;
							$data['rate_id'] = $main_price['rate_id'];

							// Get month price
							$tf = strtotime($date_from);
							$monthly_date_to = Date('Y-m-d H:i:s', mktime(Date('H', $tf), Date('i', $tf), Date('s', $tf), Date('m', $tf) + Date('t', $tf), Date('d', $tf), Date('Y', $tf))); // + 1 month
							$monthly = self::get_prices('fleet', (int) $data['id_car'], $date_from, $monthly_date_to, $data['promo'], (isset($data['el']) ? $data['el'] : false), (isset($data['rl']) ? $data['rl'] : false), (isset($data['dl']) ? true : false), (isset($data['p']) ? $data['p'] : false));
							$data['monthly_rate'] = (float) $monthly['total_rental'] + (float) $monthly['tax_total_rental'];

							// Get week price
							$weekly_date_to = Date('Y-m-d H:i:s', mktime(Date('H', $tf), Date('i', $tf), Date('s', $tf), Date('m', $tf), Date('d', $tf) + 7, Date('Y', $tf))); // + 7 days
							$weekly = self::get_prices('fleet', (int) $data['id_car'], $date_from, $weekly_date_to, $data['promo'], (isset($data['el']) ? $data['el'] : false), (isset($data['rl']) ? $data['rl'] : false), (isset($data['dl']) ? true : false), (isset($data['p']) ? $data['p'] : false));
							$data['weekly_rate'] = (float) $weekly['total_rental'] + (float) $weekly['tax_total_rental'];

							// Get day price
							$daily_date_to = Date('Y-m-d H:i:s', mktime(Date('H', $tf), Date('i', $tf), Date('s', $tf), Date('m', $tf), Date('d', $tf) + 1, Date('Y', $tf))); // + 1 day
							$daily = self::get_prices('fleet', (int) $data['id_car'], $date_from, $daily_date_to, $data['promo'], (isset($data['el']) ? $data['el'] : false), (isset($data['rl']) ? $data['rl'] : false), (isset($data['dl']) ? true : false), (isset($data['p']) ? $data['p'] : false));
							$data['daily_rate'] = (float) $daily['total_rental'] + (float) $daily['tax_total_rental'];

							EcalypseRental_Tsdweb::api_send_data($data);
						}
					}
				}
			} catch (Exception $e) {
				
			}
			/*
			 * END OF TSD Web integration
			 */
			$booking_statuses = maybe_unserialize(get_option('ecalypse_rental_booking_statuses'));
			$status = 1; // confirmed
			if (is_array($booking_statuses) && isset($booking_statuses['offline'])) {
				$status = $booking_statuses['offline'];
			}
			if (((int) $_POST['paypal'] == 1) && (float) $_POST['total_rental'] > 0) {
				$payments_others = unserialize(get_option('ecalypse_rental_available_payments_others'));
				switch (sanitize_text_field($_POST['payment_option'])) {
					default:
						$status = 2;
						break;
				}
			}

			$user_id = 0;
			if (defined('ECALYPSERENTALSTARTER_CLIENT_AREA_VERSION') && self::is_plugin('ecalypse-rental-client-area/ecalypse-rental-client-area.php')) {
				$user_id = EcalypseRental_Client_Area::return_or_register_user();
			}

			$status = apply_filters('ecalypse_rental_save_booking_status_filter', $status);

			$lng = 'en_GB';
			if (isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) {
				$lng = EcalypseRentalSession::$session['ecalypse_rental_language'];
			}

			if (!isset($data['vat'])) {
				$data['vat'] = '';
			}

			if (!isset($data['flight'])) {
				$data['flight'] = '';
			}

			if (!isset($data['company'])) {
				$data['company'] = '';
			}

			if (!isset($data['license'])) {
				$data['license'] = '';
			}

			if (!isset($data['id_card'])) {
				$data['id_card'] = '';
			}

			if (!isset($data['partner_code'])) {
				$data['partner_code'] = '';
			}

			if (!isset($data['newsletter'])) {
				$data['newsletter'] = 0;
			}

			if (!isset($data['country'])) {
				$data['country'] = 0;
			}

			if (!isset($data['comment'])) {
				$data['comment'] = '';
			}

			if (!isset($data['payment_selected_option'])) {
				$data['payment_selected_option'] = '';
			}
			$dump = $data;

			/* if (isset($main_price) && isset($main_price['pr_type']) && $main_price['pr_type'] == 2) {
			  // hourly
			  $free_distance = (isset($main_price) && isset($main_price['diff_hours']) ? (int)$main_price['diff_hours'] : 1) * $vehicle->free_distance_hour;
			  } else {
			  // daily
			  $free_distance = (isset($main_price) && isset($main_price['diff_days']) ? (int)$main_price['diff_days'] : 1) * $vehicle->free_distance;
			  } */
			$vehicle->prices = $main_price;
			$free_distance = self::get_vehicle_free_distance($vehicle);
			$free_distance .= ($free_distance > 0 ? ' ' . $distance_metric : '');

			$arr = array('id_order' => $id_order,
				'first_name' => $data['first_name'],
				'last_name' => $data['last_name'],
				'email' => $data['email'],
				'phone' => $data['phone'],
				'street' => $data['street'],
				'city' => $data['city'],
				'zip' => $data['zip'],
				'country' => $data['country'],
				'company' => $data['company'],
				'vat' => $data['vat'],
				'flight' => $data['flight'],
				'license' => $data['license'],
				'id_card' => $data['id_card'],
				'terms' => $data['terms'],
				'newsletter' => $data['newsletter'],
				'enter_loc' => $enter_loc,
				'enter_date' => $date_from,
				'return_loc' => $return_loc,
				'return_date' => $date_to,
				'id_enter_branch' => $enter_loc_id,
				'id_return_branch' => $return_loc_id,
				'vehicle' => $vehicle->name,
				'vehicle_id' => $vehicle->id_fleet,
				'vehicle_ac' => $vehicle->ac,
				'vehicle_luggage' => $vehicle->luggage,
				'vehicle_seats' => $vehicle->seats,
				'vehicle_fuel' => $vehicle->fuel,
				'vehicle_picture' => $vehicle->picture,
				'vehicle_consumption' => $vehicle->consumption,
				'vehicle_consumption_metric' => $consumption_metric,
				'vehicle_transmission' => $vehicle->transmission,
				'vehicle_free_distance' => $free_distance,
				'vehicle_deposit' => $vehicle->deposit . ' ' . $currency,
				'payment_option' => $data['payment_selected_option'],
				'comment' => $data['comment'],
				'partner_code' => $data['partner_code'],
				'status' => $status,
				'currency' => $currency,
				'id_user' => $user_id,
				'lng' => $lng
			);

			$wpdb->insert(EcalypseRental::$db['booking'], $arr);

			$id_booking = $wpdb->insert_id;

			$arr = array('id_booking' => $id_booking,
				'vehicle_id' => $vehicle->id_fleet);
			$wpdb->insert($wpdb->prefix . 'ecalypse_rental_booking_items', $arr);
			$id_booking_item = $wpdb->insert_id;

			do_action('ecalypse_rental_after_save_booking_to_db', $id_booking);

			$dump['lng'] = $lng;
			$dump['id_user'] = $user_id;
			$dump['currency'] = $currency;
			$dump['enter_loc'] = $enter_loc;
			$dump['return_loc'] = $return_loc;
			$dump['vehicle_info'] = array('vehicle' => $vehicle->name,
				'vehicle_id' => $vehicle->id_fleet,
				'vehicle_ac' => $vehicle->ac,
				'vehicle_luggage' => $vehicle->luggage,
				'vehicle_seats' => $vehicle->seats,
				'vehicle_fuel' => $vehicle->fuel,
				'vehicle_picture' => $vehicle->picture,
				'vehicle_consumption' => $vehicle->consumption,
				'vehicle_consumption_metric' => $consumption_metric,
				'vehicle_transmission' => $vehicle->transmission,
				'vehicle_free_distance' => $free_distance,
				'vehicle_deposit' => $vehicle->deposit . ' ' . $currency);
			$dump['prices'] = array();

			// Add prices/extras
			// Vehicle price (+ tax)
			$total_price = 0;
			$base_price = 0;
			if (defined('ECALYPSERENTALSTARTER_AMERICAN_PRICING_VERSION')) {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
			}
			if (defined('ECALYPSERENTALSTARTER_AMERICAN_PRICING_VERSION') && self::is_plugin('ecalypse-rental-american-pricing/ecalypse-rental-american-pricing.php')) {
				$total_price += EcalypseRental_American_Pricing::after_save_booking($main_price, $id_booking, $vehicle);
			} else {
				$item_text = $vehicle->name . ', ' . ($disable_time ? substr($date_from, 0, -9) : $date_from) . ' (' . $enter_loc . ')';
				if ($time_pricing_type !== 'half_day' || ($time_pricing_type == 'half_day' && $data['p'] == 'day')) {
					$item_text .= ' - ' . ($disable_time ? substr($date_to, 0, -9) : $date_to) . ' (' . $return_loc . ')';
				} else {
					$item_text .= ' ' . ($data['p'] == 'pm' ? 'PM' : 'AM');
				}
				$arr = array('id_booking' => $id_booking,
					'name' => $item_text,
					'price' => (float) $main_price['total_rental_clear'],
					'currency' => $main_price['currency'],
					'item_id' => $id_booking_item);
				$dump['prices'][] = $arr;
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
				$total_price += (float) $main_price['total_rental_clear'];
			}

			if (isset($main_price['summary']) && isset($main_price['summary']['branch_distance_price']) && (float) $main_price['summary']['branch_distance_price'] > 0) {
				$arr = array('id_booking' => $id_booking,
					'name' => __('Fee for returning in different location.', 'ecalypse-rental'),
					'price' => (float) $main_price['summary']['branch_distance_price'],
					'currency' => $main_price['currency']);
				$dump['prices'][] = $arr;
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
				$total_price += (float) $main_price['summary']['branch_distance_price'];
			}

			if (isset($main_price['summary']) && isset($main_price['summary']['branch_pick_up_price']) && (float) $main_price['summary']['branch_pick_up_price'] > 0) {
				$arr = array('id_booking' => $id_booking,
					'name' => __('Fee for pick-up on specific branch.', 'ecalypse-rental'),
					'price' => (float) $main_price['summary']['branch_pick_up_price'],
					'currency' => $main_price['currency']);
				$dump['prices'][] = $arr;
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
				$total_price += (float) $main_price['summary']['branch_pick_up_price'];
			}

			if (isset($main_price['summary']) && isset($main_price['summary']['branch_returning_price']) && (float) $main_price['summary']['branch_returning_price'] > 0) {
				$arr = array('id_booking' => $id_booking,
					'name' => __('Fee for returning on specific branch.', 'ecalypse-rental'),
					'price' => (float) $main_price['summary']['branch_returning_price'],
					'currency' => $main_price['currency']);
				$dump['prices'][] = $arr;
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
				$total_price += (float) $main_price['summary']['branch_returning_price'];
			}

			if (isset($main_price['outside_booking_enter']) && (float) $main_price['outside_booking_enter'] > 0) {
				$arr = array('id_booking' => $id_booking,
					'name' => __('Fee for pick-up outside booking hours.', 'ecalypse-rental'),
					'price' => (float) $main_price['outside_booking_enter'],
					'currency' => $main_price['currency']);
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
				$total_price += (float) $main_price['outside_booking_enter'];
			}

			if (isset($main_price['outside_booking_return']) && (float) $main_price['outside_booking_return'] > 0) {
				$arr = array('id_booking' => $id_booking,
					'name' => __('Fee for returning outside booking hours.', 'ecalypse-rental'),
					'price' => (float) $main_price['outside_booking_return'],
					'currency' => $main_price['currency']);
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
				$total_price += (float) $main_price['outside_booking_return'];
			}

			$base_price = $total_price;

			$vat_item = array();
			$vat_item_2 = array();
			$vat_item_3 = array();
			//if ((float) $main_price['tax_total_rental'] > 0) {
			$vat_item = array('id_booking' => $id_booking,
				'name' => $main_price['vat'] . __('% sales tax', 'ecalypse-rental'),
				'price' => round((float) $main_price['tax_total_rental'], 2),
				'currency' => $main_price['currency']);
			//$prices_email .= $arr['name'].': '.$main_price['cc_before'].number_format((float) $arr['price'],2,'.').$main_price['cc_after']."\n";
			//$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
			$total_price += round((float) $main_price['tax_total_rental'], 2);
			//}
			//if ((float) $main_price['tax_total_rental_2'] > 0) {
			$vat_item_2 = array('id_booking' => $id_booking,
				'name' => $main_price['vat_2'] . __('% sales tax', 'ecalypse-rental'),
				'price' => round((float) $main_price['tax_total_rental_2'], 2),
				'currency' => $main_price['currency']);
			//$prices_email .= $arr['name'].': '.$main_price['cc_before'].number_format((float) $arr['price'],2,'.').$main_price['cc_after']."\n";
			//$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
			$total_price += round((float) $main_price['tax_total_rental_2'], 2);
			//}
			//if ((float) $main_price['tax_total_rental_3'] > 0) {
			$vat_item_3 = array('id_booking' => $id_booking,
				'name' => $main_price['vat_3'] . __('% sales tax', 'ecalypse-rental'),
				'price' => round((float) $main_price['tax_total_rental_3'], 2),
				'currency' => $main_price['currency']);
			//$prices_email .= $arr['name'].': '.$main_price['cc_before'].number_format((float) $arr['price'],2,'.').$main_price['cc_after']."\n";
			//$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
			$total_price += round((float) $main_price['tax_total_rental_3'], 2);
			//}

			$total_items = 1;
			$add_to_discount = 0;
			$additional_vehicles_count = 0;
			$multiple_rental = get_option('ecalypse_rental_multiple_rental');
			if (isset($data['additional']) && $multiple_rental && $multiple_rental == 1) {
				foreach ($data['additional'] as $additional_vehicle) {
					//$main_price = self::get_prices('fleet', (int) $data['id_car'], $date_from, $date_to, $data['promo'], (isset($data['el']) ? $data['el'] : false), (isset($data['rl']) ? $data['rl'] : false), (isset($data['dl']) ? true : false)
					$additional_vehicle_detail = self::get_vehicle_detail((int) $additional_vehicle, $data);

					if ($additional_vehicle_detail && isset($additional_vehicle_detail->prices)) {
						$arr = array('id_booking' => $id_booking,
							'vehicle_id' => $additional_vehicle);
						$wpdb->insert($wpdb->prefix . 'ecalypse_rental_booking_items', $arr);
						$id_booking_item = $wpdb->insert_id;

						$arr = array('id_booking' => $id_booking,
							'name' => $additional_vehicle_detail->name . ', ' . $date_from . ' (' . $enter_loc . ') - ' . $date_to . ' (' . $return_loc . ')',
							'price' => (float) $additional_vehicle_detail->prices['total_rental'],
							'currency' => $additional_vehicle_detail->prices['currency'],
							'item_id' => $id_booking_item);
						$dump['prices'][] = $arr;
						$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
						$total_items++;
						$additional_vehicles_count++;
						$total_price += (float) $additional_vehicle_detail->prices['total_rental'];
						$base_price += (float) $additional_vehicle_detail->prices['total_rental'];
						$add_to_discount += $additional_vehicle_detail->prices['total_price_with_tax'];

						if ((float) $additional_vehicle_detail->prices['tax_total_rental'] > 0) {
							$vat_item['price'] += round((float) $additional_vehicle_detail->prices['tax_total_rental'], 2);
							/* $arr = array('id_booking' => $id_booking,
							  'name' 			=> $extras_detail->name . ' - ' . $extras_prices['vat'] . '% Value Added Tax',
							  'price' 			=> (float) $extras_prices['tax_total_rental'],
							  'currency' 	=> $extras_prices['currency']);
							  $wpdb->insert(EcalypseRental::$db['booking_prices'], $arr); */
							$total_price += round((float) $additional_vehicle_detail->prices['tax_total_rental'], 2);
						}

						if ((float) $additional_vehicle_detail->prices['tax_total_rental_2'] > 0) {
							$vat_item_2['price'] += round((float) $additional_vehicle_detail->prices['tax_total_rental_2'], 2);
							$total_price += round((float) $additional_vehicle_detail->prices['tax_total_rental_2'], 2);
						}

						if ((float) $additional_vehicle_detail->prices['tax_total_rental_3'] > 0) {
							$vat_item_3['price'] += round((float) $additional_vehicle_detail->prices['tax_total_rental_3'], 2);
							$total_price += round((float) $additional_vehicle_detail->prices['tax_total_rental_3'], 2);
						}
					}
				}
				if ($total_items > 1) {
					$wpdb->update(EcalypseRental::$db['booking'], array('total_items' => $total_items), array('id_booking' => $id_booking));
				}
			}

			// Extras prices
			if (!isset($data['extras'])) {
				$data['extras'] = array();
			}
			$extras = self::get_vehicle_extras((int) $data['id_car'], array());
			foreach ($extras as $ex) {
				if ($ex->mandatory == 1) {
					if (!in_array($ex->id_extras, $data['extras'])) {
						$data['extras'][] = $ex->id_extras;
					}
				}
			}

			if (isset($data['extras']) && !empty($data['extras'])) {
				foreach ($data['extras'] as $key => $id_extras) {

					// @todo: More drivers.

					$extras_detail = self::get_extras_parameters((int) $id_extras);
					$extras_prices = self::get_prices('extras', (int) $id_extras, $date_from, $date_to, $data['promo'], false, false, false, (isset($data['p']) ? $data['p'] : false));

					if ($extras_detail->max_additional_drivers > 0 && (int) $data['drivers'] > 0) {
						$arr = array('id_booking' => $id_booking,
							'name' => $data['drivers'] . 'x ' . $extras_detail->name,
							'price' => (float) $extras_prices['total_rental'] * $data['drivers'],
							'currency' => $extras_prices['currency'],
							'extras_id' => $extras_detail->id_extras);
						$dump['prices'][] = $arr;
						$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
						$total_price += (float) $extras_prices['total_rental'] * $data['drivers'];
						$base_price += (float) $extras_prices['total_rental'] * $data['drivers'];

						if ((float) $extras_prices['tax_total_rental'] > 0) {
							$vat_item['price'] += round((float) $extras_prices['tax_total_rental'], 2) * $data['drivers'];
							/* $arr = array('id_booking' => $id_booking,
							  'name' 			=> $data['drivers'].'x '.$extras_detail->name . ' - ' . $extras_prices['vat'] . '% Value Added Tax',
							  'price' 			=> (float) $extras_prices['tax_total_rental'] * $data['drivers'],
							  'currency' 	=> $extras_prices['currency']);
							  $wpdb->insert(EcalypseRental::$db['booking_prices'], $arr); */
							$total_price += round((float) $extras_prices['tax_total_rental'], 2) * $data['drivers'];
						}
						if ((float) $extras_prices['tax_total_rental_2'] > 0) {
							$vat_item_2['price'] += round((float) $extras_prices['tax_total_rental_2'], 2) * $data['drivers'];
							$total_price += round((float) $extras_prices['tax_total_rental_2'], 2) * $data['drivers'];
						}
						if ((float) $extras_prices['tax_total_rental_3'] > 0) {
							$vat_item_3['price'] += round((float) $extras_prices['tax_total_rental_3'], 2) * $data['drivers'];
							$total_price += round((float) $extras_prices['tax_total_rental_3'], 2) * $data['drivers'];
						}
					} else {
						$arr = array('id_booking' => $id_booking,
							'name' => $extras_detail->name,
							'price' => (float) $extras_prices['total_rental'],
							'currency' => $extras_prices['currency'],
							'extras_id' => $extras_detail->id_extras);
						$dump['prices'][] = $arr;
						$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
						$total_price += (float) $extras_prices['total_rental'];
						$base_price += (float) $extras_prices['total_rental'];

						if ((float) $extras_prices['tax_total_rental'] > 0) {
							$vat_item['price'] += round((float) $extras_prices['tax_total_rental'], 2);
							/* $arr = array('id_booking' => $id_booking,
							  'name' 			=> $extras_detail->name . ' - ' . $extras_prices['vat'] . '% Value Added Tax',
							  'price' 			=> (float) $extras_prices['tax_total_rental'],
							  'currency' 	=> $extras_prices['currency']);
							  $wpdb->insert(EcalypseRental::$db['booking_prices'], $arr); */
							$total_price += round((float) $extras_prices['tax_total_rental'], 2);
						}

						if ((float) $extras_prices['tax_total_rental_2'] > 0) {
							$vat_item_2['price'] += round((float) $extras_prices['tax_total_rental_2'], 2);
							$total_price += round((float) $extras_prices['tax_total_rental_2'], 2);
						}

						if ((float) $extras_prices['tax_total_rental_3'] > 0) {
							$vat_item_3['price'] += round((float) $extras_prices['tax_total_rental_3'], 2);
							$total_price += round((float) $extras_prices['tax_total_rental_3'], 2);
						}
					}
				}
			}

			// Car delivery price
			$delivery_price = self::get_delivery_price();
			if ($enter_loc != $return_loc && (float) $delivery_price > 0) {
				$arr = array('id_booking' => $id_booking,
					'name' => __('Car delivery to different location', 'ecalypse-rental'),
					'price' => (float) $delivery_price,
					'currency' => $main_price['currency']);
				$dump['prices'][] = $arr;
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
				$total_price += (float) $delivery_price;
				$base_price += (float) $delivery_price;
			}

			// Add drivers
			if (isset($data['drv']) && !empty($data['drv'])) {
				foreach ($data['drv'] as $key => $val) {
					if (!empty($val['first_name']) && !empty($val['last_name']) && !empty($val['email']) && !empty($val['phone'])) {
						$arr = array('id_booking' => $id_booking,
							'first_name' => $val['first_name'],
							'last_name' => $val['last_name'],
							'email' => $val['email'],
							'phone' => $val['phone'],
							'street' => $val['street'],
							'city' => $val['city'],
							'zip' => $val['zip'],
							'country' => $val['country'],
							'license' => $val['license'],
							'id_card' => $val['id_card']
						);
						$wpdb->insert(EcalypseRental::$db['booking_drivers'], $arr);
					}
				}
			}

			if (!empty($vat_item) && $vat_item['price'] > 0) {
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $vat_item);
				$dump['prices'][] = $vat_item;
			}
			if (!empty($vat_item_2) && $vat_item_2['price'] > 0) {
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $vat_item_2);
				$dump['prices'][] = $vat_item_2;
			}
			if (!empty($vat_item_3) && $vat_item_3['price'] > 0) {
				$wpdb->insert(EcalypseRental::$db['booking_prices'], $vat_item_3);
				$dump['prices'][] = $vat_item_3;
			}

			$locations = self::get_locations();
			if (isset($locations[$enter_loc_id]) && isset($locations[$enter_loc_id]->branch_tax)) {
				if (!empty($locations[$enter_loc_id]->branch_tax)) {
					$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
					$lang = strtolower(end(explode('_', $lang)));

					foreach ($locations[$enter_loc_id]->branch_tax as $k => $v) {
						$tax_price = $base_price * ($v['tax'] / 100);
						/* $tax_name = $v['name'];
						  if ($lang != 'gb') {
						  if (isset($v['name_translations'][$lang]) && $v['name_translations'][$lang] != '') {
						  $tax_name = $v['name_translations'][$lang];
						  }
						  } */
						$arr = array('id_booking' => $id_booking,
							'name' => $v['tax'] . ' % branch_specific_tax_' . $k,
							'price' => (float) $tax_price,
							'currency' => $main_price['currency']);
						$dump['prices'][] = $arr;
						$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
					}
				}
			}

			$dump_data_email = get_option('ecalypse_rental_dump_data_email');
			if (trim($dump_data_email) != '') {
				$company = unserialize(get_option('ecalypse_rental_company_info'));

				$email = ((isset($company['email']) && !empty($company['email'])) ? $company['email'] : 'admin@' . $_SERVER['SERVER_NAME']);
				$name = ((isset($company['name']) && !empty($company['name'])) ? $company['name'] : __('Ecalypse Rental WP Plugin', 'ecalypse-rental'));

				add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
				add_filter('wp_mail_from', create_function('', 'return "' . $email . '"; '));
				add_filter('wp_mail_from_name', create_function('', 'return "' . $name . '"; '));

				ob_start();
				print_r($dump);
				$result = ob_get_clean();
				$res = wp_mail($dump_data_email, __('New order data dump', 'ecalypse-rental'), nl2br($result));
			}

			// is online payment?
			$onlinePayment = false;
			if (isset($_POST['payment_option'])) {
				if (!($_POST['payment_option'] == 'cash' || $_POST['payment_option'] == 'cc' || $_POST['payment_option'] == 'bank')) {
					$onlinePayment = true;
				}
			}

			if ($status == 2 && $onlinePayment) {
				// if online payment
				$available_payments = unserialize(get_option('ecalypse_rental_available_payments'));
				if (isset($available_payments['ecalypse_rental_online_payment_discount']) && $available_payments['ecalypse_rental_online_payment_discount'] > 0) {
					$discount = -1 * (float) ($total_price) * ($available_payments['ecalypse_rental_online_payment_discount'] / 100);
					$arr = array('id_booking' => $id_booking,
						'name' => __('Online payment discount', 'ecalypse-rental'),
						'price' => $discount,
						'currency' => $main_price['currency']);
					$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
					$total_price += (float) $discount;
				}
			}

			self::webhook_send($id_booking);

			$hash = self::generate_hash($id_order, $data['email']);

			if (!$onlinePayment) {
				// Send e-mail
				switch ($status) {
					case 1:
						// confirmed
						$email_type = 'ecalypse_rental_reservation_email';
						break;
					case 2:
						// pending payment
						$email_type = 'ecalypse_rental_email_status_pending';
						break;
					case 3:
						// panding other
						$email_type = 'ecalypse_rental_email_status_pending_other';
						break;
				}
				EcalypseRental::send_emails($id_booking, $email_type);
			}
			return $hash;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Send emails for customers/admins (by settings) for specific order. Function is called from all payments plugins and is used as global email function.
	 * @param int $id_booking
	 * @param str $email_type
	 * @param bool $send_all - false for send only to customer, true for send to admin and copy from settings
	 */
	public static function send_emails($id_booking, $email_type = 'ecalypse_rental_reservation_email', $send_all = true) {
		global $wpdb;

		$data = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE `id_booking` = %d', $id_booking), ARRAY_A);

		$prices = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking_prices'] . '` WHERE `id_booking` = %d', $id_booking));
		$total_amount = 0;
		$added_extras = array();
		foreach ($prices as $key => $val) {
			$total_amount += number_format($val->price, 2, '.', '');
			if ($val->extras_id > 0) {
				$added_extras[] = $val->name;
			}
		}

		$extras_text = '--';
		if (!empty($added_extras)) {
			$extras_text = implode(', ', $added_extras);
		}

		if (isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) {
			$emailBody = get_option($email_type . '_' . EcalypseRentalSession::$session['ecalypse_rental_language']);
			$emailSubject = get_option($email_type . '_subject_' . EcalypseRentalSession::$session['ecalypse_rental_language']);
			$lang = EcalypseRentalSession::$session['ecalypse_rental_language'];
			if ($emailBody == '') {
				$emailBody = get_option($email_type . '_en_GB');
				$lang = 'en_GB';
			}
			if ($emailSubject == '') {
				$emailSubject = get_option($email_type . '_subject_en_GB');
			}
		} else {
			if (isset($data['lng']) && !empty($data['lng'])) {
				$lang = $data['lng'];
				$emailBody = get_option($email_type . '_' . $lang);
				$emailSubject = get_option($email_type . '_subject_' . $lang);
			} else {
				$emailBody = get_option($email_type . '_en_GB');
				$emailSubject = get_option($email_type . '_subject_en_GB');
				$lang = 'en_GB';
			}

			if ($emailBody == '') {
				$emailBody = get_option($email_type . '_en_GB');
				$lang = 'en_GB';
			}

			if ($emailSubject == '') {
				$emailSubject = get_option($email_type . '_subject_en_GB');
			}
		}

		if (!empty($emailBody) && $data) {

			$order_id = md5($data['id_order'] . EcalypseRental::$hash_salt . $data['email']);
			$date_diff = abs(strtotime($data['return_date']) - strtotime($data['enter_date']));
			$diff_days = intval($date_diff / 86400);
			$diff_hours = intval(($date_diff % 86400) / 3600);
			$diff_minutes = intval(($date_diff % 86400) / 60);

			if ($diff_days >= 1 && ($diff_hours > 0 || $diff_minutes > 0)) {
				++$diff_days; // If you pass by 30 minutes and more, it 1 day more
			}

			if ($diff_days == 0) {
				$diff_days = 1;
			}

			$theme_options = unserialize(get_option('ecalypse_rental_theme_options'));
			if (isset($theme_options['date_format'])) {
				// reformat dates
				$date_from = date(EcalypseRental::date_format_php($theme_options['date_format'], 'auto'), strtotime($data['enter_date']));
				$date_to = date(EcalypseRental::date_format_php($theme_options['date_format'], 'auto'), strtotime($data['return_date']));
			} else {
				$date_from = date(EcalypseRental::date_format_php('', true), strtotime($data['enter_date']));
				$date_to = date(EcalypseRental::date_format_php('', true), strtotime($data['return_date']));
			}

			$car_name = $data['vehicle'];
			if ($data['total_items'] > 1) {
				$car_name = '';
				$items = $wpdb->get_results($wpdb->prepare('SELECT f.name FROM `' . $wpdb->prefix . 'ecalypse_rental_booking_items` bi INNER JOIN ' . EcalypseRental::$db['fleet'] . ' f ON f.id_fleet = bi.vehicle_id WHERE bi.`id_booking` = %d', $id_booking));
				foreach ($items as $item) {
					$car_name .= $item->name . ', ';
				}
				$car_name = trim($car_name, ', ');
			}

			$emailBody = str_ireplace('[CustomerName]', $data['first_name'] . " " . $data['last_name'], $emailBody);
			$emailBody = str_ireplace('[CustomerEmail]', $data['email'], $emailBody);
			$emailBody = str_ireplace('[ReservationDetails]', $car_name . ', ' . $date_from . ' (' . $data['enter_loc'] . ') - ' . $date_to . ' (' . $data['return_loc'] . ')', $emailBody);
			$emailBody = str_ireplace('[Car]', $car_name, $emailBody);
			$emailBody = str_ireplace('[pickupdate]', $date_from, $emailBody);
			$emailBody = str_ireplace('[dropoffdate]', $date_to, $emailBody);
			$emailBody = str_ireplace('[pickup_location]', $data['enter_loc'], $emailBody);
			$emailBody = str_ireplace('[dropoff_location]', $data['return_loc'], $emailBody);
			$emailBody = str_ireplace('[total_payment]', round($total_amount, 2), $emailBody);
			$emailBody = str_ireplace('[deposit_paid]', round($data['paid_online'], 2), $emailBody);
			$emailBody = str_ireplace('[remaining_amount]', round($total_amount - $data['paid_online'], 2), $emailBody);
			$emailBody = str_ireplace('[rate]', round($total_amount / $diff_days, 2), $emailBody);
			$emailBody = str_ireplace('[rental_days]', $diff_days, $emailBody);
			$emailBody = str_ireplace('[ReservationNumber]', $data['id_order'], $emailBody);
			$emailBody = str_ireplace('[customer_comment]', $data['comment'], $emailBody);
			$emailBody = str_ireplace('[ReservationLink]', home_url() . '?page=ecalypse-rental&summary=' . $order_id, $emailBody);
			$emailBody = str_ireplace('[ReservationLinkStart]', '<a href="' . home_url() . '?page=ecalypse-rental&summary=' . $order_id . '">', $emailBody);
			$emailBody = str_ireplace('[ReservationLinkEnd]', '</a>', $emailBody);
			$emailBody = str_ireplace('[extras]', $extras_text, $emailBody);

			$emailBody = apply_filters('ecalypse_rental_email_replacement', $emailBody, $data['id_booking'], $lang);

			$emailBody = '<html><body>' . $emailBody . '</body></html>';
			$emailBody = self::removeslashes(nl2br($emailBody));

			$recipient = $data['email'];
			if ($emailSubject == '') {
				$subject = __('Reservation confirmation #', 'ecalypse-rental') . $data['id_order'];
			} else {
				$subject = $emailSubject;
				$subject = str_ireplace('[CustomerName]', $data['first_name'] . " " . $data['last_name'], $subject);
				$subject = str_ireplace('[CustomerEmail]', $data['email'], $subject);
				$subject = str_ireplace('[ReservationDetails]', $car_name . ', ' . $date_from . ' (' . $data['enter_loc'] . ') - ' . $date_to . ' (' . $data['return_loc'] . ')', $subject);
				$subject = str_ireplace('[Car]', $car_name, $subject);
				$subject = str_ireplace('[pickupdate]', $date_from, $subject);
				$subject = str_ireplace('[dropoffdate]', $date_to, $subject);
				$subject = str_ireplace('[pickup_location]', $data['enter_loc'], $subject);
				$subject = str_ireplace('[dropoff_location]', $data['return_loc'], $subject);
				$subject = str_ireplace('[total_payment]', round($total_amount, 2), $subject);
				$subject = str_ireplace('[deposit_paid]', round($data['paid_online'], 2), $subject);
				$subject = str_ireplace('[remaining_amount]', round($total_amount - $data['paid_online'], 2), $subject);
				$subject = str_ireplace('[rate]', round($total_amount / $diff_days, 2), $subject);
				$subject = str_ireplace('[rental_days]', $diff_days, $subject);
				$subject = str_ireplace('[ReservationNumber]', $data['id_order'], $subject);
				$subject = str_ireplace('[ReservationLink]', home_url() . '?page=ecalypse-rental&summary=' . $order_id, $subject);
				$subject = str_ireplace('[ReservationLinkStart]', '<a href="' . home_url() . '?page=ecalypse-rental&summary=' . $order_id . '">', $subject);
				$subject = str_ireplace('[ReservationLinkEnd]', '</a>', $subject);
				$subject = str_ireplace('[extras]', $extras_text, $subject);
				$subject = apply_filters('ecalypse_rental_email_replacement', $subject, $data['id_booking'], $lang);
			}

			$company = unserialize(get_option('ecalypse_rental_company_info'));

			$email = ((isset($company['email']) && !empty($company['email'])) ? $company['email'] : 'admin@' . $_SERVER['SERVER_NAME']);
			$name = ((isset($company['name']) && !empty($company['name'])) ? $company['name'] : 'Ecalypse Rental WP Plugin');

			add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
			add_filter('wp_mail_from', create_function('', 'return "' . $email . '"; '));
			add_filter('wp_mail_from_name', create_function('', 'return "' . $name . '"; '));

			if ($send_all) {
				$book_send_email = get_option('ecalypse_rental_book_send_email');
				if (empty($book_send_email)) {
					$book_send_email = array('client' => 1, 'admin' => 1, 'other' => 1);
				} else {
					$book_send_email = unserialize($book_send_email);
					if (!is_array($book_send_email)) {
						$book_send_email = array();
					}
					if (!isset($book_send_email['client'])) {
						$book_send_email['client'] = 1;
					}
					if (!isset($book_send_email['admin'])) {
						$book_send_email['admin'] = 1;
					}

					if (!isset($book_send_email['other'])) {
						$book_send_email['other'] = 0;
					}
				}
			} else {
				$book_send_email = array('client' => 1, 'admin' => 0, 'other' => 0);
			}

			$attachments = array();
			$attachments = apply_filters('ecalypse_rental_email_attachments', $attachments, $data['id_order']);

			if ($book_send_email['client'] == 1) {
				$res = wp_mail($recipient, $subject, $emailBody, '', $attachments);
			}

			if ($book_send_email['other'] == 1 && isset($book_send_email['other_email']) && $book_send_email['other_email'] != '') {
				@wp_mail($book_send_email['other_email'], $subject, $emailBody, '', $attachments);
			}

			// Copy to admin
			if (isset($company['email']) && !empty($company['email']) && $book_send_email['admin'] == 1) {
				@wp_mail($company['email'], $subject, $emailBody, array("Reply-To: " . $data['email']), $attachments);
			}
		}
		return true;
	}

	/**
	 * Send booking data via webhook
	 * @param int $id_booking	 
	 */
	public static function webhook_send($id_booking, $add_to_queue = true) {
		global $wpdb;

		$webhook_url = get_option('ecalypse_rental_webhook_url');
		if (trim($webhook_url) == '') {
			return false;
		}
		$data = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE `id_booking` = %d', $id_booking), ARRAY_A);

		if (!$data) {
			return false;
		}

		$post_data = $data;
		//unset($post_data['']);
		$prices = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking_prices'] . '` WHERE `id_booking` = %d', $id_booking));
		$total_amount = 0;
		$post_data['items'] = array();
		foreach ($prices as $key => $val) {
			$total_amount += number_format($val->price, 2, '.', '');
			$post_data['items'][] = array('name' => $val->name, 'price' => $val->price);
		}
		$data['total_amount'] = $total_amount;

		$additional_drivers = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking_drivers'] . '` WHERE `id_booking` = %d', $id_booking));
		$post_data['additional_drivers'] = array();
		foreach ($additional_drivers as $key => $val) {
			$d = $val;
			unset($d['id_driver']);
			unset($d['id_booking']);
			$post_data['additional_drivers'][] = $d;
		}
		
		$response = wp_remote_post( $webhook_url, array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => $post_data,
			'cookies' => array()
			)
		);

		if ( is_wp_error( $response ) ) {
		   if ($add_to_queue) {
				$arr = array('id_booking' => $id_booking,
					'date' => date('Y-m-d H:i:s')
				);
				$wpdb->insert($wpdb->prefix . "ecalypse_rental_webhook_queue", $arr);
			}
		} else {
			if (!empty($response['response']) && !empty($response['response']['code']) && $response['response']['code'] == 200) {
				return true;
			} else {
				if ($add_to_queue) {
					$arr = array('id_booking' => $id_booking,
						'date' => date('Y-m-d H:i:s')
					);
					$wpdb->insert($wpdb->prefix . "ecalypse_rental_webhook_queue", $arr);
				}
			}
		}

		/*
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $webhook_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		$response = curl_exec($ch);
		curl_close($ch);
		*/
		
		return false;
	}

	public static function date_format_js($input_format) {
		switch ($input_format) {
			case 'dd.mm.yyyy':
				return 'dd.mm.yy';
				break;
			case 'dd-mm-yyyy':
				return 'dd-mm-yy';
				break;
			case 'mm/dd/yyyy':
				return 'mm/dd/yy';
				break;
			case 'dd-M-yyyy':
				return 'dd-M-yy';
				break;
			case 'M-dd-yyyy':
				return 'M-dd-yy';
				break;
			default:
				return 'yy-mm-dd';
				break;
		}
	}

	public static function date_format_php($input_format, $with_time = true) {
		if ($with_time == 'auto') {
			$disable_time = get_option('ecalypse_rental_disable_time');
			if ($disable_time == 'yes') {
				$disable_time = true;
			} else {
				$disable_time = false;
			}
			$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
			if ($time_pricing_type == 'half_day') {
				$disable_time = true;
			}
			$with_time = !$disable_time;
		}
		$time_format = 'H:i';
		if ($with_time === true) {
			$theme_options = unserialize(get_option('ecalypse_rental_theme_options'));
			if (isset($theme_options['time_format']) && $theme_options['time_format'] == '12') {
				$time_format = 'h:ia';
			}
		}

		$return = '';
		switch ($input_format) {
			case 'dd.mm.yyyy':
				$return = 'd.m.Y';
				break;
			case 'dd-mm-yyyy':
				$return = 'd-m-Y';
				break;
			case 'mm/dd/yyyy':
				$return = 'm/d/Y';
				break;
			case 'dd-M-yyyy':
				$return = 'd-M-Y';
				break;
			case 'M-dd-yyyy':
				$return = 'M-d-Y';
				break;
			default:
				$return = 'Y-m-d';
				break;
		}
		return $return . ($with_time ? ' ' . $time_format : '');
	}

	public static function ecalypse_rental_time_format($time, $format = 24) {
		$format = (int) $format;
		if ($format != 24 && $format != 12) {
			$format = 24;
		}

		if (strpos($time, ':') !== false) {
			$hours = (int) substr($time, 0, strpos($time, ':'));
			$minutes = (int) substr($time, strpos($time, ':') + 1, 2);
		} else {
			$hours = (int) $time;
			$minutes = 0;
		}

		if (strpos($time, 'pm') !== false) {
			if ($hours != 12) {
				$hours += 12;
			}
		}

		if (strpos($time, 'am') !== false) {
			if ($hours == 12) {
				$hours = 0;
			}
		}

		if ($format == 24) {
			return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
		} else {
			$timeType = 'am';
			if ($hours == 0) {
				$hours = 12;
			} else {
				if ($hours > 11) {
					$timeType = 'pm';
				}

				if ($hours > 12) {
					$hours -= 12;
				}
			}
			return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . ' ' . $timeType;
		}
	}

	/**
	 * Find all yyyy-mm-yy date in input string and replace it with new date format given by format parameter
	 * @param type $string
	 * @param type $format
	 */
	public static function reformat_date_string($string, $format = '', $timeFormat = 24) {
		if ($format == '' && $timeFormat == 24) {
			return $string;
		}
		$dateArray = preg_match_all("/(\d{4}-\d{2}-\d{2})/", $string, $match);
		if (is_array($match)) {
			$match = $match[0];
		}
		foreach ($match as $d) {
			$string = str_replace($d, date(date_format_php($format), strtotime($d)), $string);
		}

		if ($timeFormat != 24) {
			$timeArray = preg_match_all("/(\d{2}:\d{2}:\d{2})/", $string, $match);
			if (is_array($match)) {
				$match = $match[0];
			}
			foreach ($match as $d) {
				$string = str_replace($d, ecalypse_rental_time_format(substr($d, 0, 5), $timeFormat), $string);
			}
		}

		$disable_time = get_option('ecalypse_rental_disable_time');
		if ($disable_time == 'yes') {
			$timeArray = preg_match_all("/(\d{2}:\d{2}:\d{2})/", $string, $match);
			if (is_array($match)) {
				$match = $match[0];
			}
			foreach ($match as $d) {
				$string = str_replace($d, '', $string);
			}
		}

		return $string;
	}

	/**
	 * Translate extras to current lang
	 * @param type $string
	 * @return type
	 */
	public static function translate_extras($string, $enter_loc_id = false) {
		global $extrasTranslations, $wpdb;
		$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
		$lang = strtolower(end(explode('_', $lang)));

		if ($enter_loc_id && strpos($string, 'branch_specific_tax') !== false) {
			$locations = EcalypseRental::get_locations();
			if (isset($locations[$enter_loc_id]) && isset($locations[$enter_loc_id]->branch_tax)) {
				if (!empty($locations[$enter_loc_id]->branch_tax)) {
					foreach ($locations[$enter_loc_id]->branch_tax as $k => $v) {
						$tax_name = $v['name'];
						if ($lang != 'gb') {
							if (isset($v['name_translations'][$lang]) && $v['name_translations'][$lang] != '') {
								$tax_name = $v['name_translations'][$lang];
							}
						}
						$string = str_replace('branch_specific_tax_' . $k, $tax_name, $string);
					}
				}
			}
		}

		if ($lang == 'gb') {
			return $string;
		}

		if (!$extrasTranslations) {
			$extras = $wpdb->get_results('SELECT * FROM `' . EcalypseRental::$db['extras'] . '`');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if ($extras) {
				foreach ($extras as $e) {
					$name_translations = unserialize($e->name_translations);
					if (empty($name_translations)) {
						$name_translations = array();
					}
					if ($available_languages && !empty($available_languages)) {
						foreach ($available_languages as $key => $val) {
							if ($val['country-www'] == 'gb') {
								continue;
							}
							if (!isset($extrasTranslations[$val['country-www']])) {
								$extrasTranslations[$val['country-www']] = array();
							}
							if (trim($name_translations[$val['country-www']]) != '') {
								$extrasTranslations[$val['country-www']][$e->name] = $name_translations[$val['country-www']];
							}
						}
					}
				}
			}
		}

		if (isset($extrasTranslations[$lang])) {
			$replaceFrom = array();
			$replaceTo = array();
			foreach ($extrasTranslations[$lang] as $from => $to) {
				$replaceFrom[] = $from;
				$replaceTo[] = $to;
			}
			$replaceFrom[] = __('Value Added Tax', 'ecalypse-rental');
			$replaceTo[] = EcalypseRental::t('Value Added Tax');
			$replaceFrom[] = 'PM';
			$replaceTo[] = EcalypseRental::t('PM');
			$replaceFrom[] = 'AM';
			$replaceTo[] = EcalypseRental::t('AM');
			$replaceFrom[] = __('sales tax', 'ecalypse-rental');
			$replaceTo[] = EcalypseRental::t('sales tax');
			return str_replace($replaceFrom, $replaceTo, $string);
		}
		return $string;
	}

	/**
	 * 	Get order summary
	 */
	public function get_order_summary($hash) {
		global $wpdb;

		try {


			$data['info'] = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '`
																										 WHERE MD5(CONCAT(`id_order`, %s, `email`)) = %s', self::$hash_salt, $hash));

			if ($data['info'] && !empty($data['info'])) {

				$data['prices'] = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking_prices'] . '`
																										 		 		 WHERE `id_booking` = %d', $data['info']->id_booking));

				$data['drivers'] = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking_drivers'] . '`
																										 		 			WHERE `id_booking` = %d', $data['info']->id_booking));
			}

			return $data;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Generate unique random order ID string (8 characters, max. 10 loops)
	 */
	function generate_unique_order_id() {
		global $wpdb;

		try {

			for ($x = 0; $x <= 10; $x++) {
				$id_order = strtoupper(substr(sha1(uniqid()), rand(0, 34), 8));
				$exists = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `' . EcalypseRental::$db['booking'] . '` WHERE `id_order` = %s LIMIT 1', $id_order));
				if ((int) $exists == 0) {
					return $id_order;
				}
			}

			return false;
		} catch (Exception $e) {
			return false;
			//return $e->getMessage();
		}
	}

	function generate_hash($id_order, $email) {
		return md5($id_order . self::$hash_salt . $email);
	}

	function is_maxprice_reached($val) {
		$total_price = $val->price * $val->pr_value;
		return ((isset($val->maxprice) && (float) $val->maxprice > 0 && $total_price > (float) $val->maxprice) ? true : false);
	}

	function get_currency_symbol($place, $currency) {
		$currencies = array('USD' => array('before' => '$', 'after' => ''),
			'EUR' => array('before' => '€ ', 'after' => ''),
			'CZK' => array('before' => '', 'after' => ' Kč'),
			'GBP' => array('before' => '£', 'after' => ''));

		if (isset($currencies[$currency])) {
			return $currencies[$currency][$place];
		} else {
			return (($place == 'after') ? $currency : '');
		}
	}

	public static function view($name, array $args = array()) {
		$name = trim(sanitize_text_field($name), '.');
		foreach ($args AS $key => $val) {
			$$key = $val;
		}

		load_plugin_textdomain('ecalypse-rental');

		$cr_title = ucfirst(end(explode('-', $name)));

		$view = array('name' => $name, 'dir' => ECALYPSERENTALSTARTER__PLUGIN_DIR . 'views/');
		$view = apply_filters('ecalypse_rental_view', $view);
		$file = $view['dir'] . $view['name'] . '.php';
		include($file);
	}

	public static function template($name, array $args = array()) {
		//$args = apply_filters( 'akismet_view_arguments', $args, $name );

		foreach ($args AS $key => $val) {
			$$key = $val;
		}

		load_plugin_textdomain('ecalypse-rental');

		$cr_title = ucfirst(end(explode('-', $name)));
		$path = TEMPLATEPATH;
		if (STYLESHEETPATH != TEMPLATEPATH) {
			if (is_file(STYLESHEETPATH . '/' . $name . '.php')) {
				$path = STYLESHEETPATH;
			}
		}

		if (!self::$compatible_theme) {
			$path = ECALYPSERENTALSTARTER__PLUGIN_DIR . 'templates';
		}

		$file = $path . '/' . $name . '.php';

		include($file);
	}

	public static function get_country_list() {
		$arr = array('AD' => __('Andorra', 'ecalypse-rental'), 'AE' => __('United Arab Emirates', 'ecalypse-rental'), 'AF' => __('Afghanistan', 'ecalypse-rental'), 'AG' => __('Antigua and Barbuda', 'ecalypse-rental'), 'AI' => __('Anguilla', 'ecalypse-rental'), 'AL' => __('Albania', 'ecalypse-rental'), 'AM' => __('Armenia', 'ecalypse-rental'), 'AN' => __('Netherlands Antilles', 'ecalypse-rental'), 'AO' => __('Angola', 'ecalypse-rental'), 'AQ' => __('Antarctica', 'ecalypse-rental'), 'AR' => __('Argentina', 'ecalypse-rental'), 'AS' => __('American Samoa', 'ecalypse-rental'), 'AT' => __('Austria', 'ecalypse-rental'), 'AU' => __('Australia', 'ecalypse-rental'), 'AW' => __('Aruba', 'ecalypse-rental'), 'AX' => __('Åland Islands', 'ecalypse-rental'), 'AZ' => __('Azerbaijan', 'ecalypse-rental'), 'BA' => __('Bosnia and Herzegovina', 'ecalypse-rental'), 'BB' => __('Barbados', 'ecalypse-rental'), 'BD' => __('Bangladesh', 'ecalypse-rental'), 'BE' => __('Belgium', 'ecalypse-rental'), 'BF' => __('Burkina Faso', 'ecalypse-rental'), 'BG' => __('Bulgaria', 'ecalypse-rental'), 'BH' => __('Bahrain', 'ecalypse-rental'), 'BI' => __('Burundi', 'ecalypse-rental'), 'BJ' => __('Benin', 'ecalypse-rental'), 'BL' => __('Saint Barthélemy', 'ecalypse-rental'), 'BM' => __('Bermuda', 'ecalypse-rental'), 'BN' => __('Brunei', 'ecalypse-rental'), 'BO' => __('Bolivia', 'ecalypse-rental'), 'BQ' => __('British Antarctic Territory', 'ecalypse-rental'), 'BR' => __('Brazil', 'ecalypse-rental'), 'BS' => __('Bahamas', 'ecalypse-rental'), 'BT' => __('Bhutan', 'ecalypse-rental'), 'BV' => __('Bouvet Island', 'ecalypse-rental'), 'BW' => __('Botswana', 'ecalypse-rental'), 'BY' => __('Belarus', 'ecalypse-rental'), 'BZ' => __('Belize', 'ecalypse-rental'), 'CA' => __('Canada', 'ecalypse-rental'), 'KD' => __('Caribbean Netherlands (Bonaire, Sint Eustatius, Saba)', 'ecalypse-rental'), 'CC' => __('Cocos [Keeling] Islands', 'ecalypse-rental'), 'CD' => __('Congo - Kinshasa', 'ecalypse-rental'), 'CF' => __('Central African Republic', 'ecalypse-rental'), 'CG' => __('Congo - Brazzaville', 'ecalypse-rental'), 'CH' => __('Switzerland', 'ecalypse-rental'), 'CI' => __('Côte d’Ivoire', 'ecalypse-rental'), 'CK' => __('Cook Islands', 'ecalypse-rental'), 'CL' => __('Chile', 'ecalypse-rental'), 'CM' => __('Cameroon', 'ecalypse-rental'), 'CN' => __('China', 'ecalypse-rental'), 'CO' => __('Colombia', 'ecalypse-rental'), 'CR' => __('Costa Rica', 'ecalypse-rental'), 'CS' => __('Serbia and Montenegro', 'ecalypse-rental'), 'CT' => __('Canton and Enderbury Islands', 'ecalypse-rental'), 'CU' => __('Cuba', 'ecalypse-rental'), 'CV' => __('Cape Verde', 'ecalypse-rental'), 'CX' => __('Christmas Island', 'ecalypse-rental'), 'CW' => __('Curaçao', 'ecalypse-rental'), 'CY' => __('Cyprus', 'ecalypse-rental'), 'CZ' => __('Czech Republic', 'ecalypse-rental'), 'DD' => __('East Germany', 'ecalypse-rental'), 'DE' => __('Germany', 'ecalypse-rental'), 'DJ' => __('Djibouti', 'ecalypse-rental'), 'DK' => __('Denmark', 'ecalypse-rental'), 'DM' => __('Dominica', 'ecalypse-rental'), 'DO' => __('Dominican Republic', 'ecalypse-rental'), 'DZ' => __('Algeria', 'ecalypse-rental'), 'EC' => __('Ecuador', 'ecalypse-rental'), 'EE' => __('Estonia', 'ecalypse-rental'), 'EG' => __('Egypt', 'ecalypse-rental'), 'EH' => __('Western Sahara', 'ecalypse-rental'), 'ER' => __('Eritrea', 'ecalypse-rental'), 'ES' => __('Spain', 'ecalypse-rental'), 'ET' => __('Ethiopia', 'ecalypse-rental'), 'FI' => __('Finland', 'ecalypse-rental'), 'FJ' => __('Fiji', 'ecalypse-rental'), 'FK' => __('Falkland Islands', 'ecalypse-rental'), 'FM' => __('Micronesia', 'ecalypse-rental'), 'FO' => __('Faroe Islands', 'ecalypse-rental'), 'FQ' => __('French Southern and Antarctic Territories', 'ecalypse-rental'), 'FR' => __('France', 'ecalypse-rental'), 'FX' => __('Metropolitan France', 'ecalypse-rental'), 'GA' => __('Gabon', 'ecalypse-rental'), 'GB' => __('United Kingdom', 'ecalypse-rental'), 'GD' => __('Grenada', 'ecalypse-rental'), 'GE' => __('Georgia', 'ecalypse-rental'), 'GF' => __('French Guiana', 'ecalypse-rental'), 'GG' => __('Guernsey', 'ecalypse-rental'), 'GH' => __('Ghana', 'ecalypse-rental'), 'GI' => __('Gibraltar', 'ecalypse-rental'), 'GL' => __('Greenland', 'ecalypse-rental'), 'GM' => __('Gambia', 'ecalypse-rental'), 'GN' => __('Guinea', 'ecalypse-rental'), 'GP' => __('Guadeloupe', 'ecalypse-rental'), 'GQ' => __('Equatorial Guinea', 'ecalypse-rental'), 'GR' => __('Greece', 'ecalypse-rental'), 'GS' => __('South Georgia and the South Sandwich Islands', 'ecalypse-rental'), 'GT' => __('Guatemala', 'ecalypse-rental'), 'GU' => __('Guam', 'ecalypse-rental'), 'GW' => __('Guinea-Bissau', 'ecalypse-rental'), 'GY' => __('Guyana', 'ecalypse-rental'), 'HK' => __('Hong Kong SAR China', 'ecalypse-rental'), 'HM' => __('Heard Island and McDonald Islands', 'ecalypse-rental'), 'HN' => __('Honduras', 'ecalypse-rental'), 'HR' => __('Croatia', 'ecalypse-rental'), 'HT' => __('Haiti', 'ecalypse-rental'), 'HU' => __('Hungary', 'ecalypse-rental'), 'ID' => __('Indonesia', 'ecalypse-rental'), 'IE' => __('Ireland', 'ecalypse-rental'), 'IL' => __('Israel', 'ecalypse-rental'), 'IM' => __('Isle of Man', 'ecalypse-rental'), 'IN' => __('India', 'ecalypse-rental'), 'IO' => __('British Indian Ocean Territory', 'ecalypse-rental'), 'IQ' => __('Iraq', 'ecalypse-rental'), 'IR' => __('Iran', 'ecalypse-rental'), 'IS' => __('Iceland', 'ecalypse-rental'), 'IT' => __('Italy', 'ecalypse-rental'), 'JE' => __('Jersey', 'ecalypse-rental'), 'JM' => __('Jamaica', 'ecalypse-rental'), 'JO' => __('Jordan', 'ecalypse-rental'), 'JP' => __('Japan', 'ecalypse-rental'), 'JT' => __('Johnston Island', 'ecalypse-rental'), 'KE' => __('Kenya', 'ecalypse-rental'), 'KG' => __('Kyrgyzstan', 'ecalypse-rental'), 'KH' => __('Cambodia', 'ecalypse-rental'), 'KI' => __('Kiribati', 'ecalypse-rental'), 'KM' => __('Comoros', 'ecalypse-rental'), 'KN' => __('Saint Kitts and Nevis', 'ecalypse-rental'), 'KP' => __('North Korea', 'ecalypse-rental'), 'KR' => __('South Korea', 'ecalypse-rental'), 'KW' => __('Kuwait', 'ecalypse-rental'), 'KY' => __('Cayman Islands', 'ecalypse-rental'), 'KZ' => __('Kazakhstan', 'ecalypse-rental'), 'LA' => __('Laos', 'ecalypse-rental'), 'LB' => __('Lebanon', 'ecalypse-rental'), 'LC' => __('Saint Lucia', 'ecalypse-rental'), 'LI' => __('Liechtenstein', 'ecalypse-rental'), 'LK' => __('Sri Lanka', 'ecalypse-rental'), 'LR' => __('Liberia', 'ecalypse-rental'), 'LS' => __('Lesotho', 'ecalypse-rental'), 'LT' => __('Lithuania', 'ecalypse-rental'), 'LU' => __('Luxembourg', 'ecalypse-rental'), 'LV' => __('Latvia', 'ecalypse-rental'), 'LY' => __('Libya', 'ecalypse-rental'), 'MA' => __('Morocco', 'ecalypse-rental'), 'MC' => __('Monaco', 'ecalypse-rental'), 'MD' => __('Moldova', 'ecalypse-rental'), 'ME' => __('Montenegro', 'ecalypse-rental'), 'MF' => __('Saint Martin', 'ecalypse-rental'), 'MG' => __('Madagascar', 'ecalypse-rental'), 'MH' => __('Marshall Islands', 'ecalypse-rental'), 'MI' => __('Midway Islands', 'ecalypse-rental'), 'MK' => __('Macedonia', 'ecalypse-rental'), 'ML' => __('Mali', 'ecalypse-rental'), 'MM' => __('Myanmar [Burma]', 'ecalypse-rental'), 'MN' => __('Mongolia', 'ecalypse-rental'), 'MO' => __('Macau SAR China', 'ecalypse-rental'), 'MP' => __('Northern Mariana Islands', 'ecalypse-rental'), 'MQ' => __('Martinique', 'ecalypse-rental'), 'MR' => __('Mauritania', 'ecalypse-rental'), 'MS' => __('Montserrat', 'ecalypse-rental'), 'MT' => __('Malta', 'ecalypse-rental'), 'MU' => __('Mauritius', 'ecalypse-rental'), 'MV' => __('Maldives', 'ecalypse-rental'), 'MW' => __('Malawi', 'ecalypse-rental'), 'MX' => __('Mexico', 'ecalypse-rental'), 'MY' => __('Malaysia', 'ecalypse-rental'), 'MZ' => __('Mozambique', 'ecalypse-rental'), 'NA' => __('Namibia', 'ecalypse-rental'), 'NC' => __('New Caledonia', 'ecalypse-rental'), 'NE' => __('Niger', 'ecalypse-rental'), 'NF' => __('Norfolk Island', 'ecalypse-rental'), 'NG' => __('Nigeria', 'ecalypse-rental'), 'NI' => __('Nicaragua', 'ecalypse-rental'), 'NL' => __('Netherlands', 'ecalypse-rental'), 'NO' => __('Norway', 'ecalypse-rental'), 'NP' => __('Nepal', 'ecalypse-rental'), 'NQ' => __('Dronning Maud Land', 'ecalypse-rental'), 'NR' => __('Nauru', 'ecalypse-rental'), 'NT' => __('Neutral Zone', 'ecalypse-rental'), 'NU' => __('Niue', 'ecalypse-rental'), 'NZ' => __('New Zealand', 'ecalypse-rental'), 'OM' => __('Oman', 'ecalypse-rental'), 'PA' => __('Panama', 'ecalypse-rental'), 'PC' => __('Pacific Islands Trust Territory', 'ecalypse-rental'), 'PE' => __('Peru', 'ecalypse-rental'), 'PF' => __('French Polynesia', 'ecalypse-rental'), 'PG' => __('Papua New Guinea', 'ecalypse-rental'), 'PH' => __('Philippines', 'ecalypse-rental'), 'PK' => __('Pakistan', 'ecalypse-rental'), 'PL' => __('Poland', 'ecalypse-rental'), 'PM' => __('Saint Pierre and Miquelon', 'ecalypse-rental'), 'PN' => __('Pitcairn Islands', 'ecalypse-rental'), 'PR' => __('Puerto Rico', 'ecalypse-rental'), 'PS' => __('Palestinian Territories', 'ecalypse-rental'), 'PT' => __('Portugal', 'ecalypse-rental'), 'PU' => __('U.S. Miscellaneous Pacific Islands', 'ecalypse-rental'), 'PW' => __('Palau', 'ecalypse-rental'), 'PY' => __('Paraguay', 'ecalypse-rental'), 'PZ' => __('Panama Canal Zone', 'ecalypse-rental'), 'QA' => __('Qatar', 'ecalypse-rental'), 'RE' => __('Réunion', 'ecalypse-rental'), 'RO' => __('Romania', 'ecalypse-rental'), 'RS' => __('Serbia', 'ecalypse-rental'), 'RU' => __('Russia', 'ecalypse-rental'), 'RW' => __('Rwanda', 'ecalypse-rental'), 'SA' => __('Saudi Arabia', 'ecalypse-rental'), 'SB' => __('Solomon Islands', 'ecalypse-rental'), 'SC' => __('Seychelles', 'ecalypse-rental'), 'SD' => __('Sudan', 'ecalypse-rental'), 'SE' => __('Sweden', 'ecalypse-rental'), 'SG' => __('Singapore', 'ecalypse-rental'), 'SH' => __('Saint Helena', 'ecalypse-rental'), 'SX' => __('St. Martin', 'ecalypse-rental'), 'SI' => __('Slovenia', 'ecalypse-rental'), 'SJ' => __('Svalbard and Jan Mayen', 'ecalypse-rental'), 'SK' => __('Slovakia', 'ecalypse-rental'), 'SL' => __('Sierra Leone', 'ecalypse-rental'), 'SM' => __('San Marino', 'ecalypse-rental'), 'SN' => __('Senegal', 'ecalypse-rental'), 'SO' => __('Somalia', 'ecalypse-rental'), 'SR' => __('Suriname', 'ecalypse-rental'), 'ST' => __('São Tomé and Príncipe', 'ecalypse-rental'), 'SV' => __('El Salvador', 'ecalypse-rental'), 'SY' => __('Syria', 'ecalypse-rental'), 'SZ' => __('Swaziland', 'ecalypse-rental'), 'TC' => __('Turks and Caicos Islands', 'ecalypse-rental'), 'TD' => __('Chad', 'ecalypse-rental'), 'TF' => __('French Southern Territories', 'ecalypse-rental'), 'TG' => __('Togo', 'ecalypse-rental'), 'TH' => __('Thailand', 'ecalypse-rental'), 'TJ' => __('Tajikistan', 'ecalypse-rental'), 'TK' => __('Tokelau', 'ecalypse-rental'), 'TL' => __('Timor-Leste', 'ecalypse-rental'), 'TM' => __('Turkmenistan', 'ecalypse-rental'), 'TN' => __('Tunisia', 'ecalypse-rental'), 'TO' => __('Tonga', 'ecalypse-rental'), 'TR' => __('Turkey', 'ecalypse-rental'), 'TT' => __('Trinidad and Tobago', 'ecalypse-rental'), 'TV' => __('Tuvalu', 'ecalypse-rental'), 'TW' => __('Taiwan', 'ecalypse-rental'), 'TZ' => __('Tanzania', 'ecalypse-rental'), 'UA' => __('Ukraine', 'ecalypse-rental'), 'UG' => __('Uganda', 'ecalypse-rental'), 'UM' => __('U.S. Minor Outlying Islands', 'ecalypse-rental'), 'US' => __('United States', 'ecalypse-rental'), 'UY' => __('Uruguay', 'ecalypse-rental'), 'UZ' => __('Uzbekistan', 'ecalypse-rental'), 'VA' => __('Vatican City', 'ecalypse-rental'), 'VC' => __('Saint Vincent and the Grenadines', 'ecalypse-rental'), 'VD' => __('North Vietnam', 'ecalypse-rental'), 'VE' => __('Venezuela', 'ecalypse-rental'), 'VG' => __('British Virgin Islands', 'ecalypse-rental'), 'VI' => __('U.S. Virgin Islands', 'ecalypse-rental'), 'VN' => __('Vietnam', 'ecalypse-rental'), 'VU' => __('Vanuatu', 'ecalypse-rental'), 'WF' => __('Wallis and Futuna', 'ecalypse-rental'), 'WK' => __('Wake Island', 'ecalypse-rental'), 'WS' => __('Samoa', 'ecalypse-rental'), 'YD' => "People's Democratic Republic of Yemen", 'YE' => __('Yemen', 'ecalypse-rental'), 'YT' => __('Mayotte', 'ecalypse-rental'), 'ZA' => __('South Africa', 'ecalypse-rental'), 'ZM' => __('Zambia', 'ecalypse-rental'), 'ZW' => __('Zimbabwe', 'ecalypse-rental'), 'ZZ' => __('Unknown or Invalid Region', 'ecalypse-rental'));
		asort($arr);
		reset($arr);
		return $arr;
	}

	public static function get_day_name($day, array $day_names = array()) {
		if (empty($day_names)) {
			$day_names = array(1 => __('Monday', 'ecalypse-rental'),
				2 => __('Tuesday', 'ecalypse-rental'),
				3 => __('Wednesday', 'ecalypse-rental'),
				4 => __('Thursday', 'ecalypse-rental'),
				5 => __('Friday', 'ecalypse-rental'),
				6 => __('Saturday', 'ecalypse-rental'),
				7 => __('Sunday', 'ecalypse-rental'));
		} else {
			$days = array(1 => $day_names[0],
				2 => $day_names[1],
				3 => $day_names[2],
				4 => $day_names[3],
				5 => $day_names[4],
				6 => $day_names[5],
				7 => $day_names[6]);
		}

		return $days[$day];
	}

	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		global $wpdb, $ecalypse_rental_db, $wp_roles;;

		try {

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$charset_collate = '';

			if (!empty($wpdb->charset)) {
				$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
			}

			if (!empty($wpdb->collate)) {
				$charset_collate .= " COLLATE {$wpdb->collate}";
			}

			// Branches
			$sql = "CREATE TABLE `" . $ecalypse_rental_db['branch'] . "` (
							  `id_branch` int(11) NOT NULL AUTO_INCREMENT,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `updated` datetime DEFAULT NULL,
							  `deleted` datetime DEFAULT NULL,
							  `active` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1 - yes, 0 - no',
							  `name` varchar(255) NOT NULL,
							  `country` char(2) NOT NULL,
							  `state` varchar(255) NOT NULL,
							  `city` varchar(255) NOT NULL,
							  `zip` varchar(30) NOT NULL,
							  `street` varchar(255) NOT NULL,
							  `email` varchar(255) NOT NULL,
							  `phone` varchar(255) NOT NULL,
							  `description` text NOT NULL,
							  `picture` varchar(255) DEFAULT NULL,
							  `bid` varchar(30) NOT NULL,
							  `gps` varchar(50) NOT NULL DEFAULT '',
							  `outside_price` float NOT NULL DEFAULT 0,
							  `is_default` TINYINT UNSIGNED NOT NULL DEFAULT '0',
							  `ordering` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
							  `enter_hours` TEXT NULL DEFAULT NULL,
							  `return_hours` TEXT NULL DEFAULT NULL,
							  `specific_times` TINYINT UNSIGNED NOT NULL DEFAULT '0',
							  `show_location` TINYINT UNSIGNED NOT NULL DEFAULT '1',
							  `branch_tax` TEXT NULL DEFAULT NULL,
							  `translations` TEXT NULL DEFAULT NULL,
							  PRIMARY KEY (`id_branch`),
							  KEY `deleted` (`deleted`,`active`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $ecalypse_rental_db['branch_hours'] . "` (
							  `id_branch` int(11) NOT NULL,
							  `day` tinyint(4) NOT NULL,
							  `hours_from` time NOT NULL,
							  `hours_to` time NOT NULL,
							  `hours_from_2` TIME NULL DEFAULT NULL,
							  `hours_to_2` TIME NULL DEFAULT NULL,
							  UNIQUE KEY `id_branch` (`id_branch`,`day`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			// Fleet
			$sql = "CREATE TABLE `" . $ecalypse_rental_db['fleet'] . "` (
							  `id_fleet` int(11) NOT NULL AUTO_INCREMENT,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `updated` datetime DEFAULT NULL,
							  `deleted` datetime DEFAULT NULL,
							  `name` varchar(255) NOT NULL,
							  `id_category` int(11) NOT NULL DEFAULT '0',
							  `id_branch` int(11) DEFAULT NULL,
							  `global_pricing_scheme` int(11) NOT NULL,
							  `min_rental_time` int(11) NOT NULL,
							  `seats` int(11) NOT NULL,
							  `doors` int(11) NOT NULL,
							  `luggage` int(11) NOT NULL,
							  `transmission` tinyint(4) NOT NULL,
							  `free_distance` int(11) NOT NULL,
							  `free_distance_hour` int(11) NOT NULL,
							  `ac` tinyint(4) NOT NULL,
							  `fuel` tinyint(4) NOT NULL COMMENT '1 - Petrol, 2 - Diesel',
							  `number_vehicles` int(11) NOT NULL,
							  `consumption` float NOT NULL,
							  `description` text NOT NULL,
							  `deposit` float NOT NULL,
							  `license` varchar(255) NOT NULL,
							  `vin` varchar(255) NOT NULL,
							  `internal_id` varchar(255) NOT NULL,
							  `picture` varchar(255) DEFAULT NULL,
							  `additional_pictures` text NULL DEFAULT NULL,
							  `class_code` varchar(15) NULL DEFAULT NULL,
							  `ordering` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
							  `additional_parameters` TEXT NULL DEFAULT NULL,
							  `price_from` DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT '0',
							  `similar_cars` TEXT NULL DEFAULT NULL,
							  PRIMARY KEY (`id_fleet`),
							  KEY `id_category` (`id_category`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $ecalypse_rental_db['fleet_extras'] . "` (
							  `id_fleet` int(11) NOT NULL,
							  `id_extras` int(11) NOT NULL,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
							  UNIQUE KEY `id_fleet` (`id_fleet`,`id_extras`),
							  KEY `id_fleet_2` (`id_fleet`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $ecalypse_rental_db['fleet_pricing'] . "` (
							  `id_fp` int(11) NOT NULL AUTO_INCREMENT,
								`id_fleet` int(11) NOT NULL,
							  `id_pricing` int(11) NOT NULL,
							  `priority` int(11) NOT NULL,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `valid_from` date DEFAULT NULL,
							  `valid_to` date DEFAULT NULL,
							   `repeat` TINYINT UNSIGNED NOT NULL DEFAULT '0',
							  PRIMARY KEY (`id_fp`),
								KEY `id_fleet` (`id_fleet`),
							  KEY `id_pricing` (`id_pricing`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			// Extras
			$sql = "CREATE TABLE `" . $ecalypse_rental_db['extras'] . "` (
							  `id_extras` int(11) NOT NULL AUTO_INCREMENT,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `updated` datetime DEFAULT NULL,
							  `deleted` datetime DEFAULT NULL,
							  `name` varchar(255) NOT NULL,
							  `name_admin` varchar(255) NOT NULL,
							  `name_translations` TEXT NULL DEFAULT NULL,
							  `description` text NOT NULL,
							  `description_translations` TEXT NULL DEFAULT NULL,
							  `global_pricing_scheme` int(11) NOT NULL,
							  `internal_id` varchar(255) NOT NULL,
							  `max_additional_drivers` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - NO, 1 and more - YES',
							  `picture` varchar(255) DEFAULT NULL,
							  `mandatory` TINYINT UNSIGNED NOT NULL DEFAULT '0',
							  PRIMARY KEY (`id_extras`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $ecalypse_rental_db['extras_pricing'] . "` (
							  `id_ep` int(11) NOT NULL AUTO_INCREMENT,
  							`id_extras` int(11) NOT NULL,
							  `id_pricing` int(11) NOT NULL,
							  `priority` int(11) NOT NULL,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `valid_from` date DEFAULT NULL,
							  `valid_to` date DEFAULT NULL,
							  PRIMARY KEY (`id_ep`),
								KEY `id_extras_2` (`id_extras`),
							  KEY `id_pricing` (`id_pricing`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			// Pricing
			$sql = "CREATE TABLE `" . $ecalypse_rental_db['pricing'] . "` (
							  `id_pricing` int(11) NOT NULL AUTO_INCREMENT,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `updated` datetime DEFAULT NULL,
							  `deleted` datetime DEFAULT NULL,
							  `type` tinyint(4) NOT NULL COMMENT '1 - One time, 2 - Time related',
							  `name` varchar(255) NOT NULL,
							  `currency` varchar(3) NOT NULL,
							  `onetime_price` float DEFAULT NULL,
							  `maxprice` float DEFAULT NULL,
							  `min_price` float DEFAULT '0',
							  `promocode` varchar(255) NOT NULL,
							  `active` tinyint(4) NOT NULL DEFAULT '1',
							  `tax_rates` varchar(255) NOT NULL DEFAULT '',
							  `vat` float NOT NULL DEFAULT '0',
							  `vat_2` float UNSIGNED NOT NULL DEFAULT '0',
							  `active_days` varchar(20) DEFAULT NULL,
							  `rate_id` varchar(20) DEFAULT NULL,
							  `hour_price` FLOAT NULL DEFAULT NULL,
							  `day_price` FLOAT NULL DEFAULT NULL,
							  `week_price` FLOAT NULL DEFAULT NULL,
							  `month_price` FLOAT NULL DEFAULT NULL,
							  `next_day_price` FLOAT NULL DEFAULT NULL,
							  `next_week_price` FLOAT NULL DEFAULT NULL,
							  `am_price` FLOAT NULL DEFAULT NULL,
							  `pm_price` FLOAT NULL DEFAULT NULL,
							  `full_day_price` FLOAT NULL DEFAULT NULL,
							  PRIMARY KEY (`id_pricing`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $ecalypse_rental_db['pricing_ranges'] . "` (
							  `id_pricing` int(11) NOT NULL,
							  `type` tinyint(4) NOT NULL COMMENT '1 - days, 2 - hours',
							  `no_from` int(11) NOT NULL,
							  `no_to` int(11) NOT NULL,
							  `price` float NOT NULL,
							  UNIQUE KEY `id_pricing_2` (`id_pricing`,`type`,`no_from`,`no_to`),
							  KEY `id_pricing` (`id_pricing`)
							) ENGINE=InnoDB {$charset_collate};;
							";
			dbDelta($sql);

			// Vehicle categories
			$sql = "CREATE TABLE `" . $ecalypse_rental_db['vehicle_categories'] . "` (
							  `id_category` int(11) NOT NULL AUTO_INCREMENT,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `updated` datetime DEFAULT NULL,
							  `deleted` datetime DEFAULT NULL,
							  `name` varchar(255) NOT NULL,
							  `picture` varchar(255) NOT NULL,
							  `name_translations` TEXT NULL DEFAULT NULL,
							  PRIMARY KEY (`id_category`),
							  KEY `deleted` (`deleted`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			// Translations
			$sql = "CREATE TABLE `" . $ecalypse_rental_db['translations'] . "` (
							  `id_translation` int(11) NOT NULL AUTO_INCREMENT,
							  `lang` varchar(5) NOT NULL DEFAULT 'en_GB',
							  `original` varchar(255) NOT NULL,
							  `translation` varchar(255) NOT NULL,
							  PRIMARY KEY (`id_translation`),
							  UNIQUE KEY `lang_2` (`lang`(5),`original`(186)),
							  KEY `lang` (`lang`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			// Booking
			$sql = "CREATE TABLE `" . $ecalypse_rental_db['booking'] . "` (
							  `id_booking` int(11) NOT NULL AUTO_INCREMENT,
							  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							  `updated` datetime DEFAULT NULL,
							  `deleted` datetime DEFAULT NULL,
							  `paid` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0 - NO, 1 - YES',
  							`id_order` varchar(10) NOT NULL,
							  `first_name` varchar(255) NOT NULL,
							  `last_name` varchar(255) NOT NULL,
							  `email` varchar(255) NOT NULL,
							  `phone` varchar(255) NOT NULL,
							  `street` varchar(255) NOT NULL,
							  `city` varchar(255) NOT NULL,
							  `zip` varchar(30) NOT NULL,
							  `country` varchar(2) NOT NULL,
							  `company` varchar(255) NOT NULL,
							  `vat` varchar(50) NOT NULL,
							  `flight` varchar(255) NOT NULL,
							  `license` varchar(255) NOT NULL,
							  `id_card` varchar(255) NOT NULL,
							  `terms` tinyint(4) NOT NULL,
							  `newsletter` tinyint(4) NOT NULL,
							  `enter_loc` varchar(255) NOT NULL,
							  `enter_date` datetime NOT NULL,
							  `return_loc` varchar(255) NOT NULL,
							  `return_date` datetime NOT NULL,
							  `vehicle` varchar(255) NOT NULL,
							  `vehicle_id` int(11) NOT NULL,
							  `vehicle_picture` varchar(255) NOT NULL,
							  `vehicle_ac` tinyint(4) NOT NULL,
							  `vehicle_luggage` tinyint(4) NOT NULL,
							  `vehicle_seats` tinyint(4) NOT NULL,
							  `vehicle_fuel` varchar(50) NOT NULL,
							  `vehicle_consumption` float NOT NULL,
							  `vehicle_consumption_metric` varchar(2) NOT NULL,
							  `vehicle_transmission` tinyint(4) NOT NULL,
							  `vehicle_free_distance` varchar(50) NOT NULL,
							  `vehicle_deposit` varchar(50) NOT NULL,
							  `payment_option` varchar(20) NOT NULL,
								`comment` text NOT NULL,
								`goal_sent` tinyint(4) NOT NULL DEFAULT '0',
								`internal_note` varchar(255) NULL DEFAULT '',
								`status` TINYINT UNSIGNED NOT NULL DEFAULT '1',
								`paid_online` FLOAT UNSIGNED NOT NULL DEFAULT '0',
								`currency` VARCHAR(10) NOT NULL DEFAULT '',
								`partner_code` VARCHAR(50) NOT NULL DEFAULT '',
								`id_user` int(11) NULL DEFAULT '0',
								`id_enter_branch` INT(11) NOT NULL DEFAULT '0',
								`id_return_branch` INT(11) NOT NULL DEFAULT '0',
								`total_items` INT(11) NOT NULL DEFAULT '1',
								`lng` VARCHAR(10) NOT NULL DEFAULT '',
							  PRIMARY KEY (`id_booking`),
							  UNIQUE KEY `id_order` (`id_order`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $ecalypse_rental_db['booking_drivers'] . "` (
							  `id_driver` int(11) NOT NULL AUTO_INCREMENT,
							  `id_booking` int(11) NOT NULL,
							  `first_name` varchar(255) NOT NULL,
							  `last_name` varchar(255) NOT NULL,
							  `email` varchar(255) NOT NULL,
							  `phone` varchar(255) NOT NULL,
							  `street` varchar(255) NOT NULL,
							  `city` varchar(255) NOT NULL,
							  `zip` varchar(30) NOT NULL,
							  `country` varchar(2) NOT NULL,
							  `license` varchar(255) NOT NULL,
							  `id_card` varchar(255) NOT NULL,
							  PRIMARY KEY (`id_driver`),
							  KEY `id_booking` (`id_booking`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $ecalypse_rental_db['booking_prices'] . "` (
							  `id_prices` int(11) NOT NULL AUTO_INCREMENT,
							  `id_booking` int(11) NOT NULL,
							  `name` varchar(255) NOT NULL,
							  `price` float NOT NULL,
							  `currency` varchar(3) NOT NULL,
							  `item_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
							  `extras_id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
							  PRIMARY KEY (`id_prices`),
							  KEY `id_booking` (`id_booking`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $wpdb->prefix . "ecalypse_rental_booking_items` (
								`ecalypse_rental_booking_items_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								`id_booking` INT(10) UNSIGNED NOT NULL,
								`vehicle_id` INT(10) UNSIGNED NOT NULL,
								PRIMARY KEY (`ecalypse_rental_booking_items_id`),
								INDEX `id_booking_id_fleet` (`id_booking`, `vehicle_id`)
							)
							ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $wpdb->prefix . "ecalypse_rental_fleet_parameters` (
								`id_fleet_parameter` INT UNSIGNED NOT NULL AUTO_INCREMENT,
								`name` TEXT NOT NULL,
								`values` TEXT NULL DEFAULT NULL,
								`type` TINYINT UNSIGNED NOT NULL DEFAULT '1' COMMENT '1 = range, 2 = values',
								`range_from` INT UNSIGNED NOT NULL DEFAULT '0',
								`range_to` INT UNSIGNED NOT NULL DEFAULT '0',
								`active` TINYINT UNSIGNED NOT NULL DEFAULT '1',
								`filter` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
								PRIMARY KEY (`id_fleet_parameter`)
							) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $wpdb->prefix . "ecalypse_rental_fleet_parameters_values` (
								`fleet_id` INT UNSIGNED NOT NULL,
								`fleet_parameters_id` INT UNSIGNED NOT NULL,
								`value` INT UNSIGNED NOT NULL,
								PRIMARY KEY (`fleet_id`, `fleet_parameters_id`)
							) ENGINE=InnoDB {$charset_collate};";

			dbDelta($sql);

			$sql = "CREATE TABLE `" . $wpdb->prefix . "ecalypse_rental_webhook_queue` (
							 	`id_webhook_queue` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
								`id_booking` INT(10) UNSIGNED NOT NULL,
								`date` DATETIME NOT NULL,
								PRIMARY KEY (`id_webhook_queue`)
								) ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);

			$sql = "CREATE TABLE `" . $wpdb->prefix . "rencato_connector_log` (
							`connector_log_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
							`id` INT(10) UNSIGNED NOT NULL,
							`method` VARCHAR(75) NOT NULL,
							`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
							`status` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 =error, 1 = success',
							`resent` DATETIME NULL DEFAULT NULL,
							`error` VARCHAR(255) NOT NULL DEFAULT '',
							PRIMARY KEY (`connector_log_id`)
						)
						ENGINE=InnoDB {$charset_collate};";
			dbDelta($sql);
			
			add_role( 'rental_manager', __( 'Rental Manager', 'ecalypse-rental' ), array(
				'read'                   => true,
				'edit_posts'             => true,
				'delete_posts'           => true,
				'unfiltered_html'        => true,
				'upload_files'           => true,
				'export'                 => true,
				'import'                 => true,
				'delete_others_pages'    => true,
				'delete_others_posts'    => true,
				'delete_pages'           => true,
				'delete_private_pages'   => true,
				'delete_private_posts'   => true,
				'delete_published_pages' => true,
				'delete_published_posts' => true,
				'edit_others_pages'      => true,
				'edit_others_posts'      => true,
				'edit_pages'             => true,
				'edit_private_pages'     => true,
				'edit_private_posts'     => true,
				'edit_published_pages'   => true,
				'edit_published_posts'   => true,
				'manage_categories'      => true,
				'manage_links'           => true,
				'moderate_comments'      => true,
				'publish_pages'          => true,
				'publish_posts'          => true,
				'read_private_pages'     => true,
				'read_private_posts'     => true
			) );
			
			update_option('ecalypse_rental_seasons_break', 'no');
			update_option('ecalypse_rental_time_pricing_type', 'standard');
			
			if ( class_exists('WP_Roles') ) {
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
			}
			
			if ( is_object( $wp_roles ) ) {
				$wp_roles->add_cap( 'administrator', 'manage_ecalypse_rental' );
				$wp_roles->add_cap( 'rental_manager', 'manage_ecalypse_rental' );
			}

			$migration = get_option('ecalypse_rental_run_migration');
			if (!$migration || (int) $migration < 322) {
				// do database migration for back compatibility
				$bookings = $wpdb->get_results('SELECT `id_booking`, `vehicle_id` FROM `' . $ecalypse_rental_db['booking'] . '`');
				foreach ($bookings as $b) {
					$wpdb->insert($wpdb->prefix . "ecalypse_rental_booking_items", array('id_booking' => $b->id_booking, 'vehicle_id' => $b->vehicle_id));
				}
				$wpdb->query('UPDATE ' . $wpdb->prefix . 'ecalypse_rental_booking_items SET total_items = 1');
				update_option('ecalypse_rental_run_migration', 322);
			}

			$apikey = get_option('ecalypse_rental_api_key');
			if (empty($apikey)) {
				update_option('ecalypse_rental_api_key', serialize(array('api_key' => 'FreeVersion', 'date' => Date('Y-m-d H:i:s'))));
			}

			// set email translation if not exists
			$email_body = get_option('ecalypse_rental_reservation_email_en_GB');
			if (empty($email_body)) {
				$email_body = __('Dear [CustomerName],

thank you for your reservation. Here are your reservation details:
[ReservationDetails]
[ReservationNumber]

You can return to your reservation summary page anytime by going to this link:
[ReservationLink]

We are also sending this information to the email address you have provided.

If you would like to change the reservation details, you can do so by calling our office at:
+123 456 789 or by email example@example.org

[ReservationLinkStart]Click here[ReservationLinkEnd] to print your reservation - takes them to reservation summary print out.

Thank you for your business!', 'ecalypse-rental');
				update_option('ecalypse_rental_reservation_email_en_GB', $email_body);
			}

			// set email translation if not exists
			$email_body = get_option('ecalypse_rental_reminder_email_en_GB');
			if (empty($email_body)) {
				$email_body = __('Dear [CustomerName],

do not forget on your reservation. Here are your reservation details:
[ReservationDetails]
[ReservationNumber]

You can see your reservation summary page anytime by going to this link:
[ReservationLink]

[ReservationLinkStart]Click here[ReservationLinkEnd] to print your reservation - takes them to reservation summary print out.

Thank you for your business!', 'ecalypse-rental');
				update_option('ecalypse_rental_reminder_email_en_GB', $email_body);
			}

			// set email translation if not exists
			$email_body = get_option('ecalypse_rental_thank_you_email_en_GB');
			if (empty($email_body)) {
				$email_body = __('Hi [CustomerName],

We hope everything went well with your rental. We loved having you as a customer. Let us know again when you are looking for a good deal on a rental car.

Your rental team', 'ecalypse-rental');
				update_option('ecalypse_rental_thank_you_email_en_GB', $email_body);
				update_option('ecalypse_rental_thank_you_email_subject_en_GB', 'Thank for your reservation #[ReservationNumber]');
			}

			// set email translation if not exists
			$email_body = get_option('ecalypse_rental_email_status_pending_en_GB');
			if (empty($email_body)) {
				$email_body = __('Dear [CustomerName],

thank you for your reservation. We have received it and one of our agents will review it momentarily. At this moment, your reservation is pending payment.

One we have confirmed your reservation, we will inform you via email.

Thank you,

reservation team @websiteurl', 'ecalypse-rental');
				update_option('ecalypse_rental_email_status_pending_en_GB', $email_body);
				update_option('ecalypse_rental_email_status_pending_subject_en_GB', __('Reservation #[ReservationNumber] is pending', 'ecalypse-rental'));
			}

			// set email translation if not exists
			$email_body = get_option('ecalypse_rental_email_status_pending_other_en_GB');
			if (empty($email_body)) {
				$email_body = __('Dear [CustomerName],

thank you for your reservation. We have received it and one of our agents will review it momentarily. At this moment, your reservation is pending.

One we have confirmed your reservation, we will inform you via email.

Thank you,

reservation team @ website url', 'ecalypse-rental');
				update_option('ecalypse_rental_email_status_pending_other_en_GB', $email_body);
				update_option('ecalypse_rental_email_status_pending_other_subject_en_GB', __('Reservation #[ReservationNumber] is pending', 'ecalypse-rental'));
			}
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function load_translations($lang) {
		global $wpdb;

		try {

			$sql = 'SELECT t.`original`, t.`translation`
							FROM `' . EcalypseRental::$db['translations'] . '` t
							WHERE t.`lang` = %s
							ORDER BY t.`id_translation` ASC';

			$data = $wpdb->get_results($wpdb->prepare($sql, $lang));

			$translations = array();

			if ($data && !empty($data)) {
				foreach ($data as $val) {
					$translations[mb_strtolower(stripslashes($val->original))] = stripslashes($val->translation);
				}
			}

			return $translations;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Widget dropdown
	 */
	public function ecalypse_rental_widget_dropdown($widget, $form, $instance) {

		$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
		if (empty($available_languages)) {
			$available_languages = array();
		}

		echo '<p><label for="ecalypse_rental_language">' . __('Display on language:', 'ecalypse-rental') . ' </label>';
		echo '<select id="ecalypse_rental_language" name="ecalypse_rental_language">';

		if (!empty($available_languages)) {
			foreach ($available_languages as $key => $language) {
				$selected = ( $key == $instance['ecalypse_rental_language'] ) ? 'selected' : null;
				echo '<option ' . $selected . ' value="' . $key . '">' . $language['lang'] . ' (' . strtoupper($language['country-www']) . ')</option>';
			}
		}

		$selected = ('en_GB' == $instance['ecalypse_rental_language']) ? 'selected' : null;
		echo '<option ' . $selected . ' value="en_GB">' . __('English (GB)', 'ecalypse-rental') . '</option>';

		$selected = ('all' == $instance['ecalypse_rental_language'] || !isset($instance['ecalypse_rental_language'])) ? 'selected' : null;
		echo '<option ' . $selected . ' value="all">' . __('All Languages', 'ecalypse-rental') . '</option>';

		echo '</select></p>';
	}

	public function valid_time($str) {
		if (preg_match("#([0-1]{1}[0-9]{1}|[2]{1}[0-3]{1}):[0-5]{1}[0-9]{1}#", $str)) {
			return $str;
		}
		return '';
	}

	/**
	 * Return similar cars for vehicle
	 * @global type $wpdb
	 * @param type $vehicle
	 * @return array
	 */
	public function get_similar_cars($vehicle) {
		global $wpdb;
		if (!isset($vehicle->similar_cars)) {
			return array();
		}

		$similar_cars_to_this = unserialize($vehicle->similar_cars);
		if (count($similar_cars_to_this) < 1) {
			return array();
		}

		$similar_cars = array();
		if (!empty($similar_cars_to_this)) {
			// test if this car is available in this dates and return his price
			$overbooking = get_option('ecalypse_rental_overbooking');
			foreach ($similar_cars_to_this as $k => $v) {
				$car = self::get_vehicle_detail($k, $_GET);
				if ($car && isset($car->prices) && !empty($car->prices)) {
					$date_from = date('Y-m-d', strtotime($_GET['fd'])) . ' ' . self::valid_time($_GET['fh']);
					$date_to = date('Y-m-d', strtotime($_GET['td'])) . ' ' . self::valid_time($_GET['th']);
					$data = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE `deleted` IS NULL AND `vehicle_id` = %d AND ((DATE(`enter_date`) >= %s && DATE(`enter_date`) <= %s) OR (DATE(`return_date`) >= %s && DATE(`return_date`) <= %s)) ORDER BY `enter_date` ASC', $k, $date_from, $date_to, $date_from, $date_to), ARRAY_A);
					$booking_data = array();
					foreach ($data as $book) {
						$from_timestamp = strtotime($book['enter_date']);
						$to_timestamp = strtotime($book['return_date']);
						while ($from_timestamp <= $to_timestamp) {
							$key = date('Y-m-d', $from_timestamp);
							if (!isset($booking_data[$key])) {
								$booking_data[$key] = 0;
							}
							$booking_data[$key] ++;
							$from_timestamp = strtotime('+1 day', $from_timestamp);
						}
					}
					$max = 0;
					if (!empty($booking_data)) {
						$max = max($booking_data);
					}
					if ($max < $vehicle->number_vehicles || (int) $vehicle->number_vehicles == 0) {
						$similar_cars[] = $car;
					}
				}
			}
		}
		return $similar_cars;
	}

	/**
	 * Update widget
	 */
	public function ecalypse_rental_widget_update($instance, $new_instance, $old_instance) {
		$instance["ecalypse_rental_language"] = sanitize_text_field($_POST["ecalypse_rental_language"]);
		return $instance;
	}

	/**
	 * Display widget
	 */
	public function ecalypse_rental_display_widget($instance, $widget, $args) {

		if (isset($instance['ecalypse_rental_language']) && $instance['ecalypse_rental_language'] != EcalypseRentalSession::$session['ecalypse_rental_language'] && $instance['ecalypse_rental_language'] != 'all') {
			return false;
		}

		return $instance;
	}

	/**
	 * Return url with current filter parameters and order parameter $by
	 * @param string $by
	 * @return string
	 */
	public static function sort_link($by = 'name', $name = 'order', $except = array('order')) {
		$link = '';
		foreach ($_GET as $k => $v) {
			if (in_array($k, $except)) {
				continue;
			}
			$link .= '&' . $k . '=' . $v;
		}

		$uri = $_SERVER['REQUEST_URI'];
		if (strpos($uri, '?') !== false) {
			$uri = substr($uri, 0, strpos($uri, '?'));
		}
		return 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . '/' . $uri . '?' . trim($link, '&') . ($link != '' ? '&' : '') . $name . '=' . $by;
	}

	/**
	 * Deep remove slashes
	 * @param type $string
	 * @return type
	 */
	public static function removeslashes($string) {
		$string = implode("", explode("\\", $string));
		return stripslashes(trim($string));
	}

	public static function is_plugin($plugin) {
		$active = in_array($plugin, (array) get_option('active_plugins', array()));

		$active_multisite = false;
		if (is_multisite()) {
			$plugins = get_site_option('active_sitewide_plugins');
			$active_multisite = isset($plugins[$plugin]);
		}

		return $active || $active_multisite;
	}

	public static function get_branch_tax($branch_tax) {
		$return = array();
		if (!empty($branch_tax)) {

			$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
			$lang = strtolower(end(explode('_', $lang)));

			foreach ($branch_tax as $k => $v) {
				$tax_name = $v['name'];
				if ($lang != 'gb') {
					if (isset($v['name_translations'][$lang]) && $v['name_translations'][$lang] != '') {
						$tax_name = $v['name_translations'][$lang];
					}
				}
				$return[$k] = array('tax' => $v['tax'], 'text' => $v['tax'] . '% ' . $tax_name);
			}
		}
		return $return;
	}

	public static function paypal_ipn() {
		global $wpdb;
		ob_start();
		echo '-------------' . date('Y-m-d H:i:s') . '----------' . "\n";
		print_r($_POST);
		$result = ob_get_clean();
		file_put_contents('info_log.log', $result, FILE_APPEND);

		require_once(dirname(__FILE__) . '/paypal_ipnlistener.class.php');
		$listener = new IpnListener();
		$listener->use_sandbox = false;

		try {
			$verified = $listener->processIpn();
		} catch (Exception $e) {
			// fatal error trying to process IPN.

			file_put_contents('paypal_ipn.log', $e . "\n\n-------------------------------------\n\n", FILE_APPEND);
			exit(0);
		}

		file_put_contents('paypal_ipn.log', $listener->getTextReport(), FILE_APPEND);

		if ($verified) {
			// IPN response was "VERIFIED"
			$wpdb->query($wpdb->prepare('UPDATE ' . EcalypseRental::$db['booking'] . ' SET `paid_online` = ' . ((float) $_POST['mc_gross']) . ', `status` = 1 WHERE MD5(CONCAT(`id_order`, %s, `email`)) = %s', EcalypseRental::$hash_salt, sanitize_text_field($_POST['item_number'])));
			file_put_contents('paypal_ipn.log', '***VERIFIED*** - ' . $wpdb->prepare('UPDATE ' . EcalypseRental::$db['booking'] . ' SET `paid_online` = ' . ((float) $_POST['mc_gross']) . ', `status` = 1 WHERE MD5(CONCAT(`id_order`, %s, `email`)) = %s', EcalypseRental::$hash_salt, sanitize_text_field($_POST['item_number'])), FILE_APPEND);

			$data = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE MD5(CONCAT(`id_order`, %s, `email`)) = %s LIMIT 1', EcalypseRental::$hash_salt, sanitize_text_field($_POST['item_number'])), ARRAY_A);

			if ($data) {
				EcalypseRental::send_emails($data['id_booking']);
			}
			//Header('Location: ' . home_url() . '?page=ecalypse-rental&summary=' . $_POST['item_number']); Exit;
		} else {
			// IPN response was "INVALID"
			//Header('Location: ' . home_url() . '?page=ecalypse-rental&paymentError=1'); Exit;
			file_put_contents('paypal_ipn.log', '***INVALID***', FILE_APPEND);
		}
		file_put_contents('paypal_ipn.log', "\n\n-------------------------------------\n\n", FILE_APPEND);
	}

}
