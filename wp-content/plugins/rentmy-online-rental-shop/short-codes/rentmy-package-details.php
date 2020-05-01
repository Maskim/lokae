<?php
//short code for product details of a product
function rentmy_package_details_shortcode($params)
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
        echo '<span class="rentmy-errore-msg">Invalid Package</span>';
        return;
    endif;

    $check_cart = (new RentMy_Cart())->viewCart();

    $rentmy_products = new RentMy_Products();

    $cart_params = [
        'token' => !empty($_SESSION['rentmy_cart_token']) ? $_SESSION['rentmy_cart_token'] : null,
        'start_date' => !empty($_SESSION['rentmy_rent_start']) ? $_SESSION['rentmy_rent_start'] : null,
        'end_date' => !empty($_SESSION['rentmy_rent_end']) ? $_SESSION['rentmy_rent_end'] : null,
    ];

    $response = $rentmy_products->package_details($product_id, $cart_params);
    //$GLOBALS['RentMy']::pr($response);

    if (!empty($response['data'])):
        $dataSet = $response['data'];
        $rent_my_product_details = $dataSet;

        $rent_my_product_details['rent_dates'] = !empty($check_cart['data']) ? [ 'rent_start' => $check_cart['data']['rent_start'], 'rent_end' => $check_cart['data']['rent_end'] ] : [];

        rentmy_package_details_template($rent_my_product_details);
        return ob_get_clean();
    else:
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-package-details', 'rentmy_package_details_shortcode');

