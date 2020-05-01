<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Rental Product Compatiability With Reactive Pro Plugin.
 * The WooCommerce product class handles individual product data.
 *
 *
 * @class       Redq_Rental_Reactive
 * @version     3.0.5
 * @since       3.0.5
 * @package     WooCommerce-rental-and-booking/includes
 * @category    Class
 * @author      RedQTeam
 */

class Redq_Rental_Reactive
{
    public function __construct()
    {
        add_filter('re_update_meta_fields', [$this, 'redq_rental_re_update_meta_fields'], 1);
        add_filter('re_post_type_for_posts', [$this, 'redq_rental_re_post_type_for_posts'], 1);
        add_filter('re_get_post_types_for_taxonomy', [$this, 'redq_rental_re_get_post_types_for_taxonomy'], 1);
        add_filter('re_preview_post_terms', [$this, 'redq_rental_re_preview_post_terms'], 1, 3);
        add_filter('re_preview_post_meta', [$this, 'redq_rental_re_preview_post_meta'], 1, 4);
        add_filter('re_preview_posts', [$this, 'redq_rental_re_preview_posts'], 1);
        add_filter('re_all_builder_data', [$this, 'redq_rental_re_all_builder_data'], 1);
    }

    /**
     * Plugin data from redq_rental_re_update_meta_fields()
     * Show rnb search metabox in rebuilder post type
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     */
    public function redq_rental_re_update_meta_fields($builder_data)
    {
        $builder_data[0]['fields'][] = [
            'linear' => [
                [
                    'type' => 'select', 'id' => 'rebuilder_rnb_search', 'title' => 'Choose To Enable RnB Search',
                    'options' => [
                        ['key' => 'yes', 'value' => 'Yes'],
                        ['key' => 'no', 'value' => 'No'],
                    ],
                    'default_value' => 'yes',
                ],
            ],
        ];

        return $builder_data;
    }

    /**
     * Plugin data from redq_rental_re_post_type_for_posts()
     * If rnb search is active then work only product post type
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     */
    public function redq_rental_re_post_type_for_posts($post_type)
    {
        $rnb_enabled = get_post_meta(get_the_ID(), 'rebuilder_rnb_search', true);
        $indexed_post_type = get_option('reactive_builder_will_update_post', true);
        if ((isset($rnb_enabled) && $rnb_enabled === 'yes') || (isset($indexed_post_type) && $indexed_post_type === 'product')) {
            $post_type = 'product';
        }

        return $post_type;
    }

    /**
     * Plugin data from redq_rental_re_get_post_types_for_taxonomy()
     * If rnb search is active then work only product & Inventory post types
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     */
    public function redq_rental_re_get_post_types_for_taxonomy($post_type)
    {
        $rnb_enabled = get_post_meta(get_the_ID(), 'rebuilder_rnb_search', true);
        $indexed_post_type = get_option('reactive_builder_will_update_post', true);
        if ((isset($rnb_enabled) && $rnb_enabled === 'yes') || (isset($indexed_post_type) && $indexed_post_type === 'product')) {
            $post_type = 'inventory,product';
        }
        return $post_type;
    }

    /**
     * Plugin data from redq_rental_re_preview_post_terms()
     * If rnb search ten retreive prdouct and inventory post-types taxonomy terms
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     */
    public function redq_rental_re_preview_post_terms($post_terms, $post_id, $taxonomies)
    {
        $rnb_enabled = get_post_meta(get_the_ID(), 'rebuilder_rnb_search', true);
        $indexed_post_type = get_option('reactive_builder_will_update_post', true);
        if ((isset($rnb_enabled) && $rnb_enabled === 'yes') || (isset($indexed_post_type) && $indexed_post_type === 'product')) {
            $post_terms = $this->get_product_post_terms($post_id, $taxonomies);
        }
        return $post_terms;
    }

    /**
     * Plugin data from redq_rental_re_preview_post_meta()
     * If rnb search ten retrieve product and inventory post-types meta-values
     *
     * @param $post_meta
     * @param $post_id
     * @param $post_types
     * @param $allowed_key
     * @return array
     * @version 3.0.5
     *
     * @since 3.0.5
     */
    public function redq_rental_re_preview_post_meta($post_meta, $post_id, $post_types, $allowed_key)
    {
        $rnb_enabled = get_post_meta(get_the_ID(), 'rebuilder_rnb_search', true);
        $indexed_post_type = get_option('reactive_builder_will_update_post', true);
        if ((isset($rnb_enabled) && $rnb_enabled === 'yes') || (isset($indexed_post_type) && $indexed_post_type === 'product')) {
            $post_meta = $this->redq_rental_get_product_post_meta($post_id, $post_types, $allowed_key);
        }
        return $post_meta;
    }

