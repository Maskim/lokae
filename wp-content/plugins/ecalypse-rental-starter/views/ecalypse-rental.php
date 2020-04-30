<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<div class="ecalypse-rental-wrapper">
	
	<?php include ECALYPSERENTALSTARTER__PLUGIN_DIR . 'views/header.php'; ?>

	<div class="row">
		<div class="col-md-12 ecalypse-rental-main-wrapper">
			<div class="ecalypse-rental-main-content">
				
				<?php include ECALYPSERENTALSTARTER__PLUGIN_DIR . 'views/flash_msg.php'; ?>
				
				<div class="row">
					<div class="col-md-11">
						
						<?php $currency = get_option('ecalypse_rental_global_currency'); ?>
						<?php if (!$currency || strlen($currency) != 3) { ?>
							<div class="alert alert-danger"><?php _e('Please, set-up your ', 'ecalypse-rental');?><strong><?php _e('Global Currency', 'ecalypse-rental');?></strong><?php _e(' in ', 'ecalypse-rental');?><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-settings')); ?>"><?php _e('Settings', 'ecalypse-rental');?></a>.</div>
						<?php } ?>
							
						<p class="help-block"><?php _e('To change pictures, logos , filter settings and others, go to Appearance->Theme settings (must have compatible theme active).', 'ecalypse-rental');?></p>
						
						<div class="col-md-4">
						<?php if (isset($quick_info) && !empty($quick_info)) { ?>
							
							<?php if (isset($quick_info['booking_progress']) && !empty($quick_info['booking_progress'])) { ?>
								<div class="alert alert-success">
									<p class="lead">
										<?php _e('You have ', 'ecalypse-rental');?><strong><?= $quick_info['booking_progress'] ?><?php _e(' bookings', 'ecalypse-rental');?></strong><?php _e(' in progress', 'ecalypse-rental');?>
										<?php if (isset($quick_info['booking_future']) && !empty($quick_info['booking_future'])) { ?>
										<?php _e(' and ', 'ecalypse-rental');?><strong><?= $quick_info['booking_future'] ?><?php _e(' bookings', 'ecalypse-rental');?></strong><?php _e(' in the future.', 'ecalypse-rental');?>
										<?php } ?>
										<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-booking')); ?>" class="btn btn-default"><?php _e('Show bookings', 'ecalypse-rental');?></a>
									</p>
								</div>
							<?php } ?>
							
							<div class="panel panel-info">
							  <div class="panel-heading"><?php _e('Quick info', 'ecalypse-rental');?></div>
							  <div class="panel-body quick-info">
							  	<?php if (isset($quick_info['fleet']) && !empty($quick_info['fleet'])) { ?>
							    	<p class="lead"><strong><?= $quick_info['fleet'] ?></strong><?php _e(' active vehicles: ', 'ecalypse-rental');?><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-fleet')); ?>" class="btn btn-default"><strong><?php _e('Fleet', 'ecalypse-rental');?></strong></a></p>
							  	<?php } ?>
							  	<?php if (isset($quick_info['extras']) && !empty($quick_info['extras'])) { ?>
										<p class="lead"><strong><?= $quick_info['extras'] ?></strong><?php _e(' active items: ', 'ecalypse-rental');?><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-extras')); ?>" class="btn btn-default"><strong><?php _e('Extras', 'ecalypse-rental');?></strong></a></p>
									<?php } ?>
									<?php if (isset($quick_info['branches']) && !empty($quick_info['branches'])) { ?>
										<p class="lead"><strong><?= $quick_info['branches'] ?></strong><?php _e(' active locations: ', 'ecalypse-rental');?><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-branches')); ?>" class="btn btn-default"><strong><?php _e('Branches', 'ecalypse-rental');?></strong></a></p>
									<?php } ?>
									<?php if (isset($quick_info['pricing']) && !empty($quick_info['pricing'])) { ?>
										<p class="lead"><strong><?= $quick_info['pricing'] ?></strong><?php _e(' active schemes: ', 'ecalypse-rental');?><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>" class="btn btn-default"><strong><?php _e('Pricing', 'ecalypse-rental');?></strong></a></p>
									<?php } ?>
							  </div>
							</div>
						<?php } else { ?>
							
							<h3><?php _e('Database is empty now, please continue to ', 'ecalypse-rental');?><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-settings')); ?>"><?php _e('Settings', 'ecalypse-rental');?></a><?php _e(' first.', 'ecalypse-rental');?></h3>
							
							<?php /* HIDDEN ?>
							<h3>Database is empty now, do you wish to import demo data?</h3>
							<form action="" method="post" class="form" role="form" onsubmit="return confirm('Do you really want to import demo data?');">
								
								<p>
									* This action will import 3 vehicle categories, 3 cars, 2 extras, 2 branches and 4 pricing schemes.
								</p>
								
								<!-- Submit //-->
							  <div class="form-group">
							  	<div class="col-sm-4">
							  		<button type="submit" name="import_demo_data" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;Import demo data</button>
							  	</div>
								</div>
							
							</form>
							<?php /**/ ?>
							
						<?php } ?>
							</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
	
</div>