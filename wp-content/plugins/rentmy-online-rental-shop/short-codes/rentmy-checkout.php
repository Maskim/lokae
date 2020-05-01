<?php
//short code for checkout page
function rentmy_checkout_shortcode()
{
    ob_start();
    $checkout_step = null;
    if (empty($_GET['step'])):
        echo "<span class='rentmy-errore-msg'>There is no items in cart. Checkout can't be possible.</span>";
        return;
    else:
        $checkout_step = $_GET['step'];
    endif;

    $rentmy_checkout = new RentMy_Checkout();
    $rentmy_config = new RentMy_Config();
    $store_content = $rentmy_config->store_contents();

    $terms_condition = $rentmy_checkout->termsAndCondition();
    if(!empty($terms_condition)){
        $GLOBALS['terms_condition'] = $terms_condition['data'];
    }

    if(!empty($store_content)){
        $GLOBALS['checkout_labels'] = $store_content[0]['contents']['checkout_info'];
        $GLOBALS['payment_labels'] = $store_content[0]['contents']['checkout_payment'];
        $GLOBALS['signature'] = $store_content[0]['contents']['confg']['signature'];
    }

    try {
        $GLOBALS['rm_configs'] = (new RentMy_Config())->store_config();
        $GLOBALS['rm_cart'] = (new RentMy_Cart())->viewCart();
    } catch (\Exception $e) {

    }


    if ($checkout_step == 'info'):
        $GLOBALS['rm_countries'] = (new RentMy_Config())->countries();
        $GLOBALS['rm_custom_fields'] = $rentmy_checkout->getCustomFields();
        rentmy_checkout_info_template();
        return ob_get_clean();
    elseif ($checkout_step == 'fulfillment'):
        $configObj=new RentMy_Config();
        $GLOBALS['rm_countries'] = $configObj->countries();
        $GLOBALS['rm_delivery_settings']=$configObj->getDeliverySettings();
        $GLOBALS['rm_locations']=$configObj->getLocationList();
        rentmy_checkout_fulfillment_template();
        return ob_get_clean();
    elseif ($checkout_step == 'payment'):
        $configObj=new RentMy_Config();
        $GLOBALS['rm_payment_gateways']=$configObj->getPaymentGateWays();
        rentmy_checkout_payment_template();
        return ob_get_clean();
    elseif ($checkout_step == 'complete-order'):
        rentmy_checkout_complete_template();
        return ob_get_clean();
    else:
        wp_redirect(home_url('rent-my-checkout/?step=info'));
        return ob_get_clean();
    endif;
}

add_shortcode('rentmy-checkout', 'rentmy_checkout_shortcode');
