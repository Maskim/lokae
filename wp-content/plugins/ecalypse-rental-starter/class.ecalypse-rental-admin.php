<?php

/*
  Version: 3.0.3

  @created: 2015-12-16
  @todo:

 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

class EcalypseRental_Admin {

	private static $initiated = false;
	private static $available_languages;
	private static $additional_parameters;
	public static $booking_statuses = array(1 => 'confirmed', 2 => 'pending payment', 3 => 'pending other');
	public static $types_of_rental = array('car' => 'Car rental', 'motorbike' => 'Motorbike rental', 'scooter' => 'Scooter', 'bike' => 'Bike rental', 'boat' => 'Boat rental', 'rv' => 'RV rental', 'other_motorized' => 'Other motorized vehicle rental', 'other_vehicle' => 'Other vehicle rental', 'other' => 'Other rental');
	public static $fleet_parameter_types = array(1 => 'range', 2 => 'values');
	
	public static function init() {
		global $wpdb;

		if (!self::$initiated) {
			self::$booking_statuses = array(1 => __('confirmed', 'ecalypse-rental'), 2 => __('pending payment', 'ecalypse-rental'), 3 => __('pending other', 'ecalypse-rental'));
			self::$types_of_rental = array('car' => __('Car rental', 'ecalypse-rental'), 'motorbike' => __('Motorbike rental', 'ecalypse-rental'), 'scooter' => __('Scooter', 'ecalypse-rental'), 'bike' => __('Bike rental', 'ecalypse-rental'), 'boat' => __('Boat rental', 'ecalypse-rental'), 'rv' => __('RV rental', 'ecalypse-rental'), 'other_motorized' => __('Other motorized vehicle rental', 'ecalypse-rental'), 'other_vehicle' => __('Other vehicle rental', 'ecalypse-rental'), 'other' => __('Other rental', 'ecalypse-rental'));
			self::init_hooks();
		}
		
		// Demo data
		if (isset($_POST['import_demo_data'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'import_demo_data');
			self::import_demo_data();
			self::set_flash_msg('success', __('Demo data was successfully imported.', 'ecalypse-rental'));
			Header('Location: ' . self::get_page_url('ecalypse-rental'));
			Exit;
		}

		//////////
		// AJAX //
		//////////
		if (isset($_GET['get_day_ranges']) && (int) $_GET['get_day_ranges'] > 0) {
			print self::print_pricing_ranges((int) $_GET['get_day_ranges']);
			exit;
		}

		if (isset($_GET['get_onetime_price']) && (int) $_GET['get_onetime_price'] > 0) {
			print self::print_onetime_price((int) $_GET['get_onetime_price']);
			exit;
		}

		if (isset($_GET['get_extras_price_schemes']) && (int) $_GET['get_extras_price_schemes'] > 0) {
			print self::print_price_schemes('extras', (int) $_GET['get_extras_price_schemes']); // id_extras
			exit;
		}

		if (isset($_GET['get_fleet_price_schemes']) && (int) $_GET['get_fleet_price_schemes'] > 0) {
			print self::print_price_schemes('fleet', (int) $_GET['get_fleet_price_schemes']); // id_extras
			exit;
		}

		if (isset($_POST['send_test_email'])) {
			check_ajax_referer( 'settings-email-test');
			print self::send_test_email();
			exit;
		}

		////////////////
		// NEWSLETTER //
		////////////////
		
		// BULK DELETE
		if (isset($_POST['batch_delete_newsletter']) && !empty($_POST['batch_processing_values'])) {
			check_ajax_referer( 'batch_delete_newsletter');
			$bookings = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($bookings as $id_booking) {
				$ret = self::remove_newsletter((int) $id_booking);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_booking . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_booking . ' - error';
				}
			}
			
			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Emails was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-newsletter'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Emails was not deleted due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-newsletter'));
				Exit;
			}
		}

		if (isset($_GET['ecalypse-rental-newsletter-export'])) {
			check_ajax_referer( 'ecalypse-rental-newsletter-export');
			self::newsletter_export($_GET['ecalypse-rental-newsletter-export']);
		}

		//////////////
		// SETTINGS //
		//////////////
		
		if (isset($_POST['save_seo'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-seo');
			update_option('ecalypse_rental_seo', serialize(sanitize_text_field($_POST['seo'])));
			self::set_flash_msg('success', __('SEO Settings was successfully saved.', 'ecalypse-rental'));
			Header('Location: ' . self::get_page_url('ecalypse-rental-settings').'#seo-settings');
			Exit;
		}

		if (isset($_POST['edit_settings'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-global');
			$msg = self::update_settings();
			if ($msg === true) {
				self::set_flash_msg('success', __('Settings was successfully saved.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Settings was not saved due to error  (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
				Exit;
			}
		}
		
		if (isset($_POST['edit_visual_settings'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-theme');
			unset($_POST['edit_visual_settings']);
			$data = array();
			foreach ($_POST as $k => $v) {
				$data[sanitize_text_field($k)] = sanitize_text_field($v);
			}
			update_option('ecalypse_rental_theme_options', serialize($data));
			self::set_flash_msg('success', __('Visual Settings was successfully saved.', 'ecalypse-rental'));
			Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
			Exit;
		}
		
		if (isset($_POST['edit_company_info'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-company');
			$msg = self::update_company_info();
			if ($msg === true) {
				self::set_flash_msg('success', __('Company info was successfully saved.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Company info was not saved due to error  (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
				Exit;
			}
		}

		if (isset($_POST['update_vehicle_categories'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-categories');
			$msg = self::update_vehicle_categories();
			if ($msg === true) {
				self::set_flash_msg('success', __('Vehicle Categories was successfully saved.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings') . '#vehicle-categories');
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Vehicle Categories was not saved due to error  (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings') . '#vehicle-categories');
				Exit;
			}
		}

		if (isset($_POST['add_vehicle_category'])) {
			check_admin_referer( 'settings-categories-new');
			$msg = self::add_vehicle_category();
			if ($msg === true) {
				self::set_flash_msg('success', __('Vehicle Category was successfully added.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings') . '#vehicle-categories');
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Vehicle Category was not added due to error  (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings') . '#vehicle-categories');
				Exit;
			}
		}
		
		if (isset($_POST['save_reservation_inputs'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-inputs');
			$inputs = array();
			$inputs_list = array('company' => __('Company', 'ecalypse-rental'), 'vat' => 'VAT', 'flight' => __('Flight number', 'ecalypse-rental'), 'license' => __('License number', 'ecalypse-rental'), 'id_card' => __('ID / Passport number', 'ecalypse-rental'), 'partner_code' => __('Partner code', 'ecalypse-rental'));
			foreach ($inputs_list as $k => $v) {
				if (!isset($_POST['ecalypse_rental_inputs'][$k])) {
					$inputs[$k] = 1;
				}
			}
			
			update_option('ecalypse_rental_reservation_inputs', serialize($inputs));
			$msg = true;
				
			if ($msg === true) {
				self::set_flash_msg('success', __('Reservation inputs was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings') . '#reservation-inputs');
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Reservation inputs was not updated due to error  (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings') . '#reservation-inputs');
				Exit;
			}
		}
		
		if (isset($_POST['save_holidays'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-holidays');
			$holidays = array();
			if (isset($_POST['ecalypse_rental_holidays'])) {
				foreach ($_POST['ecalypse_rental_holidays'] as $date) {
					$holidays[sanitize_text_field($date)] = sanitize_text_field($date);
				}
			}
			$msg = true;
			update_option('ecalypse_rental_holidays', serialize($holidays));
				
			if ($msg === true) {
				self::set_flash_msg('success', __('Holidays was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings') . '#holidays');
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Holidays was not updated due to error  (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings') . '#holidays');
				Exit;
			}
		}
		
		if (isset($_POST['replace_price_scheme']) && (int) $_POST['price_scheme_original'] > 0 && (int) $_POST['price_scheme_new'] > 0) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-scheme-replace');
			$msg = self::replace_price_scheme((int) $_POST['price_scheme_original'], (int) $_POST['price_scheme_new']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Price scheme was successfully replaced.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Price scheme was not replaced due to error  (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
				Exit;
			}
		}

		if (isset($_POST['save_smtp_settings'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-email-test');
			$msg = self::update_smtp_settings();
			if ($msg === true) {
				self::set_flash_msg('success', __('SMTP Settings was successfully replaced.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('SMTP Settings was not replaced due to error  (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-settings'));
				Exit;
			}
		}
		
		if (isset($_POST['export_database'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'settings-export');
			$sql = self::export_database();
			$fileName = 'car_rental_plugin_db_' . Date('Y-m-d') . '.sql';

			header('Content-Type: application/octet-stream');
			header("Content-Transfer-Encoding: Binary");
			header("Content-disposition: attachment; filename=\"" . $fileName . "\"");
			echo $sql;
			exit;
		}

		//////////////
		// BRANCHES //
		//////////////
		// ADD / MODIFY
		if (isset($_POST['add_branch'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'add_branch');
			$msg = self::add_branch();
			if ($msg === true) {
				self::set_flash_msg('success', ((isset($_POST['id_branch'])) ? __('Branch was successfully modified.', 'ecalypse-rental') : __('New branch was successfully added.', 'ecalypse-rental')));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			} else {
				self::set_flash_msg('danger', ((isset($_POST['id_branch'])) ? sprintf(__('Branch was not modified due to error (%s).', 'ecalypse-rental'), $msg) : sprintf(__('New branch was not added due to error (%s).', 'ecalypse-rental'), $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			}
		}

		// COPY
		if (isset($_POST['copy_branch']) && !empty($_POST['id_branch'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'copy_branch');
			$msg = self::copy_branch((int) $_POST['id_branch']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Branch was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Branch was not copied due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			}
		}

		// BULK COPY
		if (isset($_POST['batch_copy_branch']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_copy_branch');
			$branches = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($branches as $id_branch) {
				$ret = self::copy_branch((int) $id_branch);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_branch . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_branch . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Branches was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Branches was not copied due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			}
		}

		// DELETE
		if (isset($_POST['delete_branch']) && !empty($_POST['id_branch'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'delete_branch');
			$msg = self::delete_branch((int) $_POST['id_branch']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Branch was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Branch was not deleted due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			}
		}

		// BULK DELETE
		if (isset($_POST['batch_delete_branch']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_delete_branch');
			$branches = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($branches as $id_branch) {
				$ret = self::delete_branch((int) $id_branch);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_branch . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_branch . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Branches was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Branches was not deleted due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			}
		}

		// RESTORE
		if (isset($_POST['restore_branch']) && !empty($_POST['id_branch'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'restore_branch');
			$msg = self::restore_branch((int) $_POST['id_branch']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Branch was successfully restored.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Branch was not restored due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-branches'));
				Exit;
			}
		}

		////////////
		// EXTRAS //
		////////////
		// ADD / MODIFY
		if (isset($_POST['add_extras'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'add_extras');
			$msg = self::add_extras();
			if ($msg === true) {
				self::set_flash_msg('success', ((isset($_POST['id_extras'])) ? __('Item was successfully modified.', 'ecalypse-rental') : __('New item was successfully added.', 'ecalypse-rental')));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			} else {
				self::set_flash_msg('danger', ((isset($_POST['id_extras'])) ? sprintf(__('Item was not modified due to error (%s).', 'ecalypse-rental'), $msg) : sprintf(__('New item was not added due to error (%s).', 'ecalypse-rental'), $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			}
		}

		// COPY
		if (isset($_POST['copy_extras']) && !empty($_POST['id_extras'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'copy_extras');
			$msg = self::copy_extras((int) $_POST['id_extras']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Item was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Item was not copied due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			}
		}

		// BULK COPY
		if (isset($_POST['batch_copy_extras']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_copy_extras');
			$extras = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($extras as $id_extras) {
				$ret = self::copy_extras((int) $id_extras);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_extras . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_extras . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Items was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Items was not copied due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			}
		}

		// DELETE
		if (isset($_POST['delete_extras']) && !empty($_POST['id_extras'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'delete_extras');
			$msg = self::delete_extras((int) $_POST['id_extras']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Item was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Item was not deleted due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			}
		}

		// BULK DELETE
		if (isset($_POST['batch_delete_extras']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_delete_extras');
			$extras = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($extras as $id_extras) {
				$ret = self::delete_extras((int) $id_extras);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_extras . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_extras . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Items was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Items was not deleted due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			}
		}
		
		// DELETE FROM DATABASE
		if (isset($_POST['batch_delete_db_extras']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_delete_db_extras');
			$extras = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($extras as $id_extras) {
				$ret = self::delete_extras_from_db((int) $id_extras);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_extras . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_extras . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Extras was successfully deleted from database.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras') . '&deleted');
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Extras was not deleted from database due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras') . '&deleted');
				Exit;
			}
		}

		// RESTORE
		if (isset($_POST['restore_extras']) && !empty($_POST['id_extras'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'restore_extras');
			$msg = self::restore_extras((int) $_POST['id_extras']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Item was successfully restored.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Item was not restored due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-extras'));
				Exit;
			}
		}

		///////////
		// FLEET //
		///////////
		// ADD / MODIFY
		if (isset($_POST['add_fleet'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'add_fleet');
			$msg = self::add_fleet();
			if ($msg === true) {
				self::set_flash_msg('success', ((isset($_POST['id_fleet'])) ? __('Vehicle was successfully modified.', 'ecalypse-rental') : __('New vehicle was successfully added.', 'ecalypse-rental')));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			} else {
				self::set_flash_msg('danger', ((isset($_POST['id_fleet'])) ? sprintf(__('Vehicle was not modified due to error (%s).', 'ecalypse-rental'), $msg) : sprintf(__('New vehicle was not added due to error (%s).', 'ecalypse-rental'), $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			}
		}

		// COPY
		if (isset($_POST['copy_fleet']) && !empty($_POST['id_fleet'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'copy_fleet');
			$msg = self::copy_fleet((int) $_POST['id_fleet']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Vehicle was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Vehicle was not copied due to error (%s).", 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			}
		}
		
		// RESTORE
		if (isset($_POST['restore_fleet']) && !empty($_POST['id_fleet'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'restore_fleet');
			$msg = self::restore_fleet((int) $_POST['id_fleet']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Vehicle was successfully restored.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Vehicle was not restored due to error (%s).", 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			}
		}
		
		// BULK COPY
		if (isset($_POST['batch_copy_fleet']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_copy_fleet');
			$vehicles = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($vehicles as $id_fleet) {
				$ret = self::copy_fleet((int) $id_fleet);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_fleet . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_fleet . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Vehicles was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Vehicles was not copied due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			}
		}

		// DELETE
		if (isset($_POST['delete_fleet']) && !empty($_POST['id_fleet'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'delete_fleet');
			$msg = self::delete_fleet((int) $_POST['id_fleet']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Vehicle was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Vehicle was not deleted due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			}
		}

		// BULK DELETE
		if (isset($_POST['batch_delete_fleet']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_delete_fleet');
			$vehicles = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($vehicles as $id_fleet) {
				$ret = self::delete_fleet((int) $id_fleet);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_fleet . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_fleet . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Vehicles was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Vehicles was not deleted due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet'));
				Exit;
			}
		}

		// DELETE FROM DATABASE
		if (isset($_POST['batch_delete_db_fleet']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_delete_db_fleet');
			$vehicles = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($vehicles as $id_fleet) {
				$ret = self::delete_fleet_from_db((int) $id_fleet);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_fleet . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_fleet . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Vehicles was successfully deleted from database.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet') . '&deleted');
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Vehicles was not deleted from database due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet') . '&deleted');
				Exit;
			}
		}
		
		///////////
		// FLEET PARAMETERS //
		///////////
		// ADD / MODIFY
		if (isset($_POST['add_fleet_parameter'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'add_fleet_parameter');
			$msg = self::add_fleet_parameter();
			if ($msg === true) {
				self::set_flash_msg('success', ((isset($_POST['id_fleet_parameter'])) ? __('Parameter was successfully modified.', 'ecalypse-rental') : __('New parameter was successfully added.', 'ecalypse-rental')));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet-parameters'));
				Exit;
			} else {
				self::set_flash_msg('danger', ((isset($_POST['id_fleet_parameter'])) ? sprintf(__('Parameter was not modified due to error (%s).', 'ecalypse-rental'), $msg) : sprintf(__('New parameter was not added due to error (%s).', 'ecalypse-rental'), $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet-parameters'));
				Exit;
			}
		}

		// COPY
		if (isset($_POST['copy_fleet_parameter']) && !empty($_POST['id_fleet_parameter'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'copy_fleet_parameter');
			$msg = self::copy_fleet_parameter((int) $_POST['id_fleet_parameter']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Parameter was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet-parameters'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Parameter was not copied due to error (%s).", 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet-parameters'));
				Exit;
			}
		}

		// DELETE
		if (isset($_POST['delete_fleet_parameter']) && !empty($_POST['id_fleet_parameter'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'delete_fleet_parameter');
			$msg = self::delete_fleet_parameter((int) $_POST['id_fleet_parameter']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Parameter was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet-parameters'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Parameter was not deleted due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-fleet-parameters'));
				Exit;
			}
		}


		/////////////
		// PRICING //
		/////////////
		// ADD / MODIFY
		if (isset($_POST['add_pricing'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'add_pricing');
			$msg = self::add_pricing();
			if ($msg === true) {
				self::set_flash_msg('success', ((isset($_POST['id_pricing'])) ? __('Price scheme was successfully modified.', 'ecalypse-rental') : __('New price scheme was successfully added.', 'ecalypse-rental')));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			} else {
				self::set_flash_msg('danger', ((isset($_POST['id_pricing'])) ? sprintf(__('Price scheme was not modified due to error (%s).', 'ecalypse-rental'), $msg) : sprintf(__('New price scheme was not added due to error (%s).', 'ecalypse-rental'), $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			}
		}

		// COPY
		if (isset($_POST['copy_pricing']) && !empty($_POST['id_pricing'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'copy_pricing');
			$msg = self::copy_pricing((int) $_POST['id_pricing']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Price scheme was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Price scheme was not copied due to error (%s).", 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			}
		}

		// BULK COPY
		if (isset($_POST['batch_copy_pricing']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_copy_pricing');
			$pricing = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($pricing as $id_pricing) {
				$ret = self::copy_pricing((int) $id_pricing);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_pricing . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_pricing . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Pricing schemes was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Pricing schemes was not copied due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			}
		}

		// DELETE
		if (isset($_POST['delete_pricing']) && !empty($_POST['id_pricing'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'delete_pricing');
			$msg = self::delete_pricing((int) $_POST['id_pricing']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Price scheme was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Price scheme was not deleted due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			}
		}

		// BULK DELETE
		if (isset($_POST['batch_delete_pricing']) && !empty($_POST['batch_processing_values_delete'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_delete_pricing');
			$pricing = explode(',', $_POST['batch_processing_values_delete']);
			$report = $msg = array();
			foreach ($pricing as $id_pricing) {
				$ret = self::delete_pricing((int) $id_pricing);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_pricing . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_pricing . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Pricing schemes was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Pricing schemes was not deleted due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			}
		}
		
		// DELETE FROM DATABASE
		if (isset($_POST['batch_delete_db_pricing']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_delete_db_pricing');
			$pricings = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($pricings as $id_pricing) {
				$ret = self::delete_pricing_from_db((int) $id_pricing);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_pricing . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_pricing . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Pricing scheme was successfully deleted from database.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing') . '&deleted');
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Pricing scheme was not deleted from database due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing') . '&deleted');
				Exit;
			}
		}
		

		// RESTORE
		if (isset($_POST['restore_pricing']) && !empty($_POST['id_pricing'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'restore_pricing');
			$msg = self::restore_pricing((int) $_POST['id_pricing']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Price scheme was successfully restored.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Price scheme was not restored due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-pricing'));
				Exit;
			}
		}

		/////////////
		// BOOKING //
		/////////////
		// ADD / MODIFY
		if (isset($_POST['add_booking']) || isset($_POST['add_booking_emails'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'add_booking');
			$msg = self::add_booking();
			if ($msg === true) {
				self::set_flash_msg('success', ((isset($_POST['id_booking'])) ? __('Booking was successfully modified.', 'ecalypse-rental') : __('New booking was successfully added.', 'ecalypse-rental')));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			} else {
				self::set_flash_msg('danger', ((isset($_POST['id_booking'])) ? sprintf(__('Booking was not modified due to error (%s).', 'ecalypse-rental'), $msg) : sprintf(__('New booking was not added due to error (%s).', 'ecalypse-rental'), $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			}
		}

		// COPY
		if (isset($_POST['copy_booking']) && !empty($_POST['id_booking'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'copy_booking');
			$msg = self::copy_booking((int) $_POST['id_booking']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Booking was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Booking was not copied due to error (%s).", 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			}
		}
		
		// RESEND CONFIRMATION EMAIL
		if (isset($_POST['resend_email']) && !empty($_POST['id_booking'])) {
			
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			
			check_admin_referer( 'resend_email');
			
			$data = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE `id_booking` = %d', (int)$_POST['id_booking']), ARRAY_A);
			switch ($data['status']) {
				case 1:
					// confirmed
					$msg = self::resend_email((int) $_POST['id_booking'], 'ecalypse_rental_reservation_email');
					break;
				case 2:
					// pending payment
					$msg = self::resend_email((int) $_POST['id_booking'], 'ecalypse_rental_email_status_pending');
					break;
				case 3:
					// panding other
					$msg = self::resend_email((int) $_POST['id_booking'], 'ecalypse_rental_email_status_pending_other');
					break;
			}
			if ($msg === true) {
				self::set_flash_msg('success', __('Email was successfully sent.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Email was not sent due to error (%s).", 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			}
		}

		// BULK COPY
		if (isset($_POST['batch_copy_booking']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_copy_booking');
			$booking = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($booking as $id_booking) {
				$ret = self::copy_booking((int) $id_booking);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_booking . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_booking . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Bookings was successfully copied.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Bookings was not copied due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			}
		}

		// DELETE
		if (isset($_POST['delete_booking']) && !empty($_POST['id_booking'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'delete_booking');
			$msg = self::delete_booking((int) $_POST['id_booking']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Booking was successfully archived.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Booking was not archived due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			}
		}

		// BULK DELETE
		if (isset($_POST['batch_delete_booking']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_delete_booking');
			$booking = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($booking as $id_booking) {
				$ret = self::delete_booking_total((int) $id_booking);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_booking . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_booking . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Bookings was successfully deleted.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Bookings was not deleted due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			}
		}

		// BULK ARCHIVE
		if (isset($_POST['batch_archive_booking']) && !empty($_POST['batch_processing_values'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'batch_archive_booking');
			$booking = explode(',', $_POST['batch_processing_values']);
			$report = $msg = array();
			foreach ($booking as $id_booking) {
				$ret = self::delete_booking((int) $id_booking);
				if ($ret === true) {
					$report[] = 'ok';
					$msg[] = (int) $id_booking . ' - ok';
				} else {
					$report[] = 'error';
					$msg[] = (int) $id_booking . ' - error';
				}
			}

			if (!in_array('error', $report)) {
				self::set_flash_msg('success', __('Bookings was successfully archived.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__("Bookings was not archived due to error (%s).", 'ecalypse-rental'), implode(', ', $msg)));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			}
		}

		// RESTORE
		if (isset($_POST['restore_booking']) && !empty($_POST['id_booking'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'restore_booking');
			$msg = self::restore_booking((int) $_POST['id_booking']);
			if ($msg === true) {
				self::set_flash_msg('success', __('Booking was successfully restored.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Booking was not restored due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-booking'));
				Exit;
			}
		}

		//////////////
		// LANGUAGE //
		//////////////

		if (isset($_POST['add_language']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'add_language');
			include dirname(realpath(__FILE__)) . '/languages.php';
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}

			if (isset($ecalypse_rental_languages[sanitize_text_field($_POST['language'])])) {
				$available_languages[sanitize_text_field($_POST['language'])] = $ecalypse_rental_languages[sanitize_text_field($_POST['language'])];
				update_option('ecalypse_rental_available_languages', serialize($available_languages));
				$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));

				$email_body = get_option('ecalypse_rental_reservation_email_' . sanitize_text_field($_POST['language'])); // only allowed languages are in the post variable because condition before
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
					update_option('ecalypse_rental_reservation_email_' . sanitize_text_field($_POST['language']), $email_body); // only allowed languages are in the post variable because condition before
				}

				self::set_flash_msg('success', __('Language was successfully added.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations'));
				Exit;
			}
		}

		if (isset($_POST['primary_language']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'primary_language');
			include dirname(realpath(__FILE__)) . '/languages.php';
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}

			if (!isset($ecalypse_rental_languages[sanitize_text_field($_POST['language'])])) {
				self::set_flash_msg('error', __('Primary language not found.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations'));
				Exit;
			}
			update_option('ecalypse_rental_primary_language', sanitize_text_field($_POST['language']));
			self::set_flash_msg('success', __('Primary language was successfully updated.', 'ecalypse-rental'));
			Header('Location: ' . self::get_page_url('ecalypse-rental-translations'));
			Exit;
		}

		if (isset($_POST['disable_language']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'disable_language');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])])) {
				unset($available_languages[sanitize_text_field($_POST['language'])]);
				update_option('ecalypse_rental_available_languages', serialize($available_languages));
				self::set_flash_msg('success', __('Language was successfully disabled.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations'));
				Exit;
			}
		}

		if (isset($_POST['deactivate_language']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'deactivate_language');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])])) {
				$available_languages[sanitize_text_field($_POST['language'])]['active'] = false;
				update_option('ecalypse_rental_available_languages', serialize($available_languages));
				self::set_flash_msg('success', __('Language was successfully deactivated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}

		if (isset($_POST['activate_language']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'activate_language');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])])) {
				$available_languages[sanitize_text_field($_POST['language'])]['active'] = true;
				update_option('ecalypse_rental_available_languages', serialize($available_languages));
				self::set_flash_msg('success', __('Language was successfully activated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}

		if (isset($_POST['language_save_email']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'language_save_email');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])]) || $_POST['language'] == 'en_GB') {
				update_option('ecalypse_rental_reservation_email_' . sanitize_text_field($_POST['language']), wp_kses_post($_POST['reservation_email']));
				update_option('ecalypse_rental_reservation_email_subject_' . sanitize_text_field($_POST['language']), sanitize_text_field($_POST['reservation_email_subject']));
				self::set_flash_msg('success', __('E-mail was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}
		
		if (isset($_POST['language_save_email_reminder']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'language_save_email_reminder');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])]) || $_POST['language'] == 'en_GB') {
				update_option('ecalypse_rental_reminder_email_' . sanitize_text_field($_POST['language']), wp_kses_post($_POST['reminder_email']));
				update_option('ecalypse_rental_reminder_subject_' . sanitize_text_field($_POST['language']), sanitize_text_field($_POST['reminder_subject']));
				self::set_flash_msg('success', __('Automatic reminder e-mail was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language']));  // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}
		
		if (isset($_POST['language_save_email_thank_you']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'language_save_email_thank_you');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])]) || $_POST['language'] == 'en_GB') {
				update_option('ecalypse_rental_thank_you_email_' . sanitize_text_field($_POST['language']), wp_kses_post($_POST['thank_you_email']));
				update_option('ecalypse_rental_thank_you_email_subject_' . sanitize_text_field($_POST['language']), sanitize_text_field($_POST['thank_you_email_subject']));
				self::set_flash_msg('success', __('Thank you email was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}
		
		if (isset($_POST['language_save_email_status_pending_other']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'language_save_email_status_pending_other');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])]) || $_POST['language'] == 'en_GB') {
				update_option('ecalypse_rental_email_status_pending_other_' . sanitize_text_field($_POST['language']), wp_kses_post($_POST['email_status_pending_other']));
				update_option('ecalypse_rental_email_status_pending_other_subject_' . sanitize_text_field($_POST['language']), sanitize_text_field($_POST['email_status_pending_other_subject']));
				self::set_flash_msg('success', __('E-mail for status "pending other" was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}
		
		if (isset($_POST['language_save_email_status_pending']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'language_save_email_status_pending');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])]) || $_POST['language'] == 'en_GB') {
				update_option('ecalypse_rental_email_status_pending_' . sanitize_text_field($_POST['language']), wp_kses_post($_POST['email_status_pending']));
				update_option('ecalypse_rental_email_status_pending_subject_' . sanitize_text_field($_POST['language']), sanitize_text_field($_POST['email_status_pending_subject']));
				self::set_flash_msg('success', __('E-mail for status "pending payment" was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}

		if (isset($_POST['language_save_terms']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'language_save_terms');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			if (isset($available_languages[sanitize_text_field($_POST['language'])]) || $_POST['language'] == 'en_GB') {
				update_option('ecalypse_rental_terms_conditions_' . sanitize_text_field($_POST['language']), wp_kses_post($_POST['terms_conditions']));
				self::set_flash_msg('success', __('Terms and Conditions was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}

		if (isset($_POST['language_save_theme_translations']) && !empty($_POST['language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'language_save_theme_translations');
			$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
			if (empty($available_languages)) {
				$available_languages = array();
			}
			
			if (isset($available_languages[sanitize_text_field($_POST['language'])]) || $_POST['language'] == 'en_GB') {
				if (is_array($_POST['translation'])) {
					$translations = array();
					foreach ($_POST['translation'] as $k => $v) {
						$translations[wp_kses_post($k)] = wp_kses_post($v);
					}
				} else {
					$translations = wp_kses_post($_POST['translation']);
				}
				self::update_theme_translations(sanitize_text_field($_POST['language']),$translations);
				unset(EcalypseRentalSession::$session['ecalypse_rental_translations']);
				self::set_flash_msg('success', __('Translations was successfully updated.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); // only allowed languages are in the POST variable because it is tested before
				Exit;
			}
		}

		// Import language
		if (isset($_POST['import_language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'import_language');
			$msg = self::import_language();
			if ($msg === true) {
				self::set_flash_msg('success', __('Language file successfully imported.', 'ecalypse-rental'));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language']));
				Exit;
			} else {
				self::set_flash_msg('danger', sprintf(__('Language was not imported due to error (%s).', 'ecalypse-rental'), $msg));
				Header('Location: ' . self::get_page_url('ecalypse-rental-translations') . '&language=' . sanitize_text_field($_POST['language'])); 
				Exit;
			}
		}

		// Export language
		if (isset($_POST['export_language'])) {
			if (!current_user_can('manage_ecalypse_rental')) {
				return;
			}
			check_admin_referer( 'export_language');
			self::export_language();
			exit;
		}
	}

	public static function init_hooks() {

		self::$initiated = true;

		add_action('admin_init', array('EcalypseRental_Admin', 'admin_init'));
		add_action('admin_menu', array('EcalypseRental_Admin', 'admin_menu'));
		add_action('admin_enqueue_scripts', array('EcalypseRental_Admin', 'load_resources'));
		add_filter('plugin_action_links', array('EcalypseRental_Admin', 'plugin_action_links'), 10, 2);
		add_filter('plugin_action_links_' . plugin_basename(plugin_dir_path(__FILE__) . 'ecalypse-rental.php'), array('EcalypseRental_Admin', 'admin_plugin_settings_link'));
		add_action('wp_ajax_ecalypse_rental_save_branch_order', array('EcalypseRental_Admin', 'ajax_save_branch_order'));
		add_action('wp_ajax_ecalypse_rental_save_fleet_order', array('EcalypseRental_Admin', 'ajax_save_fleet_order'));
		add_action('wp_ajax_ecalypse_rental_load_available_cars', array('EcalypseRental_Admin', 'ajax_load_available_cars'));
	}

	public static function admin_init() {

		//load_plugin_textdomain('ecalypse-rental');
	}

	public static function admin_menu() {
		self::load_menu();
	}

	public static function admin_head() {
		if (!current_user_can('manage_options')) {
			return;
		}
	}

	public static function admin_plugin_settings_link($links) {
		$settings_link = '<a href="' . self::get_page_url() . '">' . __('Settings', 'ecalypse-rental') . '</a>';
		array_unshift($links, $settings_link);
		return $links;
	}

	public static function load_menu() {

		// Add Top level menu and sub-menu
		$hook = add_menu_page(__('Ecalypse Starter', 'ecalypse-rental'), __('Ecalypse Starter', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental', array('EcalypseRental_Admin', 'display_page'), plugin_dir_url(__FILE__) . '/assets/ecalypse_rental_menu_icon.png');
		add_submenu_page('ecalypse-rental', __('Fleet - Ecalypse Starter', 'ecalypse-rental'), __('Fleet', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-fleet', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page('ecalypse-rental', __('Extras - Ecalypse Starter', 'ecalypse-rental'), __('Extras', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-extras', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page('ecalypse-rental', __('Branches - Ecalypse Starter', 'ecalypse-rental'), __('Branches', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-branches', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page('ecalypse-rental', __('Pricing - Ecalypse Starter', 'ecalypse-rental'), __('Pricing', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-pricing', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page('ecalypse-rental', __('Booking - Ecalypse Starter', 'ecalypse-rental'), __('Booking', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-booking', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page('ecalypse-rental', __('Translations - Ecalypse Starter', 'ecalypse-rental'), __('Translations', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-translations', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page('ecalypse-rental', __('Settings - Ecalypse Starter', 'ecalypse-rental'), __('Settings', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-settings', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page('ecalypse-rental', __('Newsletter - Ecalypse Starter', 'ecalypse-rental'), __('Newsletter', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-newsletter', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page('ecalypse-rental', __('Rencato connector - Ecalypse Starter', 'ecalypse-rental'), __('Rencato connector', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-rencato-connector', array('EcalypseRental_Admin', 'display_page'));
		add_submenu_page(null, __('Fleet parameters', 'ecalypse-rental'), __('Fleet parameters', 'ecalypse-rental'), 'manage_ecalypse_rental', 'ecalypse-rental-fleet-parameters', array('EcalypseRental_Admin', 'display_page'));
	}

	public static function load_resources() {
		global $hook_suffix, $wp_version;

		$arr = array(
			'ecalypse-rental',
			'ecalypse-rental-fleet',
			'ecalypse-rental-extras',
			'ecalypse-rental-branches',
			'ecalypse-rental-pricing',
			'ecalypse-rental-booking',
			'ecalypse-rental-translations',
			'ecalypse-rental-settings',
			'ecalypse-rental-rencato-connector',
			'ecalypse-rental-newsletter',
			'ecalypse-rental-fleet-parameters'
		);

		$exp = explode('_', $hook_suffix);
		$page = end($exp);
		if (in_array($page, $arr)) {

			wp_enqueue_media();

			wp_register_style('bootstrap.css', ECALYPSERENTALSTARTER__PLUGIN_URL . 'assets/bootstrap.css', array(), ECALYPSERENTALSTARTER_VERSION);
			wp_enqueue_style('bootstrap.css');

			wp_register_style('ecalypse-rental.css', ECALYPSERENTALSTARTER__PLUGIN_URL . 'assets/ecalypse-rental.css', array(), ECALYPSERENTALSTARTER_VERSION);
			wp_enqueue_style('ecalypse-rental.css');

			wp_register_style('jquery-ui.css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css', array());
			wp_enqueue_style('jquery-ui.css');

			wp_register_style('jquery.dataTables.css', '//cdn.datatables.net/1.10.0/css/jquery.dataTables.css', array());
			wp_enqueue_style('jquery.dataTables.css');
			
			wp_enqueue_script( 'jquery' );
			/*
			// Get jquery handle - WP 3.6 or newer changed the jQuery handle (once we're on 3.6+ we can remove this logic)
			$jquery_handle = (version_compare($wp_version, '3.6-alpha1', '>=') ) ? 'jquery-core' : 'jquery';

			// Get the WP built-in version
			$wp_jquery_ver = $GLOBALS['wp_scripts']->registered[$jquery_handle]->ver;

			// Just in case it doesn't work, add a fallback version
			$jquery_ver = ( $wp_jquery_ver == '' ) ? '1.8.3' : $wp_jquery_ver;
			
			$allowed_versions = array('1.11', '1.10');
			
			wp_deregister_script('jquery');
			wp_register_script('jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js', array());
			wp_enqueue_script('jquery'); */

			wp_deregister_script('jqueryui');
			wp_register_script('jqueryui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/jquery-ui.min.js', array());
			wp_enqueue_script('jqueryui');

			wp_register_script('bootstrap.min.js', ECALYPSERENTALSTARTER__PLUGIN_URL . 'assets/bootstrap.min.js', array(), ECALYPSERENTALSTARTER_VERSION);
			wp_enqueue_script('bootstrap.min.js');

			wp_register_style('jquery.dataTables.css', 'https://cdn.datatables.net/1.10.15/css/jquery.dataTables.min.css', array());
			wp_enqueue_style('jquery.dataTables.css');

			wp_register_script('jquery.dataTables.js', 'https://cdn.datatables.net/1.10.15/js/jquery.dataTables.min.js', array());
			wp_enqueue_script('jquery.dataTables.js');

			if ($page == 'ecalypse-rental-newsletter') {

				wp_register_style('dataTables.tableTools.css', '//cdn.datatables.net/tabletools/2.2.3/css/dataTables.tableTools.css', array());
				wp_enqueue_style('dataTables.tableTools.css');

				wp_register_script('dataTables.tableTools.min.js', '//cdn.datatables.net/tabletools/2.2.3/js/dataTables.tableTools.min.js', array());
				wp_enqueue_script('dataTables.tableTools.min.js');
			}

			wp_register_script('ecalypse-rental.js', ECALYPSERENTALSTARTER__PLUGIN_URL . 'assets/ecalypse-rental.js', array(), ECALYPSERENTALSTARTER_VERSION, true);
			wp_enqueue_script('ecalypse-rental.js');
		}
	}

	public static function plugin_action_links($links, $file) {
		if ($file == plugin_basename(ECALYPSERENTALSTARTER__PLUGIN_URL . '/ecalypse-rental.php')) {
			$links[] = '<a href="' . esc_url(self::get_page_url()) . '">' . esc_html__('Settings', 'ecalypse-rental') . '</a>';
		}
		return $links;
	}

	public static function get_page_url($page = 'ecalypse-rental') {

		$arr = array('ecalypse-rental-fleet', 'ecalypse-rental-extras', 'ecalypse-rental-branches', 'ecalypse-rental-pricing',
			'ecalypse-rental-booking', 'ecalypse-rental-translations', 'ecalypse-rental-settings', 'ecalypse-rental-newsletter', 'ecalypse-rental-fleet-parameters', 'ecalypse-rental-rencato-connector');

		if (in_array($page, $arr)) {
			$url = add_query_arg(array('page' => $page), admin_url('admin.php'));
		} else {
			$url = add_query_arg(array('page' => 'ecalypse-rental'), admin_url('admin.php'));
		}

		return $url;
	}

	public static function set_flash_msg($status = 'info', $msg = NULL) {
		EcalypseRentalSession::$session['ecalypse_rental_flash_msg'] = array('status' => $status, 'msg' => $msg);
		return true;
	}

	public static function display_page() {
		global $wpdb;
		$arr = array('ecalypse-rental-fleet', 'ecalypse-rental-extras', 'ecalypse-rental-branches', 'ecalypse-rental-pricing',
			'ecalypse-rental-booking', 'ecalypse-rental-translations', 'ecalypse-rental-settings', 'ecalypse-rental-newsletter', 'ecalypse-rental-fleet-parameters', 'ecalypse-rental-rencato-connector');

		// Branches
		if ($_GET['page'] == 'ecalypse-rental-fleet') {
			$fleet = self::get_fleet();
			$fleet_by_id = array();
			foreach ($fleet as $f) {
				$fleet_by_id[$f->id_fleet] = $f->name;
			}
			$tpl = array('fleet' => $fleet,
				'vehicle_categories' => self::get_vehicle_categories(),
				'extras' => self::get_extras(),
				'branches' => self::get_branches(),
				'pricing' => self::get_pricing('p.`name` ASC', 2),
				'params' => self::get_fleet_parameters(true),
				'fleet_by_id' => $fleet_by_id,
				'params_values' => array()
				);

			if (isset($_GET['edit']) && !empty($_GET['edit'])) {
				$tpl['detail'] = self::get_fleet_detail((int) $_GET['edit']);
				$tpl['params_values'] = self::get_fleet_parameter_values((int) $_GET['edit']);
				$tpl['edit'] = true;

				$all_additional_parameters = array();
				$sql = $wpdb->get_results('SELECT `additional_parameters` FROM `' . EcalypseRental::$db['fleet']);
				foreach ($sql as $s) {
					$params = unserialize($s->additional_parameters);
					if (!empty($params)) {
						foreach ($params as $lng => $p) {
							if (!is_array($p)) {
								continue;
							}
							if (!isset($all_additional_parameters[$lng])) {
								$all_additional_parameters[$lng] = array();
							}
							foreach ($p as $pp) {
								if (!isset($pp['name'])) {
									continue;
								}
								$all_additional_parameters[$lng][$pp['name']] = $pp['name'];
							}
						}
					}
				}
				
				$tpl['all_additional_parameters'] = $all_additional_parameters;
			}

			EcalypseRental::view($_GET['page'], $tpl);
		} elseif ($_GET['page'] == 'ecalypse-rental-extras') {

			$tpl = array('extras' => self::get_extras(),
				'pricing' => self::get_pricing('p.`name` ASC'));
			$tpl['edit'] = false;

			if (isset($_GET['edit']) && !empty($_GET['edit'])) {
				$tpl['detail'] = self::get_extras_detail((int) $_GET['edit']);
				$tpl['edit'] = true;
			}

			EcalypseRental::view($_GET['page'], $tpl);
		} elseif ($_GET['page'] == 'ecalypse-rental-branches') {

			$tpl = array('branches' => self::get_branches());
			$tpl['edit'] = false;

			if (isset($_GET['edit']) && !empty($_GET['edit'])) {
				$tpl['detail'] = self::get_branch_detail((int) $_GET['edit']);
				$tpl['edit'] = true;
			}

			EcalypseRental::view($_GET['page'], $tpl);
		} elseif ($_GET['page'] == 'ecalypse-rental-pricing') {
			if (is_plugin_active( 'ecalypse-rental-american-pricing/ecalypse-rental-american-pricing.php' )) {
				EcalypseRental_American_Pricing_admin::admin_pricing();
				return;
			}
			$tpl = array('pricing' => self::get_pricing());
			$tpl['edit'] = false;

			if (isset($_GET['edit']) && !empty($_GET['edit'])) {
				$tpl['detail'] = self::get_pricing_detail((int) $_GET['edit']);
				$tpl['edit'] = true;
			}
			EcalypseRental::view($_GET['page'], $tpl);			
		} elseif ($_GET['page'] == 'ecalypse-rental-booking') {

			$tpl = array('booking' => self::get_booking(),
				'branches' => self::get_branches(),
				'fleet' => self::get_fleet());
			$tpl['edit'] = false;
			
			$vehicle_names = array();
			foreach ($tpl['fleet'] as $v) {
				$vehicle_names[$v->id_fleet] = $v->name;
			}
			$tpl['vehicle_names'] = $vehicle_names;

			if (isset($_GET['edit']) && !empty($_GET['edit'])) {
				$tpl['detail'] = self::get_booking_detail((int) $_GET['edit']);
				$tpl['edit'] = true;
			}

			EcalypseRental::view($_GET['page'], $tpl);
		} elseif ($_GET['page'] == 'ecalypse-rental-translations') {

			include dirname(realpath(__FILE__)) . '/languages.php';
			$tpl = array('languages' => $ecalypse_rental_languages);

			if (isset($_GET['language']) && !empty($_GET['language'])) {
				$tpl['translations_theme'] = self::get_theme_translations($_GET['language']);
			}

			EcalypseRental::view($_GET['page'], $tpl);
		} elseif ($_GET['page'] == 'ecalypse-rental-settings') {

			$tpl = array('vehicle_categories' => self::get_vehicle_categories(),
				'pricing' => self::get_pricing('p.`name` ASC'));
			EcalypseRental::view($_GET['page'], $tpl);
		} elseif ($_GET['page'] == 'ecalypse-rental-rencato-connector') {
			$tpl = array('vehicle_categories' => self::get_vehicle_categories(),
				'pricing' => self::get_pricing('p.`name` ASC'));
			EcalypseRental::view($_GET['page'], $tpl);
		} elseif ($_GET['page'] == 'ecalypse-rental-newsletter') {

			$tpl = array('newsletter' => self::get_newsletter());
			EcalypseRental::view($_GET['page'], $tpl);
		} elseif ($_GET['page'] == 'ecalypse-rental-fleet-parameters') {
			
			$tpl = array('params' => self::get_fleet_parameters(), 'types' => self::$fleet_parameter_types);
			
			if (isset($_GET['edit']) && !empty($_GET['edit'])) {
				$tpl['detail'] = self::get_fleet_parameter_detail((int) $_GET['edit']);
				$tpl['edit'] = true;
			}
			
			EcalypseRental::view($_GET['page'], $tpl);
		} else {

			$tpl = array('quick_info' => self::get_quick_info());

			if (isset($_GET['deleted'])) {
				$tpl['deleted'] = self::get_deleted_items();
			}

			EcalypseRental::view('ecalypse-rental', $tpl);
		}
	}
	
	
	public static function get_fleet_parameters($only_active = false) {
		global $wpdb;

		try {

			$params = $wpdb->get_results('SELECT *
												 FROM `'.$wpdb->prefix.'ecalypse_rental_fleet_parameters`'.($only_active ? ' WHERE `active` = 1' : '')
										);
			return $params;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public static function get_country_list() {
		return EcalypseRental::get_country_list();
	}

	public static function get_day_name($day) {
		return EcalypseRental::get_day_name($day);
	}

	public static function export_language() {
		global $wpdb;

		$preklad = $wpdb->get_results($wpdb->prepare('SELECT `original`, `translation` FROM ' . EcalypseRental::$db['translations'] . ' WHERE `lang` = %s GROUP BY `original`', sanitize_text_field($_POST['language'])), ARRAY_A);

		$file = json_encode($preklad);

		header('Content-Type: text/plain');
		header("Content-Transfer-Encoding: Binary");
		header('Pragma: no-cache');
		header("Content-disposition: attachment; filename=\"ecalypse_rental_language_" . sanitize_text_field($_POST['language']) . ".clng\"");
		echo $file;
		exit;
	}

	public static function import_language() {
		global $wpdb;

		try {
			if (!isset($_FILES['input_file']) || empty($_FILES['input_file']) || empty($_FILES['input_file']['tmp_name'])) {
				throw new Exception(__('Input language file is required.', 'ecalypse-rental'));
			}

			if (substr($_FILES['input_file']['name'], -4) != 'clng') {
				throw new Exception(__('Only ecalypse-rental language files are accepted.', 'ecalypse-rental'));
			}

			if (!function_exists('wp_handle_upload')) {
				require_once(ABSPATH . 'wp-admin/includes/file.php');
			}

			$uploadedfile = $_FILES['input_file'];
			$upload_overrides = array('test_form' => false, 'mimes' => array('clng' => 'text/plain'));
			$movefile = wp_handle_upload($uploadedfile, $upload_overrides);

			if ($movefile) {
				$lng_file = $movefile['file'];
			} else {
				throw new Exception(__('Upload error.', 'ecalypse-rental'));
			}
			$json = file_get_contents($lng_file);
			$lng_array = json_decode($json);

			if (!is_array($lng_array) || empty($lng_array)) {
				throw new Exception(__('Language file error.', 'ecalypse-rental'));
			}

			foreach ($lng_array as $lng_line) {
				$lng_line = (array) $lng_line;

				if (trim($lng_line['original']) == '' || trim($lng_line['translation']) == '') {
					continue;
				}

				$line = $wpdb->query($wpdb->prepare('INSERT INTO `' . EcalypseRental::$db['translations'] . '`
					SET `lang` = %s,
						`original` = %s,
						`translation` = %s
						ON DUPLICATE KEY UPDATE `translation` = %s
						', sanitize_text_field(trim($_POST['language'])), wp_kses_post(trim($lng_line['original'])), wp_kses_post(trim($lng_line['translation'])), wp_kses_post(trim($lng_line['translation']))
				));
			}

			unlink($lng_file);
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Add Branch from $_POST
	 */
	public function add_branch() {
		global $wpdb;

		try {

			$edit = false;
			if (isset($_POST['id_branch']) && !empty($_POST['id_branch'])) {
				$edit = true;
				$id_branch = (int) $_POST['id_branch'];
				if ($id_branch <= 0) {
					throw new Exception(__('Invalid Branch ID', 'ecalypse-rental'));
				}
			}

			// Save uploaded picture
			$picture_filename = (isset($_POST['current_picture']) ? sanitize_text_field($_POST['current_picture']) : NULL);
			if (isset($_FILES['picture']) && !empty($_FILES['picture']['tmp_name'])) {
				if (!function_exists('wp_handle_upload')) {
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}
				$uploadedfile = $_FILES['picture'];
				$upload_overrides = array('test_form' => false);
				$movefile = wp_handle_upload($uploadedfile, $upload_overrides);
				if ($movefile) {
					$picture_filename = $movefile['url'];
				}
			}
			if (isset($_POST['delete_picture']) && $_POST['delete_picture'] == 1) {
				$picture_filename = '';
			}
			
			$taxes = array();
			if (isset($_POST['branch_tax'])) {
				foreach ($_POST['branch_tax'] as $k => $v) {
					if ($k == 0) {
						continue;
					}
					if ((float)$v['tax'] == 0) {
						continue;
					}
					$taxes[$k] = $v;
				}
			}
			
			
			// Save Branch to DB
			$arr = array('name' => stripslashes_deep(sanitize_text_field($_POST['name'])),
				'country' => sanitize_text_field($_POST['country']),
				'state' => sanitize_text_field($_POST['state']),
				'city' => stripslashes_deep(sanitize_text_field($_POST['city'])),
				'zip' => sanitize_text_field($_POST['zip']),
				'street' => stripslashes_deep(sanitize_text_field($_POST['street'])),
				'email' => sanitize_email($_POST['email']),
				'phone' => sanitize_text_field($_POST['phone']),
				'gps' => sanitize_text_field($_POST['gps']),
				'description' => implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['description'] ) ) ),
				'picture' => $picture_filename,
				'active' => (int)$_POST['active'],
				'outside_price' => 0,
				'show_location' => (int)$_POST['show_location'],
				'is_default' => (int) $_POST['is_default'],
				'enter_hours' => self::sanitize_and_serialize($_POST['enter_hours']),
				'return_hours' => self::sanitize_and_serialize($_POST['return_hours']),
				'branch_tax' => self::sanitize_and_serialize($taxes),
				'specific_times' => isset($_POST['specific_times']) ? 1 : 0,
				'bid' => sanitize_text_field($_POST['bid']),
				'translations' => self::sanitize_and_serialize($_POST['translations']),
			);

			if ($edit == true) {

				// Update Branch
				$arr['updated'] = Date('Y-m-d H:i:s');
				$wpdb->update(EcalypseRental::$db['branch'], $arr, array('id_branch' => $id_branch));

				// Delete previous Branch hours
				$wpdb->delete(EcalypseRental::$db['branch_hours'], array('id_branch' => $id_branch), array('%d'));
			} else {

				// Add Branch
				$wpdb->insert(EcalypseRental::$db['branch'], $arr);
				$id_branch = $wpdb->insert_id;
			}

			if ((int) $_POST['is_default'] == 1) {
				// set this branch as default - set other branches to not default
				$wpdb->query('UPDATE ' . EcalypseRental::$db['branch'] . ' SET `is_default` = 0 WHERE `id_branch` <> ' . (int) $id_branch);
			}

			// Save Business Hours to DB
			if (!empty($_POST['hours']['from'])) {
				foreach ($_POST['hours']['from'] as $key => $val) {
					$from = $val;
					$to = $_POST['hours']['to'][$key];
					$from_2 = !empty($_POST['hours']['from_2'][$key]) ? sanitize_text_field($_POST['hours']['from_2'][$key]) : null;
					$to_2 = !empty($_POST['hours']['to_2'][$key]) ? sanitize_text_field($_POST['hours']['to_2'][$key]) : null;

					if (!empty($from) && !empty($to)) {

						$day = $key + 1;
						$arr = array('id_branch' => $id_branch,
							'day' => (int)$day,
							'hours_from' => sanitize_text_field($from) . ':00',
							'hours_to' => sanitize_text_field($to) . ':00',
							'hours_from_2' => $from_2,
							'hours_to_2' => $to_2);

						$wpdb->insert(EcalypseRental::$db['branch_hours'], $arr);
					}
				}
			}
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	private static function get_branch($id, $branches) {
		if (empty($branches)) {
			return false;
		}
		foreach ($branches as $b) {
			if ($b->id_branch == $id) {
				return $b;
			}
		}
		return false;
	}

	/**
	 * 	Get branches
	 */
	public function get_branches() {
		global $wpdb;

		try {

			$where = '`deleted` IS NULL';
			$order = '`ordering` DESC';

			if (isset($_GET['deleted'])) {
				$where = '`deleted` IS NOT NULL';
				$order = '`deleted` DESC';
			}

			$branches = $wpdb->get_results('SELECT * FROM `' . EcalypseRental::$db['branch'] . '` WHERE ' . $where . ' ORDER BY ' . $order);
			
			if ($branches && !empty($branches)) {
				foreach ($branches as $key => $val) {

					$branches[$key]->hours = $wpdb->get_results(
						$wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['branch_hours'] . '`
																								 	 WHERE `id_branch` = %d ORDER BY `day` ASC', $val->id_branch));
				}
			}

			return $branches;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function get_branch_detail($id_branch) {
		global $wpdb;

		try {

			$branches = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['branch'] . '` WHERE `id_branch` = %d', $id_branch));
			$hours = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['branch_hours'] . '` WHERE `id_branch` = %d', $id_branch));
			$branches->hours = array();
			
			$branches->enter_hours = unserialize($branches->enter_hours);
			$branches->return_hours = unserialize($branches->return_hours);
			
			if ($hours && !empty($hours)) {
				foreach ($hours as $key => $val) {
					$branches->hours[$val->day] = array('hours_from' => substr($val->hours_from, 0, 5),
						'hours_to' => substr($val->hours_to, 0, 5));
					if (isset($val->hours_from_2) && $val->hours_from_2 != '00:00:00') {
						$branches->hours[$val->day]['hours_from_2'] = substr($val->hours_from_2, 0, 5);
						$branches->hours[$val->day]['hours_to_2'] = substr($val->hours_to_2, 0, 5);
					}
				}
			}

			return $branches;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function copy_branch($id_branch) {
		global $wpdb;

		try {

			$branches = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['branch'] . '` WHERE `id_branch` = %d', $id_branch));
			$hours = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['branch_hours'] . '` WHERE `id_branch` = %d', $id_branch));

			// Save Branch to DB
			$arr = array('name' => $branches->name . __(' (copy)', 'ecalypse-rental'),
				'country' => $branches->country,
				'state' => $branches->state,
				'city' => $branches->city,
				'zip' => $branches->zip,
				'street' => $branches->street,
				'email' => $branches->email,
				'phone' => $branches->phone,
				'description' => $branches->description,
				'picture' => $branches->picture,
				'active' => $branches->active,
				'enter_hours' => $branches->enter_hours,
				'return_hours' => $branches->return_hours,
				'specific_times' => $branches->specific_times,
				'bid' => $branches->bid
			);
			$wpdb->insert(EcalypseRental::$db['branch'], $arr);
			$id_branch = $wpdb->insert_id;

			// Save Business Hours to DB
			if (!empty($hours)) {
				foreach ($hours as $key => $val) {
					$arr = array('id_branch' => $id_branch,
						'day' => $val->day,
						'hours_from' => $val->hours_from,
						'hours_to' => $val->hours_to);
					$wpdb->insert(EcalypseRental::$db['branch_hours'], $arr);
				}
			}

			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function delete_branch($id_branch) {
		global $wpdb;

		try {

			$arr = array('deleted' => Date('Y-m-d H:i:s'));
			$wpdb->update(EcalypseRental::$db['branch'], $arr, array('id_branch' => $id_branch), array('%s'));
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function restore_branch($id_branch) {
		global $wpdb;

		try {

			$wpdb->query('UPDATE ' . EcalypseRental::$db['branch'] . ' SET `deleted` = NULL WHERE `id_branch` = ' . (int) $id_branch);
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function ajax_save_branch_order() {
		check_ajax_referer( 'ecalypse_rental_save_branch_order' );
		global $wpdb;
		$r = count($_POST['ordering']);
		foreach ($_POST['ordering'] as $o) {
			$wpdb->query($wpdb->prepare('UPDATE ' . EcalypseRental::$db['branch'] . ' SET `ordering` = %d WHERE `id_branch` = %d LIMIT 1', (int)$r, (int) $o));
			$r--;
		}
		echo '1';
		exit;
	}

	/**
	 * 	EXTRAS
	 */
	public function add_extras() {
		global $wpdb;

		try {

			$edit = false;
			if (isset($_POST['id_extras']) && !empty($_POST['id_extras'])) {
				$edit = true;
				$id_extras = (int) $_POST['id_extras'];
				if ($id_extras <= 0) {
					throw new Exception(__('Invalid Extras ID', 'ecalypse-rental'));
				}
			}

			// Save uploaded picture
			$picture_filename = (isset($_POST['current_picture']) ? sanitize_text_field($_POST['current_picture']) : NULL);
			if (isset($_FILES['picture']) && !empty($_FILES['picture']['tmp_name'])) {
				if (!function_exists('wp_handle_upload')) {
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}
				$uploadedfile = $_FILES['picture'];
				$upload_overrides = array('test_form' => false);
				$movefile = wp_handle_upload($uploadedfile, $upload_overrides);
				if ($movefile) {
					$picture_filename = $movefile['url'];
				}
			}

			// Save Extras to DB
			$arr = array('name' => sanitize_text_field($_POST['name']),
				'name_admin' => sanitize_text_field($_POST['name_admin']),
				'name_translations' => serialize($_POST['name_translations']),
				'description' => implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['description'] ) ) ),
				'description_translations' => self::sanitize_and_serialize($_POST['description_translations']),
				'global_pricing_scheme' => (int)$_POST['global_pricing_scheme'],
				'internal_id' => sanitize_text_field($_POST['internal_id']),
				'max_additional_drivers' => (int)$_POST['max_additional_drivers'],
				'mandatory' => (isset($_POST['mandatory']) ? (int) $_POST['mandatory'] : 0),
				'picture' => sanitize_text_field($picture_filename)
			);

			if ($edit == true) {

				// Update Extras
				$arr['updated'] = Date('Y-m-d H:i:s');
				$wpdb->update(EcalypseRental::$db['extras'], $arr, array('id_extras' => $id_extras));

			} else {

				// Add Extras
				$wpdb->insert(EcalypseRental::$db['extras'], $arr);
				$id_extras = $wpdb->insert_id;
			}
			
			

			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get extras
	 */
	public function get_extras() {
		global $wpdb;

		try {

			$where = 'e.`deleted` IS NULL';
			$order = 'e.`id_extras` DESC';

			if (isset($_GET['deleted'])) {
				$where = 'e.`deleted` IS NOT NULL';
				$order = 'e.`deleted` DESC';
			}

			$extras = $wpdb->get_results('SELECT e.*, p.`name` as `pricing_name`, p.`type` as `pricing_type`,
																			(SELECT COUNT(*) FROM `' . EcalypseRental::$db['extras_pricing'] . '` pr WHERE pr.`id_extras` = e.`id_extras`) as `pricing_count`
																		FROM `' . EcalypseRental::$db['extras'] . '` e
																		LEFT JOIN `' . EcalypseRental::$db['pricing'] . '` p ON p.`id_pricing` = e.`global_pricing_scheme`
																		WHERE ' . $where . ' ORDER BY ' . $order);

			return $extras;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function get_extras_detail($id_extras) {
		global $wpdb;

		try {

			$extras = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['extras'] . '` WHERE `id_extras` = %d', $id_extras));

			// Pricing schemes
			$extras->pricing = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['extras_pricing'] . '` WHERE `id_extras` = %d ORDER BY `priority` ASC', $id_extras));

			return $extras;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function copy_extras($id_extras) {
		global $wpdb;

		try {

			$extras = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['extras'] . '` WHERE `id_extras` = %d', $id_extras));

			// Save Extras to DB
			$arr = array('name' => $extras->name . ' (copy)',
				'description' => $extras->description,
				'global_pricing_scheme' => $extras->global_pricing_scheme,
				'internal_id' => $extras->internal_id,
				'max_additional_drivers' => $extras->max_additional_drivers,
				'picture' => $extras->picture
			);

			$wpdb->insert(EcalypseRental::$db['extras'], $arr);
			
			

			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function delete_extras($id_extras) {
		global $wpdb;

		try {

			$arr = array('deleted' => Date('Y-m-d H:i:s'));
			$wpdb->update(EcalypseRental::$db['extras'], $arr, array('id_extras' => $id_extras), array('%s'));
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function delete_extras_from_db($id_extras) {
		global $wpdb;

		try {
			$wpdb->query('DELETE FROM ' . EcalypseRental::$db['extras'] . ' WHERE `id_extras` = ' . (int) $id_extras . ' LIMIT 1');
			$wpdb->query('DELETE FROM ' . EcalypseRental::$db['fleet_extras'] . ' WHERE `id_extras` = ' . (int) $id_extras);	
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function restore_extras($id_extras) {
		global $wpdb;

		try {

			$wpdb->query('UPDATE ' . EcalypseRental::$db['extras'] . ' SET `deleted` = NULL WHERE `id_extras` = ' . (int) $id_extras);
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	/**
	 * 	FLEET PARAMETERS
	 */
	public function add_fleet_parameter() {
		global $wpdb;

		try {

			$edit = false;
			if (isset($_POST['id_fleet_parameter']) && !empty($_POST['id_fleet_parameter'])) {
				$edit = true;
				$id_fleet_parameter = (int) $_POST['id_fleet_parameter'];
				if ($id_fleet_parameter <= 0) {
					throw new Exception(__('Invalid Fleet Parameter ID', 'ecalypse-rental'));
				}
			}
			
			if (trim($_POST['name']['gb'] == '')) {
				throw new Exception(__('Name of parameter in english must be set.', 'ecalypse-rental'));
			}

			if (isset($_POST['values']) && !empty($_POST['values'])) {
				$values = array();
				$langs = array();
				foreach ($_POST['values'] as $lng => $param) {
					$langs[] = sanitize_text_field($lng);
					$values[sanitize_text_field($lng)] = array();
				}
				foreach ($_POST['values'] as $lng => $params) {					
					foreach ($params as $id => $param) {
						if (trim($param) == '') {
							// if is empty in all languages then unset it
							$empty = true;
							foreach ($langs as $l) {
								if (isset($_POST['values'][$l][$id]) && trim($_POST['values'][$l][$id]) != '') {
									$empty = false;
									break;
								}
							}
							if ($empty) {
								unset($_POST['values'][$lng][$id]);
								continue;
							}
						}
						$values[sanitize_text_field($lng)][$id] = $param;
					}
				}
				$values_array = $values['gb'];
				$values = self::sanitize_and_serialize($values);
			} else {
				$values = null;
				$values_array = array();
			}
			

			// Save Fleet to DB
			$arr = array('name' => self::sanitize_and_serialize($_POST['name']),
				'values' => $values,
				'type' => (int)$_POST['type'],
				'range_from' => (int)$_POST['range_from'],
				'range_to' => (int)$_POST['range_to'],
				'active' => (int)$_POST['active'],
				'filter' => (int)$_POST['filter']
			);

			if ($edit == true) {
				// Update Fleet Parameter
				unset($arr['type']);
				
				// get old parameter values
				$fleet_parameter = $wpdb->get_row($wpdb->prepare('SELECT * FROM `'.$wpdb->prefix.'ecalypse_rental_fleet_parameters` WHERE `id_fleet_parameter` = %d LIMIT 1', $id_fleet_parameter));
				// check if any values was deleted
				$old_values = $fleet_parameter->values != '' ? unserialize($fleet_parameter->values) : array();
				if (is_array($old_values) && isset($old_values['gb'])) {
					$to_delete = array();
					foreach ($old_values['gb'] as $k => $v) {
						if (!isset($values_array[$k])) {
							$to_delete[] = (int)$k;
						}
					}
					if (count($to_delete) > 0) {
						$wpdb->query($wpdb->prepare('DELETE FROM `'.$wpdb->prefix.'ecalypse_rental_fleet_parameters_values` WHERE `fleet_parameters_id` = %d AND `value` IN ('.implode(',', $to_delete).')', $id_fleet_parameter));
					}
				}
				
				$wpdb->update($wpdb->prefix."ecalypse_rental_fleet_parameters", $arr, array('id_fleet_parameter' => $id_fleet_parameter));
			} else {

				// Add Fleet Parameter
				$wpdb->insert($wpdb->prefix."ecalypse_rental_fleet_parameters", $arr);
				$id_fleet_parameter = $wpdb->insert_id;
			}
			
			

			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function get_fleet_parameter_values($id_fleet) {
		global $wpdb;
		$return = array();
		$param_values = $wpdb->get_results($wpdb->prepare('SELECT * FROM `'.$wpdb->prefix.'ecalypse_rental_fleet_parameters_values` WHERE `fleet_id` = %d', $id_fleet));
		foreach ($param_values as $p) {
			$return[$p->fleet_parameters_id] = $p->value;
		}
		return $return;
	}
	
	public function get_fleet_parameter_detail($id_fleet_parameter) {
		global $wpdb;

		try {

			$fleet_parameter = $wpdb->get_row($wpdb->prepare('SELECT *
																							FROM `'.$wpdb->prefix.'ecalypse_rental_fleet_parameters` WHERE `id_fleet_parameter` = %d', $id_fleet_parameter));

			return $fleet_parameter;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function copy_fleet_parameter($id_fleet_parameter) {
		global $wpdb;

		try {

			$param = $wpdb->get_row($wpdb->prepare('SELECT * FROM `'.$wpdb->prefix.'ecalypse_rental_fleet_parameters` WHERE `id_fleet_parameter` = %d', $id_fleet_parameter));

			$name = unserialize($param->name);
			$name['gb'] = $name['gb'].' (copy)';
			// Save Fleet Parameter to DB
			$arr = array('name' => serialize($name),
				'values' => $param->values,
				'type' => $param->type,
				'range_from' => $param->range_from,
				'range_to' => $param->range_to,
				'active' => $param->active
			);

			$wpdb->insert($wpdb->prefix.'ecalypse_rental_fleet_parameters', $arr);
			
			

			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function delete_fleet_parameter($id_fleet_parameter) {
		global $wpdb;

		try {

			$wpdb->query('DELETE FROM `'.$wpdb->prefix.'ecalypse_rental_fleet_parameters` WHERE `id_fleet_parameter` = ' . (int) $id_fleet_parameter . ' LIMIT 1');
			$wpdb->query('DELETE FROM `'.$wpdb->prefix.'ecalypse_rental_fleet_parameters_values` WHERE `fleet_parameters_id` = ' . (int) $id_fleet_parameter);
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	FLEET
	 */
	public function add_fleet() {
		global $wpdb;

		try {

			$edit = false;
			if (isset($_POST['id_fleet']) && !empty($_POST['id_fleet'])) {
				$edit = true;
				$id_fleet = (int) $_POST['id_fleet'];
				if ($id_fleet <= 0) {
					throw new Exception(__('Invalid Fleet ID', 'ecalypse-rental'));
				}
			}

			// Save uploaded picture
			$picture_filename = (isset($_POST['current_picture']) ? sanitize_text_field($_POST['current_picture']) : NULL);
			if (isset($_FILES['picture']) && !empty($_FILES['picture']['tmp_name'])) {
				if (!function_exists('wp_handle_upload')) {
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}
				$uploadedfile = $_FILES['picture'];
				$upload_overrides = array('test_form' => false);
				$movefile = wp_handle_upload($uploadedfile, $upload_overrides);
				if ($movefile) {
					$picture_filename = $movefile['url'];
				}
			}
			
			$similar_cars = array();
			if (isset($_POST['similar_cars']) && is_array($_POST['similar_cars'])) {
				foreach ($_POST['similar_cars'] as $carId) {
					$similar_cars[(int)$carId] = (int)$carId;
				}
				$similar_cars = self::sanitize_and_serialize($similar_cars);
			} else {
				$similar_cars = null;
			}

			if (isset($_POST['additional-pictures']) && !empty($_POST['additional-pictures'])) {
				$additionalPictures = self::sanitize_and_serialize($_POST['additional-pictures']);
			} else {
				$additionalPictures = null;
			}

			if (isset($_POST['additional_parameters']) && !empty($_POST['additional_parameters'])) {
				$parameters = array();
				$langs = array();
				foreach ($_POST['additional_parameters'] as $lng => $param) {
					$langs[] = sanitize_text_field($lng);
					$parameters[sanitize_text_field($lng)] = array();
				}
				foreach ($_POST['additional_parameters'] as $lng => $params) {
					$i = 1;
					foreach ($params as $id => $param) {
						if (trim($param['name']) == '') {
							// if is empty in all languages then unset it
							$empty = true;
							foreach ($langs as $l) {
								if (isset($_POST['additional_parameters'][$l][$id]['name']) && trim($_POST['additional_parameters'][$l][$id]['name']) != '') {
									$empty = false;
									break;
								}
							}
							if ($empty) {
								unset($_POST['additional_parameters'][$lng][$id]);
								continue;
							}
						}
						$parameters[sanitize_text_field($lng)][$i] = $param;
						$i++;
					}
				}
				$additionalParameters = self::sanitize_and_serialize($parameters);
			} else {
				$additionalParameters = null;
			}

			// Save Fleet to DB
			$arr = array('name' => sanitize_text_field($_POST['name']),
				'id_category' => (int)$_POST['id_category'],
				'id_branch' => (int)$_POST['id_branch'],
				'global_pricing_scheme' => (int)$_POST['global_pricing_scheme'],
				'min_rental_time' => (int)$_POST['min_rental_time'],
				'seats' => (int)$_POST['seats'],
				'doors' => (int)$_POST['doors'],
				'luggage' => (int)$_POST['luggage'],
				'transmission' => (int)$_POST['transmission'],
				'free_distance' => (int)$_POST['free_distance'],
				'free_distance_hour' => (isset($_POST['free_distance_hour']) ? (int)$_POST['free_distance_hour'] : 0),
				'ac' => (int)$_POST['ac'],
				'fuel' => (int)$_POST['fuel'],
				'number_vehicles' => (int)$_POST['number_vehicles'],
				'consumption' => (float)$_POST['consumption'],
				'deposit' => (float)$_POST['deposit'],
				'license' => sanitize_text_field($_POST['license']),
				'vin' => sanitize_text_field($_POST['vin']),
				'internal_id' => sanitize_text_field($_POST['internal_id']),
				'class_code' => sanitize_text_field($_POST['class_code']),
				'price_from' => (float)$_POST['price_from'],
				'description' => (is_array($_POST['description']) ? self::sanitize_and_serialize($_POST['description']) : implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['description'] ) ) )),
				'picture' => sanitize_text_field($picture_filename),
				'additional_pictures' => $additionalPictures,
				'additional_parameters' => $additionalParameters,
				'similar_cars' => $similar_cars
			);

			if ($edit == true) {

				// Update Fleet
				$arr['updated'] = Date('Y-m-d H:i:s');
				$wpdb->update(EcalypseRental::$db['fleet'], $arr, array('id_fleet' => $id_fleet));

				// Delete extras
				$wpdb->delete(EcalypseRental::$db['fleet_extras'], array('id_fleet' => $id_fleet), array('%d'));
				
				// Delete previous Fleet parameters
				$wpdb->delete($wpdb->prefix.'ecalypse_rental_fleet_parameters_values', array('fleet_id' => $id_fleet), array('%d'));
			} else {

				// Add Fleet
				$wpdb->insert(EcalypseRental::$db['fleet'], $arr);
				$id_fleet = $wpdb->insert_id;
			}

			// Add extras
			if (isset($_POST['extras']) && !empty($_POST['extras'])) {
				foreach ($_POST['extras'] as $kD => $vD) {
					$wpdb->insert(EcalypseRental::$db['fleet_extras'], array('id_fleet' => $id_fleet, 'id_extras' => (int)$vD));
				}
			}
			
			// save custom parameters
			if (isset($_POST['custom_parameters']) && !empty($_POST['custom_parameters'])) {
				foreach ($_POST['custom_parameters'] as $key => $value) {
					if (trim($value) == '' || (int)$value < 1) {
						continue;
					}
					$sql = 'INSERT INTO `' . $wpdb->prefix.'ecalypse_rental_fleet_parameters_values` (`fleet_id`, `fleet_parameters_id`, `value`)
									VALUES (%d, %d, %d)';
					$wpdb->query($wpdb->prepare($sql, $id_fleet, (int)$key, (int)$value));
				}
			}
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * return first non empty parameter name in languages
	 * @param type $i
	 * @param type $lng
	 */
	public static function fleet_placeholder_param($i, $additional_parameters) {
		if (empty(self::$available_languages)) {
			$available_languages_all = unserialize(get_option('ecalypse_rental_available_languages'));
			$available_languages = array('gb');
			foreach ($available_languages_all as $l) {
				$available_languages[] = $l['country-www'];
			}
			self::$available_languages = $available_languages;
		}

		self::$additional_parameters = $additional_parameters;
		if (self::$available_languages && !empty(self::$available_languages)) {
			foreach (self::$available_languages as $key => $val) {
				if (isset(self::$additional_parameters[$val][$i]) && isset(self::$additional_parameters[$val][$i]['name']) && self::$additional_parameters[$val][$i]['name'] != '') {
					return self::$additional_parameters[$val][$i]['name'];
				}
			}
		}
		return '';
	}

	/**
	 * 	Get fleet
	 */
	public function get_fleet() {
		global $wpdb;

		try {

			$where = 'f.`deleted` IS NULL';
			$order = 'f.`ordering` DESC';

			if (isset($_GET['deleted'])) {
				$where = 'f.`deleted` IS NOT NULL';
				$order = 'f.`deleted` DESC';
			}

			$fleet = $wpdb->get_results('SELECT f.*, p.`name` as `pricing_name`, p.`type` as `pricing_type`,
																		 (SELECT GROUP_CONCAT(`id_extras`) FROM `' . EcalypseRental::$db['fleet_extras'] . '` fe WHERE fe.`id_fleet` = f.`id_fleet`) as `extras`,
																		 (SELECT `name` FROM `' . EcalypseRental::$db['branch'] . '` b WHERE b.`id_branch` = f.`id_branch`) as `branch_name`,
																		 (SELECT COUNT(*) FROM `' . EcalypseRental::$db['fleet_pricing'] . '` pr WHERE pr.`id_fleet` = f.`id_fleet`) as `pricing_count`
																	 FROM `' . EcalypseRental::$db['fleet'] . '` f
																	 LEFT JOIN `' . EcalypseRental::$db['pricing'] . '` p ON p.`id_pricing` = f.`global_pricing_scheme`
																	 WHERE ' . $where . ' ORDER BY ' . $order);
			return $fleet;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function get_fleet_detail($id_fleet) {
		global $wpdb;

		try {

			$fleet = $wpdb->get_row($wpdb->prepare('SELECT f.*,
																								(SELECT GROUP_CONCAT(`id_extras`) FROM `' . EcalypseRental::$db['fleet_extras'] . '` fe WHERE fe.`id_fleet` = f.`id_fleet`) as `extras`
																							FROM `' . EcalypseRental::$db['fleet'] . '` f WHERE f.`id_fleet` = %d', $id_fleet));

			// Pricing schemes
			$fleet->pricing = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['fleet_pricing'] . '` WHERE `id_fleet` = %d ORDER BY `priority` ASC', $id_fleet));

			return $fleet;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function copy_fleet($id_fleet) {
		global $wpdb;

		try {

			$fleet = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['fleet'] . '` WHERE `id_fleet` = %d', $id_fleet));

			// Save Fleet to DB
			$arr = array('name' => $fleet->name . __(' (copy)', 'ecalypse-rental'),
				'id_category' => $fleet->id_category,
				'id_branch' => $fleet->id_branch,
				'global_pricing_scheme' => $fleet->global_pricing_scheme,
				'min_rental_time' => $fleet->min_rental_time,
				'seats' => $fleet->seats,
				'doors' => $fleet->doors,
				'luggage' => $fleet->luggage,
				'transmission' => $fleet->transmission,
				'free_distance' => $fleet->free_distance,
				'free_distance_hour' => $fleet->free_distance_hour,
				'ac' => $fleet->ac,
				'fuel' => $fleet->fuel,
				'number_vehicles' => $fleet->number_vehicles,
				'consumption' => $fleet->consumption,
				'deposit' => $fleet->deposit,
				'license' => $fleet->license,
				'vin' => $fleet->vin,
				'internal_id' => $fleet->internal_id,
				'class_code' => $fleet->class_code,
				'description' => $fleet->description,
				'picture' => $fleet->picture,
				'price_from' => $fleet->price_from,
				'additional_parameters' => $fleet->additional_parameters
			);

			$wpdb->insert(EcalypseRental::$db['fleet'], $arr);
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function delete_fleet($id_fleet) {
		global $wpdb;

		try {

			$arr = array('deleted' => Date('Y-m-d H:i:s'));
			$wpdb->update(EcalypseRental::$db['fleet'], $arr, array('id_fleet' => $id_fleet), array('%s'));
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function delete_fleet_from_db($id_fleet) {
		global $wpdb;

		try {

			$arr = array('deleted' => Date('Y-m-d H:i:s'));
			$wpdb->query('DELETE FROM ' . EcalypseRental::$db['fleet'] . ' WHERE `id_fleet` = ' . (int) $id_fleet . ' LIMIT 1');
			$wpdb->query('DELETE FROM ' . EcalypseRental::$db['fleet_extras'] . ' WHERE `id_fleet` = ' . (int) $id_fleet);
			$wpdb->query('DELETE FROM ' . EcalypseRental::$db['fleet_pricing'] . ' WHERE `id_fleet` = ' . (int) $id_fleet);
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function restore_fleet($id_fleet) {
		global $wpdb;

		try {

			$wpdb->query('UPDATE ' . EcalypseRental::$db['fleet'] . ' SET `deleted` = NULL WHERE `id_fleet` = ' . (int) $id_fleet);
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function ajax_save_fleet_order() {
		check_ajax_referer( 'ecalypse_rental_save_fleet_order' );
		global $wpdb;
		$r = count($_POST['ordering']);
		foreach ($_POST['ordering'] as $o) {
			$wpdb->query($wpdb->prepare('UPDATE ' . EcalypseRental::$db['fleet'] . ' SET `ordering` = %d WHERE `id_fleet` = %d LIMIT 1', $r, (int) $o));
			$r--;
		}
		echo '1';
		exit;
	}

	/**
	 * 	BOOKING
	 */
	public function add_booking() {
		global $wpdb;

		try {

			$edit = false;
			if (isset($_POST['id_booking']) && !empty($_POST['id_booking'])) {
				$edit = true;
				$id_booking = (int) $_POST['id_booking'];
				if ($id_booking <= 0) {
					throw new Exception(__('Invalid Booking ID', 'ecalypse-rental'));
				}
			}

			$enter_date = Date('Y-m-d H:i:s', strtotime($_POST['enter_date'] . ' ' . $_POST['enter_date_hour']));
			$return_date = Date('Y-m-d H:i:s', strtotime($_POST['return_date'] . ' ' . $_POST['return_date_hour']));
			
			$branches = self::get_branches();
			$enter_loc = self::get_branch($_POST['id_enter_branch'], $branches) ? self::get_branch($_POST['id_enter_branch'], $branches)->name : '';
			$return_loc = self::get_branch($_POST['id_return_branch'], $branches) ? self::get_branch($_POST['id_return_branch'], $branches)->name : '';

			// Save booking to DB
			$arr = array('first_name' => sanitize_text_field($_POST['first_name']),
				'last_name' => sanitize_text_field($_POST['last_name']),
				'email' => sanitize_text_field($_POST['email']),
				'phone' => sanitize_text_field($_POST['phone']),
				'street' => sanitize_text_field($_POST['street']),
				'city' => sanitize_text_field($_POST['city']),
				'zip' => sanitize_text_field($_POST['zip']),
				'country' => sanitize_text_field($_POST['country']),
				'company' => sanitize_text_field($_POST['company']),
				'vat' => sanitize_text_field($_POST['vat']),
				'flight' => sanitize_text_field($_POST['flight']),
				'license' => sanitize_text_field($_POST['license']),
				'id_card' => sanitize_text_field($_POST['id_card']),
				'enter_loc' => sanitize_text_field($enter_loc),
				'id_enter_branch' => (int)$_POST['id_enter_branch'],
				'enter_date' => $enter_date,
				'return_loc' => sanitize_text_field($return_loc),
				'id_return_branch' => (int)$_POST['id_return_branch'],
				'return_date' => $return_date,
				'payment_option' => sanitize_text_field($_POST['payment_option']),
				'comment' => implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST['comment'] ) ) ),
				'partner_code' => sanitize_text_field($_POST['partner_code']),
				'status' => (int) $_POST['status'],
				'paid_online' => (float) $_POST['paid_online'],
			);
	
			if ((int) $_POST['change_vehicle'] > 0) {

				$vehicle = EcalypseRental::get_vehicle_parameters((int) $_POST['change_vehicle']);
				$vehicle->consumption_metric = get_option('ecalypse_rental_consumption');
				$currency = get_option('ecalypse_rental_global_currency');
				$distance_metric = get_option('ecalypse_rental_distance_metric');

				$vehicle_arr = array(
					'vehicle' => $vehicle->name,
					'vehicle_id' => $vehicle->id_fleet,
					'vehicle_picture' => $vehicle->picture,
					'vehicle_ac' => $vehicle->ac,
					'vehicle_luggage' => $vehicle->luggage,
					'vehicle_seats' => $vehicle->seats,
					'vehicle_fuel' => $vehicle->fuel,
					'vehicle_consumption' => $vehicle->consumption,
					'vehicle_consumption_metric' => $vehicle->consumption_metric,
					'vehicle_transmission' => $vehicle->transmission,
					'vehicle_free_distance' => $vehicle->free_distance . ' ' . $distance_metric,
					'vehicle_deposit' => $vehicle->deposit . ' ' . $currency,
				);

				$arr = array_merge($arr, $vehicle_arr);
			}

			if ($edit == true) {

				// Update booking
				$arr['updated'] = Date('Y-m-d H:i:s');
				$wpdb->update(EcalypseRental::$db['booking'], $arr, array('id_booking' => $id_booking));
			
				// Delete drivers
				$wpdb->delete(EcalypseRental::$db['booking_drivers'], array('id_booking' => $id_booking), array('%d'));

				// Delete prices
				$wpdb->delete(EcalypseRental::$db['booking_prices'], array('id_booking' => $id_booking), array('%d'));
				
				if ((int) $_POST['change_vehicle'] > 0) {
					$wpdb->query('UPDATE `'. $wpdb->prefix . 'ecalypse_rental_booking_items` bi SET `vehicle_id` = '.(int) $_POST['change_vehicle'].' WHERE `id_booking` = '.$id_booking.' LIMIT 1');	
				}
			} else {

				// Add booking
				$id_order = EcalypseRental::generate_unique_order_id();
				$arr['id_order'] = $id_order;
				$arr['terms'] = 1;

				$wpdb->insert(EcalypseRental::$db['booking'], $arr);
				$id_booking = $wpdb->insert_id;
				
				$arr = array('id_booking' => $id_booking,
					 'vehicle_id' => $vehicle->id_fleet);
				$wpdb->insert($wpdb->prefix . 'ecalypse_rental_booking_items', $arr);
			}
			
			// Add drivers
			if ($_POST['drv'] && !empty($_POST['drv'])) {
				foreach ($_POST['drv']['email'] as $key => $val) {
					if (!empty($val) && !empty($_POST['drv']['first_name'][$key]) && !empty($_POST['drv']['last_name'][$key]) && !empty($_POST['drv']['phone'][$key])) {
						$arr = array('id_booking' => $id_booking,
							'first_name' => sanitize_text_field($_POST['drv']['first_name'][$key]),
							'last_name' => sanitize_text_field($_POST['drv']['last_name'][$key]),
							'email' => sanitize_email($val),
							'phone' => sanitize_text_field($_POST['drv']['phone'][$key]),
							'street' => isset($_POST['drv']['street'][$key]) ? sanitize_text_field($_POST['drv']['street'][$key]) : $_POST['drv']['street'][$key],
							'city' => isset($_POST['drv']['city'][$key]) ? sanitize_text_field($_POST['drv']['city'][$key]) : $_POST['drv']['city'][$key],
							'zip' => isset($_POST['drv']['zip'][$key]) ? sanitize_text_field($_POST['drv']['zip'][$key]) : '',
							'country' => isset($_POST['drv']['country'][$key]) ? sanitize_text_field($_POST['drv']['country'][$key]) : '',
							'license' => isset($_POST['drv']['license'][$key]) ? sanitize_text_field($_POST['drv']['license'][$key]) : '',
							'id_card' => isset($_POST['drv']['id_card'][$key]) ? sanitize_text_field($_POST['drv']['id_card'][$key]) : ''
						);
						$wpdb->insert(EcalypseRental::$db['booking_drivers'], $arr);
					}
				}
			}

			// Add prices
			$additional_vehicles = array();
			$total_price = 0;
			if ($_POST['prices'] && !empty($_POST['prices'])) {
				$item_number = 0;
				foreach ($_POST['prices']['name'] as $key => $val) {
					$item_number++;
					if (!empty($val) && isset($_POST['prices']['price'][$key]) && !empty($_POST['prices']['currency'][$key])) {
						$arr = array('id_booking' => $id_booking,
							'name' => sanitize_text_field($val),
							'price' => (float)$_POST['prices']['price'][$key],
							'currency' => sanitize_text_field($_POST['prices']['currency'][$key]),
						);
						if ($item_number == 1 && (int) $_POST['change_vehicle'] > 0) {
							$arr['name'] = $vehicle->name.substr($arr['name'], strpos($arr['name'], ', 20'));
						}
						$currency = sanitize_text_field($_POST['prices']['currency'][$key]);
						$total_price += (float)$_POST['prices']['price'][$key];
						if (isset($_POST['prices']['item_id'][$key]) && (int)$_POST['prices']['item_id'][$key] > 0) {
							$arr['item_id'] = (int)$_POST['prices']['item_id'][$key];
							$additional_vehicles[] = (int)$_POST['prices']['item_id'][$key];
						}
						if (isset($_POST['prices']['extras_id'][$key]) && (int)$_POST['prices']['extras_id'][$key] > 0) {
							$arr['extras_id'] = (int)$_POST['prices']['extras_id'][$key];
						}
						if (isset($_POST['prices']['new_item_id'][$key]) && (int)$_POST['prices']['new_item_id'][$key] > 0) {
							$arrBI = array('id_booking' => $id_booking,
										'vehicle_id' => (int)$_POST['prices']['new_item_id'][$key]);
							$wpdb->insert($wpdb->prefix . 'ecalypse_rental_booking_items', $arrBI);
							$arr['item_id'] = $wpdb->insert_id;
							$additional_vehicles[] = $arr['item_id'];
						}
						$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
					}
				}
			}
			
			if (isset($_POST['add_booking_emails'])) {
				switch ($_POST['status']) {
					case 1:
						// confirmed
						self::resend_email($id_booking, 'ecalypse_rental_reservation_email');
						break;
					case 2:
						// pending payment
						self::resend_email($id_booking, 'ecalypse_rental_email_status_pending');
						break;
					case 3:
						// panding other
						self::resend_email($id_booking, 'ecalypse_rental_email_status_pending_other');
						break;
				}
			}
			
			if ($edit == true) {
				$additional_vehicles[] = $wpdb->get_var('SELECT `ecalypse_rental_booking_items_id` FROM `'. $wpdb->prefix . 'ecalypse_rental_booking_items` bi INNER JOIN '.EcalypseRental::$db['booking'].' b ON b.id_booking = bi.id_booking`vehicle_id` WHERE bi.vehicle_id = b.vehicle_id LIMIT 1');
				$wpdb->query('DELETE FROM `'. $wpdb->prefix . 'ecalypse_rental_booking_items` WHERE `id_booking` = '.$id_booking.' AND `ecalypse_rental_booking_items_id` NOT IN ('.trim(implode(',', $additional_vehicles),',').')');
			}
			
			do_action( 'ecalypse_rental_admin_after_booking_save', $id_booking );

			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get booking
	 */
	public function get_booking() {
		global $wpdb;

		try {

			$where = 'b.`deleted` IS NULL';
			$order = 'b.`enter_date` ASC';

			if (isset($_GET['deleted'])) {
				$where = 'b.`deleted` IS NOT NULL';
				$order = 'b.`deleted` DESC';
			}
			
			if (isset($_GET['q']) && trim($_GET['q']) != '') {
				$where .= $wpdb->prepare(" AND (`b`.`first_name` LIKE %s OR `b`.`last_name` LIKE %s OR `b`.`email` LIKE %s OR `b`.`phone` LIKE %s OR `b`.`id_order` LIKE %s OR `b`.`vehicle` LIKE %s OR `b`.`id_booking` LIKE %s OR MD5(CONCAT(`id_order`, %s, `email`)) = %s)", '%'.sanitize_text_field($_GET['q']).'%', '%'.sanitize_text_field($_GET['q']).'%', '%'.sanitize_text_field($_GET['q']).'%', '%'.sanitize_text_field($_GET['q']).'%', '%'.sanitize_text_field($_GET['q']).'%', '%'.sanitize_text_field($_GET['q']).'%', '%'.sanitize_text_field($_GET['q']).'%', EcalypseRental::$hash_salt, sanitize_text_field($_GET['q']));
			}
			if (isset($_GET['filter_from']) && trim($_GET['filter_from']) != '' && strtotime($_GET['filter_from']) !== false) {
				$where .= $wpdb->prepare(" AND b.`return_date` >= %s", sanitize_text_field($_GET['filter_from']));
			} else {
				$where .= ' AND b.`return_date` >= NOW()';
			}
			
			if (isset($_GET['filter_to']) && trim($_GET['filter_to']) != '' && strtotime($_GET['filter_to']) !== false) {
				$where .= $wpdb->prepare(" AND b.`return_date` <= %s", sanitize_text_field($_GET['filter_to']));
			}

			$sql = 'SELECT b.*,
								MD5(CONCAT(b.`id_order`, "' . EcalypseRental::$hash_salt . '", b.`email`)) as `hash`,
								(SELECT SUM(bp.`price`) FROM `' . EcalypseRental::$db['booking_prices'] . '` bp WHERE bp.`id_booking` = b.`id_booking`) as `total_rental`,
								(SELECT GROUP_CONCAT(vehicle_id, ",") FROM `'. $wpdb->prefix . 'ecalypse_rental_booking_items` bi WHERE bi.id_booking = b.id_booking) as `all_vehicles`,
								(SELECT bp.`currency` FROM `' . EcalypseRental::$db['booking_prices'] . '` bp WHERE bp.`id_booking` = b.`id_booking` LIMIT 1) as `currency`
							FROM `' . EcalypseRental::$db['booking'] . '` b
							WHERE ' . $where . '
							ORDER BY ' . $order;

			$booking = $wpdb->get_results($sql);

			return $booking;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function ajax_load_available_cars() {
		global $wpdb;
		$booking = false;
		if (isset($_POST['booking-id'])) {
			$booking = self::get_booking_detail((int)$_POST['booking-id']);
		}
		$additional_vehicles = EcalypseRental::get_vehicles($_POST);
		$return = array();
		if ($additional_vehicles && isset($additional_vehicles['results'])) {
			foreach ($additional_vehicles['results'] as $v) {
				$price = 0;
				if (isset($v->prices)) {
					$price = $v->prices['total_rental'];
				}
				$return[$v->id_fleet] = array('name' => $v->name, 'price' => $price);
			}
		}
		echo json_encode($return);
		exit;
	}

	/**
	 * 	Get booking detail
	 */
	public function get_booking_detail($id_booking) {
		global $wpdb;

		try {

			$data = array();
			$data['info'] = $wpdb->get_row($wpdb->prepare('SELECT *, MD5(CONCAT(`id_order`, "' . EcalypseRental::$hash_salt . '", `email`)) as `hash` FROM `' . EcalypseRental::$db['booking'] . '`
																										 WHERE `id_booking` = %d', $id_booking));

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
	 * 	Resend confirmation email
	 */
	public static function resend_email($id_booking, $email_type = 'ecalypse_rental_reservation_email') {
		try {
			return EcalypseRental::send_emails($id_booking, $email_type, false);	
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Copy booking
	 */
	public function copy_booking($id_booking) {
		global $wpdb;

		try {

			$booking = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking'] . '` WHERE `id_booking` = %d', $id_booking));
			$prices = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking_prices'] . '` WHERE `id_booking` = %d', $id_booking));
			$drivers = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['booking_drivers'] . '` WHERE `id_booking` = %d', $id_booking));

			// Save Branch to DB
			$arr = array();
			foreach ($booking as $key => $val) {
				$arr[$key] = (!is_null($val) ? $val : NULL);
			}

			// Generate new Order ID
			$arr['id_order'] = EcalypseRental::generate_unique_order_id();

			unset($arr['id_booking']);
			unset($arr['updated']);
			unset($arr['deleted']);
			$wpdb->insert(EcalypseRental::$db['booking'], $arr);
			$id_booking = $wpdb->insert_id;

			// Save Prices
			if (!empty($prices)) {
				foreach ($prices as $key => $val) {
					$arr = array('id_booking' => $id_booking,
						'name' => $val->name,
						'price' => $val->price,
						'currency' => $val->currency);
					$wpdb->insert(EcalypseRental::$db['booking_prices'], $arr);
				}
			}

			// Save Drivers
			if (!empty($drivers)) {
				foreach ($drivers as $key => $val) {
					$arr = array();
					foreach ($val as $kD => $vD) {
						$arr[$kD] = $vD;
					}
					$arr['id_booking'] = $id_booking;
					unset($arr['id_driver']);
					$wpdb->insert(EcalypseRental::$db['booking_drivers'], $arr);
				}
			}
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Delete booking
	 */
	public function delete_booking($id_booking) {
		global $wpdb;

		try {

			$arr = array('deleted' => Date('Y-m-d H:i:s'));
			$wpdb->update(EcalypseRental::$db['booking'], $arr, array('id_booking' => $id_booking), array('%s'));
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Delete booking from database
	 */
	public function delete_booking_total($id_booking) {
		global $wpdb;

		try {


			$wpdb->delete(EcalypseRental::$db['booking'], array('id_booking' => $id_booking), array('%d'));
			$wpdb->delete(EcalypseRental::$db['booking_drivers'], array('id_booking' => $id_booking), array('%d'));
			$wpdb->delete(EcalypseRental::$db['booking_prices'], array('id_booking' => $id_booking), array('%d'));
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Restore booking
	 */
	public function restore_booking($id_booking) {
		global $wpdb;

		try {

			$wpdb->query('UPDATE ' . EcalypseRental::$db['booking'] . ' SET `deleted` = NULL WHERE `id_booking` = ' . (int) $id_booking);
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Update settings
	 */
	public function update_settings() {
		try {

			// Update easy options
			$opts = array('ecalypse_rental_call_for_price','ecalypse_rental_use_free_hour_km','ecalypse_rental_multiple_rental','default_enter_time','default_return_time',	'ecalypse_rental_hour_pricing_after_day','ecalypse_rental_dump_data_email', 'ecalypse_rental_type_of_rental','ecalypse_rental_global_currency', 'ecalypse_rental_consumption', 'ecalypse_rental_delivery_price',
				'ecalypse_rental_overbooking', 'ecalypse_rental_any_location_search', 'ecalypse_rental_paypal',
				'ecalypse_rental_require_payment', 'ecalypse_rental_distance_metric', 'ecalypse_rental_show_vat', 'ecalypse_rental_reminder_days', 'ecalypse_rental_thank_you_days', 'ecalypse_rental_detail_page', 'ecalypse_rental_disable_time', 'ecalypse_rental_compatible_theme', 'ecalypse_rental_min_before_days', 'ecalypse_rental_max_before_days', 'ecalypse_rental_webhook_url');

			foreach ($opts as $val) {
				update_option($val, sanitize_text_field($_POST[$val]));
			}
			
			$allowed_days = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0);
			foreach ($allowed_days as $k => $v) {
				if (isset($_POST['ecalypse_rental_allowed_days']) && isset($_POST['ecalypse_rental_allowed_days'][$k]) && $_POST['ecalypse_rental_allowed_days'][$k] == 1) {
					$allowed_days[(int)$k] = 1;
				}
			}
			update_option('ecalypse_rental_allowed_days', serialize($allowed_days));
			
			$vat_settings = array('vat' => 0, 'vat_2' => 0, 'vat_3' => 0, 'vat_calculation' => 1);
			if (isset($_POST['vat'])) {
				$vat_settings['vat'] = (float)$_POST['vat'];
			}
			
			if (isset($_POST['vat_2'])) {
				$vat_settings['vat_2'] = (float)$_POST['vat_2'];
			}
			
			if (isset($_POST['vat_3'])) {
				$vat_settings['vat_3'] = (float)$_POST['vat_3'];
			}
			
			if (isset($_POST['vat_calculation'])) {
				$vat_settings['vat_calculation'] = (int)$_POST['vat_calculation'];
			}
			update_option('ecalypse_rental_vat_settings', serialize($vat_settings));
			
			if (isset($_POST['ecalypse_rental_booking_statuses'])) {
				update_option('ecalypse_rental_booking_statuses', self::sanitize_and_serialize($_POST['ecalypse_rental_booking_statuses']));
			}

			update_option('ecalypse_rental_disclaimer', self::sanitize_and_serialize($_POST['ecalypse_rental_disclaimer']));

			// Update available currencies
			$av_currencies = array();
			if (isset($_POST['av_currencies_cc']) && !empty($_POST['av_currencies_cc'])) {
				foreach ($_POST['av_currencies_cc'] as $key => $cc) {
					$rate = null;
					if (isset($_POST['av_currencies_rate'][$key]) && !empty($_POST['av_currencies_rate'][$key])) {
						$rate = number_format((float) $_POST['av_currencies_rate'][$key], 3);
					}
					if (!empty($cc) && !empty($rate)) {
						$av_currencies[sanitize_text_field($cc)] = $rate;
					}
				}
			}
			update_option('ecalypse_rental_available_currencies', serialize($av_currencies));

			// Update where to send email after booking
			$ecalypse_rental_book_send_email = array('client' => 1, 'admin' => 1, 'other' => 0);
			if (!isset($_POST['ecalypse_rental_book_send_email']['client'])) {
				$ecalypse_rental_book_send_email['client'] = 0;
			}
			if (!isset($_POST['ecalypse_rental_book_send_email']['admin'])) {
				$ecalypse_rental_book_send_email['admin'] = 0;
			}
			
			if (isset($_POST['ecalypse_rental_book_send_email']['other'])) {
				$ecalypse_rental_book_send_email['other'] = 1;
			}
			
			$ecalypse_rental_book_send_email['other_email'] = '';
			if (isset($_POST['ecalypse_rental_book_send_email']['other_email'])) {
				$ecalypse_rental_book_send_email['other_email'] = sanitize_email($_POST['ecalypse_rental_book_send_email']['other_email']);
			}

			update_option('ecalypse_rental_book_send_email', serialize($ecalypse_rental_book_send_email));
			
			if (isset($_POST['ecalypse_rental_compatible_theme']) && $_POST['ecalypse_rental_compatible_theme'] == 'no') {
				// load translations for non compatible theme
				self::get_local_theme_translations();
			}
			wp_cache_delete( 'alloptions', 'options' );
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Update company info
	 */
	public function update_company_info() {
		try {

			// Update easy options
			$opts = array('name', 'id', 'vat', 'email', 'phone', 'fax', 'street', 'city', 'zip', 'country', 'web');
			$info = array();
			foreach ($opts as $val) {
				$info[$val] = sanitize_text_field($_POST[$val]);
			}

			update_option('ecalypse_rental_company_info', serialize($info));
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Update SMTP settings
	 */
	public function update_smtp_settings() {
		try {

			unset($_POST['save_smtp_settings']);
			update_option('ecalypse_rental_smtp', self::sanitize_and_serialize($_POST));
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	/**
	 * Update API key
	 */
	public function save_rencato_connector_settings() {
		try {
			if ($_POST['ecalypse_rental_enable_rencato_connector'] == 1) {
				throw new Exception(__('API key not found or connector is disabled in the Rencato account.', 'ecalypse-rental'));
			} else {
				$connector_settings = unserialize(get_option('ecalypse_rental_rencato_connector_settings'));
				if (!is_array($connector_settings)) {
					$connector_settings = array();
				}
				$connector_settings['enabled'] = 0;
				update_option('ecalypse_rental_rencato_connector_settings', serialize($connector_settings));
				return true;
			}
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	
	/**
	 * 	Get Quick info for Homepage
	 */
	public function get_quick_info() {
		global $wpdb;
		try {

			$info = array();
			$info['fleet'] = $wpdb->get_var('SELECT COUNT(*) FROM `' . EcalypseRental::$db['fleet'] . '` WHERE `deleted` IS NULL');

			$info['extras'] = $wpdb->get_var('SELECT COUNT(*) FROM `' . EcalypseRental::$db['extras'] . '` WHERE `deleted` IS NULL');

			$info['branches'] = $wpdb->get_var('SELECT COUNT(*) FROM `' . EcalypseRental::$db['branch'] . '` WHERE `deleted` IS NULL');

			$info['pricing'] = $wpdb->get_var('SELECT COUNT(*) FROM `' . EcalypseRental::$db['pricing'] . '` WHERE `deleted` IS NULL AND `active` = 1');

			$info['booking_progress'] = $wpdb->get_var('SELECT COUNT(*) FROM `' . EcalypseRental::$db['booking'] . '`
																									WHERE `enter_date` < NOW() AND `return_date` > NOW() AND `deleted` IS NULL');

			$info['booking_future'] = $wpdb->get_var('SELECT COUNT(*) FROM `' . EcalypseRental::$db['booking'] . '`
																								WHERE `enter_date` > NOW() AND `deleted` IS NULL');

			$info['deleted'] = $wpdb->get_var('SELECT ((SELECT COUNT(*) FROM `' . EcalypseRental::$db['fleet'] . '` WHERE `deleted` IS NOT NULL) + 
																								(SELECT COUNT(*) FROM `' . EcalypseRental::$db['extras'] . '` WHERE `deleted` IS NOT NULL) + 
																								(SELECT COUNT(*) FROM `' . EcalypseRental::$db['branch'] . '` WHERE `deleted` IS NOT NULL) + 
																								(SELECT COUNT(*) FROM `' . EcalypseRental::$db['pricing'] . '` WHERE `deleted` IS NOT NULL) +
																								(SELECT COUNT(*) FROM `' . EcalypseRental::$db['booking'] . '` WHERE `deleted` IS NOT NULL))
																								');

			return $info;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * 	Get vehicle categories
	 */
	public function get_vehicle_categories() {
		global $wpdb;

		try {
			
			return $wpdb->get_results('SELECT vc.*,
																	 (SELECT COUNT(*) FROM `' . EcalypseRental::$db['fleet'] . '` f
																	  WHERE f.`id_category` = vc.`id_category` AND f.`deleted` IS NULL) as `no_vehicles`
																 FROM `' . EcalypseRental::$db['vehicle_categories'] . '` vc
																 WHERE `deleted` IS NULL ORDER BY `id_category` ASC');
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function update_vehicle_categories() {
		global $wpdb;
		try {

			$vehicle_cats = array();

			if (isset($_POST['vehicle_categories_name']) && !empty($_POST['vehicle_categories_name'])) {
				if (!function_exists('wp_handle_upload')) {
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}

				foreach ($_POST['vehicle_categories_name'] as $key => $name) {
					$key = sanitize_text_field($key);
					$name = sanitize_text_field($name);
					$picture = '';

					// Previous picture
					if (isset($_POST['vehicle_categories_picture'][$key]) && !empty($_POST['vehicle_categories_picture'][$key])) {
						$picture = sanitize_text_field($_POST['vehicle_categories_picture'][$key]);
					}

					// Save uploaded picture
					if (isset($_FILES['vehicle_categories_file']['name'][$key]) && !empty($_FILES['vehicle_categories_file']['tmp_name'][$key])) {
						$thisfile = array(
							'name' => $_FILES['vehicle_categories_file']['name'][$key],
							'type' => $_FILES['vehicle_categories_file']['type'][$key],
							'tmp_name' => $_FILES['vehicle_categories_file']['tmp_name'][$key],
							'error' => $_FILES['vehicle_categories_file']['error'][$key],
							'size' => $_FILES['vehicle_categories_file']['size'][$key]
						);
						$uploadedfile = $thisfile;
						$upload_overrides = array('test_form' => false);
						$movefile = wp_handle_upload($uploadedfile, $upload_overrides);
						if ($movefile) {
							$picture = $movefile['url'];
						}
					}

					if (isset($_POST['vehicle_categories_delete'][$key]) && $_POST['vehicle_categories_delete'][$key] == 1) {

						$wpdb->update(EcalypseRental::$db['vehicle_categories'], array('deleted' => Date('Y-m-d H:i:s')), array('id_category' => $key), array('%s'));
					} else {

						// Save to database
						$arr = array('name' => $name,
							'name_translations' => self::sanitize_and_serialize($_POST['vehicle_categories_name_translations'][$key]),
							'picture' => sanitize_text_field($picture),
							'updated' => Date('Y-m-d H:i:s'));

						$wpdb->update(EcalypseRental::$db['vehicle_categories'], $arr, array('id_category' => $key));
					}
				}
			}
			
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	private static function wp_exist_post_by_title($title_str) {
		global $wpdb;
		return $wpdb->get_row($wpdb->prepare("SELECT * FROM wp_posts WHERE post_title = %s", $title_str), 'ARRAY_A');
	}

	public function add_vehicle_category() {
		global $wpdb;

		try {

			// Save uploaded picture
			$picture = '';
			if (isset($_FILES['vehicle_category_picture']) && !empty($_FILES['vehicle_category_picture']['tmp_name'])) {
				if (!function_exists('wp_handle_upload')) {
					require_once(ABSPATH . 'wp-admin/includes/file.php');
				}
				$uploadedfile = $_FILES['vehicle_category_picture'];
				$upload_overrides = array('test_form' => false);
				$movefile = wp_handle_upload($uploadedfile, $upload_overrides);
				if ($movefile) {
					$picture = $movefile['url'];
				}
			}

			// Save to database
			$arr = array('name' => sanitize_text_field($_POST['vehicle_category_name']),
				'name_translations' => self::sanitize_and_serialize($_POST['vehicle_category_name_translations']),
				'picture' => sanitize_text_field($picture));

			$wpdb->insert(EcalypseRental::$db['vehicle_categories'], $arr, array('%s', '%s'));
			$category_id = $wpdb->insert_id;

			if (!self::wp_exist_post_by_title($_POST['vehicle_category_name'])) {
				$post_category = array(
					'post_title' => sanitize_text_field($_POST['vehicle_category_name']),
					'post_name' => sanitize_title($_POST['vehicle_category_name']),
					'post_content' => '[ecalypse_rental_category id="' . $category_id . '"]',
					'post_status' => __('publish', 'ecalypse-rental'),
					'post_author' => 1,
					'post_type' => __('page', 'ecalypse-rental'),
					'page_template' => 'our-cars-template.php'
				);
				wp_insert_post($post_category);
			}
			
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	public function replace_price_scheme($original_id, $new_id) {
		global $wpdb;

		try {

			// Extras
			$wpdb->query(
				$wpdb->prepare('UPDATE `' . EcalypseRental::$db['extras'] . '`
												SET `global_pricing_scheme` = %d
												WHERE `global_pricing_scheme` = %d', $new_id, $original_id));

			$wpdb->query(
				$wpdb->prepare('UPDATE `' . EcalypseRental::$db['extras_pricing'] . '`
												SET `id_pricing` = %d
												WHERE `id_pricing` = %d', $new_id, $original_id));

			// Fleet
			$wpdb->query(
				$wpdb->prepare('UPDATE `' . EcalypseRental::$db['fleet'] . '`
												SET `global_pricing_scheme` = %d
												WHERE `global_pricing_scheme` = %d', $new_id, $original_id));

			$wpdb->query(
				$wpdb->prepare('UPDATE `' . EcalypseRental::$db['fleet_pricing'] . '`
												SET `id_pricing` = %d
												WHERE `id_pricing` = %d', $new_id, $original_id));
			

			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * 	PRICING
	 */
	public function add_pricing() {
		global $wpdb;

		try {

			$edit = false;
			if (isset($_POST['id_pricing']) && !empty($_POST['id_pricing'])) {
				$edit = true;
				$id_pricing = (int) $_POST['id_pricing'];
				if ($id_pricing <= 0) {
					throw new Exception("Invalid Pricing ID");
				}
			}
			
			$tax_rates = array();
			if (isset($_POST['tax_rates'])) {
				$tax_rates = $_POST['tax_rates'];
			}
			
			// Save Pricing scheme to DB
			$arr = array('type' => (int)$_POST['type'],
				'name' => sanitize_text_field($_POST['name']),
				'currency' => sanitize_text_field($_POST['currency']),
				'onetime_price' => (float)$_POST['onetime_price'],
				'maxprice' => (float)$_POST['maxprice'],
				'min_price' => (float)$_POST['min_price'],
				'promocode' => sanitize_text_field($_POST['promocode']),
				'active' => (int)$_POST['active'],
				'rate_id' => sanitize_text_field($_POST['rate_id']),
				'active_days' => implode(';', $_POST['active_days']),
				'tax_rates' => self::sanitize_and_serialize($tax_rates)
			);
			
			$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
			if ($time_pricing_type == 'half_day') {
				$arr['am_price'] = (float)$_POST['am_price'];
				$arr['pm_price'] = (float)$_POST['pm_price'];
				$arr['full_day_price'] = (float)$_POST['full_day_price'];
			}

			if ($edit == true) {

				// Update Scheme
				$arr['updated'] = Date('Y-m-d H:i:s');
				$wpdb->update(EcalypseRental::$db['pricing'], $arr, array('id_pricing' => $id_pricing));

				// Delete previous Ranges
				$wpdb->delete(EcalypseRental::$db['pricing_ranges'], array('id_pricing' => $id_pricing), array('%d'));
			} else {

				// Add Scheme
				$wpdb->insert(EcalypseRental::$db['pricing'], $arr);
				$id_pricing = $wpdb->insert_id;
			}


			// TIME BASED
			if ((int) $_POST['type'] == 2) {
				
				if ($time_pricing_type != 'half_day') {

					// Update day ranges
					if (isset($_POST['days_price']) && !empty($_POST['days_price'])) {
						foreach ($_POST['days_price'] as $key => $price) {
							$from = (isset($_POST['days']['from'][$key]) ? (int) $_POST['days']['from'][$key] : NULL);
							$to = (isset($_POST['days']['to'][$key]) ? (int) $_POST['days']['to'][$key] : NULL);

							if ($from > 0 && $price > 0) {
								$arr = array('id_pricing' => $id_pricing,
									'type' => 1, // DAYS
									'no_from' => $from,
									'no_to' => $to,
									'price' => (float)$price
								);

								$wpdb->insert(EcalypseRental::$db['pricing_ranges'], $arr);
							}
						}
					}

					// Update hour ranges
					if (isset($_POST['hours_price']) && !empty($_POST['hours_price'])) {
						foreach ($_POST['hours_price'] as $key => $price) {
							$from = (isset($_POST['hours']['from'][$key]) ? (int) $_POST['hours']['from'][$key] : NULL);
							$to = (isset($_POST['hours']['to'][$key]) ? (int) $_POST['hours']['to'][$key] : NULL);

							if ($from > 0 && $price > 0) {
								$arr = array('id_pricing' => $id_pricing,
									'type' => 2, // HOURS
									'no_from' => $from,
									'no_to' => $to,
									'price' => (float)$price
								);

								$wpdb->insert(EcalypseRental::$db['pricing_ranges'], $arr);
							}
						}
					}
				} else {
					// FULL/HALF day pricing
				}
			}
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function get_pricing($sort = NULL, $type = NULL) {
		global $wpdb;

		try {

			if (empty($sort)) {
				$sort = 'p.`id_pricing` DESC';
				if (isset($_GET['deleted'])) {
					$sort = 'p.`deleted` DESC';
				}
			}

			$where = ' p.`deleted` IS NULL ';
			if (isset($_GET['deleted'])) {
				$where = ' p.`deleted` IS NOT NULL ';
			}

			if (!empty($type)) {
				$where .= " AND p.`type` = " . (int) $type . " ";
			}



			$pricing = $wpdb->get_results('SELECT p.*,
																		   ((SELECT COUNT(*) FROM `' . EcalypseRental::$db['extras_pricing'] . '` ep WHERE ep.`id_pricing` = p.`id_pricing`) + 
																			  (SELECT COUNT(*) FROM `' . EcalypseRental::$db['extras'] . '` e WHERE e.`global_pricing_scheme` = p.`id_pricing`)) as `extras_usage`,
																		   ((SELECT COUNT(*) FROM `' . EcalypseRental::$db['fleet_pricing'] . '` fp WHERE fp.`id_pricing` = p.`id_pricing`) + 
																			  (SELECT COUNT(*) FROM `' . EcalypseRental::$db['fleet'] . '` f WHERE f.`global_pricing_scheme` = p.`id_pricing`)) as `fleet_usage`
																		   
																	 	 FROM `' . EcalypseRental::$db['pricing'] . '` p 
																		 WHERE ' . $where . '
																		 ORDER BY ' . $sort);
			return $pricing;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function get_pricing_detail($id_pricing) {
		global $wpdb;

		try {

			$pricing = $wpdb->get_row($wpdb->prepare('SELECT p.* FROM `' . EcalypseRental::$db['pricing'] . '` p WHERE p.`id_pricing` = %d', $id_pricing));

			// Days and hours
			$ranges = $wpdb->get_results($wpdb->prepare('SELECT pr.* FROM `' . EcalypseRental::$db['pricing_ranges'] . '` pr WHERE pr.`id_pricing` = %d ORDER BY pr.`type`, pr.`no_from`', $id_pricing));
			if ($ranges && !empty($ranges)) {
				foreach ($ranges as $key => $val) {
					$type = (((int) $val->type == 1) ? 'days' : 'hours');
					if (!isset($pricing->$type)) {
						$pricing->$type = array();
					}

					array_push($pricing->$type, array('from' => $val->no_from,
						'to' => $val->no_to,
						'price' => $val->price));
				}
			}

			return $pricing;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function delete_pricing($id_pricing) {
		global $wpdb;

		try {

			$arr = array('deleted' => Date('Y-m-d H:i:s'));
			$wpdb->update(EcalypseRental::$db['pricing'], $arr, array('id_pricing' => $id_pricing), array('%s'));
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	public function delete_pricing_from_db($id_pricing) {
		global $wpdb;

		try {
			$wpdb->query('DELETE FROM ' . EcalypseRental::$db['pricing'] . ' WHERE `id_pricing` = ' . (int) $id_pricing . ' LIMIT 1');
			$wpdb->query('DELETE FROM ' . EcalypseRental::$db['fleet_pricing'] . ' WHERE `id_pricing` = ' . (int) $id_pricing);
			$wpdb->query('DELETE FROM ' . EcalypseRental::$db['pricing_ranges'] . ' WHERE `id_pricing` = ' . (int) $id_pricing);
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function restore_pricing($id_pricing) {
		global $wpdb;

		try {

			$wpdb->query('UPDATE ' . EcalypseRental::$db['pricing'] . ' SET `deleted` = NULL WHERE `id_pricing` = ' . (int) $id_pricing);
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function print_pricing_ranges($id_pricing) {
		global $wpdb;

		try {
			
			$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
			if ($time_pricing_type == 'half_day') {
				$pricing = $wpdb->get_row($wpdb->prepare('SELECT p.* FROM `' . EcalypseRental::$db['pricing'] . '` p WHERE p.`id_pricing` = %d', $id_pricing));

				if ($pricing && !empty($pricing)) {
					echo '<h5>' . __('Name:', 'ecalypse-rental') . ' <strong>' . $pricing->name . '</strong></h5>';
					echo '<h5>' . __('AM price:', 'ecalypse-rental') . ' <strong>' . $pricing->am_price . '&nbsp;' . $pricing->currency . '</strong></h5>';
					echo '<h5>' . __('PM price:', 'ecalypse-rental') . ' <strong>' . $pricing->pm_price . '&nbsp;' . $pricing->currency . '</strong></h5>';
					echo '<h5>' . __('Full day price:', 'ecalypse-rental') . ' <strong>' . $pricing->full_day_price . '&nbsp;' . $pricing->currency . '</strong></h5>';
					echo '<h5>' . __('Active:', 'ecalypse-rental') . (($pricing->active == 1) ? __('yes', 'ecalypse-rental') : __('no', 'ecalypse-rental')) . '</h5>';
					echo '<h5>' . __('Created:', 'ecalypse-rental') . $pricing->created . '</h5>';
					echo '<h5>' . __('Updated:', 'ecalypse-rental') . (!empty($pricing->updated) ? $pricing->updated : '&ndash;') . '</h5>';
				}
				return;
			}

			// Days and hours
			$ranges = $wpdb->get_results($wpdb->prepare('SELECT pr.*, p.`currency`
																									 FROM `' . EcalypseRental::$db['pricing_ranges'] . '` pr
																									 LEFT JOIN `' . EcalypseRental::$db['pricing'] . '` p ON p.`id_pricing` = pr.`id_pricing` 
																									 WHERE pr.`id_pricing` = %d
																									 ORDER BY pr.`type`, pr.`no_from`', $id_pricing));
			if ($ranges && !empty($ranges)) {
				$set_type = 0;
				foreach ($ranges as $key => $val) {

					if ($set_type != $val->type) {
						if ($set_type > 0) {
							echo '</table></div>';
						}
						echo '<div style="width:48%;float:left;margin-right:10px;"><h4>' . (($val->type == 1) ? __('Days range', 'ecalypse-rental') : __('Hours range', 'ecalypse-rental')) . '</h4>';
						echo '<table class="table table-striped">';
						$set_type = $val->type;
					}

					echo '<tr>';
					echo '<td class="text-right" style="width:2em;">' . $val->no_from . '</td>';
					echo '<td class="text-center" style="width:2em;">&mdash;</td>';
					echo '<td class="text-right" style="width:2em;">' . $val->no_to . '</td>';
					echo '<td class="text-center">' . (($val->type == 1) ? __('days', 'ecalypse-rental') : __('hours', 'ecalypse-rental')) . '</td>';
					echo '<td class="text-right"><strong>' . $val->price . '&nbsp;' . $val->currency . '</strong> ' . (($val->type == 1) ? __('/ day', 'ecalypse-rental') : __('/ hour', 'ecalypse-rental')) . '</td>';
					echo '</tr>';
				}
				echo '</table>';
			} else {
				echo '<h4>' . __('Day or hour ranges are not set. Please "Modify" your Price scheme.', 'ecalypse-rental') . '</h4>';
			}
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function print_onetime_price($id_pricing) {
		global $wpdb;

		try {

			$pricing = $wpdb->get_row($wpdb->prepare('SELECT p.* FROM `' . EcalypseRental::$db['pricing'] . '` p WHERE p.`id_pricing` = %d', $id_pricing));

			if ($pricing && !empty($pricing)) {
				echo '<h5>' . __('Name:', 'ecalypse-rental') . ' <strong>' . $pricing->name . '</strong></h5>';
				echo '<h5>' . __('One Time Price:', 'ecalypse-rental') . ' <strong>' . $pricing->onetime_price . '&nbsp;' . $pricing->currency . '</strong></h5>';
				echo '<h5>' . __('VAT: ', 'ecalypse-rental') . $pricing->vat . '%</h5>';
				echo '<h5>' . __('Active: ', 'ecalypse-rental') . (($pricing->active == 1) ? 'yes' : 'no') . '</h5>';
				echo '<h5>' . __('Created: ', 'ecalypse-rental') . $pricing->created . '</h5>';
				echo '<h5>' . __('Updated: ', 'ecalypse-rental') . (!empty($pricing->updated) ? $pricing->updated : '&ndash;') . '</h5>';
			}
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function print_price_schemes($type, $id) {
		global $wpdb;

		try {

			$pricing = $wpdb->get_results($wpdb->prepare('SELECT ep.*, p.`name`, p.`type`
																										FROM `' . EcalypseRental::$db[$type . '_pricing'] . '` ep
																										INNER JOIN `' . EcalypseRental::$db['pricing'] . '` p ON p.`id_pricing` = ep.`id_pricing`
																										WHERE ep.`id_' . $type . '` = %d
																										ORDER BY ep.`priority`', $id));

			if ($pricing && !empty($pricing)) {
				echo '<table class="table table-striped">';
				echo '<thead><tr>';
				echo '<th>' . __('Priority', 'ecalypse-rental') . '</th>';
				echo '<th>' . __('Name', 'ecalypse-rental') . '</th>';
				echo '<th>' . __('Valid from', 'ecalypse-rental') . '</th>';
				echo '<th>' . __('Valid to', 'ecalypse-rental') . '</th>';
				echo '</tr></thead><tbody>';
				foreach ($pricing as $key => $val) {
					echo '<tr>';
					echo '<td>' . $val->priority . '</td>';
					echo '<td><a href="' . esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')) . '&amp;' . (($val->type == 1) ? 'get_onetime_price' : 'get_day_ranges') . '=' . $val->id_pricing . '" class="ecalypse_rental_show_ranges">' . $val->name . '</a></td>';
					echo '<td>' . (($val->valid_from != '0000-00-00') ? $val->valid_from : '&ndash;') . '</td>';
					echo '<td>' . (($val->valid_to != '0000-00-00') ? $val->valid_to : '&ndash;') . '</td>';
					echo '</tr>';
				}
				echo '</tbody></table>';
			}
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function copy_pricing($id_pricing) {
		global $wpdb;

		try {

			$pricing = $wpdb->get_row($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['pricing'] . '` WHERE `id_pricing` = %d', $id_pricing));
			$ranges = $wpdb->get_results($wpdb->prepare('SELECT * FROM `' . EcalypseRental::$db['pricing_ranges'] . '` WHERE `id_pricing` = %d', $id_pricing));

			// Save Pricing to DB
			$arr = array('type' => $pricing->type,
				'name' => $pricing->name . __(' (copy)', 'ecalypse-rental'),
				'currency' => $pricing->currency,
				'onetime_price' => $pricing->onetime_price,
				'maxprice' => $pricing->maxprice,
				'min_price' => $pricing->min_price,
				'promocode' => $pricing->promocode,
				'active' => $pricing->active,
				'day_price' => $pricing->day_price,
				'week_price' => $pricing->week_price,
				'month_price' => $pricing->month_price,
				'rate_id' => $pricing->rate_id,
				'active_days' => $pricing->active_days,
			);

			$wpdb->insert(EcalypseRental::$db['pricing'], $arr);
			$id_pricing = $wpdb->insert_id;

			// Save Ranges to DB
			if (!empty($ranges)) {
				foreach ($ranges as $key => $val) {
					$arr = array('id_pricing' => $id_pricing,
						'type' => $val->type,
						'no_from' => $val->no_from,
						'no_to' => $val->no_to,
						'price' => $val->price);
					$wpdb->insert(EcalypseRental::$db['pricing_ranges'], $arr);
				}
			}
			
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * 	Get newsletter users
	 */
	public function get_newsletter() {
		global $wpdb;

		try {

			return $wpdb->get_results('SELECT `created`, `first_name`, `last_name`, `email`, `id_booking`
																 FROM `' . EcalypseRental::$db['booking'] . '`
																 WHERE `newsletter` = 1
																 GROUP BY `email`
																 ORDER BY `id_booking` DESC');
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
	
	/**
	 * Remove email from newsletter
	 * @param type $booking_id
	 */
	function remove_newsletter($id_booking) {
		global $wpdb;

		try {

			$wpdb->query('UPDATE ' . EcalypseRental::$db['booking'] . ' SET `newsletter` = 0 WHERE `id_booking` = ' . (int) $id_booking.' LIMIT 1');
			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	/**
	 * Export all newsletter emails as CSV
	 * @param type $format
	 */
	private static function newsletter_export($format) {
		$newsletter = self::get_newsletter();
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=newsletter_export.csv');
		$output = fopen('php://output', 'w');
		foreach ($newsletter as $news) {
			fputcsv($output, (array) $news, ';');
		}

		exit;
	}

	/**
	 * 	TRANSLATIONS
	 */
	
	public function get_local_theme_translations() {
		global $wpdb;

		$dir = ECALYPSERENTALSTARTER__PLUGIN_DIR . '/templates';
		$counter = 0;

		foreach (glob($dir . '/*.php') as $filename) {

			$translations = array();
			$content = file_get_contents($filename);

			preg_match_all("#EcalypseRental\:\:t\('([^']+)'\)#", $content, $out);
			if (isset($out[1]) && !empty($out[1])) {
				$translations = array_merge($translations, $out[1]);
			}

			preg_match_all('#EcalypseRental\:\:t\("([^"]+)"\)#', $content, $out);
			if (isset($out[1]) && !empty($out[1])) {
				$translations = array_merge($translations, $out[1]);
			}


			if (!empty($translations)) {
				foreach ($translations as $val) {
					$wpdb->query($wpdb->prepare('INSERT IGNORE INTO `' . EcalypseRental::$db['translations'] . '` (`original`) VALUES (%s)', $val));
				}
			}

			$counter += count($translations);
		}

		return $counter;
	}
	
	public function get_theme_translations($lang) {
		global $wpdb;

		try {

			$sql = 'SELECT t.`original`, tt.`translation`
							FROM `' . EcalypseRental::$db['translations'] . '` t
							LEFT JOIN `' . EcalypseRental::$db['translations'] . '` tt ON tt.`original` = t.`original` AND tt.`lang` = %s
							WHERE t.`lang` = "en_GB"
							ORDER BY t.`original` ASC';

			$data = $wpdb->get_results($wpdb->prepare($sql, $lang));

			$translations = array();

			if ($data && !empty($data)) {
				foreach ($data as $val) {
					if (stripslashes($val->original) == $val->original) {
						$translations[$val->original] = $val->translation;
					} else {
						// delete it from database
						$data = $wpdb->query($wpdb->prepare('DELETE FROM `' . EcalypseRental::$db['translations'] . '` WHERE `original` = %s', $val->original));
					}
				}
			}

			return $translations;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function update_theme_translations($lang, $translations) {
		global $wpdb;
		try {

			if ($translations && !empty($translations)) {
				foreach ($translations['key'] as $key => $val) {

					$tt = (isset($translations['val'][$key]) ? $translations['val'][$key] : '');
					$sql = 'INSERT INTO `' . EcalypseRental::$db['translations'] . '` (`lang`, `original`, `translation`)
									VALUES (%s, %s, %s)
									ON DUPLICATE KEY UPDATE
										`translation` = %s';
					$wpdb->query($wpdb->prepare($sql, $lang, stripslashes($val), stripslashes($tt), stripslashes($tt)));
				}
			}

			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function send_test_email() {


		$company = unserialize(get_option('ecalypse_rental_company_info'));

		$email = ((isset($company['email']) && !empty($company['email'])) ? $company['email'] : 'admin@' . $_SERVER['SERVER_NAME']);
		$name = ((isset($company['name']) && !empty($company['name'])) ? $company['name'] : 'Ecalypse Starter WP Plugin');

		add_filter('wp_mail_content_type', create_function('', 'return "text/html"; '));
		add_filter('wp_mail_from', create_function('', 'return "' . $email . '"; '));
		add_filter('wp_mail_from_name', create_function('', 'return "' . $name . '"; '));
		$res = wp_mail(sanitize_email($_POST['user']), "Testing e-mail from Ecalypse Starter WP plugin.", "<h1>E-mailing from the Wordpress works fine.</h1><p>Lorem ipsum dolor sit amet consectetuer metus montes Vestibulum ipsum congue. Ridiculus quis sed enim odio natoque dui et lobortis Nulla hendrerit. Eget semper Phasellus orci eu risus scelerisque tellus at Aliquam feugiat. Libero nulla eros accumsan ut dui diam et id Curabitur lacus. Phasellus odio et nunc condimentum Curabitur., 'ecalypse-rental')</p>");
		if ($res == true) {
			echo __('Test e-mail sent.', 'ecalypse-rental');
		} else {
			echo __('Something went wrong :(', 'ecalypse-rental');
		}
	}

	public function export_database() {
		try {
			global $wpdb;
			$sql = '-- Ecalypse Starter Wordpress Plugin' . "\n";
			$sql .= '-- Date: ' . Date('Y-m-d H:i:s') . "\n\n";

			if (isset($_POST['export_structure']) && $_POST['export_structure'] == 1) {
				foreach (EcalypseRental::$db as $key => $table) {
					$tbl = $wpdb->get_row('SHOW CREATE TABLE ' . $table);
					$tt = 'Create Table';
					$sql .= "-- Table: " . $table . "\n";
					$sql .= "DROP TABLE " . $table . ";\n";
					$sql .= $tbl->$tt . ";" . "\n\n";
				}
			}


			if (isset($_POST['export_data']) && $_POST['export_data'] == 1) {
				foreach (EcalypseRental::$db as $key => $table) {
					$data = $wpdb->get_results('SELECT * FROM ' . $table);
					$sql .= "-- Data for table: " . $table . "\n";

					foreach ($data as $kD => $vD) {
						$subSql = 'INSERT INTO `' . $table . '` ';

						$vD = (array) $vD;
						$subSql .= '(`' . implode('`,`', array_keys($vD)) . '`) VALUES (';
						foreach ($vD as $kE => $vE) {
							$subSql .= '"' . addslashes($vE) . '"' . ',';
						}
						$subSql = substr($subSql, 0, -1);
						$subSql .= ');';
						$subSql .= "\n";

						$sql .= $subSql;
					}
				}

				// Options data
				$options = array('ecalypse_rental_available_languages', 'ecalypse_rental_primary_language', 'ecalypse_rental_global_currency',
					'ecalypse_rental_consumption', 'ecalypse_rental_delivery_price', 'ecalypse_rental_available_currencies',
					'ecalypse_rental_overbooking', 'ecalypse_rental_any_location_search', 'ecalypse_rental_paypal', 'ecalypse_rental_smtp',
					'ecalypse_rental_require_payment', 'ecalypse_rental_distance_metric', 'ecalypse_rental_company_info',
					'ecalypse_rental_reservation_email_en_GB', 'ecalypse_rental_terms_conditions_en_GB', 'ecalypse_rental_book_send_email');
				foreach ($options as $key) {
					$value = get_option($key);
					if (!empty($value)) {
						$sql .= "INSERT INTO `" . $wpdb->prefix . "options` (`option_name`, `option_value`) VALUES ('" . addslashes($key) . "', '" . addslashes($value) . "')
										 ON DUPLICATE KEY UPDATE `option_value` = '" . addslashes($value) . "';" . "\n";
					}
				}

				$available_languages = unserialize(get_option('ecalypse_rental_available_languages'));
				if ($available_languages && !empty($available_languages)) {
					foreach ($available_languages as $lang => $val) {
						$value = get_option('ecalypse_rental_reservation_email_' . $lang);
						if (!empty($value)) {
							$sql .= "INSERT INTO `" . $wpdb->prefix . "options` (`option_name`, `option_value`) VALUES ('" . addslashes('ecalypse_rental_reservation_email_' . $lang) . "', '" . addslashes($value) . "')
											 ON DUPLICATE KEY UPDATE `option_value` = '" . addslashes($value) . "';" . "\n";
						}

						$value = get_option('ecalypse_rental_terms_conditions_' . $lang);
						if (!empty($value)) {
							$sql .= "INSERT INTO `" . $wpdb->prefix . "options` (`option_name`, `option_value`) VALUES ('" . addslashes('ecalypse_rental_terms_conditions_' . $lang) . "', '" . addslashes($value) . "')
											 ON DUPLICATE KEY UPDATE `option_value` = '" . addslashes($value) . "';" . "\n";
						}
					}
				}
			}

			/* echo $sql;
			  exit; */

			return $sql;
		} catch (PDOException $e) {
			return false;
		}
	}

	public function import_demo_data() {
		global $wpdb;

		try {



			return true;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function rrmdir($dir) {
		foreach (glob($dir . '/*') as $file) {
			if (is_dir($file))
				self::rrmdir($file);
			else
				unlink($file);
		} rmdir($dir);
	}
	
	protected function sanitize_and_serialize($input) {
		
		if (is_array($input)) {
			$data = self::sanitize_array($input);
		} else {
			$data = $input;
		}
		return serialize($data);
	}
	
	protected function sanitize_array($input) {
		$data = array();
		foreach ($input as $k => $v) {
			if (is_array($v)) {
				$data[sanitize_text_field($k)] = self::sanitize_array($v);
			} else {
				$data[sanitize_text_field($k)] = sanitize_text_field($v);
			}
		}
		return $data;
	}
}
