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
					<div class="col-md-12">
					
						<?php if ($edit == true) { ?>
							<h3><?php _e('Edit booking: ', 'ecalypse-rental');?>#<?= $detail['info']->id_order ?></h3>
							<a href="<?= esc_url(home_url('/')); ?>?page=ecalypse-rental&summary=<?= $detail['info']->hash ?>" target="_blank" class="btn btn-info btn-xs"><?php _e('Show confirmation link', 'ecalypse-rental');?></a>
						<?php } else { ?>
							<?php if (isset($_GET['deleted'])) { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-booking')); ?>" class="btn btn-default" style="float:right;"><?php _e('Show normal', 'ecalypse-rental');?></a>
							<?php } else { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-booking')); ?>&amp;deleted" class="btn btn-default" style="float:right;"><?php _e('Show archived', 'ecalypse-rental');?></a>
							<?php } ?>
							
							<a href="javascript:void(0);" class="btn btn-success" id="ecalypse-rental-booking-add-button"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;<?php _e('Add new booking', 'ecalypse-rental');?></a>
							
						<?php } ?>
						
						<div id="<?= (($edit == true) ? 'ecalypse-rental-booking-edit-form' : 'ecalypse-rental-booking-add-form') ?>" class="ecalypse-rental-add-form">
							<form action="" method="post" role="form" class="form-horizontal">
								
								<div class="row">
									<div class="col-md-11">
										
										<!-- Enter date //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-enter-date" class="col-sm-3 control-label"><?php _e('Pickup date and time', 'ecalypse-rental');?></label>
									    <div class="col-sm-6">
									    	<input type="text" name="enter_date" class="form-control pricing_datepicker" id="ecalypse-rental-enter-date" value="<?= (($edit == true) ? Date('Y-m-d', strtotime($detail['info']->enter_date)) : (isset($_GET['new']) ? $_GET['new'] : '')) ?>">
									    </div>
									    <div class="col-sm-3">
									    	<input type="text" name="enter_date_hour" class="form-control" placeholder="12:00" value="<?= (($edit == true) ? Date('H:i', strtotime($detail['info']->enter_date)) : '') ?>">
									    </div>
									  </div>
									  
										<!-- Enter location //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-enter-location" class="col-sm-3 control-label"><?php _e('Pickup location', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<select name="id_enter_branch" class="form-control">
										    	<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
										    	<?php if ($branches && !empty($branches)) { ?>
													<?php $enter_branch = null;?>
										    		<?php foreach ($branches as $key => $val) { ?>
														<?php if ($edit == true && $detail['info']->enter_loc == $val->name) {$enter_branch = $val;} ?>
										    			<option value="<?= $val->id_branch ?>" <?= (($edit == true && ($detail['info']->enter_loc == $val->name || $detail['info']->id_enter_branch == $val->id_branch)) ? 'selected="selected"' : '') ?>><?= $val->name ?></option>
										    		<?php } ?>
										    	<?php } ?>
									    	</select>
									    </div>
									  </div>
										
										<!-- Return date //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-return-date" class="col-sm-3 control-label"><?php _e('Return date and time', 'ecalypse-rental');?></label>
									    <div class="col-sm-6">
									    	<input type="text" name="return_date" class="form-control pricing_datepicker" id="ecalypse-rental-return-date" value="<?= (($edit == true) ? Date('Y-m-d', strtotime($detail['info']->return_date)) : '') ?>">
									    </div>
									    <div class="col-sm-3">
									    	<input type="text" name="return_date_hour" class="form-control" placeholder="12:00" value="<?= (($edit == true) ? Date('H:i', strtotime($detail['info']->return_date)) : '') ?>">
									    </div>
									  </div>
									  
										<!-- Return location //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-enter-location" class="col-sm-3 control-label"><?php _e('Return location', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<select name="id_return_branch" class="form-control">
										    	<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
										    	<?php if ($branches && !empty($branches)) { ?>
										    		<?php foreach ($branches as $key => $val) { ?>
										    			<option value="<?= $val->id_branch ?>" <?= (($edit == true && ($detail['info']->return_loc == $val->name || $detail['info']->id_return_branch == $val->id_branch)) ? 'selected="selected"' : '') ?>><?= $val->name ?></option>
										    		<?php } ?>
										    	<?php } ?>
									    	</select>
									    </div>
									  </div>
										
										<!-- Return location //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-enter-location" class="col-sm-3 control-label"><?php _e('Vehicle', 'ecalypse-rental');?></label>
									    <div class="col-sm-4">
									    	<?php if (!empty($detail['info']->vehicle_picture)) { ?>
													<img src="<?= $detail['info']->vehicle_picture ?>" height="60">
													&nbsp;
												<?php } ?>
												<h4><?= $detail['info']->vehicle ?></h4>
												
											</div>
									    <div class="col-sm-5">
									    	<select name="change_vehicle" class="form-control">
									    		<?php if ($edit == true) { ?>
										    		<option value="0"><?php _e('Do not change vehicle<', 'ecalypse-rental');?>/option>
										    	<?php } else { ?>
										    		<option value="0">- <?php _e('Select vehicle', 'ecalypse-rental');?> -</option>
										    	<?php } ?>
										    	
										    	<?php if ($fleet && !empty($fleet)) { ?>
										    		<?php foreach ($fleet as $key => $val) { ?>
										    			<option value="<?= $val->id_fleet ?>"><?= $val->name ?></option>
										    		<?php } ?>
										    	<?php } ?>
									    	</select>
									    </div>
									  </div>
										
									<!-- Order status //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-status" class="col-sm-3 control-label"><?php _e('Status', 'ecalypse-rental');?></label>
									    <div class="col-sm-3">
									    	<select name="status" class="form-control">
												<?php foreach (EcalypseRental_Admin::$booking_statuses as $k => $v) {?>
													<option value="<?php echo $k;?>"<?php echo $k == $detail['info']->status ? ' selected="selected"' : '';?>><?php echo $v;?></option>
												<?php } ?>
									    	</select>
									    </div>
										<div class="col-sm-6">
											<?php if ($edit == true) { ?>
									  			<button type="submit" class="btn btn-warning" name="add_booking"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Save the whole booking', 'ecalypse-rental');?></button>
												<button type="submit" class="btn btn-warning" name="add_booking_emails"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Save the whole booking and send relevant status email', 'ecalypse-rental');?></button>
									  		<?php } ?>
									    </div>
									  </div>
									
									 <!-- Paid online //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-paid-online" class="col-sm-3 control-label"><?php _e('Paid online', 'ecalypse-rental');?></label>
									    <div class="col-sm-1">
									    	<input type="text" name="paid_online" class="form-control" id="ecalypse-rental-paid-online" value="<?= (($edit == true) ? $detail['info']->paid_online : '0') ?>">
									    </div>
										<div class="col-sm-1">
											<?= (($edit == true) ? $detail['info']->currency : '-') ?>
										</div>
									  </div>
										
									  <div class="form-group">
									  	<div class="col-sm-3"></div>
									    <div class="col-sm-9">
									    	<h3><?php _e('Driver details', 'ecalypse-rental');?></h3>
									    </div>
									  </div>
									  
									  <!-- First name //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-first-name" class="col-sm-3 control-label"><?php _e('First name', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="first_name" class="form-control" id="ecalypse-rental-first-name" value="<?= (($edit == true) ? $detail['info']->first_name : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Last name //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-last-name" class="col-sm-3 control-label"><?php _e('Last name', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="last_name" class="form-control" id="ecalypse-rental-last-name" value="<?= (($edit == true) ? $detail['info']->last_name : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Contact e-mail //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-email" class="col-sm-3 control-label"><?php _e('Contact e-mail', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="email" class="form-control" id="ecalypse-rental-email" value="<?= (($edit == true) ? $detail['info']->email : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Contact phone //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-phone" class="col-sm-3 control-label"><?php _e('Contact phone', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="phone" class="form-control" id="ecalypse-rental-phone" value="<?= (($edit == true) ? $detail['info']->phone : '') ?>">
									    </div>
									  </div>
									  
									  
									  <!-- Street //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-street" class="col-sm-3 control-label"><?php _e('Street', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="street" class="form-control" id="ecalypse-rental-street" value="<?= (($edit == true) ? $detail['info']->street : '') ?>">
									    </div>
									  </div>
									 	
									 	<!-- City //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-city" class="col-sm-3 control-label"><?php _e('City', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="city" class="form-control" id="ecalypse-rental-city" placeholder="Prague, London, Los Angeles, ..." value="<?= (($edit == true) ? $detail['info']->city : '') ?>">
									    </div>
									  </div>
									  
									  <!-- ZIP //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-zip" class="col-sm-3 control-label"><?php _e('ZIP Code', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="zip" class="form-control" id="ecalypse-rental-zip" value="<?= (($edit == true) ? $detail['info']->zip : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Country //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-country" class="col-sm-3 control-label"><?php _e('Country', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<select name="country" class="form-control" id="ecalypse-rental-country">
										    	<option value="">- <?php _e('select', 'ecalypse-rental');?> -</option>
										    	<?php $countries = EcalypseRental_Admin::get_country_list(); ?>
										    	<?php foreach ($countries as $key => $val) { ?>
										    		<option value="<?= $key ?>" <?= (($edit == true && $key == $detail['info']->country) ? 'selected="selected"' : '') ?>><?= $val ?></option>
										    	<?php } ?>
									    	</select>
									    </div>
									  </div>
									  
									  <!-- Company name //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-company" class="col-sm-3 control-label"><?php _e('Company name', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="company" class="form-control" id="ecalypse-rental-company" value="<?= (($edit == true) ? $detail['info']->company : '') ?>">
									    </div>
									  </div>
									  
									  <!-- VAT //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-vat" class="col-sm-3 control-label"><?php _e('VAT no.', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="vat" class="form-control" id="ecalypse-rental-vat" value="<?= (($edit == true) ? $detail['info']->vat : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Flight no. //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-flight" class="col-sm-3 control-label"><?php _e('Flight no.', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="flight" class="form-control" id="ecalypse-rental-flight" value="<?= (($edit == true) ? $detail['info']->flight : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Driver's license no //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-license-no" class="col-sm-3 control-label"><?php _e('Driver\'s license no.', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="license" class="form-control" id="ecalypse-rental-license-no" value="<?= (($edit == true) ? $detail['info']->license : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Passport / ID number //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-id-no" class="col-sm-3 control-label"><?php _e('Passport or ID no.', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="id_card" class="form-control" id="ecalypse-rental-id-no" value="<?= (($edit == true) ? $detail['info']->id_card : '') ?>">
									    </div>
									  </div>
									  
										<!-- Payment option //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-payment" class="col-sm-3 control-label"><?php _e('Payment option', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<select name="payment_option" class="form-control" id="ecalypse-rental-payment" >
									    		<option value=""><?php _e('Undefined', 'ecalypse-rental');?></option>
									    		<option <?= (($edit == true && $detail['info']->payment_option == 'cash') ? 'selected="selected"' : '') ?> value="cash"><?php _e('Cash', 'ecalypse-rental');?></option>
													<option <?= (($edit == true && $detail['info']->payment_option == 'cc') ? 'selected="selected"' : '') ?> value="cc"><?php _e('Credit Card', 'ecalypse-rental');?></option>
													<option <?= (($edit == true && $detail['info']->payment_option == 'paypal') ? 'selected="selected"' : '') ?> value="paypal"><?php _e('PayPal', 'ecalypse-rental');?></option>
													<option <?= (($edit == true && $detail['info']->payment_option == 'bank') ? 'selected="selected"' : '') ?> value="bank"><?php _e('Bank transfer', 'ecalypse-rental');?></option>
									    	</select>
									    </div>
									  </div>
										
										<!-- Partner code -->
										<div class="form-group">
									    <label for="ecalypse-rental-partner-code" class="col-sm-3 control-label"><?php _e('Partner code', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="partner_code" class="form-control" id="ecalypse-rental-partner-code" value="<?= (($edit == true) ? $detail['info']->partner_code : '') ?>">
											<p class="help-block"><?php _e('Have your partners fill out this field while making a booking if you are using Ecalypse Partner sales reports plugin to track your partner`s sales.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
										
										<!-- Comment -->
										<div class="form-group">
									    <label for="ecalypse-rental-comment" class="col-sm-3 control-label"><?php _e('Comments', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<textarea name="comment" class="form-control" id="ecalypse-rental-comment"><?= (($edit == true) ? $detail['info']->comment : '') ?></textarea>
									    </div>
									  </div>
										
										<?php do_action( 'ecalypse_rental_admin_booking_form', $edit ? $detail['info']->id_booking : 0 ); ?>

										
										<div class="form-group">
									  	<div class="col-sm-3"></div>
									    <div class="col-sm-9">
									    	<h3><?php _e('Additional drivers', 'ecalypse-rental');?></h3>
									    </div>
									  </div>
									  
									  
									  <?php if ($edit == true && isset($detail['drivers'])) { ?>
									  	<?php foreach ($detail['drivers'] as $key => $val) { ?>
									  		<?php $drv = $key + 1; ?>
									  		
									  		<div class="form-group additional_driver">
											    <label class="col-sm-3 control-label">
														<a href="javascript:void(0);" class="btn btn-xs btn-danger delete_driver"><?php _e('Delete', 'ecalypse-rental');?></a>
														&nbsp;&nbsp;&nbsp;<?php _e('Driver no. ', 'ecalypse-rental');?><?= $drv ?>
													</label>
											    <div class="col-sm-9">
											    	
													  <div class="form-group">
													    <div class="col-sm-6">
													    	<input type="text" name="drv[first_name][]" class="form-control" placeholder="First name" value="<?= $val->first_name ?>">
													    </div>
													    <div class="col-sm-6">
													    	<input type="text" name="drv[last_name][]" class="form-control" placeholder="Last name" value="<?= $val->last_name ?>">
													    </div>
													  </div>
													  
													  <div class="form-group">
													    <div class="col-sm-6">
													    	<input type="text" name="drv[email][]" class="form-control" placeholder="E-mail" value="<?= $val->email ?>">
													    </div>
													    <div class="col-sm-6">
													    	<input type="text" name="drv[phone][]" class="form-control" placeholder="Phone" value="<?= $val->phone ?>">
													    </div>
													  </div>
													  
													  <!-- Street //-->
													  <div class="form-group">
													    <div class="col-sm-6">
													    	<input type="text" name="drv[street][]" class="form-control" placeholder="Street" value="<?= $val->street ?>">
													    </div>
													    <div class="col-sm-6">
													    	<input type="text" name="drv[city][]" class="form-control" placeholder="City" value="<?= $val->city?>">
													    </div>
													  </div>
													 	
													  <!-- ZIP //-->
													  <div class="form-group">
													    <div class="col-sm-4">
													    	<input type="text" name="drv[zip][]" class="form-control" placeholder="ZIP" value="<?= $val->zip ?>">
													    </div>
													    <div class="col-sm-8">
													    	<select name="drv[country][]" class="form-control">
														    	<option value="">- <?php _e('select', 'ecalypse-rental');?>', 'ecalypse-rental');?> -</option>
														    	<?php $countries = EcalypseRental_Admin::get_country_list(); ?>
														    	<?php foreach ($countries as $kD => $vD) { ?>
														    		<option value="<?= $kD ?>" <?= (($edit == true && $kD == $val->country) ? 'selected="selected"' : '') ?>><?= $vD ?></option>
														    	<?php } ?>
													    	</select>
													    </div>
													  </div>
													  											    	
											    </div>
											  </div>
									  		
									  	<?php } ?>
									  <?php } ?>
									  
									  <div class="form-group additional_driver additional_driver_new">
									    <label class="col-sm-3 control-label">
												<a href="javascript:void(0);" class="btn btn-xs btn-danger delete_driver">Delete</a>
												&nbsp;&nbsp;&nbsp;<?php _e('New driver', 'ecalypse-rental');?>												
											</label>
									    <div class="col-sm-9">
									    	
											  <div class="form-group">
											    <div class="col-sm-6">
											    	<input type="text" name="drv[first_name][]" class="form-control" placeholder="First name">
											    </div>
											    <div class="col-sm-6">
											    	<input type="text" name="drv[last_name][]" class="form-control" placeholder="Last name">
											    </div>
											  </div>
											  
											  <div class="form-group">
											    <div class="col-sm-6">
											    	<input type="text" name="drv[email][]" class="form-control" placeholder="E-mail">
											    </div>
											    <div class="col-sm-6">
											    	<input type="text" name="drv[phone][]" class="form-control" placeholder="Phone">
											    </div>
											  </div>
											  
											  <div class="form-group">
											    <div class="col-sm-6">
											    	<input type="text" name="drv[street][]" class="form-control" placeholder="Street">
											    </div>
											    <div class="col-sm-6">
											    	<input type="text" name="drv[city][]" class="form-control" placeholder="City">
											    </div>
											  </div>
											 	
											  <div class="form-group">
											    <div class="col-sm-4">
											    	<input type="text" name="drv[zip][]" class="form-control" placeholder="ZIP">
											    </div>
											    <div class="col-sm-8">
											    	<select name="drv[country][]" class="form-control">
												    	<option value="">- <?php _e('select', 'ecalypse-rental');?> -</option>
												    	<?php $countries = EcalypseRental_Admin::get_country_list(); ?>
												    	<?php foreach ($countries as $kD => $vD) { ?>
												    		<option value="<?= $kD ?>"><?= $vD ?></option>
												    	<?php } ?>
											    	</select>
											    </div>
											  </div>
											  		    	
									    </div>
									  </div>
									  
									  <div class="form-group additional_driver_new_button">
									  	<label class="col-sm-3 control-label"></label>
									    <div class="col-sm-9">
									  		<a href="javascript:void(0);" class="btn btn-success add_another_driver"><?php _e('Add another driver', 'ecalypse-rental');?></a>
									  	</div>
									  </div>
									  
									  <script type="text/javascript">
									  
									  	jQuery(document).ready(function($) {
												
												jQuery('.additional_driver_new').hide();
												
												jQuery(document).on('click', '.delete_driver', function() {
													jQuery(this).parent().parent().remove();
												});
												
												jQuery('.add_another_driver').on('click', function() {
													jQuery('.additional_driver_new_button').before('<div class="form-group additional_driver">' + jQuery('.additional_driver_new').html() + '</div>');
												});
											
												$('.datepicker').datepicker({dateFormat: 'yy-mm-dd'});
											});
									  
									  </script>
									  
									  <div class="form-group">
									  	<div class="col-sm-3"></div>
									    <div class="col-sm-9">
									    	<h3><?php _e('Prices', 'ecalypse-rental');?></h3>
									    </div>
									  </div>
										
										
										<?php $currency = array(get_option('ecalypse_rental_global_currency')); ?>
										<?php $av_currencies = unserialize(get_option('ecalypse_rental_available_currencies')); ?>
										<?php if (!empty($av_currencies)) { $av_currencies = array_keys($av_currencies); $currency = array_merge($currency, $av_currencies); } ?>
										
										<?php if (isset($detail['prices']) && !empty($detail['prices'])) { ?>
											<?php foreach ($detail['prices'] as $key => $val) { ?>
												<div class="form-group price_row">
											    <label class="col-sm-3 control-label">
														<a href="javascript:void(0);" class="btn btn-xs btn-danger delete_price"><?php _e('Delete', 'ecalypse-rental');?></a>
														&nbsp;&nbsp;&nbsp;Row no. <?= $key+1 ?>
													</label>
											    <div class="col-sm-9">
													  <div class="form-group">
													    <div class="col-sm-8">
															<input type="hidden" name="prices[item_id][]" value="<?php echo $val->item_id;?>">
															<input type="hidden" name="prices[extras_id][]" value="<?php echo $val->extras_id;?>">
															<input type="hidden" name="prices[new_item_id][]" value="0">
													    	<input type="text" name="prices[name][]" class="form-control" value="<?= (($edit == true) ? $val->name : '') ?>" <?php echo $val->item_id > 0 ? ' readonly="readonly"' : '' ;?>>
													    </div>
													    <div class="col-sm-2">
													    	<div class="form-group">
															    <input type="text" name="prices[price][]" class="form-control" value="<?= (($edit == true) ? $val->price : '') ?>">
															  </div>
													    </div>
													    <div class="col-sm-2">
													    	<div class="form-group">
															    <select name="prices[currency][]" class="form-control price_currency" style="width:70%;margin-left:1em;padding: 3px 3px;height: 2.35em;">
															    	<?php foreach ($currency as $cc) { ?>
																			<option value="<?= $cc ?>" <?php if ($edit == true && $val->currency == $cc) { ?>selected<?php } ?>><?= $cc ?></option>
																		<?php } ?>
															    </select>
															  </div>
													    </div>
													  </div>
											    </div>
											  </div>
											  
											<?php } ?>
										<?php } ?>
										
										<div class="form-group price_new">
									    <label class="col-sm-3 control-label">
												<a href="javascript:void(0);" class="btn btn-xs btn-danger delete_price"><?php _e('Delete', 'ecalypse-rental');?></a>
												&nbsp;&nbsp;&nbsp;<?php _e('New row', 'ecalypse-rental');?>
											</label>
									    <div class="col-sm-9">
											  <div class="form-group">
											    <div class="col-sm-8">
											    	<input type="text" name="prices[name][]" class="form-control" placeholder="Description">
													<input type="hidden" name="prices[new_item_id][]" value="0">
											    </div>
											    <div class="col-sm-2">
											    	<div class="form-group">
													    <input type="text" name="prices[price][]" class="form-control" placeholder="Price">
													  </div>
											    </div>
											    <div class="col-sm-2">
											    	<div class="form-group">
													    <select name="prices[currency][]" class="form-control price_currency" style="width:70%;margin-left:1em;padding: 3px 3px;height: 2.35em;">
													    	<?php foreach ($currency as $cc) { ?>
																	<option value="<?= $cc ?>"><?= $cc ?></option>
																<?php } ?>
													    </select>
													  </div>
											    </div>
											  </div>
									    </div>
									  </div>
									  
									  <div id="available_vehicles" style="display: none;" class="form-group">
										  <label class="col-sm-3 control-label"></label>
									    <div class="col-sm-3">
											
										  <select class="form-control" id="available_vehicles_select">
											  <option value="0"><?php _e('Select vehicle', 'ecalypse-rental');?></option>
											  <?php foreach ($vehicle_names as $k => $v) { ?>
												<option value="<?php echo $k;?>"><?php echo $v;?></option>
											  <?php } ?>
										  </select>
										</div>
										<div class="col-sm-6">
											<a href="#" class="btn btn-success add_vehicle" id="available_vehicles_add_button"><?php _e('Add vehicle', 'ecalypse-rental');?></a>
										</div>
									  </div>
										
									  <div class="form-group price_new_button">
									  	<label class="col-sm-3 control-label"></label>
									    <div class="col-sm-9">
									  		<a href="javascript:void(0);" class="btn btn-success add_price"><?php _e('Add another row', 'ecalypse-rental');?></a>
											<a href="#" class="btn btn-success add_another_vehicle"><?php _e('Add another vehicle', 'ecalypse-rental');?></a>
									  	</div>
									  </div>
									  	  
									  <script type="text/javascript">
									  
									  	jQuery(document).ready(function() {
											
												function load_available_cars() {
													if (jQuery('#ecalypse-rental-enter-date').val() == '' || jQuery('#ecalypse-rental-return-date').val() == '' || jQuery('input[name=enter_date_hour]').val() == '' || jQuery('input[name=return_date_hour]').val() == '') {
														alert('<?php _e('Set enter and return dates and times first.', 'ecalypse-rental');?>');
														return false;
													}
													jQuery.ajax({
														url: ajaxurl,
														global: false,
														type: "POST",
														data: ({
															action: 'ecalypse_rental_load_available_cars',
															'fd': jQuery('#ecalypse-rental-enter-date').val(),
															'td': jQuery('#ecalypse-rental-return-date').val(),
															'fh': jQuery('input[name=enter_date_hour]').val(),
															'th': jQuery('input[name=return_date_hour]').val(),
															'el': jQuery('select[name=id_enter_branch]').val()
															<?php if ($edit == true) { ?>
															, 'booking-id': <?php echo $_GET['edit'];?>		
															<?php } ?>
														}),
														dataType: "json",
														async: true,
														success: function(data){
															if (data && data != 0) {
																jQuery('#available_vehicles_select option:not(:eq(0))').remove();
																jQuery.each(data, function(k,v){
																	jQuery('#available_vehicles_select').append('<option value="'+k+'" data-price="'+v.price+'">'+v.name+'</option>');
																});
															}
														}
													});
													return true;
												}
												
												jQuery('#available_vehicles_add_button').click(function(e){
													e.preventDefault();
													opt =  jQuery("#available_vehicles_select option:selected");
													if (parseInt(opt.val()) < 1) {
														return false;
													}
													jQuery('.price_new').before('<div class="form-group price_row">' + jQuery('.price_new').html() + '</div>');
													jQuery('.price_row').last().find('input:first').val(opt.text()+', '+jQuery('#ecalypse-rental-enter-date').val()+' '+jQuery('input[name=enter_date_hour]').val()+' ('+jQuery('select[name=id_enter_branch] option:selected').text()+') - '+jQuery('#ecalypse-rental-return-date').val()+' '+jQuery('input[name=return_date_hour]').val()+' ('+jQuery('select[name=id_return_branch] option:selected').text()+')').attr('readonly', 'readonly').before('<input type="hidden" name="prices[new_item_id][]" value="'+opt.val()+'">');
													if (opt.data('price') > 0) {
														jQuery('.price_row').last().find('.col-sm-2 input').val(opt.data('price'));
													}
												});
												
												jQuery('.price_new').hide();
												
												jQuery('.add_another_vehicle').click(function(e){
													e.preventDefault();
													if (load_available_cars()) {
														jQuery('#available_vehicles').show('slow');
													}
												});
												
												jQuery(document).on('click', '.delete_price', function() {
													jQuery(this).parent().parent().remove();
												});
												
												jQuery('.add_price').on('click', function() {
													jQuery('.price_new').before('<div class="form-group price_row">' + jQuery('.price_new').html() + '</div>');
													jQuery('.price_currency').val(jQuery('.price_currency').first().val());
												});
												
												jQuery(document).on('change', '.price_currency', function() {
													var currency = jQuery(this).val();
													jQuery('.price_currency').val(currency);
												});
												
												jQuery('#ecalypse-rental-booking-add-form').hide();
												<?php if (isset($_GET['new'])) {?>
													jQuery('#ecalypse-rental-booking-add-form').show();
												<?php } ?>
												
											});
									  
									  </script>
									  
									  <!-- Submit //-->
									  <div class="form-group">
									  	<div class="col-sm-offset-3 col-sm-9">
											<?php wp_nonce_field( 'add_booking'); ?>
									  		<?php if ($edit == true) { ?>
									  			<input type="hidden" name="id_booking" value="<?= $detail['info']->id_booking ?>">
									  			<button type="submit" class="btn btn-warning" name="add_booking"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									  		<?php } else { ?>
									  			<button type="submit" class="btn btn-warning" name="add_booking"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Add', 'ecalypse-rental');?></button>
									  		<?php } ?>
									  	</div>
									  </div>
										
									</div>
								</div>
								
							</form>
						</div>
						
						<hr>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">
						<form class="booking-fulltext-search" action="" method="get">
							<?php if (isset($_GET['deleted'])) { ?>
								<input type="hidden" name="deleted" value="1">
								<?php } ?>
							<label><?php _e('Return date from:', 'ecalypse-rental');?> </label>
							<input type="text" name="filter_from" class="datepicker" value="<?= isset($_GET['filter_from']) ? $_GET['filter_from'] : date('Y-m-d') ?>">
							
							<label><?php _e('Return date to:', 'ecalypse-rental');?> </label>
							<input type="text" name="filter_to" class="datepicker" value="<?= isset($_GET['filter_to']) ? $_GET['filter_to'] : '' ?>">
							
							<label><?php _e('Fulltext search:', 'ecalypse-rental');?> </label>
							<input type="text" name="q" placeholder="Fulltext search" value="<?php echo isset($_GET['q']) ? $_GET['q'] : '';?>" />
							<input type="hidden" name="page" value="<?php echo $_GET['page'];?>">
							<input type="submit" class="btn btn-default" value="Show">
						</form>
						<?php if (isset($booking) && !empty($booking)) { ?>
						<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-booking" /> <?php _e('Select all', 'ecalypse-rental');?></label>
							<table class="table table-striped" id="ecalypse-rental-booking">
					      <thead>
					        <tr>
					          <th>#</th>
							  <th><?php _e('Name', 'ecalypse-rental');?><br><?php _e('Created on', 'ecalypse-rental');?></th>
							  <th><?php _e('Email', 'ecalypse-rental');?></th>
					          <th><?php _e('Vehicle', 'ecalypse-rental');?></th>
					          <th><?php _e('Pickup date', 'ecalypse-rental');?></th>
					          <th><?php _e('Pickup loc.', 'ecalypse-rental');?></th>
					          <th><?php _e('Return date', 'ecalypse-rental');?></th>
					          <th><?php _e('Return loc.', 'ecalypse-rental');?></th>
					          <th><?php _e('Price', 'ecalypse-rental');?></th>
					          <th><?php _e('Order ID', 'ecalypse-rental');?></th>
					          <th><?php _e('Action', 'ecalypse-rental');?></th>
					        </tr>
					      </thead>
					      <tbody>
					      	
					      	<?php foreach ($booking as $key => $val) { ?>
				      		<tr>
					          <td>
											<input type="checkbox" class="input-control batch_processing" name="batch[]" value="<?= $val->id_booking ?>">&nbsp;
											<abbr title="Created: <?= $val->created ?>

								<?= (!empty($val->updated) ? 'Updated: ' . $val->updated : '') ?>"><?= $val->id_booking ?></abbr></td>
							  <td><?= $val->first_name.' '.$val->last_name; ?><br><?= $val->created ?></td>
							  <td><?= $val->email; ?><br>
							  <form action="" method="post" class="form-inline" role="form">
												<div class="form-group">
													<?php wp_nonce_field( 'resend_email'); ?>
													<input type="hidden" name="id_booking" value="<?= $val->id_booking ?>">
													<button name="resend_email" class="btn btn-xs btn-success"><?php _e('Resend current status email', 'ecalypse-rental');?></button>
												</div>
											</form>
							  </td>
										<td><strong><?= (!empty($val->vehicle) ? $val->vehicle : '- Unknown -') ?></strong>
										<?php $val->all_vehicles = explode(',',$val->all_vehicles); ?>
												<?php $m=false; foreach ($val->all_vehicles as $vid) { 
													if ((int)$vid < 1) {continue;}
													if (!$m && $vid == $val->vehicle_id) {
														$m = true; 
														continue;
													}
													?>
												<br><?php echo isset($vehicle_names[$vid]) ? $vehicle_names[$vid] : '- unknown -';?>
												<?php } ?>
										</td>
										<td><?= $val->enter_date ?></td>
										<td><?= $val->enter_loc ?></td>
					          <td><?= $val->return_date ?></td>
					          <td><?= $val->return_loc ?></td>
					          <td><?= EcalypseRental::get_currency_symbol('before', $val->currency) ?><?= number_format($val->total_rental, 2, '.', ',') ?><?= EcalypseRental::get_currency_symbol('after', $val->currency) ?></td>
					          <td><a href="<?= esc_url(home_url('/')); ?>?page=ecalypse-rental&summary=<?= $val->hash ?>" target="_blank" class="btn btn-info btn-xs"><?php _e('Show', 'ecalypse-rental');?> #<?= $val->id_order ?></a></td>
										<td>
											<form action="" method="post" class="form-inline" role="form">
												<div class="form-group">
													<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-booking')); ?>&amp;edit=<?= $val->id_booking ?>" class="btn btn-xs btn-primary"><?php _e('Modify', 'ecalypse-rental');?></a>
												</div>
											</form>
											<form action="" method="post" class="form-inline" role="form">
												<div class="form-group">
													<input type="hidden" name="id_booking" value="<?= $val->id_booking ?>">
													<?php wp_nonce_field( 'copy_booking'); ?>
													<button name="copy_booking" class="btn btn-xs btn-warning"><?php _e('Copy', 'ecalypse-rental');?></button>
												</div>
											</form>
											<?php if (isset($_GET['deleted'])) { ?>
												<form action="" method="post" class="form-inline" role="form" onsubmit="return confirm('<?= __('Do you really want to restore this Booking?', 'ecalypse-rental') ?>');">
													<div class="form-group">
														<input type="hidden" name="id_booking" value="<?= $val->id_booking ?>">
														<?php wp_nonce_field( 'restore_booking'); ?>
														<button name="restore_booking" class="btn btn-xs btn-success"><?php _e('Restore', 'ecalypse-rental');?></button>
													</div>
												</form>
											<?php } else { ?>
												<form action="" method="post" class="form-inline" role="form" onsubmit="return confirm('<?= __('Do you really want to archive this Booking?', 'ecalypse-rental') ?>');">
													<div class="form-group">
														<input type="hidden" name="id_booking" value="<?= $val->id_booking ?>">
														<?php wp_nonce_field( 'delete_booking'); ?>
														<button name="delete_booking" class="btn btn-xs btn-danger"><?php _e('Archive', 'ecalypse-rental');?></button>
													</div>
												</form>
											<?php } ?>
										</td>
					        </tr>
									<?php } ?>
					      </tbody>
					    </table>
						<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-booking" /> <?php _e('Select all', 'ecalypse-rental');?></label>
						
					    <h4><?php _e('Batch action on selected items', 'ecalypse-rental');?></h4>
					    
					    <form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Booking is selected to copy.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to copy selected Bookings?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<input type="hidden" name="batch_processing_values" value="">
									<?php wp_nonce_field( 'batch_copy_booking'); ?>
									<button name="batch_copy_booking" class="btn btn-warning"><?php _e('Copy', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Bookings', 'ecalypse-rental');?></button>
								</div>
							</form>
							
						<?php if (isset($_GET['deleted'])) { ?>
							<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Booking is selected to delete.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to delete selected Bookings?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<input type="hidden" name="batch_processing_values" value="">
									<?php wp_nonce_field( 'batch_delete_booking'); ?>
									<button name="batch_delete_booking" class="btn btn-danger"><?php _e('Delete', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Bookings', 'ecalypse-rental');?></button>
								</div>
							</form>
						<?php } else { ?>
					    <form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Booking is selected to archive.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to archive selected Bookings?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<input type="hidden" name="batch_processing_values" value="">
									<?php wp_nonce_field( 'batch_archive_booking'); ?>
									<button name="batch_archive_booking" class="btn btn-danger"><?php _e('Archive', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Bookings', 'ecalypse-rental');?></button>
								</div>
							</form>
					    <?php } ?>
					    
						<?php } else { ?>
							<div class="alert alert-info">
								<span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;
								<?= esc_html__( 'There are no active Bookings.', 'ecalypse-rental' ); ?>
							</div>
						<?php } ?>
						
					</div>
				</div>
				
				
				
			</div>
		</div>
	</div>
	
</div>
