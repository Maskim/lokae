<?php

class Lokae_Stylesheets 
{
    function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'lokae_enqueue_scripts'), 101);
    }

    public function lokae_enqueue_scripts() {
        wp_enqueue_style( 'style', get_stylesheet_uri() );
    }
}

new Lokae_Stylesheets();