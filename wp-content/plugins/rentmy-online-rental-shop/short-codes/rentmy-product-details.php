<?php
//short code for product details of a product
function rentmy_product_details_shortcode($params)
{

    ob_start();
    $product_id = '';

    if (!empty($_GET['uid'])):
        $product_id = trim($_GET['uid']);
    endif;
    if (!empty($params['product_id'])):
        $product_id = $params['product_id'];
    endif;

    if (empty($product_id)):
        echo '<span class="rentmy-errore-msg">Invalid Product</span>';
        return;
    endif;

    $check_cart = (new RentMy_Cart())->viewCart();

    $rentmy_products = new RentMy_Products();

    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();
    if(!empty($store_content)){
        $GLOBALS['store_config'] = $store_content[0]['contents']['confg'];
    }

    $cart_params = [
        'token' => !empty($_SESSION['rentmy_cart_token']) ? $_SESSION['rentmy_cart_token'] : null,
        'start_date' => !empty($_SESSION['rentmy_rent_start']) ? $_SESSION['rentmy_rent_start'] : null,
        'end_date' => !empty($_SESSION['rentmy_rent_end']) ? $_SESSION['rentmy_rent_end'] : null,
    ];

    $response = $rentmy_products->details($product_id, $cart_params);

    if (!empty($response['data'])):
        $dataSet = $response['data'];
        $rent_my_product_details = $dataSet;
        $addon_products = $rentmy_products->get_addon_products($rent_my_product_details['id']);
        $related_products = $rentmy_products->get_related_products($rent_my_product_details['id']);
        $rent_my_product_details['addon'] = !empty($addon_products['data']) ? $addon_products : null;
        $rent_my_product_details['related_products'] = !empty($related_products['data']) ? $related_products['data'] : null;

        $rent_my_product_details['rent_dates'] = !empty($check_cart['data']) ? [ 'rent_start' => $check_cart['data']['rent_start'], 'rent_end' => $check_cart['data']['rent_end'] ] : [];

        rentmy_product_details_template($rent_my_product_details);
        return ob_get_clean();
    else:
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-products-details', 'rentmy_product_details_shortcode');

function rentmy_product_details_template($rent_my_product_details)
{
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-product-details">
            <div class="">
                <div class="images">
                    <?php if(!empty($rent_my_product_details['images'])): ?>
                        <div class="product-view-area">
                            <div class="image-list-area">
                                <ul class="image-list">
                                    <?php $rm_counter = 1; foreach($rent_my_product_details['images'] as $thumb_image): ?>
                                        <li class="image-item">
                                            <a class="view-image <?php echo $rm_counter==1? 'active-viewimg' : ''; ?>" href="javascript:void(0)">
                                                <img
                                                    data-targetsource="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $thumb_image['image_large'], 'list'); ?>"
                                                    src="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $thumb_image['image_small'], 'list'); ?>">
                                            </a>
                                        </li>
                                    <?php $rm_counter++; endforeach; ?>
                                </ul>
                            </div>
                            <div class="product-view-image">
                                <?php foreach($rent_my_product_details['images'] as $thumb_image): ?>
                                    <img class="active" src="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $thumb_image['image_large'], 'list'); ?>">
                                <?php break; endforeach; ?>
                            </div>
                        </div>
                    <?php else:?>
                        <img src="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $rent_my_product_details['images'][0]['image_large'], 'list'); ?>">
                    <?php endif;?>
                </div>
                <div class="details">
                    <h1 class="product_title"><?php echo $rent_my_product_details['name']; ?></h1>
                    <?php $priceTypes = getRentalTypes($rent_my_product_details['prices']);
                    $prices = getPrices($rent_my_product_details['prices']);
                    ?>
                    <div class="price">
                        <div class="buy" style="display: none;">
                            <h6><?php echo !empty($prices['base']['price']) ? $GLOBALS['RentMy']::currency($prices['base']['price'], 'pre', 'amount', 'post') : ''; ?></h6>
                            <input readonly type="hidden" class="rentmy-base-buy-price" id="rentmy-base-buy-price" value="<?php echo !empty($prices['base']['price']) ? $prices['base']['price'] : 0;?>">
                        </div>

                        <?php if($GLOBALS['store_config']['datetime']['exact_start_date'] == 1 || $GLOBALS['store_config']['datetime']['exact_start_date'] == '1') { ?>

                            <div class="rent" style="display: none;">
                                <?php if(!empty($rent_my_product_details['rental_price'])): ?>
                                    <h6><?php echo $GLOBALS['RentMy']::currency($rent_my_product_details['rental_price'], 'pre', 'amount', 'post'); ?></h6>
                                    <input readonly type="hidden" class="rentmy-base-rent-price" id="rentmy-base-rent-price" value="<?php echo !empty($rent_my_product_details['rental_price']) ? $rent_my_product_details['rental_price'] : 0; ?>">
                                <?php endif; ?>
                            </div>

                        <?php } else { ?>

                            <div class="rent" style="display: none;">
                                <?php if(!empty($rent_my_product_details['rental_price'])): ?>
                                    <h6><?php echo $GLOBALS['RentMy']::currency($rent_my_product_details['rental_price'], 'pre', 'amount', 'post'); ?></h6>
                                    <input readonly type="hidden" class="rentmy-base-rent-price" id="rentmy-base-rent-price" value="<?php echo !empty($rent_my_product_details['rental_price']) ? $rent_my_product_details['rental_price'] : 0; ?>">
                                <?php else: ?>

                                    <?php if(empty($rent_my_product_details['prices'][0]['fixed'])): ?>
                                        <h6><?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post') . ' for ' . $prices['rent'][0]['duration'] . ' ' . $prices['rent'][0]['label']; ?></h6>
                                        <input readonly type="hidden" class="rentmy-base-rent-price" id="rentmy-base-rent-price" value="<?php echo !empty($prices['rent'][0]['price']) ? $prices['rent'][0]['price'] : 0; ?>">
                                    <?php else: ?>
                                        <h6><?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post'); ?></h6>
                                        <input readonly type="hidden" class="rentmy-base-rent-price" id="rentmy-base-rent-price" value="<?php echo !empty($prices['rent'][0]['price']) ? $prices['rent'][0]['price'] : 0; ?>">
                                    <?php endif; ?>

                                <?php endif; ?>
                            </div>

                        <?php } ?>

                    </div>

                    <div class="rental-type">
                        <label class="radio-container buy_input"
                            for="buy" <?php if (!in_array('base', $priceTypes)) { ?> style="display:none;" <?php } ?> >
                            <input type="radio" <?php if (in_array('base', $priceTypes)) { ?> checked="checked" <?php } ?> id="rental_type_buy" name="rental_type" value="buy">
                            Buy
                            <span class="checkmark"></span>
                        </label>

                        <label class="radio-container rent_input"
                            for="rent" <?php if (!in_array('rent', $priceTypes)) { ?> style="display:none;" <?php } ?>>
                            <input type="radio" <?php if (in_array('rent', $priceTypes)) { ?> checked="checked" <?php } ?> id="rental_type_rent" name="rental_type" value="rent">
                            Rent
                            <span class="checkmark"></span>
                        </label>

                    </div>

                    <?php /** Start Pricing Options  */ ?>

                    <?php if($GLOBALS['store_config']['datetime']['exact_start_date'] != 1 || $GLOBALS['store_config']['datetime']['exact_start_date'] != '1') { ?>

                        <?php if(empty($rent_my_product_details['extact_durations'])): ?>
                            <?php if(empty($rent_my_product_details['rent_dates']['rent_start'])): ?>

                                <div class="price-options" style="display: none;">
                                    <?php foreach ($prices['rent'] as $i => $rents) { ?>
                                        <label class="radio-container">
                                            <input type="radio" name="rental-price"
                                                data-price_id="<?php echo $rents['id']; ?>"
                                                data-price="<?php echo $rents['price']; ?>"
                                                data-duration="<?php echo $rents['duration']; ?>"
                                                data-label="<?php echo $rents['label']; ?>"
                                                data-start_date="<?php echo date('m-d-Y h:i A', strtotime($rents['rent_start'])); ?>"
                                                data-end_date="<?php echo date('m-d-Y h:i A', strtotime($rents['rent_end'])); ?>"
                                                value="<?php echo $rents['id']; ?>" <?php if ($i == 0) { ?> checked <?php } ?> >
                                            <?php echo $GLOBALS['RentMy']::currency($rents['price']) . ' /' . $rents['duration'] . ' ' . $rents['label']; ?>
                                            <span class="checkmark"></span>
                                        </label>
                                    <?php } ?>
                                </div>

                            <?php else: $fixed_rent_dates = $rent_my_product_details['rent_dates']; ?>

                                <div class="price-options" style="display: none;">
                                    <?php foreach ($prices['rent'] as $i => $rents) { ?>
                                        <label class="radio-container">
                                            <i class="fa fa-arrow-right"></i>
                                            <input type="radio" name="rental-price"
                                                data-price_id="<?php echo $rents['id']; ?>"
                                                data-price="<?php echo $rents['price']; ?>"
                                                data-duration="<?php echo $rents['duration']; ?>"
                                                data-label="<?php echo $rents['label']; ?>"
                                                data-start_date="<?php echo date('m-d-Y h:i A', strtotime($fixed_rent_dates['rent_start'])); ?>"
                                                data-end_date="<?php echo date('m-d-Y h:i A', strtotime($fixed_rent_dates['rent_end'])); ?>"
                                                value="<?php echo $rents['id']; ?>" <?php if ($i == 0) { ?> checked <?php } ?>>
                                            <?php echo $GLOBALS['RentMy']::currency($rents['price']) . ' /' . $rents['duration'] . ' ' . $rents['label']; ?>
                                            <br>
                                        </label>
                                    <?php } ?>
                                </div>

                            <?php endif;?>
                        <?php endif;?>

                    <?php } else { ?>

                        <div class="price-options" style="display: none;">
                            <h4>
                                Due Date <?php echo date('m/d/Y', strtotime(trim($rent_my_product_details['rent_end']))); ?>
                            </h4>
                        </div>

                    <?php } ?>

                    <?php /** Variant Set && Variants select  */ ?>
                    <div class="variants">
                        <?php
                        foreach ($rent_my_product_details['variant_set_list'] as $i => $variantSets) { ?>
                            <div class="form-group variantSets">
                                <label><?php echo $variantSets['name']; ?></label>
                                <select name="variantSets[]" data-index="<?php echo $i + 1; ?>"
                                        data-total="<?php echo count($rent_my_product_details['variant_set_list']); ?>"
                                        data-id="<?php echo $variantSets['id']; ?>"
                                        data-next-id="<?php echo $rent_my_product_details['variant_set_list'][$i + 1]['id']; ?>"
                                        data-prev-id="<?php echo $rent_my_product_details['variant_set_list'][$i - 1]['id']; ?>"
                                        id="variantSet_<?php echo $variantSets['id']; ?>">
                                    <?php foreach ($rent_my_product_details['variant_list'] as $variants) { ?>
                                        <?php if ($variants['variant_set_id'] == $variantSets['id']) { ?>
                                            <option value="<?php echo $variants['id']; ?>" <?php if ($variants['selected'] == 1) { ?> selected <?php }; ?>><?php echo $variants['name']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        <?php } ?>
                    </div>
                    <?php /********* End Variant set  and variant selection */ ?>

                    <div class="quantity">
                        <label>Quantity:</label>
                        <button type="button" class="decrease">-</button>
                        <input type="text" disabled value="1" name="quantity" id="rm_quantity">
                        <button type="button" class="increase">+</button>
                    </div>

                    <?php if($GLOBALS['store_config']['show_checkout_availability_text'] == 1 || $GLOBALS['store_config']['show_checkout_availability_text'] == '1'): ?>
                        <div class="availability">
                            <span>Available :
                                <span class="availability-count">
                                    <?php echo $rent_my_product_details['available']; ?>
                                </span>
                            </span>
                        </div>
                    <?php endif;?>

                    <?php if($GLOBALS['store_config']['datetime']['exact_start_date'] != 1 || $GLOBALS['store_config']['datetime']['exact_start_date'] != '1') { ?>

                        <?php if( !empty($rent_my_product_details['extact_durations']) && empty($rent_my_product_details['rent_dates']['rent_start']) ): $exact_duration = $rent_my_product_details['extact_durations']; ?>
                            <div class="exact-date-wrapper" style="display: none">
                                <div class="date-range pt-4">
                                    <label>Select Start Date:</label>
                                    <input autocomplete="off" class="single-date-range form-control" id="single-date-range" type="text"
                                        name="single-date-range"
                                        value="<?php echo date('m-d-Y'); ?>"/>
                                </div>
                                <div class="quantity pt-4">
                                    <div class="form-group">
                                        <label>Select Duration:</label>
                                        <select class="form-control rentmy-duration-component" name="duration" id="duration">
                                            <option value="">-Select-</option>
                                            <?php foreach($exact_duration['durations'] as $duration): ?>
                                                <option data-times='<?php echo !empty($duration['times']) ? implode(',', $duration['times']) : '';?>' data-type="<?php echo $duration['type'];?>" data-label="<?php echo $duration['label'];?>" value="<?php echo $duration['value']; ?>"><?php echo $duration['label']; ?></option>
                                            <?php endforeach;?>
                                        </select>
                                    </div>
                                </div>

                                <?php if($GLOBALS['store_config']['show_start_time'] == 1 || $GLOBALS['store_config']['show_start_time'] == '1'): ?>
                                    <div class="quantity pt-3">
                                        <div class="form-group">
                                            <label>Select Start time: </label>
                                            <select class="form-control rentmy-duration-component" name="exact_time" id="exact_time">
                                                <option value="">-Select-</option>
                                            </select>
                                        </div>
                                    </div>
                                <?php endif;?>

                            </div>
                        <?php else: ?>
                            <div class="rm-rental-daterange">
                                <label>Rental date range:</label>
                                <?php if(empty($rent_my_product_details['rent_dates']['rent_start'])): ?>
                                    <input autocomplete="off" class="daterange" id="rm-date" type="text"
                                        name="rm-date"
                                        value="<?php echo date('m-d-Y h:i a', strtotime($prices['rent'][0]['rent_start'])) . '-' . date('m-d-Y h:i a', strtotime($prices['rent'][0]['rent_end'])); ?>"/>
                                <?php else: $fixed_rent_dates = $rent_my_product_details['rent_dates'];?>
                                    <input autocomplete="off" disabled="true" class="daterange" id="rm-date" type="text"
                                        name="rm-date"
                                        value="<?php echo date('m-d-Y h:i a', strtotime($fixed_rent_dates['rent_start'])) . '-' . date('m-d-Y h:i a', strtotime($fixed_rent_dates['rent_end'])); ?>"/>
                                <?php endif;?>
                            </div>
                        <?php endif;?>

                    <?php } else { ?>

                        <input style="width: 440px;" autocomplete="off" id="exact-rent-start" type="hidden"
                            name="exact-rent-start"
                            value="<?php echo trim($rent_my_product_details['rent_start']); ?>"/>

                        <input style="width: 440px;" autocomplete="off" id="exact-rent-end" type="hidden"
                            name="exact-rent-end"
                            value="<?php echo trim($rent_my_product_details['rent_end']); ?>"/>

                    <?php } ?>



                    <div class="hidden_variables">
                        <input type="hidden" id="rm_pd_product_id" value="<?php echo $rent_my_product_details['id']; ?>"/>
                        <input type="hidden" id="rm_v_products_id"
                            value="<?php echo $rent_my_product_details['default_variant']['variants_products_id']; ?>"/>
                    </div>

                    <button
                            <?php echo $rent_my_product_details['available'] < 1 ? 'disabled' : '';?>
                            type="button"
                            class="add_to_cart_button alt"
                            id="rentmy-rent-item"
                            name="rentmy-rent-item"
                            value="ADD TO CART"
                            data-default_availability="<?php echo $rent_my_product_details['available'] < 1 ? false : true;?>">ADD TO CART
                    </button>

                    <?php if (!empty($rent_my_product_details['addon'])): foreach ($rent_my_product_details['addon']['data'] as $add_on): ?>
                        <br>
                        <br>
                        <div class="">
                            <h5>
                                <img class="addon-small-img" src="<?php echo $GLOBALS['RentMy']::imageLink($add_on['id'], $add_on['image'], 'list'); ?>">
                                <?php echo $add_on['name']; ?>
                            </h5>
                            <div class="">
                                <div class="">

                                </div>
                                <div class="addon-product-parent-row" data-updated_quantity="<?php echo $add_on['min_quantity']; ?>" data-min_quantity="<?php echo $add_on['min_quantity']; ?>" data-product_id="<?php echo $add_on['id']; ?>">
                                    <?php $step = 1; foreach ($add_on['variants'] as $variants): ?>
                                        <div style="clear: both; padding: 10px 0px;">
                                            <div style="float:left;">
                                                <?php
                                                if (strpos($variants['name'], 'Unassigned') !== false) {
                                                    echo $add_on['name'];
                                                }
                                                else {
                                                    echo $variants['name'];
                                                }
                                                ?>
                                            </div>
                                            <div style="float:right">
                                                <input style="width: 50px;"
                                                        data-product_id="<?php echo $add_on['id']; ?>"
                                                        data-quantity_id="<?php echo $variants['quantity_id']; ?>"
                                                        data-variants_products_id="<?php echo $variants['id']; ?>"
                                                        autocomplete="off"
                                                        class="rentmy-add-on-products"
                                                        name="qty"
                                                        type="text"
                                                        value="<?php echo $step == 1 ? $add_on['min_quantity'] : '0'; ?>">
                                            </div>
                                        </div>

                                    <?php $step++; endforeach; $step = 0;?>
                                    <div style="clear: both">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>

                    <?php if(!empty($rent_my_product_details['description'])){ ?>
                        <hr>
                        <p class="description"><?php echo $rent_my_product_details['description']; ?></p>
                    <?php } ?>

                </div>
                <br>
                <p> </p>
            </div>

            <!-- related product block starts -->
            <?php if(!empty($rent_my_product_details['related_products'])): ?>
                <?php $related_products = $rent_my_product_details['related_products']; ?>
                <div class='rentmy-product-list related-producst-list'>
                    <div class="related-product-title">
                        <h4>Related Product</h4>
                    </div>
                    <div class='products'>
                        <?php foreach($related_products as $related): ?>
                            <div class="product-grid">
                                <div class="product-grid-inner text-center">
                                    <div class="product-grid-img">
                                        <img class="img-fluid"
                                            src="<?php echo $GLOBALS['RentMy']::imageLink($related['id'], $related['images'][0]['image_small'], 'list'); ?>">
                                        <a href="javascript:void(0)">
                                            <div class="product-overley">

                                            </div>
                                        </a>
                                    </div>
                                    <div class="product-grid-body">
                                        <div class="product-name">
                                            <a href="<?php echo home_url('rentmy-product-details?uid='.$related['uuid']);?>">
                                                <h4><?php echo $related['name'];?></h4>
                                            </a>
                                        </div>
                                        <?php
                                        $priceTypes = getRentalTypes($related['prices']);
                                        $prices = getPrices($related['prices']);
                                        $generic_prices = empty($related['price']) ? $related['prices'] : $related['price']; ?>
                                        <?php if (in_array('rent', $priceTypes)) { ?>
                                            <span class="price">Starting at <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post') . (!empty($prices['rent'][0]['duration']) ? ' for '. $prices['rent'][0]['duration'] : '') . ' ' . (!empty($prices['rent'][0]['label']) ? $prices['rent'][0]['label'] : ''); ?></span>
                                        <?php } elseif (in_array('fixed', $priceTypes)) { ?>
                                            <span class="price">Starting at <?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post'); ?></span>
                                        <?php } else { ?>
                                            <span class="price">Buy now for <?php echo $GLOBALS['RentMy']::currency($prices['base']['price'], 'pre', 'amount', 'post'); ?></span>
                                        <?php } ?>
                                        <a class="button" href="<?php echo home_url('rentmy-product-details?uid='.$related['uuid']);?>">View Details</a>
                                        <!--                                        <a href="#" class="button add_to_cart_button_list">Add to cart</a>-->
                                        <?php if (in_array('base', $priceTypes) && ($related['type'] != 2)) { ?>
                                            <a data-variants_products_id="<?php echo $related['default_variant']['variants_products_id']; ?>"
                                            data-product_id="<?php echo $related['id']; ?>" href="javascript:void(0)"
                                            class="button add_to_cart_button_list">Add to cart</a>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <!-- related product block ends -->
        </div>
    </div>
    <?php
}

function getRentalTypes($prices)
{
    if (empty($prices)) {
        return false;
    }
    $types = [];
    foreach ($prices as $price) {
        foreach ($price as $k => $p) {
            if ($k == 'rent' || $k == 'hourly' || $k == 'daily' || $k == 'weekly' || $k == 'monthly' || $k == 'fixed') {
                $types[] = 'rent';
            } else {
                if (!empty($p['price'])) {
                    $types[] = $k;
                }
            }

        }
    }
    return array_unique($types);
}

function getPrices($prices)
{
    $formatPrice = [];
    $formatPrice['rent'] = [];
    foreach ($prices as $price) {
        foreach ($price as $k => $p) {
            if ($k == 'base') {
                $formatPrice['base'] = $p;
            } elseif ($k == 'fixed') {
                $formatPrice['rent'][] = $p;
            } else {
                foreach ($p as $i => $j) {
                    $formatPrice['rent'][] = $j;
                }
            }
        }
    }
    return $formatPrice;
}
