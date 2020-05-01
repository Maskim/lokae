<?php
/**
 * Super simple widget.
 */
class RentMy_Tags_Widget extends WP_Widget
{
    public function __construct()
    {                      // id_base        ,  visible name
        parent::__construct( 'rentmy_tags_widget', 'RentMy Filter List' );
    }

    public function widget( $args, $instance )
    {
        // if(!empty($instance['shortcode'])):
        //     echo do_shortcode($instance['shortcode']);
        // else:
            echo do_shortcode('[rentmy-tags-list]');
        // endif;
    }
}
