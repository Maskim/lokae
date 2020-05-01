<?php

/**
 * Class RentMy_Checkout
 */
Class RentMy_Checkout extends RentMy
{
    public $shipping_type = ['fedex' => 4, 'ups' => 5, 'standard' => 6];

    function __construct()
    {
        if (!headers_sent() && '' == session_id())
            session_start();
    }

    // capture data from first step of checkout
    function saveInfo($params)
    {
        self::setCheckoutSession('info', $params);
        return $params;

    }

    // capture data from second step of checkout
    function saveFulfilment($params)
    {
        if ($params['type'] == 'instore') {
            $data['delivery'] = $params;
            $data['shipping_method'] = 1;
            self::setCheckoutSession('fulfillment', $data);
        } elseif ($params['type'] == 'delivery') {
            $data['shipping_method'] = 1;
            $data['delivery'] = $params;
            self::setCheckoutSession('fulfillment', $data);
        } elseif ($params['type'] == 'shipping') {
            $data = $params;
            $data['delivery'] = json_decode(stripslashes($params['shipping']), true);
            $data['delivery']['type'] = $params['type'];
            unset($data['shipping']);
            self::setCheckoutSession('fulfillment', $data);
        }

        return $params;

    }


    // get checkout custom fields
    function getCustomFields()
    {
        try {
            $response = self::fetch(
                '/custom-fields',
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    // get terms and conditions fields
    function termsAndCondition()
    {
        try {
            $response = self::fetch(
                '/pages/terms-and-conditions',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    // check free shipping for the cart token
    function checkFreeShipping()
    {
        try {
            $response = self::fetch(
                '/free-shipping/' . $_SESSION['rentmy_cart_token'],
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    // upload files upon custom fields files field
    function uploadMedia($media)
    {
        try {
            $response = self::fetch(
                '/media/upload',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                $media
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    // get currency configurations
    function getCurrencyConfig()
    {
        try {
            $response = self::fetch(
                '/currency-config',
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }


    // get checkout location lists
    function getLocationLists()
    {
        try {
            $response = self::fetch(
                '/locations/list',
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    /**
     * get shipping methods
     * @param $data
     * @return mixed|string|null
     */
    function getShippingList($data)
    {
        unset($data['loc']);
        $response = self::rentmy_fetch(
            '/shipping/rate',
            [
                'token' => get_option('rentmy_accessToken'),
            ],
            [
                'address' => $data,
                'pickup' => get_option('rentmy_locationId'),
                'token' => $_SESSION['rentmy_cart_token']
            ]
        );
        if ($response['status'] == 'NOK') {
            return $response;
        }
        if ($response['status'] == 'OK') {
            if (!empty($response['result'])) {
                $fulfillment = [];
                $i = 0;
                $res = '';
                $html_head = '<h4 class="shipping-choose-label">Select Shipping Method</h4>';
                foreach ($response['result'] as $key => $shippings) {

                    if (strtolower($key) == 'standard') {
                        $shipping_method = 6;
                    } else {
                        $shipping_method = 4;
                    }

//                    $shipping_method = $this->shipping_type[$shipping['response']['carrier_code']];

                    foreach ($shippings as $shipping) {
                        $html = '<label class="radio-container radiolist-container">';
                        $json = json_encode($shipping);
                        $html .= "<input type='radio' data-type='" . $shipping_method . "'   data-amount='" . $shipping['charge'] . "' data-tax='" . $shipping['tax'] . "' name='shipping_method' value='" . $json . "'><span class='rentmy-radio-text'>" . $shipping['service_name'] . "</span>";
                        $html .= '<span class="rentmy-radio-date">Estimated Delivery Date: ' . date("F j, Y", strtotime($shipping['delivery_date'])) . '</span>';
                        $html .= '<span class="rentmy-radio-day">  Delivery days: ' . $shipping['delivery_days'] . '</span>';
                        $html .= '<span class="rentmy-radio-price">' . self::currency($shipping['charge']) . '</span>';
                        $html .= '<span class="checkmark"></span></label>';

                        $res .= $html;
                        $fulfillment['data'][$i] = ['html' => $html, 'cost' => $shipping['charge']];
                        $i++;
                    }
                }
                $fulfillment['html'] = $html_head . $res;
            }

        } else {
            $fulfillment = [];
        }
//        print_r( [
//            'address' => $data,
//            'pickup'=> 130,//get_option('rentmy_locationId'),
//            'token' => 1571943865922 //$_COOKIE['rentmy_cart_token']
//        ]);
        return $fulfillment;
    }

    /**
     * @param $data
     * @return mixed|string|null
     */
    function getDeliveryCost($data)
    {
        try {
            $response = self::fetch(
                '/delivery-charge-list',
                [
                    'token' => get_option('rentmy_accessToken'),
                ],
                [
                    'address' => $data,
                ]
            );

            return $response;
        } catch (Exception $e) {

        }
    }

    // get delivery addresses methods
    function addShippingToCarts($params)
    {
        try {
            if (!empty($_SESSION['rentmy_cart_token'])) {
                $response = self::rentmy_fetch(
                    '/carts/delivery',
                    [
                        'token' => get_option('rentmy_accessToken'),
                    ],
                    [
                        'shipping_cost' => $params['shipping_cost'],
                        'shipping_method' => $params['shipping_method'],
                        'tax' => $params['tax'],
                        'token' => $_SESSION['rentmy_cart_token'],
                    ]
                );
                return $response;
            } else {
                return ['status' => 'NOK', 'message' => 'Invalid cart token'];
            }
        } catch (Exception $e) {

        }
    }

    // finally do the checkout process
    function doCheckout($data)
    {

        try {
            $info = $_SESSION['rentmy_checkout']['info'];
            $fulfillment = $_SESSION['rentmy_checkout']['fulfillment'];
            $payment = $data;
            $cartToken = $_SESSION['rentmy_cart_token'];
            if (empty($cartToken)) {
                return ['status' => 'NOK', 'message' => 'Invalid cart.'];
            }
            $checkout_info = [
                'first_name' => $info['first_name'],
                'last_name' => $info['last_name'],
                'mobile' => $info['mobile'],
                'email' => $info['email'],
                'address_line1' => $info['address_line1'],
                'address2' => $info['address_line2'],
                'city' => $info['city'],
                'state' => $info['state'],
                'combinedAddress' => "",
                'country' => 'us',
                'zipcode' => $info['zipcode'],
                'custom_values' => null,
                'special_instructions' => $info['special_instructions'],
                'special_requests' => $info['special_requests'],
                'driving_license' => $info['driving_license'],
                'fieldSelection' => null,
                'fieldText' => null,
                'pickup' => 130,
                'delivery' => $fulfillment['delivery'],
                'shipping_method' => $fulfillment['shipping_method'],
                'currency' => 'USD',
                'token' => $cartToken,
                'custom_values' => null,
                'signature' => null,
                'gateway_id' => $payment['payment_gateway_id'],
                'type' => $payment['payment_gateway_type'],
                'note' => $payment['note'],
                'payment_gateway_name' => trim($payment['payment_gateway_name']),
                'account' => $payment['card_no'],
            ];
            if (!empty($info['signature'])) {
                $checkout_info['signature'] = trim($info['signature']);
            }
            if ($fulfillment['delivery']['type'] == 'shipping') {
                $checkout_info['shipping_address1'] = $fulfillment['shipping_address1'];
                $checkout_info['shipping_address2'] = $fulfillment['shipping_address2'];
                $checkout_info['shipping_city'] = $fulfillment['shipping_city'];
                $checkout_info['shipping_country'] = $fulfillment['shipping_country'];
                $checkout_info['shipping_email'] = $info['email'];
                $checkout_info['shipping_first_name'] = $info['first_name'];
                $checkout_info['shipping_last_name'] = $info['last_name'];
                $checkout_info['shipping_mobile'] = $info['mobile'];
                $checkout_info['shipping_state'] = $fulfillment['shipping_state'];
                $checkout_info['shipping_zipcode'] = $fulfillment['shipping_zipcode'];
            }
            if ($checkout_info['payment_gateway_name'] != 'Stripe' && $checkout_info['type'] == 1) {
                $checkout_info["expiry"] = $payment['exp_month'] . $payment['exp_year'];
                $checkout_info['cvv2'] = $payment['cvv'];
            }

            if (!empty($data['custom_values'])) {
                $checkout_info['custom_values'] = $data['custom_values'];
            }


            // added for partial payments
            if (!empty($payment['payment_amount'])) {
                $checkout_info['payment_amount'] = $payment['payment_amount'];
                $checkout_info['amount_tendered'] = 0;
            }
            // partial payment ends

//            print_r("<pre>");
//            print_r($checkout_info);
//            print_r("</pre>");
//            exit();
            $response = self::rentmy_fetch(
                '/orders/online',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'Location' => get_option('rentmy_locationId')
                ],
                $checkout_info
            );
            // print_r("<pre>");
            // print_r($response);
            // print_r("</pre>");exit();
            if (!$response['result']['data']['payment']['success']) {
                if (empty($response['result']['data']['payment']['message'])) {
                    $message = "Payment not completed successfully . Order can't be created. Please try again.";
                } else {
                    $message = $response['result']['data']['payment']['message'];
                }
                return ['status' => 'NOK', 'message' => $message];
            } else if (!$response['result']['data']['availability']['success']) {
                return ['status' => 'NOK', 'message' => "Order can't be created . Some products may not available . Please try again . "];
            }

            $_SESSION['order_uid'] = $response['result']['data']['order']['data']['uid'];
            // delete session && cookie
            unset($_SESSION['rentmy_cart_token']);
            unset($_SESSION['rentmy_checkout']);
            return ['status' => 'OK', 'uid' => $_SESSION['order_uid']];
        } catch (Exception $e) {

        }
    }

    /**
     * @param $type - info for billing details , fulfillment for shipping details
     * @param $data
     */
    function setCheckoutSession($type, $data)
    {
        $_SESSION['rentmy_checkout'][$type] = $data;
    }

    /**
     * @param string $type type = '' return full checkout details, info for billing, fulfillment for shipping/delivery
     * @return mixed
     */
    function getCheckoutSession($type = '')
    {
        if (empty($type)) {
            return $_SESSION['rentmy_checkout'];
        } else {
            return $_SESSION['rentmy_checkout'][$type];
        }
    }
}
