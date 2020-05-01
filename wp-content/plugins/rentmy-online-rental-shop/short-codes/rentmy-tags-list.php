<?php
//list all tags of a store
function rent_my_tags_list_shortcode(){

    ob_start();
    $rentmy_tags = new RentMy_Category();

    $response = $rentmy_tags->tags();

    if( !empty($response) ):
        $dataSet = $response;
        $rent_my_tags_list = $dataSet;
        echo rentmy_tags_template($rent_my_tags_list);
        return ob_get_clean();
    else:
        echo !empty($response['message']) ? '<span class="rentmy-errore-msg">' . $response['message'] . '</span>' : '';
        return ob_get_clean();
    endif;
}
add_shortcode( 'rentmy-tags-list', 'rent_my_tags_list_shortcode' );

function rentmy_tags_template($array){

    $tags = !empty($_GET['tags']) ? explode(',', $_GET['tags']) : [];
    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-plugin-categorytagsearch-area">
            <form action="<?php echo home_url('rentmy-products-list');?>" method="get" id="rentmy-tag-filter-form" accept-charset="ISO-8859-1">
                <h3>Filters</h3>
                <?php if(!empty($array)): ?>
                    <ul class="rentmy-product-categories filter-rentmy">
                        <?php foreach($array as $arr): ?>
                        <li class="tag-filter">
                            <div class="rentmy-form-group-100">
                            <label for="filter-checkbox-<?php echo $arr['id']; ?>" class="checkbox-container custom-control-label">
                                <input class="custom-control-input rentmy-control-input" id="filter-checkbox-<?php echo $arr['id']; ?>" type="checkbox" <?php echo in_array($arr['id'], $tags) ? 'checked' : ''; ?> value="<?php echo $arr['id']; ?>" />
                                <?php echo $arr['name']; ?>
                                <?php echo in_array($arr['id'], $tags) ? '<span class="checkmark"></span>' : '<span class="checkmark rentmy-no-check"></span>'; ?>
                                </label>
                            </div>
                        </li>
                        <?php endforeach; ?>
                        <input class="rentmy-tags" type="hidden" readonly name="tags" value="">
                    </ul>
                <?php endif; ?>

                <h3>Price</h3>
                <ul class="rentmy-product-categories price-rentmy">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="min_price">Min</label>
                            <input autocomplete="off" type="text" class="form-control value-price-filter" name="min_price" value="<?php echo !empty($_GET['min_price']) ? (int)trim($_GET['min_price']) : null;?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="max_price">Max</label>
                            <input autocomplete="off" type="text" class="form-control value-price-filter" name="max_price" value="<?php echo !empty($_GET['max_price']) ? (int)trim($_GET['max_price']) : null;?>">
                        </div>
                    </div>
                    <div class="w-100">
                        <input type="submit" class="theme-btn lbtn-sm" value="Submit">
                        <input type="button" class="clear-price-filter theme-btn lbtn-sm" value="Clear">
                    </div>
                </ul>

                <h3>Type</h3>
                <ul class="rentmy-product-categories type-area">
                    <label class="type-area-container tag-filter">Rent
                        <input type="radio" name="purchase_type" value="rent" <?php echo !empty($_GET['purchase_type']) ? ($_GET['purchase_type'] == 'rent' ? 'checked' : '') : ''; ?>>
                        <span class="type-area-checkmark"></span>
                    </label>
                    <label class="type-area-container tag-filter">Buy
                        <input type="radio" name="purchase_type" value="buy" <?php echo !empty($_GET['purchase_type']) ? ($_GET['purchase_type'] == 'buy' ? 'checked' : '') : ''; ?>>
                        <span class="type-area-checkmark"></span>
                    </label>
                    <label class="type-area-container tag-filter">All
                        <input type="radio" name="purchase_type" value="" <?php echo !empty($_GET['purchase_type']) ? ($_GET['purchase_type'] == 'all' ? 'checked' : '') : 'checked'; ?>>
                        <span class="type-area-checkmark"></span>
                    </label>
                </ul>

                <?php if(!empty($_GET['uid'])): ?>
                    <input type="hidden" readonly name="uid" value="<?php echo $_GET['uid']; ?>">
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php
}
