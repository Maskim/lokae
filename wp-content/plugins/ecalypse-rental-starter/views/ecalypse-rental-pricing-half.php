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
							<h3><?php _e('Edit Pricing Scheme', 'ecalypse-rental');?> <?= $detail->name ?></h3>
						<?php } else { ?>
							<?php if (isset($_GET['deleted'])) { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>" class="btn btn-default" style="float:right;"><?php _e('Show normal', 'ecalypse-rental');?></a>
							<?php } else { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>&amp;deleted" class="btn btn-default" style="float:right;"><?php _e('Show deleted', 'ecalypse-rental');?></a>
							<?php } ?>
							
							<a href="javascript:void(0);" class="btn btn-success" id="ecalypse-rental-pricing-add-button"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;<?php _e('Add new Pricing Scheme', 'ecalypse-rental');?></a>
						<?php } ?>
						
						<div id="<?= (($edit == true) ? 'ecalypse-rental-pricing-edit-form' : 'ecalypse-rental-pricing-add-form') ?>" class="ecalypse-rental-add-form">
							<form action="" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
								
								<div class="row">
									<div class="col-md-11">
										
										<!-- Type //-->
									  <div class="form-group">
									    <label class="col-sm-3 control-label"><?php _e('Type', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												  <input type="radio" name="type" value="1" <?= (($detail->type == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<strong><abbr title="This is a one time fee, which is paid once per rental and is not dependent on the number of days - typically a pick up or drop off service or similar."><?php _e('One time', 'ecalypse-rental');?></abbr></strong>
												</label>
												<label class="radio-inline">
												  <input type="radio" name="type" value="2" <?= (($detail->type == 2) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<strong><abbr title="The price of which is directly dependent on the time for which it is used, this system requires inputting prices according to the extent of use e.g. 400 CZK/day for 1-10 days, 11-20 days 350 CZK/day, etc."><?php _e('Time based', 'ecalypse-rental');?></abbr></strong>
												</label>
											<p class="help-block"><?php _e('One time is a one time charge only; for pricing based on how long users rent, select Time based.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
										<!-- Name //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-name" class="col-sm-3 control-label">Name</label>
									    <div class="col-sm-9">
									    	<input type="text" name="name" class="form-control" id="ecalypse-rental-name" value="<?= (($edit == true) ? $detail->name : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Default pricing currency //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-currency" class="col-sm-3 control-label"><?php _e('Default currency', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<?php $currency = get_option('ecalypse_rental_global_currency'); ?>
									    	<?php if ($currency && !empty($currency)) { ?>
										    	<select name="currency" id="ecalypse-rental-currency" class="form-control">
										    		<option value="<?= $currency ?>"><?= $currency ?></option>
										    		<?php $av_currencies = unserialize(get_option('ecalypse_rental_available_currencies')); ?>
										    		<?php if ($av_currencies && !empty($av_currencies)) { ?>
										    			<?php foreach ($av_currencies as $cc => $rate) { ?>
										    				<option value="<?= $cc ?>" <?php if ($edit == true && $cc == $detail->currency) { ?>selected<?php } ?>><?= $cc ?> (<?= $rate ?> &times; <?= $currency ?>)</option>	
										    			<?php } ?>
										    		<?php } ?>
									    		</select>
									    		
									    	<?php } else { ?>
									    		<p class="help-block">
														<?php _e('Please, set-up your', 'ecalypse-rental');?> <a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-settings')); ?>#global-settings"><?php _e('Global currency', 'ecalypse-rental');?></a> <?php _e('first.', 'ecalypse-rental');?>
													</p>
									    	<?php } ?>
									    </div>
									  </div>
									  
									  <!-- One time price //-->
									  <div class="form-group type-onetime">
									    <label for="ecalypse-rental-onetime-price" class="col-sm-3 control-label"><?php _e('One time price', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<?php if ($currency && !empty($currency)) { ?>
									    		<div class="input-group">
									    			<input type="text" name="onetime_price" class="form-control" id="ecalypse-rental-onetime-price" value="<?= (($edit == true) ? $detail->onetime_price : '') ?>">
									    			<span class="input-group-addon addon-currency"></span>
									    		</div>
									    	<?php } else { ?>
									    		<p class="help-block">
														<?php _e('Please, set-up your', 'ecalypse-rental');?> <a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-settings')); ?>#global-settings"><?php _e('Global currency', 'ecalypse-rental');?></a> <?php _e('first.', 'ecalypse-rental');?>
													</p>
									    	<?php } ?>
									    </div>
									  </div>
									  
									  <!-- Time based - days //-->
									  <div class="form-group type-timerelated">
									    <label class="col-sm-3 control-label"><abbr title="Day ranges per day pricing (e.g. for 1-3 days of rental, price will be 20 USD/day; 4-5 days of rental, 18 USD/day etc.)"><?php _e('Day ranges per day pricing', 'ecalypse-rental');?></abbr></label>
									    <div class="col-sm-9">
									    	<table class="table" id="ecalypse-rental-day-range">
									    		<?php if (isset($detail->days) && !empty($detail->days)) { ?>
									    			<?php foreach ($detail->days as $key => $val) { ?>
									    				<tr>
											    			<td>&nbsp;<strong><?php _e('From', 'ecalypse-rental');?></strong>&nbsp;</td>
																<td><input type="text" name="days[from][]" class="form-control" size="2" placeholder="day no." value="<?= (($edit == true) ? $val['from'] : '') ?>"></td>
											    			<td>&nbsp;<strong><abbr title="For the infinite validity, leave the field blank."><span class="glyphicon glyphicon-question-sign"></span>&nbsp;&nbsp;<?php _e('To', 'ecalypse-rental');?></abbr></strong>&nbsp;</td>
																<td><input type="text" name="days[to][]" class="form-control" size="2" placeholder="day no." value="<?= (($edit == true) ? $val['to'] : '') ?>"></td>
											    			<td>&nbsp;<strong><?php _e('Price per day', 'ecalypse-rental');?></strong>&nbsp;</td>
																<td>
																	<div class="input-group" style="width:150px;">
																	  <input type="text" name="days_price[]" class="form-control" placeholder="price" value="<?= (($edit == true) ? $val['price'] : '') ?>">
																		<span class="input-group-addon addon-currency"></span>
																	</div>
																</td>
															</tr>
									    			<?php } ?>
									    		<?php } ?>
									    	
									    		<tr id="day-range-row">
									    			<td>&nbsp;<strong><?php _e('From', 'ecalypse-rental');?></strong>&nbsp;</td>
														<td><input type="text" name="days[from][]" class="form-control" size="2" placeholder="day no."></td>
									    			<td>&nbsp;<strong><abbr title="For the infinite validity, leave the field blank."><span class="glyphicon glyphicon-question-sign"></span>&nbsp;&nbsp;<?php _e('To', 'ecalypse-rental');?></abbr></strong>&nbsp;</td>
														<td><input type="text" name="days[to][]" class="form-control" size="2" placeholder="day no."></td>
									    			<td>&nbsp;<strong><?php _e('Price per day', 'ecalypse-rental');?></strong>&nbsp;</td>
														<td>
															<div class="input-group" style="width:150px;">
															  <input type="text" name="days_price[]" class="form-control" placeholder="price">
																<span class="input-group-addon addon-currency"></span>
															</div>
														</td>
													</tr>
													<tr id="day-range-row-before"><td colspan="6"></td></tr>
									    	</table>
										    <div id="ecalypse-rental-dayrange-insert"></div>
										    <p class="help-block" id="days-range-help" style="color:tomato;"><?php _e('Warning! There might be an overlapse in the settings.', 'ecalypse-rental');?></p>
												
												<a href="javascript:void(0);" id="ecalypse-rental-add-day-range" class="btn btn-info btn-xs" title="Watch out for overlapses!"><?php _e('Add Day Range', 'ecalypse-rental');?></a>
									    </div>
									  </div>
									  
									  <!-- Time based - hours //-->
									  <div class="form-group type-timerelated">
									    <label class="col-sm-3 control-label"><abbr title="Hour ranges per hour pricing (e.g. for 1-3 hours of rental, price will be 15 USD/hour; 4-6 hours of rental, 25 USD/hour etc.)"><?php _e('Hour ranges per hour pricing', 'ecalypse-rental');?></abbr></label>
									    <div class="col-sm-9">
									    	<a href="javascript:void(0);" id="ecalypse-rental-hour-range-box-show" class="btn btn-warning btn-xs">Setup the hour ranges', 'ecalypse-rental');?></a>
									    	<div id="ecalypse-rental-hour-range-box">
										    	<table class="table" id="ecalypse-rental-hour-range">
														<?php if (isset($detail->hours) && !empty($detail->hours)) { ?>
										    			<?php foreach ($detail->hours as $key => $val) { ?>
										    				<tr>
												    			<td>&nbsp;<strong><?php _e('From', 'ecalypse-rental');?></strong>&nbsp;</td>
																	<td><input type="text" name="hours[from][]" class="form-control" size="2" placeholder="hour" value="<?= (($edit == true) ? $val['from'] : '') ?>"></td>
												    			<td>&nbsp;<strong><?php _e('To', 'ecalypse-rental');?></strong>&nbsp;</td>
																	<td><input type="text" name="hours[to][]" class="form-control" size="2" placeholder="hour" value="<?= (($edit == true) ? $val['to'] : '') ?>"></td>
												    			<td>&nbsp;<strong><?php _e('Price per hour', 'ecalypse-rental');?></strong>&nbsp;</td>
																	<td>
																		<div class="input-group" style="width:150px;">
																		  <input type="text" name="hours_price[]" class="form-control" placeholder="price" value="<?= (($edit == true) ? $val['price'] : '') ?>">
																			<span class="input-group-addon addon-currency"></span>
																		</div>
																	</td>
																</tr>
										    			<?php } ?>
										    		<?php } ?>
														<tr id="hour-range-row">
										    			<td>&nbsp;<strong><?php _e('From', 'ecalypse-rental');?></strong>&nbsp;</td>
															<td><input type="text" name="hours[from][]" class="form-control" size="2" placeholder="hour"></td>
										    			<td>&nbsp;<strong><?php _e('To', 'ecalypse-rental');?></strong>&nbsp;</td>
															<td><input type="text" name="hours[to][]" class="form-control" size="2" placeholder="hour"></td>
										    			<td>&nbsp;<strong><?php _e('Price per hour', 'ecalypse-rental');?></strong>&nbsp;</td>
															<td>
																<div class="input-group" style="width:150px;">
																  <input type="text" name="hours_price[]" class="form-control" placeholder="price">
																	<span class="input-group-addon addon-currency"></span>
																</div>
															</td>
														</tr>
														<tr id="hour-range-row-before"><td colspan="6"></td></tr>
										    	</table>
											    <div id="ecalypse-rental-dayrange-insert"></div>
											    <p class="help-block" id="hours-range-help"><?php _e('Warning! There might be an overlapse in the settings.', 'ecalypse-rental');?></p>
													<a href="javascript:void(0);" id="ecalypse-rental-add-hour-range" class="btn btn-info btn-xs" title="Watch out for overlapses!"><?php _e('Add Hour Range', 'ecalypse-rental');?></a>
												</div>
									    </div>
									  </div>
									  
									  <!-- Active on these days //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-promocode" class="col-sm-3 control-label"><?php _e('Pricing is active on these days', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<?php
													if ($edit == true && !empty($detail->active_days)) {
														$days = explode(';', $detail->active_days);
													} else {
														$days = array(0,1,2,3,4,5,6);
													}
												?>
									    	<label class="radio-inline"><?php _e('Select all', 'ecalypse-rental');?> <input type="checkbox" class="form-control days-check-all" name="days_all" value="all" style="margin: -2px 0 0 4px;"></label>
									    	<label class="radio-inline"><?php _e('Monday', 'ecalypse-rental');?> <input type="checkbox" class="form-control days-check" name="active_days[]" value="1" <?php if (in_array(1, $days)) { ?>checked<?php } ?> style="margin: -2px 0 0 4px;"></label>
									    	<label class="radio-inline"><?php _e('Tuesday', 'ecalypse-rental');?> <input type="checkbox" class="form-control days-check" name="active_days[]" value="2" <?php if (in_array(2, $days)) { ?>checked<?php } ?> style="margin: -2px 0 0 4px;"></label>
									    	<label class="radio-inline"><?php _e('Wednesday', 'ecalypse-rental');?> <input type="checkbox" class="form-control days-check" name="active_days[]" value="3" <?php if (in_array(3, $days)) { ?>checked<?php } ?> style="margin: -2px 0 0 4px;"></label>
									    	<label class="radio-inline"><?php _e('Thursday', 'ecalypse-rental');?> <input type="checkbox" class="form-control days-check" name="active_days[]" value="4" <?php if (in_array(4, $days)) { ?>checked<?php } ?> style="margin: -2px 0 0 4px;"></label>
									    	<label class="radio-inline"><?php _e('Friday', 'ecalypse-rental');?> <input type="checkbox" class="form-control days-check" name="active_days[]" value="5" <?php if (in_array(5, $days)) { ?>checked<?php } ?> style="margin: -2px 0 0 4px;"></label>
									    	<label class="radio-inline"><?php _e('Saturday', 'ecalypse-rental');?> <input type="checkbox" class="form-control days-check" name="active_days[]" value="6" <?php if (in_array(6, $days)) { ?>checked<?php } ?> style="margin: -2px 0 0 4px;"></label>
									    	<label class="radio-inline"><?php _e('Sunday', 'ecalypse-rental');?> <input type="checkbox" class="form-control days-check" name="active_days[]" value="0" <?php if (in_array(0, $days)) { ?>checked<?php } ?> style="margin: -2px 0 0 4px;"></label>
									    </div>
									  </div>
									  
									  <!-- Max price //-->
									  <div class="form-group type-timerelated">
									    <label for="ecalypse-rental-maxprice" class="col-sm-3 control-label"><?php _e('Max total price', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<?php if ($currency && !empty($currency)) { ?>
										    	<div class="input-group">
													  <input type="text" name="maxprice" class="form-control" id="ecalypse-rental-maxprice" value="<?= (($edit == true) ? $detail->maxprice : '') ?>">
										    		<span class="input-group-addon addon-currency"></span>
													</div>
													<p class="help-block"><?php _e('Max price that this scheme allows (will be set when calculated price reaches this amount).', 'ecalypse-rental');?></p>
												<?php } else { ?>
									    		<p class="help-block">
														<?php _e('Please, set-up your', 'ecalypse-rental');?> <a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-settings')); ?>#global-settings"><?php _e('Global currency', 'ecalypse-rental');?></a> <?php _e('first.', 'ecalypse-rental');?>
													</p>
									    	<?php } ?>
									    </div>
									  </div>
									  
									   <!-- Min price //-->
									  <div class="form-group type-timerelated">
									    <label for="ecalypse-rental-min_price" class="col-sm-3 control-label"><?php _e('Minimum price', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<?php if ($currency && !empty($currency)) { ?>
										    	<div class="input-group">
													  <input type="text" name="min_price" class="form-control" id="ecalypse-rental-min_price" value="<?= (($edit == true) ? $detail->min_price : '0') ?>">
										    		<span class="input-group-addon addon-currency"></span>
													</div>
													<p class="help-block"><?php _e('If total price is lower then this amount then this price is used.', 'ecalypse-rental');?></p>
												<?php } else { ?>
									    		<p class="help-block">
														<?php _e('Please, set-up your', 'ecalypse-rental');?> <a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-settings')); ?>#global-settings"><?php _e('Global currency', 'ecalypse-rental');?></a> <?php _e('first.', 'ecalypse-rental');?>
													</p>
									    	<?php } ?>
									    </div>
									  </div>
									   
									   <!-- Tax rates //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-tax_rates" class="col-sm-3 control-label"><?php _e('Tax rates', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<?php $vat_settings = unserialize(get_option('ecalypse_rental_vat_settings')); ?>
											<?php
											if (!$vat_settings) {
												$vat_settings = array('vat' => 0, 'vat_2' => 0, 'vat_3' => 0, 'vat_calculation' => 1);
											}
											$tax_rates = array();
											if ($edit) {
												$tax_rates = unserialize($detail->tax_rates);
											}
											unset($vat_settings['vat_calculation']);
											$i = 0;
											?>
										    	<?php foreach ($vat_settings as $k => $v) { $i++; ?>
													<label class="radio-inline">Tax <?php echo $i;?> (<?php echo $v;?>%) <input type="checkbox" class="form-control" name="tax_rates[<?php echo $k;?>]" value="1" <?php if (isset($tax_rates[$k])) { ?>checked<?php } ?> style="margin: -2px 0 0 4px;"></label>
												<?php } ?>
												<p class="help-block"><?php _e('Select what taxes you want to use with this pricing scheme.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
									  
									  <!-- Promo code //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-promocode" class="col-sm-3 control-label"><?php _e('Promo code', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="promocode" class="form-control" id="ecalypse-rental-promocode" value="<?= (($edit == true) ? $detail->promocode : '') ?>">
									    	<p class="help-block"><?php _e('If you fill out this field, this pricing scheme will be used ONLY if this promo code is inserted by client. To be active, it must be assigned to a specific vehicle and all criteria must match (days of the week, seasons, etc.).', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
									  <!-- Active //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-active" class="col-sm-3 control-label"><?php _e('Active', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												  <input type="radio" name="active" value="1" <?= (($detail->active == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
												  <input type="radio" name="active" value="0" <?= (($detail->active == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('No', 'ecalypse-rental');?>
												</label>
									    </div>
									  </div>
									  
									  <!-- RATE ID //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-rate-id" class="col-sm-3 control-label"><?php _e('Rate ID', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<div class="input-group">
												  <input type="text" name="rate_id" class="form-control" id="ecalypse-rental-rate-id" value="<?= (($edit == true) ? $detail->rate_id : '') ?>">
												  <p class="help-block"><?php _e('If using TSDweb extension, insert your TSD rate ID here; else, use for internal records.', 'ecalypse-rental');?></p>
												</div>
									    </div>
									  </div>
									  
									  <!-- Submit //-->
									  <div class="form-group">
									  	<div class="col-sm-offset-3 col-sm-9">
									  		<?php if ($edit == true) { ?>
											<?php wp_nonce_field( 'add_pricing'); ?>
									  			<input type="hidden" name="id_pricing" value="<?= $detail->id_pricing ?>">
									  			<button type="submit" class="btn btn-warning" name="add_pricing"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									  		<?php } else { ?>
									  			<button type="submit" class="btn btn-warning" name="add_pricing"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Add', 'ecalypse-rental');?></button>
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
						
						<?php if (isset($pricing) && !empty($pricing)) { ?>
							<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-pricing" /> <?php _e('Select all', 'ecalypse-rental');?></label>
							<table class="table table-striped" id="ecalypse-rental-pricing">
					      <thead>
					        <tr>
					          <th>#</th>
					          <th><?php _e('Type', 'ecalypse-rental');?></th>
					          <th><?php _e('Name', 'ecalypse-rental');?></th>
					          <th><?php _e('Price', 'ecalypse-rental');?></th>
					          <th><?php _e('Max. price', 'ecalypse-rental');?></th>
					          <th><?php _e('Pricing code', 'ecalypse-rental');?></th>
					          <th><?php _e('Usage', 'ecalypse-rental');?></th>
					          <th><?php _e('Action', 'ecalypse-rental');?></th>
					        </tr>
					      </thead>
					      <tbody>
					      	<?php foreach ($pricing as $key => $val) { ?>
					      		<?php $total_usage = (int) $val->fleet_usage + (int) $val->extras_usage; ?>
					      		<tr>
						          <td>
						          	<input type="checkbox" class="input-control batch_processing" name="batch[]" value="<?= $val->id_pricing ?>" data-usage="<?= $total_usage ?>">&nbsp;
												<abbr title="Created: <?= $val->created ?>

<?= (!empty($val->updated) ? 'Updated: ' . $val->updated : '') ?>"><?= $val->id_pricing ?></abbr>
											</td>
											<td>
												<?php
													$btn_class = '';
													if ($val->active == 0) {
														$btn_class = 'btn-default';
													} elseif ($val->type == 1) {
														$btn_class = 'btn-info';
													} elseif ($val->type == 2) {
														$btn_class = 'btn-success';
													}
												?>
												<span class="btn btn-xs <?= $btn_class ?>"><?= (($val->type == 1) ? 'ONE TIME' : 'TIME BASED') ?></span>
											</td>
						          <td>
												<strong><?= (!empty($val->name) ? $val->name : '- Unknown -') ?></strong>
												<?php if ($val->active == 0) { ?>&nbsp;&nbsp;<em><?php _e('(Inactive)', 'ecalypse-rental');?></em><?php } ?>
											</td>
											<td>
												<?php if ($val->type == 1) { ?>
													<strong><?= $val->onetime_price ?>&nbsp;<?= $val->currency ?></strong>
												<?php } elseif ($val->type == 2) { ?>
													<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>&get_day_ranges=<?= $val->id_pricing ?>" class="btn btn-xs btn-success ecalypse_rental_show_ranges"><?php _e('Show ranges', 'ecalypse-rental');?></a>
												<?php } ?>
											</td>
											<td>
												<?= (!empty($val->maxprice) ? $val->maxprice . '&nbsp;' . $val->currency : '&mdash;') ?>
											</td>
											<td>
												<?= (!empty($val->promocode) ? $val->promocode : '&mdash;') ?>
											</td>
											<td><?= $total_usage ?> &times;</td>
						          <td>
												<form action="" method="post" class="form-inline" role="form" style="float: left;margin-right: 10px;">
													<div class="form-group">
														<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>&amp;edit=<?= $val->id_pricing ?>" class="btn btn-xs btn-primary"><?php _e('Modify', 'ecalypse-rental');?></a>
													</div>
												</form>
												
												<form action="" method="post" class="form-inline" role="form" style="float: left;margin-right: 10px;">
													<div class="form-group">
														<input type="hidden" name="id_pricing" value="<?= $val->id_pricing ?>">
														<?php wp_nonce_field( 'copy_pricing'); ?>
														<button name="copy_pricing" class="btn btn-xs btn-warning"><?php _e('Copy', 'ecalypse-rental');?></button>
													</div>
												</form>
												
												<?php if (isset($_GET['deleted'])) { ?>
													<form action="" method="post" class="form-inline" role="form" style="float: left;margin-right: 10px;" onsubmit="return confirm('<?= __('Do you really want to restore this Pricing scheme?', 'ecalypse-rental') ?>');">
														<div class="form-group">
															<input type="hidden" name="id_pricing" value="<?= $val->id_pricing ?>">
															<?php wp_nonce_field( 'restore_pricing'); ?>
															<button name="restore_pricing" class="btn btn-xs btn-success"><?php _e('Restore', 'ecalypse-rental');?></button>
														</div>
													</form>
												<?php } else { ?>
													<?php if ($total_usage == 0) { ?>
														<form action="" method="post" class="form-inline" role="form" style="float: left;margin-right: 10px;" onsubmit="return confirm('<?= __('Do you really want to delete this Pricing scheme?', 'ecalypse-rental') ?>');">
															<div class="form-group">
																<input type="hidden" name="id_pricing" value="<?= $val->id_pricing ?>">
																<?php wp_nonce_field( 'delete_pricing'); ?>
																<button name="delete_pricing" class="btn btn-xs btn-danger"><?php _e('Delete', 'ecalypse-rental');?></button>
															</div>
														</form>
													<?php } ?>
												<?php } ?>
											</td>
						        </tr>
						        
					      	<?php } ?>
					      </tbody>
					    </table>
					    <label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-pricing" /> <?php _e('Select all', 'ecalypse-rental');?></label>
					    <h4><?php _e('Batch action on selected items', 'ecalypse-rental');?></h4>
					    
					    <form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Price scheme is selected to copy.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to copy selected Pricing schemes?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<input type="hidden" name="batch_processing_values" value="">
									<?php wp_nonce_field( 'batch_copy_pricing'); ?>
									<button name="batch_copy_pricing" class="btn btn-warning">Copy <span class="batch_processing_count"></span><?php _e('selected Pricing schemes', 'ecalypse-rental');?></button>
								</div>
							</form>
						
						<?php if (isset($_GET['deleted'])) { ?>
								<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Item is selected to delete.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to delete selected Items?', 'ecalypse-rental') ?>');">
									<div class="form-group">
										<input type="hidden" name="batch_processing_values" value="">
										<?php wp_nonce_field( 'batch_delete_db_pricing'); ?>
										<button name="batch_delete_db_pricing" class="btn btn-danger"><?php _e('Delete', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Pricing schemes from database', 'ecalypse-rental');?></button>
									</div>
								</form>
							<?php } else { ?>
								<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values_delete]').val() == '') { alert(<?php __('No Price scheme is selected to delete.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to delete selected Pricing schemes?', 'ecalypse-rental') ?>');">
									<div class="form-group">
										<input type="hidden" name="batch_processing_values_delete" value="">
										<?php wp_nonce_field( 'batch_delete_pricing'); ?>
										<button name="batch_delete_pricing" class="btn btn-danger"><?php _e('Delete', 'ecalypse-rental');?> <span class="batch_processing_count_delete"></span><?php _e('selected Pricing schemes', 'ecalypse-rental');?></button>
									</div>
								</form>
								<?php } ?>
						
						<?php } else { ?>
							<div class="alert alert-info">
								<span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;
								<?= esc_html__( 'You do not have any Pricing schemes created yet, please create one clicking on "Add New Pricing Scheme".', 'ecalypse-rental' ); ?>
							</div>
						<?php } ?>
					
					</div>
				</div>
				
					
			</div>
		</div>
	</div>
	
</div>

<script type="text/javascript">

	jQuery(document).ready(function() {
		
		<?php if ($edit == true) { ?>
			<?php if ($detail->type == 1) { ?>
				jQuery('.type-onetime').show();
				jQuery('.type-timerelated').hide();
				jQuery('#ecalypse-rental-hour-range-box').hide();
			<?php } else { ?>
				jQuery('.type-onetime').hide();
				jQuery('.type-timerelated').show();
				<?php if (isset($detail->hours) && !empty($detail->hours)) { ?>
					jQuery('#ecalypse-rental-hour-range-box').show();
				<?php } else { ?>
					jQuery('#ecalypse-rental-hour-range-box').hide();
				<?php } ?>
			<?php } ?>
		<?php } else { ?>
			jQuery('.type-onetime').hide();
			jQuery('.type-timerelated').hide();
			jQuery('#ecalypse-rental-hour-range-box').hide();
		<?php } ?>
		
	});

</script>