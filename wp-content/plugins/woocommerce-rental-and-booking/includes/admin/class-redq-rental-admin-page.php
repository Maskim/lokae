<?php

if (!defined('ABSPATH')) exit;

/**
 *
 */
class Redq_Rental_Admin_Page
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'redq_rental_admin_menu'));
        add_action('admin_menu', array($this, 'rnb_disable_add_new_inventory'), 10, 1);
        add_action('woocommerce_before_order_itemmeta', array($this, 'rnb_before_order_itemmeta'), 10, 3);
        add_action('woocommerce_order_status_changed', array($this, 'rnb_order_status_changed'), 10, 3);
        add_action('trashed_post', array($this, 'rnb_trashed_orders'), 10, 1);
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'redq_rental_hidden_order_meta'));
    }

    /**
     * redq_rental_admin_menu
     *
     * @version 1.0.0
     * @since 2.0.4
     */
    public function redq_rental_admin_menu()
    {
        add_menu_page($page_title = esc_html__('RnB Menu Page', 'redq-rental'), $menu_title = esc_html__('RnB Calendar', 'redq-rental'), $capability = 'publish_posts', $menu_slug = 'rnb_admin', $function = array($this, 'redq_rental_admin_main_menu_options'), $icon_url = 'dashicons-calendar-alt', 59);
        add_menu_page($page_title = esc_html__('RnB Add-ons', 'redq-rental'), $menu_title = esc_html__('RnB Add-ons', 'redq-rental'), $capability = 'publish_posts', $menu_slug = 'rnb_addons', $function = array($this, 'redq_rental_admin_addons_page'), $icon_url = 'dashicons-plus-alt', 58);
    }

    /**
     * redq_rental_admin_main_menu_options
     *
     * @version 1.0.0
     * @since 2.0.4
     */
    public function redq_rental_admin_main_menu_options()
    {
        if (!current_user_can('publish_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'redq-rental'));
        }

        include_once 'views/admin-menu-page-full-calender.php';
    }


    public function redq_rental_admin_addons_page()
    {
        if (!current_user_can('publish_posts')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'redq-rental'));
        }

        include_once 'views/admin-rnb-addons.php';
    }


    /**
     * Hide add new inventory link for inventory post type
     *
     * @version 3.0.1
     * @since 3.0.1
     */
    public function rnb_disable_add_new_inventory()
    {
        global $submenu;
        unset($submenu['edit.php?post_type=inventory'][10]);
        ob_start();
        if (isset($_GET['post_type']) && $_GET['post_type'] == 'inventory') {
            echo '<style type="text/css">
            #favorite-actions, .add-new-h2 { display:none; }
            </style>';
        }
        ob_clean();
    }


    /**
     * redq_rental_admin_menu
     *
     * @version 1.0.0
     * @since 2.0.4
     */
    public function redq_rental_hidden_order_meta($args)
    {
        $args[] = 'return_hidden_datetime';
        $args[] = 'pickup_hidden_datetime';
        $args[] = 'return_hidden_days';
        $args[] = 'redq_google_cal_sync_id';
        $args[] = 'booking_inventory';
        return $args;
    }


    /**
     * rnb_before_order_itemmeta
     *
     * @param string $item_id
     * @param array $item
     * @param object $product
     *
     * @return void
     */
    public function rnb_before_order_itemmeta($item_id, $item, $product)
    {
        if (isset($product) && !empty($product)) :
            $product_id = $product->get_id();
            if ($item->get_type() === 'line_item') {
                $order_id = get_the_ID();

                $is_translated = apply_filters('wpml_element_has_translations', NULL, $product_id, 'product');
                if (in_array('sitepress-multilingual-cms/sitepress.php', apply_filters('active_plugins', get_option('active_plugins'))) && function_exists('icl_object_id') && $is_translated) {
                    $order_lang = get_post_meta($order_id, 'rnb_place_order_in_lang', true);
                    $inventory_id = get_post_meta($order_id, 'order_item_' . $item_id . '_' . $order_lang . '_inventory_ref', true);
                } else {
                    $inventory_id = get_post_meta($order_id, 'order_item_' . $item_id . '_inventory_ref', true);
                }

                if (!empty($inventory_id)) {
                    echo '<div class="rnb-inventory-ref"> <span class="title"> ' . esc_html__('Inventory Reference', 'redq-rental') . ' : </span>';
                    echo '<a target="_blank" href="' . get_edit_post_link($inventory_id) . '">' . get_the_title($inventory_id) . '</a>';
                    echo '</div>';
                }
            }
        endif;
    }


    /**
     * rnb_order_status_changed
     *
     * @param string $order_id
     * @param string $old_status
     * @param string $new_status
     *
     * @return void
     */
    public function rnb_order_status_changed($order_id, $old_status, $new_status)
    {
        $order = new WC_Order($order_id);
        $items = $order->get_items();
        if (isset($items) && !empty($items)) :
            foreach ($items as $item_key => $item_value) {
                $item_id = $item_key;
                $product_id = $item_value->get_product_id();
                $product = wc_get_product($product_id);
                $product_type = $product ? $product->get_type() : '';
                if (isset($product_type) && $product_type === 'redq_rental' && $new_status === 'cancelled' && $old_status !== 'cancelled') :
                    $args = array(
                        'product_id' => $product_id,
                        'order_id'   => $order_id,
                        'item_id'    => $item_id
                    );
                    rnb_booking_dates_update($args);
                endif;
            }
        endif;
    }


    /**
     * Delete posts
     *
     * @version 1.0.0
     * @since 2.0.4
     */
    public function rnb_trashed_orders($order_id)
    {
        $post_type = get_post_type($order_id);
        if (isset($post_type) && $post_type === 'shop_order') :
            $order = new WC_Order($order_id);
            $items = $order->get_items();
            if (isset($items) && !empty($items)) :
                foreach ($items as $item_key => $item_value) {
                    $item_id = $item_key;
                    $product_id = $item_value->get_product_id();
                    $product = wc_get_product($product_id);
                    $product_type = $product ? $product->get_type() : '';
                    if (isset($product_type) && $product_type === 'redq_rental') {
                        $args = array(
                            'product_id' => $product_id,
                            'order_id'   => $order_id,
                            'item_id'    => $item_id
                        );
                        rnb_booking_dates_update($args);
                    }
                }
            endif;
        endif;
    }


    /**
     * Check array key from multi-dimentional array
     *
     * @version 3.0.9
     * @since 2.0.4
     */
    public function get_multidimentional_array_index($products, $field, $value)
    {
        foreach ($products as $key => $product) {
            if ($product->$field === $value)
                return $key;
        }
        return false;
    }


    /**
     * Sync delete order and available dates
     *
     * @version 3.0.9
     * @since 2.0.4
     */
    public function rnb_sync_order_dates($order_id, $item_id, $product_id, $booked_dates_ara)
    {

        $reset_buffer_days = array();

        $is_translated = apply_filters('wpml_element_has_translations', NULL, $product_id, 'product');
        if (in_array('sitepress-multilingual-cms/sitepress.php', apply_filters('active_plugins', get_option('active_plugins'))) && function_exists('icl_object_id') && $is_translated) {
            /**
             * Disabled booking dates for all laguages for a certain product
             *
             * This will work only if WPML is installed
             */
            $languages = apply_filters('wpml_active_languages', NULL, 'orderby=id&order=desc');
            $translated_posts = array();

            if (isset($languages) && sizeof($languages) !== 0) {
                foreach ($languages as $lang_key => $lang_value) {
                    $id = icl_object_id($product_id, 'product', false, $lang_value['language_code']);

                    $block_dates_times = get_post_meta($id, 'redq_block_dates_and_times', true);
                    $block_datetimes = array();
                    $deleted_block_dates_times = array();
                    $copied_block_dates_times = $block_dates_times;
                    $flag = 0;

                    $inventory_refs = get_post_meta($order_id, 'order_item_' . $item_id . '_' . $lang_key . '_inventory_ref', true);
                    foreach ($copied_block_dates_times as $key => $value) {

                        if (in_array($key, $inventory_refs)) {
                            $updated_ara = array();
                            $updated_only_block_dates_ara = array();
                            $deleted_ara = array();
                            $deleted_only_block_dates_ara = array();

                            foreach ($value['block_dates'] as $bkey => $bvalue) {
                                if (in_array($bvalue['from'], $booked_dates_ara)) {
                                    $deleted_ara[] = $bvalue;
                                    $flag = 1;
                                    continue;
                                }
                                $updated_ara[] = $bvalue;
                            }

                            foreach ($value['only_block_dates'] as $obkey => $obvalue) {
                                if (in_array($obvalue, $booked_dates_ara)) {
                                    $deleted_only_block_dates_ara[] = $obvalue;
                                    continue;
                                }
                                $updated_only_block_dates_ara[] = $obvalue;
                            }

                            update_post_meta($key, 'redq_rental_availability', $updated_ara);

                            $block_dates_times[$key]['block_dates'] = $updated_ara;
                            $block_dates_times[$key]['only_block_dates'] = $updated_only_block_dates_ara;

                            $deleted_block_dates_times[$key]['deleted_block_dates'] = $deleted_ara;
                            $deleted_block_dates_times[$key]['deleted_only_block_dates'] = $deleted_only_block_dates_ara;
                        }
                        //if( $flag === 1 )break;
                    }
                    update_post_meta($id, 'redq_block_dates_and_times', $block_dates_times);
                    update_post_meta($id, 'redq_deleted_block_dates_and_times', $deleted_block_dates_times);
                }
                update_post_meta($order_id, 'order_item_' . $item_id . '_extra_pre_buffer_dates', $reset_buffer_days);
                update_post_meta($order_id, 'order_item_' . $item_id . '_extra_buffer_dates', $reset_buffer_days);
            }
        } else {

            $block_dates_times = get_post_meta($product_id, 'redq_block_dates_and_times', true);
            $block_datetimes = array();
            $deleted_block_dates_times = array();
            $copied_block_dates_times = $block_dates_times;
            $flag = 0;

            $inventory_refs = get_post_meta($order_id, 'order_item_' . $item_id . '_inventory_ref', true);
            $inventory_refs = is_array($inventory_refs) ? $inventory_refs : array();

            foreach ($copied_block_dates_times as $key => $value) {
                if (in_array($key, $inventory_refs)) {
                    $updated_ara = array();
                    $updated_only_block_dates_ara = array();
                    $deleted_ara = array();
                    $deleted_only_block_dates_ara = array();

                    foreach ($value['block_dates'] as $bkey => $bvalue) {
                        if (in_array($bvalue['from'], $booked_dates_ara)) {
                            $deleted_ara[] = $bvalue;
                            $flag = 1;
                            continue;
                        }
                        $updated_ara[] = $bvalue;
                    }

                    foreach ($value['only_block_dates'] as $obkey => $obvalue) {
                        if (in_array($obvalue, $booked_dates_ara)) {
                            $deleted_only_block_dates_ara[] = $obvalue;
                            continue;
                        }
                        $updated_only_block_dates_ara[] = $obvalue;
                    }

                    update_post_meta($key, 'redq_rental_availability', $updated_ara);

                    $block_dates_times[$key]['block_dates'] = $updated_ara;
                    $block_dates_times[$key]['only_block_dates'] = $updated_only_block_dates_ara;

                    $deleted_block_dates_times[$key]['deleted_block_dates'] = $deleted_ara;
                    $deleted_block_dates_times[$key]['deleted_only_block_dates'] = $deleted_only_block_dates_ara;
                }

                //if( $flag === 1 )break;
            }

            update_post_meta($product_id, 'redq_block_dates_and_times', $block_dates_times);
            update_post_meta($product_id, 'redq_deleted_block_dates_and_times', $deleted_block_dates_times);

            update_post_meta($order_id, 'order_item_' . $item_id . '_extra_pre_buffer_dates', $reset_buffer_days);
            update_post_meta($order_id, 'order_item_' . $item_id . '_extra_buffer_dates', $reset_buffer_days);
        }
    }
}

new Redq_Rental_Admin_Page();
