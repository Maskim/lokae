<?php
/**
 * RentMy setup
 *
 * @package RentMy
 * @since   1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Main RentMy Class.
 *
 * @class RentMy
 */
class RentMy
{
    /**
     * RentMy version.
     *
     * @var string
     */
    public $version = '1.1.0';

    /**
     * The single instance of the class.
     *
     * @var RentMy
     * @since 2.1
     */
    protected static $_instance = null;

    /**
     * Session instance.
     *
     * @var RentMy_Session|RentMy_Session_Handler
     */
    public $session = null;


    /**
     * Product factory instance.
     *
     * @var RentMy_Product_Factory
     */
    public $product_factory = null;

    /**
     * Countries instance.
     *
     * @var RentMy_Countries
     */
    public $countries = null;


    /**
     * Cart instance.
     *
     * @var RentMy_Cart
     */
    public $cart = null;

    /**
     * Customer instance.
     *
     * @var RentMy_Customer
     */
    public $customer = null;

    /**
     * Order factory instance.
     *
     * @var RentMy_Order_Factory
     */
    public $order_factory = null;

    /**
     * RentMy api access Token
     * @var null
     */
    public static $accessToken = null;

    /**
     * Main RentMy Instance.
     *
     * Ensures only one instance of rentmy is loaded or can be loaded.
     *
     * @return RentMy - Main instance.
     * @see RentMy()
     * @since 1.0.0
     * @static
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * RentMy Constructor.
     */
    public function __construct()
    {
        $this->define_constants();
        $this->includes();
        $this->getConfig();

        if (is_admin()) {
            //register plugin activation and deactivation hooks
            register_activation_hook(RENTMY_PLUGIN_FILE, array($this, 'rentmy_activate'));
            register_deactivation_hook(RENTMY_PLUGIN_FILE, array($this, 'rentmy_deactivate'));
        }

        // other basic hooks
        // for template choose and other stuffs

        if (!is_admin()) {
            add_action('admin_bar_menu', array($this, 'rentmy_admin_bar_menu_add'), 50);
            add_filter('body_class', array($this, 'rentmy_body_classes'));
            // add js and css stuffs here
            add_action('wp_enqueue_scripts', array($this, 'add_rentmy_scripts'));

            // add search bar of rentmy products to the primary menu
//            add_filter('wp_nav_menu_items', array($this, 'rentmy_add_search_box_to_menu'), 10, 2);
        }

        // ajax calls initialiazed
        new RentMy_Ajax();

        // add widgets hook here
        add_action('widgets_init', array($this, 'rentmy_default_widget_registers'));

        // add initial html on this. like loader pre values etc.
        add_action('wp_body_open', array($this, 'rentmy_content_after_body_open'));


    }

    /**
     * Define WC Constants.
     */
    private function define_constants()
    {
        $upload_dir = wp_upload_dir(null, false);

        $this->define('RENTMY_ABSPATH', dirname(RENTMY_PLUGIN_FILE) . DIRECTORY_SEPARATOR);
        $this->define('RENTMY_PLUGIN_BASENAME', plugin_basename(RENTMY_PLUGIN_FILE));
        $this->define('RENTMY_VERSION', $this->version);
        $this->define('RENTMY_LOG_DIR', $upload_dir['basedir'] . '/rentmy-logs/');
        $this->define('RENTMY_TEMPLATE_DEBUG_MODE', false);
        $this->define('RENTMY_NOTICE_MIN_PHP_VERSION', '5.6.20');
        $this->define('RENTMY_NOTICE_MIN_WP_VERSION', '4.9');

        $this->define("RENTMY_S3_URL", "https://s3.us-east-2.amazonaws.com/images.rentmy.co");
//        $this->define("RENTMY_BASE_URL", "http://client-api-stage.rentmy.leaperdev.rocks");
//        $this->define("RENTMY_API_URL", "http://client-api-stage.rentmy.leaperdev.rocks/api");
        $this->define("RENTMY_BASE_URL", "https://clientapi.rentmy.co");
        $this->define("RENTMY_API_URL", "https://clientapi.rentmy.co/api");
//        $this->define("RENTMY_BASE_URL", "http://192.168.88.18/rentmy.git");
//        $this->define("RENTMY_API_URL", "http://192.168.88.18/rentmy.git/api");
        $this->define("RENTMY_PLACEHOLDER_IMAGE", plugin_dir_url(plugin_dir_path(__FILE__) . '../' . DIRECTORY_SEPARATOR . 'assets/images/rent-my-sample.png') . 'rent-my-sample.png');

    }

