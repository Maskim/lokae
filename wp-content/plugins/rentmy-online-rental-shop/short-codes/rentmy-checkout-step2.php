<?php
function rentmy_checkout_fulfillment_template()
{
    $shipping_setting = false;
    $instore_pickup = true;
    $delivery_setting = false;
    $checkout_info = null;
    if ($GLOBALS['rm_delivery_settings']['delivery_settings']['shipping']) {
        $shipping_setting = true;
    }
    if ($GLOBALS['rm_delivery_settings']['delivery_settings']['disable_instore_pickup']) {
        $instore_pickup = false;
    }
    if ($GLOBALS['rm_delivery_settings']['delivery_settings']['is_requiered']) {
        $delivery_setting = true;
    }

    if(!empty($GLOBALS['checkout_labels'])){
        $checkout_label = $GLOBALS['checkout_labels'];
    }

    if(!empty($_SESSION['rentmy_checkout']['info'])){
        $checkout_info = $_SESSION['rentmy_checkout']['info'];
    }
    
    if(!empty($_SESSION['rentmy_checkout']['fulfillment'])){
        $checkout_shipping = $_SESSION['rentmy_checkout']['fulfillment'];

        $checkout_info['country'] = $checkout_shipping['shipping_country'] ?? $checkout_info['country'];
        $checkout_info['address_line1'] = $checkout_shipping['shipping_address1'] ?? $checkout_info['address_line1'];
        $checkout_info['address_line2'] = $checkout_shipping['shipping_address2'] ?? $checkout_info['address_line2'];
        $checkout_info['city'] = $checkout_shipping['shipping_city'] ?? $checkout_info['city'];
        $checkout_info['zipcode'] = $checkout_shipping['shipping_zipcode'] ?? $checkout_info['zipcode'];
        $checkout_info['state'] = $checkout_shipping['shipping_state'] ?? $checkout_info['state'];

    }

    ?>
    <div class="rentmy-plugin-manincontent">
        <div class="rentmy-checkout-fullfilment">
            <ul class="rentmy-progressbar">
                <li>
                    <a class="btn btn-circle">1</a><br>
                    <?php echo $checkout_label['step_one']; ?>
                </li>
                <li class="active">
                    <a class="btn btn-circle">2</a><br>
                    <?php echo $checkout_label['step_two']; ?>
                </li>
                <li>
                    <a class="btn btn-circle">3</a><br>
                    <?php echo $checkout_label['step_three']; ?>
                </li>
                <li>
                    <a class="btn btn-circle">4</a><br>
                    <?php echo $checkout_label['step_four']; ?>
                </li>
            </ul>

            <form id="checkout-<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>" class="" action=""
                method="post">
                <div class="fullfilment-type">
                    <ul>
                        <?php if ($instore_pickup) { ?>
                            <li data-id="instore">
                                <img src="<?php echo plugin_dir_url(__DIR__); ?>assets/images/pickup.png">
                                <span>Instore Pickup</span>
                            </li>
                        <?php } ?>
                        <?php if ($delivery_setting) { ?>
                            <li data-id="delivery">
                                <img src="<?php echo plugin_dir_url(__DIR__); ?>assets/images/truck.png">
                                <span>Delivery</span>
                            </li>
                        <?php } ?>
                        <?php if ($shipping_setting) { ?>
                            <li data-id="shipping">
                                <img src="<?php echo plugin_dir_url(__DIR__); ?>assets/images/airplane.png">
                                <span>Shipping</span>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <form id="checkout-<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>" class="" action="" method="post" onsubmit="return false;">
                <div class="rm-instore-container" style="display: none;">
                    <div class="checkout-header">
                        <h2>
                            Shipping Options (Instore)
                        </h2>
                    </div>

                    <div>
                        <?php  foreach ($GLOBALS['rm_locations']['data'] as $i=>$location) { ?>
                            <label class="radio-container">
                                <input type="radio" <?php if($i==0){ ?> checked="checked" <?php } ?> name="rm_instore_loc" value="<?php echo $location['id'];?>"
                                data-type="instore" data-location="<?php echo $location['location'];?>" data-name="<?php echo $location['name'];?>">
                                <?php echo $location['name'] . ' (' . $location['location'] . ')'; ?>
                                <span class="checkmark"></span>
                            </label>
                        <?php } ?>
                    </div>
                </div>
                <div class="rm-delivery-container"  style="display: none;">
                    <div class="checkout-header">
                        <h2>
                            Shipping Options
                        </h2>
                    </div>

                    <div class="rentmy-form-row">
                        <div class="form-group">
                            <label for="">Country*</label>
                            <select name="shipping_country" id="rm_sh_country" class="form-control" required>
                                <?php foreach ($GLOBALS['rm_countries'] as $country) { ?>
                                    <option value="<?php echo $country['code'];?>" <?php if($country['code'] == (!empty($checkout_info) ? $checkout_info['country'] : 'US')){ ?> selected <?php } ?>><?php echo $country['name'];?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for=""><?php echo $checkout_label['lbl_address_line_1']; ?>*</label>
                            <input name="shipping_address1" class="form-control" id="rm_sh_address_line1"
                                autocomplete="shipping address-line1" required type="text" value="<?php echo $checkout_info['address_line1'] ?? ''; ?>"/>
                        </div>
                        <div class="form-group">
                            <label for=""><?php echo $checkout_label['lbl_address_line_2']; ?></label>
                            <input name="shipping_address2"  class="form-control" id="rm_sh_address_line2"
                                autocomplete="shipping  address-line2" type="text" value="<?php echo $checkout_info['address_line2'] ?? ''; ?>"/>
                        </div>
                        <div class="form-group">
                            <label for=""><?php echo $checkout_label['lbl_city']; ?>*</label>
                            <input name="shipping_city" id="rm_sh_city" class="form-control" autocomplete="shipping  address-level2"
                                type="text" value="<?php echo $checkout_info['city'] ?? ''; ?>"/>
                        </div>
                        <div class="form-group ">
                            <label for=""><?php echo $checkout_label['lbl_zipcode']; ?>*</label>
                            <input name="shipping_zipcode" id="rm_sh_zipcode" class="form-control"
                                autocomplete="shipping postal-code"
                                type="text" value="<?php echo $checkout_info['zipcode'] ?? ''; ?>"/>
                        </div>
                        <div class="form-group">
                            <label for=""><?php echo $checkout_label['lbl_state']; ?> *</label>
                            <input name="shipping_state" id="rm_sh_state" class="form-control"
                                autocomplete="shipping address-level1" type="text" value="<?php echo $checkout_info['state'] ?? ''; ?>"/>
                        </div>
                    </div>

                </div>
                <div class="checkout-back-continue">
                    <input type="hidden" id="shipping_method" name="shipping_method" value=""></input>
                    <a class="back-continue-btn back-btn rentmy-checkout checkout-button" name="rentmy-checkout"
                    href="<?php echo home_url('/rentmy-checkout/?step=info'); ?>">BACK</a>
                    <input class="back-continue-btn checkout-continue-btn rentmy-hidden" type="button" id="rm-btn-delivery-cost"
                        value="GET DELIVERY COST"/>
                    <input class="back-continue-btn checkout-continue-btn rentmy-hidden" type="button" id="rm-btn-shipping-cost"
                        value="GET SHIPPING METHODS" />
                    <input type="submit" class="back-continue-btn checkout-continue-btn"
                        id="rentmy-btn-checkout-fulfillment"
                        data-succeredirect="<?php echo home_url('/rentmy-checkout/?step=payment'); ?>"
                        data-step="<?php echo !empty($_GET['step']) ? $_GET['step'] : null; ?>"
                        name="rentmy-checkout" value="CONTINUE"/>
                </div>
                <div class="checkout-fulfillment-message"></div>
            </form>
        </div>
    </div>
    <?php
}
