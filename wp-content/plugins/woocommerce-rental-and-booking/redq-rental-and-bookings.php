<?php

/**
 * Plugin Name: WooCommerce Rental & Booking System
 * Plugin URI: https://codecanyon.net/item/rnb-woocommerce-rental-booking-system/14835145?ref = redqteam
 * Description: RnB – WooCommerce Rental & Booking is a user friendly woocommerce booking plugin built as woocommerce extension. This powerful woocommerce plugin allows you to sell your time or date based bookings. It creates a new product type to your WooCommerce site. Perfect for those wanting to offer rental , booking , or real estate agencies or services.
 * Version: 10.0.1
 * Author: RedQ Team
 * Author URI: http://redqteam.com
 * License: http : //www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: redq-rental
 * Domain Path: /languages
 * WC requires at least: 3.0.0
 * WC tested up to: latest
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * RedQ_Rental_And_Bookings
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    class RedQ_Rental_And_Bookings
    {
        /**
         * Plugin data from get_plugins()
         *
         * @since 1.0
         * @var object
         */
        public $plugin_data;

        /**
         * Includes to load
         *
         * @since 1.0
         * @var array
         */
        public $includes;

        /**
         * Plugin Action and Filter Hooks
         *
         * @return null
         * @since 1.0.0
         */
        public function __construct()
        {
            register_activation_hook(__FILE__, array(&$this, 'rnb_init_custom_db_table'));
            add_action('after_setup_theme', array($this, 'rnb_include_template_functions'), 11);
            add_action('plugins_loaded', array($this, 'redq_rental_set_plugins_data'), 1);
            add_action('plugins_loaded', array($this, 'redq_rental_define_constants'), 1);
            add_action('plugins_loaded', array($this, 'redq_rental_set_includes'), 1);
            add_action('plugins_loaded', array($this, 'redq_rental_load_includes'), 1);
            add_action('woocommerce_redq_rental_add_to_cart', array($this, 'redq_add_to_cart'), 30);
            add_filter('woocommerce_integrations', array($this, 'redq_rental_include_integration'));
            add_filter('woocommerce_get_settings_pages', array($this, 'redq_rental_get_settings_pages'));
            add_action('plugins_loaded', array($this, 'redq_support_multilanguages'));

            $quote_menu = get_option('rnb_enable_rft_endpoint', 'yes');

            if ($quote_menu == 'yes') {
                add_action('init', array($this, 'request_quote_endpoints'));
            }
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'redq_more_plugins_links'), 1);
        }

        /**
         * rnb_init_custom_db_table
         *
         * @return void
         */
        public function rnb_init_custom_db_table()
        {
            global $wpdb;

            $wpdb->hide_errors();

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';

            $collate = '';

            if ($wpdb->has_cap('collation')) {
                $collate = $wpdb->get_charset_collate();
            }

            $schema = "CREATE TABLE {$wpdb->prefix}rnb_availability (
                id BIGINT UNSIGNED NOT NULL auto_increment,
                pickup_datetime timestamp NULL,
                return_datetime timestamp NULL,
                rental_duration varchar(200) NULL,
                product_id BIGINT UNSIGNED NULL,
                inventory_id BIGINT UNSIGNED NULL,
                order_id BIGINT UNSIGNED NULL,
                item_id BIGINT UNSIGNED NULL,
                lang varchar(200) NOT NULL DEFAULT 'en',
				created_at timestamp NOT NULL DEFAULT 0,
                updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                block_by ENUM('FRONTEND_ORDER', 'BACKEND_ORDER', 'CUSTOM') NOT NULL DEFAULT 'CUSTOM',
                delete_status boolean DEFAULT 0 NOT NULL,
                PRIMARY KEY (id)
						) $collate;
				CREATE TABLE IF NOT EXISTS {$wpdb->prefix}rnb_inventory_product (
                    inventory BIGINT UNSIGNED NOT NULL,
                    product BIGINT UNSIGNED NOT NULL,
					KEY inventory (inventory),
					KEY product (product)) $collate;";

            dbDelta($schema);
        }

        /**
         * redq_more_plugins_links
         *
         * @param array $links
         *
         * @return array
         */
        public function redq_more_plugins_links($links)
        {
            $links[] = '<a href="https://redq.gitbooks.io/woocommerce-rental-and-bookings-reloaded/content/" target="_blank">' . __('Docs', 'redq-rental') . '</a>';
            $links[] = '<a href="https://redqsupport.ticksy.com/" target="_blank">' . __('Support', 'redq-rental') . '</a>';
            $links[] = '<a href="https://codecanyon.net/user/redqteam/portfolio?ref=redqteam" target="_blank">' . __('Portfolio', 'redq-rental') . '</a>';
            return $links;
        }


        public function redq_rental_set_plugins_data()
        {
            if (!function_exists('get_plugins')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $plugin_dir = plugin_basename(dirname(__FILE__));
            $plugin_data = current(get_plugins('/' . $plugin_dir));
            $this->plugin_data = apply_filters('redq_plugin_data', $plugin_data);
        }


        /**
         * Plugin constant define
         *
         * @return null
         * @since 1.0.0
         */
        public function redq_rental_define_constants()
        {
            define('REDQ_RENTAL_VERSION', $this->plugin_data['Version']);                    // plugin version
            define('REDQ_RENTAL_FILE', __FILE__);                                            // plugin's main file path
            define('REDQ_RENTAL_DIR', dirname(plugin_basename(REDQ_RENTAL_FILE)));            // plugin's directory
            define('REDQ_RENTAL_PATH', untrailingslashit(plugin_dir_path(REDQ_RENTAL_FILE)));    // plugin's directory path
            define('REDQ_RENTAL_URL', untrailingslashit(plugin_dir_url(REDQ_RENTAL_FILE)));    // plugin's directory URL

            define('REDQ_RENTAL_INC_DIR', 'includes');    // includes directory
            define('REDQ_RENTAL_ASSETS_DIR', 'assets');        // assets directory
            define('REDQ_RENTAL_LANG_DIR', 'languages');    // languages directory
            define('REDQ_ROOT_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
            define('REDQ_PACKAGE_TEMPLATE_PATH', untrailingslashit(plugin_dir_path(__FILE__)) . '/templates/');
        }


        /**
         * Plugin includes files
         *
         * @return null
         * @since 1.0.0
         */
        public function redq_rental_set_includes()
        {
            $this->includes = apply_filters('redq_rental', array(
                'admin' => array(
                    REDQ_RENTAL_INC_DIR . '/admin/class-redq-rental-meta-boxes.php',
                    REDQ_RENTAL_INC_DIR . '/admin/class-redq-rental-admin-page.php',
                    REDQ_RENTAL_INC_DIR . '/integrations/class-full-calendar-integration.php',
                ),
                'frontends' => array(
                    REDQ_RENTAL_INC_DIR . '/class-redq-product-redq_rental.php',
                    REDQ_RENTAL_INC_DIR . '/class-redq-product-cart.php',
                    REDQ_RENTAL_INC_DIR . '/class-redq-product-tabs.php',
                )
            ));
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/class-redq-plugin-color-control.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/admin/class-redq-rental-post-types.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/admin/class-rnb-term-meta-text.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/admin/class-rnb-term-meta-icon.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/admin/class-rnb-term-meta-image.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/admin/class-rnb-term-meta-select.php';

            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/admin/class-save-meta.php';

            include_once('includes/rnb-data-provider.php');
            include_once('includes/rnb-arrange-data.php');
            include_once('includes/rnb-template-hooks.php');
            include_once('includes/rnb-core-functions.php');
            include_once('includes/class-redq-rental-orders.php');

            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/redq-quote-functions.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/class-redq-request-for-a-quote.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/class-redq-email.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/class-redq-enqueue-files.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/redq-rental-global-functions.php';
            require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/integrations/class-google-calendar-integration.php';


            if (in_array('reactive/reactive.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                require_once trailingslashit(REDQ_RENTAL_PATH) . REDQ_RENTAL_INC_DIR . '/class-reactive-support.php';
            }
        }


        /**
         * Function used to Init WooCommerce Template Functions - This makes them pluggable by plugins and themes.
         */
        public function rnb_include_template_functions()
        {
            include_once('includes/rnb-template-functions.php');
        }


        /**
         * Plugin includes files
         *
         * @return null
         * @since 1.0.0
         */
        public function redq_rental_load_includes()
        {
            $includes = $this->includes;

            foreach ($includes as $condition => $files) {
                $do_includes = false;
                switch ($condition) {
                    case 'admin':
                        if (is_admin()) {
                            $do_includes = true;
                        }
                        break;
                    case 'frontend':
                        if (!is_admin()) {
                            $do_includes = true;
                        }
                        break;
                    default:
                        $do_includes = true;
                        break;
                }

                if ($do_includes) {
                    foreach ($files as $file) {
                        require_once trailingslashit(REDQ_RENTAL_PATH) . $file;
                    }
                }
            }
        }


        /**
         * Manage all Block Times
         *
         * @return object
         * @since 1.0.0
         */
        public function manage_all_times($date, $start_time, $end_time, $step = 5)
        {
            $times = array();
            $block_times = array();

            $start_exp = explode(':', $start_time);
            $end_exp = explode(':', $end_time);

            $start_hour = $start_exp[0];
            $start_min = $start_exp[1];
            $end_hour = $end_exp[0];
            $end_min = $end_exp[1];


            for ($hour = $start_hour; $hour <= $end_hour; $hour++) {
                for ($minute = $start_min; $minute <= 60; $minute = $minute + 5) {
                    $com = $hour . ':' . $minute;
                    array_push($times, $com);
                }
            }

            $block_times[$date] = $times;

            return $block_times;
        }


        /**
         * Add to cart page show in fornt-end
         *
         * @return null
         * @since 1.0.0
         */
        public function redq_add_to_cart()
        {
            wc_get_template('single-product/add-to-cart/redq_rental.php', $args = array(), $template_path = '', REDQ_PACKAGE_TEMPLATE_PATH);
        }


        /**
         * Support languages for inventory
         *
         * @return null
         * @since 1.0.0
         */
        public function redq_support_multilanguages()
        {
            load_plugin_textdomain('redq-rental', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        /**
         * RFQ Endpoint
         *
         * @return null
         * @since 3.0.0
         */
        public static function request_quote_endpoints()
        {
            add_rewrite_endpoint('request-quote', EP_ROOT | EP_PAGES);
            add_rewrite_endpoint('view-quote', EP_ALL);
        }

        /**
         * RFQ Status
         *
         * @return null
         * @since 3.0.0
         */
        public static function register_post_status()
        {
            $quote_statuses = apply_filters(
                'redq_register_request_quote_post_statuses',
                array(
                    'quote-pending' => array(
                        'label' => _x('Pending', 'Quote status', 'redq-rental'),
                        'public' => false,
                        'protected' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        'label_count' => _n_noop('Pending <span class="count">(%s)</span>', 'Pending <span class="count">(%s)</span>', 'redq-rental')
                    ),
                    'quote-processing' => array(
                        'label' => _x('Processing', 'Quote status', 'redq-rental'),
                        'public' => false,
                        'protected' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        'label_count' => _n_noop('Processing <span class="count">(%s)</span>', 'Processing <span class="count">(%s)</span>', 'redq-rental')
                    ),
                    'quote-on-hold' => array(
                        'label' => _x('On Hold', 'Quote status', 'redq-rental'),
                        'public' => false,
                        'protected' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        'label_count' => _n_noop('On Hold <span class="count">(%s)</span>', 'On Hold <span class="count">(%s)</span>', 'redq-rental')
                    ),
                    'quote-accepted' => array(
                        'label' => _x('Accepted', 'Quote status', 'redq-rental'),
                        'public' => false,
                        'protected' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        'label_count' => _n_noop('Accepted <span class="count">(%s)</span>', 'Accepted <span class="count">(%s)</span>', 'redq-rental')
                    ),
                    'quote-completed' => array(
                        'label' => _x('Completed', 'Quote status', 'redq-rental'),
                        'public' => false,
                        'protected' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        'label_count' => _n_noop('Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'redq-rental')
                    ),
                    'quote-cancelled' => array(
                        'label' => _x('Cancelled', 'Quote status', 'redq-rental'),
                        'public' => false,
                        'protected' => true,
                        'exclude_from_search' => false,
                        'show_in_admin_all_list' => true,
                        'show_in_admin_status_list' => true,
                        'label_count' => _n_noop('Cancelled <span class="count">(%s)</span>', 'Cancelled <span class="count">(%s)</span>', 'redq-rental')
                    ),
                )
            );

            foreach ($quote_statuses as $quote_status => $values) {
                register_post_status($quote_status, $values);
            }
        }

        /**
         * Add integrations
         * This add the Google Calendar integration
         */
        public function redq_rental_include_integration($integrations)
        {
            $integrations[] = 'Redq_Rental_Google_Calendar_Integration';

            return $integrations;
        }

        /**
         * Add integrations
         * This add the Google Calendar integration
         */
        public function redq_rental_get_settings_pages($settings)
        {
            $settings[] = include('includes/admin/class-redq-rental-settings-rnb.php');
            return $settings;
        }
    }

    new RedQ_Rental_And_Bookings();

    register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
    register_activation_hook(__FILE__, 'redq_rental_flush_rewrites');

    function redq_rental_flush_rewrites()
    {
        RedQ_Rental_And_Bookings::request_quote_endpoints();
        RedQ_Rental_And_Bookings::register_post_status();
        flush_rewrite_rules();
    }
} else {
    function redq_admin_notice()
    {
?>
        <div class="error">
            <p><?php _e('Please Install WooCommerce First before activating this Plugin. You can download WooCommerce from <a href="http://wordpress.org/plugins/woocommerce/">here</a>.', 'redq-rental'); ?></p>
        </div>
<?php
    }

    add_action('admin_notices', 'redq_admin_notice');
}
