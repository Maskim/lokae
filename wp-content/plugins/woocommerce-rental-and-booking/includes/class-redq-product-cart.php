<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle cart page
 *
 * @version 5.0.0
 * @since 1.0.0
 */
class WC_Redq_Rental_Cart
{
    public function __construct()
    {
        add_filter('woocommerce_add_cart_item_data', array($this, 'redq_rental_add_cart_item_data'), 20, 2);
        add_filter('woocommerce_add_cart_item', array($this, 'redq_rental_add_cart_item'), 20, 1);
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'redq_rental_get_cart_item_from_session'), 20, 2);
        add_filter('woocommerce_cart_item_quantity', array($this, 'redq_cart_item_quantity'), 20, 2);
        add_filter('woocommerce_add_to_cart_validation', array($this, 'redq_add_to_cart_validation'), 20, 3);
        add_action('woocommerce_checkout_process', array($this, 'redq_rental_checkout_order_process'), 20, 3);
        add_filter('woocommerce_get_item_data', array($this, 'redq_rental_get_item_data'), 20, 2);
        add_action('woocommerce_new_order_item', array($this, 'redq_rental_order_item_meta'), 20, 3);
        add_action('woocommerce_thankyou', array($this, 'woocommerce_thankyou'), 20, 1);
        add_action('wp_ajax_quote_booking_data', array($this, 'quote_booking_data'));
        add_action('wp_ajax_nopriv_quote_booking_data', array($this, 'quote_booking_data'));

        add_action('wp_ajax_rnb_calculate_inventory_data', array($this, 'calculate_inventory_data'));
        add_action('wp_ajax_nopriv_rnb_calculate_inventory_data', array($this, 'calculate_inventory_data'));
    }

    /**
     * calculate_inventory_data
     *
     */
    public function calculate_inventory_data()
    {
        $posted = $_POST['form'];
        $product_id = $posted['add-to-cart'];

        $posted_data = $this->get_posted_data($product_id, $posted);

        if (isset($posted_data['error']) && !empty($posted_data['error'])) {
            wp_send_json($posted_data);
        }

        $cart_quantity = rnb_check_cart_data($product_id, $posted_data);

        $quantity = (isset($posted_data['quantity']) && !empty($posted_data['quantity'])) ? intval($posted_data['quantity']) : 1;
        $final_cost = wc_price($posted_data['rental_days_and_costs']['cost'] * $quantity);

        $price_breakdown = $posted_data['rental_days_and_costs'];
        $response_data = array(
            'quantity'           => $quantity,
            'available_quantity' => $posted_data['available_quantity'] - $cart_quantity,
            'total_cost'         => $final_cost,
            'date_multiply'      => $posted_data['date_multiply'],
            'price_breakdown'    => rnb_format_prices($price_breakdown, $quantity),
        );

        wp_send_json($response_data);
    }


    /**
     * If checkout failed during an AJAX call, send failure response.
     */
    protected function send_ajax_failure_response()
    {
        if (is_ajax()) {
            // only print notices if not reloading the checkout, otherwise they're lost in the page reload
            if (!isset(WC()->session->reload_checkout)) {
                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();
            }

            $response = array(
                'result'   => 'failure',
                'messages' => isset($messages) ? $messages : '',
                'refresh'  => isset(WC()->session->refresh_totals),
                'reload'   => isset(WC()->session->reload_checkout),
            );

            unset(WC()->session->refresh_totals, WC()->session->reload_checkout);

            wp_send_json($response);
        }
    }


    public function woocommerce_thankyou($order_id)
    {
        $order = new WC_Order($order_id);
        $items = $order->get_items();

        foreach ($items as $item) {
            foreach ($item['item_meta'] as $key => $value) {
                if ($key === 'Quote Request') {
                    wp_update_post(array(
                        'ID'          => $value[0],
                        'post_status' => 'quote-completed'
                    ));
                }
            }
        }
    }


    /**
     * Insert posted data into cart item meta
     *
     * @param $cart_item_meta
     * @param string $product_id , array $cart_item_meta
     * @return array
     */
    public function redq_rental_add_cart_item_data($cart_item_meta, $product_id)
    {
        $product_type = wc_get_product($product_id)->get_type();
        if (isset($product_type) && $product_type === 'redq_rental' && !isset($cart_item_meta['rental_data']['quote_id'])) {
            $posted_data = $this->get_posted_data($product_id, $_POST);
            $cart_item_meta['rental_data'] = $posted_data;
        }

        return $cart_item_meta;
    }


    /**
     * Add cart item meta
     *
     * @param array $cart_item
     * @return array
     */
    public function redq_rental_add_cart_item($cart_item)
    {
        $product_id = $cart_item['data']->get_id();
        $product_type = wc_get_product($product_id)->get_type();
        if (isset($cart_item['rental_data']['quote_id']) && $product_type === 'redq_rental') {
            $cart_item['data']->set_price($cart_item['rental_data']['rental_days_and_costs']['cost']);
        } else {
            if (isset($cart_item['rental_data']['rental_days_and_costs']['cost']) && $product_type === 'redq_rental') {
                $cart_item['data']->set_price($cart_item['rental_data']['rental_days_and_costs']['cost']);
            }

            if (isset($cart_item['quantity']) && $product_type === 'redq_rental') {
                $cart_item['quantity'] = isset($cart_item['rental_data']['quantity']) ? $cart_item['rental_data']['quantity'] : 1;
            }
        }

        return $cart_item;
    }


    /**
     * Get item data from session
     *
     * @param array $cart_item
     * @param $values
     * @return array
     */
    public function redq_rental_get_cart_item_from_session($cart_item, $values)
    {
        if (!empty($values['rental_data'])) {
            $cart_item = $this->redq_rental_add_cart_item($cart_item);
        }
        return $cart_item;
    }


    /**
     * Set quantity always 1
     *
     * @param $product_quantity
     * @param array $cart_item_key , int $product_quantity
     * @return int
     */
    public function redq_cart_item_quantity($product_quantity, $cart_item_key)
    {
        global $woocommerce;
        $cart_details = $woocommerce->cart->cart_contents;

        if (isset($cart_details)) {
            foreach ($cart_details as $key => $value) {
                if ($key === $cart_item_key) {
                    $product_id = $value['product_id'];
                    $product_type = wc_get_product($product_id)->get_type();
                    if ($product_type === 'redq_rental') {
                        return $value['quantity'] ? $value['quantity'] : 1;
                    }

                    return $product_quantity;
                }
            }
        }
    }


    /**
     * Set Validation
     *
     * @param array $valid , int $product_id, int $quantity
     * @return array
     */
    public function redq_add_to_cart_validation($valid)
    {
        return $valid;
    }


    /**
     * Show cart item data in cart and checkout page
     *
     * @param $custom_data
     * @param $cart_item
     * @return array
     */
    public function redq_rental_get_item_data($custom_data, $cart_item)
    {
        $product_id = $cart_item['data']->get_id();
        $product_type = wc_get_product($product_id)->get_type();

        if (isset($product_type) && $product_type === 'redq_rental') {
            $rental_data = $cart_item['rental_data'];
            $quantity = intval($cart_item['quantity']);

            $options_data = array();
            $options_data['quote_id'] = '';


            $get_labels = redq_rental_get_settings($product_id, 'labels', array('pickup_location', 'return_location', 'pickup_date', 'return_date', 'resources', 'categories', 'person', 'deposites', 'inventory'));
            $labels = $get_labels['labels'];
            $get_displays = redq_rental_get_settings($product_id, 'display');
            $displays = $get_displays['display'];

            $get_conditions = redq_rental_get_settings($product_id, 'conditions');
            $conditional_data = $get_conditions['conditions'];

            $get_general = redq_rental_get_settings($product_id, 'general');
            $general_data = $get_general['general'];

            if (isset($rental_data) && !empty($rental_data)) {
                if (isset($rental_data['quote_id'])) {
                    $custom_data[] = array(
                        'name'    => $options_data['quote_id'] ? $options_data['quote_id'] : __('Quote Request', 'redq-rental'),
                        'value'   => '#' . $rental_data['quote_id'],
                        'display' => ''
                    );
                }

                if (isset($rental_data['pickup_location'])) {
                    $custom_data[] = array(
                        'name'    => $labels['pickup_location'],
                        'value'   => $rental_data['pickup_location']['address'],
                        'display' => ''
                    );
                }

                if (isset($rental_data['pickup_location']) && !empty($rental_data['pickup_location']['cost'])) {
                    $custom_data[] = array(
                        'name'    => $labels['pickup_location'] . __(' Cost', 'redq-rental'),
                        'value'   => wc_price($rental_data['pickup_location']['cost']),
                        'display' => ''
                    );
                }

                if (isset($rental_data['dropoff_location'])) {
                    $custom_data[] = array(
                        'name'    => $labels['return_location'],
                        'value'   => $rental_data['dropoff_location']['address'],
                        'display' => ''
                    );
                }

                if (isset($rental_data['dropoff_location']) && !empty($rental_data['dropoff_location']['cost'])) {
                    $custom_data[] = array(
                        'name'    => $labels['return_location'] . __(' Cost', 'redq-rental'),
                        'value'   => wc_price($rental_data['dropoff_location']['cost']),
                        'display' => ''
                    );
                }

                if (isset($rental_data['location_cost'])) {
                    $custom_data[] = array(
                        'name'    => esc_html__('Location Cost', 'redq-rental'),
                        'value'   => wc_price($rental_data['location_cost']),
                        'display' => ''
                    );
                }

                if (isset($rental_data['payable_cat'])) {
                    $cat_name = '';
                    foreach ($rental_data['payable_cat'] as $key => $value) {
                        if ($value['multiply'] === 'per_day') {
                            $cat_name .= $value['name'] . '×' . $value['quantity'] . ' ( ' . wc_price($value['cost']) . ' - ' . __('Per Day', 'redq-rental') . ' )' . ' , <br> ';
                        } else {
                            $cat_name .= $value['name'] . '×' . $value['quantity'] . ' ( ' . wc_price($value['cost']) . ' - ' . __('One Time', 'redq-rental') . ' )' . ' , <br> ';
                        }
                    }
                    $custom_data[] = array(
                        'name'    => $labels['categories'],
                        'value'   => $cat_name,
                        'display' => ''
                    );
                }

                if (isset($rental_data['payable_resource'])) {
                    $resource_name = '';
                    foreach ($rental_data['payable_resource'] as $key => $value) {
                        if ($value['cost_multiply'] === 'per_day') {
                            $resource_name .= $value['resource_name'] . ' ( ' . wc_price($value['resource_cost']) . ' - ' . __('Per Day', 'redq-rental') . ' )' . ' , <br> ';
                        } else {
                            $resource_name .= $value['resource_name'] . ' ( ' . wc_price($value['resource_cost']) . ' - ' . __('One Time', 'redq-rental') . ' )' . ' , <br> ';
                        }
                    }
                    $custom_data[] = array(
                        'name'    => $labels['resource'],
                        'value'   => $resource_name,
                        'display' => ''
                    );
                }

                if (isset($rental_data['payable_security_deposites'])) {
                    $security_deposite_name = '';
                    foreach ($rental_data['payable_security_deposites'] as $key => $value) {
                        if ($value['cost_multiply'] === 'per_day') {
                            $security_deposite_name .= $value['security_deposite_name'] . ' ( ' . wc_price($value['security_deposite_cost']) . ' - ' . __('Per Day', 'redq-rental') . ' )' . ' , <br> ';
                        } else {
                            $security_deposite_name .= $value['security_deposite_name'] . ' ( ' . wc_price($value['security_deposite_cost']) . ' - ' . __('One Time', 'redq-rental') . ' )' . ' , <br> ';
                        }
                    }
                    $custom_data[] = array(
                        'name'    => $labels['deposite'],
                        'value'   => $security_deposite_name,
                        'display' => ''
                    );
                }

                if (isset($rental_data['adults_info'])) {
                    $custom_data[] = array(
                        'name'    => $labels['adults'],
                        'value'   => $rental_data['adults_info']['person_count'],
                        'display' => ''
                    );
                }

                if (isset($rental_data['childs_info'])) {
                    $custom_data[] = array(
                        'name'    => $labels['childs'],
                        'value'   => $rental_data['childs_info']['person_count'],
                        'display' => ''
                    );
                }


                if (isset($rental_data['pickup_date']) && $displays['pickup_date'] === 'open') {
                    $pickup_date_time = convert_to_output_format($rental_data['pickup_date'], $conditional_data['date_format']);

                    if (isset($rental_data['pickup_time'])) {
                        $pickup_date_time .= ' ' . esc_html__('at', 'redq-rental') . ' ' . $rental_data['pickup_time'];
                    }
                    $custom_data[] = array(
                        'name'    => $labels['pickup_datetime'],
                        'value'   => $pickup_date_time,
                        'display' => ''
                    );
                }

                if ((isset($rental_data['dropoff_date']) && $displays['return_date'] === 'open') || (isset($rental_data['dropoff_time']) && $displays['return_time'] === 'open')) {
                    $return_date_time = convert_to_output_format($rental_data['dropoff_date'], $conditional_data['date_format']);

                    if (isset($rental_data['dropoff_time'])) {
                        $return_date_time .= ' ' . esc_html__('at', 'redq-rental') . ' ' . $rental_data['dropoff_time'];
                    }

                    $custom_data[] = array(
                        'name'    => $labels['return_datetime'],
                        'value'   => $return_date_time,
                        'display' => ''
                    );
                }


                if (isset($rental_data['booking_inventory']) && !empty($rental_data['booking_inventory'])) {
                    $custom_data[] = array(
                        'name'    => $labels['inventory'],
                        'value'   => get_the_title($rental_data['booking_inventory']),
                        'display' => ''
                    );
                }

                if (isset($rental_data['rental_days_and_costs'])) {
                    if ($rental_data['rental_days_and_costs']['pricing_type'] === 'flat_hours') {
                        $custom_data[] = array(
                            'name'    => $general_data['total_hours'] ? $general_data['total_hours'] : esc_html__('Total Hours', 'redq-rental'),
                            'value'   => $rental_data['rental_days_and_costs']['flat_hours'],
                            'display' => ''
                        );
                    }

                    if ($rental_data['rental_days_and_costs']['days'] <= 0 && $rental_data['rental_days_and_costs']['pricing_type'] !== 'flat_hours') {
                        $custom_data[] = array(
                            'name'    => $general_data['total_hours'] ? $general_data['total_hours'] : esc_html__('Total Hours', 'redq-rental'),
                            'value'   => $rental_data['rental_days_and_costs']['hours'],
                            'display' => ''
                        );
                    }

                    if ($rental_data['rental_days_and_costs']['days'] > 0 && $rental_data['rental_days_and_costs']['pricing_type'] !== 'flat_hours') {
                        $custom_data[] = array(
                            'name'    => $general_data['total_days'] ? $general_data['total_days'] : esc_html__('Total Days', 'redq-rental'),
                            'value'   => floor($rental_data['rental_days_and_costs']['flat_hours'] / 24) . __(' Days ', 'redq-rental') . $rental_data['rental_days_and_costs']['flat_hours'] % 24 . __(' Hours', 'redq-rental'),
                            'display' => ''
                        );
                    }

                    if (!empty($rental_data['rental_days_and_costs']['due_payment'])) {
                        $custom_data[] = array(
                            'name'    => $general_data['payment_due'] ? $general_data['payment_due'] : esc_html__('Due Payment', 'redq-rental'),
                            'value'   => wc_price(floatval($rental_data['rental_days_and_costs']['due_payment']) * $quantity),
                            'display' => ''
                        );
                    }
                }
            }
        }

        return $custom_data;
    }


    /**
     * Checking Processed Data
     *
     * @return void
     */
    public function redq_rental_checkout_order_process()
    {
        $cart_items = WC()->cart->get_cart();

        //Check if rentable is no
        if (isset($cart_items) && !empty($cart_items)) :
            foreach ($cart_items as $cart_item) {
                $product_id = $cart_item['product_id'];
                $product_type = wc_get_product($product_id)->get_type();
                $get_conditions = redq_rental_get_settings($product_id, 'conditions');
                $conditional_data = $get_conditions['conditions'];
                if (isset($product_type) && $product_type !== 'redq_rental') {
                    return;
                }
                if ($conditional_data['blockable'] === 'no') {
                    return;
                }
            }
        endif;

        //Checking available quantity in both cart item and previously booked dates
        if (isset($cart_items) && !empty($cart_items)) :
            foreach ($cart_items as $cart_item) {
                $quantity_ara = array();
                $product_id = $cart_item['product_id'];
                $product_type = wc_get_product($product_id)->get_type();

                if (isset($product_type) && $product_type !== 'redq_rental') {
                    return;
                }

                $get_conditions = redq_rental_get_settings($product_id, 'conditions');
                $conditional_data = $get_conditions['conditions'];
                $time_interval = !empty($conditional_data['time_interval']) ? (int) $conditional_data['time_interval'] : 30;

                $quantity = isset($cart_item['quantity']) ? $cart_item['quantity'] : 1;
                $rental_data = $cart_item['rental_data'];
                $dates = $rental_data['rental_days_and_costs']['booked_dates']['saved'];

                $pickup_datetime = '';
                $return_datetime = '';

                if (isset($rental_data['pickup_date']) && !empty($rental_data['pickup_date'])) {
                    $date = date_create($rental_data['pickup_date']);
                    $pickup_datetime = date_format($date, "Y-m-d");
                }


                if (isset($rental_data['pickup_time']) && !empty($rental_data['pickup_time'])) {
                    $pickup_datetime .= ' ' . $rental_data['pickup_time'];
                }

                if (isset($rental_data['pickup_time']) && empty($rental_data['pickup_time'])) {
                    $pickup_datetime .= ' ' . rnb_time_subtraction($time_interval);
                }

                if (!isset($rental_data['pickup_time'])) {
                    $pickup_datetime .= ' ' . rnb_time_subtraction($time_interval);
                }

                if (current_time('timestamp') > strtotime($pickup_datetime)) {
                    wc_add_notice(sprintf(__('Pickup date must be greater than current time period', 'redq-rental'), $quantity), 'error');
                    $this->send_ajax_failure_response();
                }

                if (isset($rental_data['dropoff_date']) && !empty($rental_data['dropoff_date'])) {
                    $date = date_create($rental_data['dropoff_date']);
                    $return_datetime = date_format($date, "Y-m-d");
                }

                if (isset($rental_data['dropoff_time']) && !empty($rental_data['dropoff_time'])) {
                    $return_datetime .= ' ' . $rental_data['dropoff_time'];
                } else {
                    $return_datetime .= ' ' . rnb_time_subtraction($time_interval);
                }

                $inventory_id = $rental_data['booking_inventory'];
                $check_inventory = array(
                    'pickup_datetime' => $pickup_datetime,
                    'return_datetime' => $return_datetime,
                    'inventory_id'    => $inventory_id,
                    'product_id'      => $product_id,
                    'quantity'        => get_post_meta($inventory_id, 'quantity', true),
                );

                $available_qty = rnb_inventory_quantity_availability_check($check_inventory);

                if ($quantity > $available_qty) {
                    wc_add_notice(sprintf(__('Quantity %s is not available', 'redq-rental'), $quantity), 'error');
                    $this->send_ajax_failure_response();
                }
            }
        endif;

        //End checking available quantity
    }


    /**
     * order_item_meta function
     *
     * @param string $item_id , array $values
     * @param $values
     * @param $order_id
     * @return void
     * @throws Exception
     */
    public function redq_rental_order_item_meta($item_id, $values, $order_id)
    {
        if (array_key_exists('legacy_values', $values)) {
            $product_id = $values->legacy_values['product_id'];
            $product_type = wc_get_product($product_id)->get_type();
        }

        if (isset($product_type) && $product_type === 'redq_rental') {
            $rental_data = $values->legacy_values['rental_data'];


            $options_data = array();
            $options_data['quote_id'] = '';
            $quantity = isset($rental_data['quantity']) ? $rental_data['quantity'] : 1;

            $get_labels = redq_rental_get_settings($product_id, 'labels', array('pickup_location', 'return_location', 'pickup_date', 'return_date', 'resources', 'categories', 'person', 'deposites', 'inventory'));
            $labels = $get_labels['labels'];
            $get_displays = redq_rental_get_settings($product_id, 'display');
            $displays = $get_displays['display'];
            $get_conditions = redq_rental_get_settings($product_id, 'conditions');
            $conditional_data = $get_conditions['conditions'];
            $get_general = redq_rental_get_settings($product_id, 'general');
            $general_data = $get_general['general'];

            $time_interval = !empty($conditional_data['time_interval']) ? (int) $conditional_data['time_interval'] : 30;

            if (isset($rental_data['quote_id'])) {
                wc_add_order_item_meta($item_id, $options_data['quote_id'] ? $options_data['quote_id'] : __('Quote Request', 'redq-rental'), $rental_data['quote_id']);
            }

            if (isset($rental_data['pickup_location'])) {
                wc_add_order_item_meta($item_id, $labels['pickup_location'], $rental_data['pickup_location']['address']);
            }

            if (isset($rental_data['pickup_location']) && !empty($rental_data['pickup_location']['cost'])) {
                wc_add_order_item_meta($item_id, $labels['pickup_location'] . __(' Cost', 'redq-rental'), wc_price($rental_data['pickup_location']['cost']));
            }

            if (isset($rental_data['dropoff_location'])) {
                wc_add_order_item_meta($item_id, $labels['return_location'], $rental_data['dropoff_location']['address']);
            }

            if (isset($rental_data['dropoff_location']) && !empty($rental_data['dropoff_location']['cost'])) {
                wc_add_order_item_meta($item_id, $labels['return_location'] . __(' Cost', 'redq-rental'), wc_price($rental_data['dropoff_location']['cost']));
            }

            if (isset($rental_data['location_cost']) && !empty($rental_data['location_cost'])) {
                wc_add_order_item_meta($item_id, esc_html__('Location Cost', 'redq-rental'), wc_price($rental_data['location_cost']));
            }

            if (isset($rental_data['payable_cat'])) {
                $rnb_cat = '';
                foreach ($rental_data['payable_cat'] as $key => $value) {
                    if ($value['multiply'] === 'per_day') {
                        $rnb_cat .= $value['name'] . '×' . $value['quantity'] . ' ( ' . wc_price($value['cost']) . ' - ' . __('Per Day', 'redq-rental') . ' )' . ' , <br> ';
                    } else {
                        $rnb_cat .= $value['name'] . '×' . $value['quantity'] . ' ( ' . wc_price($value['cost']) . ' - ' . __('One Time', 'redq-rental') . ' )' . ' , <br> ';
                    }
                }
                wc_add_order_item_meta($item_id, $labels['categories'], $rnb_cat);
            }

            if (isset($rental_data['payable_resource'])) {
                $resource_name = '';
                foreach ($rental_data['payable_resource'] as $key => $value) {
                    if ($value['cost_multiply'] === 'per_day') {
                        $resource_name .= $value['resource_name'] . ' ( ' . wc_price($value['resource_cost']) . ' - ' . __('Per Day', 'redq-rental') . ' )' . ' , <br> ';
                    } else {
                        $resource_name .= $value['resource_name'] . ' ( ' . wc_price($value['resource_cost']) . ' - ' . __('One Time', 'redq-rental') . ' )' . ' , <br> ';
                    }
                }
                wc_add_order_item_meta($item_id, $labels['resource'], $resource_name);
            }

            if (isset($rental_data['payable_security_deposites'])) {
                $security_deposite_name = '';
                foreach ($rental_data['payable_security_deposites'] as $key => $value) {
                    if ($value['cost_multiply'] === 'per_day') {
                        $security_deposite_name .= $value['security_deposite_name'] . ' ( ' . wc_price($value['security_deposite_cost']) . ' - ' . __('Per Day', 'redq-rental') . ' )' . ' , <br> ';
                    } else {
                        $security_deposite_name .= $value['security_deposite_name'] . ' ( ' . wc_price($value['security_deposite_cost']) . ' - ' . __('One Time', 'redq-rental') . ' )' . ' , <br> ';
                    }
                }
                wc_add_order_item_meta($item_id, $labels['deposite'], $security_deposite_name);
            }

            if (isset($rental_data['adults_info'])) {
                wc_add_order_item_meta($item_id, $labels['adults'], $rental_data['adults_info']['person_count']);
            }

            if (isset($rental_data['childs_info'])) {
                wc_add_order_item_meta($item_id, $labels['childs'], $rental_data['childs_info']['person_count']);
            }

            if (isset($rental_data['pickup_date']) && $displays['pickup_date'] === 'open') {
                $pickup_date_time = convert_to_output_format($rental_data['pickup_date'], $conditional_data['date_format']);

                $ptime = '';

                if (isset($rental_data['pickup_time'])) {
                    $pickup_date_time = $pickup_date_time . ' ' . esc_html__('at', 'redq-rental') . ' ' . $rental_data['pickup_time'];
                    $ptime = $rental_data['pickup_time'];
                } else {
                    $ptime = '00:00';
                }

                wc_add_order_item_meta($item_id, $labels['pickup_datetime'], $pickup_date_time);
                wc_add_order_item_meta($item_id, 'pickup_hidden_datetime', $rental_data['pickup_date'] . '|' . $ptime);
            }

            if ((isset($rental_data['dropoff_date']) && $displays['return_date'] === 'open') || (isset($rental_data['dropoff_time']) && $displays['return_time'] === 'open')) {
                $return_date_time = convert_to_output_format($rental_data['dropoff_date'], $conditional_data['date_format']);
                $rtime = '';

                if (isset($rental_data['dropoff_time'])) {
                    $return_date_time = $return_date_time . ' ' . esc_html__('at', 'redq-rental') . ' ' . $rental_data['dropoff_time'];
                    $rtime = $rental_data['dropoff_time'];
                } else {
                    $rtime = '23:00';
                }

                wc_add_order_item_meta($item_id, $labels['return_datetime'], $return_date_time);
                wc_add_order_item_meta($item_id, 'return_hidden_datetime', $rental_data['dropoff_date'] . '|' . $rtime);
            }


            if (isset($rental_data['rental_days_and_costs'])) {
                if ($rental_data['rental_days_and_costs']['pricing_type'] === 'flat_hours') {
                    wc_add_order_item_meta($item_id, $general_data['total_hours'] ? $general_data['total_hours'] : esc_html__('Total Hours', 'redq-rental'), $rental_data['rental_days_and_costs']['flat_hours']);
                    if ($rental_data['rental_days_and_costs']['days'] > 0) {
                        wc_add_order_item_meta($item_id, 'return_hidden_days', $rental_data['rental_days_and_costs']['days']);
                    }
                }

                if ($rental_data['rental_days_and_costs']['days'] > 0 && $rental_data['rental_days_and_costs']['pricing_type'] !== 'flat_hours') {
                    wc_add_order_item_meta($item_id, $general_data['total_days'] ? $general_data['total_days'] : esc_html__('Total Days', 'redq-rental'), floor($rental_data['rental_days_and_costs']['flat_hours'] / 24) . __(' Days ', 'redq-rental') . $rental_data['rental_days_and_costs']['flat_hours'] % 24 . __(' Hours', 'redq-rental'));
                    wc_add_order_item_meta($item_id, 'return_hidden_days', $rental_data['rental_days_and_costs']['days']);
                }

                if ($rental_data['rental_days_and_costs']['days'] <= 0 && $rental_data['rental_days_and_costs']['pricing_type'] !== 'flat_hours') {
                    wc_add_order_item_meta($item_id, $general_data['total_hours'] ? $general_data['total_hours'] : esc_html__('Total Hours', 'redq-rental'), $rental_data['rental_days_and_costs']['hours']);
                }

                if (!empty($rental_data['rental_days_and_costs']['due_payment'])) {
                    wc_add_order_item_meta($item_id, $general_data['payment_due'] ? $general_data['payment_due'] : esc_html__('Due Payment', 'redq-rental'), wc_price($rental_data['rental_days_and_costs']['due_payment'] * $quantity));
                }

                $price_breakdown = $rental_data['rental_days_and_costs']['price_breakdown'];
                wc_add_order_item_meta($item_id, 'rnb_price_breakdown', $price_breakdown);
            }

            // Start inventory post meta update from here
            $booked_dates_ara = isset($rental_data['rental_days_and_costs']['booked_dates']['saved']) ? $rental_data['rental_days_and_costs']['booked_dates']['saved'] : array();


            $inventory_id = $rental_data['booking_inventory'];

            $pickup_datetime = '';
            $return_datetime = '';

            if (isset($rental_data['pickup_date']) && !empty($rental_data['pickup_date'])) {
                $date = date_create($rental_data['pickup_date']);
                $pickup_datetime = date_format($date, "Y-m-d");
            }

            if (isset($rental_data['pickup_time']) && !empty($rental_data['pickup_time'])) {
                $pickup_datetime .= ' ' . $rental_data['pickup_time'];
            } else {
                $pickup_datetime .= ' ' . rnb_time_subtraction(0); // ' 00:00';
            }

            if (isset($rental_data['dropoff_date']) && !empty($rental_data['dropoff_date'])) {
                $date = date_create($rental_data['dropoff_date']);
                $return_datetime = date_format($date, "Y-m-d");
            }

            if (isset($rental_data['dropoff_time']) && !empty($rental_data['dropoff_time'])) {
                $return_datetime .= ' ' . $rental_data['dropoff_time'];
            } else {
                $return_datetime .= ' ' . rnb_time_subtraction($time_interval);
            }

            $booked_dates_ara = array(
                'pickup_datetime' => $pickup_datetime,
                'return_datetime' => $return_datetime,
                'inventory_id'    => $inventory_id,
                'product_id'      => $product_id,
                'quantity'        => get_post_meta($inventory_id, 'quantity', true),
            );

            wc_add_order_item_meta($item_id, 'booking_inventory', $inventory_id);
            wc_add_order_item_meta($item_id, $labels['inventory'], get_the_title($inventory_id));


            rnb_process_rental_order_data($product_id, $order_id, $item_id, $inventory_id, $booked_dates_ara, $quantity);
        }
    }


    /**
     * quote_booking_data
     *
     * @return void
     * @throws Exception
     */
    public function quote_booking_data()
    {
        $quote_id = $_POST['quote_id'];
        $product_id = $_POST['product_id'];
        $cart_data = array();
        $posted_data = array();

        $display_options = redq_rental_get_settings($product_id, 'display')['display'];
        $quote_meta = json_decode(get_post_meta($quote_id, 'order_quote_meta', true), true);
        $cost = get_post_meta($quote_id, '_quote_price', true);
        $cost = floatval($cost);

        if (isset($quote_meta) && is_array($quote_meta)) :
            foreach ($quote_meta as $key => $value) {
                if (isset($quote_meta[$key]['name'])) :
                    $posted_data[$quote_meta[$key]['name']] = $quote_meta[$key]['value'];
                endif;
            }
        endif;

        $pre_payment_percentage = get_option('rnb_instance_payment');
        if (empty($pre_payment_percentage) || $display_options['instance_payment'] === 'closed') {
            $pre_payment_percentage = 100;
        }

        $instance_payment = ($cost * $pre_payment_percentage) / 100;
        $due_payment = $cost - $instance_payment;

        $posted_data['quote_id'] = $quote_id;
        $ajax_data = $this->get_posted_data($product_id, $posted_data);

        $quantity = intval($ajax_data['quantity']);

        $ajax_data['rental_days_and_costs']['cost'] = $instance_payment;
        $ajax_data['rental_days_and_costs']['instant_pay'] = $pre_payment_percentage;
        $ajax_data['rental_days_and_costs']['due_payment'] = floatval($due_payment) * $quantity;

        $cart_data['rental_data'] = $ajax_data;
        if (WC()->cart->add_to_cart($product_id, $quantity = 1, $variation_id = '', $variation = '', $cart_data)) {
            echo json_encode(array(
                'success' => true,
            ));
        }

        wp_die();
    }


    /**
     * Return all post data for rental
     *
     * @param string $product_id , array $posted_data
     * @param $posted_data
     * @return void|array
     */
    public function get_posted_data($product_id, $posted_data)
    {
        $payable_cat = array();
        $payable_resource = array();
        $payable_security_deposites = array();
        $adults_info = array();
        $childs_info = array();
        $pickup_location = array();
        $dropoff_location = array();
        $data = array();

        $conditional_data = redq_rental_get_settings($product_id, 'conditions');
        $conditional_data = $conditional_data['conditions'];
        $euro_format = $conditional_data['euro_format'];
        $time_interval = !empty($conditional_data['time_interval']) ? (int) $conditional_data['time_interval'] : 30;


        $pickup_datetime = '';
        $return_datetime = '';


        //Re-format term data
        if (isset($posted_data['pickup_location']) && is_numeric($posted_data['pickup_location'])) {
            $pickup_data = get_pickup_location_data($posted_data['pickup_location'], 'pickup_location');
            $posted_data['pickup_location'] = $pickup_data;
        }

        if (isset($posted_data['dropoff_location']) && is_numeric($posted_data['dropoff_location'])) {
            $dropoff_data = get_dropoff_location_data($posted_data['dropoff_location'], 'dropoff_location');
            $posted_data['dropoff_location'] = $dropoff_data;
        }

        if (isset($posted_data['extras'])) {
            $resource_data = get_resource_data($posted_data['extras'], 'resource');
            $posted_data['extras'] = $resource_data;
        }

        if (isset($posted_data['categories'])) {
            $category_data = get_category_data($posted_data['categories'], $posted_data['cat_quantity'], 'rnb_categories');
            $posted_data['categories'] = $category_data;
        }

        if (isset($posted_data['additional_adults_info'])) {
            $adult_data = get_person_data($posted_data['additional_adults_info'], 'person');
            $posted_data['additional_adults_info'] = $adult_data;
        }

        if (isset($posted_data['additional_childs_info'])) {
            $child_data = get_person_data($posted_data['additional_childs_info'], 'person');
            $posted_data['additional_childs_info'] = $child_data;
        }

        if (isset($posted_data['security_deposites'])) {
            $deposit_data = get_deposit_data($posted_data['security_deposites'], 'deposite');
            $posted_data['security_deposites'] = $deposit_data;
        }
        //End



        if (isset($posted_data['pickup_date']) && !empty($posted_data['pickup_date'])) {
            $pickup_datetime = convert_to_generalized_format($posted_data['pickup_date'], $euro_format);
        }

        if (isset($posted_data['pickup_time']) && !empty($posted_data['pickup_time'])) {
            $pickup_datetime .= ' ' . $posted_data['pickup_time'];
        } else {
            $pickup_datetime .= ' ' . rnb_time_subtraction($time_interval); //   ' 00:00';
        }

        if (isset($posted_data['dropoff_date']) && !empty($posted_data['dropoff_date'])) {
            $return_datetime = convert_to_generalized_format($posted_data['dropoff_date'], $euro_format);
        } else {
            $return_datetime = convert_to_generalized_format($posted_data['pickup_date'], $euro_format);
        }

        if (isset($posted_data['dropoff_time']) && !empty($posted_data['dropoff_time'])) {
            $return_datetime .= ' ' . $posted_data['dropoff_time'];
        } else {
            $return_datetime .= ' ' . rnb_time_subtraction($time_interval);  //' 23:00';
        }

        if (isset($posted_data['inventory_quantity']) && !empty($posted_data['inventory_quantity'])) {
            $quantity = $posted_data['inventory_quantity'];
        } else {
            $quantity = 1;
        }

        $inventory_id = isset($posted_data['booking_inventory']) ? $posted_data['booking_inventory'] : '';

        $check_inventory = array(
            'pickup_datetime' => $pickup_datetime,
            'return_datetime' => $return_datetime,
            'inventory_id'    => $inventory_id,
            'product_id'      => $product_id,
            'quantity'        => get_post_meta($inventory_id, 'quantity', true),
        );

        $rental_duration = rnb_calculate_date_difference($pickup_datetime, $return_datetime, $format = '%y:%m:%d:%h:%i');
        $available_qty = rnb_inventory_quantity_availability_check($check_inventory);

        if (!is_ajax() && $quantity > $available_qty) {
            wc_add_notice(sprintf(__('Quantity %s is not available', 'redq-rental'), $quantity), 'error');
            $this->send_ajax_failure_response();
        }

        //Start
        $request_data = [
            'pickup_date'     => isset($posted_data['pickup_date']) ? convert_to_generalized_format($posted_data['pickup_date'], $euro_format) : '',
            'pickup_time'     => isset($posted_data['pickup_time']) ? $posted_data['pickup_time'] : '',
            'dropoff_date'    => isset($posted_data['dropoff_date']) ? convert_to_generalized_format($posted_data['dropoff_date'], $euro_format) : '',
            'dropoff_time'    => isset($posted_data['dropoff_time']) ? $posted_data['dropoff_time'] : '',
            'inventory_id'    => $inventory_id,
            'product_id'      => $product_id,
            'total_qty'       => get_post_meta($inventory_id, 'quantity', true),
            'available_qty'   => $available_qty,
            'selected_qty'    => $quantity,
            'rental_duration' => $rental_duration
        ];
        $response = $this->rnb_data_validation($product_id, $request_data);

        if (!empty($response['error'])) {
            return $response;
        }
        //End


        $date_multiply = 'per_hour';
        $check_duration = explode(':', $rental_duration);
        if ($check_duration[0] > '0' || $check_duration[1] > '0' || $check_duration[2] > '0') {
            $date_multiply = 'per_day';
        }

        if ($conditional_data['single_day_booking'] === 'open') {
            $date_multiply = 'per_day';
        }

        $data['date_multiply'] = $date_multiply;


        $data['available_quantity'] = $available_qty;


        if (isset($posted_data['booking_inventory']) && !empty($posted_data['booking_inventory'])) {
            $data['booking_inventory'] = $posted_data['booking_inventory'];
        }

        // $pricing_data = redq_rental_get_pricing_data($product_id);
        $pricing_data = redq_rental_get_pricing_data($data['booking_inventory'], $product_id);

        if (isset($posted_data['quote_id']) && !empty($posted_data['quote_id'])) {
            $data['quote_id'] = $posted_data['quote_id'];
        }

        if (isset($posted_data['categories']) && !empty($posted_data['categories'])) {
            foreach ($posted_data['categories'] as $key => $value) {
                $categories = explode('|', $value);
                $payable_cat[$key]['name'] = $categories[0];
                $payable_cat[$key]['cost'] = $categories[1];
                $payable_cat[$key]['multiply'] = $categories[2];
                $payable_cat[$key]['hourly_cost'] = $categories[3];
                $payable_cat[$key]['quantity'] = $categories[4];
            }
            $data['payable_cat'] = $payable_cat;
        }

        if (isset($posted_data['extras']) && !empty($posted_data['extras'])) {
            foreach ($posted_data['extras'] as $key => $value) {
                $extras = explode('|', $value);
                $payable_resource[$key]['resource_name'] = $extras[0];
                $payable_resource[$key]['resource_cost'] = $extras[1];
                $payable_resource[$key]['cost_multiply'] = $extras[2];
                $payable_resource[$key]['resource_hourly_cost'] = $extras[3];
            }
            $data['payable_resource'] = $payable_resource;
        }

        if (isset($posted_data['security_deposites']) && !empty($posted_data['security_deposites'])) {
            foreach ($posted_data['security_deposites'] as $key => $value) {
                $extras = explode('|', $value);
                $payable_security_deposites[$key]['security_deposite_name'] = $extras[0];
                $payable_security_deposites[$key]['security_deposite_cost'] = $extras[1];
                $payable_security_deposites[$key]['cost_multiply'] = $extras[2];
                $payable_security_deposites[$key]['security_deposite_hourly_cost'] = $extras[3];
            }
            $data['payable_security_deposites'] = $payable_security_deposites;
        }

        if (isset($posted_data['additional_adults_info']) && !empty($posted_data['additional_adults_info'])) {
            $person = explode('|', $posted_data['additional_adults_info']);
            $adults_info['person_count'] = $person[0];
            $adults_info['person_cost'] = $person[1];
            $adults_info['cost_multiply'] = $person[2];
            $adults_info['person_hourly_cost'] = $person[3];

            $data['adults_info'] = $adults_info;
        }


        if (isset($posted_data['additional_childs_info']) && !empty($posted_data['additional_childs_info'])) {
            $person = explode('|', $posted_data['additional_childs_info']);
            $childs_info['person_count'] = $person[0];
            $childs_info['person_cost'] = $person[1];
            $childs_info['cost_multiply'] = $person[2];
            $childs_info['person_hourly_cost'] = $person[3];

            $data['childs_info'] = $childs_info;
        }


        if ($conditional_data['booking_layout'] === 'layout_one') {
            if (isset($posted_data['pickup_location']) && !empty($posted_data['pickup_location'])) {
                $pickup_location_split = explode('|', $posted_data['pickup_location']);
                $pickup_location['address'] = $pickup_location_split[0];
                $pickup_location['title'] = $pickup_location_split[1];
                $pickup_location['cost'] = $pickup_location_split[2];
                $data['pickup_location'] = $pickup_location;
            }

            if (isset($posted_data['dropoff_location']) && !empty($posted_data['dropoff_location'])) {
                $dropoff_location_split = explode('|', $posted_data['dropoff_location']);
                $dropoff_location['address'] = $dropoff_location_split[0];
                $dropoff_location['title'] = $dropoff_location_split[1];
                $dropoff_location['cost'] = $dropoff_location_split[2];

                $data['dropoff_location'] = $dropoff_location;
            }
        } else {
            if (isset($posted_data['pickup_location']) && !empty($posted_data['pickup_location'])) {
                $pickup_location['address'] = $posted_data['pickup_location'];
                $pickup_location['title'] = $posted_data['pickup_location'];
                $data['pickup_location'] = $pickup_location;
            }
            if (isset($posted_data['dropoff_location']) && !empty($posted_data['dropoff_location'])) {
                $dropoff_location['address'] = $posted_data['dropoff_location'];
                $dropoff_location['title'] = $posted_data['dropoff_location'];
                $data['dropoff_location'] = $dropoff_location;
            }

            if (isset($posted_data['total_distance']) && !empty($posted_data['total_distance'])) {
                $distance = explode('|', $posted_data['total_distance']);
                $total_kilos = $distance[0] ? $distance[0] : '';
                if (isset($pricing_data['distance_unit_type']) && !empty($pricing_data['distance_unit_type']) && $pricing_data['distance_unit_type'] === 'mile') {
                    $location_cost = floatval($pricing_data['perkilo_price']) * $total_kilos * 0.621;
                    $data['location_cost'] = $location_cost;
                } else {
                    $location_cost = floatval($pricing_data['perkilo_price']) * $total_kilos;
                    $data['location_cost'] = $location_cost;
                }
            }
        }


        if (isset($posted_data['pickup_date']) && !empty($posted_data['pickup_date'])) {
            $data['pickup_date'] = convert_to_generalized_format($posted_data['pickup_date'], $euro_format);
        }

        if (isset($posted_data['pickup_time']) && !empty($posted_data['pickup_time'])) {
            $data['pickup_time'] = $posted_data['pickup_time'];
        }

        if (isset($posted_data['dropoff_date']) && !empty($posted_data['dropoff_date'])) {
            $data['dropoff_date'] = convert_to_generalized_format($posted_data['dropoff_date'], $euro_format);
        }

        if (isset($posted_data['dropoff_time']) && !empty($posted_data['dropoff_time'])) {
            $data['dropoff_time'] = $posted_data['dropoff_time'];
        }

        if (isset($posted_data['inventory_quantity']) && !empty($posted_data['inventory_quantity'])) {
            $data['quantity'] = $posted_data['inventory_quantity'];
        }


        if (isset($data['pickup_date']) && !empty($data['pickup_date']) && !isset($data['dropoff_date']) && empty($data['dropoff_date'])) {
            if (!isset($data['pickup_time']) || !isset($data['dropoff_time'])) {
                $data['dropoff_date'] = $data['pickup_date'];
            } else {
                $data['dropoff_date'] = $data['pickup_date'];
            }
        }

        if (isset($data['pickup_time']) && !empty($data['pickup_time']) && !isset($data['dropoff_time']) && empty($data['dropoff_time'])) {
            $data['dropoff_time'] = $data['pickup_time'];
        }

        if (isset($data['dropoff_date']) && !empty($data['dropoff_date']) && !isset($data['pickup_date']) && empty($data['pickup_date'])) {
            if (!isset($data['pickup_time']) || !isset($data['dropoff_time'])) {
                $data['pickup_date'] = $data['dropoff_date'];
            } else {
                $data['pickup_date'] = $data['dropoff_date'];
            }
        }

        if (isset($data['dropoff_time']) && !empty($data['dropoff_time']) && !isset($data['pickup_time']) && empty($data['pickup_time'])) {
            $data['pickup_time'] = $data['dropoff_time'];
        }

        $cost_calculation = $this->calculate_cost($product_id, $data['booking_inventory'], $data, $conditional_data);

        $data['rental_days_and_costs'] = $cost_calculation;
        $data['max_hours_late'] = get_post_meta($product_id, 'redq_max_time_late', true);


        if ($conditional_data['euro_format'] === 'yes') {
            $data['pickup_date'] = str_replace('.', '/', $data['pickup_date']);
        }

        if ($conditional_data['euro_format'] === 'yes') {
            $data['dropoff_date'] = str_replace('.', '/', $data['dropoff_date']);
        }

        return $data;
    }


    /**
     * rnb_data_validation
     *
     * @param $product_id
     * @param mixed $request_data
     *
     * @return void
     */
    public function rnb_data_validation($product_id, $request_data)
    {
        $labels = redq_rental_get_settings($product_id, 'labels', array('notice'));
        $conditional_data = redq_rental_get_settings($product_id, 'conditions');

        $conditional_data = $conditional_data['conditions'];
        $labels = $labels['labels'];

        $response['error'] = [];
        $translated_strings = rnb_get_translated_strings();

        if (empty($request_data['dropoff_date'])) {
            $request_data['dropoff_date'] = $request_data['pickup_date'];
        }

        if (empty($request_data['dropoff_time'])) {
            $request_data['dropoff_time'] = $request_data['pickup_time'];
        }


        if (empty($request_data['inventory_id'])) {
            $response['error'][] = sprintf(__('Sorry! product has no inventory', 'redq-rental'));
            return $response;
        }

        if ($request_data['pickup_date'] && $request_data['dropoff_date']) {
            //Valid duration
            if (strtotime($request_data['pickup_date'] . ' ' . $request_data['pickup_time']) > strtotime($request_data['dropoff_date'] . ' ' . $request_data['dropoff_time'])) {
                $response['error'][] = sprintf(__('%s', 'redq-rental'), $labels['invalid_range_notice']);
            }

            if (strtotime($request_data['pickup_date'] . ' ' . $request_data['pickup_time']) === strtotime($request_data['dropoff_date'] . ' ' . $request_data['dropoff_time']) && $conditional_data['single_day_booking'] === 'closed') {
                $response['error'][] = sprintf(__('%s', 'redq-rental'), $labels['invalid_range_notice']);
            }

            //Min max validation
            $rental_duration = $this->calculate_rental_days($request_data, $conditional_data);

            if (!empty($conditional_data['min_book_days']) && $rental_duration['days'] < $conditional_data['min_book_days']) {
                $response['error'][] = sprintf(__('%s', 'redq-rental'), $labels['min_day_notice']);
            }

            if (!empty($conditional_data['max_book_days']) && $rental_duration['days'] >= $conditional_data['max_book_days'] &&  $rental_duration['extra_hours'] > 0) {
                $response['error'][] = sprintf(__('%s', 'redq-rental'), $labels['max_day_notice']);
            }
        }

        //available quantity
        $cart_quantity = rnb_check_cart_data($product_id, $request_data);
        $request_data['available_qty'] -= $cart_quantity;

        if ($request_data['selected_qty'] > $request_data['available_qty']) {
            $response['error'][] = sprintf(__('%s', 'redq-rental'), $labels['quantity_notice']);
        }

        return $response;
    }


    /**
     * Return rental cost and days
     *
     * @param $product_id
     * @param $inventory_id
     * @param $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_cost($product_id, $inventory_id, $data, $conditional_data)
    {
        $display_options = redq_rental_get_settings($product_id, 'display')['display'];

        $payable_person = array();

        $pricing_data = redq_rental_get_pricing_data($inventory_id, $product_id);
        $calculate_cost_and_day = array();

        $location_cost = isset($data['location_cost']) ? $data['location_cost'] : 0;
        $pickup_cost = isset($data['pickup_location']['cost']) ? $data['pickup_location']['cost'] : 0;
        $dropoff_cost = isset($data['dropoff_location']['cost']) ? $data['dropoff_location']['cost'] : 0;
        $payable_cat = isset($data['payable_cat']) ? $data['payable_cat'] : array();
        $payable_resource = isset($data['payable_resource']) ? $data['payable_resource'] : array();
        $payable_security_deposites = isset($data['payable_security_deposites']) ? $data['payable_security_deposites'] : array();
        $adults_info = isset($data['adults_info']) ? $data['adults_info'] : [];
        $childs_info = isset($data['childs_info']) ? $data['childs_info'] : array();
        $payable_person['adults'] = $adults_info;
        $payable_person['childs'] = $childs_info;

        $pickup_date = isset($data['pickup_date']) ? $data['pickup_date'] : '';
        $pickup_time = isset($data['pickup_time']) ? $data['pickup_time'] : '';
        $dropoff_date = isset($data['dropoff_date']) ? $data['dropoff_date'] : '';
        $dropoff_time = isset($data['dropoff_time']) ? $data['dropoff_time'] : '';

        $price_discount = isset($pricing_data['price_discount']) && $pricing_data['price_discount'] ? $pricing_data['price_discount'] : array();

        $days = $this->calculate_rental_days($data, $conditional_data);
        $pricing_type = $pricing_data['pricing_type'];

        $calculate_cost_and_day['pricing_type'] = $pricing_type;
        $calculate_cost_and_day['flat_hours'] = $days['flat_hours'];
        $calculate_cost_and_day['days'] = $days['days'];
        $calculate_cost_and_day['hours'] = $days['hours'];
        $calculate_cost_and_day['booked_dates'] = $days['booked_dates'];

        $booking_args = array(
            'pricing_data'               => $pricing_data,
            'extra_hours_payment'        => $conditional_data['pay_extra_hours'],
            'pickup_date'                => $pickup_date,
            'durations'                  => $days,
            'payable_cat'                => $payable_cat,
            'payable_resource'           => $payable_resource,
            'payable_person'             => $payable_person,
            'payable_security_deposites' => $payable_security_deposites,
            'pickup_cost'                => $pickup_cost,
            'dropoff_cost'               => $dropoff_cost,
            'location_cost'              => $location_cost
        );


        switch ($pricing_type) {
            case 'flat_hours':
                $price_breakdown = $this->calculate_flat_hours_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data);
                break;
            case 'general_pricing':
                $price_breakdown = $this->calculate_general_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data);
                break;
            case 'daily_pricing':
                $price_breakdown = $this->calculate_daily_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data);
                break;
            case 'monthly_pricing':
                $price_breakdown = $this->calculate_monthly_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data);
                break;
            case 'days_range':
                $price_breakdown = $this->calculate_day_ranges_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data);
                break;
        }

        $pre_payment_percentage = get_option('rnb_instance_payment');
        if (empty($pre_payment_percentage) || $display_options['instance_payment'] === 'closed') {
            $pre_payment_percentage = 100;
        }

        $cost = $price_breakdown['deposit_free_total'];

        $cost = apply_filters('before_final_booking_cost', $cost, $product_id, $inventory_id, $data, $conditional_data);

        $instance_payment = ($cost * $pre_payment_percentage) / 100;
        $due_payment = $cost - $instance_payment;

        $calculate_cost_and_day['price_breakdown'] = $price_breakdown;
        $calculate_cost_and_day['cost'] = $instance_payment;
        $calculate_cost_and_day['instant_pay'] = $pre_payment_percentage;
        $calculate_cost_and_day['due_payment'] = $due_payment;

        return $calculate_cost_and_day;
    }


    /**
     * Calculate total rental days
     *
     * @param array $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_rental_days($data, $conditional_data)
    {
        $durations = array();
        $choose_euro_format = $conditional_data['euro_format'];
        $max_hours_late = floatval($conditional_data['max_time_late']);
        $output_format = $conditional_data['date_format'];
        $enable_single_day_time_booking = $conditional_data['single_day_booking'];

        $pickup_date = isset($data['pickup_date']) ? $data['pickup_date'] : '';
        $dropoff_date = isset($data['dropoff_date']) ? $data['dropoff_date'] : '';
        $pickup_time = isset($data['pickup_time']) ? $data['pickup_time'] : '';
        $dropoff_time = isset($data['dropoff_time']) ? $data['dropoff_time'] : '';

        $formated_pickup_time = $pickup_time ? date("H:i", strtotime($pickup_time)) : '0:00';
        $formated_dropoff_time = $dropoff_time ? date("H:i", strtotime($dropoff_time)) : ($enable_single_day_time_booking === 'open' ? '24:00' : '0:00');

        $pickup_date_time = strtotime("$pickup_date $formated_pickup_time");
        $dropoff_date_time = strtotime("$dropoff_date $formated_dropoff_time");

        $hours = abs($pickup_date_time - $dropoff_date_time) / (60 * 60);

        $total_hours = 0;
        $extra_hours = $hours % 24;

        if ($hours < 24) {
            if ($enable_single_day_time_booking === 'open') {
                $hours = 24;
                $days = 1;
                $extra_hours = 0;
            } else {
                $days = 0;
            }
            $total_hours = ceil($hours);
        } else {
            $days = intval($hours / 24);

            $days = $conditional_data['pay_extra_hours'] !== 'yes' && $extra_hours > $max_hours_late ? $days + 1 : $days;
            $extra_hours = $extra_hours > $max_hours_late ? $extra_hours - $max_hours_late : 0;
        }

        if ($conditional_data['is_not_a_full_rental_day'] === 'yes' && $pickup_date !== $dropoff_date) {
            $opening_time = date("H:i", strtotime($conditional_data['opening_time']));
            $closing_time = date("H:i", strtotime($conditional_data['closing_time']));
            $pickup_date_exploded = explode('-', $pickup_date);
            $dropoff_date_exploded = explode('-', $dropoff_date);

            // Is same month ?
            $end_of_first_day = strtotime("$pickup_date 23:59");
            $start_of_last_day = strtotime("$dropoff_date 00:00");
            $days_between = floor(abs($end_of_first_day - $start_of_last_day) / (60 * 60 * 24));

            //First day
            $first_day_closing_date_time = strtotime("$pickup_date $closing_time");
            $first_day_hours = abs($pickup_date_time - $first_day_closing_date_time) / (60 * 60);

            //Last day
            $last_day_opening_date_time = strtotime("$dropoff_date $opening_time");
            $last_day_hours = abs($dropoff_date_time - $last_day_opening_date_time) / (60 * 60);
            
            $durations['days_between'] = $days_between;
            $durations['first_day_hours'] = $first_day_hours;
            $durations['last_day_hours'] = $last_day_hours;
        }

        $booked_dates = array();
        $current = strtotime($pickup_date);
        $count = 0;

        while ($count < $days) {
            $day = strtotime('+' . $count . ' day', $current);
            $booked_dates['formatted'][] = date($output_format, $day);
            $booked_dates['saved'][] = date('Y-m-d', $day);
            $booked_dates['iso'][] = $day;
            $count++;
        }

        $durations['flat_hours'] = $hours;
        $durations['days'] = $days;
        $durations['hours'] = $total_hours;
        $durations['extra_hours'] = $extra_hours;
        $durations['booked_dates'] = $booked_dates;

        return $durations;
    }

    /**
     * Calculate hourly pricing
     *
     * @param $hours
     * @param $pricing_data
     * @return string
     */
    public function calculate_hourly_price($hours, $pricing_data)
    {
        $cost = 0;
        $flag = 0;

        if ($pricing_data['hourly_pricing_type'] === 'hourly_general') {
            $cost = (int) $hours * (float) $pricing_data['hourly_general'];
        }

        if ($pricing_data['hourly_pricing_type'] === 'hourly_range') {
            $hourly_ranges_pricing_plan = $pricing_data['hourly_range'];
            foreach ($hourly_ranges_pricing_plan as $key => $value) {
                if ($flag === 0) {
                    if ($value['cost_applicable'] === 'per_hour') {
                        if ((int) $value['min_hours'] <= (int) $hours && (int) $value['max_hours'] >= (int) $hours) {
                            $cost = (float) $value['range_cost'] * (int) $hours;
                            $flag = 1;
                        }
                    } else if ((int) $value['min_hours'] <= (int) $hours && (int) $value['max_hours'] >= (int) $hours) {
                        $cost = (float) $value['range_cost'];
                        $flag = 1;
                    }
                }
            }
        }

        return $cost;
    }

    /**
     * Calculate Flat hours pricing plan's cost
     *
     * @param $booking_args
     * @param $product_id
     * @param $inventory_id
     * @param $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_flat_hours_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data)
    {
        extract($booking_args);

        $price_breakdown = [];
        $duration_cost = 0;
        $total = 0;
        $deposit_free_total = 0;
        $total_hours = ceil($durations['flat_hours']);

        $duration_cost = apply_filters('before_payable_security_deposites', $this->calculate_hourly_price($total_hours, $pricing_data), $product_id, $inventory_id, $data, $conditional_data);

        $price_breakdown['duration_breakdown']['hourly'] = $duration_cost;
        $price_breakdown['duration_total'] = $duration_cost;

        $extras_breakdown = $this->calculate_hourly_extras_cost($duration_cost, $total_hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, true, $product_id, $inventory_id, $data, $conditional_data);
        $price_breakdown['extras_hour_breakdown'] = $extras_breakdown;
        $price_breakdown['extras_total'] = isset($extras_breakdown['non_refundable']) ? array_sum(array_values($extras_breakdown['non_refundable'])) : 0;
        $price_breakdown['deposit_total'] = isset($extras_breakdown['refundable']) ? array_sum(array_values($extras_breakdown['refundable'])) : 0;

        foreach ($price_breakdown as $key => $price) {
            if ($key === 'extras_hour_breakdown' || $key === 'duration_breakdown') {
                continue;
            }

            if ($key !== 'discount_total') {
                $total += $price;
            } else {
                $total -= $price;
            }

            if ($key === 'deposit_total') {
                continue;
            }

            if ($key !== 'discount_total') {
                $deposit_free_total += $price;
            } else {
                $deposit_free_total -= $price;
            }
        }
        // $deposit_free_total = apply_filters('before_payable_security_deposites', $deposit_free_total, $product_id, $inventory_id, $data, $conditional_data);

        $price_breakdown['deposit_free_total'] = $deposit_free_total;
        $price_breakdown['total'] = $total;

        return $price_breakdown;
    }

    /**
     * Calculate general pricing plan's cost
     *
     * @param $booking_args
     * @param $product_id
     * @param $inventory_id
     * @param $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_general_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data)
    {
        extract($booking_args);

        $price_breakdown = [];
        $days = $durations['days'];
        $hours = $durations['hours'];
        $extra_hours = $durations['extra_hours'];

        $general_pricing = $pricing_data['general_pricing'];
        $price_discount = isset($pricing_data['price_discount']) ? $pricing_data['price_discount'] : [];

        if ($days > 0) {
            $rental_days = $days;
            $day_cost = intval($rental_days) * floatval($general_pricing);

            $price_breakdown['duration_breakdown']['daily'] = $day_cost;
            $price_breakdown['duration_total'] = $day_cost;

            $after_discount = $this->calculate_price_discount($day_cost, $price_discount, $rental_days);

            $price_breakdown['discount_total'] = $day_cost - $after_discount;

            $extras_breakdown = $this->calculate_extras_cost($day_cost, $rental_days, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, $product_id, $inventory_id, $data, $conditional_data);
            $price_breakdown['extras_breakdown'] = $extras_breakdown;
            $price_breakdown['extras_total'] = isset($extras_breakdown['non_refundable']) ? array_sum(array_values($extras_breakdown['non_refundable'])) : 0;
            $price_breakdown['deposit_total'] = isset($extras_breakdown['refundable']) ? array_sum(array_values($extras_breakdown['refundable'])) : 0;

            if ($extra_hours_payment === 'yes' && $extra_hours > 0) {
                $hour_cost = $this->calculate_hourly_price($extra_hours, $pricing_data);
                $price_breakdown['duration_breakdown']['hourly'] = $hour_cost;
                $price_breakdown['duration_total'] += $hour_cost;

                $extras_hour_breakdown = $this->calculate_hourly_extras_cost($hour_cost, $extra_hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, false, $product_id, $inventory_id, $data, $conditional_data);
                $price_breakdown['extras_hour_breakdown'] = $extras_hour_breakdown;
                $price_breakdown['extras_total'] += isset($extras_hour_breakdown['non_refundable']) ? array_sum(array_values($extras_hour_breakdown['non_refundable'])) : 0;
                $price_breakdown['deposit_total'] += isset($extras_hour_breakdown['refundable']) ? array_sum(array_values($extras_hour_breakdown['refundable'])) : 0;
            }
            $price_breakdown['duration_total'] = apply_filters('before_payable_security_deposites', $price_breakdown['duration_total'], $product_id, $inventory_id, $data, $conditional_data);
        } else {
            $duration_total = apply_filters('before_payable_security_deposites', $this->calculate_hourly_price($hours, $pricing_data), $product_id, $inventory_id, $data, $conditional_data);
            $price_breakdown['duration_total'] = $duration_total;

            $extras_hour_breakdown = $this->calculate_hourly_extras_cost($duration_total, $hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, true, $product_id, $inventory_id, $data, $conditional_data);

            $price_breakdown['extras_hour_breakdown'] = $extras_hour_breakdown;
            $price_breakdown['extras_total'] = isset($extras_hour_breakdown['non_refundable']) ? array_sum(array_values($extras_hour_breakdown['non_refundable'])) : 0;
            $price_breakdown['deposit_total'] = isset($extras_hour_breakdown['refundable']) ? array_sum(array_values($extras_hour_breakdown['refundable'])) : 0;
        }

        $total = 0;
        $deposit_free_total = 0;
        foreach ($price_breakdown as $key => $price) {
            if ($key === 'extras_breakdown' || $key === 'extras_hour_breakdown' || $key === 'duration_breakdown') {
                continue;
            }

            if ($key !== 'discount_total') {
                $total += $price;
            } else {
                $total -= $price;
            }

            if ($key === 'deposit_total') {
                continue;
            }

            if ($key !== 'discount_total') {
                $deposit_free_total += $price;
            } else {
                $deposit_free_total -= $price;
            }
        }
        // $deposit_free_total = apply_filters('before_payable_security_deposites', $deposit_free_total, $product_id, $inventory_id, $data, $conditional_data);

        $price_breakdown['deposit_free_total'] = $deposit_free_total;
        $price_breakdown['total'] = $total;

        return $price_breakdown;
    }


    /**
     * Calculate daily pricing plan's cost
     *
     * @param $booking_args
     * @param $product_id
     * @param $inventory_id
     * @param $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_daily_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data)
    {
        extract($booking_args);

        $price_breakdown = [];
        $day_cost = 0;
        $days = $durations['days'];
        $hours = $durations['hours'];
        $extra_hours = $durations['extra_hours'];

        $daily_pricing_plan = $pricing_data['daily_pricing'];
        $price_discount = isset($pricing_data['price_discount']) ? $pricing_data['price_discount'] : [];

        if ($days > 0) {
            $rental_days = $days;
            for ($i = 0; $i < intval($rental_days); $i++) {
                $day = strtolower(date("l", strtotime($pickup_date . " +$i day")));
                $day_cost = $daily_pricing_plan[$day] != '' ? $day_cost + floatval($daily_pricing_plan[$day]) : $day_cost + 0;
            }
            $price_breakdown['duration_breakdown']['daily'] = $day_cost;
            $price_breakdown['duration_total'] = $day_cost;

            $after_discount = $this->calculate_price_discount($day_cost, $price_discount, $rental_days);
            $price_breakdown['discount_total'] = $day_cost - $after_discount;

            $extras_breakdown = $this->calculate_extras_cost($day_cost, $rental_days, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, $product_id, $inventory_id, $data, $conditional_data);
            $price_breakdown['extras_breakdown'] = $extras_breakdown;
            $price_breakdown['extras_total'] = array_sum(array_values($extras_breakdown['non_refundable']));
            $price_breakdown['deposit_total'] = isset($extras_breakdown['refundable']) ? array_sum(array_values($extras_breakdown['refundable'])) : 0;

            if ($extra_hours_payment === 'yes' && $extra_hours > 0) {
                $hour_cost = $this->calculate_hourly_price($extra_hours, $pricing_data);
                $price_breakdown['duration_breakdown']['hourly'] = $hour_cost;
                $price_breakdown['duration_total'] += $hour_cost;

                $extras_hour_breakdown = $this->calculate_hourly_extras_cost($hour_cost, $extra_hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, false, $product_id, $inventory_id, $data, $conditional_data);
                $price_breakdown['extras_hour_breakdown'] = $extras_hour_breakdown;
                $price_breakdown['extras_total'] += isset($extras_hour_breakdown['non_refundable']) ? array_sum(array_values($extras_hour_breakdown['non_refundable'])) : 0;
                $price_breakdown['deposit_total'] += isset($extras_hour_breakdown['refundable']) ? array_sum(array_values($extras_hour_breakdown['refundable'])) : 0;
            }
            $price_breakdown['duration_total'] = apply_filters('before_payable_security_deposites', $price_breakdown['duration_total'], $product_id, $inventory_id, $data, $conditional_data);
        } else {
            $duration_total = apply_filters('before_payable_security_deposites', $this->calculate_hourly_price($hours, $pricing_data), $product_id, $inventory_id, $data, $conditional_data);
            $price_breakdown['duration_total'] = $duration_total;

            $extras_hour_breakdown = $this->calculate_hourly_extras_cost($duration_total, $hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, true, $product_id, $inventory_id, $data, $conditional_data);

            $price_breakdown['extras_hour_breakdown'] = $extras_hour_breakdown;
            $price_breakdown['extras_total'] = isset($extras_hour_breakdown['non_refundable']) ? array_sum(array_values($extras_hour_breakdown['non_refundable'])) : 0;
            $price_breakdown['deposit_total'] = isset($extras_hour_breakdown['refundable']) ? array_sum(array_values($extras_hour_breakdown['refundable'])) : 0;
        }

        $total = 0;
        $deposit_free_total = 0;
        foreach ($price_breakdown as $key => $price) {
            if ($key === 'extras_breakdown' || $key === 'extras_hour_breakdown' || $key === 'duration_breakdown') {
                continue;
            }

            if ($key !== 'discount_total') {
                $total += $price;
            } else {
                $total -= $price;
            }

            if ($key === 'deposit_total') {
                continue;
            }

            if ($key !== 'discount_total') {
                $deposit_free_total += $price;
            } else {
                $deposit_free_total -= $price;
            }
        }
        // $deposit_free_total = apply_filters('before_payable_security_deposites', $deposit_free_total, $product_id, $inventory_id, $data, $conditional_data);

        $price_breakdown['deposit_free_total'] = $deposit_free_total;
        $price_breakdown['total'] = $total;

        return $price_breakdown;
    }


    /**
     * Calculate monthly pricing plan's cost
     *
     * @param $booking_args
     * @param $product_id
     * @param $inventory_id
     * @param $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_monthly_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data)
    {
        extract($booking_args);

        $price_breakdown = [];
        $day_cost = 0;
        $days = $durations['days'];
        $hours = $durations['hours'];
        $extra_hours = $durations['extra_hours'];

        $monthly_pricing_plan = $pricing_data['monthly_pricing'];
        $price_discount = isset($pricing_data['price_discount']) ? $pricing_data['price_discount'] : [];

        if ($days > 0) {
            $rental_days = $days;
            for ($i = 0; $i < intval($rental_days); $i++) {
                $month = strtolower(date("F", strtotime($pickup_date . " +$i day")));
                $day_cost = $monthly_pricing_plan[$month] != '' ? $day_cost + floatval($monthly_pricing_plan[$month]) : $day_cost + 0;
            }

            $price_breakdown['duration_breakdown']['daily'] = $day_cost;
            $price_breakdown['duration_total'] = $day_cost;

            $after_discount = $this->calculate_price_discount($day_cost, $price_discount, $rental_days);
            $price_breakdown['discount_total'] = $day_cost - $after_discount;

            $extras_breakdown = $this->calculate_extras_cost($day_cost, $rental_days, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, $product_id, $inventory_id, $data, $conditional_data);
            $price_breakdown['extras_breakdown'] = $extras_breakdown;
            $price_breakdown['extras_total'] = array_sum(array_values($extras_breakdown['non_refundable']));
            $price_breakdown['deposit_total'] = isset($extras_breakdown['refundable']) ? array_sum(array_values($extras_breakdown['refundable'])) : 0;

            if ($extra_hours_payment === 'yes' && $extra_hours > 0) {
                $hour_cost = $this->calculate_hourly_price($extra_hours, $pricing_data);
                $price_breakdown['duration_breakdown']['hourly'] = $hour_cost;
                $price_breakdown['duration_total'] += $hour_cost;

                $extras_hour_breakdown = $this->calculate_hourly_extras_cost($hour_cost, $extra_hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, false, $product_id, $inventory_id, $data, $conditional_data);
                $price_breakdown['extras_hour_breakdown'] = $extras_hour_breakdown;
                $price_breakdown['extras_total'] += isset($extras_hour_breakdown['non_refundable']) ? array_sum(array_values($extras_hour_breakdown['non_refundable'])) : 0;
                $price_breakdown['deposit_total'] += isset($extras_hour_breakdown['refundable']) ? array_sum(array_values($extras_hour_breakdown['refundable'])) : 0;
            }
            $price_breakdown['deposit_total'] = apply_filters('before_payable_security_deposites', $price_breakdown['deposit_total'], $product_id, $inventory_id, $data, $conditional_data);
        } else {
            $duration_total = apply_filters('before_payable_security_deposites', $this->calculate_hourly_price($hours, $pricing_data), $product_id, $inventory_id, $data, $conditional_data);
            $price_breakdown['duration_total'] = $duration_total;

            $extras_hour_breakdown = $this->calculate_hourly_extras_cost($duration_total, $hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, true, $product_id, $inventory_id, $data, $conditional_data);

            $price_breakdown['extras_hour_breakdown'] = $extras_hour_breakdown;
            $price_breakdown['extras_total'] = isset($extras_hour_breakdown['non_refundable']) ? array_sum(array_values($extras_hour_breakdown['non_refundable'])) : 0;
            $price_breakdown['deposit_total'] = isset($extras_hour_breakdown['refundable']) ? array_sum(array_values($extras_hour_breakdown['refundable'])) : 0;
        }

        $price_breakdown['duration_total'] = apply_filters('before_payable_security_deposites', $price_breakdown['duration_total'], $product_id, $inventory_id, $data, $conditional_data);

        $total = 0;
        $deposit_free_total = 0;
        foreach ($price_breakdown as $key => $price) {
            if ($key === 'extras_breakdown' || $key === 'extras_hour_breakdown' || $key === 'duration_breakdown') {
                continue;
            }

            if ($key !== 'discount_total') {
                $total += $price;
            } else {
                $total -= $price;
            }

            if ($key === 'deposit_total') {
                continue;
            }

            if ($key !== 'discount_total') {
                $deposit_free_total += $price;
            } else {
                $deposit_free_total -= $price;
            }
        }
        // $deposit_free_total = apply_filters('before_payable_security_deposites', $deposit_free_total, $product_id, $inventory_id, $data, $conditional_data);

        $price_breakdown['deposit_free_total'] = $deposit_free_total;
        $price_breakdown['total'] = $total;

        return $price_breakdown;
    }


    /**
     * Calculate day ranges plan's cost
     *
     * @param $booking_args
     * @param $product_id
     * @param $inventory_id
     * @param $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_day_ranges_pricing_plan_cost($booking_args, $product_id, $inventory_id, $data, $conditional_data)
    {
        extract($booking_args);

        $price_breakdown = [];
        $cost = 0;
        $flag = 0;
        $day_cost = 0;
        $hour_cost = 0;
        $days = $durations['days'];
        $hours = $durations['hours'];
        $extra_hours = $durations['extra_hours'];

        $day_ranges_pricing_plan = $pricing_data['days_range'];
        $price_discount = isset($pricing_data['price_discount']) ? $pricing_data['price_discount'] : [];

        if ($days > 0) {
            if ($conditional_data['is_not_a_full_rental_day'] === 'yes') {
                $days_between = $durations['days_between'];
                $first_day_hours = $durations['first_day_hours'];
                $last_day_hours = $durations['last_day_hours'];
                $day_between_cost = 0;
                $first_day_cost = 0;
                $last_day_cost = 0;
    
                if ($first_day_hours > 0) {
                    $first_day_cost = $this->calculate_hourly_price($first_day_hours, $pricing_data);
                     
                    if ($first_day_cost === 0 && $conditional_data['is_missing_time_slot_as_a_day'] === 'yes') {
                        foreach ($pricing_data['days_range'] as $key => $value) {
                            if ((int) $value['min_days'] <= (int) 1 && (int) $value['max_days'] >= (int) 1) {
                                $days_between += 1;
                                break;
                            }
                        }
                    }
                }

                if ($last_day_hours > 0) {
                    $last_day_cost = $this->calculate_hourly_price($last_day_hours, $pricing_data);

                    if ($last_day_cost === 0 && $conditional_data['is_missing_time_slot_as_a_day'] === 'yes') {
                        foreach ($pricing_data['days_range'] as $key => $value) {
                            if ((int) $value['min_days'] <= (int) 1 && (int) $value['max_days'] >= (int) 1) {
                                $days_between += 1;
                                break;
                            }
                        }
                    }
                }
        
                if ($days_between > 0) {
                    foreach ($day_ranges_pricing_plan as $key => $value) {
                        if ($flag === 0) {
                            if ((int) $value['min_days'] <= (int) $days_between && (int) $value['max_days'] >= (int) $days_between) {
                                $day_between_cost = (float) $value['range_cost'];
                                $flag = 1;
                            }
                        }
                    }
                }

                $day_cost = $day_between_cost + $first_day_cost + $last_day_cost;
                // var_dump("$day_between_cost + $first_day_cost + $last_day_cost = $day_cost"); die();
            } else {
                $rental_days = $days;
                foreach ($day_ranges_pricing_plan as $key => $value) {
                    if ($flag === 0) {
                        if ($value['cost_applicable'] === 'per_day') {
                            if ((int) $value['min_days'] <= (int) $rental_days && (int) $value['max_days'] >= (int) $rental_days) {
                                $day_cost = (float) $value['range_cost'] * (int) $rental_days;
                                $flag = 1;
                            }
                        } else if ((int) $value['min_days'] <= (int) $rental_days && (int) $value['max_days'] >= (int) $rental_days) {
                            $day_cost = (float) $value['range_cost'];
                            $flag = 1;
                        }
                    }
                }
            }

            $price_breakdown['duration_breakdown']['daily'] = $day_cost;
            $price_breakdown['duration_total'] = $day_cost;

            $after_discount = $this->calculate_price_discount($day_cost, $price_discount, $rental_days);
            $price_breakdown['discount_total'] = $day_cost - $after_discount;

            $extras_breakdown = $this->calculate_extras_cost($day_cost, $rental_days, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, false, $product_id, $inventory_id, $data, $conditional_data);
            $price_breakdown['extras_breakdown'] = $extras_breakdown;
            $price_breakdown['extras_total'] = array_sum(array_values($extras_breakdown['non_refundable']));
            $price_breakdown['deposit_total'] = isset($extras_breakdown['refundable']) ? array_sum(array_values($extras_breakdown['refundable'])) : 0;

            if ($extra_hours_payment === 'yes' && $extra_hours > 0) {
                $hour_cost = $this->calculate_hourly_price($extra_hours, $pricing_data);
                $price_breakdown['duration_breakdown']['hourly'] = $hour_cost;
                $price_breakdown['duration_total'] += $hour_cost;

                $extras_hour_breakdown = $this->calculate_hourly_extras_cost($hour_cost, $extra_hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, false, $product_id, $inventory_id, $data, $conditional_data);
                $price_breakdown['extras_hour_breakdown'] = $extras_hour_breakdown;
                $price_breakdown['extras_total'] += isset($extras_hour_breakdown['non_refundable']) ? array_sum(array_values($extras_hour_breakdown['non_refundable'])) : 0;
                $price_breakdown['deposit_total'] += isset($extras_hour_breakdown['refundable']) ? array_sum(array_values($extras_hour_breakdown['refundable'])) : 0;
            }
            $price_breakdown['duration_total'] = apply_filters('before_payable_security_deposites', $price_breakdown['duration_total'], $product_id, $inventory_id, $data, $conditional_data);
        } else {
            $hour_cost = $this->calculate_hourly_price($hours, $pricing_data);

            if ($hour_cost === 0 && $conditional_data['is_missing_time_slot_as_a_day'] === 'yes') {
                foreach ($pricing_data['days_range'] as $key => $value) {
                    if ((int) $value['min_days'] <= (int) 1 && (int) $value['max_days'] >= (int) 1) {
                        $hour_cost = $value['range_cost'];
                        break;
                    }
                }
            }

            $duration_total = apply_filters('before_payable_security_deposites', $hour_cost, $product_id, $inventory_id, $data, $conditional_data);
            $price_breakdown['duration_total'] = $duration_total;

            $extras_hour_breakdown = $this->calculate_hourly_extras_cost($duration_total, $hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, true, $product_id, $inventory_id, $data, $conditional_data);

            $price_breakdown['extras_hour_breakdown'] = $extras_hour_breakdown;
            $price_breakdown['extras_total'] = isset($extras_hour_breakdown['non_refundable']) ? array_sum(array_values($extras_hour_breakdown['non_refundable'])) : 0;
            $price_breakdown['deposit_total'] = isset($extras_hour_breakdown['refundable']) ? array_sum(array_values($extras_hour_breakdown['refundable'])) : 0;
        }

        $total = 0;
        $deposit_free_total = 0;
        foreach ($price_breakdown as $key => $price) {
            if ($key === 'extras_breakdown' || $key === 'extras_hour_breakdown' || $key === 'duration_breakdown') {
                continue;
            }

            if ($key !== 'discount_total') {
                $total += $price;
            } else {
                $total -= $price;
            }

            if ($key === 'deposit_total') {
                continue;
            }

            if ($key !== 'discount_total') {
                $deposit_free_total += $price;
            } else {
                $deposit_free_total -= $price;
            }
        }
        // $deposit_free_total = apply_filters('before_payable_security_deposites', $deposit_free_total, $product_id, $inventory_id, $data, $conditional_data);

        $price_breakdown['deposit_free_total'] = $deposit_free_total;
        $price_breakdown['total'] = $total;

        return $price_breakdown;
    }


    /**
     * Calculate price discount
     *
     * @param string $cost , array $price_discount, string $days
     * @param $price_discount
     * @param $days
     * @return string
     */
    public function calculate_price_discount($cost, $price_discount, $days)
    {
        $flag = 0;

        foreach ($price_discount as $key => $value) {
            if (($flag === 0) && (int) $value['min_days'] <= (int) $days && (int) $value['max_days'] >= (int) $days) {
                $discount_type = $value['discount_type'];
                $discount_amount = $value['discount_amount'];
                $flag = 1;
            }
        }

        if (isset($discount_type) && !empty($discount_type) && isset($discount_amount) && !empty($discount_amount)) {
            if ($discount_type === 'percentage') {
                $cost = $cost - ($cost * $discount_amount) / 100;
            } else {
                $cost = $cost - $discount_amount;
            }
        }

        return $cost;
    }


    /**
     * Calculate resource and person cost
     *
     * @param $cost
     * @param $days
     * @param $payable_cat
     * @param $payable_resource
     * @param $payable_person
     * @param $payable_security_deposites
     * @param $pickup_cost
     * @param $dropoff_cost
     * @param $location_cost
     * @param $product_id
     * @param $inventory_id
     * @param $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_extras_cost($cost, $days, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, $product_id, $inventory_id, $data, $conditional_data)
    {
        $breakdown = [];

        $location_total = 0;
        $category_total = 0;
        $resource_total = 0;
        $adult_total = 0;
        $child_total = 0;
        $deposit_total = 0;

        if (isset($pickup_cost) && !empty($pickup_cost)) {
            $location_total = (float) $location_total + (float) $pickup_cost;
            $breakdown['details_breakdown']['pickup_location_cost'] = (float) $pickup_cost;
        }

        if (isset($dropoff_cost) && !empty($dropoff_cost)) {
            $location_total = (float) $location_total + (float) $dropoff_cost;
            $breakdown['details_breakdown']['return_location_cost'] = (float) $dropoff_cost;
        }

        if (isset($location_cost) && !empty($location_cost)) {
            $location_total = (float) $location_total + (float) $location_cost;
            $breakdown['details_breakdown']['kilometer_cost'] = floatval($location_cost);
        }

        $breakdown['non_refundable']['location_total'] = $location_total;

        if (isset($payable_cat) && count($payable_cat) !== 0) {
            foreach ($payable_cat as $key => $value) {
                if ($value['multiply'] === 'per_day') {
                    $category_total = (float) $category_total + (int) $value['quantity'] * (int) $days * (float) $value['cost'];
                } else {
                    $category_total = (float) $category_total + (int) $value['quantity'] * (float) $value['cost'];
                }
            }
            $breakdown['non_refundable']['category_total'] = $category_total;
            $breakdown['details_breakdown']['category_cost'] = $category_total;
        }

        if (isset($payable_resource) && count($payable_resource) !== 0) {
            foreach ($payable_resource as $key => $value) {
                if ($value['cost_multiply'] === 'per_day') {
                    $resource_total = floatval($resource_total) + intval($days) * floatval($value['resource_cost']);
                } else {
                    $resource_total = floatval($resource_total) + floatval($value['resource_cost']);
                }
            }
            $breakdown['non_refundable']['resource_total'] = $resource_total;
            $breakdown['details_breakdown']['resource_cost'] = $resource_total;
        }

        $adults = $payable_person['adults'];
        $childs = $payable_person['childs'];

        if (isset($adults) && count($adults) !== 0) {
            if ($adults['cost_multiply'] === 'per_day') {
                $adult_total = (float) $adult_total + (int) $days * (float) $adults['person_cost'];
            } else {
                $adult_total = (float) $adult_total + (float) $adults['person_cost'];
            }
            $breakdown['non_refundable']['adult_total'] = $adult_total;
            $breakdown['details_breakdown']['adult_cost'] = $adult_total;
        }

        if (isset($childs) && count($childs) !== 0) {
            if ($childs['cost_multiply'] === 'per_day') {
                $child_total = (float) $child_total + (int) $days * (float) $childs['person_cost'];
            } else {
                $child_total = (float) $child_total + (float) $childs['person_cost'];
            }
            $breakdown['non_refundable']['child_total'] = $child_total;
            $breakdown['details_breakdown']['child_cost'] = $child_total;
        }

        if (isset($payable_security_deposites) && count($payable_security_deposites) !== 0) {
            foreach ($payable_security_deposites as $key => $value) {
                if ($value['cost_multiply'] === 'per_day') {
                    $deposit_total = (float) $deposit_total + (int) $days * (float) $value['security_deposite_cost'];
                } else {
                    $deposit_total = (float) $deposit_total + (float) $value['security_deposite_cost'];
                }
            }
            $breakdown['refundable']['deposit_total'] = $deposit_total;
            $breakdown['details_breakdown']['deposit_cost'] = $deposit_total;
        }

        return $breakdown;
    }


    /**
     * Calculate hourly resource and person cost
     *
     * @param $cost
     * @param $hours
     * @param $payable_cat
     * @param $payable_resource
     * @param $payable_person
     * @param $payable_security_deposites
     * @param $pickup_cost
     * @param $dropoff_cost
     * @param $location_cost
     * @param $one_time_item
     * @param $product_id
     * @param $inventory_id
     * @param $data
     * @param $conditional_data
     * @return array
     */
    public function calculate_hourly_extras_cost($cost, $hours, $payable_cat, $payable_resource, $payable_person, $payable_security_deposites, $pickup_cost, $dropoff_cost, $location_cost, $one_time_item, $product_id, $inventory_id, $data, $conditional_data)
    {
        $breakdown = [];

        $location_total = 0;
        $category_total = 0;
        $resource_total = 0;
        $adult_total = 0;
        $child_total = 0;
        $deposit_total = 0;

        if (isset($pickup_cost) && !empty($pickup_cost) && $one_time_item) {
            $location_total = (float) $location_total + (float) $pickup_cost;
            $breakdown['details_breakdown']['pickup_location_cost'] = (float) $pickup_cost;
        }

        if (isset($dropoff_cost) && !empty($dropoff_cost) && $one_time_item) {
            $location_total = (float) $location_total + (float) $dropoff_cost;
            $breakdown['details_breakdown']['return_location_cost'] = (float) $dropoff_cost;
        }

        if (isset($location_cost) && !empty($location_cost) && $one_time_item) {
            $location_total = (float) $location_total + (float) $location_cost;
            $breakdown['details_breakdown']['kilometer_cost'] = (float) $location_cost;
        }

        $breakdown['non_refundable']['location_total'] = $location_total;

        if (isset($payable_cat) && count($payable_cat) !== 0) {
            foreach ($payable_cat as $key => $value) {
                if ($value['multiply'] === 'per_day') {
                    $category_total = (float) $category_total + (int) $value['quantity'] * (int) $hours * (float) $value['hourly_cost'];
                } elseif ($one_time_item) {
                    $category_total = (float) $category_total + (int) $value['quantity'] * (float) $value['cost'];
                }
            }
            $breakdown['non_refundable']['category_total'] = $category_total;
            $breakdown['details_breakdown']['category_cost'] = $category_total;
        }

        if (isset($payable_resource) && count($payable_resource) !== 0) {
            foreach ($payable_resource as $key => $value) {
                if ($value['cost_multiply'] === 'per_day') {
                    $resource_total = (float) $resource_total + (int) $hours * (float) $value['resource_hourly_cost'];
                } elseif ($one_time_item) {
                    $resource_total = (float) $resource_total + (float) $value['resource_cost'];
                }
            }
            $breakdown['non_refundable']['resource_total'] = $resource_total;
            $breakdown['details_breakdown']['resource_cost'] = $resource_total;
        }

        $adults = $payable_person['adults'];
        $childs = $payable_person['childs'];

        if (isset($adults) && count($adults) !== 0) {
            if ($adults['cost_multiply'] === 'per_day') {
                $adult_total = (float) $adult_total + (int) $hours * (float) $adults['person_hourly_cost'];
            } elseif ($one_time_item) {
                $adult_total = (float) $adult_total + (float) $adults['person_cost'];
            }
            $breakdown['non_refundable']['adult_total'] = $adult_total;
            $breakdown['details_breakdown']['adult_cost'] = $adult_total;
        }

        if (isset($childs) && count($childs) !== 0) {
            if ($childs['cost_multiply'] === 'per_day') {
                $child_total = (float) $child_total + (int) $hours * (float) $childs['person_hourly_cost'];
            } elseif ($one_time_item) {
                $child_total = (float) $child_total + (float) $childs['person_cost'];
            }
            $breakdown['non_refundable']['child_total'] = $child_total;
            $breakdown['details_breakdown']['child_cost'] = $child_total;
        }

        //$cost = apply_filters('before_payable_security_deposites', $cost, $product_id, $inventory_id, $data, $conditional_data);

        if (isset($payable_security_deposites) && count($payable_security_deposites) !== 0) {
            foreach ($payable_security_deposites as $key => $value) {
                if ($value['cost_multiply'] === 'per_day') {
                    $deposit_total = (float) $deposit_total + (int) $hours * (float) $value['security_deposite_hourly_cost'];
                } elseif ($one_time_item) {
                    $deposit_total = (float) $deposit_total + (float) $value['security_deposite_cost'];
                }
            }
            $breakdown['refundable']['deposit_total'] = $deposit_total;
            $breakdown['details_breakdown']['deposit_cost'] = $deposit_total;
        }

        return $breakdown;
    }
}

new WC_Redq_Rental_Cart();
