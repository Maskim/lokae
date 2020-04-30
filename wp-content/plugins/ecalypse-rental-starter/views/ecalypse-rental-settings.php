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
					
				<!-- INFO //-->
				<div class="panel panel-default">
					<div class="panel-heading"><h4><?php _e('Company information', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
					  <form action="" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
							<?php wp_nonce_field( 'settings-company'); ?>
							<?php $company = unserialize(get_option('ecalypse_rental_company_info')); ?>
							
							<div class="row">
								<div class="col-md-4">
									
									<div class="form-group">
								    <label for="ecalypse_rental_company_name" class="col-sm-3 control-label"><?php _e('Name', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="name" class="form-control" id="ecalypse_rental_company_name" value="<?= (isset($company['name']) ? $company['name'] : '') ?>">
								    </div>
								  </div>
								  
								  <div class="form-group">
								    <label for="ecalypse_rental_company_id" class="col-sm-3 control-label"><?php _e('ID no.', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="id" class="form-control" id="ecalypse_rental_company_id" value="<?= (isset($company['id']) ? $company['id'] : '') ?>">
								    </div>
								  </div>
								  
								  <div class="form-group">
								    <label for="ecalypse_rental_company_vat" class="col-sm-3 control-label"><?php _e('VAT no.', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="vat" class="form-control" id="ecalypse_rental_company_vat" value="<?= (isset($company['vat']) ? $company['vat'] : '') ?>">
								    </div>
								  </div>
								  
								  <div class="form-group">
								    <label for="ecalypse_rental_company_email" class="col-sm-3 control-label"><?php _e('E-mail', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="email" class="form-control" id="ecalypse_rental_company_email" value="<?= (isset($company['email']) ? $company['email'] : '') ?>">
										<p class="help-block"><?php _e('Booking confirmation emails are sent to this address.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
								  <div class="form-group">
								    <label for="ecalypse_rental_company_phone" class="col-sm-3 control-label"><?php _e('Phone', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="phone" class="form-control" id="ecalypse_rental_company_phone" value="<?= (isset($company['phone']) ? $company['phone'] : '') ?>">
								    </div>
								  </div>
								  
									<div class="form-group">
								    <label for="ecalypse_rental_company_fax" class="col-sm-3 control-label"><?php _e('Fax', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="fax" class="form-control" id="ecalypse_rental_company_fax" value="<?= (isset($company['fax']) ? $company['fax'] : '') ?>">
								    </div>
								  </div>
								  
								</div>
								
								<div class="col-md-4">
								
								  <div class="form-group">
								    <label for="ecalypse_rental_company_street" class="col-sm-3 control-label"><?php _e('Street', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="street" class="form-control" id="ecalypse_rental_company_street" value="<?= (isset($company['street']) ? $company['street'] : '') ?>">
								    </div>
								  </div>
								  
								  <div class="form-group">
								    <label for="ecalypse_rental_company_city" class="col-sm-3 control-label"><?php _e('City', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="city" class="form-control" id="ecalypse_rental_company_city" value="<?= (isset($company['city']) ? $company['city'] : '') ?>">
								    </div>
								  </div>
								  
								  <div class="form-group">
								    <label for="ecalypse_rental_company_zip" class="col-sm-3 control-label"><?php _e('ZIP code', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="zip" class="form-control" id="ecalypse_rental_company_zip" value="<?= (isset($company['zip']) ? $company['zip'] : '') ?>">
								    </div>
								  </div>
								  
								  <div class="form-group">
								    <label for="ecalypse_rental_company_country" class="col-sm-3 control-label"><?php _e('Country', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<select name="country" class="form-control" id="ecalypse_rental_company_country">
									    	<option value="">- <?php _e('select', 'ecalypse-rental');?> -</option>
									    	<?php $countries = EcalypseRental_Admin::get_country_list(); ?>
									    	<?php foreach ($countries as $key => $val) { ?>
									    		<option value="<?= $key ?>" <?= ((isset($company['country']) && $key == $company['country']) ? 'selected="selected"' : '') ?>><?= $val ?></option>
									    	<?php } ?>
								    	</select>
								    </div>
								  </div>
								  
								  <div class="form-group">
								    <label for="ecalypse_rental_company_web" class="col-sm-3 control-label"><?php _e('Webpage URL', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="web" class="form-control" id="ecalypse_rental_company_web" value="<?= (isset($company['web']) ? $company['web'] : '') ?>">
								    </div>
								  </div>
								  
									<!-- Submit //-->
								  <div class="form-group">
								  	<div class="col-sm-offset-3 col-sm-9">
								  		<button type="submit" name="edit_company_info" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save Company info', 'ecalypse-rental');?></button>
								  	</div>
									</div>
							
								</div>
							</div>
							
						</form>
					</div>
				</div><!-- .panel //-->
				
				
				<!-- GLOBAL SETTINGS //-->
				<div class="panel panel-default">
					<div class="panel-heading"><h4 id="global-settings"><?php _e('Global Settings', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
					  
					  <form action="" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
						  <?php wp_nonce_field( 'settings-global'); ?>
							<div class="row">
								<div class="col-md-6">
									
									<!-- Type of rental //-->
								  <?php $type_of_rental = get_option('ecalypse_rental_type_of_rental'); ?>
									<div class="form-group">
								    <label for="ecalypse_rental_type_of_rental" class="col-sm-3 control-label"><?php _e('Type of Rental', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
										<select name="ecalypse_rental_type_of_rental" id="ecalypse_rental_type_of_rental">
											<?php foreach (EcalypseRental_Admin::$types_of_rental as $k => $v) { ?>
												<option value="<?php echo $k;?>"<?php echo $type_of_rental == $k ? ' selected="selected"' : '';?>><?php echo $v;?></option>
											<?php } ?>
										</select>
								    	<p class="help-block">
												<?php _e('We will use this information when indexing your website in search results. It is important to target the right audience', 'ecalypse-rental');?>
											</p>
								    </div>
								  </div>
									
									<!-- Currency //-->
								  <?php $currency = get_option('ecalypse_rental_global_currency'); ?>
									<div class="form-group">
								    <label for="ecalypse_rental_global_currency" class="col-sm-3 control-label"><?php _e('Global Currency', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="ecalypse_rental_global_currency" class="form-control" id="ecalypse_rental_global_currency" placeholder="USD, EUR, CZK, ..." value="<?= (!empty($currency) ? $currency : '') ?>">
								    	<p class="help-block">
												<?php _e('Fill your primary currency', 'ecalypse-rental');?> (<strong><?php _e('3 letter code', 'ecalypse-rental');?></strong>), <?php _e('do not change it if you already have Price schemes created.', 'ecalypse-rental');?><br>
												<?php _e('If you change Global currency, the available currencies will be deleted and you have to correct all your Price schemes.', 'ecalypse-rental');?>
											</p>
								    </div>
								  </div>
								  
								  <!-- Available currencies //-->
								  <div class="form-group disabled">
								    <label class="col-sm-3 control-label"><?php _e('Other currencies available', 'ecalypse-rental');?></label>
								    <div class="col-sm-9 ecalypse-rental-av-currencies-div">
										<h4 style="color: #000;margin-top: 0px; margin-bottom: 20px;">This feature is available in full version of Ecalypse Rental Plugin. <a href="https://wp.ecalypse.com/" target="_blank">Get it here</a></h4>
								    	<?php $av_currencies = array(); ?>
								    	<?php if ($currency && !empty($currency) && strlen($currency) == 3) { ?>
									    	<div id="ecalypse-rental-av-currencies-insert"></div>
									    	<div id="ecalypse-rental-av-currencies">
									    		<div class="row">
		  											<div class="col-xs-1 text-right"><h4>1</h4></div>
													<div class="col-xs-3"><input type="text" name="" disabled="disabled" class="form-control" size="5" placeholder="USD, EUR, ..."></div>
									    			<div class="col-xs-1 text-center"><h4>=</h4></div>
													<div class="col-xs-3"><input type="text" name="" disabled="disabled" class="form-control" size="5" placeholder="5, 12.5, ..."></div>
									    			<div class="col-xs-1"><h4><?= $currency ?></h4></div>
									    		</div>
										    </div>
										    <?php if ($av_currencies && !empty($av_currencies)) { ?>
									    		<?php foreach ($av_currencies as $cc => $rate) { ?>
									    			<div class="row">
			  											<div class="col-xs-1 text-right"><h4>1</h4></div>
														<div class="col-xs-3"><input type="text" name="" disabled="disabled" class="form-control" size="5" placeholder="USD, EUR, ..." value="<?= $cc ?>"></div>
										    			<div class="col-xs-1 text-center"><h4>=</h4></div>
														<div class="col-xs-3"><input type="text" name="" disabled="disabled" class="form-control" size="5" placeholder="5, 12.5, ..." value="<?= $rate ?>"></div>
										    			<div class="col-xs-1"><h4><?= $currency ?></h4></div>
										    		</div>
									    		<?php } ?>
									    	<?php } ?>
										    <p class="help-block">
													<?php _e('You can set-up another available currencies. Fill the 3 letter currency code to first input and appropriate currency exchange to your Global currency.', 'ecalypse-rental');?>
													<strong><?php _e('Example', 'ecalypse-rental');?></strong>: <em>1 EUR = 1.35 USD</em> <?php _e('OR', 'ecalypse-rental');?> <em>1 USD = 0.75 EUR</em>
												</p>
												<a href="javascript:void(0);" id="ecalypse-rental-add" class="btn btn-info btn-xs"><?php _e('Add another Currency', 'ecalypse-rental');?></a>
											<?php } else { ?>
							  				<div class="alert alert-warning" role="alert">
													<?php _e('You can set-up more available currencies, but first, set-up Global Currency.', 'ecalypse-rental');?>
												</div>
							  			<?php } ?>
								    </div>
								  </div>
								  
								  <?php $vat_settings = unserialize(get_option('ecalypse_rental_vat_settings')); ?>
								  <!-- VAT //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-vat" class="col-sm-3 control-label"><?php _e('TAX 1', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<div class="input-group">
												  <input type="text" name="vat" class="form-control" id="ecalypse-rental-vat" value="<?= (isset($vat_settings['vat']) ? $vat_settings['vat'] : '') ?>">
									    		<span class="input-group-addon">%</span>
												</div>
									    </div>
									  </div>
									  
									  <!-- VAT 2 //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-vat2" class="col-sm-3 control-label"><?php _e('TAX 2', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<div class="input-group">
												  <input type="text" name="vat_2" class="form-control" id="ecalypse-rental-vat2" value="<?= (isset($vat_settings['vat_2']) ? $vat_settings['vat_2'] : '') ?>">
									    		<span class="input-group-addon">%</span>
												</div>
									    </div>
									  </div>
									  
									  <!-- VAT 3 //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-vat3" class="col-sm-3 control-label"><?php _e('TAX 3', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<div class="input-group">
												  <input type="text" name="vat_3" class="form-control" id="ecalypse-rental-vat3" value="<?= (isset($vat_settings['vat_3']) ? $vat_settings['vat_3'] : '') ?>">
									    		<span class="input-group-addon">%</span>
												</div>
									    </div>
									  </div>
									  
									  <!-- Method of counting vat //-->								  
								  <div class="form-group">
								    <label for="ecalypse_rental_vat_calculation" class="col-sm-3 control-label"><?php _e('VAT calculation', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline">
												<input type="radio" name="vat_calculation" value="1" <?= (($vat_settings['vat_calculation'] == 1 || !isset($vat_settings['vat_calculation'])) ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('total = base * (1 + Vat1 + Vat2 + Vat3)', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline"><input type="radio" name="vat_calculation" value="2" <?= (($vat_settings['vat_calculation'] == 2) ? 'checked="checked"' : '') ?>>
												&nbsp; <?php _e('total = base * (1 + VAT1) * (1 + VAT2) * (1 + VAT3)', 'ecalypse-rental');?>
											</label>
								    </div>
								  </div>
									  
									  <?php $showvat = get_option('ecalypse_rental_show_vat'); ?> 
									  <div class="form-group">
										<label for="ecalypse_rental_show_vat" class="col-sm-3 control-label"><?php _e('Show prices with VAT?', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="radio-inline">
													<input type="radio" name="ecalypse_rental_show_vat" value="yes" <?= (($showvat == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline"><input type="radio" name="ecalypse_rental_show_vat" value="no" <?= (($showvat == 'no') ? 'checked="checked"' : '') ?>>
													&nbsp; <?php _e('No', 'ecalypse-rental');?>
												</label>
												<p class="help-block"><?php _e('Clients will or will not see prices including VAT in the search results.', 'ecalypse-rental');?></p>
										</div>
									  </div>
									  
									   <?php $multiple_rental = get_option('ecalypse_rental_multiple_rental'); ?> 
									  <div class="form-group">
										<label for="ecalypse_rental_multiple_rental" class="col-sm-3 control-label"><?php _e('Enable multiple rental', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="radio-inline">
													<input type="radio" name="ecalypse_rental_multiple_rental" value="1" <?= (($multiple_rental == 1) ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline"><input type="radio" name="ecalypse_rental_multiple_rental" value="0" <?= (((int)$multiple_rental == 0) ? 'checked="checked"' : '') ?>>
													&nbsp; <?php _e('No', 'ecalypse-rental');?>
												</label>
											<p class="help-block">
											<?php _e('Select yes if you want to allow rent of multiple items in one booking.', 'ecalypse-rental');?>
											</p>
										</div>
									  </div>
									  
									  <?php $time_pricing_type = get_option('ecalypse_rental_time_pricing_type'); ?> 
									  <div class="form-group disabled">
										<label for="ecalypse_rental_time_pricing_type" class="col-sm-3 control-label"><?php _e('Time based pricing format', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<h4 style="color: #000;margin-top: 0px; margin-bottom: 20px;">This feature is available in full version of Ecalypse Rental Plugin. <a href="https://wp.ecalypse.com/" target="_blank">Get it here</a></h4>
											<label class="radio-inline">
												<input type="radio" name="" disabled="disabled" value="standard" <?= (($time_pricing_type == 'standard' || $time_pricing_type == '') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Standard', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline"><input disabled="disabled" type="radio" name="" value="" <?= (($time_pricing_type == 'half_day') ? 'checked="checked"' : '') ?>>
													&nbsp; <?php _e('Half day / full day', 'ecalypse-rental');?>
												</label>
											<p class="help-block">
											<?php _e('Standard = set daily pricing', 'ecalypse-rental');?><br><?php _e('Half day / full day = set am/pm and daily prices', 'ecalypse-rental');?>
											</p>
										</div>
									  </div>
									  
									   <?php $seasons_break = get_option('ecalypse_rental_seasons_break'); ?> 
									  <div class="form-group disabled">
										<label for="ecalypse_rental_seasons_break" class="col-sm-3 control-label"><?php _e('Use season breaks?', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<h4 style="color: #000;margin-top: 0px; margin-bottom: 20px;">This feature is available in full version of Ecalypse Rental Plugin. <a href="https://wp.ecalypse.com/" target="_blank">Get it here</a></h4>
											<label class="radio-inline">
												<input type="radio" name="" disabled="" value="yes" <?= (($seasons_break == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline"><input disabled="disabled" type="radio" name="" value="no" <?= (($seasons_break == 'no') ? 'checked="checked"' : '') ?>>
													&nbsp; <?php _e('No', 'ecalypse-rental');?>
												</label>
											<p class="help-block"><?php _e('To read up on how season breaks work, go to', 'ecalypse-rental');?> <a href="https://goo.gl/GwRaSQ" target="_blank"><?php _e('our solution center here', 'ecalypse-rental');?></a>.</p>
										</div>
									  </div>
									  
									  <?php $hour_pricing_after_day = get_option('ecalypse_rental_hour_pricing_after_day'); ?> 
									  <div class="form-group">
										<label for="ecalypse_rental_hour_pricing_after_day" class="col-sm-3 control-label"><?php _e('Hourly pricing after first day possible?', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="radio-inline">
													<input type="radio" name="ecalypse_rental_hour_pricing_after_day" value="yes" <?= (($hour_pricing_after_day == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline"><input type="radio" name="ecalypse_rental_hour_pricing_after_day" value="no" <?= (($hour_pricing_after_day == 'no') ? 'checked="checked"' : '') ?>>
													&nbsp; <?php _e('No', 'ecalypse-rental');?>
												</label>
											<p class="help-block"><?php _e('Note: If selected yes: if a client rents a car for 26 hours, the price will be calculated as 1 day + x hours as long as price is lower than 2 * daily price.', 'ecalypse-rental');?></p>
										</div>
									  </div>
									  
									<!-- Price for delivery //-->
									<?php $delivery_price = get_option('ecalypse_rental_delivery_price'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_delivery_price" class="col-sm-3 control-label"><?php _e('Price for vehicle delivery', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<?php if ($currency && !empty($currency) && strlen($currency) == 3) { ?>
								    		<div class="row">
			  									<div class="col-xs-3"><input type="text" name="ecalypse_rental_delivery_price" class="form-control" id="ecalypse_rental_delivery_price" value="<?= (!empty($delivery_price) ? $delivery_price : '') ?>"></div>
								    			<div class="col-xs-1"><h4><?= $currency ?></h4></div>
												</div>
												<p class="help-block"><?php _e('If customer chooses other drop off from pick up location, how much will be charged.', 'ecalypse-rental');?><br><?php _e('Please insert just a number (float possible).', 'ecalypse-rental');?></p>
								    	<?php } else { ?>
							  				<div class="alert alert-warning" role="alert">
													<?php _e('You can set-up price for car delivery, but first, set-up Global Currency.', 'ecalypse-rental');?>
												</div>
							  			<?php } ?>
								    </div>
								  </div>
								  
								  <!-- Consumption //-->
								  <?php $consumption = get_option('ecalypse_rental_consumption'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_consumption" class="col-sm-3 control-label"><?php _e('Consumption metric', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<label class="radio-inline">
											  <input type="radio" name="ecalypse_rental_consumption" id="ecalypse-rental-consumption-eu" value="eu" <?= (($consumption == 'eu') ? 'checked="checked"' : '') ?>> l / 100 km
											</label>
											<label class="radio-inline">
											  <input type="radio" name="ecalypse_rental_consumption" id="ecalypse-rental-consumption-us" value="us" <?= (($consumption == 'us') ? 'checked="checked"' : '') ?>> MPG
											</label>
											<p class="help-block"><?php _e('What metric of consumption will you use?', 'ecalypse-rental');?></p>
								    </div>
								  </div>
									
									<!-- Distance Metric //-->
								  <?php $distance_metric = get_option('ecalypse_rental_distance_metric'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_consumption" class="col-sm-3 control-label"><?php _e('Distance metric', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<label class="radio-inline">
											  <input type="radio" name="ecalypse_rental_distance_metric" id="ecalypse-rental-consumption-km" value="km" <?= (($distance_metric == 'km') ? 'checked="checked"' : '') ?>> <?php _e('kilometers', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline">
											  <input type="radio" name="ecalypse_rental_distance_metric" id="ecalypse-rental-consumption-mi" value="mi" <?= (($distance_metric == 'mi') ? 'checked="checked"' : '') ?>> <?php _e('miles', 'ecalypse-rental');?>
											</label>
											<p class="help-block"><?php _e('What metric of distance will you use?', 'ecalypse-rental');?></p>
								    </div>
								  </div>
								  
								   <!-- Overbooking //-->
								  <?php $overbooking = get_option('ecalypse_rental_overbooking'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_overbooking" class="col-sm-3 control-label"><?php _e('Allow branches overbooking?', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline">
												<input type="radio" name="ecalypse_rental_overbooking" value="yes" <?= (($overbooking == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline"><input type="radio" name="ecalypse_rental_overbooking" value="no" <?= (($overbooking == 'no') ? 'checked="checked"' : '') ?>>
												&nbsp; <?php _e('No', 'ecalypse-rental');?>
											</label>
											<p class="help-block"><?php _e('Select "yes" to make all your cars always available; select "no" to check reservation requests against cars available.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
								   
								    <!-- Free km per hour //-->
								  <?php $use_free_hour_km = get_option('ecalypse_rental_use_free_hour_km'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_use_free_hour_km" class="col-sm-3 control-label"><?php _e('Use free km/miles per hour?', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline">
												<input type="radio" name="ecalypse_rental_use_free_hour_km" value="yes" <?= (($use_free_hour_km == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline"><input type="radio" name="ecalypse_rental_use_free_hour_km" value="no" <?= (($use_free_hour_km == 'no') ? 'checked="checked"' : '') ?>>
												&nbsp; <?php _e('No', 'ecalypse-rental');?>
											</label>
											<p class="help-block"><?php _e('Select "no" to disable free km/miles per hour.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
								  
								   <!-- Detail page //-->
								  <?php $detail_page = get_option('ecalypse_rental_detail_page'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_detail_page" class="col-sm-3 control-label"><?php _e('Use fleet detail page?', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline">
												<input type="radio" name="ecalypse_rental_detail_page" value="yes" <?= (($detail_page == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline"><input type="radio" name="ecalypse_rental_detail_page" value="no" <?= (($detail_page == 'no') ? 'checked="checked"' : '') ?>>
												&nbsp; <?php _e('No', 'ecalypse-rental');?>
											</label>
											<p class="help-block"><?php _e('Select "yes" to enable fleet detail pages when clients click show details on "our cars page', 'ecalypse-rental');?>".</p>
								    </div>
								  </div>
								   
								   <!-- Call for price //-->
								  <?php $detail_page = get_option('ecalypse_rental_call_for_price'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_call_for_price" class="col-sm-3 control-label"><?php _e('Show call for price?', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline">
												<input type="radio" name="ecalypse_rental_call_for_price" value="yes" <?= (($detail_page == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline"><input type="radio" name="ecalypse_rental_call_for_price" value="no" <?= (($detail_page == 'no') ? 'checked="checked"' : '') ?>>
												&nbsp; <?php _e('No', 'ecalypse-rental');?>
											</label>
											<p class="help-block"><?php _e('Select "yes" to show clicable text "call for price" instead of "not available" if a vehicle is not available.', 'ecalypse-rental');?>".</p>
								    </div>
								  </div>
								   
								    <!-- Disable Time //-->
								  <?php $disable_time = get_option('ecalypse_rental_disable_time'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_disable_time" class="col-sm-3 control-label"><?php _e('Disable time selectors', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline">
												<input type="radio" name="ecalypse_rental_disable_time" value="yes" <?= (($disable_time == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline"><input type="radio" name="ecalypse_rental_disable_time" value="no" <?= (($disable_time == 'no') ? 'checked="checked"' : '') ?>>
												&nbsp; <?php _e('No', 'ecalypse-rental');?>
											</label>
											<p class="help-block"><?php _e('Select "yes" show only pickup and return dates without time selectors.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
									
								  <!-- Ecalypse compatible theme //-->
								  <?php $compatible_theme = get_option('ecalypse_rental_compatible_theme'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_compatible_theme" class="col-sm-3 control-label"><?php _e('Are you using an Ecalypse theme?', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline">
												<input type="radio" name="ecalypse_rental_compatible_theme" value="yes" <?= (($compatible_theme == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline"><input type="radio" name="ecalypse_rental_compatible_theme" value="no" <?= (($compatible_theme == 'no') ? 'checked="checked"' : '') ?>>
												&nbsp; <?php _e('No', 'ecalypse-rental');?>
											</label>
											<p class="help-block"><?php _e('Change this to "No" if you are using a theme that was not made by Ecalypse specifically for this plugin.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
								  
									<!-- Any location //-->
								  <?php $anylocation = get_option('ecalypse_rental_any_location_search'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_any_location_search" class="col-sm-3 control-label"><?php _e('Any location search?', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline">
												<input type="radio" name="ecalypse_rental_any_location_search" value="yes" <?= (($anylocation == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
											</label>
											<label class="radio-inline"><input type="radio" name="ecalypse_rental_any_location_search" value="no" <?= (($anylocation == 'no') ? 'checked="checked"' : '') ?>>
												&nbsp; <?php _e('No', 'ecalypse-rental');?>
											</label>
											<p class="help-block"><?php _e('Clients can search cars independent of the branch they are assigned to.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
									
									<!-- Webhook URL //-->
								  <?php $webhook_url = get_option('ecalypse_rental_webhook_url'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_webhook_url" class="col-sm-3 control-label"><?php _e('Webhook URL', 'ecalypse-rental');?></label>
									<div class="col-sm-9">
								    
										<input type="text" class="form-control" name="ecalypse_rental_webhook_url" id="ecalypse_rental_webhook_url" value="<?php echo $webhook_url;?>">
									
										<p class="help-block"><?php _e('Set webhook URL where the data are send after booking is done.', 'ecalypse-rental');?></p>
									</div>
								  </div>
									
									<!-- Automatic reminder //-->
								  <?php $automatic_reminder = get_option('ecalypse_rental_reminder_days'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_reminder_days" class="col-sm-3 control-label"><?php _e('Automatic reminder', 'ecalypse-rental');?></label>
									<div class="col-sm-9">
								    
										<input type="text" class="form-control" name="ecalypse_rental_reminder_days" id="ecalypse_rental_reminder_days" value="<?php echo $automatic_reminder;?>">
									
										<p class="help-block"><?php _e('Set number of days before booking enter date.', 'ecalypse-rental');?></p>
									</div>
								  </div>
									
									<!-- Thank you email//-->
								  <?php $ty_days = get_option('ecalypse_rental_thank_you_days'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_thank_you_days" class="col-sm-3 control-label"><?php _e('Thank you email', 'ecalypse-rental');?></label>
									<div class="col-sm-9">
								    
										<input type="text" class="form-control" name="ecalypse_rental_thank_you_days" id="ecalypse_rental_reminder_days" value="<?php echo $ty_days;?>">
									
										<p class="help-block"><?php _e('Set number of days after return the car.', 'ecalypse-rental');?></p>
									</div>
								  </div>
								  	
									<!-- Min before days //-->
									<?php $min_before_days = get_option('ecalypse_rental_min_before_days'); ?>
									<div class="form-group">
										<label for="ecalypse_rental_min_before_days" class="col-sm-3 control-label"><?php _e('Earliest book time(days)', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<input type="text" name="ecalypse_rental_min_before_days" class="form-control" id="ecalypse_rental_min_before_days" value="<?= (!empty($min_before_days) ? $min_before_days : '') ?>">
											<p class="help-block"><?php _e('Set the earliest day of booking or leave blank (if you set value to 2, clients booking on January 1st will be able to make booking earliest for Jan-3rd).', 'ecalypse-rental');?></p>
										</div>
									</div>

									<!-- Max before days //-->
									<?php $max_before_days = get_option('ecalypse_rental_max_before_days'); ?>
									<div class="form-group">
										<label for="ecalypse_rental_max_before_days" class="col-sm-3 control-label"><?php _e('Latest book time(days)', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<input type="text" name="ecalypse_rental_max_before_days" class="form-control" id="ecalypse_rental_max_before_days" value="<?= (!empty($max_before_days) ? $max_before_days : '') ?>">
											<p class="help-block"><?php _e('Set the latest day for booking or leave blank for unrestricted (if you set to 20, clients booking on January 1st will be able to make booking latest until Jan-20th).', 'ecalypse-rental');?></p>
										</div>
									</div>
											  
								  <!-- PayPal //-->
								  <?php $paypal = get_option('ecalypse_rental_paypal'); ?>
								  <div class="form-group">
								    <label for="ecalypse_rental_paypal" class="col-sm-3 control-label"><?php _e('PayPal settings', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
								    	<input type="text" name="ecalypse_rental_paypal" class="form-control" id="ecalypse_rental_paypal" value="<?= (!empty($paypal) ? $paypal : '') ?>">
								    	<p class="help-block"><?php _e('Please, insert your PayPal e-mail for receiving payments.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
									
									<!-- Require payment //-->
								  <?php $require_payment = get_option('ecalypse_rental_require_payment'); ?>
								  <div class="form-group">
								    <label for="ecalypse-rental-payment" class="col-sm-3 control-label"><?php _e('Require payment with booking?', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline"><input type="radio" name="ecalypse_rental_require_payment" value="yes" <?= (($require_payment == 'yes') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Yes', 'ecalypse-rental');?></label>
											<label class="radio-inline"><input type="radio" name="ecalypse_rental_require_payment" value="no" <?= (($require_payment == 'no') ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('No', 'ecalypse-rental');?></label>
											<p class="help-block"><?php _e('The last step for user will be Checkout with PayPal.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
									
									<!-- Where to send emails //-->
								  <?php $book_send_email = get_option('ecalypse_rental_book_send_email'); ?>
								  <?php if (empty($book_send_email)) { $book_send_email = array('client' => 1, 'admin' => 1); } else { $book_send_email = unserialize($book_send_email); } ?>									
								  <div class="form-group">
								    <label for="ecalypse-rental-payment" class="col-sm-3 control-label"><?php _e('Confirmation emails', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
									    <label class="radio-inline"><input type="checkbox" name="ecalypse_rental_book_send_email[client]" value="1" <?= ((!isset($book_send_email['client']) || $book_send_email['client'] == 1) ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Send to client', 'ecalypse-rental');?></label>
										<label class="radio-inline"><input type="checkbox" name="ecalypse_rental_book_send_email[admin]" value="1" <?= ((!isset($book_send_email['admin']) || $book_send_email['admin'] == 1) ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Send to admin', 'ecalypse-rental');?></label>
										<div>
											<label class="radio-inline"><input type="checkbox" name="ecalypse_rental_book_send_email[other]" value="1" <?= ((!isset($book_send_email['other']) || $book_send_email['other'] == 1) ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Send to other email:', 'ecalypse-rental');?></label>
											<input type="text" name="ecalypse_rental_book_send_email[other_email]" style="display: inline-block; margin-left: 15px;margin-top: 5px; position: relative; top: 5px;" value="<?= (isset($book_send_email['other_email']) ? $book_send_email['other_email'] : '') ?>">
										</div>
										<p class="help-block"><?php _e('Where to send email confirmation after booking.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
									
									<!-- Dump data email//-->
									<?php $dump_data_email = get_option('ecalypse_rental_dump_data_email'); ?>
									<div class="form-group">
										<label for="ecalypse_rental_dump_data_email" class="col-sm-3 control-label"><?php _e('Dump data email', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<input type="text" name="ecalypse_rental_dump_data_email" class="form-control" id="ecalypse_rental_dump_data_email" value="<?= (!empty($dump_data_email) ? $dump_data_email : '') ?>">
											<p class="help-block"><?php _e('Set email where all booking data will be send after booking is finished. Leave blank to disable this functionality.', 'ecalypse-rental');?></p>
										</div>
									</div>
									
									<!-- Allowed days //-->
								  <?php $allowed_days = get_option('ecalypse_rental_allowed_days', ''); ?>
								  <?php if (empty($allowed_days)) { $allowed_days = array(0 => 1, 1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1, 6 => 1); } else { $allowed_days = unserialize($allowed_days); } ?>
								  <div class="form-group">
								    <label for="ecalypse-rental-allowed_days" class="col-sm-3 control-label"><?php _e('Allowed days in the calendar', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
										<?php $days = array(0 => __('Sunday', 'ecalypse-rental'), 1 => __('Monday', 'ecalypse-rental'), 2 => __('Tuesday', 'ecalypse-rental'), 3 => __('Wednesday', 'ecalypse-rental'), 4 => __('Thursday', 'ecalypse-rental'), 5 => __('Friday', 'ecalypse-rental'), 6 => __('Saturday', 'ecalypse-rental')); ?>
										<?php foreach ($days as $k => $v) { ?>
											<label class="radio-inline"><input type="checkbox" name="ecalypse_rental_allowed_days[<?php echo $k;?>]" value="1" <?= ((isset($allowed_days[$k]) && $allowed_days[$k] == 1) ? 'checked="checked"' : '') ?>>&nbsp; <?php echo $v;?></label>
										<?php } ?>
											<p class="help-block"><?php _e('Use this setting to disable days from booking form (days will be greyed out in the calendar). Clients will not be able to book cars on these days.', 'ecalypse-rental');?></p>
								    </div>
								  </div>
								
										<!-- Default pickup time //-->
								  <?php $default_pickup_time = get_option('default_enter_time', ''); ?>
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Default pick-up time', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<select name="default_enter_time" class="form-control" style="width:200px;">
												<option value=""><?php _e('Not set', 'ecalypse-rental');?></option>
												<?php for ($i = 0;$i<=23;$i++) { ?>
												<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>:00"<?= (str_pad($i, 2, '0', STR_PAD_LEFT).':00' == $default_pickup_time) ? ' selected="selected"' : '' ?>><?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>:00</option>
												<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>:30"<?= (str_pad($i, 2, '0', STR_PAD_LEFT).':30' == $default_pickup_time) ? ' selected="selected"' : '' ?>><?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>:30</option>
												<?php } ?>
										  </select>
										</div>
									  </div>
										
											<!-- Default return time //-->
								  <?php $default_return_time = get_option('default_return_time', ''); ?>
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Default return time', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<select name="default_return_time" class="form-control" style="width:200px;">
												<option value=""><?php _e('Not set', 'ecalypse-rental');?></option>
												<?php for ($i = 0;$i<=23;$i++) { ?>
												<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>:00"<?= (str_pad($i, 2, '0', STR_PAD_LEFT).':00' == $default_return_time) ? ' selected="selected"' : '' ?>><?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>:00</option>
												<option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>:30"<?= (str_pad($i, 2, '0', STR_PAD_LEFT).':30' == $default_return_time) ? ' selected="selected"' : '' ?>><?php echo str_pad($i, 2, '0', STR_PAD_LEFT);?>:30</option>
												<?php } ?>
										  </select>
										</div>
									  </div>
									
									<!-- Disclaimer //-->
								  <div class="form-group">
									<label for="ecalypse-rental-disclaimer" class="col-sm-3 control-label"><?php _e('Disclaimer', 'ecalypse-rental');?></label>
									<div class="col-sm-9">

										<ul class="nav nav-tabs" role="tablist">
											  <li role="presentation" class="active"><a href="javascript:void(0);" class="edit_disclaimer" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
											  <?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
												<?php if ($available_languages && !empty($available_languages)) { ?>
													<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
													<li role="presentation"><a href="javascript:void(0);" class="edit_disclaimer" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
													<?php } ?>
											  <?php } ?>
											</ul>

											<?php $disclaimer = get_option('ecalypse_rental_disclaimer');?>
											<?php $disclaimer = unserialize($disclaimer); ?>
											<?php if ($disclaimer == false) { $disclaimer['gb'] = ''; } ?>
											
											<textarea class="form-control disclaimer disclaimer_gb" name="ecalypse_rental_disclaimer[gb]" id="ecalypse-rental-disclaimer" rows="3" placeholder="Brief disclaimer in English (GB)."><?= ((isset($disclaimer['gb']) && !empty($disclaimer['gb'])) ? $disclaimer['gb'] : '') ?></textarea>
											<?php if ($available_languages && !empty($available_languages)) { ?>
												<?php foreach ($available_languages as $key => $val) { ?>
												<?php if ($val['country-www'] == 'gb') {continue;} ?>
												<textarea class="form-control disclaimer disclaimer_<?= strtolower($val['country-www']) ?>" name="ecalypse_rental_disclaimer[<?= strtolower($val['country-www']) ?>]" rows="3" placeholder="Brief disclaimer in <?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)."><?= ((isset($disclaimer[strtolower($val['country-www'])]) && !empty($disclaimer[strtolower($val['country-www'])])) ? $disclaimer[strtolower($val['country-www'])] : '') ?></textarea>
											<?php } ?>
											<?php } ?>
										<p class="help-block"><?php _e('This is shown before "book now" button on checkout page.', 'ecalypse-rental');?></p>
									</div>
								  </div>
									
								  <!-- booking statuses //-->
								  <?php $booking_statuses = maybe_unserialize(get_option('ecalypse_rental_booking_statuses')); ?>
								  <h4><?php _e('Booking statuses after type of payment', 'ecalypse-rental');?></h4>
								  <div class="form-group">
								    <label class="col-sm-3 control-label"><?php _e('Offline payment (bank transfer, cash, etc.)', 'ecalypse-rental');?></label>
								    <div class="col-sm-9">
											<select name="ecalypse_rental_booking_statuses[offline]" id="booking-statuses" class="form-control" style="width:200px;">
												<?php foreach (EcalypseRental_Admin::$booking_statuses as $k => $v) { ?>
													<option value="<?php echo $k;?>"<?= ((isset($booking_statuses['offline']) && $booking_statuses['offline']  == $k) ? ' selected="selected"' : '') ?>><?php echo $v;?></option>
												<?php } ?>
										  </select>
										</div>
								  </div>
								    	
								  <!-- Submit //-->
								  <div class="form-group">
								  	<div class="col-sm-offset-3 col-sm-9">
								  		<button type="submit" name="edit_settings" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save Settings', 'ecalypse-rental');?></button>
								  	</div>
									</div>
									
								</div>
							</div>
							<!-- .row //-->
							
						</form>
					
					</div>
					<!-- .panel-body //-->
				</div>
				<!-- .panel .panel-default //-->
				
				<!-- INFO //-->
				<div class="panel panel-default">
					<div class="panel-heading"><h4><?php _e('Visual settings', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
					  <form action="" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
						  <?php wp_nonce_field( 'settings-theme'); ?>
							<?php $theme_options = unserialize(get_option('ecalypse_rental_theme_options')); ?>
							
							<div class="row">
								<div class="col-md-6">
									 <div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Reservation phone number', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<input type="text" name="phone_number" class="form-control" id="phone-number" placeholder="e.g. 000 800-100-200" value="<?php if (isset($theme_options['phone_number'])) { echo $theme_options['phone_number']; } ?>">
										</div>
									  </div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Date format', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<select name="date_format" id="date-format" class="form-control" style="width:200px;">
												<option value="yyyy-mm-dd"<?= ((isset($theme_options['date_format']) && $theme_options['date_format']  == 'yyyy-mm-dd') ? ' selected="selected"' : '') ?>>yyyy-mm-dd (2014-06-15)</option>
												<option value="dd.mm.yyyy"<?= ((isset($theme_options['date_format']) && $theme_options['date_format']  == 'dd.mm.yyyy') ? ' selected="selected"' : '') ?>>dd.mm.yyyy (15.06.2014)</option>
												<option value="mm/dd/yyyy"<?= ((isset($theme_options['date_format']) && $theme_options['date_format']  == 'mm/dd/yyyy') ? ' selected="selected"' : '') ?>>mm/dd/yyyy (06/15/2014)</option>
												<option value="dd-M-yyyy"<?= ((isset($theme_options['date_format']) && $theme_options['date_format']  == 'dd-M-yyyy') ? ' selected="selected"' : '') ?>>dd-M-yyyy (15-Jun-2014)</option>
												<option value="M-dd-yyyy"<?= ((isset($theme_options['date_format']) && $theme_options['date_format']  == 'M-dd-yyyy') ? ' selected="selected"' : '') ?>>M-dd-yyyy (Jun-15-2014)</option>
										  </select>
										</div>
									  </div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Time format', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<select name="time_format" id="time-format" class="form-control" style="width:200px;">
												<option value="24"<?= ((isset($theme_options['time_format']) && $theme_options['time_format']  == '24') ? ' selected="selected"' : '') ?>><?php _e('24 hours', 'ecalypse-rental');?></option>
												<option value="12"<?= ((isset($theme_options['time_format']) && $theme_options['time_format']  == '12') ? ' selected="selected"' : '') ?>><?php _e('12 hours', 'ecalypse-rental');?></option>
										  </select>
										</div>
									  </div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('First day of the week', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<select name="date_format_first_day" id="date-format-first-day" class="form-control" style="width:150px;">
												<option value="0"<?= ((isset($theme_options['date_format_first_day']) && (int)$theme_options['date_format_first_day']  == 0) ? ' selected="selected"' : '') ?>><?php _e('Sunday', 'ecalypse-rental');?></option>
												<option value="1"<?= ((isset($theme_options['date_format_first_day']) && (int)$theme_options['date_format_first_day']  == 1) ? ' selected="selected"' : '') ?>><?php _e('Monday', 'ecalypse-rental');?></option>
										  </select>
										</div>
									  </div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Display car available button', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<select name="car_available_button" id="car-available-button" class="form-control" style="width:100px;">
												<option value="0"<?= ((isset($theme_options['car_available_button']) && $theme_options['car_available_button']  == 0) ? ' selected="selected"' : '') ?>><?php _e('No', 'ecalypse-rental');?></option>
												<option value="1"<?= ((isset($theme_options['car_available_button']) && $theme_options['car_available_button']  == 1) ? ' selected="selected"' : '') ?>><?php _e('Yes', 'ecalypse-rental');?></option>
										  </select>
										</div>
									  </div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Display Return location', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<select name="display_return_location" id="return-location" class="form-control" style="width:100px;">
												<option value="1"<?= ((isset($theme_options['display_return_location']) && $theme_options['display_return_location']  == 1) ? ' selected="selected"' : '') ?>><?php _e('Yes', 'ecalypse-rental');?></option>	
												<option value="0"<?= ((isset($theme_options['display_return_location']) && $theme_options['display_return_location']  == 0) ? ' selected="selected"' : '') ?>><?php _e('No', 'ecalypse-rental');?></option>
										  </select>
										</div>
									  </div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Default sort by value', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<select name="default_sort_by" id="car-default-sort-by" class="form-control" style="width:100px;">
												<option value="ordering"<?= ((isset($theme_options['default_sort_by']) && $theme_options['default_sort_by']  == 'ordering') ? ' selected="selected"' : '') ?>><?php _e('Admin order', 'ecalypse-rental');?></option>
												<option value="name"<?= ((isset($theme_options['default_sort_by']) && $theme_options['default_sort_by']  == 'name') ? ' selected="selected"' : '') ?>><?php _e('Name', 'ecalypse-rental');?></option>
												<option value="price"<?= ((isset($theme_options['default_sort_by']) && $theme_options['default_sort_by']  == 'price') ? ' selected="selected"' : '') ?>><?php _e('Price', 'ecalypse-rental');?></option>
										  </select>
										</div>
									  </div>
									<h4><?php _e('Filters in search results', 'ecalypse-rental');?></h4>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Price range', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="control-label">
												<input type="checkbox" name="filter_price_range" value="1" <?php if (isset($theme_options['filter_price_range']) && $theme_options['filter_price_range'] == 1) { ?>checked<?php } ?>> <?php _e('Show filter: Price range', 'ecalypse-rental');?>
											  </label>
											<div style="margin-top: 15px;">
												<label><?php _e('min. price:', 'ecalypse-rental');?></label> <input style="width:75px;" type="text" name="filter_price_range_min" value="<?php echo (isset($theme_options['filter_price_range_min']) && (int)$theme_options['filter_price_range_min'] >= 0) ? (int)$theme_options['filter_price_range_min'] : 0; ?>">
												<label style="margin-left:10px;"><?php _e('max. price:', 'ecalypse-rental');?></label> <input style="width:75px;" type="text" name="filter_price_range_max" value="<?php echo (isset($theme_options['filter_price_range_max']) && (int)$theme_options['filter_price_range_max'] >= 0) ? (int)$theme_options['filter_price_range_max'] : 500; ?>">
												<p class="help-block"><?php _e('This sets the maximum and minimum values of your price filter shown on the front end. Insert values in your default currency. When clients change currency, values will automatically recalculate according to exchange rates set in settings.', 'ecalypse-rental');?></p>
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Extras', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="control-label">
												<input type="checkbox" name="filter_extras" value="1" <?php if (isset($theme_options['filter_extras']) && $theme_options['filter_extras'] == 1) { ?>checked<?php } ?>> <?php _e('Show filter: Extras', 'ecalypse-rental');?>
											  </label>
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Fuel', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="control-label">
												<input type="checkbox" name="filter_fuel" value="1" <?php if (isset($theme_options['filter_fuel']) && $theme_options['filter_fuel'] == 1) { ?>checked<?php } ?>> <?php _e('Show filter: Fuel', 'ecalypse-rental');?>
											  </label>
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Number of passengers', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="control-label">
												<input type="checkbox" name="filter_passengers" value="1" <?php if (isset($theme_options['filter_passengers']) && $theme_options['filter_passengers'] == 1) { ?>checked<?php } ?>> <?php _e('Show filter: Number of passengers', 'ecalypse-rental');?>
											  </label>
											<div style="margin-top: 15px;">
												<label><?php _e('min. number of passengers:', 'ecalypse-rental');?></label> <input style="width:75px;" type="text" name="filter_passengers_range_min" value="<?php echo (isset($theme_options['filter_passengers_range_min']) && (int)$theme_options['filter_passengers_range_min'] >= 0) ? (int)$theme_options['filter_passengers_range_min'] : 1; ?>">
												<label style="margin-left:10px;"><?php _e('max. number of passengers:', 'ecalypse-rental');?></label> <input style="width:75px;" type="text" name="filter_passengers_range_max" value="<?php echo (isset($theme_options['filter_passengers_range_max']) && (int)$theme_options['filter_passengers_range_max'] >= 0) ? (int)$theme_options['filter_passengers_range_max'] : 8; ?>">
												<p class="help-block"><?php _e('This sets the maximum and minimum values of your "number of passengers" filter shown on the front end.', 'ecalypse-rental');?></p>
											</div>
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Vehicle categories', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="control-label">
												<input type="checkbox" name="filter_vehicle_categories" value="1" <?php if (isset($theme_options['filter_vehicle_categories']) && $theme_options['filter_vehicle_categories'] == 1) { ?>checked<?php } ?>> <?php _e('Show filter: Vehicle categories', 'ecalypse-rental');?>
											  </label>
										</div>
									</div>
									
									<div class="form-group">
										<label class="col-sm-3 control-label"><?php _e('Vehicle names', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<label class="control-label">
												<input type="checkbox" name="filter_vehicle_names" value="1" <?php if (isset($theme_options['filter_vehicle_names']) && $theme_options['filter_vehicle_names'] == 1) { ?>checked<?php } ?>> <?php _e('Show filter: Vehicle names', 'ecalypse-rental');?>
											  </label>
										</div>
									</div>
									
									<?php
									$ecalypse_rental_transform_array = array('our_cars_page','our_locations_page','manage_booking_page','show_subheader_text','show_breadcrumb','picture_homepage','picture_otherpages','picture_logo','theme_colors');
									foreach ($ecalypse_rental_transform_array as $name) {
										if (isset($theme_options[$name])) {
											if (is_array($theme_options[$name])) {
												foreach ($theme_options[$name] as $k => $v) { ?>
													<input type="hidden" name="<?php echo $name; ?>[<?php echo $k;?>]" value="<?php echo $v; ?>">
												<?php }
											} else {
											?>
											<input type="hidden" name="<?php echo $name; ?>" value="<?php echo $theme_options[$name]; ?>">
										<?php
											}
										}
									}
									?>
											
									<!-- Submit //-->
								  <div class="form-group">
								  	<div class="col-sm-offset-3 col-sm-9">
								  		<button type="submit" name="edit_visual_settings" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save Visual Settings', 'ecalypse-rental');?></button>
								  	</div>
									</div>
									
								</div>
							</div>
							
						</form>
					</div>
				</div><!-- .panel //-->
				
				<div class="panel panel-default disabled">
					<div class="panel-heading"><h4 id="global-scheme-replace"><?php _e('Minimum bookingtime per month (in days)', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
					  
						<div class="row">
							<div class="col-md-12">
								<h4 style="color: #000;margin-top: 0px; margin-bottom: 20px;">This feature is available in full version of Ecalypse Rental Plugin. <a href="https://wp.ecalypse.com/" target="_blank">Get it here</a></h4>
								<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>#min-booking-time" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
									<?php wp_nonce_field( 'settings-min-booking-time'); ?>
									<?php $min_rental_times =  array(); ?>
									<?php if (!is_array($min_rental_times)) { $min_rental_times = array();} ?>
									<?php for ($i=1;$i<=12;$i++) { ?>
										<?php $dt = DateTime::createFromFormat('!m', $i); ?>
									<div class="row" style="margin-bottom:5px">
											<div class="col-md-1">
												<label for="min_rental_time_<?php echo $i;?>"><?php echo $dt->format('F');?></label>
											</div>
											<div class="col-md-1">
												<input type="text" name="" disabled="disabled" class="form-control" id="min_rental_time_<?php echo $i;?>" value="<?= (isset($min_rental_times[$i]) ? $min_rental_times[$i] : '') ?>">
											</div>
										</div>
									<?php } ?>
									
									<div class="row">
										<div class="col-md-4">
											
											<!-- Submit //-->
											<br><button disabled="disabled" disabled="disabled" type="submit" name="" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Save minimum booking times per month', 'ecalypse-rental');?></button>
										
										</div>
									</div>
									
									
								</form>
							</div>
						</div>
					</div>
				</div>
				
				<div class="panel panel-default">
					<div class="panel-heading"><h4 id="global-scheme-replace"><?php _e('Global price scheme replace', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
					  
						<div class="row">
							<div class="col-md-12">
								<p class="help-block"><?php _e('Replaces all instances of "Original price scheme" with selected scheme.', 'ecalypse-rental');?></p>
								<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>#vehicle-categories" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
									<?php wp_nonce_field( 'settings-scheme-replace'); ?>
									<div class="row">
										<div class="col-md-4">
											<label for="price_scheme_original"><?php _e('Original price scheme', 'ecalypse-rental');?></label>
											<select name="price_scheme_original" id="price_scheme_original" class="form-control">
									    	<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
									    	<?php if (isset($pricing) && !empty($pricing)) { ?>
										    	<?php foreach ($pricing as $key => $val) { ?>
										    		<option value="<?= $val->id_pricing ?>" <?= (($edit == true && $detail->global_pricing_scheme == $val->id_pricing) ? 'selected="selected"' : '') ?>><?= $val->name ?></option>
										    	<?php } ?>
										    <?php } ?>
								    	</select>
										</div>
									
										<div class="col-md-4">
											<label for="price_scheme_new"><?php _e('Replace for this price scheme', 'ecalypse-rental');?></label>
											<select name="price_scheme_new" id="price_scheme_new" class="form-control">
									    	<option value="0">- none -</option>
									    	<?php if (isset($pricing) && !empty($pricing)) { ?>
										    	<?php foreach ($pricing as $key => $val) { ?>
										    		<option value="<?= $val->id_pricing ?>" <?= (($edit == true && $detail->global_pricing_scheme == $val->id_pricing) ? 'selected="selected"' : '') ?>><?= $val->name ?></option>
										    	<?php } ?>
										    <?php } ?>
								    	</select>
										</div>
									</div>
									
									<div class="row">
										<div class="col-md-4">
											
											<!-- Submit //-->
										  <br><button type="submit" name="replace_price_scheme" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Replace Price Scheme', 'ecalypse-rental');?></button>
										
										</div>
									</div>
									
									
								</form>
								
							</div>
						</div>
					</div>
				</div>
							
				
				<div class="panel panel-default">
					<div class="panel-heading"><h4 id="vehicle-categories"><?php _e('Vehicle Categories', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
					  
						<div class="row">
							<div class="col-md-12">
								
								<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
									<li role="presentation" class="active"><a href="javascript:void(0);" class="categories_categories_switcher" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
									<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
									<?php if ($available_languages && !empty($available_languages)) { ?>														
										<?php foreach ($available_languages as $key => $val) { ?>
											<?php if ($val['country-www'] == 'gb') {continue;} ?>
											<li role="presentation"><a href="javascript:void(0);" class="categories_categories_switcher" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
										<?php } ?>
									<?php } ?>
								</ul>
								
								<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>#vehicle-categories" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
									<?php wp_nonce_field( 'settings-categories'); ?>
									<!-- Vehicle categories //-->
								  <table class="table" id="ecalypse-rental-vehicle-categories">
							      <thead>
							        <tr>
							          <th>#</th>
							          <th><?php _e('Name', 'ecalypse-rental');?></th>
							          <th><?php _e('Current Picture', 'ecalypse-rental');?></th>
							          <th><?php _e('New picture', 'ecalypse-rental');?></th>
									  <th>S<?php _e('hortcode', 'ecalypse-rental');?></th>
							          <th><?php _e('No. of vehicles', 'ecalypse-rental');?></th>
							          <th><?php _e('Delete', 'ecalypse-rental');?></th>
							        </tr>
							      </thead>
							      <tbody>
							      	
							      	<?php if ($vehicle_categories && !empty($vehicle_categories)) { ?>
								    		<?php foreach ($vehicle_categories as $key => $val) { ?>
								    			<tr>
								    				<td><?= $val->id_category ?></td>
								    				<td>
														<div class="ecalypse_rental_categories_translations" data-lng="gb">
															<input type="text" name="vehicle_categories_name[<?= $val->id_category ?>]" class="form-control" value="<?= $val->name ?>">
														</div>
														<?php $name_translations = unserialize($val->name_translations);?>
														<?php if (empty($name_translations)) { $name_translations = array(); } ?>
														<?php if ($available_languages && !empty($available_languages)) { ?>
															<?php foreach ($available_languages as $lkey => $lval) { ?>
																<?php if ($lval['country-www'] == 'gb') {continue;} ?>
																	<div class="ecalypse_rental_categories_translations" data-lng="<?php echo $lval['country-www'];?>" style="display:none;">																		
																		<input type="text" name="vehicle_categories_name_translations[<?= $val->id_category ?>][<?php echo $lval['country-www'];?>]" placeholder="Name in <?php echo $lval['lang-native'];?>" class="form-control" value="<?= ((isset($name_translations[$lval['country-www']])) ? $name_translations[$lval['country-www']] : '') ?>">
																	</div>
															<?php } ?>
														<?php } ?>
														</td>
								    				<td class="text-center">
								    					<?php if (!empty($val->picture)) { ?>
									    					<img src="<?= $val->picture ?>" height="80">
									    				<?php } else { ?>
									    					<em>- <?php _e('none', 'ecalypse-rental');?> -</em>
									    				<?php } ?>
									    				<input type="hidden" name="vehicle_categories_picture[<?= $val->id_category ?>]" class="form-control" value="<?= $val->picture ?>">
								    				</td>
								    				<td>
								    					<input type="file" name="vehicle_categories_file[<?= $val->id_category ?>]">
								    				</td>
													<td>
								    					[ecalypse_rental_category id="<?= $val->id_category ?>"]
								    				</td>
								    				<td class="text-center"><?= $val->no_vehicles ?></td>
								    				<td>
								    					<?php if ($val->no_vehicles == 0) { ?>
																<div class="checkbox">
															    <label>
															      <input type="checkbox" name="vehicle_categories_delete[<?= $val->id_category ?>]" value="1">&nbsp;&nbsp;<?php _e('Delete', 'ecalypse-rental');?>
															    </label>
															  </div>
															<?php } ?>
														</td>
								    			</tr>
								    		<?php } ?>
								    	<?php } ?>
								    	
				      			</tbody>
				      		</table>
									
								  <!-- Submit //-->
								  <button type="submit" name="update_vehicle_categories" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save Vehicle Categories', 'ecalypse-rental');?></button>
								  
								</form>
								
						  </div>
						</div>
						<!-- .row //-->
						
						<div class="row">
							<div class="col-md-12">
								<br>
							</div>
						</div>
						
						<div class="row">
							<div class="col-md-6">
						  	
						  	<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>#vehicle-categories" method="post" role="form" enctype="multipart/form-data">
									<?php wp_nonce_field( 'settings-categories-new'); ?>
									<h4>Add vehicle category</h4>
									
									<p class="help-block">
										<?php _e('Insert your Vehicle Categories before you create your Fleet. You can also add illustrative picture.', 'ecalypse-rental');?>
										<br><?php _e('You can\'t delete Category with vehicles assigned into it.', 'ecalypse-rental');?>
									</p>
										
									<div class="form-group">
								    <label for="ecalypse-rental-category-name"><?php _e('Category name', 'ecalypse-rental');?></label>
									<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
										<li role="presentation" class="active"><a href="javascript:void(0);" class="categories_new_categories_switcher" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>										
										<?php if ($available_languages && !empty($available_languages)) { ?>														
											<?php foreach ($available_languages as $key => $val) { ?>
												<?php if ($val['country-www'] == 'gb') {continue;} ?>
												<li role="presentation"><a href="javascript:void(0);" class="categories_new_categories_switcher" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
											<?php } ?>
										<?php } ?>
									</ul>

									<div class="ecalypse_rental_new_categories_translations" data-lng="gb">
										<input type="text" name="vehicle_category_name" id="ecalypse-rental-category-name" class="form-control" placeholder="Category name">
									</div>
									<?php if ($available_languages && !empty($available_languages)) { ?>
										<?php foreach ($available_languages as $key => $val) { ?>
											<?php if ($val['country-www'] == 'gb') {continue;} ?>
												<div class="ecalypse_rental_new_categories_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">																		
													<input type="text" name="vehicle_category_name_translations[<?php echo $val['country-www'];?>]" placeholder="Name in <?php echo $val['lang-native'];?>" class="form-control" value="">
												</div>
										<?php } ?>
									<?php } ?>
								  </div>
									
									<div class="form-group">
								    <label for="ecalypse-rental-category-picture"><?php _e('Category picture', 'ecalypse-rental');?></label>
								    <input type="file" name="vehicle_category_picture" id="ecalypse-rental-category-picture">
								  </div>
								  
								  <button type="submit" class="btn btn-success" name="add_vehicle_category"><?php _e('Add Vehicle Category', 'ecalypse-rental');?></button>
									
							  </form>
						  
						  </div>
						</div>
						<!-- .row //-->
							
					</div>
					<!-- .panel-body //-->
				</div>
				
				<div class="panel panel-default">
					<div class="panel-heading"><h4 id="holidays"><?php _e('Holidays', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
						<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>#holidays" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
						<div class="row">
							<div class="col-md-12" id="ecalypse_rental_holidays_div">
								<?php wp_nonce_field( 'settings-holidays'); ?>
								<?php $holidays = get_option('ecalypse_rental_holidays'); ?>
									
									<?php $holidays = unserialize($holidays);?>
									<?php if ($holidays && !empty($holidays)) { ?>
										<?php asort($holidays); ?>
										<?php foreach ($holidays as $key => $val) { ?>
												<span style="margin-right:5px;margin-bottom:5px;" class="ecalypse_rental_holidays btn btn-warning"><?php echo $val;?> <a href="#" class="ecalypse_rental_remove_holiday">X</a><input type="hidden" name="ecalypse_rental_holidays[]" value="<?php echo $val;?>"></span>
										<?php } ?>
									<?php } else { ?>
										<p><?php _e('You have no holidays yet.', 'ecalypse-rental');?></p>	
									<?php } ?>

							</div>
						</div>
						<!-- .row //-->

						<div class="row">
							<div class="col-md-6">

								<h4><?php _e('Add new holiday date', 'ecalypse-rental');?></h4>

								<div>
									<label for="ecalypse-rental-holiday-date"><?php _e('Date', 'ecalypse-rental');?></label>
									<input type="text" name="vehicle_holiday_date" id="ecalypse-rental-holiday-date" class="form-control" placeholder="Date">
								</div>
								<p class="help-block"><?php _e('Please note: dates closed will repeat every year unless deleted.', 'ecalypse-rental');?></p>
								<button id="ecalypse_rental_add_holidays" class="btn btn-success" name="add_holidays"><?php _e('Add this date', 'ecalypse-rental');?></button>

								
								<!-- Submit //-->
								<br><br><button type="submit" name="save_holidays" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save Holidays', 'ecalypse-rental');?></button>
							</div>
						</div>
						<!-- .row //-->
						
								</form>
					</div>
					<!-- .panel-body //-->
				</div>
				
				<div class="panel panel-default">
					<div class="panel-heading"><h4 id="reservation-inputs"><?php _e('Reservation inputs', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
						<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>#reservation-inputs" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
						<div class="row">
							<div class="col-md-10">
								<?php wp_nonce_field( 'settings-inputs'); ?>
								<?php
								$inputs_list = array('company' => __('Company', 'ecalypse-rental'), 'vat' => 'VAT', 'flight' => __('Flight number', 'ecalypse-rental'), 'license' => __('License number', 'ecalypse-rental'), 'id_card' => __('ID / Passport number', 'ecalypse-rental'), 'partner_code' => __('Partner code', 'ecalypse-rental'));
								$inputs = get_option('ecalypse_rental_reservation_inputs');
								$inputs = unserialize($inputs);
								if (empty($inputs)) {
									$inputs = array();
								}
								foreach ($inputs_list as $k => $v) { ?>
									<label style="margin-right:20px;"><input type="checkbox" name="ecalypse_rental_inputs[<?php echo $k;?>]" value="1"<?php echo !isset($inputs[$k]) ? ' checked="checked"' : '';?>> <span><?php echo $v;?></span></label>
								<?php } ?>
								<p class="help-block"><?php _e('Checked inputs will be shown in reservation form.', 'ecalypse-rental');?></p>
								
								<!-- Submit //-->
								<button type="submit" name="save_reservation_inputs" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save Reservation inputs', 'ecalypse-rental');?></button>
							</div>
						</div>
						<!-- .row //-->
						
								</form>
					</div>
					<!-- .panel-body //-->
				</div>
				
				<!-- SEO SETTINGS //-->
				<div class="panel panel-default">
					<div class="panel-heading"><h4 id="seo-settings"><?php _e('Home page SEO settings', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
					  
					  <form action="" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
						  <?php wp_nonce_field( 'settings-seo'); ?>
							<div class="row">
								<div class="col-md-6">
									
									<div class="form-group">
											<div class="col-sm-3"></div>
											<div class="col-sm-9">
												<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
													<li role="presentation" class="active"><a href="javascript:void(0);" class="edit_seo_name_desc" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
													<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
													<?php if ($available_languages && !empty($available_languages)) { ?>														
														<?php foreach ($available_languages as $key => $val) { ?>
															<?php if ($val['country-www'] == 'gb') {continue;} ?>
															<li role="presentation"><a href="javascript:void(0);" class="edit_seo_name_desc" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
														<?php } ?>
													<?php } ?>
												</ul>
											</div>
										</div>
									<?php $seo = unserialize(get_option('ecalypse_rental_seo')); ?>
									<?php if (empty($seo)) { $seo = array(); } ?>
									<!-- Title //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-seo-title" class="col-sm-3 control-label"><?php _e('Title', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_seo_translations" data-lng="gb">
												<input type="text" name="seo[title][gb]" class="form-control" id="ecalypse-rental-seo-title" placeholder="Title in English" value="<?= (isset($seo['title']) && isset($seo['title']['gb'])) ? $seo['title']['gb'] : '' ?>">
											</div>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_seo_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<input type="text" name="seo[title][<?php echo $val['country-www'];?>]" placeholder="Title in <?php echo $val['lang-native'];?>" class="form-control" value="<?= (isset($seo['title']) && isset($seo['title'][$val['country-www']])) ? $seo['title'][$val['country-www']] : '' ?>">
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									
									<!-- Description //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-seo-description" class="col-sm-3 control-label"><?php _e('Meta description', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_seo_translations" data-lng="gb">
												<input type="text" name="seo[description][gb]" class="form-control" id="ecalypse-rental-seo-description" placeholder="Description in English" value="<?= (isset($seo['description']) && isset($seo['description']['gb'])) ? $seo['description']['gb'] : '' ?>">
											</div>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_seo_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<input type="text" name="seo[description][<?php echo $val['country-www'];?>]" placeholder="Description in <?php echo $val['lang-native'];?>" class="form-control" value="<?= (isset($seo['description']) && isset($seo['description'][$val['country-www']])) ? $seo['description'][$val['country-www']] : '' ?>">
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									
									<!-- KW //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-seo-description" class="col-sm-3 control-label"><?php _e('Meta keywords', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_seo_translations" data-lng="gb">
												<input type="text" name="seo[keywords][gb]" class="form-control" id="ecalypse-rental-seo-description" placeholder="Keywords in English" value="<?= (isset($seo['keywords']) && isset($seo['keywords']['gb'])) ? $seo['keywords']['gb'] : '' ?>">
											</div>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_seo_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<input type="text" name="seo[keywords][<?php echo $val['country-www'];?>]" placeholder="Keywords in <?php echo $val['lang-native'];?>" class="form-control" value="<?= (isset($seo['keywords']) && isset($seo['keywords'][$val['country-www']])) ? $seo['keywords'][$val['country-www']] : '' ?>">
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
								    	
								  <!-- Submit //-->
								  <div class="form-group">
								  	<div class="col-sm-offset-3 col-sm-9">
								  		<button type="submit" name="save_seo" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save SEO Settings', 'ecalypse-rental');?></button>
										<p class="help-block">
											*<?php _e('This is valid only for Ecalypse compatible themes', 'ecalypse-rental');?>
										</p>
								  	</div>
									</div>
									
								</div>
							</div>
							<!-- .row //-->
							
						</form>
					
					</div>
					<!-- .panel-body //-->
				</div>
				
				<div class="panel panel-default">
					<div class="panel-heading"><h4><?php _e('E-mail testing', 'ecalypse-rental');?></h4></div>
					<div class="panel-body">
					  
						<div class="row">
							<div class="col-md-12">
							
								<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>" method="post" role="form" class="form-horizontal">
									<div class="form-group">
								    <label for="smtp-user" class="col-sm-3 control-label">Your e-mail</label>
								    <div class="col-sm-9">
								    	<input type="text" class="form-control" name="email" id="smtp-user" value="<?= (isset($smtp['email']) ? $smtp['email'] : '') ?>" placeholder="User name or e-mail">
								    </div>
								  </div>
									
									<div class="form-group">
										<div class="col-sm-offset-3 col-sm-9">
											<button type="button" name="send_test_email" class="btn btn-info"><span class="glyphicon glyphicon-send"></span>&nbsp;&nbsp;<?php _e('Send test e-mail', 'ecalypse-rental');?></button>
										</div>
									</div>
									
									<script>
										
										jQuery(document).ready(function() {
											
											jQuery('[name="send_test_email"]').click(function() {
												jQuery(this).prop('disabled', true);
												jQuery.post("<?= $_SERVER['REQUEST_URI'] ?>", { send_test_email: "1", _wpnonce: '<?php echo wp_create_nonce('settings-email-test');?>', user: jQuery('#smtp-user').val() })
												  .done(function( data ) {
												  	jQuery('[name="send_test_email"]').prop('disabled', false);
												    alert( data );
												});
											});
											
										});
									
									</script>
									
								</form>
								
							</div>
						</div>
					</div>
				</div>
				
				<!-- SMTP Settings //-->
				<?php /* HIDDEN ?>
				<div class="panel panel-default">
					<div class="panel-heading"><h4>SMTP Settings (for sending e-mails)</h4></div>
					<div class="panel-body">
					  
					  <?php $smtp = unserialize(get_option('ecalypse_rental_smtp')); ?>
						<div class="row">
							<div class="col-md-12">
							
								<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>" method="post" role="form" class="form-horizontal">
									
									<p>
										* This settings is important if you want to send automatic e-mails to customers with their reservation.<br>
										* If some of the options are empty, there is no guarantee that reservation e-mail will be sent.<br><br>
									</p>
											
									<div class="form-group">
								    <label for="smtp-server" class="col-sm-3 control-label">SMTP Server</label>
								    <div class="col-sm-9">
								    	<input type="text" class="form-control" name="server" id="smtp-server" value="<?= (isset($smtp['server']) ? $smtp['server'] : '') ?>" placeholder="SMTP Server / e.g. smtp.gmail.com">
								    </div>
								  </div>
									
									<div class="form-group">
								    <label for="smtp-user" class="col-sm-3 control-label">Username / e-mail</label>
								    <div class="col-sm-9">
								    	<input type="text" class="form-control" name="email" id="smtp-user" value="<?= (isset($smtp['email']) ? $smtp['email'] : '') ?>" placeholder="User name or e-mail">
								    </div>
								  </div>
									
									<div class="form-group">
								    <label for="smtp-pass" class="col-sm-3 control-label">Password</label>
								    <div class="col-sm-9">
								    	<input type="password" class="form-control" name="pwd" id="smtp-pass" value="<?= (isset($smtp['pwd']) ? $smtp['pwd'] : '') ?>" placeholder="Password">
								    </div>
								  </div>
									
									<div class="form-group">
								    <label for="smtp-port" class="col-sm-3 control-label">SMTP Port</label>
								    <div class="col-sm-9">
								    	<input type="text" class="form-control" name="port" id="smtp-port" value="<?= (isset($smtp['port']) ? $smtp['port'] : '') ?>" placeholder="SMTP Port">
								    	<p class="help-block">Set 465 or 587 for Google</p>
								    </div>
								  </div>
									
									<div class="form-group">
								    <label for="smtp-sec" class="col-sm-3 control-label">SMTP Secure</label>
								    <div class="col-sm-9">
								    	<select name="secure" id="smtp-sec"  class="form-control">
								    		<option value="">None</option>
								    		<option value="tls" <?php if (isset($smtp['secure']) && $smtp['secure'] == 'tls') { ?>selected<?php } ?>>TLS</option>
								    		<option value="ssl" <?php if (isset($smtp['secure']) && $smtp['secure'] == 'ssl') { ?>selected<?php } ?>>SSL</option>
								    	</select>
								    </div>
								  </div>
									
									<div class="form-group">
										<div class="col-sm-offset-3 col-sm-9">
											<br><button type="submit" name="save_smtp_settings" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;Confirm &amp; Save settings</button>
											&nbsp;&nbsp;&nbsp;&nbsp;
											<button type="button" name="send_test_email" class="btn btn-info"><span class="glyphicon glyphicon-send"></span>&nbsp;&nbsp;Send test e-mail</button>
										</div>
									</div>
									
									<script>
										
										$(document).ready(function() {
											
											$('[name="send_test_email"]').click(function() {
												$(this).prop('disabled', true);
												$.post("<?= $_SERVER['REQUEST_URI'] ?>", { send_test_email: "1", server: $('#smtp-server').val(), user: $('#smtp-user').val(), pass: $('#smtp-pass').val(), port: $('#smtp-port').val(), secure: $('#smtp-sec').val() })
												  .done(function( data ) {
												  	$('[name="send_test_email"]').prop('disabled', false);
												    alert( data );
												});
											});
											
										});
									
									</script>
									
								</form>
								
							</div>
						</div>
					</div>
				</div>
				<?php /**/ ?>
				
				<?php if (isset($_GET['export'])) { ?>
					<!-- Export/Import //-->
					<div class="panel panel-default">
						<div class="panel-heading"><h4 id="global-scheme-replace"><?php _e('Export data', 'ecalypse-rental');?></h4></div>
						<div class="panel-body">
						  
							<div class="row">
								<div class="col-md-12">
								
									<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-settings') ?>" method="post" role="form" class="form-horizontal">
										<?php wp_nonce_field( 'settings-export'); ?>
										<div class="form-group">
											<div class="col-sm-12">
												<div class="checkbox">
											    <label>
											      <input type="checkbox" name="export_structure" value="1">&nbsp;&nbsp;<?php _e('Export structure', 'ecalypse-rental');?>
											    </label>
											  </div>
											</div>
										</div>
										
										<div class="form-group">
											<div class="col-sm-12">
												<div class="checkbox">
											    <label>
											      <input type="checkbox" name="export_data" value="1">&nbsp;&nbsp;<?php _e('Export data', 'ecalypse-rental');?>
											    </label>
											  </div>
											</div>
										</div>
														  
										<div class="form-group">
											<div class="col-sm-12">
												<button type="submit" name="export_database" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Export', 'ecalypse-rental');?></button>
											</div>
										</div>
										
										
									</form>
									
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
				
			</div>
		</div>
	</div>
	
</div>

<script type="text/javascript">
	jQuery(document).ready(function ($) {
		$(document).on('click', '#ecalypse-rental-holiday-date', function () {
			$(this).datepicker({dateFormat: 'mm-dd'}).datepicker('show');
		});
		
		$(document).on('click', '.ecalypse_rental_remove_holiday', function (e) {
			e.preventDefault();
			$(this).parent().remove();
		});
		
		$('#ecalypse_rental_add_holidays').click(function(e){
			e.preventDefault();
			if ($('#ecalypse-rental-holiday-date').val() == '') {
				return;
			}
			$('#ecalypse_rental_holidays_div p').remove();
			$('#ecalypse_rental_holidays_div').append('<span style="margin-right:5px;margin-bottom:5px;" class="ecalypse_rental_holidays btn btn-warning">'+$('#ecalypse-rental-holiday-date').val()+' <a href="#" class="ecalypse_rental_remove_holiday">X</a><input type="hidden" name="ecalypse_rental_holidays[]" value="'+$('#ecalypse-rental-holiday-date').val()+'"></span>');
			
		});
	});
</script>