<?php

/**
 * rnb_arrange_pickup_location_data function
 *
 * @param [type] $product_id
 * @param [type] $conditional_data
 * @return array
 */
function rnb_arrange_pickup_location_data($product_id, $inventory_id, $conditional_data)
{
    $pickup_labels = redq_rental_get_settings($product_id, 'labels', ['pickup_location']);

    return [
        'data' => $conditional_data['booking_layout'] !== 'layout_two' ? WC_Product_Redq_Rental::redq_get_rental_payable_attributes('pickup_location', $inventory_id) : [],
        'title' => $pickup_labels['labels']['pickup_location'],
        'placeholder' => $pickup_labels['labels']['pickup_loc_placeholder'],
        'layout' => $conditional_data['booking_layout']
    ];
}

/**
 * rnb_arrange_return_location_data function
 *
 * @param [type] $product_id
 * @param [type] $conditional_data
 * @return array
 */
function rnb_arrange_return_location_data($product_id, $inventory_id, $conditional_data)
{
    $return_labels = redq_rental_get_settings($product_id, 'labels', ['return_location']);

    return [
        'data' => $conditional_data['booking_layout'] !== 'layout_two' ? WC_Product_Redq_Rental::redq_get_rental_payable_attributes('dropoff_location', $inventory_id) : [],
        'title' => $return_labels['labels']['return_location'],
        'placeholder' => $return_labels['labels']['return_loc_placeholder'],
        'layout' => $conditional_data['booking_layout']
    ];
}

/**
 * rnb_arrange_resource_data function
 *
 * @param [type] $product_id
 * @param [type] $conditional_data
 * @return array
 */
function rnb_arrange_resource_data($product_id, $inventory_id, $conditional_data)
{
    $resource_labels = redq_rental_get_settings($product_id, 'labels', ['resources']);
    $resource_infos = WC_Product_Redq_Rental::redq_get_rental_payable_attributes('resource', $inventory_id);

    foreach ($resource_infos as $key => $value) {
        if ($value['resource_applicable'] === 'per_day') {
            $resource_infos[$key]['extra_meta'] = '<span class="pull-right show_if_day">' . wc_price($value['resource_cost']) . '<span>' . __(' - Per Day', 'redq-rental') . '</span></span>
				<span class="pull-right show_if_time" style="display: none;">' . wc_price($value['resource_hourly_cost']) . ' ' . __(' - Per Hour', 'redq-rental') . '</span>';
        } else {
            $resource_infos[$key]['extra_meta'] = '<span class="pull-right">' . wc_price($value['resource_cost']) . ' ' . __(' - One Time', 'redq-rental') . '</span>';
        }
    }

    return [
        'data' => $resource_infos,
        'title' => $resource_labels['labels']['resource'],
    ];
}

/**
 * rnb_arrange_category_data function
 *
 * @param [type] $product_id
 * @param [type] $conditional_data
 * @return array
 */
function rnb_arrange_category_data($product_id, $inventory_id, $conditional_data)
{
    $category_labels = redq_rental_get_settings($product_id, 'labels', ['categories']);
    $category_infos = WC_Product_Redq_Rental::redq_get_rental_payable_attributes('rnb_categories', $inventory_id);
    foreach ($category_infos as $key => $value) {
        if ($value['applicable'] === 'per_day') {
            $category_infos[$key]['extra_meta'] = '<span class="pull-right show_if_day">' . wc_price($value['cost']) . '<span> ' . __(' - Per Day', 'redq-rental') . '</span></span>
				<span class="pull-right show_if_time" style="display: none;">' . wc_price($value['hourlycost']) . ' ' . __(' - Per Hour', 'redq-rental') . '</span>';
        } else {
            $category_infos[$key]['extra_meta'] = '<span class="pull-right">' . wc_price($value['cost']) . ' ' . __(' - One Time', 'redq-rental') . '</span>';
        }

        $args = [
            'input_name' => 'cat_quantity',
            'min_value' => 1,
            'max_value' => $value['qty'] ? $value['qty'] : 1,
        ];

        global $product;

        $defaults = [
            'input_id' => uniqid('quantity_'),
            'input_name' => 'quantity',
            'input_value' => '1',
            'classes' => apply_filters('woocommerce_quantity_input_classes', ['input-text', 'qty', 'text'], $product),
            'max_value' => apply_filters('woocommerce_quantity_input_max', -1, $product),
            'min_value' => apply_filters('woocommerce_quantity_input_min', 0, $product),
            'step' => apply_filters('woocommerce_quantity_input_step', 1, $product),
            'pattern' => apply_filters('woocommerce_quantity_input_pattern', has_filter('woocommerce_stock_amount', 'intval') ? '[0-9]*' : ''),
            'inputmode' => apply_filters('woocommerce_quantity_input_inputmode', has_filter('woocommerce_stock_amount', 'intval') ? 'numeric' : ''),
            // 'product_name' => $product ? $product->get_title() : '',
            'placeholder' => __('Quantity', 'woocommerce'),
            'title' => esc_attr_x('Qty', 'Product quantity input tooltip', 'woocommerce'),
            'labelledby' => !empty($args['product_name']) ? sprintf(__('%s quantity', 'woocommerce'), strip_tags($args['product_name'])) : '',
        ];

        $args = apply_filters('woocommerce_quantity_input_args', wp_parse_args($args, $defaults), $product);

        // Apply sanity to min/max args - min cannot be lower than 0.
        $args['min_value'] = max($args['min_value'], 0);
        $args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

        // Max cannot be lower than min if defined.
        if ('' !== $args['max_value'] && $args['max_value'] < $args['min_value']) {
            $args['max_value'] = $args['min_value'];
        }

        $category_infos[$key]['quantity_input'] = $args;
    }

    return [
        'data' => $category_infos,
        'title' => $category_labels['labels']['categories'],
    ];
}

