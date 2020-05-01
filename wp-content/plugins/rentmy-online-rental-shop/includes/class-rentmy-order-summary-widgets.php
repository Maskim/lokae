<?php
/**
 * Super simple widget.
 */
class RentMy_OrderSummary_Widget extends WP_Widget
{
    public function __construct()
    {                      // id_base        ,  visible name
        parent::__construct( 'rentmy_order_summary_widget', 'RentMy Order Summary' );
    }

    public function widget( $args, $instance )
    {
        // if(!empty($instance['shortcode'])):
        //     echo do_shortcode($instance['shortcode']);
        // else:
            echo do_shortcode('[rentmy-order-summary]');
        // endif;
    }
}
