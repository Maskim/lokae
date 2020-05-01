<?php
/**
 * Super simple widget.
 */
class RentMy_Search_Widget extends WP_Widget
{
    public function __construct()
    {                      // id_base        ,  visible name
        parent::__construct( 'rentmy_search_widget', 'RentMy Search' );
    }

    public function widget( $args, $instance )
    {
        // if(!empty($instance['shortcode'])):
        //     echo do_shortcode($instance['shortcode']);
        // else:
            echo do_shortcode('[rentmy-search]');
        // endif;
    }
}
