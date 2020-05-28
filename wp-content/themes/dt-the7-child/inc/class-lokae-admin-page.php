<?php

if (!defined('ABSPATH')) exit;

/**
 * 
 */
class Lokae_Admin_Page 
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'lokae_admin_menu'));
    }

    /**
     * lokae_admin_menu
     *
     * @version 1.0.0
     */
    public function lokae_admin_menu()
    {
        add_menu_page(
            $page_title = esc_html__('Lokae', 'lokae'), 
            $menu_title = esc_html__('Lokae', 'lokae'), 
            $capability = 'administrator', 
            $menu_slug = 'lokae_admin', 
            $function = array($this, 'lokae_admin_main_menu_options'), 
            $icon_url = 'dashicons-admin-generic', 
            21
        );
    }

    public function lokae_admin_main_menu_options() {
        if (!current_user_can('administrator')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'lokae'));
        }

        include_once LOKAE_TEMPLATE_DIR . '/admin/template-settings.php';
    }
}

new Lokae_Admin_Page();