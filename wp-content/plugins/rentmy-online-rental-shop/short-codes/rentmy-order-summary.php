<?php
//list all tags of a store
function rent_my_order_summary_shortcode(){

    ob_start();
    $cart_token = !empty($_SESSION['rentmy_cart_token']) ? $_SESSION['rentmy_cart_token'] : null;

    $rentmy_cart = new RentMy_Cart();
    $response = $rentmy_cart->viewCart($cart_token);

    if (!empty($response['data'])) {
        $rent_my_cart_details = $response['data'];
    } else {
        $rent_my_cart_details = null;
    }
    rentmy_order_summary_template($rent_my_cart_details);
    return ob_get_clean();
}
add_shortcode( 'rentmy-order-summary', 'rent_my_order_summary_shortcode' );

function rentmy_order_summary_template($rent_my_cart_details){
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-plugin-categorytagsearch-area">
            <h3>Order Summary</h3>
            <div class="rentmy-cart-form-sidebar">
            </div>
            <form id="rentmy-cart-form-sidebar" action="" method="post">
                <?php if (!empty($rent_my_cart_details['cart_items'])): ?>
                    <table class="cart" cellspacing="0">
                        <tbody>
                        <?php foreach ($rent_my_cart_details['cart_items'] as $cart_items): ?>
                            <tr class="rentmy-cart-form__cart-item" id="cart-row-<?php echo $cart_items['id']; ?>">
                                <td width="20%">
                                    <img class="rentmy-responsive-image" src="<?php echo $GLOBALS['RentMy']::imageLink($cart_items['product_id'], $cart_items['product']['images'][0]['image_small'], 'small'); ?>"
                                        alt="">
                                </td>
                                <td width="80%">
                                    <h5><?php echo $cart_items['product']['name']; ?></h5>
                                    <h6>Price: <?php echo $GLOBALS['RentMy']::currency($cart_items['price']); ?> Qty: <?php echo $cart_items['quantity']; ?></h6>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
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
                                <small class="cart_p">
                                    <?php if( $rent_my_cart_details['delivery_charge'] >= 0 && trim($_GET['step']) == 'payment' ) {
                                            echo $GLOBALS['RentMy']::currency($rent_my_cart_details['delivery_charge']);
                                        } else { ?>
                                            Calculated in the next step
                                    <?php } ?>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td> Discount</td>
                            <td>
                                <span class="cart_p"> <?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['total_discount'], 'pre', 'rentmy-cart-total_discount', 'post'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td> Tax</td>
                            <td>
                                <span class="cart_p"> <?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['tax'], 'pre', 'rentmy-cart-tax', 'post'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td> Delivery Tax</td>
                            <td>
                                <small class="cart_p">
                                    <?php if( $rent_my_cart_details['delivery_tax'] >= 0 && trim($_GET['step']) == 'payment') {
                                        echo $GLOBALS['RentMy']::currency($rent_my_cart_details['delivery_tax']);
                                    } else { ?>
                                        Calculated in the next step
                                    <?php } ?>
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td> Deposit Amount</td>
                            <td>
                                <span class="cart_p"> <?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['deposit_amount'], 'pre', 'rentmy-cart-deposit_amount', 'post'); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5>Total</h5></td>
                            <td>
                                <h5>
                                    <span class="cart_p"><?php echo $GLOBALS['RentMy']::currency($rent_my_cart_details['total'], 'pre', 'rentmy-cart-total', 'post'); ?></span>
                                </h5></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <span class="rentmy-errore-msg">No items found in cart</span>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <?php
}
