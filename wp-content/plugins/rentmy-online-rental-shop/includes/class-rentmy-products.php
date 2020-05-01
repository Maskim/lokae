<?php

/**
 * Class RentMy_Products
 */
class RentMy_Products extends RentMy
{
    /**
     * @param $params
     * @return mixed
     */
    function productList($params)
    {
        try {
            $response = self::fetch(
                '/products/online',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                [
                    'page_no' => $params['page_no'],
                    'limit' => $params['limit'],
                    'tag_id' => $params['tag_id'],
                    'price_max' => $params['price_max'],
                    'price_min' => $params['price_min'],
                    'purchase_type' => $params['purchase_type'],
                    'all' => true,
                    'sort'=> !empty($params['sort']) ? $params['sort'] : '',
                    'sort_type'=> !empty($params['sort_type']) ? $params['sort_type'] : ''
                ]
            );
            return $response;
        } catch (Exception $e) {

        }

    }

    /**
     * @param $params
     * @return mixed|string|null
     */
    function productListByCategory($params)
    {
        try {
            $response = self::fetch(
                '/category/products/' . $params['category_id'],
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                [
                    'page_no' => $params['page_no'],
                    'limit' => $params['limit'],
                    'tag_id' => $params['tag_id'],
                    'price_max' => $params['price_max'],
                    'price_min' => $params['price_min'],
                    'purchase_type' => $params['purchase_type'],
                    'all' => true,
                    'sort'=> !empty($params['sort']) ? $params['sort'] : '',
                    'sort_type'=> !empty($params['sort_type']) ? $params['sort_type'] : ''
                ]
            );
            return $response;
        } catch (Exception $e) {

        }

    }

    /**
     * @param $params
     * @return mixed|string|null
     */
    function productSearch($params)
    {
        try {
            $response = self::fetch(
                '/search/products/',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                [
                    'page_no' => $params['page_no'],
                    'limit' => $params['limit'],
                    'search' => $params['search'],
                    'category_id' => '',
                    'sort'=> !empty($params['sort']) ? $params['sort'] : '',
                    'sort_type'=> !empty($params['sort_type']) ? $params['sort_type'] : ''
                ]
            );
            return $response;
        } catch (Exception $e) {

        }

    }

    /**
     * @param $product_id
     * @return mixed|string|null
     */
    function details($product_id, $cart_params = null)
    {
        try {

            if( !empty($cart_params['token']) && !empty($cart_params['start_date']) && !empty($cart_params['end_date']) ){
                $add_params = '&token=' . $cart_params['token'] . '&start_date=' . urlencode($cart_params['start_date']) . '&end_date=' . urlencode($cart_params['end_date']);
            } else {
                $add_params = '';
            }

            $location_id = get_option('rentmy_locationId');
            $response = self::fetch(
                '/products/' . $product_id . '?location=' . $location_id . $add_params,
                [
                    'token' => get_option('rentmy_accessToken'),
                ],
                null,
                null
            );
            return $response;
        } catch (Exception $e) {

        }

    }

    /**
     * @param $package_id
     * @return mixed|string|null
     */
    function package_details($product_id, $cart_params = null)
    {
        try {

            if( !empty($cart_params['token']) && !empty($cart_params['start_date']) && !empty($cart_params['end_date']) ){
                $add_params = '&token=' . $cart_params['token'] . '&start_date=' . $cart_params['start_date'] . '&end_date=' . $cart_params['end_date'];
            } else {
                $add_params = '';
            }

            $location_id = get_option('rentmy_locationId');
            $response = self::rentmy_fetch(
                '/package-details/' . $product_id . '/360?location=' . $location_id . $add_params,
                [
                    'token' => get_option('rentmy_accessToken'),
                ],
                null,
                null
            );
            // print_r("<pre>");print_r($response);print_r("</pre>");
            return $response['result'];
        } catch (Exception $e) {

        }

    }

