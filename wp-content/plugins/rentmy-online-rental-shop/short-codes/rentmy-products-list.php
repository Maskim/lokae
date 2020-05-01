<?php
//list all products of a store
function rent_my_products_list_shortcode($params)
{

    ob_start();
    $attributes = [
        'location' => get_option('rentmy_locationId'),
        'page_no' => !empty($_GET['page_no']) ? $_GET['page_no'] : 1,
        'limit' => !empty($_GET['limit']) ? $_GET['limit'] : 12,
    ];

    // if uid is empty on get url we will check for the shortcode params for type and uid and if found
    // we will forcefully update the get params. else leave as it is.
    // will do this for all kind of other operations like tags and ids
    if (empty($_GET['uid'])) {
        if (!empty($params['type'])) {
            if ($params['type'] == 'category' && !empty($params['id'])) {
                $categoryObj = new RentMy_Category();
                $category=$categoryObj->getCategoryDetails($params['id']);
                $_GET['uid'] = trim($category['uuid']);
            }
        }
    }

    if (empty($_GET['tags'])) {
        if (!empty($params['type'])) {
            if ($params['type'] == 'tag' && !empty($params['id'])) {
                $_GET['tags'] = trim($params['id']);
            }
        }
    }

    if (!empty($params['sort-type'])) {
        $attributes['sort_type'] = $params['sort-type'];
    }

    if (!empty($params['sort'])) {
        $attributes['sort'] = $params['sort'];
    }


    if (!empty($params['location'])) {
        $attributes['location'] = $params['location'];
    }

    if (!empty($params['page_no'])) {
        $attributes['page_no'] = $params['page_no'];
    }

    if (!empty($params['limit'])) {
        $attributes['limit'] = $params['limit'];
    }

    if (!empty($_GET['tags'])) {
        $attributes['tag_id'] = trim($_GET['tags']);
    }else {
        $attributes['tag_id'] = null;
    }

    if (!empty($_GET['purchase_type'])) {
        $attributes['purchase_type'] = trim($_GET['purchase_type']);
    }else {
        $attributes['purchase_type'] = null;
    }

    if (!empty($_GET['min_price'])) {
        $attributes['price_min'] = trim($_GET['min_price']);
    }else {
        $attributes['price_min'] = null;
    }

    if (!empty($_GET['max_price'])) {
        $attributes['price_max'] = trim($_GET['max_price']);
    }else {
        $attributes['price_max'] = null;
    };

    if (empty($attributes)) {
        echo '<span class="rentmy-errore-msg">No Attributes or Parameter found for this Rent My API.</span>';
        return;
    };

    $rentmy_products_list = new RentMy_Products();

    if (!empty($_GET['search'])) {
        $attributes['search'] = trim($_GET['search']);
        $response = $rentmy_products_list->productSearch($attributes);
    } else if (!empty($_GET['uid'])) {
        $attributes['category_id'] = $_GET['uid'];
        $response = $rentmy_products_list->productListByCategory($attributes);
    } else {
        $response = $rentmy_products_list->productList($attributes);
    }
    // print_r("<pre>");print_r($response);print_r("</pre>");
    if (!empty($response)):
        rentmy_product_list_template($response);
        return ob_get_clean();
    else:
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-products-list', 'rent_my_products_list_shortcode');

function rentmy_product_list_template($rent_my_product)
{
    global $post;
    $post_slug = $post->post_name;
    ?>

    <?php if (!empty($rent_my_product['data'])) { ?>
    <div class="rentmy-plugin-manincontent">
        <div class='rentmy-product-list'>
            <div class='products'>
                <?php foreach ($rent_my_product['data'] as $product): ?>
                    <div class="product-grid">
                        <div class="product-grid-inner text-center">
                            <div class="product-grid-img">
                                <img class="img-fluid"
                                     src="<?php echo $GLOBALS['RentMy']::imageLink($product['id'], $product['images'][0]['image_small'], 'list'); ?>">
                                <?php if ($product['type'] == 2) { ?>
                                    <a href="<?php echo home_url('rentmy-package-details?uid=') . $product['uuid']; ?>">
                                        <div class="product-overley"></div>
                                    </a>
                                <?php } else { ?>
                                    <a href="<?php echo home_url('rentmy-product-details?uid=') . $product['uuid']; ?>">
                                        <div class="product-overley"></div>
                                    </a>
                                <?php } ?>
                            </div>
                            <div class="product-grid-body">
                                <div class="product-name">
                                    <?php if ($product['type'] == 2) { ?>
                                        <a href="<?php echo home_url('rentmy-package-details?uid=') . $product['uuid']; ?>">
                                            <h4 class=""><?php echo $product['name']; ?></h4>
                                        </a>
                                    <?php } else { ?>
                                        <a href="<?php echo home_url('rentmy-product-details?uid=') . $product['uuid']; ?>">
                                            <h4 class=""><?php echo $product['name']; ?></h4>
                                        </a>
                                    <?php } ?>
                                </div>
                                <?php
                                $priceTypes = getRentalTypes($product['prices']);
                                $prices = getPrices($product['prices']);
                                $generic_prices = empty($product['price']) ? $product['prices'] : $product['price']; ?>
                                <?php if (in_array('rent', $priceTypes)) { ?>
                                    <span class="price">Starting at <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post') . (!empty($prices['rent'][0]['duration']) ? ' for ' . $prices['rent'][0]['duration'] : '') . ' ' . (!empty($prices['rent'][0]['label']) ? $prices['rent'][0]['label'] : ''); ?></span>
                                <?php } elseif (in_array('fixed', $priceTypes)) { ?>
                                    <span class="price">Starting at <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post'); ?></span>
                                <?php } else { ?>
                                    <span class="price">Buy now for <?php echo $GLOBALS['RentMy']::currency($prices['base']['price'], 'pre', 'amount', 'post'); ?></span>
                                <?php } ?>
                                <?php if ($product['type'] == 2) { ?>
                                    <a class="button"
                                       href="<?php echo home_url('rentmy-package-details?uid=') . $product['uuid']; ?>">View
                                        Details</a>
                                <?php } else { ?>
                                    <a class="button"
                                       href="<?php echo home_url('rentmy-product-details?uid=') . $product['uuid']; ?>">View
                                        Details</a>
                                <?php } ?>

                                <?php if (in_array('base', $priceTypes) && ($product['type'] != 2)) { ?>
                                    <a data-variants_products_id="<?php echo $product['default_variant']['variants_products_id']; ?>"
                                       data-product_id="<?php echo $product['id']; ?>" href="javascript:void(0)"
                                       class="button add_to_cart_button_list">Add to cart</a>
                                <?php } ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php

            $limit = $rent_my_product['limit'];
            $total = $rent_my_product['total'];
            $page = empty($_GET['page_no']) ? 1 : $_GET['page_no'];
            $cat_param = !empty($_GET['uid']) ? '&uid=' . $_GET['uid'] : '';
            $adjacents = 3;
            if (empty($cat_param)) {
                $targetpage = home_url($post_slug . '?limit=' . $limit);
            } else {
                $targetpage = home_url($post_slug . '?' . $cat_param . '&limit=' . $limit);
            }
            $pagination = $GLOBALS['RentMy']::paginate($page, $total, $limit, $adjacents, $targetpage);
            echo $pagination;
            ?>
        </div>
    </div>
<?php } else {
    echo '<span class="rentmy-errore-msg">No Products Found</span>';
} ?>

    <?php
}
