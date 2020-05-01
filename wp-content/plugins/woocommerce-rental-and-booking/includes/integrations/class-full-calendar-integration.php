<?php

class WC_Full_Calendar_API
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'full_calendar_styles_and_scripts']);
    }

    /**
     * Plugin enqueues admin stylesheet and scripts.
     *
     * @since 1.0.0
     *
     * @param mixed $hook
     */
    public function full_calendar_styles_and_scripts($hook)
    {
        global $post, $woocommerce, $wp_scripts;

        wp_register_script('moment', REDQ_ROOT_URL . '/assets/js/moment.js', ['jquery'], $ver = true, true);
        wp_enqueue_script('moment');

        wp_register_style('qtip2', '//cdnjs.cloudflare.com/ajax/libs/qtip2/3.0.3/jquery.qtip.min.css', [], $ver = false, $media = 'all');
        wp_enqueue_style('qtip2');

        wp_register_script('qtip2', '//cdn.jsdelivr.net/qtip2/3.0.3/jquery.qtip.min.js', ['jquery', 'moment'], $ver = true, true);
        wp_enqueue_script('qtip2');

        wp_register_style('fullcalendar', REDQ_ROOT_URL . '/assets/css/fullcalendar.css', [], $ver = false, $media = 'all');
        wp_enqueue_style('fullcalendar');

        wp_register_script('fullcalendar.min', REDQ_ROOT_URL . '/assets/js/fullcalendar.min.js', ['jquery'], $ver = false, true);
        wp_enqueue_script('fullcalendar.min');

        wp_register_script('locale-all', REDQ_ROOT_URL . '/assets/js/locale-all.js', ['jquery'], $ver = false, true);
        wp_enqueue_script('locale-all');

        wp_register_style('magnific-popup', REDQ_ROOT_URL . '/assets/css/magnific-popup.css', [], $ver = false, $media = 'all');
        wp_enqueue_style('magnific-popup');

        wp_register_script('magnific-popup', REDQ_ROOT_URL . '/assets/js/jquery.magnific-popup.min.js', ['jquery'], $ver = false, true);
        wp_enqueue_script('magnific-popup');

        $this->redq_show_rental_data_on_full_calendar($hook);
    }

    /**
     * Show all booking data on full calendar.
     *
     * @since 2.4.0
     *
     * @param mixed $hook
     */
    public function redq_show_rental_data_on_full_calendar($hook)
    {
        if ('toplevel_page_rnb_admin' === $hook) {
            global $wpdb;

            $args = [
                'post_type' => 'shop_order',
                'post_status' => 'any',
                'posts_per_page' => -1,
            ];

            $orders = get_posts($args);

            $fullcalendar = [];

            if (isset($orders) && !empty($orders)) {
                foreach ($orders as $o) {
                    $order_id = $o->ID;
                    $order = new WC_Order($order_id);

                    foreach ($order->get_items() as $item) {
                        $item_meta_array = $item['item_meta_array'];

                        $order_item_id = $item->get_id();
                        $product_id = $item->get_product_id();
                        $quantity = $item->get_quantity();
                        $order_item_details = $item->get_formatted_meta_data('');

                        $fullcalendar[$order_item_id]['post_status'] = $o->post_status;
                        $fullcalendar[$order_item_id]['title'] = get_the_title($product_id) . ' ×' . $quantity;
                        $fullcalendar[$order_item_id]['link'] = get_the_permalink($product_id);
                        $fullcalendar[$order_item_id]['id'] = $order_id;
                        $fullcalendar[$order_item_id]['description'] = '<table cellspacing="0" class="redq-rental-display-meta"><tbody><tr><th>' . __('Order ID:', 'redq-rental') . '</th><td>#' . $order_id . '</td></tr>';
                        $fullcalendar[$order_item_id]['description'] .= '<table cellspacing="0" class="redq-rental-display-meta"><tbody><tr><th>' . __('Quantity:', 'redq-rental') . '</th><td>' . $quantity . '</td></tr>';

                        $is_translated = apply_filters('wpml_element_has_translations', null, $product_id, 'product');

                        foreach ($order_item_details as $order_item_key => $order_item_value) {
                            if ('pickup_hidden_datetime' !== $order_item_value->key && 'return_hidden_datetime' !== $order_item_value->key && 'return_hidden_days' !== $order_item_value->key && 'booking_inventory' !== $order_item_value->key) {
                                $fullcalendar[$order_item_id]['description'] .= '<tr><th>' . $order_item_value->key . '</th><td>' . $order_item_value->value . '</td></tr>';
                            }

                            if ('pickup_hidden_datetime' === $order_item_value->key) {
                                $pickup_datetime = explode('|', $order_item_value->value);
                                $fullcalendar[$order_item_id]['start'] = $pickup_datetime[0];
                                $fullcalendar[$order_item_id]['start_time'] = isset($pickup_datetime[1]) ? $pickup_datetime[1] : '';
                            }

                            if ('return_hidden_datetime' === $order_item_value->key) {
                                $return_datetime = explode('|', $order_item_value->value);
                                $fullcalendar[$order_item_id]['end'] = $return_datetime[0];
                                $fullcalendar[$order_item_id]['return_date'] = $return_datetime[0];
                                $fullcalendar[$order_item_id]['return_time'] = isset($return_datetime[1]) ? $return_datetime[1] : '';
                            }

                            $fullcalendar[$order_item_id]['url'] = admin_url('post.php?post=' . absint($order->get_id()) . '&action=edit');
                        }

                        $order_total = $order->get_formatted_order_total();

                        $fullcalendar[$order_item_id]['description'] .= '<tr><th>' . esc_html__('Order Total', 'redq-rental') . '</th><td>' . $order_total . '</td>';
                        $fullcalendar[$order_item_id]['description'] .= '</tbody></table>';
                    }
                }
            }

            $calendar_data = [];

            if (isset($fullcalendar) && !empty($fullcalendar)) {
                foreach ($fullcalendar as $key => $value) {
                    if (array_key_exists('start', $value) && array_key_exists('end', $value)) {
                        $calendar_data[$key] = $value;
                    }

                    if (array_key_exists('start', $value) && !array_key_exists('end', $value)) {
                        $start_info = isset($value['start_time']) && !empty($value['start_time']) ? $value['start'] . 'T' . $value['start_time'] : $value['start'];
                        $return_info = isset($value['return_time']) && !empty($value['return_time']) ? $value['start'] . 'T' . $value['return_time'] : $value['start'];

                        $value['start'] = rnb_format_date_time($start_info);
                        $value['end'] = rnb_format_date_time($return_info);

                        $calendar_data[$key] = $value;
                    }

                    if (array_key_exists('end', $value) && !array_key_exists('start', $value)) {
                        $start_info = isset($value['start_time']) && !empty($value['start_time']) ? $value['end'] . 'T' . $value['start_time'] : $value['end'];
                        $return_info = isset($value['return_time']) && !empty($value['return_time']) ? $value['end'] . 'T' . $value['return_time'] : $value['end'];

                        $value['start'] = rnb_format_date_time($start_info);
                        $value['end'] = rnb_format_date_time($return_info);

                        $calendar_data[$key] = $value;
                    }

                    if (array_key_exists('start', $value) && array_key_exists('end', $value)) {
                        $start_info = isset($value['start_time']) && !empty($value['start_time']) ? $value['start'] . 'T' . $value['start_time'] : $value['start'];
                        $return_info = isset($value['return_time']) && !empty($value['return_time']) ? $value['end'] . 'T' . $value['return_time'] : $value['end'];

                        $value['start'] = rnb_format_date_time($start_info);
                        $value['end'] = rnb_format_date_time($return_info);

                        $calendar_data[$key] = $value;
                    }
                }
            }

            wp_register_script('redq-admin-page', REDQ_ROOT_URL . '/assets/js/admin-page.js', ['jquery'], $ver = false, true);
            wp_enqueue_script('redq-admin-page');

            $loc_data = [
                'calendar_data' => $calendar_data,
                'lang_domain' => get_option('rnb_lang_domain', 'en'),
                'day_of_week_start' => get_option('rnb_day_of_week_start', 1) - 1,
            ];

            wp_localize_script('redq-admin-page', 'REDQRENTALFULLCALENDER', $loc_data);
        }
    }
}

new WC_Full_Calendar_API();