    /** Check package availability
     * @param $data
     * @return mixed
     */
    function check_package_availability($data)
    {
        try {

            $location_id = get_option('rentmy_locationId');
            $params = [];
            foreach ($data['products'] as $p) {
                $params['variants[]'] = $p['variants_products_id'];
            }

            $response = self::rentmy_fetch(
                '/package/' . $data['product_uid'] . '/availability',
                [
                    'token' => get_option('rentmy_accessToken'),
                ],
                null,
                $params
            );
            return $response;
        } catch (Exception $e) {

        }

    }

    /**
     * @param $data ['product_id']
     * @param $data ['variant_id']
     * @param $data ['chain']
     *
     */
    function get_product_variant_chain($data)
    {
        try {
            $response = self::fetch(
                '/variant-chain?product_id=' . $data['product_id'] . '&variant_id=' . $data['variant_id'] . '&variant_chain=',
                [
                    'token' => get_option('rentmy_accessToken'),
                ],
                null,
                null
            );
            return !empty($response['data']) ? $response['data'] : [];
        } catch (Exception $e) {

        }
    }

    function get_product_fromchain($data)
    {
        try {
            $response = self::fetch(
                '/get-path-of-chain?product_id=' . $data['product_id'] . '&variant_id=' . $data['variant_id'] . '&variant_chain=' . $data['chain_id'],
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                null,
                null
            );
            return !empty($response['data']) ? $response['data'] : [];
        } catch (Exception $e) {

        }
    }

    /** Check package and item available with price returned
     * @param $data
     * @return mixed
     */
    function get_price_value($data)
    {
        try {

            $location_id = get_option('rentmy_locationId');
            $params = $data;
            $params['location'] = $location_id;
            $response = self::rentmy_fetch(
                '/get-price-value',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ],
                $params,
                null
            );
            return $response;
        } catch (Exception $e) {

        }

    }

    /** Check package add ons and return add on products
     * @param $data
     * @return mixed
     */
    function get_addon_products($product_id)
    {
        try {
            $location_id = get_option('rentmy_locationId');
            $response = self::rentmy_fetch(
                '/products/'.$product_id.'/addons?required=true&location=' . $location_id,
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => $location_id
                ]
            );
            return $response['result'];
        } catch (Exception $e) {

        }

    }

    /** get related products by product id ...
     * @param $data
     * @return mixed
     */
    function get_related_products($product_id)
    {
        try {
            $location_id = get_option('rentmy_locationId');
            $response = self::rentmy_fetch(
                '/products/'.$product_id.'/user/related-products',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => $location_id
                ]
            );
            return $response['result'];
        } catch (Exception $e) {

        }

    }

    /**
     * Get list of featured items
     * @return mixed
     */
    function get_featured_products(){
        try {
            $location_id = get_option('rentmy_locationId');
            $response = self::rentmy_fetch(
                '/products/featured',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => $location_id
                ]
            );
            return $response['result'];
        } catch (Exception $e) {

        }
    }

    /**
     * Get exact duration of a selected date
     * @return mixed
     */
    function getExactDuration($start_date){
        try {
            $location_id = get_option('rentmy_locationId');
            $response = self::rentmy_fetch(
                '/product/get_exact_duration',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => $location_id
                ],
                [
                    'start_date' => $start_date
                ]
            );
            return $response['result']['data'];
        } catch (Exception $e) {

        }
    }

    /**
     * Get duration from a given date and time
     * @return mixed
     */
    function getDatesFromDuration($data){
        try {
            $location_id = get_option('rentmy_locationId');
            $data['location_id'] = $location_id;
            $response = self::rentmy_fetch(
                '/product/get_dates_from_duration',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => $location_id
                ],
                $data
            );
            return $response['result']['data'];
        } catch (Exception $e) {

        }
    }

    /**
     * Get duration from a start_date date and price id
     * @return mixed
     */
    function getDatesPriceDuration($data){
        try {
            $location_id = get_option('rentmy_locationId');
            $add_params = '?start_date=' . urlencode($data['start_date']) . '&price_id=' . $data['price_id'] . '&location=' . $location_id;
            $response = self::rentmy_fetch(
                '/product/get_dates_price_duration' . $add_params,
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => $location_id
                ]
            );
            return $response['result']['data'];
        } catch (Exception $e) {

        }
    }
}
