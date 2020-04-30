<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<div class="row">
  <div class="col-sm-12 col-md-12 col-lg-6">
  	<h2><?= ($cr_title != 'Ecalypse rental' ? $cr_title . ' - ' : '') ?><?= __('Ecalypse Starter Rental Plugin', 'ecalypse-rental') ?></h2>
	
	<div class="row">
	<div class="col-md-12">
		<a class="btn btn-warning" href="https://wp.ecalypse.com"><span class="glyphicon glyphicon-save"></span> Get Full Version Here</a>
	</div>
</div>
	</div>
	
	<div class="col-sm-12 col-md-12 col-lg-6 besi-link">
		<a target="_blank" href="https://www.besi100.com/?utm_source=Ecalypse_wordpress&utm_medium=banner&utm_campaign=Ad_Besi100&utm_term=Vehicle_rental_handover_app&utm_content=Wordpress_banner"><img src="<?php echo ECALYPSERENTALSTARTER__PLUGIN_URL;?>/assets/img/besi100.png" alt="Besi100.com" style="max-width: 100%;"></a>
	</div>
</div>



<div class="row">
	<div class="col-md-12">
		<ul class="nav nav-tabs">
		  <li<?= (($_GET['page'] == 'ecalypse-rental') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental')); ?>"><span class="glyphicon glyphicon-home"></span>&nbsp;&nbsp;<?= __('Home', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-fleet') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-fleet')); ?>"><span class="glyphicon glyphicon-road"></span>&nbsp;&nbsp;<?= __('Fleet', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-extras') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-extras')); ?>"><span class="glyphicon glyphicon-asterisk"></span>&nbsp;&nbsp;<?= __('Extras', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-branches') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-branches')); ?>"><span class="glyphicon glyphicon-map-marker"></span>&nbsp;&nbsp;<?= __('Branches', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-pricing') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>"><span class="glyphicon glyphicon-usd"></span>&nbsp;&nbsp;<?= __('Pricing', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-booking') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-booking')); ?>"><span class="glyphicon glyphicon-calendar"></span>&nbsp;&nbsp;<?= __('Booking', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-translations') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-translations')); ?>"><span class="glyphicon glyphicon-globe"></span>&nbsp;&nbsp;<?= __('Translations', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-settings') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-settings')); ?>"><span class="glyphicon glyphicon-cog"></span>&nbsp;&nbsp;<?= __('Settings', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-newsletter') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-newsletter')); ?>"><span class="glyphicon glyphicon-envelope"></span>&nbsp;&nbsp;<?= __('Newsletter', 'ecalypse-rental') ?></a></li>
		  <li<?= (($_GET['page'] == 'ecalypse-rental-rencato-connector') ? ' class="active"' : '') ?>><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-rencato-connector')); ?>"><span class="glyphicon glyphicon-transfer"></span>&nbsp;&nbsp;<?= __('Rencato connector', 'ecalypse-rental') ?></a></li>
		</ul>
	</div>
</div>