<?php

/**
 * rnb_get_inventory_taxonomies
 *
 * @return array
 */
function rnb_get_inventory_taxonomies()
{
    $taxonomy_args = [
        [
            'taxonomy' => 'rnb_categories',
            'label' => __('RnB Categories', 'redq-rental'),
            'post_type' => 'inventory'
        ],
        [
            'taxonomy' => 'resource',
            'label' => __('Resources', 'redq-rental'),
            'post_type' => 'inventory'
        ],
        [
            'taxonomy' => 'person',
            'label' => __('Person', 'redq-rental'),
            'post_type' => 'inventory'
        ],
        [
            'taxonomy' => 'deposite',
            'label' => __('Deposit', 'redq-rental'),
            'post_type' => 'inventory'
        ],
        [
            'taxonomy' => 'attributes',
            'label' => __('Attributes', 'redq-rental'),
            'post_type' => 'inventory'
        ],
        [
            'taxonomy' => 'features',
            'label' => __('Features', 'redq-rental'),
            'post_type' => 'inventory'
        ],
        [
            'taxonomy' => 'pickup_location',
            'label' => __('Pickup Location', 'redq-rental'),
            'post_type' => 'inventory'
        ],
        [
            'taxonomy' => 'dropoff_location',
            'label' => __('Dropoff Location', 'redq-rental'),
            'post_type' => 'inventory'
        ],
    ];
    return apply_filters('rnb_register_inventory_taxonomy', $taxonomy_args);
}

/**
 * rnb_term_meta_data_provider
 *
 * @return array
 */