function rentmy_package_details_template($rent_my_product_details)
{
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-product-details">
            <div class="">
                <div class="images">
                    <img src="<?php echo $GLOBALS['RentMy']::imageLink($rent_my_product_details['id'], $rent_my_product_details['images'][0]['image_large'], 'list'); ?>">
                </div>
                <div class="details">
                    <h1 class="product_title"><?php echo $rent_my_product_details['name']; ?></h1>
                    <?php $priceTypes = getRentalTypes($rent_my_product_details['price']);
                    $prices = getPrices($rent_my_product_details['price']);
                    ?>
                    <div class="price">
                        <div class="buy" style="display: none;">
                            <h6><?php echo !empty($prices['base']['price']) ? $GLOBALS['RentMy']::currency($prices['base']['price'], 'pre', 'amount', 'post') : ''; ?></h6>
                        </div>
                        <div class="rent" style="display: none;">
                            <?php if(!empty($rent_my_product_details['rental_price'])): ?>
                                <h6><?php echo $GLOBALS['RentMy']::currency($rent_my_product_details['rental_price'], 'pre', 'amount', 'post'); ?></h6>
                            <?php else: ?>
                                <h6><?php echo $GLOBALS['RentMy']::currency($prices['rent'][0]['price'], 'pre', 'amount', 'post') . ' for ' . $prices['rent'][0]['duration'] . ' ' . $prices['rent'][0]['label']; ?></h6>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="rental-type">
                        <!--                    <label class="radio-container buy_input"-->
                        <!--                           for="buy" --><?php //if ($rent_my_product_details['type']==2) {
                        ?><!-- style="display:none;" --><?php //}
                        ?><!-- >-->
                        <!--                        <input type="radio" checked="checked" id="rental_type_buy" name="rental_type" value="buy">-->
                        <!--                        Buy-->
                        <!--                        <span class="checkmark"></span>-->
                        <!--                    </label>-->

                        <label class="radio-container rent_input"
                            for="rent" <?php if (!in_array('rent', $priceTypes)) { ?> style="display:none;" <?php } ?>>
                            <input type="radio" checked="checked" id="rental_type_rent" name="rental_type" value="rent">
                            Rent
                            <span class="checkmark"></span>
                        </label>

                    </div>

                    <?php /** Start Pricing Options  */ ?>


                    <?php if(empty($rent_my_product_details['rent_dates']['rent_start'])): ?>

                        <div class="price-options" style="display: none;">
                            <?php foreach ($prices['rent'] as $i => $rents) { ?>
                                <label class="radio-container">
                                    <input type="radio" name="rental-price"
                                        data-start_date="<?php echo date('m-d-Y h:i A', strtotime($rents['rent_start'])); ?>"
                                        data-end_date="<?php echo date('m-d-Y h:i A', strtotime($rents['rent_end'])); ?>"
                                        value="<?php echo $rents['id']; ?>" <?php if ($i == 0) { ?> checked <?php } ?> >
                                    <?php echo $GLOBALS['RentMy']::currency($rents['price']) . '/' . $rents['duration'] . ' ' . $rents['label']; ?>
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
                                        data-start_date="<?php echo date('m-d-Y h:i A', strtotime($fixed_rent_dates['rent_start'])); ?>"
                                        data-end_date="<?php echo date('m-d-Y h:i A', strtotime($fixed_rent_dates['rent_end'])); ?>"
                                        value="<?php echo $rents['id']; ?>" <?php if ($i == 0) { ?> checked <?php } ?>>
                                    <?php echo $GLOBALS['RentMy']::currency($rents['price']) . '/' . $rents['duration'] . ' ' . $rents['label']; ?>
                                    <br>
                                </label>
                            <?php } ?>
                        </div>

                    <?php endif;?>

                    <?php /** Variant Set && Variants select  */ ?>
                    <div class="variants">
                        <?php
                        if (!empty($rent_my_product_details['variant_set_list']))
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
                    <div class="availability">
                        <span>Available :
                            <span class="availability-count">
                                <?php echo $rent_my_product_details['term']; ?>
                            </span>
                        </span>
                    </div>
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

                    <div class="package-items">
                        <h5>Package Includes</h5>
                        <ul>
                            <?php foreach ($rent_my_product_details['products'] as $packageItems) { ?>
                                <li>
                                    <h6 data-id="<?php echo $packageItems['id']; ?>"><?php echo $packageItems['name'] . ' (' . $packageItems['quantity'] . ')'; ?></h6>

                                    <div <?php if(!empty($packageItems['variants'][0]['variant_chain']) && ($packageItems['variants'][0]['variant_chain']== 'Unassigned: Unassigned')) { ?> style="display: none;" <?php } ?>>
                                        <select class="package_variant">
                                            <?php foreach ($packageItems['variants'] as $i => $pvariants) { ?>
                                                <option value="<?php echo $pvariants['id']; ?>" <?php if ($i == 0) { ?> selected <?php } ?>><?php echo $pvariants['variant_chain']; ?> </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <p class="description"><?php echo $rent_my_product_details['description']; ?></p>
                    <div class="hidden_variables">
                        <input type="hidden" id="rm_pd_product_id" value="<?php echo $rent_my_product_details['id']; ?>"/>
                        <input type="hidden" id="rm_pd_product_uid" value="<?php echo $rent_my_product_details['uid']; ?>"/>
                        <input type="hidden" id="rm_v_products_id"
                            value="<?php echo $rent_my_product_details['variants_products_id']; ?>"/>
                    </div>

                    <button type="button" class="add_to_cart_package_button button alt"
                            name="rentmy-rent-item"
                            value="ADD TO CART">ADD TO CART
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
}

//function getRentalTypes($prices)
//{
//    if (empty($prices)) {
//        return false;
//    }
//    $types = [];
//    foreach ($prices as $price) {
//        foreach ($price as $k => $p) {
//            if ($k == 'rent' || $k == 'hourly' || $k == 'daily' || $k == 'weekly' || $k == 'monthly') {
//                $types[] = 'rent';
//            } else {
//                if (!empty($p['price'])) {
//                    $types[] = $k;
//                }
//            }
//
//        }
//    }
//    return array_unique($types);
//}
//
//function getPrices($prices)
//{
//    $formatPrice = [];
//    $formatPrice['rent'] = [];
//    foreach ($prices as $price) {
//        foreach ($price as $k => $p) {
//            if ($k == 'base') {
//                $formatPrice['base'] = $p;
//            } else {
//                foreach ($p as $i => $j) {
//                    $formatPrice['rent'][] = $j;
//                }
//            }
//        }
//    }
//    return $formatPrice;
//}
