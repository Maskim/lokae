<?php

/**
 * ajax call methods and other stuffs
 */
class RentMy_Ajax
{

    function __construct()
    {
      //  add_action('wp_ajax_nopriv_rentmy_add_to_cart', array($this, 'rentmy_add_to_cart'));
     //   add_action('wp_ajax_rentmy_add_to_cart', array($this, 'rentmy_add_to_cart'));
        add_action('wp_ajax_nopriv_rentmy_checkout_information', array($this, 'rentmy_checkout_information'));
        add_action('wp_ajax_rentmy_checkout_information', array($this, 'rentmy_checkout_information'));

        add_action('wp_ajax_nopriv_rentmy_options', array($this, 'rentmy_options'));
        add_action('wp_ajax_rentmy_options', array($this, 'rentmy_options'));

        add_action('wp_ajax_nopriv_rentmy_order_details', array($this, 'rentmy_order_details'));
        add_action('wp_ajax_rentmy_order_details', array($this, 'rentmy_order_details'));

        add_action('wp_ajax_nopriv_rentmy_cart_topbar', array($this, 'rentmy_cart_topbar'));
        add_action('wp_ajax_rentmy_cart_topbar', array($this, 'rentmy_cart_topbar'));
    }

    function rentmy_add_to_cart($data)
    {
        $add_to_cart = new RentMy_Cart();
        $response = $add_to_cart->addProductToCart($data);
        return $response;
    }

    function rentmy_remove_from_cart($data)
    {
        $remove_cart = new RentMy_Cart();
        $response = $remove_cart->deleteCart($data);
        return $response;
    }

    function rentmy_update_cart($data)
    {
        $view_update_cart = new RentMy_Cart();
        $response = $view_update_cart->viewCart();
        return $response;
    }

    function rentmy_update_cart_item($data)
    {
        $update_cart_item = new RentMy_Cart();
        $response = $update_cart_item->updateCart($data);
        return $response;
    }

    function rentmy_apply_coupon($data)
    {
        $apply_coupon = new RentMy_Cart();
        $response = $apply_coupon->applyCoupon($data);
        return $response;
    }

    function get_price_value($data)
    {
        $get_price_value = new RentMy_Products();
        $response = $get_price_value->get_price_value($data);
        return $response;
    }

    function get_cart_availability($data)
    {
        $get_available_products = new RentMy_Cart();
        $response = $get_available_products->getCartAvailability($data);
        return $response;
    }

    function get_exact_duration($data)
    {
        $get_exact_duration = new RentMy_Products();
        $response = $get_exact_duration->getExactDuration($data['start_date']);
        return $response;
    }

    function get_dates_from_duration($data)
    {
        $get_dates_from_duration = new RentMy_Products();
        $response = $get_dates_from_duration->getDatesFromDuration($data);
        return $response;
    }

    function get_dates_price_duration($data)
    {
        $get_dates_price_duration = new RentMy_Products();
        $response = $get_dates_price_duration->getDatesPriceDuration($data);
        return $response;
    }


    function rentmy_checkout_information()
    {
        $data = [];

        $checkout_info = new RentMy_Checkout();

        if ($_POST['step'] == 'info') {
            parse_str($_POST['data'], $data);
            $response = $checkout_info->saveInfo($data);
        } elseif ($_POST['step'] == 'fulfillment') {
            $data= $_POST['data'];
            $response = $checkout_info->saveFulfilment($data);
        } elseif ($_POST['step'] == 'payment') {
            $response = $checkout_info->savePayment($data);
        } else {
            wp_send_json([
                'status' => 'NOK',
                'message' => '',
            ]);
        }

        wp_send_json($response);
    }

    /**
     * $action_type = 'get_variant_chain' ->get change variant
     * $action_type = 'get_last_variant' -> get final product details of the chain
     *
     */
    function rentmy_options()
    {
        $data = $_POST;
        $action = $data['action_type'];
        switch ($action) {
            case 'get_variant_chain' :
                $params=['product_id'=> $data['data']['product_id'],'variant_id'=> $data['data']['variant_id'],'chain_id'=> $data['data']['chain_id']];
                $response=(new RentMy_Products())->get_product_variant_chain($params);
                break;
            case 'get_last_variant':
                $params=['product_id'=> $data['data']['product_id'],'variant_id'=> $data['data']['variant_id'],'chain_id'=> $data['data']['chain_id']];
                $response=(new RentMy_Products())->get_product_fromchain($params);
                if(!empty($response['prices'][0])){
                    foreach($response['prices'][0] as $key=>$prices){
                        if($key== 'base'){
                            $fPrice="<h6>".$GLOBALS['RentMy']::currency($prices['price']). "</h6>";
                            $response['prices'][0][$key]['html']= $fPrice;
                        }else{
                            foreach($prices as $i=>$price){
                                $fPrice="<h6>".$GLOBALS['RentMy']::currency($price['price']). " for ". $price['duration']. " ".$price['label']."</h6>";
                                $priceOptions='<label class="radio-container" for="rent">';
                                $priceOptions.='<input type="radio" checked="checked"  name="rental-price" value="'.$price['id'].'">';
                                $priceOptions.= $GLOBALS['RentMy']::currency($price['price']) . '/' . $price['duration'] . ' ' . $price['label'];
                                $priceOptions.= '<span class="checkmark"></span></label>';
                                $response['prices'][0][$key][$i]['price_options']= $priceOptions;
                                $response['prices'][0][$key][$i]['html']= $fPrice;

                            }
                        }
                    }
                }
                break;
            case 'get_config':
                $response=get_option('rentmy_config');
                break;
            case 'add_to_cart':
                $response=$this->rentmy_add_to_cart($data['data']);
                break;
            case 'add_to_cart_package':
                $add_to_cart = new RentMy_Cart();
                $response = $add_to_cart->addPackageToCart($data['data']);
                break;
            case 'update_package_availability':
                $productObj=new RentMy_Products();
                $response=$productObj->check_package_availability($data['data']);
                break;
            case 'remove_from_cart':
                $response=$this->rentmy_remove_from_cart($data['data']);
                break;
            case 'update_cart':
                $response=$this->rentmy_update_cart($data['data']);
                break;
            case 'apply_coupon':
                $response=$this->rentmy_apply_coupon($data['data']);
                break;
            case 'update_cart_item':
                $response=$this->rentmy_update_cart_item($data['data']);
                break;
            case 'get_price_value':
                $response=$this->get_price_value($data['data']);
                break;
            case 'get_cart_availability':
                $response = $this->get_cart_availability($data['data']);
                break;
            case 'get_delivery_cost':
                $response=(new RentMy_Checkout())->getDeliveryCost($data['data']);
                break;
            case 'upload_media':
                $data['file'] = $_FILES['file'];
                $response=(new RentMy_Checkout())->uploadMedia($data);
                break;
            case 'get_shipping_methods':
                parse_str($data['data'], $requestData);
                $response=(new RentMy_Checkout())->getShippingList($requestData);
                break;
            case 'add_shipping_to_cart':
                $response=(new RentMy_Checkout())->addShippingToCarts($data['data']);
                break;
            case 'submit_order':
                $response=(new RentMy_Checkout())->doCheckout($data['data']);
                break;
            case 'free_shipping':
                $response=(new RentMy_Checkout())->checkFreeShipping();
                break;
            case 'get_exact_duration':
                $response=$this->get_exact_duration($data['data']);
                break;
            case 'get_dates_from_duration':
                $response=$this->get_dates_from_duration($data['data']);
                break;
            case 'get_dates_price_duration':
                $response=$this->get_dates_price_duration($data['data']);
                break;
        }

        wp_send_json($response);
    }