    /**
     * Plugin data from redq_rental_re_preview_posts()
     * If rnb search then add extra rnb_terms meta-key in main posts objects
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     * @return array
     */
    public function redq_rental_re_preview_posts($posts)
    {
        $rnb_enabled = get_post_meta(get_the_ID(), 'rebuilder_rnb_search', true);
        $indexed_post_type = get_option('reactive_builder_will_update_post', true);
        if ((isset($rnb_enabled) && $rnb_enabled === 'yes') || (isset($indexed_post_type) && $indexed_post_type === 'product')) {
            $post_types = 'inventory,product';
            $taxonomies = $this->get_all_taxonomies($post_types);
            foreach ($posts as &$post) {
                $post->rnb_terms = $this->get_rnb_terms($post->ID, $taxonomies);
            }
        }

        return $posts;
    }

    /**
     * Plugin data from redq_rental_re_all_builder_data()
     * If rnb search then added new key in settinsData object.
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     */
    public function redq_rental_re_all_builder_data($builderData)
    {
        foreach ($builderData as $key => $value) {
            $builderData[$key]['settingsData']['rnb_active'] = true;
        }

        //Update blocked dates to preview data
        foreach ($builderData as $key => $value) {
            $preview_data = $builderData[$key]['previewData'];
            foreach ($preview_data as $pre_key => $pre_value) {
                if (isset($pre_value) && is_array($pre_value)) {
                    foreach ($pre_value as $sub_key => $sub_value) {
                        if (array_key_exists('redq_block_dates_and_times', $sub_value->meta)) {
                            $product_id = $sub_value->ID;
                            $product = wc_get_product($product_id);
                            $product_type = $product ? $product->get_type() : '';
                            if (isset($product_type) && $product_type === 'redq_rental') {
                                $block_dates = calculate_block_dates($sub_value->ID);
                                $cart_dates = rental_product_in_cart($sub_value->ID);
                                $starting_block_days = redq_rental_staring_block_days($sub_value->ID);
                                $block_dates = array_merge($block_dates, $cart_dates, $starting_block_days);
                                $euro_format = get_post_meta($sub_value->ID, 'redq_choose_european_date_format', true);

                                $formatted_block_dates = [];

                                if ($euro_format === 'no') {
                                    foreach ($block_dates as $bkey => $bvalue) {
                                        $standard_day = strtotime($bvalue);
                                        $output_format = 'Y-m-d';
                                        $standard_date = date($output_format, $standard_day);
                                        $formatted_block_dates[] = $standard_date;
                                    }
                                } else {
                                    foreach ($block_dates as $bkey => $bvalue) {
                                        $dat = str_replace('/', '.', $bvalue);
                                        $standard_day = strtotime($dat);
                                        $output_format = 'Y-m-d';
                                        $standard_date = date($output_format, $standard_day);
                                        $formatted_block_dates[] = $standard_date;
                                    }
                                }

                                $builderData[$key]['previewData'][$pre_key][$sub_key]->meta['redq_block_dates_and_times'] = $formatted_block_dates;
                            }
                        }
                    }
                }
            }
        }

        return $builderData;
    }

    /**
     * Plugin data from get_all_taxonomies()
     * Retrieve all taxonomy for product and inventory post-types
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     */
    public function get_all_taxonomies($post_types = 'post')
    {
        $all_post_types = explode(',', $post_types);
        $taxonomies = [];
        foreach ($all_post_types as $type) {
            $taxonomies = array_merge($taxonomies, get_object_taxonomies($type));
        }
        return $taxonomies;
    }

