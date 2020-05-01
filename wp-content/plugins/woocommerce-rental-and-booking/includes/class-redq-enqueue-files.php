<?php

class Rnb_Enqueue_Files
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'frontend_styles_and_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_styles_and_scripts']);
        add_filter('woocommerce_screen_ids', [$this, 'rnb_screen_ids']);

        add_action('wp_ajax_rnb_get_inventory_data', [$this, 'get_inventory_data']);
        add_action('wp_ajax_nopriv_rnb_get_inventory_data', [$this, 'get_inventory_data']);
    }

    public function get_inventory_data()
    {
        $inventory_id = $_POST['inventory_id'];
        $product_id = $_POST['post_id'];

        $availability = rnb_inventory_availability_check($product_id, $inventory_id);
        $allowed_datetime = rnb_inventory_availability_check($product_id, $inventory_id, 'ALLOWED_DATETIMES_ONLY');

        $cart_dates = rental_product_in_cart($product_id);
        $starting_block_days = redq_rental_staring_block_days($product_id);

        $holidays = redq_rental_handle_holidays($product_id);

        $buffer_dates = array_merge($starting_block_days, $cart_dates, $holidays);

        $rnb_data = rnb_get_combined_settings_data($product_id);

        $pricing_data = redq_rental_get_pricing_data($inventory_id, $product_id);
        $rnb_data['pricings'] = $pricing_data;

        $price_unit = rnb_get_product_price($product_id, $inventory_id);
        $price_unit_markup = $price_unit['prefix'] . '&nbsp;' . wc_price($price_unit['price']) . '&nbsp;' . $price_unit['suffix'];

        $woocommerce_info = rnb_get_woocommerce_currency_info();
        $translated_strings = rnb_get_translated_strings();
        $localize_info = rnb_get_localize_info($product_id);

        $booking_data = [
            'rnb_data'           => $rnb_data,
            'block_dates'        => $availability,
            'woocommerce_info'   => $woocommerce_info,
            'translated_strings' => $translated_strings,
            'availability'       => $availability,
            'buffer_days'        => $buffer_dates,
            'quantity'           => get_post_meta($inventory_id, 'quantity', true),
            'unit_price'         => $price_unit_markup,
            'product_id'         => $product_id
        ];

        $calendar_data = [
            'availability'       => $availability,
            'calendar_props'     => $rnb_data,
            'block_dates'        => $availability,
            'allowed_datetime'   => $allowed_datetime,
            'localize_info'      => $localize_info,
            'translated_strings' => $translated_strings,
            'buffer_days'        => $buffer_dates,
        ];

        $conditions = redq_rental_get_settings($product_id, 'conditions');
        $conditional_data = $conditions['conditions'];

        $pick_up_locations = rnb_arrange_pickup_location_data($product_id, $inventory_id, $conditional_data);
        $return_locations  = rnb_arrange_return_location_data($product_id, $inventory_id, $conditional_data);
        $deposits          = rnb_arrange_security_deposit_data($product_id, $inventory_id, $conditional_data);
        $adult_data        = rnb_arrange_adult_data($product_id, $inventory_id, $conditional_data);
        $child_data        = rnb_arrange_child_data($product_id, $inventory_id, $conditional_data);
        $resources         = rnb_arrange_resource_data($product_id, $inventory_id, $conditional_data);
        $categories        = rnb_arrange_category_data($product_id, $inventory_id, $conditional_data);

        echo json_encode([
            'booking_data'      => $booking_data,
            'calendar_data'     => $calendar_data,
            'pick_up_locations' => $pick_up_locations,
            'return_locations'  => $return_locations,
            'deposits'          => $deposits,
            'adults'            => $adult_data,
            'childs'            => $child_data,
            'resources'         => $resources,
            'categories'        => $categories,
        ]);

        wp_die();
    }

    /**
     * Frontend enqueued front-end stylesheet and scripts
     *
     * @return null
     * @since 1.0.0
     */
    public function frontend_styles_and_scripts()
    {
        $post_id = get_the_ID();
        $redq_product_inventory = rnb_get_product_inventory_id($post_id);

        $inventory_id = '';
        if (!empty($redq_product_inventory)) {
            $inventory_id = $redq_product_inventory[0];
        }



        wp_enqueue_script('underscore');

        wp_register_script('rnb-calendar', REDQ_ROOT_URL . '/assets/js/rnb-calendar.js', ['jquery'], false, true);
        wp_enqueue_script('rnb-calendar');

        wp_register_script('rnb-template', REDQ_ROOT_URL . '/assets/js/rnb-template.js', ['jquery'], false, true);
        wp_enqueue_script('rnb-template');

        wp_register_script('rnb-init', REDQ_ROOT_URL . '/assets/js/rnb-init.js', ['jquery'], false, true);
        wp_enqueue_script('rnb-init');

        wp_register_script('quote-handle', REDQ_ROOT_URL . '/assets/js/quote.js', ['jquery'], false, true);
        wp_enqueue_script('quote-handle');

        wp_register_style('rental-quote', REDQ_ROOT_URL . '/assets/css/quote-front.css', [], $ver = false, $media = 'all');
        wp_enqueue_style('rental-quote');

        wp_register_script('chosen.jquery', REDQ_ROOT_URL . '/assets/js/chosen.jquery.js', ['jquery'], true);
        wp_enqueue_script('chosen.jquery');

        wp_register_style('chosen', REDQ_ROOT_URL . '/assets/css/chosen.css', [], $ver = false, $media = 'all');
        wp_enqueue_style('chosen');

        wp_localize_script('quote-handle', 'REDQ_MYACCOUNT_API', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);

        if (is_rental_product($post_id)) {
            $check_ssl = is_ssl() ? 'https' : 'http';

            wp_register_style('rental-global', REDQ_ROOT_URL . '/assets/css/rental-global.css', [], $ver = false, $media = 'all');
            wp_enqueue_style('rental-global');

            wp_register_script('clone', REDQ_ROOT_URL . '/assets/js/clone.js', ['jquery'], true);
            wp_enqueue_script('clone');

            wp_register_script('jquery.datetimepicker.full', REDQ_ROOT_URL . '/assets/js/jquery.datetimepicker.full.js', ['jquery'], true);
            wp_enqueue_script('jquery.datetimepicker.full');

            wp_register_style('jquery.datetimepicker', REDQ_ROOT_URL . '/assets/css/jquery.datetimepicker.css', [], $ver = false, $media = 'all');
            wp_enqueue_style('jquery.datetimepicker');

            wp_register_script('sweetalert.min', REDQ_ROOT_URL . '/assets/js/sweetalert.min.js', ['jquery'], true);
            wp_enqueue_script('sweetalert.min');

            wp_register_style('sweetalert', REDQ_ROOT_URL . '/assets/css/sweetalert.css', [], $ver = false, $media = 'all');
            wp_enqueue_style('sweetalert');

            wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
            wp_enqueue_style('ion-icon', '' . $check_ssl . '://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');

            wp_register_script('jquery.steps', REDQ_ROOT_URL . '/assets/js/jquery.steps.js', ['jquery'], true);
            wp_enqueue_script('jquery.steps');

            wp_register_style('jquery.steps', REDQ_ROOT_URL . '/assets/css/jquery.steps.css', [], $ver = false, $media = 'all');
            wp_enqueue_style('jquery.steps');

            wp_register_style('rental-style', REDQ_ROOT_URL . '/assets/css/rental-style.css', [], $ver = false, $media = 'all');
            wp_enqueue_style('rental-style');

            wp_register_style('magnific-popup', REDQ_ROOT_URL . '/assets/css/magnific-popup.css', [], $ver = false, $media = 'all');
            wp_enqueue_style('magnific-popup');

            wp_register_script('date', REDQ_ROOT_URL . '/assets/js/date.js', ['jquery'], true);
            wp_enqueue_script('date');

            wp_register_script('accounting', REDQ_ROOT_URL . '/assets/js/accounting.js', ['jquery'], true);
            wp_enqueue_script('accounting');

            wp_register_script('jquery.flip', REDQ_ROOT_URL . '/assets/js/jquery.flip.js', ['jquery'], true);
            wp_enqueue_script('jquery.flip');

            wp_register_script('magnific-popup', REDQ_ROOT_URL . '/assets/js/jquery.magnific-popup.min.js', ['jquery'], true);
            wp_enqueue_script('magnific-popup');

            //Enable or Disable google map
            $this->rnb_handle_google_map($post_id);

            wp_register_script('front-end-scripts', REDQ_ROOT_URL . '/assets/js/main-script.js', ['jquery', 'underscore', 'chosen.jquery', 'rnb-calendar', 'rnb-template', 'rnb-init'], true);
            wp_enqueue_script('front-end-scripts');

            wp_register_script('rnb-rfq', REDQ_ROOT_URL . '/assets/js/rnb-rfq.js', ['jquery', 'underscore', 'chosen.jquery', 'rnb-calendar', 'rnb-template', 'rnb-init'], true);
            wp_enqueue_script('rnb-rfq');

            wp_register_script('rnb-validation', REDQ_ROOT_URL . '/assets/js/rnb-validation.js', ['jquery'], true);
            wp_enqueue_script('rnb-validation');

            $cart_dates = rental_product_in_cart($post_id);
            $starting_block_days = redq_rental_staring_block_days($post_id);
            $holidays = redq_rental_handle_holidays($post_id);
            $buffer_dates = array_merge($starting_block_days, $cart_dates, $holidays);
            $availability = rnb_inventory_availability_check($post_id, $inventory_id);

            $settings_data = rnb_get_combined_settings_data($post_id);
            $woocommerce_info = rnb_get_woocommerce_currency_info();
            $translated_strings = rnb_get_translated_strings();
            $localize_info = rnb_get_localize_info($post_id);

            $conditions = redq_rental_get_settings($post_id, 'conditions');
            $conditional_data = $conditions['conditions'];

            $pickup_locations = rnb_arrange_pickup_location_data($post_id, $inventory_id, $conditional_data);
            $return_locations = rnb_arrange_return_location_data($post_id, $inventory_id, $conditional_data);
            $deposits = rnb_arrange_security_deposit_data($post_id, $inventory_id, $conditional_data);
            $adult_data = rnb_arrange_adult_data($post_id, $inventory_id, $conditional_data);
            $child_data = rnb_arrange_child_data($post_id, $inventory_id, $conditional_data);
            $resources = rnb_arrange_resource_data($post_id, $inventory_id, $conditional_data);
            $categories = rnb_arrange_category_data($post_id, $inventory_id, $conditional_data);

            wp_localize_script('front-end-scripts', 'CALENDAR_DATA', [
                'availability'       => $availability,
                'calendar_props'     => $settings_data,
                'block_dates'        => $availability,
                'woocommerce_info'   => $woocommerce_info,
                'allowed_datetime'   => rnb_inventory_availability_check($post_id, $inventory_id, 'ALLOWED_DATETIMES_ONLY'),
                'localize_info'      => $localize_info,
                'translated_strings' => $translated_strings,
                'buffer_days'        => $buffer_dates,
                'quantity'           => get_post_meta($inventory_id, 'quantity', true),
                'ajax_url'           => rnb_get_ajax_url(),
                'pick_up_locations'  => $pickup_locations,
                'return_locations'   => $return_locations,
                'resources'          => $resources,
                'categories'         => $categories,
                'adults'             => $adult_data,
                'childs'             => $child_data,
                'deposits'           => $deposits,
            ]);

            wp_localize_script('rnb-rfq', 'RFQ_DATA', [
                'ajax_url' => rnb_get_ajax_url(),
                'translated_strings' => $translated_strings,
            ]);
        }
    }

    public function rnb_handle_google_map($product_id)
    {
        if (!is_product()) {
            return;
        }

        $gmap_enable = get_option('rnb_enable_gmap');
        $map_key = get_option('rnb_gmap_api_key');
        $conditions = redq_rental_get_settings($product_id, 'conditions');
        if ($gmap_enable === 'yes' && $map_key && isset($conditions['conditions']['booking_layout']) && $conditions['conditions']['booking_layout'] !== 'layout_one') {
            $markers = [
                'pickup'      => REDQ_ROOT_URL . '/assets/img/marker-pickup.png',
                'destination' => REDQ_ROOT_URL . '/assets/img/marker-destination.png'
            ];

            wp_register_script('google-map-api', '//maps.googleapis.com/maps/api/js?key=' . $map_key . '&libraries=places,geometry&language=en-US', true, false);
            wp_enqueue_script('google-map-api');

            wp_register_script('rnb-map', REDQ_ROOT_URL . '/assets/js/rnb-map.js', ['jquery'], true);
            wp_enqueue_script('rnb-map');

            wp_localize_script('rnb-map', 'RNB_MAP', [
                'markers'       => $markers,
                'pickup_title'  => esc_html__('Pickup Point', 'redq-rental'),
                'dropoff_title' => esc_html__('DropOff Point', 'redq-rental'),
            ]);
        }
    }

    public function rnb_screen_ids($screen_ids)
    {
        $screen_ids[] = 'toplevel_page_rnb_admin';
        $screen_ids[] = 'toplevel_page_rnb_addons';
        $screen_ids[] = 'edit-request_quote';
        $screen_ids[] = 'edit-inventory';
        $screen_ids[] = 'inventory';
        $screen_ids[] = 'edit-resource';
        $screen_ids[] = 'edit-rnb_categories';
        $screen_ids[] = 'edit-resource';
        $screen_ids[] = 'edit-person';
        $screen_ids[] = 'edit-deposite';
        $screen_ids[] = 'edit-attributes';
        $screen_ids[] = 'edit-features';
        $screen_ids[] = 'edit-pickup_location';
        $screen_ids[] = 'edit-dropoff_location';

        return $screen_ids;
    }

    /**
     * Plugin enqueues admin stylesheet and scripts
     *
     * @since 1.0.0
     */
    public function admin_styles_and_scripts($hook)
    {
        global $woocommerce, $wpdb;
        $screen = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        wp_register_script('jquery-ui-js', REDQ_ROOT_URL . '/assets/js/jquery-ui.js', ['jquery'], $ver = true, true);
        wp_register_style('jquery-ui-css', REDQ_ROOT_URL . '/assets/css/jquery-ui.css', [], $ver = false, $media = 'all');
        wp_register_script('select2.min', REDQ_ROOT_URL . '/assets/js/select2.min.js', ['jquery'], $ver = true, true);

        wp_register_script('jquery.datetimepicker.full', REDQ_ROOT_URL . '/assets/js/jquery.datetimepicker.full.js', ['jquery'], true);
        wp_enqueue_script('jquery.datetimepicker.full');

        wp_register_style('jquery.datetimepicker', REDQ_ROOT_URL . '/assets/css/jquery.datetimepicker.css', [], $ver = false, $media = 'all');
        wp_enqueue_style('jquery.datetimepicker');

        wp_register_style('redq-admin', REDQ_ROOT_URL . '/assets/css/redq-admin.css', [], $ver = false, $media = 'all');
        wp_register_style('redq-quote', REDQ_ROOT_URL . '/assets/css/redq-quote.css', [], $ver = false, $media = 'all');
        wp_register_script('icon-picker', REDQ_ROOT_URL . '/assets/js/icon-picker.js', ['jquery'], $ver = true, true);
        wp_register_script('redq_rental_writepanel_js', REDQ_ROOT_URL . '/assets/js/writepanel.js', ['jquery', 'jquery-ui-datepicker'], true);

        // Admin styles for WC , Inventory & RFQ pages only
        if (in_array($screen_id, wc_get_screen_ids(), true) && $screen_id !== 'shop_coupon') {
            $postid = get_the_ID();

            $params = [
                'plugin_url'     => $woocommerce->plugin_url(),
                'ajax_url'       => admin_url('admin-ajax.php'),
                'calendar_image' => $woocommerce->plugin_url() . '/assets/images/calendar.png',
            ];

            $products_by_inventory = $wpdb->get_results($wpdb->prepare("SELECT product FROM {$wpdb->prefix}rnb_inventory_product WHERE inventory = %d", $postid));

            if (isset($postid) && !empty($postid)) {
                $post_type = get_post_type($postid);
                $post_id = isset($post_type) && $post_type === 'inventory' && count($products_by_inventory) ? $products_by_inventory[0]->product : '';
                $conditions = redq_rental_get_settings($post_id, 'conditions');
                $admin_data = $conditions['conditions'];
                $params['calendar_data'] = $admin_data;
            }

            wp_enqueue_script('jquery-ui-js');
            wp_enqueue_style('jquery-ui-css');
            wp_enqueue_script('select2.min');
            wp_enqueue_style('redq-admin');
            wp_enqueue_style('redq-quote');
            wp_enqueue_script('icon-picker');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_media();
            wp_enqueue_style('font-awesome', '//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');
            wp_enqueue_script('redq_rental_writepanel_js');
            wp_localize_script('redq_rental_writepanel_js', 'RNB_ADMIN_DATA', $params);
        }
    }
}

new Rnb_Enqueue_Files();
