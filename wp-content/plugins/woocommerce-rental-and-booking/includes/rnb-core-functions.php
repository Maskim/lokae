<?php

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @access public
 * @param string $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function rnb_get_template($template_name, $args = [], $template_path = '', $default_path = '')
{
    if (!empty($args) && is_array($args)) {
        extract($args);
    }

    $located = rnb_locate_template($template_name, $template_path, $default_path);

    if (!file_exists($located)) {
        _doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '2.1');
        return;
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $located = apply_filters('rnb_get_template', $located, $template_name, $args, $template_path, $default_path);

    do_action('woocommerce_before_template_part', $template_name, $template_path, $located, $args);

    include $located;

    do_action('woocommerce_after_template_part', $template_name, $template_path, $located, $args);
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *        yourtheme        /    $template_path    /    $template_name
 *        yourtheme        /    $template_name
 *        $default_path    /    $template_name
 *
 * @access public
 * @param string $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function rnb_locate_template($template_name, $template_path = '', $default_path = '')
{
    if (!$template_path) {
        $template_path = WC()->template_path();
    }

    if (!$default_path) {
        $default_path = trailingslashit(REDQ_RENTAL_PATH) . 'templates/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template(
        [
            trailingslashit($template_path) . $template_name,
            $template_name
        ]
    );

    // Get default template/
    if (!$template || WC_TEMPLATE_DEBUG_MODE) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return apply_filters('woocommerce_locate_template', $template, $template_name, $template_path);
}

function rnb_inventory_availability_check($product_id, $inventory_id, $render = 'BLOCKED_DATES_ONLY')
{
    global $wpdb;
    $inventory_id = (int) $inventory_id;

    $booking_data = $wpdb->get_results(
        "select *,
        (select sum(meta_value) from {$wpdb->prefix}woocommerce_order_itemmeta where order_item_id=wr.item_id and meta_key='_qty') as booked,
        (select meta_value from {$wpdb->prefix}postmeta where post_id=wr.inventory_id and meta_key='quantity') as quantity
        from {$wpdb->prefix}rnb_availability as wr where inventory_id='" . $inventory_id . "' AND delete_status='0'",
        ARRAY_A
    );

    if (empty($booking_data)) {
        return [];
    }

    $conditional_data = redq_rental_get_settings($product_id, 'conditions');
    $conditional_data = $conditional_data['conditions'];
    $time_interval = (!empty($conditional_data['time_interval'])) ? $conditional_data['time_interval'] : 5;
    $time_format = $conditional_data['time_format'];
    $date_format = $conditional_data['date_format'];

    // foreach ($booking_data as $key => $value) {
    //     if (!empty($conditional_data['before_block_days'])) {
    //         $booking_data[$key]['pickup_datetime'] = date("Y-m-d H:i:s", strtotime('+' . $conditional_data['before_block_days'] . ' day', strtotime($value['pickup_datetime'])));
    //     }

    //     if (!empty($conditional_data['post_block_days'])) {
    //         $booking_data[$key]['return_datetime'] = date("Y-m-d H:i:s", strtotime('-' . $conditional_data['post_block_days'] . ' day', strtotime($value['return_datetime'])));
    //     }

    //     if ($value['block_by'] == 'CUSTOM') {
    //         $booking_data[$key]['booked'] = $value['quantity'];
    //     }
    // }

    $filtered_booking_data = array_filter($booking_data, function ($value, $index) use ($inventory_id) {
        return $value['inventory_id'] == $inventory_id && new DateTime() < new DateTime($value['return_datetime']);
    }, ARRAY_FILTER_USE_BOTH);

    $sloted_data = [];

    $first_order = reset($filtered_booking_data);

    $total_inventory = isset($first_order['quantity']) ? $first_order['quantity'] : null;

    if ($total_inventory == null) {
        return [];
    }

    foreach ($filtered_booking_data as $key => $data) {
        $single_slotted_data = [];
        $begin = new DateTime($data['pickup_datetime']);
        $end = new DateTime($data['return_datetime']);
        // $end = $end->modify('+1 hour');
        $end_interval = $time_interval - 1;
        $end = $end->modify("+{$end_interval} minutes");

        // $interval = new DateInterval('PT1H');
        $interval = new DateInterval("PT{$time_interval}M");
        $daterange = new DatePeriod($begin, $interval, $end);

        foreach ($daterange as $date) {
            // $processed_booked_slot = array('datetime' => $date->format('Y:m:d H:m:s'), 'booked' => $data['booked']);
            if (isset($sloted_data[$date->format("{$date_format} H:i")])) {
                $sloted_data[$date->format("{$date_format} H:i")] += $data['booked'];
            } else {
                $sloted_data[$date->format("{$date_format} H:i")] = $data['booked'];
            }
        }
    }

    $final_booked_date = array_filter($sloted_data, function ($value, $index) use ($total_inventory) {
        return $value >= $total_inventory;
    }, ARRAY_FILTER_USE_BOTH);

    $formatted_blocked_date = [];
    foreach ($final_booked_date as $date => $value) {
        $datetime = explode(' ', $date);
        // if (isset($formatted_blocked_date[$datetime[0]])) {
        $formatted_blocked_date[$datetime[0]][] = $datetime[1];
        // }
    }

    $blocked_date = [];

    if (!empty($conditional_data['allowed_times'])) {
        $time_frame = $conditional_data['allowed_times'];
    } else {
        $time_frame = rnb_get_hours_range($lower = 0, $upper = 86400, $step = $time_interval * 60, $format = 'H:i');
    }

    $allowed_datetime = [];

    if ($time_format == '12-hours') {
        foreach ($time_frame as $key => $time) {
            $time_frame[$key] = date('H:i', strtotime($time));
        }
    }

    foreach ($formatted_blocked_date as $key => $blocked) {
        if (count($blocked) === count($time_frame)) {
            $blocked_date[] = $key;
        } else {
            $allowed_datetime[$key] = array_values(array_diff($time_frame, $blocked));
        }
    }

    foreach ($allowed_datetime as $key => $value) {
        if (empty($value)) {
            $blocked_date[] = $key;
        }
    }

    if ('BLOCKED_DATES_ONLY' == $render) {
        return $blocked_date;
    }

    if ($time_format == '12-hours') {
        foreach ($allowed_datetime as $key => $datetime) {
            foreach ($datetime as $k => $time) {
                $allowed_datetime[$key][$k] = date('g:i a', strtotime($time));
            }
        }
    }

    return $allowed_datetime;
}

function rnb_inventory_quantity_availability_check($frontend)
{
    global $wpdb;

    $inventory_id = $frontend['inventory_id'];
    $product_id = $frontend['product_id'];

    $filtered_booking_data = $wpdb->get_results(
        "select *,
        (select sum(meta_value) from {$wpdb->prefix}woocommerce_order_itemmeta where order_item_id= wr.item_id and meta_key='_qty') as booked,
        (select meta_value from {$wpdb->prefix}postmeta where post_id= wr.inventory_id and meta_key='quantity') as quantity
        from {$wpdb->prefix}rnb_availability as wr where inventory_id='" . $inventory_id . "' AND delete_status='0'",
        ARRAY_A
    );

    if (empty($filtered_booking_data)) {
        return $frontend['quantity'];
    }

    $conditional_data = redq_rental_get_settings($product_id, 'conditions');
    $conditional_data = $conditional_data['conditions'];
    $time_interval = (!empty($conditional_data['time_interval'])) ? $conditional_data['time_interval'] : 5;

    //For pre & post block dates
    // $frontend['pickup_datetime'] = date("Y-m-d H:i:s", strtotime('-' . $conditional_data['before_block_days'] . ' day', strtotime($frontend['pickup_datetime'])));
    // $frontend['return_datetime'] = date("Y-m-d H:i:s", strtotime('+' . $conditional_data['post_block_days'] . ' day', strtotime($frontend['return_datetime'])));
    //End

    $sloted_data = [];
    $first_order = reset($filtered_booking_data);

    $total_inventory = isset($first_order['quantity']) ? $first_order['quantity'] : null;

    if ($total_inventory == null) {
        return [];
    }

    foreach ($filtered_booking_data as $key => $data) {
        $single_slotted_data = [];
        $begin = new DateTime($data['pickup_datetime']);
        $end = new DateTime($data['return_datetime']);
        // $end = $end->modify('+1 hour');
        $end_interval = $time_interval - 1;
        $end = $end->modify("+{$end_interval} minutes");

        // $interval = new DateInterval('PT1H');
        $interval = new DateInterval("PT{$time_interval}M");
        $daterange = new DatePeriod($begin, $interval, $end);

        foreach ($daterange as $date) {
            if (isset($sloted_data[$date->format('Y:m:d H:i')])) {
                $sloted_data[$date->format('Y:m:d H:i')] += $data['booked'];
            } else {
                $sloted_data[$date->format('Y:m:d H:i')] = $data['booked'];
            }
        }
    }

    $to = new DateTime($frontend['pickup_datetime']);
    $from = new DateTime($frontend['return_datetime']);
    // $from = $from->modify('+1 hour');
    $end_interval = $time_interval - 1;
    $from = $from->modify("+{$end_interval} minutes");

    // $interval = new DateInterval('PT1H');
    $interval = new DateInterval("PT{$time_interval}M");
    $daterange = new DatePeriod($to, $interval, $from);

    $requested_data_slot = [];
    foreach ($daterange as $date) {
        $requested_data_slot[] = $date->format('Y:m:d H:i');
    }

    $final_availability_slot = [];
    foreach ($requested_data_slot as $key => $single_slot) {
        if (isset($sloted_data[$single_slot])) {
            $final_availability_slot[] = $total_inventory - $sloted_data[$single_slot];
        } else {
            $final_availability_slot[] = $total_inventory;
        }
    }

    return count($final_availability_slot) ? min($final_availability_slot) : null;
}

function rnb_get_hours_range($start = 0, $end = 86400, $step = 3600, $format = 'H:i a')
{
    $times = [];
    foreach (range($start, $end, $step) as $timestamp) {
        $hour_mins = gmdate('H:i', $timestamp);
        if (!empty($format)) {
            $times[$hour_mins] = gmdate($format, $timestamp);
        } else {
            $times[$hour_mins] = $hour_mins;
        }
    }
    return $times;
}

function array_key_exists_recursive($key, $array)
{
    if (array_key_exists($key, $array)) {
        return true;
    }
    foreach ($array as $k => $value) {
        if (is_array($value) && array_key_exists_recursive($key, $value)) {
            return true;
        }
    }
    return false;
}

function rnb_get_product_inventory_id($product_id)
{
    global $wpdb;
    $pivot_table = $wpdb->prefix . 'rnb_inventory_product';

    if ($wpdb->get_var("SHOW TABLES LIKE '$pivot_table'") == $pivot_table) {
        $results = $wpdb->get_results($wpdb->prepare("SELECT inventory FROM $pivot_table WHERE product = %d", $product_id), ARRAY_N);

        $inventory_id = [];
        foreach ($results as $r) {
            foreach ($r as $i) {
                $inventory_id[] = $i;
            }
        }

        return $inventory_id;
    }
    return [];
}

/**
 * get woocommerce currency info
 *
 * @return array
 */
function rnb_get_woocommerce_currency_info()
{
    $woocommerce_info = [
        'symbol' => get_woocommerce_currency_symbol(),
        'currency' => get_woocommerce_currency(),
        'thousand' => wc_get_price_thousand_separator(),
        'decimal' => wc_get_price_decimal_separator(),
        'number' => wc_get_price_decimals(),
        'position' => get_option('woocommerce_currency_pos'),
    ];
    return $woocommerce_info;
}

/**
 * Get translated strings
 *
 * @return void
 */
function rnb_get_translated_strings()
{
    $translated_strings = [
        'invalid_range_notice'     => __('Invalid Date Range', 'redq-rental'),
        'max_day_notice'           => __('Max Rental Days', 'redq-rental'),
        'min_day_notice'           => __('Min Rental Days', 'redq-rental'),
        'quantity_notice'          => __('Quantity is not available', 'redq-rental'),
        'quote_user_name'          => __('User name field is required', 'redq-rental'),
        'quote_password'           => __('Password field is required', 'redq-rental'),
        'quote_first_name'         => __('First name field is required', 'redq-rental'),
        'quote_last_name'          => __('Last name field is required', 'redq-rental'),
        'quote_email'              => __('Quote email is required', 'redq-rental'),
        'quote_phone'              => __('Phone is required', 'redq-rental'),
        'quote_message'            => __('Message is required', 'redq-rental'),
        'positive_days'            => __('No of days must be greater than 1 day', 'redq-rental'),
        'positive_hours'           => __('Total hours must be greater than 0 hours', 'redq-rental'),
        'pickup_loc_required'      => __('Pickup location is required', 'redq-rental'),
        'dropoff_loc_required'     => __('Drop-off location is required', 'redq-rental'),
        'pickup_time_required'     => __('Pickup time is required', 'redq-rental'),
        'dropoff_time_required'    => __('Drop-off time is required', 'redq-rental'),
        'adult_required'           => __('Adults is required', 'redq-rental'),
        'child_required'           => __('Child is required', 'redq-rental'),
        'email_validation_message' => __('Email Must be valid & required!', 'redq-rental'),
        'phone_validation_message' => __('Phone number must be valid & required!', 'redq-rental'),
    ];
    return $translated_strings;
}

function rnb_get_localize_info($product_id)
{
    $general_data = redq_rental_get_settings($product_id, 'general');
    $localize_info = [
        'domain' => $general_data['general']['lang_domain'],
        'months' => $general_data['general']['months'],
        'weekdays' => $general_data['general']['weekdays']
    ];
    return $localize_info;
}

/**
 * get combined settings data
 *
 * @param int $product_id
 * @return void
 */
function rnb_get_combined_settings_data($product_id)
{
    $rnb_data = [];

    $general_data = redq_rental_get_settings($product_id, 'general');
    $labels = redq_rental_get_settings($product_id, 'labels', ['pickup_location', 'return_location', 'pickup_date', 'return_date', 'resources', 'categories', 'person', 'deposites']);
    $layout_two_labels = redq_rental_get_settings($product_id, 'layout_two', ['inventory', 'datetime', 'location', 'resource', 'person', 'deposit', 'resources']);
    $displays = redq_rental_get_settings($product_id, 'display');
    $conditions = redq_rental_get_settings($product_id, 'conditions');
    $validations = redq_rental_get_settings($product_id, 'validations');

    $rnb_data['settings']['general'] = $general_data['general'];
    $rnb_data['settings']['labels'] = $labels['labels'];
    $rnb_data['settings']['displays'] = $displays['display'];
    $rnb_data['settings']['conditions'] = $conditions['conditions'];
    $rnb_data['settings']['validations'] = $validations['validations'];
    $rnb_data['settings']['layout_two_labels'] = $layout_two_labels;

    return $rnb_data;
}

/**
 * rnb_date_between
 *
 * @param  mixed $date
 * @param  mixed $from
 * @param  mixed $to
 *
 * @return boolean
 */
function rnb_date_between($StartDate1, $EndDate1, $StartDate2, $EndDate2)
{
    $result = false;

    if (($StartDate1 <= $EndDate2) && ($StartDate2 <= $EndDate1)) {
        $result = true;
    }

    return $result;
}