    /**
     * Plugin data from get_product_post_terms()
     * Retrieve all taxonomy for product and inventory post-types
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     */
    public function get_product_post_terms($post_id, $taxonomies)
    {
        $temp = [];

        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy == 'resource' || $taxonomy == 'person' || $taxonomy == 'deposite' || $taxonomy == 'attributes' || $taxonomy == 'features' || $taxonomy == 'pickup_location' || $taxonomy == 'dropoff_location') {
                $product_id = $post_id;
                if (empty($product_id)) {
                    $product_id = get_the_ID();
                } else {
                    $product_id = $product_id;
                }

                $payable_attributes_identifiers = get_post_meta($product_id, 'resource_identifier', true);
                $selected_terms = [];

                if (is_array($payable_attributes_identifiers) && !empty($payable_attributes_identifiers)) {
                    foreach ($payable_attributes_identifiers as $resource_key => $resource_value) {
                        $args = [
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'fields' => 'all',
                        ];
                        if (taxonomy_exists($taxonomy)) {
                            $terms = wp_get_post_terms($resource_value['inventory_id'], $taxonomy, $args);
                        }

                        if (isset($terms) && is_array($terms)) {
                            foreach ($terms as $term_key => $term_value) {
                                $selected_terms[] = $term_value;
                            }
                        }
                    }
                }

                $unique = array_map('unserialize', array_unique(array_map('serialize', $selected_terms)));
                $unique_selected_terms = [];

                foreach ($unique as $key => $value) {
                    $unique_selected_terms[] = $value;
                }

                $temp[$taxonomy] = [];
                foreach ($unique_selected_terms as $term) {
                    $temp[$taxonomy][] = $term->slug;
                }
            } else {
                $terms = wp_get_post_terms($post_id, $taxonomy);
                $temp[$taxonomy] = [];
                foreach ($terms as $term) {
                    $temp[$taxonomy][] = $term->slug;
                }
            }
        }

        return $temp;
    }

    /**
     * Plugin data from redq_rental_get_product_post_meta()
     * Retrieve all metavalues for product and inventory post-types
     *
     * @since 3.0.5
     * @version 3.0.5
     * @var object
     */
    public function redq_rental_get_product_post_meta($post_id, $post_types = 'post', $allowed_key)
    {
        $fields = get_post_custom($post_id);
        $temp = [];

        foreach ($fields as $metakey => $metavalues) {
            if (in_array($metakey, $allowed_key)) {
                if (!empty($metavalues)) {
                    if ($metavalues[0] != null) {
                        $temp[$metakey] = $metavalues[0];
                        if ($metakey === 'redq_block_dates_and_times') {
                            $dates = calculate_block_dates($post_id);
                            $formated_dates = [];

                            $euro_format = get_post_meta($post_id, 'redq_choose_european_date_format', true);

                            if (isset($euro_format) && $euro_format === 'no') {
                                if (isset($dates) && !empty($dates)) {
                                    foreach ($dates as $dkey => $dvalue) {
                                        $formated_date = date('Y-m-d', strtotime($dvalue));
                                        $formated_dates[] = $formated_date;
                                    }
                                }
                            } else {
                                if (isset($dates) && !empty($dates)) {
                                    foreach ($dates as $dkey => $dvalue) {
                                        $date = str_replace('/', '-', $dvalue);
                                        $formated_date = date('Y-m-d', strtotime($date));
                                        $formated_dates[] = $formated_date;
                                    }
                                }
                            }

                            $temp[$metakey] = $formated_dates;
                        }

                        if (
                            in_array(
                                'woocommerce/woocommerce.php',
                                apply_filters('active_plugins', get_option('active_plugins'))
                            )
                        ) {
                            if ($metakey === '_product_image_gallery') {
                                $attachment_ids = explode(',', $metavalues[0]);
                                foreach ($attachment_ids as $attachment_id) {
                                    $image_link = wp_get_attachment_url($attachment_id);
                                    $temp['_product_image_gallery_links'][] = $image_link;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $temp;
    }

    /**
     * Plugin data from redq_rental_get_product_post_meta()
     * Retrieve all rnb-terms for product and inventory post-types
     *
     * @since 3.0.5
     * @version 3.0.8
     * @var object
     */
    public function get_rnb_terms($post_id, $taxonomies)
    {
        $temp = [];
        foreach ($taxonomies as $taxonomy) {
            if ($taxonomy == 'resource' || $taxonomy == 'person' || $taxonomy == 'deposite' || $taxonomy == 'attributes' || $taxonomy == 'features' || $taxonomy == 'pickup_location' || $taxonomy == 'dropoff_location') {
                if ($taxonomy == 'features' || $taxonomy == 'attributes') {
                    $rnb_terms = WC_Product_Redq_Rental::redq_get_rental_non_payable_attributes($taxonomy, $post_id);
                }
                if ($taxonomy == 'resource' || $taxonomy == 'person' || $taxonomy == 'pickup_location' || $taxonomy == 'dropoff_location') {
                    $rnb_terms = WC_Product_Redq_Rental::redq_get_rental_payable_attributes($taxonomy, $post_id);
                }
                $temp[$taxonomy] = [];
                $temp[$taxonomy] = $rnb_terms;
            } else {
                $terms = wp_get_post_terms($post_id, $taxonomy);
                $temp[$taxonomy] = [];
                foreach ($terms as $term) {
                    $temp[$taxonomy][] = $term->name;
                }
            }
        }

        return $temp;
    }
}

new Redq_Rental_Reactive();
