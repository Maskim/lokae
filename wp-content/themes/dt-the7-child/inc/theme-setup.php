<?php
/**
 * Theme setup.
 *
 * @package Lokae
 */

defined( 'ABSPATH' ) || exit;

    /**
	 * Theme setup.
	 *
	 * @since 1.0.0
	 */
	function lokae_setup() {
        /**
            * Load theme text domain.
        */
        load_child_theme_textdomain( 'lokae', LOKAE_THEME_DIR . '/languages' );
    }

    add_action( 'after_setup_theme', 'lokae_setup', 6 );