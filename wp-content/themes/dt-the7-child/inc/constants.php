<?php
/**
 * Constants.
 *
 * @package Lokae
 */

defined( 'ABSPATH' ) || exit;

if ( !defined( 'LOKAE_THEME_DIR' )) {
    define( 'LOKAE_THEME_DIR', get_theme_file_path() );
}

if ( ! defined( 'LOKAE_CORE_DIR' ) ) {
	define( 'LOKAE_CORE_DIR', trailingslashit( LOKAE_THEME_DIR ) . basename( dirname( __FILE__ ) ) );
}

if ( ! defined( 'LOKAE_TEMPLATE_DIR' ) ) {
	define( 'LOKAE_TEMPLATE_DIR', trailingslashit( LOKAE_THEME_DIR ) . '/templates' );
}
