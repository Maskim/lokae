<?php
//short code for product details of a product
function rent_my_cart_details_shortcode()
{

    ob_start();
    $cart_token = null;
    if (empty($_SESSION['rentmy_cart_token'])):
        echo '<span class="rentmy-errore-msg">No items found in cart</span>';
        return;
    else:
        $cart_token = $_SESSION['rentmy_cart_token'];
    endif;

    $rentmy_cart = new RentMy_Cart();
    $response = $rentmy_cart->viewCart($cart_token);
    $cart_reated_product = $rentmy_cart->get_related_products_cart($cart_token);
    $GLOBALS['cart_related_product'] = !empty($cart_reated_product['result']['data']) ? $cart_reated_product['result']['data'] : null;

    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();
    if(!empty($store_content)){
        $GLOBALS['cart_labels'] = $store_content[0]['contents']['cart'];
    }

    if (!empty($response['data'])):
        $dataSet = $response['data'];
        $rent_my_cart_details = $dataSet;
        rentmy_cart_details_template($rent_my_cart_details);
        return ob_get_clean();
    else:
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        echo !empty($response['error']) ? '<span class="rentmy-errore-msg">' . $response['error'] . '</span>' : '';
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-cart-details', 'rent_my_cart_details_shortcode');

function rentmy_cart_details_template($rent_my_cart_details)
{
    ?>
    <?php if (!empty($rent_my_cart_details['cart_items'])): ?>

        <?php $cart_related_product = $GLOBALS['cart_related_product']; ?>
        <?php if(!empty($cart_related_product)): ?>
            <div class="rentmy-plugin-manincontent">
                <div class='rentmy-product-list cart-related-producst-list'>
                    <div class="related-product-title">
                        <h4>Add-on Products</h4>
                    </div>
                    <div class='products'>
                        <?php foreach($cart_related_product as $related): ?>
                        <div class="product-grid">
                            <div class="product-grid-inner text-center">
                                <div class="product-grid-img">
                                    <img class="img-fluid"
                                        src="<?php echo $GLOBALS['RentMy']::imageLink($related['id'], $related['images'][0]['image_small'], 'list'); ?>">
                                    <a href="<?php echo home_url('rentmy-product-details?uid='.$related['uuid']);?>">
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
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
        </div>
        <?php endif; ?>

        <div class="rentmy-plugin-manincontent">
            <form id="rentmy-cart-form" class="rentmy-cart-form" action="" method="post">

                <?php if(!empty($rent_my_cart_details['rent_start'])): ?>
                    <p>
                        <!-- show this by default.. it is from the cart added product -->
                        <label class="date-range-selection-default"> Rental Dates
                        <span>
                            <!-- <i class="fa fa-spin fa-spinner"></i> -->
                        </span>
                        <i class="fa fa-edit edit-icon date-range-selection-change"></i>
                        </label>

                        <!-- on the edit click show below forms -->
                        <label class="date-range-selection-active" style="display: none;">
                            <input style="width: 440px;" autocomplete="off" class="daterange" id="rm-date" type="text"
                                name="rm-date"
                                data-start_date="<?php echo date('m-d-Y h:i A', strtotime($rent_my_cart_details['rent_start'])); ?>"
                                data-end_date="<?php echo date('m-d-Y h:i A', strtotime($rent_my_cart_details['rent_end'])); ?>"
                                value="<?php echo date('m-d-Y h:i a', strtotime($rent_my_cart_details['rent_start'])) . '-' . date('m-d-Y h:i a', strtotime($rent_my_cart_details['rent_end'])); ?>"/>

                            <button onclick="return false;" class="button theme-btn cancel-btn date-range-selection-cancel">Cancel</button>
                        </label>
                    </p>
                <?php endif; ?>


                <table class="cart" cellspacing="0">
                    <thead>
                    <tr>
                        <th class="product-remove">&nbsp;</th>
                        <th class="product-thumbnail">
                            <?php esc_html_e(' ', 'rentmy'); ?>
                        </th>
                        <th class="product-name">
                            <?php echo $GLOBALS['cart_labels']['th_product']; ?>
                        </th>
                        <th class="product-price">
                            <?php echo $GLOBALS['cart_labels']['th_unit_price']; ?>
                        </th>
                        <th class="product-quantity">
                            <?php echo $GLOBALS['cart_labels']['th_quantity']; ?>
                        </th>
                        <th class="product-subtotal">
                            <?php echo $GLOBALS['cart_labels']['th_subtotal']; ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rent_my_cart_details['cart_items'] as $cart_items): ?>
                        <tr class="rentmy-cart-form__cart-item" id="cart-row-<?php echo $cart_items['id']; ?>">
                            <td class="product-remove">
                                <a href="javascript:void(0)" data-cart_item_id="<?php echo $cart_items['id']; ?>" data-product_id="<?php echo $cart_items['product_id']; ?>" class="remove remove_from_cart" aria-label="Remove this item">×</a>
                            </td>
                            <td class="product-thumbnail">
                                <img src="<?php echo $GLOBALS['RentMy']::imageLink($cart_items['product_id'], $cart_items['product']['images'][0]['image_small'], 'small'); ?>"
                                    alt="">
                            </td>
                            <td class="product-name" data-title="<?php esc_attr_e('Product', 'rentmy'); ?>">
                                <p><?php echo $cart_items['product']['name']; ?></p>
                            </td>
                            <td align="right" class="product-price rentmy-cart-row-price-<?php echo $cart_items['id'];?>" data-title="<?php esc_attr_e('Price', 'rentmy'); ?>">
                                <p><?php echo $GLOBALS['RentMy']::currency($cart_items['price']); ?></p>
                            </td>
                            <td align="right" class="product-quantity" data-title="<?php esc_attr_e('Quantity', 'rentmy'); ?>">
                                <div class="quantity clearfix">
                                    <span data-cart_item_price="<?php echo $cart_items['price']; ?>" data-increment="0" data-cart_item_id="<?php echo $cart_items['id'];?>" class="cart-minus btn btn-sm btn-dark no-m rentmy_item_quantity_update">-</span>
                                    <span class="cart-qunt btn btn-sm no-m rentmy-cart-row-quantity-<?php echo $cart_items['id'];?>"><?php echo $cart_items['quantity']; ?></span>
                                    <span data-cart_item_price="<?php echo $cart_items['price']; ?>" data-increment="1" data-cart_item_id="<?php echo $cart_items['id'];?>" class="cart-plus btn btn-sm btn-dark no-m rentmy_item_quantity_update">+</span>
                                </div>
                            </td>
                            <td align="right" class="product-subtotal" data-title="<?php esc_attr_e('Subtotal', 'rentmy'); ?>">
                                <p><?php echo $GLOBALS['RentMy']::currency($cart_items['sub_total'], 'pre', 'rentmy-cart-row-sub_total-' .  $cart_items['id'], 'post'); ?></p>
                            </td>
                        </tr>

                        <!-- addon items  -->
                        <?php if(!empty($cart_items['products'])): foreach ($cart_items['products'] as $addon_products): ?>
                        <tr class="rentmy-cart-form__cart-item cart-addon-row-<?php echo $cart_items['id']; ?>">
                            <td class="product-remove">

                            </td>
                            <td class="product-thumbnail">
                                <img src="<?php echo $GLOBALS['RentMy']::imageLink($addon_products['id'], $addon_products['images'], 'small'); ?>"
                                    alt="">
                            </td>
                            <td class="product-name">
                                <p>
                                    <?php echo $addon_products['name']; ?>
                                    <?php
                                    if (strpos($addon_products['variant_chain'], 'Unassigned') !== false) {

                                    } else {
                                        echo '<br><small>' . $addon_products['variant_chain'] . '</small>';
                                    }
                                    ?>
                                </p>
                            </td>
                            <td align="right" class="product-price">

                            </td>
                            <td align="right" class="product-quantity">
                                <div class="quantity clearfix">
                                    <span id="addon-item-quantity-<?php echo $addon_products['id']; ?>-parent-<?php echo $cart_items['id']; ?>" class="cart-qunt btn btn-sm no-m"><?php echo $addon_products['quantity']; ?></span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; endif; ?>
                        <!-- addon cart items ends here -->

                    <?php endforeach; ?>
                    <tr class="rentmy-cart-form__cart-item">
                        <td class="product-price">&nbsp;</td>
                        <td class="product-price">&nbsp;</td>
                        <td class="product-price">&nbsp;</td>
                        <td class="product-price">&nbsp;</td>
                        <td class="product-price">
                            Total:
                        </td>
                        <td class="product-price" data-title="<?php esc_attr_e('Total', 'rentmy'); ?>">
                            <p><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['total'], 'pre', 'rentmy-cart-sub_total', 'post'); ?></p>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <div class="cart-checkout-area">
                    <div class="coupon-code">
                        <input type="text" class="coupon-text rentmy_coupon_text" placeholder="<?php echo $GLOBALS['cart_labels']['txt_coupon']; ?>">
                        <a class="coupon-btn checkout-button button alt rm-forward rentmy_coupon_btn" href="javascript:void(0)"><?php echo $GLOBALS['cart_labels']['btn_coupon']; ?></a>
                    </div>
                    <div class="procces-contiue-checkout">
                        <a class="rentmy-checkout checkout-button button alt rm-forward" id="rentmy-checkout"
                        name="rentmy-checkout" href="<?php echo home_url('/rentmy-checkout?step=info'); ?>"><?php echo $GLOBALS['cart_labels']['btn_checkout']; ?></a>
                        <a class="continue-btn rentmy-checkout checkout-button button alt rm-forward" href="<?php echo home_url('/rentmy-products-list'); ?>"> Continue
                            Shopping </a>
                    </div>
                </div>
                <div class="cart-summery-area">
                    <h4 class="pb-2 cart-total-title">Cart Totals</h4>
                    <div class="table-responsive">
                        <table class="table cart">
                            <tbody>
                            <tr>
                                <td> Subtotal</td>
                                <td>
                                    <span class="cart_p"><b><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['sub_total'], 'pre', 'rentmy-cart-sub_total', 'post'); ?></b></span>
                                </td>
                            </tr>
                            <tr>
                                <td> Shipping Charge</td>
                                <td>
                                    <small class="cart_p"> Calculated in the next step</small>
                                </td>
                            </tr>
                            <tr>
                                <td> Discount</td>
                                <td>
                                    <span class="cart_p"> <?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['total_discount'], 'pre', 'rentmy-cart-total_discount', 'post'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td> <?php echo $GLOBALS['cart_labels']['lbl_tax']; ?></td>
                                <td>
                                    <span class="cart_p"> <?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['tax'], 'pre', 'rentmy-cart-tax', 'post'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td> Delivery Tax</td>
                                <td>
                                    <small class="cart_p"> Calculated in the next step</small>
                                </td>
                            </tr>
                            <tr>
                                <td> <?php echo $GLOBALS['cart_labels']['lbl_total_deposite']; ?></td>
                                <td>
                                    <span class="cart_p"> <?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['deposit_amount'], 'pre', 'rentmy-cart-deposit_amount', 'post'); ?></span>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <h5><?php echo $GLOBALS['cart_labels']['lbl_total']; ?></h5></td>
                                <td>
                                    <h5>
                                        <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['total'], 'pre', 'rentmy-cart-total', 'post'); ?></span>
                                    </h5></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>
    <?php else: ?>
        <span class="rentmy-errore-msg">No items found in cart</span>
    <?php endif; ?>
    <?php
}
