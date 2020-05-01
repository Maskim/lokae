<?php
function rentmy_mini_cart_template()
{
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-plugin-categorytagsearch-area">
            <ul class="nav-list">
                <li>
                    <a class="cart-bar">
                        <i class="fa fa-shopping-bag"></i>
                        <span class="cart-item cart-item-total-count-topbar">0</span>
                    </a>
                </li>
            </ul>
            <div class="cart-body">
                <span class="top-arrow"><i class="fa fa-caret-up"></i></span>
                <div class="inner-cart-body inner-cart-body-topbar">
                    <i style="margin: 30px auto; display: table;" class="fa fa-smile-o fa-5x" aria-hidden="true"></i>
                    <p class="text-center">No Products in cart</p>
                </div>
                <div class="carthome-total w-100" style="display:none">
                    <h5> Cart Total <span class="cart-item-total-topbar"></span></h5>
                    <a href="<?php echo home_url('rentmy-checkout?step=info'); ?>"
                    class="button lbtn-50 theme-btn lbtn-xs radius">Checkout</a>
                    <a href="<?php echo home_url('rentmy-cart'); ?>" class="button lbtn-50 theme-btn lbtn-xs radius">View
                        Cart</a>
                </div>
                <br>
            </div>
        </div>
    </div>
    <?php
}
add_shortcode( 'rentmy-mini-cart', 'rentmy_mini_cart_template' );