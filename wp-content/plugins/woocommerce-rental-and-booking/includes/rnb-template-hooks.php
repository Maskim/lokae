<?php
/**
 * RnB Template Hooks
 *
 * Action/filter hooks used for RnB functions/templates.
 *
 * @author        RedQTeam
 * @category    Core
 * @package    RnB/Templates
 * @version     2.1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Content Wrappers.
 *
 * @see woocommerce_output_content_wrapper()
 */
add_action('rnb_before_add_to_cart_form', 'rnb_price_flip_box', 10);
add_action('rnb_before_add_to_cart_form', 'rnb_validation_notice', 15);

/**
 * Content Wrappers.
 *
 * @see woocommerce_output_content_wrapper()
 */
add_action('rnb_main_rental_content', 'rnb_pickup_locations', 10);

add_action('rnb_main_rental_content', 'rnb_return_locations', 15);
add_action('rnb_main_rental_content', 'rnb_pickup_datetimes', 20);
add_action('rnb_main_rental_content', 'rnb_return_datetimes', 25);
add_action('rnb_main_rental_content', 'rnb_quantity', 28);
add_action('rnb_main_rental_content', 'rnb_payable_resources', 30);
add_action('rnb_main_rental_content', 'rnb_payable_categories', 40);
add_action('rnb_main_rental_content', 'rnb_payable_persons', 50);
add_action('rnb_main_rental_content', 'rnb_payable_deposits', 60);
add_action('rnb_main_rental_content', 'rnb_select_inventory', 5);

/**
 * Content Wrappers.
 *
 * @see rnb_modal_booking_func()
 */
add_action('rnb_modal_booking', 'rnb_modal_booking_func', 10);

/**
 * Content Wrappers.
 *
 * @see woocommerce_output_content_wrapper()
 */

$rnb_booking_layout = get_post_meta(get_the_ID(), 'rnb_booking_layout', true);
if ($rnb_booking_layout === 'layout_two') {
    add_action('woocommerce_before_add_to_cart_button', 'rnb_booking_summary_two', 10);
} else {
    add_action('woocommerce_before_add_to_cart_button', 'rnb_booking_summary', 10);
}

/**
 * Content Wrappers.
 *
 * @see woocommerce_output_content_wrapper()
 */
add_action('rnb_plain_booking_button', 'rnb_direct_booking', 10);
add_action('rnb_plain_booking_button', 'rnb_request_quote', 20);
