<?php
if (!defined('ABSPATH')) exit;

include_once dirname(RENTMY_PLUGIN_FILE) .  DIRECTORY_SEPARATOR .'includes'.DIRECTORY_SEPARATOR.'class-rentmy.php';
include_once dirname(RENTMY_PLUGIN_FILE) . DIRECTORY_SEPARATOR. 'includes'.DIRECTORY_SEPARATOR.'class-rentmy-token.php';
include_once dirname(RENTMY_PLUGIN_FILE) . DIRECTORY_SEPARATOR. 'includes'.DIRECTORY_SEPARATOR.'class-rentmy-config.php';

if (!empty($_POST['rentmy_hidden']) && (current_user_can('administrator'))) {

    $rentMynonce = $_POST['rentmy_hidden'];
    unset($_SESSION['rentmy_config']);

    if (wp_verify_nonce($rentMynonce, 'rentMy-nonce')) {

        if ($_POST['update-configuration']) {
            (new RentMy_Config())->store_config();
        }
        else if ($_POST['update-settings']) {
            //print_r('test');
        }
        else {
            //Form data sent
            $rentmy_store_id = sanitize_text_field($_POST['rentmy_store_id']);
            $rentmy_api_key = sanitize_text_field($_POST['rentmy_api_key']);
            $rentmy_secret_key = sanitize_text_field($_POST['rentmy_secret_key']);
            update_option('rentmy_storeUid', $rentmy_store_id);
            update_option('rentmy_store_id', $rentmy_store_id);
            update_option('rentmy_apiKey', $rentmy_api_key);
            update_option('rentmy_secretKey', $rentmy_secret_key);
            (new RentMy_Token())->getToken();
            (new RentMy_Config())->store_config(); // if we are not using update settings. (currently it is commented out), then we have to use this. while plugin keys updates everytime. we will update the configs also.
        }

        $updated = true;
    }

} else {

    $rentmy_store_id = get_option('rentmy_storeUid');
    $rentmy_api_key = get_option('rentmy_apiKey');
    $rentmy_secret_key = get_option('rentmy_secretKey');
}

$nonce = wp_create_nonce('rentMy-nonce');
$get_pages = get_pages();
?>
<style>
    .update-nag, .updated, .error, .is-dismissible { display: none !important; }
    .card{max-width: 100%;}
</style>
<div class="wrap rentmy-admin-wrap">
    <div class="card rentmy-admin-header">
        <div class="rentmy-admin-pull-left">
            <img src="<?php echo esc_url(plugins_url('/assets/logo.png', __FILE__)); ?>" alt="RentMy" width="200"/>
        </div>
    </div>

    <form name="rentmy_form" method="post" action="<?php echo str_replace('%7E', '~', $_SERVER['REQUEST_URI']); ?>">
        <div class="card">
        <h2><?php _e("1. Connect your RentMy account"); ?></h2>
        <?php if(!empty($updated)):?>
        <div class="updated" style="display: block !important;">
            <p>
                <strong>
                    <?php _e('API keys and token saved successfully.'); ?>
                </strong>
            </p>
        </div>
        <?php endif;?>
        <p class="rentmy-admin-subtitle"><?php _e("Don't have a RentMy account? <a href=\"https://client.rentmy.co/auth/login\" target=\"_blank\" rel=\"noopener\">Get started for free</a>."); ?></p>
        <hr/>

        <input type="hidden" name="rentmy_hidden" value="<?php echo $nonce; ?>">
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="rentmy_store_id"><?php _e("Your RentMy Store UID"); ?></label>
                </th>
            </tr>
            <tr>
                <td>
                    <input required type="text" name="rentmy_store_id" class="regular-text"
                           value="<?php echo $rentmy_store_id; ?>"/>
<!--                        <p class="description">--><?php //_e("You can find your <b>Store ID</b> under <b>Settings >Widget Section</b> in your RentMy account."); ?><!--</p>-->
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="rentmy_api_key"><?php _e("Your RentMy API Key"); ?></label>
                </th>
            </tr>
            <tr>
                <td>
                    <input required type="text" name="rentmy_api_key" class="regular-text"
                           value="<?php echo $rentmy_api_key; ?>"/>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="rentmy_secret_key"><?php _e("Your RentMy Secret Key"); ?></label>
                </th>
            </tr>
            <tr>
                <td>
                    <input required type="text" name="rentmy_secret_key" class="regular-text"
                           value="<?php echo $rentmy_secret_key; ?>"/>
                </td>
            </tr>

            </tbody>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Update Options', 'rentmy_trdom') ?>"
                   class="button button-primary"/>

            <?php if(get_option('rentmy_accessToken') != ''){ ?>
<!--            <input type="submit" name="update-configuration" value="--><?php //_e('Update Configuration', 'rentmy_trdom') ?><!--"-->
<!--                   class="button button-primary"/>-->
            <?php } ?>
        </p>
    </div>