function rnb_term_meta_data_provider()
{
    //Rnb Categories Term Meta args
    $args[] = [
        'taxonomy' => 'rnb_categories',
        'args' => [
            'title' => __('Quantity', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_rnb_cat_qty',
            'column_name' => __('Qty', 'redq-rental'),
            'placeholder' => '',
            'required' => false,
        ]
    ];
    $args[] = [
        'taxonomy' => 'rnb_categories',
        'args' => [
            'title' => __('Choose Payable or Not', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_rnb_cat_payable_or_not',
            'column_name' => __('Pay', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'yes',
                    'value' => esc_html__('Yes', 'redq-rental')
                ],
                '1' => [
                    'key' => 'no',
                    'value' => esc_html__('No', 'redq-rental')
                ],
            ],
        ]
    ];
    $args[] = [
        'taxonomy' => 'rnb_categories',
        'args' => [
            'title' => __('Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_rnb_cat_cost_termmeta',
            'column_name' => __('Cost', 'redq-rental'),
            'placeholder' => '',
            'text_type' => 'price',
            'required' => false,
        ]
    ];
    $args[] = [
        'taxonomy' => 'rnb_categories',
        'args' => [
            'title' => __('Price Applicable', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_rnb_cat_price_applicable_term_meta',
            'column_name' => __('Applied', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'one_time',
                    'value' => esc_html__('One Time', 'redq-rental')
                ],
                '1' => [
                    'key' => 'per_day',
                    'value' => esc_html__('Per Day', 'redq-rental')
                ],
            ],
        ]
    ];
    $args[] = [
        'taxonomy' => 'rnb_categories',
        'args' => [
            'title' => __('Hourly Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_rnb_cat_hourly_cost_termmeta',
            'column_name' => __('H.Cost', 'redq-rental'),
            'placeholder' => '',
            'text_type' => 'price',
            'required' => false,
        ]
    ];
    $args[] = [
        'taxonomy' => 'rnb_categories',
        'args' => [
            'title' => __('Clickable', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_rnb_cat_clickable_term_meta',
            'column_name' => __('Clickable', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'yes',
                    'value' => esc_html__('Yes', 'redq-rental')
                ],
                '1' => [
                    'key' => 'no',
                    'value' => esc_html__('No', 'redq-rental')
                ],
            ],
        ]
    ];

    //Resource Term Meta args

    $args[] = [
        'taxonomy' => 'resource',
        'args' => [
            'title' => __('Price Applicable', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_price_applicable_term_meta',
            'column_name' => __('Applicable', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'one_time',
                    'value' => 'One Time'
                ],
                '1' => [
                    'key' => 'per_day',
                    'value' => 'Per Day'
                ],
            ],
        ]
    ];

    $args[] = [
        'taxonomy' => 'resource',
        'args' => [
            'title' => __('Resource Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_resource_cost_termmeta',
            'column_name' => __('R.Cost', 'redq-rental'),
            'placeholder' => __('Resource Cost', 'redq-rental'),
            'text_type' => 'price',
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'resource',
        'args' => [
            'title' => __('Hourly Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_hourly_cost_termmeta',
            'column_name' => __('H.Cost', 'redq-rental'),
            'placeholder' => __('Hourly Cost', 'redq-rental'),
            'text_type' => 'price',
            'required' => false,
        ]
    ];

    //Person Term Meta args
    $args[] = [
        'taxonomy' => 'person',
        'args' => [
            'title' => __('Choose payable or not', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_person_payable_or_not',
            'column_name' => __('Payable', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'yes',
                    'value' => esc_html__('Yes', 'redq-rental')
                ],
                '1' => [
                    'key' => 'no',
                    'value' => esc_html__('No', 'redq-rental')
                ],
            ],
        ]
    ];

    $args[] = [
        'taxonomy' => 'person',
        'args' => [
            'title' => __('Choose Person Type', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_person_type',
            'column_name' => __('P.Type', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'none',
                    'value' => esc_html__('None', 'redq-rental')
                ],
                '1' => [
                    'key' => 'adult',
                    'value' => esc_html__('Adult', 'redq-rental')
                ],
                '2' => [
                    'key' => 'child',
                    'value' => esc_html__('Child', 'redq-rental')
                ],
            ],
        ]
    ];

    $args[] = [
        'taxonomy' => 'person',
        'args' => [
            'title' => __('Person Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_person_cost_termmeta',
            'column_name' => __('P.Cost', 'redq-rental'),
            'placeholder' => __('Person Cost', 'redq-rental'),
            'text_type' => 'price',
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'person',
        'args' => [
            'title' => __('Price Applicable', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_person_price_applicable_term_meta',
            'column_name' => __('Applicable', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'one_time',
                    'value' => 'One Time'
                ],
                '1' => [
                    'key' => 'per_day',
                    'value' => 'Per Day'
                ],
            ],
        ]
    ];

    $args[] = [
        'taxonomy' => 'person',
        'args' => [
            'title' => __('Hourly Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_peroson_hourly_cost_termmeta',
            'column_name' => __('H.Cost', 'redq-rental'),
            'placeholder' => __('Hourly Cost', 'redq-rental'),
            'text_type' => 'price',
            'required' => false,
        ]
    ];

    //Deposit Term Meta args
    $args[] = [
        'taxonomy' => 'deposite',
        'args' => [
            'title' => __('Security Deposite Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_sd_cost_termmeta',
            'column_name' => __('S.D.Cost', 'redq-rental'),
            'placeholder' => __('Security Deposite Cost', 'redq-rental'),
            'text_type' => 'price',
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'deposite',
        'args' => [
            'title' => __('Price Applicable', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_sd_price_applicable_term_meta',
            'column_name' => __('Applicable', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'one_time',
                    'value' => 'One Time'
                ],
                '1' => [
                    'key' => 'per_day',
                    'value' => 'Per Day'
                ],
            ],
        ]
    ];

    $args[] = [
        'taxonomy' => 'deposite',
        'args' => [
            'title' => __('Hourly Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_sd_hourly_cost_termmeta',
            'column_name' => __('H.Cost', 'redq-rental'),
            'placeholder' => __('Hourly Cost', 'redq-rental'),
            'text_type' => 'price',
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'deposite',
        'args' => [
            'title' => __('Security Deposite Clickable', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_sd_price_clickable_term_meta',
            'column_name' => __('Clickable', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'yes',
                    'value' => 'Yes'
                ],
                '1' => [
                    'key' => 'no',
                    'value' => 'No'
                ],
            ],
        ]
    ];

    //Pickup Location Term Meta args
    $args[] = [
        'taxonomy' => 'pickup_location',
        'args' => [
            'title' => __('Pickup Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_pickup_cost_termmeta',
            'column_name' => __('Cost', 'redq-rental'),
            'placeholder' => __('Pickup Location Cost', 'redq-rental'),
            'text_type' => 'price',
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'pickup_location',
        'args' => [
            'title' => __('Latitude', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_pickup_location_lat',
            'column_name' => __('Latitude', 'redq-rental'),
            'placeholder' => __('Latitude', 'redq-rental'),
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'pickup_location',
        'args' => [
            'title' => __('Longitude', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_pickup_location_lng',
            'column_name' => __('Longitude', 'redq-rental'),
            'placeholder' => __('Longitude', 'redq-rental'),
            'required' => false,
        ]
    ];

    //Dropoff Location Term Meta args
    $args[] = [
        'taxonomy' => 'dropoff_location',
        'args' => [
            'title' => __('Dropoff Cost', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_dropoff_cost_termmeta',
            'column_name' => __('Cost', 'redq-rental'),
            'placeholder' => __('Dropoff Location Cost', 'redq-rental'),
            'text_type' => 'price',
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'dropoff_location',
        'args' => [
            'title' => __('Latitude', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_dropoff_location_lat',
            'column_name' => __('Latitude', 'redq-rental'),
            'placeholder' => __('Latitude', 'redq-rental'),
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'dropoff_location',
        'args' => [
            'title' => __('Longitude', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_dropoff_location_lng',
            'column_name' => __('Longitude', 'redq-rental'),
            'placeholder' => __('Longitude', 'redq-rental'),
            'required' => false,
        ]
    ];

    //Attributes Term Meta args
    $args[] = [
        'taxonomy' => 'attributes',
        'args' => [
            'title' => __('Attribute Name', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_attribute_name',
            'column_name' => __('A.Name', 'redq-rental'),
            'placeholder' => '',
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'attributes',
        'args' => [
            'title' => __('Attribute Value', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_attribute_value',
            'column_name' => __('A.Value', 'redq-rental'),
            'placeholder' => '',
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'attributes',
        'args' => [
            'title' => __('Choose Image/Icon', 'redq-rental'),
            'type' => 'select',
            'id' => 'choose_attribute_icon',
            'column_name' => __('I.Type', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'icon',
                    'value' => 'Icon'
                ],
                '1' => [
                    'key' => 'image',
                    'value' => 'Image'
                ],
            ],
        ]
    ];

    $args[] = [
        'taxonomy' => 'attributes',
        'args' => [
            'title' => __('Attribute Icon', 'redq-rental'),
            'type' => 'text',
            'id' => 'inventory_attribute_icon',
            'column_name' => __('Icon', 'redq-rental'),
            'placeholder' => __('Font-awesome icon Ex. fa fa-car', 'redq-rental'),
            'required' => false,
        ]
    ];

    $args[] = [
        'taxonomy' => 'attributes',
        'args' => [
            'title' => __('Attribute Image', 'redq-rental'),
            'type' => 'image',
            'id' => 'attributes_image_icon',
            'column_name' => __('Image', 'redq-rental'),
        ]
    ];

    $args[] = [
        'taxonomy' => 'attributes',
        'args' => [
            'title' => __('Highlighted Or Not', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_attribute_highlighted',
            'column_name' => __('Highlighted', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'yes',
                    'value' => 'Yes'
                ],
                '1' => [
                    'key' => 'no',
                    'value' => 'No'
                ],
            ],
        ]
    ];

    //Feature term meta args
    $args[] = [
        'taxonomy' => 'features',
        'args' => [
            'title' => __('Highlighted Or Not', 'redq-rental'),
            'type' => 'select',
            'id' => 'inventory_feature_highlighted',
            'column_name' => __('Highlighted', 'redq-rental'),
            'options' => [
                '0' => [
                    'key' => 'yes',
                    'value' => 'Yes'
                ],
                '1' => [
                    'key' => 'no',
                    'value' => 'No'
                ],
            ],
        ]
    ];

    //Car Company term meta args
    $args[] = [
        'taxonomy' => 'car_company',
        'args' => [
            'title' => __('Car Company Image', 'redq-rental'),
            'type' => 'image',
            'id' => 'product_car_company_icon',
            'column_name' => __('Image', 'redq-rental'),
        ]
    ];

    return apply_filters('rnb_inventory_term_meta_args', $args);
}

/**
 * rnb_format_prices
 *
 * @param mixed $price_breakdown
 *
 * @return void
 */
function rnb_format_prices($breakdown, $quantity = 1)
{
    $formatted_prices = [];

    $prices = $breakdown['price_breakdown'];
    $instant_pay = $breakdown['instant_pay'];

    $details_breakdown = rnb_rearrange_details_breakdown($prices);

    $duration_total = isset($prices['duration_total']) && $prices['duration_total'] ? $prices['duration_total'] : 0;
    $discount_total = isset($prices['discount_total']) && $prices['discount_total'] ? $prices['discount_total'] : 0;
    $deposit_free_total = isset($prices['deposit_free_total']) && $prices['deposit_free_total'] ? $prices['deposit_free_total'] : 0;
    $deposit_total = isset($prices['deposit_total']) && $prices['deposit_total'] ? $prices['deposit_total'] : 0;
    $total = isset($prices['total']) && $prices['total'] ? $prices['total'] : 0;

    if (isset($details_breakdown['pickup_location_cost']) && $details_breakdown['pickup_location_cost']) {
        $data = [
            'text' => __('Pickup Location Cost', 'redq-rental'),
            'cost' => wc_price($details_breakdown['pickup_location_cost'] * $quantity)
        ];
        $formatted_prices['pickup_location_cost'] = $data;
    }

    if (isset($details_breakdown['return_location_cost']) && $details_breakdown['return_location_cost']) {
        $data = [
            'text' => __('Return Location Cost', 'redq-rental'),
            'cost' => wc_price($details_breakdown['return_location_cost'] * $quantity)
        ];
        $formatted_prices['return_location_cost'] = $data;
    }

    if (isset($details_breakdown['kilometer_cost']) && $details_breakdown['kilometer_cost']) {
        $data = [
            'text' => __('Kilometer Cost', 'redq-rental'),
            'cost' => wc_price($details_breakdown['kilometer_cost'] * $quantity)
        ];
        $formatted_prices['kilometer_cost'] = $data;
    }

    if ($duration_total) {
        $data = [
            'text' => __('Duration Cost', 'redq-rental'),
            'cost' => wc_price($duration_total * $quantity)
        ];
        $formatted_prices['duration_cost'] = $data;
    }

    if ($discount_total) {
        $data = [
            'text' => __('Discount Cost', 'redq-rental'),
            'cost' => wc_price(-$discount_total * $quantity)
        ];
        $formatted_prices['discount_cost'] = $data;
    }

    if (isset($details_breakdown['resource_cost']) && $details_breakdown['resource_cost']) {
        $data = [
            'text' => __('Resource Cost', 'redq-rental'),
            'cost' => wc_price($details_breakdown['resource_cost'] * $quantity)
        ];
        $formatted_prices['resource_cost'] = $data;
    }

    if (isset($details_breakdown['category_cost']) && $details_breakdown['category_cost']) {
        $data = [
            'text' => __('Category Cost', 'redq-rental'),
            'cost' => wc_price($details_breakdown['category_cost'] * $quantity)
        ];
        $formatted_prices['category_cost'] = $data;
    }

    if (isset($details_breakdown['adult_cost']) && $details_breakdown['adult_cost']) {
        $data = [
            'text' => __('Adult Cost', 'redq-rental'),
            'cost' => wc_price($details_breakdown['adult_cost'] * $quantity)
        ];
        $formatted_prices['adult_cost'] = $data;
    }

    if (isset($details_breakdown['child_cost']) && $details_breakdown['child_cost']) {
        $data = [
            'text' => __('Child Cost', 'redq-rental'),
            'cost' => wc_price($details_breakdown['child_cost'] * $quantity)
        ];
        $formatted_prices['child_cost'] = $data;
    }

    if ($deposit_free_total) {
        $data = [
            'text' => __('Sub Total', 'redq-rental'),
            'cost' => wc_price($deposit_free_total * $quantity)
        ];
        $formatted_prices['deposit_free_total'] = $data;
    }

    if ($instant_pay === 100) {
        if ($deposit_total) {
            $data = [
                'text' => __('Deposit', 'redq-rental'),
                'cost' => wc_price($deposit_total * $quantity),
            ];
            $formatted_prices['deposit'] = $data;
        }

        if ($total) {
            $data = [
                'text' => __('Grand Total', 'redq-rental'),
                'cost' => wc_price($total * $quantity)
            ];
            $formatted_prices['grand_total'] = $data;
        }
    }

    if ($instant_pay !== 100) {
        $data = [
            'text' => __('Instant Pay', 'redq-rental'),
            'cost' => $instant_pay . '%'
        ];
        $formatted_prices['instant_pay'] = $data;

        $data = [
            'text' => __('Initial Value', 'redq-rental'),
            'cost' => wc_price($breakdown['cost'] * $quantity),
        ];
        $formatted_prices['pay_during_booking'] = $data;

        if ($deposit_total) {
            $data = [
                'text' => __('Deposit', 'redq-rental'),
                'cost' => wc_price($deposit_total * $quantity),
            ];
            $formatted_prices['deposit'] = $data;
        }

        $data = [
            'text' => __('Total Instant Pay', 'redq-rental'),
            'cost' => wc_price(($breakdown['cost'] + $deposit_total) * $quantity),
        ];
        $formatted_prices['total_instant_pay'] = $data;

        $data = [
            'text' => __('Payment Due', 'redq-rental'),
            'cost' => wc_price($breakdown['due_payment'] * $quantity),
        ];
        $formatted_prices['due_payment'] = $data;
    }

    if ($total) {
        $data = [
            'text' => __('Quote Total', 'redq-rental'),
            'cost' => $total * $quantity
        ];
        $formatted_prices['quote_total'] = $data;
    }

    return $formatted_prices;
}

/**
 * rnb_rearrange_details_breakdown
 *
 * @param array $prices
 *
 * @return array
 */
function rnb_rearrange_details_breakdown($prices)
{
    $breakdown = [];

    $day_based_breakdown = isset($prices['extras_breakdown']['details_breakdown']) ? $prices['extras_breakdown']['details_breakdown'] : [];
    $hour_based_breakdown = isset($prices['extras_hour_breakdown']['details_breakdown']) ? $prices['extras_hour_breakdown']['details_breakdown'] : [];

    if (!empty($day_based_breakdown) && !empty($hour_based_breakdown)) {
        foreach ($day_based_breakdown as $key => $value) {
            $breakdown[$key] = $value + $hour_based_breakdown[$key];
        }
    }

    if (!empty($day_based_breakdown) && empty($hour_based_breakdown)) {
        $breakdown = $day_based_breakdown;
    }

    if (empty($day_based_breakdown) && !empty($hour_based_breakdown)) {
        $breakdown = $hour_based_breakdown;
    }

    return $breakdown;
}

/**
 * get_pickup_location_data
 *
 * @param int $term_id
 * @param string $taxonomy
 * @return string
 */
function get_pickup_location_data($term_id, $taxonomy)
{
    if (!$term_id) {
        return;
    }

    $term = get_term_by('id', $term_id, $taxonomy);

    $cost = get_term_meta($term_id, 'inventory_pickup_cost_termmeta', true);
    $cost = $cost ? (float) $cost : 0;

    $result = [
        $term->name,
        $term->description ? $term->description : $term->name,
        $cost
    ];

    return implode('|', $result);
}

/**
 * get_dropoff_location_data
 *
 * @param int $term_id
 * @param string $taxonomy
 * @return string
 */
function get_dropoff_location_data($term_id, $taxonomy)
{
    if (!$term_id) {
        return;
    }

    $term = get_term_by('id', $term_id, $taxonomy);

    $cost = get_term_meta($term_id, 'inventory_dropoff_cost_termmeta', true);
    $cost = $cost ? (float) $cost : 0;

    $result = [
        $term->name,
        $term->description ? $term->description : $term->name,
        $cost
    ];

    return implode('|', $result);
}

/**
 * get_resource_data
 *
 * @param int $term_id
 * @param string $taxonomy
 * @return string
 */
function get_resource_data($term_ids, $taxonomy)
{
    $results = [];

    if (!count($term_ids)) {
        return $results;
    }

    foreach ($term_ids as $key => $term_id) {
        $term = get_term_by('id', $term_id, $taxonomy);

        $cost = get_term_meta($term_id, 'inventory_resource_cost_termmeta', true);
        $applicable = get_term_meta($term_id, 'inventory_price_applicable_term_meta', true);
        $hourly_cost = get_term_meta($term_id, 'inventory_hourly_cost_termmeta', true);

        $cost = $cost ? (float) $cost : 0;
        $hourly_cost = $hourly_cost ? (float) $hourly_cost : 0;

        $data = [
            $term->name,
            $cost,
            $applicable,
            $hourly_cost
        ];

        $results[] = implode('|', $data);
    }

    return $results;
}

/**
 * get_category_data
 *
 * @param int $term_id
 * @param string $taxonomy
 * @return string
 */
function get_category_data($categories, $quantity, $taxonomy)
{
    $results = [];

    if (!count($categories)) {
        return $results;
    }

    foreach ($categories as $key => $category) {

        $ids = explode('|', $category);
        $term_id = $ids[0];
        $qty = isset($ids[1]) ? (int) $ids[1] : 1;

        $term = get_term_by('id', $term_id, $taxonomy);

        $cost = get_term_meta($term_id, 'inventory_rnb_cat_cost_termmeta', true);
        $applicable = get_term_meta($term_id, 'inventory_rnb_cat_price_applicable_term_meta', true);
        $hourly_cost = get_term_meta($term_id, 'inventory_rnb_cat_hourly_cost_termmeta', true);

        $cost = $cost ? (float) $cost : 0;
        $hourly_cost = $hourly_cost ? (float) $hourly_cost : 0;
        $qty = $qty ? (int) $qty : 1;

        $data = [
            $term->name,
            $cost,
            $applicable,
            $hourly_cost,
            $qty
        ];

        $results[] = implode('|', $data);
    }

    return $results;
}

/**
 * get_person_data
 *
 * @param int $term_id
 * @param string $taxonomy
 * @return string
 */
function get_person_data($term_id, $taxonomy)
{
    if (!$term_id) {
        return;
    }

    $term = get_term_by('id', $term_id, $taxonomy);

    $cost = get_term_meta($term_id, 'inventory_person_cost_termmeta', true);
    $applicable = get_term_meta($term_id, 'inventory_person_price_applicable_term_meta', true);
    $hourly_cost = get_term_meta($term_id, 'inventory_peroson_hourly_cost_termmeta', true);
    $cost = $cost ? (float) $cost : 0;
    $hourly_cost = $hourly_cost ? (float) $hourly_cost : 0;

    $result = [
        $term->name,
        $cost,
        $applicable,
        $hourly_cost
    ];

    return implode('|', $result);
}

/**
 * get_deposit_data
 *
 * @param int $term_id
 * @param string $taxonomy
 * @return string
 */
function get_deposit_data($term_ids, $taxonomy)
{
    $results = [];

    if (!count($term_ids)) {
        return $results;
    }

    foreach ($term_ids as $key => $term_id) {
        $term = get_term_by('id', $term_id, $taxonomy);

        $cost = get_term_meta($term_id, 'inventory_sd_cost_termmeta', true);
        $applicable = get_term_meta($term_id, 'inventory_sd_price_applicable_term_meta', true);
        $hourly_cost = get_term_meta($term_id, 'inventory_sd_hourly_cost_termmeta', true);

        $cost = $cost ? (float) $cost : 0;
        $hourly_cost = $hourly_cost ? (float) $hourly_cost : 0;

        $data = [
            $term->name,
            $cost,
            $applicable,
            $hourly_cost
        ];

        $results[] = implode('|', $data);
    }

    return $results;
}
