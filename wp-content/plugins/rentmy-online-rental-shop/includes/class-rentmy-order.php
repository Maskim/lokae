<?php

/**
 * Class RentMy_Order
 */
Class RentMy_Order extends RentMy
{
    function __construct() {
        if(!headers_sent() && '' == session_id())
            session_start();
    }

    function viewOrderDetails($order_id) {
        try {
            $response = self::fetch(
                '/orders/'.$order_id.'/complete',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }
}
