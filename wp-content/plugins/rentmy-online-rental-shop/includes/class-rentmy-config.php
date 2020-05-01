<?php

/**
 * Class RentMy_Config
 */
Class RentMy_Config extends RentMy
{
    function __construct()
    {
    }

    /**
     * get store configs according to store params
     * @param $type categories,location,tags,variants,variant_sets,paymentgateways
     * primarily we will store only location id
     * settings?type=categories,location,tags,variants,variant_sets,paymentgateways
     * @return mixed|string|null
     */
    public function config($type)
    {
        try {
            $response = self::rentmy_fetch(
                '/settings?type=' . $type,
                get_option('rentmy_accessToken')
            );
            return $response['result']['data'];
        } catch (Exception $e) {

        }
    }


    /**
     * Get categories with threaded mode
     * @return mixed|string|null
     */
    function categories()
    {
        try {
            $response = self::rentmy_fetch(
                '/categories',
                get_option('rentmy_accessToken'),
                null,
                null
            );
            return $response['result']['data'];
        } catch (Exception $e) {

        }

    }

    /**
     * Get Store config
     * @return mixed
     */
    function store_config()
    {
        try {
            if (!session_id()) {
                session_start();
            }
            if (empty($_SESSION['rentmy_config'])) {
                $response = self::rentmy_fetch(
                    '/settings?type=store_config',
                    get_option('rentmy_accessToken')
                );
                $_SESSION['rentmy_config'] = $response['result']['data']['config'];
            }
            return update_option('rentmy_config', $_SESSION['rentmy_config']);

        } catch (Exception $e) {

        }
    }

    /**
     * Get Store Contents
     * @return mixed
     */
    function store_contents()
    {
        try {
            $response = self::rentmy_fetch(
                '/contents',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response['result']['data'];

        } catch (Exception $e) {

        }
    }

    /**
     * Get country list
     * @return mixed
     */
    function countries()
    {
        $response = self::rentmy_fetch(
            '/countries',
            get_option('rentmy_accessToken')
        );
        return $response['result']['data'];

    }

    // get delivery settings
    function getDeliverySettings()
    {
        try {
            $response = self::rentmy_fetch(
                '/stores/delivery-settings',
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return $response['result'];
        } catch (Exception $e) {

        }
    }

    // get location list
    function getLocationList()
    {
        try {
            $response = self::rentmy_fetch(
                '/locations/list',
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return $response['result'];
        } catch (Exception $e) {

        }

    }

    // get payment gateways that are enabled
    function getPaymentGateWays()
    {
        $response = self::rentmy_fetch(
            '/payments/gateway?is_online=1',
            [
                'token' => get_option('rentmy_accessToken'),
            ]
        );
        return $response['result'];

    }
}