    function includes()
    {
        // add classes one by one
        if (!class_exists('RentMy_Category', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-categories.php';
        }

        if (!class_exists('RentMy_Config', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-config.php';
        }

        if (!class_exists('RentMy_Products', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-products.php';
        }

        if (!class_exists('RentMy_Cart', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-cart.php';
        }

        if (!class_exists('RentMy_Checkout', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-checkout.php';
        }

        if (!class_exists('RentMy_Order', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-order.php';
        }

        if (!class_exists('RentMy_Category_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-category-widgets.php';
        }

        if (!class_exists('RentMy_Tags_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-tags-widgets.php';
        }

        if (!class_exists('RentMy_Search_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-search-widgets.php';
        }

        if (!class_exists('RentMy_OrderSummary_Widget', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-order-summary-widgets.php';
        }

        if (!class_exists('RentMy_Ajax', false)) {
            include_once plugin_dir_path(__FILE__) . '../' . 'includes/class-rentmy-ajax.php';
        }


        if (!is_admin()) {
            // add shortcodes and other files
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-categories-list.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-tags-list.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-search.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-mini-cart.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-order-summary.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-products-list.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-product-details.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-package-details.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-cart-details.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout-step1.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout-step2.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout-step3.php';
            include_once plugin_dir_path(__FILE__) . '../' . 'short-codes/rentmy-checkout-step4.php';
        }
    }


    /**
     * Define constant if not already set.
     *
     * @param string $name Constant name.
     * @param string|bool $value Constant value.
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    //rent my generic api calls base function
    public function fetch($slashedPath = null, $token = null, $postFields = [], $queryParams = [])
    {
        // Create a new cURL resource
        $curl = curl_init();
        $post_fields_string = null;
        $get_fields_string = null;
        $html = null;
        $error = null;

        if (!$curl) {
            $error = "Couldn't initialize a cURL handle";
            return $error;
        }

        if (!$slashedPath) {
            $error = "API PATH is not specified properly";
            return $error;
        }

        //url-ify the data for the POST
        if (!empty($postFields)) {
            foreach ($postFields as $key => $value) {
                //$post_fields_string .= $key . '=' . $value . '&';
            }
            $post_fields_string = urldecode(http_build_query($postFields));
            rtrim($post_fields_string, '&');
            curl_setopt($curl, CURLOPT_POST, count($postFields));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields_string);
        }
        //url-ify the data for the GET
        if (!empty($queryParams)) {
            foreach ($queryParams as $key => $value) {
                $get_fields_string .= $key . '=' . $value . '&';
            }
            rtrim($get_fields_string, '&');
            $get_fields_string = '?' . $get_fields_string;
        }

        if (!empty($token)) {
            $headers_array = [
                'Accept: application/json, text/plain, */*',
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
            ];
            if (is_array($token)) {
                if (!empty($token['token'])) {
                    $authorization = "Authorization: Bearer " . $token['token'];
                    array_push($headers_array, $authorization);
                }
                if (!empty($token['location'])) {
                    $location = "Location: " . $token['location'];
                    array_push($headers_array, $location);
                }
            } else {
                $authorization = "Authorization: Bearer " . $token;
                array_push($headers_array, $authorization);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_array);
        }

        $api_url = RENTMY_API_URL . $slashedPath . $get_fields_string;
        // Set the file URL to fetch through cURL
        curl_setopt($curl, CURLOPT_URL, $api_url);
        // Follow redirects, if any
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        // Fail the cURL request if response code = 400 (like 404 errors)
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        // Return the actual result of the curl result instead of success code
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Wait for 10 seconds to connect, set 0 to wait indefinitely
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // Execute the cURL request for a maximum of 50 seconds
        curl_setopt($curl, CURLOPT_TIMEOUT, 50);
        // Do not check the SSL certificates
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        // Fetch the URL and save the content in $html variable
        $html = curl_exec($curl);
        // Check if any error has occurred
        if (curl_errno($curl)) {
            $error = 'cURL error: ' . curl_error($curl);
        } else {
            // cURL executed successfully
            $error = curl_getinfo($curl);
        }
        // close cURL resource to free up system resources
        curl_close($curl);
        return !empty($html) ? json_decode($html, true)['result'] : $error;
    }

//rent my generic api calls base function
    public function rentmy_fetch($slashedPath = null, $token = null, $postFields = [], $queryParams = [])
    {
        // Create a new cURL resource
        $curl = curl_init();
        $post_fields_string = null;
        $get_fields_string = null;
        $html = null;
        $error = null;

        if (!$curl) {
            $error = "Couldn't initialize a cURL handle";
            return $error;
        }

        if (!$slashedPath) {
            $error = "API PATH is not specified properly";
            return $error;
        }

        //url-ify the data for the POST
        if (!empty($postFields)) {
            foreach ($postFields as $key => $value) {
                //$post_fields_string .= $key . '=' . $value . '&';
            }
            $post_fields_string = urldecode(http_build_query($postFields));
            rtrim($post_fields_string, '&');
            curl_setopt($curl, CURLOPT_POST, count($postFields));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields_string);
        }
        //url-ify the data for the GET
        if (!empty($queryParams)) {
            foreach ($queryParams as $key => $value) {
                $get_fields_string .= $key . '=' . $value . '&';
            }
            rtrim($get_fields_string, '&');
            $get_fields_string = '?' . $get_fields_string;
        }

        if (!empty($token)) {
            $headers_array = [
                'Accept: application/json, text/plain, */*',
                'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36'
            ];
            if (is_array($token)) {
                if (!empty($token['token'])) {
                    $authorization = "Authorization: Bearer " . $token['token'];
                    array_push($headers_array, $authorization);
                }
                if (!empty($token['location'])) {
                    $location = "Location: " . $token['location'];
                    array_push($headers_array, $location);
                }
            } else {
                $authorization = "Authorization: Bearer " . $token;
                array_push($headers_array, $authorization);
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers_array);
        }

        $api_url = RENTMY_API_URL . $slashedPath . $get_fields_string;
        // Set the file URL to fetch through cURL
        curl_setopt($curl, CURLOPT_URL, $api_url);
        // Follow redirects, if any
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        // Fail the cURL request if response code = 400 (like 404 errors)
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        // Return the actual result of the curl result instead of success code
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        // Wait for 10 seconds to connect, set 0 to wait indefinitely
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        // Execute the cURL request for a maximum of 50 seconds
        curl_setopt($curl, CURLOPT_TIMEOUT, 50);
        // Do not check the SSL certificates
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        // Fetch the URL and save the content in $html variable
        $html = curl_exec($curl);
        // Check if any error has occurred
        if (curl_errno($curl)) {
            $error = 'cURL error: ' . curl_error($curl);
        } else {
            // cURL executed successfully
            $error = curl_getinfo($curl);
        }
        // close cURL resource to free up system resources
        curl_close($curl);
        return !empty($html) ? json_decode($html, true) : $error;
    }

    function rentmy_activate()
    {
        $pages_to_create = [
            (object)[
                'page_title' => 'RentMy Product Details',
                'page_content' => '[rentmy-products-details]'
            ],
            (object)[
                'page_title' => 'RentMy Package Details',
                'page_content' => '[rentmy-package-details]'
            ],
            (object)[
                'page_title' => 'RentMy Products List',
                'page_content' => '[rentmy-products-list]'
            ],
            (object)[
                'page_title' => 'RentMy Cart',
                'page_content' => '[rentmy-cart-details]'
            ],
            (object)[
                'page_title' => 'RentMy Checkout',
                'page_content' => '[rentmy-checkout]'
            ]
        ];
        foreach ($pages_to_create as $keys => $pages) {
            $the_page = get_page_by_title($pages->page_title);
            if (empty($the_page)) {
                $_p = array();
                $_p['post_title'] = $pages->page_title;
                $_p['post_content'] = $pages->page_content;
                $_p['post_status'] = 'publish';
                $_p['post_type'] = 'page';
                $_p['comment_status'] = 'closed';
                $_p['ping_status'] = 'closed';
                wp_insert_post($_p);
            } else {
                //make sure the page is not trashed...
                $the_page->post_status = 'publish';
                $the_page->post_content = $pages->page_content;
                wp_update_post($the_page);
            }
        }

        // assign the created page values
        update_option('rentmy_product_list_page', 'rentmy-products-list');
        update_option('rentmy_product_details_page', 'rentmy-product-details');
        update_option('rentmy_package_details_page', 'rentmy-package-details');
        update_option('rentmy_cart_page', 'rentmy-cart');
        update_option('rentmy_checkout_page', 'rentmy-checkout');
    }

    function rentmy_deactivate()
    {
        add_action('admin_bar_menu', function ($wp_admin_bar) {
            $wp_admin_bar->remove_node('rent-my-products-list');
        }, 55);
        return;
    }

    function rentmy_admin_bar_menu_add($wp_admin_bar)
    {
        $params = array(
            'id' => 'rent-my-products-list',
            'title' => 'Rent My API Product List',
            'href' => home_url('rentmy-products-list'),
            'meta' => array(
                'class' => 'custom-node-class'
            )
        );
        $wp_admin_bar->add_node($params);
    }

    function rentmy_body_classes($classes)
    {
        $classes[] = 'has-sidebar rentmy-page';
        return $classes;
    }

    function rentmy_default_widget_registers()
    {
        // Register our own widget.
        register_widget('RentMy_Category_Widget');
        register_widget('RentMy_Tags_Widget');
        register_widget('RentMy_Search_Widget');
        register_widget('RentMy_OrderSummary_Widget');
    }

    function rentmy_content_after_body_open() {
        $rentmy_config = get_option('rentmy_config');
        ?>
        <input id="rentmy_base_file_url" type="hidden" readonly value="<?php echo RENTMY_S3_URL . '/'; ?>">
        <input id="rentmy_home_url" type="hidden" readonly value="<?php echo home_url(); ?>">
        <input id="rentmy_cart_url" type="hidden" readonly value="<?php echo home_url('/rentmy-cart'); ?>">
        <script>
            var rentmy_config_data_preloaded = <?php echo json_encode($rentmy_config); ?>;
        </script>
        <?php
    }

    function rentmy_add_search_box_to_menu($items, $params)
    {
        if ($params->theme_location == 'primary') {
            $search_value = !empty($_GET['search']) ? $_GET['search'] : null;
            return $items . "
            <li><a href=" . home_url('rentmy-products-list') . ">Product List</a></li>
            <li><a href=" . home_url('rentmy-cart') . ">Cart</a></li>
            <li><a href=" . home_url('/rentmy-checkout?step=info') . ">Checkout</a></li>
            <li class='rentmy-menu-header-search'>
                <form action='" . home_url('rentmy-products-list') . "' method='get' class='rentmy-search-form' accept-charset='ISO-8859-1'>
                    <input type='text' name='search' placeholder='Search' value='" . $search_value . "'>
                </form>
            </li>";
        }
        return $items;
    }

    //render image link along from the product itself
    public static function imageLink($product_id, $image, $type = 'list')
    {
        if ($type == 'list') {
            if (empty($image)) {
                return esc_url(plugins_url('../assets/images/product-image-placeholder.jpg', __FILE__));
            } else {
                return RENTMY_S3_URL . '/products/' . get_option('rentmy_storeId') . '/' . $product_id . '/' . $image;
            }
        } elseif ($type == 'small') {
            if (empty($image)) {
                return esc_url(plugins_url('../assets/images/product-image-placeholder.jpg', __FILE__));
            } else {
                return RENTMY_S3_URL . '/products/' . get_option('rentmy_storeId') . '/' . $product_id . '/' . $image;
            }
        }
    }

    function add_rentmy_scripts()
    {

        wp_enqueue_style('rentmy-styles', plugins_url('assets/css/rentmy-styles.css', RENTMY_PLUGIN_FILE));
        wp_enqueue_style('rentmy-font-awesome-icons', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
        wp_enqueue_style('rentmy-bootstrap', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
        wp_enqueue_style('rentmy-vendor-styles', plugins_url('assets/css/vendor.css', RENTMY_PLUGIN_FILE));

        wp_enqueue_script('rentmy-vendor-js', plugins_url('assets/js/vendor.js', RENTMY_PLUGIN_FILE), array('jquery'));
        wp_enqueue_script('rentmy-cookie-manager-js', plugins_url('assets/js/rentmy-cookieJar.js', RENTMY_PLUGIN_FILE));
        wp_enqueue_script('algolia-js', 'https://cdn.jsdelivr.net/npm/places.js@1.16.6');
        wp_enqueue_script('checkout-js', plugins_url('assets/js/checkout.js', RENTMY_PLUGIN_FILE), array('jquery'));
        wp_enqueue_script('rentmy-products-js', plugins_url('assets/js/products.js', RENTMY_PLUGIN_FILE), array('jquery'));
       // wp_enqueue_script('stripe-js', 'https://js.stripe.com/v2/');
        wp_enqueue_script('stripe-js-v3', 'https://js.stripe.com/v3/');

        wp_localize_script('checkout-js', 'rentmy_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));

        wp_localize_script('rentmy-products-js', 'rentmy_ajax_object', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
        ));

    }

    /**
     * Load configs on load
     */
    public function getConfig()
    {
        if (!is_admin()) {
            $this->storeConfig();
        }
    }

    public static function pr($var)
    {
        print_r("<pre>");
        print_r($var);
        print_r("</pre>");
    }

    /**
     * @param int $amount
     * @param string $pre_class
     * @param string $amount_class
     * @param string $post_class
     * @return string
     */
    public static function currency($amount = 0, $pre_class = 'pre', $amount_class = 'amount', $post_class = 'post')
    {
        $config = $_SESSION['rentmy_config'];
        $currency = !empty($config['currency_format']) ? $config['currency_format'] : '';
        if (empty($amount)) {
            $amount = 0;
        }
        $amount = number_format($amount, 2);
        if (!empty($currency)) {
            $html = '';
            if ($currency['pre']) {
                $html .= "<span class='" . $pre_class . "'>" . $currency['symbol'] . "</span>";
            }
            $html .= "<span class='" . $amount_class . "'>" . $amount . "</span>";
            if ($currency['post']) {
                $html .= "<span class='" . $post_class . "'>" . $currency['code'] . " </span>";
            }
        } else {
            $html = "<span class='" . $pre_class . "'>$</span><span class='" . $amount_class . "'>" . $amount . "</span><span class='" . $post_class . "'> USD</span>";
        }
        return $html;
    }

    /** Get store Config */
    public function storeConfig()
    {
        try {
            if (!session_id()) {
                session_start();
            }
            if (empty($_SESSION['rentmy_config'])) {
                $_SESSION['rentmy_config'] = get_option('rentmy_config');
            }

        } catch (Exception $e) {

        }
    }

    /**
     * Pagination
     * @param int $page
     * @param $total
     * @param int $limit
     * @param int $adjacents
     * @param $targetpage
     * @return string
     */
    public static function paginate($page=1, $total, $limit =12, $adjacents=3, $targetpage)
    {
        $lastpage = ceil($total / $limit);
        $prev = $page - 1;                            //previous page is page - 1
        $next = $page + 1;
        $lpm1 = $lastpage - 1;
        $pagination = "";

        if ($lastpage > 1) {
            $pagination .= "<div class=\"rentmy-pagination\">";
            //previous button
            if ($page > 1)
                $pagination .= "<a href=\"$targetpage&page_no=$prev\">Previous</a>";
            else
                $pagination .= "<span class=\"disabled\">Previous</span>";

            //pages
            if ($lastpage < 7 + ($adjacents * 2))    //not enough pages to bother breaking it up
            {
                for ($counter = 1; $counter <= $lastpage; $counter++) {
                    if ($counter == $page)
                        $pagination .= "<span class=\"current\">$counter</span>";
                    else
                        $pagination .= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";
                }
            } elseif ($lastpage > 5 + ($adjacents * 2))    //enough pages to hide some
            {
                //close to beginning; only hide later pages
                if ($page < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        if ($counter == $page)
                            $pagination .= "<span class=\"current\">$counter</span>";
                        else
                            $pagination .= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";
                    }
                    $pagination .= "...";
                    $pagination .= "<a href=\"$targetpage&page_no=$lpm1\">$lpm1</a>";
                    $pagination .= "<a href=\"$targetpage&page_no=$lastpage\">$lastpage</a>";
                } //in middle; hide some front and some back
                elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                    $pagination .= "<a href=\"$targetpage&page_no=1\">1</a>";
                    $pagination .= "<a href=\"$targetpage&page_no=2\">2</a>";
                    $pagination .= "...";
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                        if ($counter == $page)
                            $pagination .= "<span class=\"current\">$counter</span>";
                        else
                            $pagination .= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";
                    }
                    $pagination .= "...";
                    $pagination .= "<a href=\"$targetpage&page_no=$lpm1\">$lpm1</a>";
                    $pagination .= "<a href=\"$targetpage&page_no=$lastpage\">$lastpage</a>";
                } //close to end; only hide early pages
                else {
                    $pagination .= "<a href=\"$targetpage&page_no=1\">1</a>";
                    $pagination .= "<a href=\"$targetpage&page_no=2\">2</a>";
                    $pagination .= "...";
                    for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                        if ($counter == $page)
                            $pagination .= "<span class=\"current\">$counter</span>";
                        else
                            $pagination .= "<a href=\"$targetpage&page_no=$counter\">$counter</a>";
                    }
                }
            }

            //next button
            if ($page < $counter - 1)
                $pagination .= "<a href=\"$targetpage&page_no=$next\">next</a>";
            else
                $pagination .= "<span class=\"disabled\">next</span>";
            $pagination .= "</div>\n";
        }
        return $pagination;
    }
}
