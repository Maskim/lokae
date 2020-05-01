<?php
//list all tags of a store
function rent_my_search_shortcode(){

    ob_start();
    echo rentmy_search_template();
    return ob_get_clean();
}
add_shortcode( 'rentmy-search', 'rent_my_search_shortcode' );

function rentmy_search_template(){
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-plugin-categorytagsearch-area">
            <h3>Search</h3>
            <form action="<?php echo home_url('rentmy-products-list');?>" method="get" id="rentmy-search-form" accept-charset="ISO-8859-1">
                <div class="cart-checkout-area">
                    <div class="coupon-code">
                        <input type="text" class="coupon-text rentmy_coupon_text" placeholder="Search" name="search" value="<?php echo !empty($_GET['search']) ? $_GET['search'] : null;?>">
                        <button class="rentmy-button" type="submit" name="rentmy-submit-search" value="search-submit">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}
