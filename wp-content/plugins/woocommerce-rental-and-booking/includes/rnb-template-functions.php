<?php

/**
 * RNB Template
 *
 * Functions for the templating system.
 *
 * @author   RedQteam
 * @category Core
 * @package  RnB/Functions
 * @version  2.5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!function_exists('rnb_price_flip_box')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_price_flip_box()
    {
        rnb_get_template('rnb/global/price-flip-box.php');
    }
}

if (!function_exists('rnb_validation_notice')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_validation_notice()
    {
        rnb_get_template('rnb/global/rnb-notice.php');
    }
}

if (!function_exists('rnb_select_inventory')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_select_inventory()
    {
        rnb_get_template('rnb/booking-content/select-inventory.php');
    }
}

if (!function_exists('rnb_pickup_locations')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_pickup_locations()
    {
        global $post;

        $inventory_id = rnb_get_default_inventory_id($post->ID);
        $has_term = rnb_has_term($inventory_id, 'pickup_location');

        if ($has_term) {
            rnb_get_template('rnb/booking-content/pickup-locations.php');
        }
    }
}

if (!function_exists('rnb_return_locations')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_return_locations()
    {
        global $post;

        $inventory_id = rnb_get_default_inventory_id($post->ID);
        $has_term = rnb_has_term($inventory_id, 'dropoff_location');

        if ($has_term) {
            rnb_get_template('rnb/booking-content/return-locations.php');
        }
    }
}

if (!function_exists('rnb_pickup_datetimes')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_pickup_datetimes()
    {
        rnb_get_template('rnb/booking-content/pickup-datetimes.php');
    }
}

if (!function_exists('rnb_return_datetimes')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_return_datetimes()
    {
        rnb_get_template('rnb/booking-content/return-datetimes.php');
    }
}

if (!function_exists('rnb_quantity')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_quantity()
    {
        rnb_get_template('rnb/booking-content/quantity.php');
    }
}

if (!function_exists('rnb_payable_categories')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_payable_categories()
    {
        global $post;

        $inventory_id = rnb_get_default_inventory_id($post->ID);
        $has_term = rnb_has_term($inventory_id, 'rnb_categories');

        if ($has_term) {
            rnb_get_template('rnb/booking-content/rnb-categories.php');
        }
    }
}

if (!function_exists('rnb_payable_resources')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_payable_resources()
    {
        global $post;

        $inventory_id = rnb_get_default_inventory_id($post->ID);
        $has_term = rnb_has_term($inventory_id, 'resource');

        if ($has_term) {
            rnb_get_template('rnb/booking-content/payable-resources.php');
        }
    }
}

if (!function_exists('rnb_payable_persons')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_payable_persons()
    {
        global $post;

        $inventory_id = rnb_get_default_inventory_id($post->ID);
        $has_term = rnb_has_term($inventory_id, 'person');

        if ($has_term) {
            rnb_get_template('rnb/booking-content/payable-persons.php');
        }
    }
}

if (!function_exists('rnb_payable_deposits')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_payable_deposits()
    {
        global $post;

        $inventory_id = rnb_get_default_inventory_id($post->ID);
        $has_term = rnb_has_term($inventory_id, 'deposite');

        if ($has_term) {
            rnb_get_template('rnb/booking-content/payable-deposits.php');
        }
    }
}

if (!function_exists('rnb_booking_summary')) {
    function rnb_booking_summary()
    {
        rnb_get_template('rnb/booking-content/booking-summary.php');
    }
}

if (!function_exists('rnb_booking_summary_two')) {
    function rnb_booking_summary_two()
    {
        rnb_get_template('rnb/booking-content/booking-summary-two.php');
    }
}

if (!function_exists('rnb_direct_booking')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_direct_booking()
    {
        rnb_get_template('rnb/booking-content/direct-booking.php');
    }
}

if (!function_exists('rnb_request_quote')) {
    /**
     * Output the start of the page wrapper.
     *
     */
    function rnb_request_quote()
    {
        rnb_get_template('rnb/booking-content/request-quote.php');
    }
}

/**
 * Display meta data belonging to an item.
 * @param array $item
 */
function display_item_meta($item)
{
    $product = $this->get_product_from_item($item);

    $item_meta = new WC_Order_Item_Meta($item, $product);
    $item_meta->display();
}

if (!function_exists('rnb_modal_booking_func')) {
    /**
     * Output of the modal
     *
     */
    function rnb_modal_booking_func()
    {
        rnb_get_template('rnb/booking-content/booking-modal.php');
    }
}

/**
 * Multi-select fields
 *
 * @param array $field
 * @return void
 */
function rnb_multi_select_field($field)
{
    global $thepostid, $post;

    $thepostid              = empty($thepostid) ? $post->ID : $thepostid;
    $field['class']         = isset($field['class']) ? $field['class'] : 'select short';
    $field['wrapper_class'] = isset($field['wrapper_class']) ? $field['wrapper_class'] : '';
    $field['name']          = isset($field['name']) ? $field['name'] : $field['id'];
    $field['value']         = isset($field['value']) ? $field['value'] : (get_post_meta($thepostid, $field['id'], true) ? get_post_meta($thepostid, $field['id'], true) : array());

    echo '<p class="form-field ' . esc_attr($field['id']) . '_field ' . esc_attr($field['wrapper_class']) . '"><label for="' . esc_attr($field['id']) . '">' . wp_kses_post($field['label']) . '</label><select id="' . esc_attr($field['id']) . '" name="' . esc_attr($field['name']) . '" class="' . esc_attr($field['class']) . '" multiple="multiple">';

    foreach ($field['options'] as $key => $value) {
        echo '<option value="' . esc_attr($key) . '" ' . (in_array($key, $field['value']) ? 'selected="selected"' : '') . '>' . esc_html($value) . '</option>';
    }

    echo '</select> ';

    if (!empty($field['description'])) {
        if (isset($field['desc_tip']) && false !== $field['desc_tip']) {
            echo '<img class="help_tip" data-tip="' . esc_attr($field['description']) . '" src="' . esc_url(WC()->plugin_url()) . '/assets/images/help.png" height="16" width="16" />';
        } else {
            echo '<span class="description">' . wp_kses_post($field['description']) . '</span>';
        }
    }
    echo '</p>';
}