<!---->
<!--        <div class="card">-->
<!--        <h2>--><?php //_e("2. Settings"); ?><!--</h2>-->
<!--        <p class="bq-admin-subtitle">--><?php //_e("Settings that are associated with RentMy WordPress site."); ?><!--</p>-->
<!--        <hr/>-->
<!--        <table class="form-table">-->
<!--            <tbody>-->
<!--            <tr>-->
<!--                <td>-->
<!--                    Assign Product List Page-->
<!--                </td>-->
<!--                <td>-->
<!--                    <select name="rentmy_product_list_page" id="">-->
<!--                        --><?php //foreach($get_pages as $page) { ?>
<!--                            <option --><?php //echo get_option('rentmy_product_list_page') == $page->post_name ? 'selected' : ''; ?><!-- value="--><?php //echo $page->post_name;?><!--">--><?php //echo $page->post_title;?><!--</option>-->
<!--                        --><?php //} ?>
<!--                    </select>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>-->
<!--                    Assign Product Details Page-->
<!--                </td>-->
<!--                <td>-->
<!--                    <select name="rentmy_product_details_page" id="">-->
<!--                        --><?php //foreach($get_pages as $page) { ?>
<!--                            <option --><?php //echo get_option('rentmy_product_details_page') == $page->post_name ? 'selected' : ''; ?><!-- value="--><?php //echo $page->post_name;?><!--">--><?php //echo $page->post_title;?><!--</option>-->
<!--                        --><?php //} ?>
<!--                    </select>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>-->
<!--                    Assign Package Details Page-->
<!--                </td>-->
<!--                <td>-->
<!--                    <select name="rentmy_package_details_page" id="">-->
<!--                        --><?php //foreach($get_pages as $page) { ?>
<!--                            <option --><?php //echo get_option('rentmy_package_details_page') == $page->post_name ? 'selected' : ''; ?><!-- value="--><?php //echo $page->post_name;?><!--">--><?php //echo $page->post_title;?><!--</option>-->
<!--                        --><?php //} ?>
<!--                    </select>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>-->
<!--                    Assign Cart Page-->
<!--                </td>-->
<!--                <td>-->
<!--                    <select name="rentmy_cart_page" id="">-->
<!--                        --><?php //foreach($get_pages as $page) { ?>
<!--                            <option --><?php //echo get_option('rentmy_cart_page') == $page->post_name ? 'selected' : ''; ?><!-- value="--><?php //echo $page->post_name;?><!--">--><?php //echo $page->post_title;?><!--</option>-->
<!--                        --><?php //} ?>
<!--                    </select>-->
<!--                </td>-->
<!--            </tr>-->
<!--            <tr>-->
<!--                <td>-->
<!--                    Assign Checkout Page-->
<!--                </td>-->
<!--                <td>-->
<!--                    <select name="rentmy_checkout_page" id="">-->
<!--                        --><?php //foreach($get_pages as $page) { ?>
<!--                            <option --><?php //echo get_option('rentmy_checkout_page') == $page->post_name ? 'selected' : ''; ?><!-- value="--><?php //echo $page->post_name;?><!--">--><?php //echo $page->post_title;?><!--</option>-->
<!--                        --><?php //} ?>
<!--                    </select>-->
<!--                </td>-->
<!--            </tr>-->
<!--            </tbody>-->
<!--        </table>-->
<!--        <input type="submit" name="update-settings" value="--><?php //_e('Update Settings', 'rentmy_trdom') ?><!--"-->
<!--               class="button button-primary"/>-->
<!--    </div>-->

        <div class="card">
        <h2><?php _e("2. RentMy Useful Shortcodes"); ?></h2>
        <p class="bq-admin-subtitle"><?php _e("To get started, simply <b>copy</b> and <b>paste</b> this product list short code to any <b>Page</b> or <b>Post</b>."); ?></p>
        <hr/>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="embed_code"><?php _e("All Products"); ?></label>
                </th>
            </tr>
            <tr>
                <td>
                    <code>[rentmy-products-list]</code>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="embed_code"><?php _e("Category products"); ?></label>
                </th>
            </tr>
            <tr>
                <td>
                    <code>[rentmy-products-list type=category id=comma seperated category_id]</code>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="embed_code"><?php _e("Tagged products"); ?></label>
                </th>
            </tr>
            <tr>
                <td>
                    <code>[rentmy-products-list type=tag id=comma seperated tag ids]</code>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="embed_code"><?php _e("Sorted product list"); ?></label>
                </th>
            </tr>
            <tr>
                <td>
                    <code>[rentmy-products-list type=category id=category_id sort_type=ASC sort=buy_price]</code><br/>
                    <code>[rentmy-products-list type=category id=1815 sort_type=DESC sort=rent_price]</code><br/>
                    <code>[rentmy-products-list type=category id=1815 sort_type=DESC sort=created]</code><br/>
                    <code>[rentmy-products-list type=category id=1815 sort_type=ASC sort=name]</code><br/>
                </td>
            </tr>
            <tr>
                <td colspan="2"><u><small>For details call us at <a href="tel:+4087288556"> Phone: + (408) 728-8556</a> or email at <a href = "mailto: Hello.RentMy.co">Hello.RentMy.co</a></small></u></td>
            </tr>
            </tbody>
        </table>
    </div>
    </form>
</div>
