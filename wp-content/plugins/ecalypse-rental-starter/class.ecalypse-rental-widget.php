<?php
/**
 * @package EcalypseRentalStarter
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

class EcalypseRental_Widget_Info extends WP_Widget {

	function __construct() {
		load_plugin_textdomain('ecalypse_rental');
		parent::__construct('ecalypse_rental_widget_info', __( 'Ecalypse Rental Starter Info' , 'ecalypse_rental'), array('description' => __('Company info.', 'ecalypse_rental') ));
	}

	function form($instance) {
		$title = (($instance) ? $instance['title'] : __( 'Company info' , 'ecalypse_rental'));
		include(ECALYPSERENTALSTARTER__PLUGIN_DIR . 'widget-views/info-form.php');
	}
	
	function update($new_instance, $old_instance) {
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

	function widget($args, $instance) {
		$info = get_option('ecalypse_rental_company_info');

		echo $args['before_widget'];
		include(ECALYPSERENTALSTARTER__PLUGIN_DIR . 'widget-views/info-widget.php');
		echo $args['after_widget'];
		
	}
}

function EcalypseRentalStarter_register_widgets() {
	
	register_widget('EcalypseRental_Widget_Info');
	
}

add_action( 'widgets_init', 'EcalypseRentalStarter_register_widgets' );
