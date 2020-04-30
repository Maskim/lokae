<?php

/*
  Version: 3.0.3

  @created: 2015-12-16
  @todo: ---

 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

$extrasTranslations = null;

class EcalypseRentalTheme {

	public static function init() {
		add_action('wp_enqueue_scripts', array('EcalypseRentalTheme', 'add_styles_scripts'));
		add_shortcode('ecalypse_rental_category', array('EcalypseRentalTheme', 'render_category'));
		add_shortcode('ecalypse_rental_cars', array('EcalypseRentalTheme', 'render_category'));
		add_shortcode('ecalypse_rental_manage_booking', array('EcalypseRentalTheme', 'manage_booking'));
		add_shortcode('ecalypse_rental_locations', array('EcalypseRentalTheme', 'render_locations'));
		add_shortcode('ecalypse_rental_fleet', array('EcalypseRentalTheme', 'render_fleet'));
		add_shortcode('ecalypse_rental_button', array('EcalypseRentalTheme', 'render_button'));
		add_shortcode('ecalypse_rental_book_box', array('EcalypseRentalTheme', 'render_book_box'));
		add_shortcode('ecalypse_rental_currency_selector', array('EcalypseRentalTheme', 'render_currency_selector'));
	}

	/**
	 * Add JS and CSS to non compatible theme
	 */
	public static function add_styles_scripts() {
		wp_enqueue_script("jquery");
		wp_enqueue_script('ecalypse-rental-app', ECALYPSERENTALSTARTER__PLUGIN_URL . '/assets/front-end/app.js', array());
		wp_enqueue_script('ecalypse-rental-lb', ECALYPSERENTALSTARTER__PLUGIN_URL . '/assets/front-end/lightbox.min.js', array());
		wp_enqueue_style('ecalypse-rental-style', ECALYPSERENTALSTARTER__PLUGIN_URL . '/assets/front-end/style.css');
		wp_enqueue_style('ecalypse-rental-lb', ECALYPSERENTALSTARTER__PLUGIN_URL . '/assets/front-end/lightbox.css');
		wp_enqueue_style('ecalypse-rental-print', ECALYPSERENTALSTARTER__PLUGIN_URL . '/assets/front-end/print.css', array('ecalypse-rental-style'), '', 'print');
		if (is_file(ECALYPSERENTALSTARTER__PLUGIN_DIR.'/assets/front-end/colors.css')) {
			wp_enqueue_style('ecalypse-rental-color-style', ECALYPSERENTALSTARTER__PLUGIN_URL . '/assets/front-end/colors.css');
		}
	}

	public static function add_currency_selector() {
		$currency = array(get_option('ecalypse_rental_global_currency'));
		$av_currencies = unserialize(get_option('ecalypse_rental_available_currencies'));
		if (!empty($av_currencies)) {
			$av_currencies = array_keys($av_currencies);
			$currency = array_merge($currency, $av_currencies);
		}

		include ECALYPSERENTALSTARTER__PLUGIN_DIR . '/templates/currency_selector.php';
	}

	public static function manage_booking($params = array()) {
		
		if (isset($params['lng'])) {
			EcalypseRental::change_lng($params['lng']);
		}
		
		ob_start();
		include(ECALYPSERENTALSTARTER__PLUGIN_DIR . '/templates/manage-booking.php');
		include ECALYPSERENTALSTARTER__PLUGIN_DIR . '/templates/booking-javascript.php';
		$box = ob_get_clean();

		return $box;
	}

	function render_booking_form($params = array()) {
		global $theme_options;
		
		if (isset($params['lng'])) {
			EcalypseRental::change_lng($params['lng']);
		}

		// Locations + business hours
		$locations = EcalypseRental::get_locations();
		$vehicle_cats = EcalypseRental::get_vehicle_categories();
		$vehicle_names = EcalypseRental::get_vehicle_names();

		wp_register_style('jquery-ui.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
		wp_enqueue_style('jquery-ui.css');

		
		include(self::get_file_template_path('booking-form.php'));
		include(self::get_file_template_path('booking-javascript.php'));
	}
	
	function render_button($params = array()) {
		global $theme_options;
		global $ecalypse_rental_fleet_loaded;
		
		if (isset($params['lng'])) {
			EcalypseRental::change_lng($params['lng']);
		}
		
		if (isset($params['id']) && (int) $params['id'] > 0) {
			$fleet_id = $params['id'];
		} else {
			return false;
		}
		
		$text = EcalypseRental::t('Book This Car');
		if (isset($params['text'])) {
			$text = $params['text'];
		}
		
		// test if fleet exists
		$vehicle = EcalypseRental::get_vehicle($fleet_id);
		if (!$vehicle) {
			return;
		}

		// Locations + business hours
		$locations = EcalypseRental::get_locations();
		$vehicle_cats = EcalypseRental::get_vehicle_categories();
		$vehicle_names = EcalypseRental::get_vehicle_names();

		wp_register_style('jquery-ui.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
		wp_enqueue_style('jquery-ui.css');
		
		ob_start();
		if (!$ecalypse_rental_fleet_loaded) {
			$locations = EcalypseRental::get_locations();
			//include(get_file_template_path('booking-form.php'));
			include(self::get_file_template_path('booking-javascript.php'));
		}
		include(self::get_file_template_path('shortcode-button.php'));
		$ecalypse_rental_fleet_loaded = true;
		$button = ob_get_clean();
		return $button;
		
	}

	public static function render_category($params = array()) {
		if (isset($params['lng'])) {
			EcalypseRental::change_lng($params['lng']);
		}

		if (isset($params['id']) && (int) $params['id'] > 0) {
			$category_id = $params['id'];
			$params = array('cats' => $category_id);
		} else {
			if (is_int($params)) {
				$params = array('cats' => $params);
			} else {
				$params = array();
			}
		}

		// test if category exists
		// Locations + business hours
		$locations = EcalypseRental::get_locations();
		$vehicle_cats = EcalypseRental::get_vehicle_categories();
		$vehicle_names = EcalypseRental::get_vehicle_names();
		if (isset($_GET['order'])) {
			$params['order'] = $_GET['order'];
		}
		$vehicles = EcalypseRental::get_vehicles($params);
		$fleet_parameters = EcalypseRental::get_fleet_parameters(true);

		wp_register_style('jquery-ui.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
		wp_enqueue_style('jquery-ui.css');

		ob_start();
		include(ECALYPSERENTALSTARTER__PLUGIN_DIR . '/templates/choose-car-content.php');
		$box = ob_get_clean();
		return $box;
	}

	public static function render_locations($params = array()) {
		
		if (isset($params['lng'])) {
			EcalypseRental::change_lng($params['lng']);
		}

		$locations = EcalypseRental::get_locations(true);

		ob_start();
		include(self::get_file_template_path('our-locations.php'));
		$box = ob_get_clean();

		return $box;
	}

	public static function render_fleet($params = array()) {
		global $ecalypse_rental_fleet_loaded;
		
		if (isset($params['lng'])) {
			EcalypseRental::change_lng($params['lng']);
		}

		$fleet_id = (int) $params['id'];
		if ($fleet_id < 1) {
			return;
		}

		// test if fleet exists
		$vehicle = EcalypseRental::get_vehicle($fleet_id);
		if (!$vehicle) {
			return;
		}
		$vehicle_cats_raw = EcalypseRental::get_vehicle_categories();
		$vehicle_cats = array();
		foreach ($vehicle_cats_raw as $val) {
			$vehicle_cats[$val->id_category] = $val;
		}

		if (!$ecalypse_rental_fleet_loaded) {
			$locations = EcalypseRental::get_locations();
			//include(get_file_template_path('booking-form.php'));
			include(self::get_file_template_path('booking-javascript.php'));
		}
		
		$showvat = get_option('ecalypse_rental_show_vat');
		if ((float) $vehicle->price_from > 0){
			$vehicle->prices['price'] = $vehicle->price_from;
		} elseif ((float) $vehicle->prices['vat'] > 0 && $showvat && $showvat == 'yes') {
			$vehicle->prices['price'] = $vehicle->prices['price_with_tax'];
		}

		wp_register_style('jquery-ui.css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
		wp_enqueue_style('jquery-ui.css');
		ob_start();
		include(self::get_file_template_path('shortcode-fleet.php'));
		$box = ob_get_clean();
		$ecalypse_rental_fleet_loaded = true;
		return $box;
	}

	public static function render_book_box($params = array()) {
		
		if (isset($params['lng'])) {
			EcalypseRental::change_lng($params['lng']);
		}
		
		ob_start();
		include(self::get_file_template_path('book-box.php'));
		$box = ob_get_clean();

		return $box;
	}
	
	public static function render_currency_selector() {
		ob_start();
		self::add_currency_selector();
		$box = ob_get_clean();

		return $box;
	}

	public static function get_file_template_path($template) {
		return ECALYPSERENTALSTARTER__PLUGIN_DIR . '/templates/' . $template;
	}

}