    function rentmy_order_details(){
        $cart_token = null;
        if (empty($_SESSION['rentmy_cart_token'])):
            $response = null;
        else:
            $cart_token = $_SESSION['rentmy_cart_token'];
            $rentmy_cart = new RentMy_Cart();
            $response = $rentmy_cart->viewCart($cart_token);
            $response = $response['data'];
        endif;
        $html = $this->rentmy_order_summary_ajax_template($response);
        die($html);
    }

    function rentmy_cart_topbar(){
        $cart_token = null;
        if (empty($_SESSION['rentmy_cart_token'])):
            $response = null;
        else:
            $cart_token = $_SESSION['rentmy_cart_token'];
            $rentmy_cart = new RentMy_Cart();
            $response = $rentmy_cart->viewCart($cart_token);
            $response = $response['data'];
        endif;
        wp_send_json($response);
    }

    function rentmy_order_summary_ajax_template($rent_my_cart_details){

        $html = '';

        if(empty($rent_my_cart_details)):
            $html .= '<p class="rentmy-success-msg">No items found in cart</p>';
            return $html;
        endif;

        if (!empty($rent_my_cart_details['cart_items'])):

            $html .='<form id="rentmy-cart-form-sidebar" action="" method="post">
                <table class="cart" cellspacing="0">
                    <tbody>';

                    foreach ($rent_my_cart_details['cart_items'] as $cart_items):
                        $html .='<tr class="rentmy-cart-form__cart-item" id="cart-row-'.$cart_items['id'].'">
                            <td width="20%">
                                <img class="rentmy-responsive-image" src="'.$GLOBALS['RentMy']::imageLink($cart_items['product_id'], $cart_items['product']['images'][0]['image_small'], 'small').'"
                                     alt="">
                            </td>
                            <td width="80%">
                                <h5>'.$cart_items['product']['name'].'</h5>
                                <h6>Price: '.$GLOBALS['RentMy']::currency($cart_items['price']).' Qty: '.$cart_items['quantity'].'</h6>
                            </td>
                        </tr>';
                    endforeach;

                    $html .='</tbody>
                </table>
                <div class="table-responsive">
                    <table class="table cart">
                        <tbody>
                        <tr>
                            <td> Subtotal</td>
                            <td>
                                <span class="cart_p"><b>'.$GLOBALS['RentMy']::currency($rent_my_cart_details['sub_total'], 'pre', 'rentmy-cart-sub_total', 'post').'</b></span>
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
                                <span class="cart_p">'.$GLOBALS['RentMy']::currency($rent_my_cart_details['total_discount'], 'pre', 'rentmy-cart-total_discount', 'post').'</span>
                            </td>
                        </tr>
                        <tr>
                            <td> Tax</td>
                            <td>
                                <span class="cart_p">'.$GLOBALS['RentMy']::currency($rent_my_cart_details['tax'], 'pre', 'rentmy-cart-tax', 'post').'</span>
                            </td>
                        </tr>
                        <tr>
                            <td> Delivery Tax</td>
                            <td>
                                <small class="cart_p"> Calculated in the next step</small>
                            </td>
                        </tr>
                        <tr>
                            <td> Deposit Amount</td>
                            <td>
                                <span class="cart_p">'.$GLOBALS['RentMy']::currency($rent_my_cart_details['deposit_amount'], 'pre', 'rentmy-cart-deposit_amount', 'post').'</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5>Total</h5></td>
                            <td>
                                <h5>
                                    <span class="cart_p">'.$GLOBALS['RentMy']::currency($rent_my_cart_details['total'], 'pre', 'rentmy-cart-total', 'post').'</span>
                                </h5></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </form>';
        else:
            $html .= '<span class="rentmy-errore-msg">No items found in cart</span>';
        endif;

        return $html;
    }
}
