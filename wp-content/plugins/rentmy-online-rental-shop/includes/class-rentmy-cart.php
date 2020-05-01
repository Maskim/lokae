<?php

/**
 * Class RentMy_Cart
 */
Class RentMy_Cart extends RentMy
{
    /**
     * submit cart using these method
     * @return mixed|string|null
     */
    function addProductToCart($params)
    {
        try {
            $params['location'] = get_option('rentmy_locationId');
            $params['token'] = $_SESSION['rentmy_cart_token'];
            $response = self::rentmy_fetch(
                '/carts/add-to-cart',
                get_option('rentmy_accessToken'),
                $params,
                null
            );
            if ($response['status'] == 'OK') {
                if (!empty($response['result']['data']['token'])) {
                    self::setCartToken($response['result']['data']['token']);
                    self::setRentStart($response['result']['data']['rent_start']);
                    self::setRentEnd($response['result']['data']['rent_end']);
                    return $response;
                }else{
                    return ['status'=> 'NOK','result'=> $response['result']];
                }
            }

            return $response;
        } catch (Exception $e) {

        }
    }
    /**
     * Package add to cart .
     * @return mixed|string|null
     */
    function addPackageToCart($params)
    {
        try {
            $params['location'] = get_option('rentmy_locationId');
            $params['token'] = $_SESSION['rentmy_cart_token'];
            $response = self::rentmy_fetch(
                '/carts/add-to-cart',
                get_option('rentmy_accessToken'),
                $params,
                null
            );
            if ($response['status'] == 'OK') {
                if (!empty($response['result']['data']['token'])) {
                    self::setCartToken($response['result']['data']['token']);
                    self::setRentStart($response['result']['data']['rent_start']);
                    self::setRentEnd($response['result']['data']['rent_end']);
                    return $response;
                }else{
                    return ['status'=> 'NOK','result'=> $response['result']];
                }
            }

            return $response;
        } catch (Exception $e) {

        }
    }

    /**
     * view cart using these method
     * @return mixed|string|null
     */
    function viewCart()
    {
        try {
            $response = self::fetch(
                '/carts/' . $_SESSION['rentmy_cart_token'],
                get_option('rentmy_accessToken'),
                null,
                null
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    /** get related products by cart token ...
     * @param $data
     * @return mixed
     */
    function get_related_products_cart($token)
    {
        try {
            $location_id = get_option('rentmy_locationId');
            $response = self::rentmy_fetch(
                '/products/'.$token.'/user/related-products?source=cart',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => $location_id
                ]
            );
            return $response;
        } catch (Exception $e) {

        }

    }

    /**
     * update cart using these method
     * @return mixed|string|null
     */
    function updateCart($params)
    {
        try {
            $response = self::fetch(
                '/carts/update',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                [
                    'id' => $params['id'],
                    'increment' => $params['increment'],
                    'token' => $_SESSION['rentmy_cart_token'],
                    'price' => $params['price'],
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }


    /**
     * get cart available products
     * @return mixed|string|null
     */
    function getCartAvailability($params)
    {
        try {
            $response = self::rentmy_fetch(
                '/products/availability',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                [
                    'start_date' => $params['start_date'],
                    'end_date' => $params['end_date'],
                    'token' => $_SESSION['rentmy_cart_token'],
                    'type' => $params['type'],
                    'source' => $params['source'],
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    /**
     * delete cart using these method
     * @return mixed|string|null
     */
    function deleteCart($params)
    {
        try {
            $response = self::fetch(
                '/carts/cart-remove-item',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                [
                    'cart_item_id' => $params['cart_item_id'],
                    'product_id' => $params['product_id'],
                    'token' => $_SESSION['rentmy_cart_token']
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    /**
     * apply coupon to cart using these method
     * @return mixed|string|null
     */
    function applyCoupon($params)
    {
        try {
            $response = self::fetch(
                '/carts/apply-coupon',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                [
                    'coupon' => $params['coupon'],
                    'token' => $_SESSION['rentmy_cart_token']
                ]
            );
            return $response;
        } catch (Exception $e) {

        }
    }

    /**
     * Get cart Token from session
     */
    function getCartToken()
    {
        return $_SESSION['rentmy_cart_token'];
    }

    /** Set cart token to Session */
    function setCartToken($token)
    {
        $_SESSION['rentmy_cart_token'] = $token;
    }

    /** Save cart details into session */
    function setCartSession($data)
    {
        $_SESSION['rentmy_cart'] = $data;
    }

    /** Get Cart details from session */
    function getCartSession()
    {
        return $_SESSION['rentmy_cart'];
    }

    // set rent start date
    function setRentStart($date){
        $_SESSION['rentmy_rent_start'] = $date;
    }

    // set rent end date
    function setRentEnd($date){
        $_SESSION['rentmy_rent_end'] = $date;
    }

    // get rent start date
    function getRentStart(){
        return $_SESSION['rentmy_rent_start'];
    }

    // get rent end date
    function getRentEnd(){
        return $_SESSION['rentmy_rent_end'];
    }
}
