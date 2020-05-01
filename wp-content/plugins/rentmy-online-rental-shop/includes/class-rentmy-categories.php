<?php

/**
 * Class RentMy_Category
 */
Class RentMy_Category extends RentMy
{
    /**
     * Get categories list
     * @return mixed|string|null
     */
    function categories()
    {
        try {
            $response = self::fetch(
                '/categories',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return !empty($response['data']) ? $response['data'] : null;
        } catch (Exception $e) {

        }

    }

    /**
     * Get category details
     * @param $id
     * @return |null
     */
    function getCategoryDetails($id){
        try {
            $response = self::fetch(
                '/categories/' .$id,
                [
                    'token' => get_option('rentmy_accessToken'),
                ]
            );
            return !empty($response['data']) ? $response['data'] : null;
        } catch (Exception $e) {

        }
    }

    /**
     * Get children category list
     * @return mixed|string|null
     */
    function children($parent_category_uid)
    {
        try {
            $response = self::fetch(
                '/get/child-categories/' . $parent_category_uid,
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return !empty($response['data']) ? $response['data'] : null;
        } catch (Exception $e) {

        }

    }

    /**
     * Get children tags list
     * @return mixed|string|null
     */
    function tags()
    {
        try {
            $response = self::fetch(
                '/tags',
                [
                    'token' => get_option('rentmy_accessToken'),
                    'location' => get_option('rentmy_locationId')
                ]
            );
            return $response['data'];
        } catch (Exception $e) {

        }

    }
}
