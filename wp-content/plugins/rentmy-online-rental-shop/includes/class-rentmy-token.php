<?php

Class RentMy_Token extends RentMy
{
    public $apiKey;
    public $apiSecret;

    public function __construct()
    {
        $this->apiKey = get_option('rentmy_apiKey');
        $this->apiSecret = get_option('rentmy_secretKey');
    }


    /**
     * Get AccessToken
     * @return mixed
     * @todo check domain name
     */
    public function getToken()
    {
        try {
            $response = $this->fetch(
                '/apps/access-token',
                null,
                [
                    'api_key' => $this->apiKey,
                    'api_secret' => $this->apiSecret
                ]
            );
            if (!empty($response['data']['token'])) {
                update_option('rentmy_accessToken', $response['data']['token'] );
                update_option('rentmy_refreshToken', $response['data']['refresh_token'] );
                update_option('rentmy_storeId', $response['data']['store_id'] );
                update_option('rentmy_locationId', $response['data']['location_id'] );
            }
        } catch (Exception $e) {

        }

    }

}