/**
 * rnb_arrange_adult_data
 *
 * @param [type] $product_id
 * @param [type] $conditional_data
 * @return array
 */
function rnb_arrange_adult_data($product_id, $inventory_id, $conditional_data)
{
    $person_labels = redq_rental_get_settings($product_id, 'labels', ['person']);
    $person_info = WC_Product_Redq_Rental::redq_get_rental_payable_attributes('person', $inventory_id);

    $adults = isset($person_info['adults']) ? $person_info['adults'] : [];

    if (isset($adults) && !empty($adults)) {
        foreach ($adults as $key => $value) {
            if ($value['person_cost_applicable'] === 'per_day') {

                $extra_meta = __(' ', 'redq-rental');
                $extra_hourly_meta = __(' ', 'redq-rental');

                if (isset($value['person_cost']) && !empty($value['person_cost'])) {
                    $extra_meta .= wc_price($value['person_cost']);
                    $extra_meta .= __(' - Per day', 'redq-rental');
                }

                if (isset($value['person_hourly_cost']) && !empty($value['person_hourly_cost'])) {
                    $extra_hourly_meta .= wc_price($value['person_hourly_cost']);
                    $extra_hourly_meta .= __(' - Per hour', 'redq-rental');
                }

                $adults[$key]['extra_meta'] = $extra_meta;
                $adults[$key]['extra_hourly_meta'] = $extra_hourly_meta;
            } else {
                $extra_meta = __(' ', 'redq-rental');
                if (isset($value['person_cost']) && !empty($value['person_cost'])) {
                    $extra_meta .= wc_price($value['person_cost']);
                    $extra_meta .= __(' - One time', 'redq-rental');
                }

                $adults[$key]['extra_meta'] = $extra_meta;
            }
        }
    }

    return [
        'data' => $adults,
        'title' => $person_labels['labels']['adults'],
        'placeholder' => $person_labels['labels']['adults_placeholder'],
    ];
}

/**
 * rnb_arrange_child_data
 *
 * @param [type] $product_id
 * @param [type] $conditional_data
 * @return array
 */
function rnb_arrange_child_data($product_id, $inventory_id, $conditional_data)
{
    $person_labels = redq_rental_get_settings($product_id, 'labels', ['person']);

    $person_info = WC_Product_Redq_Rental::redq_get_rental_payable_attributes('person', $inventory_id);
    $childs = isset($person_info['childs']) ? $person_info['childs'] : [];

    if (isset($childs) && !empty($childs)) {
        foreach ($childs as $key => $value) {
            if ($value['person_cost_applicable'] === 'per_day') {

                $extra_meta = __(' ', 'redq-rental');
                $extra_hourly_meta = __(' ', 'redq-rental');

                if (isset($value['person_cost']) && !empty($value['person_cost'])) {
                    $extra_meta .= wc_price($value['person_cost']);
                    $extra_meta .= __(' - Per day', 'redq-rental');
                }

                if (isset($value['person_hourly_cost']) && !empty($value['person_hourly_cost'])) {
                    $extra_hourly_meta .= wc_price($value['person_hourly_cost']);
                    $extra_hourly_meta .= __(' - Per hour', 'redq-rental');
                }

                $childs[$key]['extra_meta'] = $extra_meta;
                $childs[$key]['extra_hourly_meta'] = $extra_hourly_meta;
            } else {
                $extra_meta = __(' ', 'redq-rental');

                if (isset($value['person_cost']) && !empty($value['person_cost'])) {
                    $extra_meta .= wc_price($value['person_cost']);
                    $extra_meta .= __(' - One time', 'redq-rental');
                }

                $childs[$key]['extra_meta'] = $extra_meta;
            }
        }
    }

    return [
        'data' => $childs,
        'title' => $person_labels['labels']['childs'],
        'placeholder' => $person_labels['labels']['childs_placeholder'],
    ];
}

/**
 * rnb_arrange_security_deposit_data function
 *
 * @param [type] $product_id
 * @param [type] $conditional_data
 * @return array
 */
function rnb_arrange_security_deposit_data($product_id, $inventory_id, $conditional_data)
{
    $security_deposit = WC_Product_Redq_Rental::redq_get_rental_payable_attributes('deposite', $inventory_id);
    $deposit_labels = redq_rental_get_settings($product_id, 'labels', ['deposites']);

    foreach ($security_deposit as $key => $value) {
        if ($value['security_deposite_applicable'] === 'per_day') {
            $security_deposit[$key]['extra_meta'] = '<span class="pull-right show_if_day">' . wc_price($value['security_deposite_cost']) . '<span> ' . __(' - Per Day', 'redq-rental') . ' </span></span>
				<span class="pull-right show_if_time" style="display: none;">' . wc_price($value['security_deposite_hourly_cost']) . ' ' . __(' - Per Hour', 'redq-rental') . '</span>';
        } else {
            $security_deposit[$key]['extra_meta'] = '<span class="pull-right">' . wc_price($value['security_deposite_cost']) . ' ' . __(' - One Time', 'redq-rental') . '</span>';
        }
    }

    return [
        'data' => $security_deposit,
        'title' => $deposit_labels['labels']['deposite'],
    ];
}

function rnb_time_subtraction($time)
{
    $hour = $time >= 60 ? 01 : 00;
    $mins = $time >= 60 ? 00 : $time;

    $upper_limit = new DateTime('24:00');
    $lower_limit = new DateTime("$hour:$mins");

    $interval = $upper_limit->diff($lower_limit);
    $result = $interval->format('%H:%i');

    return $result;
}
