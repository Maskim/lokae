<?php

class Lokae_Javascript 
{
    function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'lokae_enqueue_scripts'), 101);
    }

    public function lokae_enqueue_scripts() {
        wp_enqueue_script( 'script', LOKAE_ASSETS_DIR . '/js/price.js' );
    }
}

new Lokae_Javascript();