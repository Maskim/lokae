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
							<h3><?php _e('Edit vehicle:', 'ecalypse-rental');?> <?= $detail->name ?></h3>
						<?php } else { ?>
							<?php if (isset($_GET['deleted'])) { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-fleet')); ?>" class="btn btn-default" style="float:right;"><?php _e('Show normal', 'ecalypse-rental');?></a>
							<?php } else { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-fleet')); ?>&amp;deleted" class="btn btn-default" style="float:right;"><?php _e('Show deleted', 'ecalypse-rental');?></a>
							<?php } ?>

							<a href="javascript:void(0);" class="btn btn-success" id="ecalypse-rental-fleet-add-button"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;<?php _e('Add new vehicle', 'ecalypse-rental');?></a>
							
							<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-fleet-parameters')); ?>" class="btn btn-warning"><span class="glyphicon glyphicon-list"></span>&nbsp;&nbsp;<?php _e('Manage fleet parameters', 'ecalypse-rental');?></a>
						<?php } ?>

						<div id="<?= (($edit == true) ? 'ecalypse-rental-fleet-edit-form' : 'ecalypse-rental-fleet-add-form') ?>" class="ecalypse-rental-add-form">
							<form action="" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">

								<div class="row">
									<div class="col-md-11">

										<div class="alert alert-info">
											<p><span class="glyphicon glyphicon-share-alt"></span>&nbsp;&nbsp;<?php _e('Whichever field is left blank will not be used in car description.', 'ecalypse-rental');?></p>
											<p><span class="glyphicon glyphicon-share-alt"></span>&nbsp;&nbsp;<?php _e('Manage your', 'ecalypse-rental');?> <a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-settings')); ?>#vehicle-categories"><?php _e('Vehicle categories', 'ecalypse-rental');?></a>, <a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>"><?php _e('Pricing schemes', 'ecalypse-rental');?></a> and <a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-extras')); ?>"><?php _e('Extras', 'ecalypse-rental');?></a> <?php _e('first.', 'ecalypse-rental');?></p>
											<?php if ($edit) { ?>
											<p><span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;<?php _e('For this vehicle goals use this URL:', 'ecalypse-rental');?> <strong>/goals/request-done-<?= $detail->id_fleet ?></strong> <?php _e('or you can set event goal with event category', 'ecalypse-rental');?> = <strong>request', 'ecalypse-rental');?></strong> <?php _e('and event action', 'ecalypse-rental');?> = <strong><?php _e('complete', 'ecalypse-rental');?></strong> <?php _e('and event label', 'ecalypse-rental');?> = <strong><?= $detail->id_fleet ?></strong>. <?php _e('For this functionality you need to include Google Analytics code to your website (e.g. by one of GA plugins).', 'ecalypse-rental');?></p>
											<?php } ?>
										</div>

										<!-- Name //-->
										<div class="form-group">
											<label for="ecalypse-rental-type" class="col-sm-3 control-label"><?php _e('Name', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="name" class="form-control" id="ecalypse-rental-type" placeholder="Ford Mondeo / SUV / Mid-size" value="<?= (($edit == true) ? $detail->name : '') ?>">
											</div>
										</div>

										<!-- Vehicle Category //-->
										<div class="form-group">
											<label for="ecalypse-rental-category" class="col-sm-3 control-label"><?php _e('Vehicle category', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<select name="id_category" id="ecalypse-rental-category" class="form-control">
													<option value="none">- <?php _e('none', 'ecalypse-rental');?> -</option>
													<?php if ($vehicle_categories && !empty($vehicle_categories)) { ?>
														<?php foreach ($vehicle_categories as $key => $val) { ?>
															<option value="<?= $val->id_category ?>" <?= (($edit == true && $detail->id_category == $val->id_category) ? 'selected="selected"' : '') ?>><?= $val->name ?></option>
														<?php } ?>
													<?php } ?>
												</select>
												<p class="help-block"><?php _e('To select from vehicle categories, first create them in settings module.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Current location //-->
										<div class="form-group">
											<label for="ecalypse-rental-location" class="col-sm-3 control-label"><?php _e('Current location', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<select name="id_branch" id="ecalypse-rental-location" class="form-control">
													<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
													<option value="-1"><?php _e('Unassigned (unavailable for rent)', 'ecalypse-rental');?></option>
													<?php if ($branches && !empty($branches)) { ?>
														<?php foreach ($branches as $key => $val) { ?>
															<option value="<?= $val->id_branch ?>" <?= (($edit == true && $detail->id_branch == $val->id_branch) ? 'selected="selected"' : '') ?>><?= $val->name ?></option>
														<?php } ?>
													<?php } ?>
												</select>
												<p class="help-block"><?php _e('To select from locations, go to branches module and create a branch.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Global Pricing Scheme //-->
										<div class="form-group">
											<label class="col-sm-3 control-label"><?php _e('Global Pricing scheme', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<select name="global_pricing_scheme" class="form-control">
													<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
													<?php if (isset($pricing) && !empty($pricing)) { ?>
														<?php foreach ($pricing as $key => $val) { ?>
															<option value="<?= $val->id_pricing ?>" <?= (($edit == true && $detail->global_pricing_scheme == $val->id_pricing) ? 'selected="selected"' : '') ?>><?= $val->name ?></option>
														<?php } ?>
													<?php } ?>
												</select>
												<p class="help-block"><?php _e('This pricing scheme is used when no other pricing scheme is active or usable.', 'ecalypse-rental');?></p>
												<p class="help-block"><?php _e('To assign a pricing scheme, go to pricing module and create a pricing scheme.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Price Scheme //-->
										<div class="form-group disabled">
											<label class="col-sm-3 control-label"><abbr title="Highest priority first!"><?php _e('Pricing scheme', 'ecalypse-rental');?></abbr></label>
											<div class="col-sm-9">
												<h4 style="color: #000;margin-top: 0px; margin-bottom: 20px;">Full version of Ecalypse Rental Plugin allows to set time ranges for pricing schemes, seasons, repeats, drag and drop priorities setting and more. <a href="https://wp.ecalypse.com/" target="_blank">Get it here</a></h4>
												<div id="pricing_sort">

													<?php if ($edit == true && isset($detail->pricing) && !empty($detail->pricing)) { ?>
														<?php foreach ($detail->pricing as $key => $val) { ?>

															<!-- Price scheme row //-->
															<div class="row" style="position: relative;" class="sortable">
																<div class="col-xs-4">
																	<select name="" disabled="disabled" class="form-control">
																		<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
																		<?php if (isset($pricing) && !empty($pricing)) { ?>
																			<?php foreach ($pricing as $kD => $vD) { ?>
																				<option value="<?= $vD->id_pricing ?>" <?= (($val->id_pricing == $vD->id_pricing) ? 'selected="selected"' : '') ?>><?= $vD->name ?></option>
																			<?php } ?>
																		<?php } ?>
																	</select>
																</div>
																<div class="col-xs-3">
																	<div class="form-group has-feedback">
																		<input type="text" name="" disabled="disabled" class="form-control" placeholder="Valid from" value="<?= (($val->valid_from != '0000-00-00') ? $val->valid_from : '') ?>">
																		<span class="glyphicon glyphicon-calendar form-control-feedback"></span>
																	</div>
																</div>
																<div class="col-xs-3">
																	<div class="form-group has-feedback">
																		<input type="text" name="" disabled="disabled" class="form-control" placeholder="Valid until" value="<?= (($val->valid_to != '0000-00-00') ? $val->valid_to : '') ?>">
																		<span class="glyphicon glyphicon-calendar form-control-feedback"></span>
																	</div>
																</div>
																<div class="col-xs-1" style="padding-right: 0px;">
																	<div class="checkbox">
																		<label title="Repeat every year">
																			<input type="checkbox" name="" disabled="disabled" value="1" <?= ($val->repeat == 1 ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Repeat', 'ecalypse-rental');?>
																		</label>
																	</div>
																</div>
																<div class="col-xs-1">
																	<span class="glyphicon glyphicon-sort" style="margin-top:9px;cursor:move;" title="Move up or down to sort Price scheme. Highest priority first!"></span>
																</div>														
															</div><!-- .row //-->

														<?php } ?>
													<?php } ?>

													<div id="ecalypse-rental-prices">
														<!-- Price scheme row //-->
														<div class="row" style="position: relative;" class="sortable">
															<div class="col-xs-4">
																<select name="" disabled="disabled" class="form-control">
																	<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
																	<?php if (isset($pricing) && !empty($pricing)) { ?>
																		<?php foreach ($pricing as $key => $val) { ?>
																			<option value="<?= $val->id_pricing ?>"><?= $val->name ?></option>
																		<?php } ?>
																	<?php } ?>
																</select>
															</div>
															<div class="col-xs-3">
																<div class="form-group has-feedback">
																	<input type="text" name="" class="form-control" placeholder="Valid from">
																	<span class="glyphicon glyphicon-calendar form-control-feedback"></span>
																</div>
															</div>
															<div class="col-xs-3">
																<div class="form-group has-feedback">
																	<input type="text" name="" class="form-control" placeholder="Valid until">
																	<span class="glyphicon glyphicon-calendar form-control-feedback"></span>
																</div>
															</div>
															<div class="col-xs-1"  style="padding-right: 0px;">
																<div class="checkbox">
																	<label title="Repeat every year">
																		<input type="checkbox" name="" disabled="disabled" value="1" <?= ($val->repeat == 1 ? 'checked="checked"' : '') ?>>&nbsp; <?php _e('Repeat', 'ecalypse-rental');?>
																	</label>
																</div>
															</div>
															<div class="col-xs-1">
																<span class="glyphicon glyphicon-sort" style="margin-top:9px;cursor:move;" title="Move up or down to sort Price scheme. Highest priority first!"></span>
															</div>														
														</div><!-- .row //-->
													</div>

													<div id="ecalypse-rental-prices-insert"></div>
													<p class="help">*<?php _e('select repeat to repeat this pricing scheme each year', 'ecalypse-rental');?></p>
												</div>
												<a href="javascript:void(0);" id="" class="btn btn-info btn-xs"><?php _e('Add Pricing Scheme', 'ecalypse-rental');?></a>
											</div>
										</div>
										
										<!-- Price from //-->
										<div class="form-group">
											<label for="price-from" class="col-sm-3 control-label"><?php _e('Price from', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<div class="input-group">
													<?php $global_currency = get_option('ecalypse_rental_global_currency');?>
													<?php if (EcalypseRental::get_currency_symbol('before', $global_currency) != '') {?><span class="input-group-addon"><?= EcalypseRental::get_currency_symbol('before', $global_currency) ?></span><?php } ?>
													<input type="text" name="price_from" class="form-control" id="price-from" placeholder="Set default price from" value="<?= (($edit == true) ? $detail->price_from : '') ?>">	
													<?php if (EcalypseRental::get_currency_symbol('after', $global_currency) != '') {?><span class="input-group-addon"><?= EcalypseRental::get_currency_symbol('after', $global_currency) ?></span><?php } ?>
												</div>
												<p class="help-block">* <?php _e('if you want to set a price from displayed on the front end when clients browse through cars, insert it here.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Extras //-->
										<div class="form-group">
											<label for="ecalypse-rental-extras" class="col-sm-3 control-label"><?php _e('Extras', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<?php if ($extras && !empty($extras)) { ?>
													<?php foreach ($extras as $key => $val) { ?>
														<div class="checkbox">
															<label>
																<input type="checkbox" name="extras[]" value="<?= $val->id_extras ?>" <?= (($edit == true && !empty($detail->extras) && in_array($val->id_extras, explode(',', $detail->extras))) ? 'checked="checked"' : '') ?>>&nbsp; <?= $val->name_admin == '' ? $val->name : $val->name_admin ?>
															</label>
														</div>
													<?php } ?>
												<?php } ?>
												<p class="help-block"><?php _e('To select what extras are offered with this vehicle, create them in Extras module first.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Minimum rental time //-->
										<div class="form-group">
											<label for="ecalypse-rental-min-time" class="col-sm-3 control-label"><?php _e('Minimum rental time', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="min_rental_time" class="form-control" id="ecalypse-rental-min-time" placeholder="In hours: 1, 2, 4, 8, 12, 24, ..." value="<?= (($edit == true) ? $detail->min_rental_time : '') ?>">
												<p class="help-block"><?php _e('In whole hours, minimum value', 'ecalypse-rental');?> = 1</p>
											</div>
										</div>

										<!-- Number of Seats //-->
										<div class="form-group">
											<label for="ecalypse-rental-seats" class="col-sm-3 control-label"><?php _e('Seats', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="seats" class="form-control" id="ecalypse-rental-seats" placeholder="Number of seats: 2, 4, 5, 6, 7, ..." value="<?= (($edit == true) ? $detail->seats : '') ?>">
											</div>
										</div>

										<!-- Number of Doors //-->
										<div class="form-group">
											<label for="ecalypse-rental-doors" class="col-sm-3 control-label"><?php _e('Doors', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="doors" class="form-control" id="ecalypse-rental-doors" placeholder="Number of doors: 2, 4, 5" value="<?= (($edit == true) ? $detail->doors : '') ?>">
											</div>
										</div>

										<!-- Number of Luggage //-->
										<div class="form-group">
											<label for="ecalypse-rental-luggage" class="col-sm-3 control-label"><?php _e('Luggage', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="luggage" class="form-control" id="ecalypse-rental-luggage" placeholder="Number of luggage: 2, 3, 4, 5, ..." value="<?= (($edit == true) ? $detail->luggage : '') ?>">
											</div>
										</div>

										<!-- Transmission //-->
										<div class="form-group">
											<label for="ecalypse-rental-transmission" class="col-sm-3 control-label"><?php _e('Transmission', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<label class="radio-inline">
													<input type="radio" name="transmission" id="ecalypse-rental-transmission-automatic" value="0" <?= (($detail->transmission == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Not use', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
													<input type="radio" name="transmission" id="ecalypse-rental-transmission-automatic" value="1" <?= (($detail->transmission == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Automatic', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
													<input type="radio" name="transmission" id="ecalypse-rental-transmission-manual" value="2" <?= (($detail->transmission == 2) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Manual', 'ecalypse-rental');?>
												</label>
											</div>
										</div>

										<!-- Free km / miles //-->
										<div class="form-group">
											<label for="ecalypse-rental-free-dist" class="col-sm-3 control-label"><?php _e('Free distance (km/mi per day)', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="free_distance" class="form-control" id="ecalypse-rental-free-dist" placeholder="Free distance in kilometers or miles." value="<?= (($edit == true) ? $detail->free_distance : '') ?>">
												<p class="help-block">0 = <?php _e('unlimited', 'ecalypse-rental');?></p>
											</div>
										</div>
										
										<?php $use_free_hour_km = get_option('ecalypse_rental_use_free_hour_km', 'yes'); ?>
										<?php if ($use_free_hour_km == 'yes') { ?>
											<!-- Free km / miles //-->
											<div class="form-group">
												<label for="ecalypse-rental-free-dist-hour" class="col-sm-3 control-label"><?php _e('Hourly free distance (km/mi per hour)', 'ecalypse-rental');?></label>
												<div class="col-sm-9">
													<input type="text" name="free_distance_hour" class="form-control" id="ecalypse-rental-free-dist-hour" placeholder="Free distance in kilometers or miles." value="<?= (($edit == true) ? $detail->free_distance_hour : '') ?>">
													<p class="help-block">0 = <?php _e('unlimited', 'ecalypse-rental');?></p>
												</div>
											</div>
										<?php } ?>

										<!-- A/C //-->
										<div class="form-group">
											<label for="ecalypse-rental-ac" class="col-sm-3 control-label">A/C</label>
											<div class="col-sm-9">
												<label class="radio-inline">
													<input type="radio" name="ac" id="ecalypse-rental-ac-not" value="0" <?= (($detail->ac == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Not use', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
													<input type="radio" name="ac" id="ecalypse-rental-ac-yes" value="1" <?= (($detail->ac == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
													<input type="radio" name="ac" id="ecalypse-rental-ac-no" value="2" <?= (($detail->ac == 2) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('No', 'ecalypse-rental');?>
												</label>
											</div>
										</div>

										<!-- Fuel //-->
										<div class="form-group">
											<label for="ecalypse-rental-ac" class="col-sm-3 control-label"><?php _e('Fuel', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<label class="radio-inline">
													<input type="radio" name="fuel" id="ecalypse-rental-fuel-not" value="0" <?= (($detail->fuel == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Not use', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
													<input type="radio" name="fuel" id="ecalypse-rental-fuel-yes" value="1" <?= (($detail->fuel == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Petrol', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
													<input type="radio" name="fuel" id="ecalypse-rental-fuel-no" value="2" <?= (($detail->fuel == 2) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Diesel', 'ecalypse-rental');?>
												</label>
												<p class="help-block"><?php _e('Add more options by going to fleet -> manage fleet parameters.', 'ecalypse-rental');?></p>
											</div>
										</div>
										
										<!-- All custom parameters //-->
										<?php foreach ($params as $param) { ?>
											<?php $name = unserialize($param->name);?>
											<!-- <?php echo $name['gb'];?> //-->
											<div class="form-group">
												<label for="ecalypse-rental-param-<?php echo $param->id_fleet_parameter;?>" class="col-sm-3 control-label"><?php echo $name['gb'];?></label>
												<div class="col-sm-9">
													<?php if ($param->type == 2) { ?>
														<?php $values = unserialize($param->values); ?>
														<?php foreach ($values['gb'] as $key => $value) { ?>
															<label class="radio-inline">
																<input type="radio" name="custom_parameters[<?php echo $param->id_fleet_parameter;?>]" value="<?php echo $key;?>"<?php echo isset($params_values[$param->id_fleet_parameter]) && $params_values[$param->id_fleet_parameter] == $key ? ' checked="checked"' : '';?>>&nbsp;&nbsp;<?php echo $value == '' ? '-not-set-' : $value;?>
															</label>
														<?php } ?>
													<?php } else { ?>
														<input type="text" name="custom_parameters[<?php echo $param->id_fleet_parameter;?>]" class="form-control" id="ecalypse-rental-param-<?php echo $param->id_fleet_parameter;?>" value="<?php echo isset($params_values[$param->id_fleet_parameter]) ? $params_values[$param->id_fleet_parameter] : '';?>">
														<p class="help-block"><?php _e('Enter number between', 'ecalypse-rental');?> <?php echo $param->range_from;?> <?php _e('and', 'ecalypse-rental');?> <?php echo $param->range_to;?>.</p>
													<?php } ?>
												</div>
											</div>
										<?php } ?>
										
										<!-- Number of vehicles //-->
										<div class="form-group">
											<label for="ecalypse-rental-number-vehicles" class="col-sm-3 control-label"><?php _e('Available vehicles', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="number_vehicles" class="form-control" id="ecalypse-rental-number-vehicles" placeholder="Number of available vehicles." value="<?= (($edit == true) ? $detail->number_vehicles : '') ?>">
												<p class="help-block"><?php _e('If you allow branches overbooking in Settings module, this value will be overridden and set to unlimited.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Consumption //-->
										<div class="form-group">
											<label for="ecalypse-rental-consumption" class="col-sm-3 control-label"><?php _e('Consumption', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="consumption" class="form-control" id="ecalypse-rental-consumption" placeholder="Vehicle consumption (in l/100 km or MPG)" value="<?= (($edit == true) ? $detail->consumption : '') ?>">
												<p class="help-block"><?php _e('Set mpg or l/100km in Settings module.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Additional parameters //-->
										<div class="form-group">
											<label class="col-sm-3 control-label"><?php _e('Parameters', 'ecalypse-rental');?></label>

											<div class="col-sm-9">
												<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
													<li role="presentation" class="active"><a href="javascript:void(0);" class="edit_fleet_parameters" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
													<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
													<?php if ($available_languages && !empty($available_languages)) { ?>														
														<?php foreach ($available_languages as $key => $val) { ?>
															<?php if ($val['country-www'] == 'gb') {continue;} ?>
															<li role="presentation"><a href="javascript:void(0);" class="edit_fleet_parameters" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
														<?php } ?>
													<?php } ?>
												</ul>
												
												<div id="additional_parameters_sort_gb" class="additional_parameters_tab" data-lng="gb">
													<div id="ecalypse-rental-additional-parameters-gb" style="display: none;">
														<!-- Additional parameter row //-->
														<div class="row" style="position: relative;" class="sortable" data-row-i="0">
															<div class="col-xs-3">
																<div class="">
																	<input type="text" name="additional_parameters[gb][0][name]" class="form-control fleet-parameter-name" placeholder="Parameter name">
																</div>
															</div>
															<div class="col-xs-4">
																<div class="form-group has-feedback">
																	<input type="text" name="additional_parameters[gb][0][value]" class="form-control" placeholder="Parameter value">
																</div>
															</div>
															<div class="col-xs-1">
																<span class="glyphicon glyphicon-sort" style="margin-top:9px;cursor:move;" title="Move up or down to sort parameters!"></span>
																<span class="glyphicon glyphicon-remove fleet-delete-parameter" style="margin-top:9px;margin-left: 5px;cursor:pointer;" title="Remove this parameter!"></span>
															</div>
														</div><!-- .row //-->
													</div>
													<?php if ($edit == true && isset($detail->additional_parameters) && !empty($detail->additional_parameters)) { ?>
														<?php $additional_parameters = unserialize($detail->additional_parameters); ?>														
														<?php if (!isset($additional_parameters['gb'])) { $additional_parameters['gb'] = array();} ?>
														<?php $i =0;foreach ($additional_parameters['gb'] as $key => $val) { $i++; ?>
															<!-- Additional parameter row //-->
															<div class="row" style="position: relative;" class="sortable" data-row-i="<?php echo $i;?>">
																<div class="col-xs-3">
																	<div class="">
																		<input type="text" name="additional_parameters[gb][<?php echo $i;?>][name]" class="form-control fleet-parameter-name" placeholder="<?php echo EcalypseRental_Admin::fleet_placeholder_param($i, $additional_parameters);?>" value="<?php echo $val['name'];?>">
																	</div>
																</div>
																<div class="col-xs-4">
																	<div class="form-group has-feedback">
																		<input type="text" name="additional_parameters[gb][<?php echo $i;?>][value]" class="form-control" placeholder="Parameter value" value="<?php echo $val['value'];?>">
																	</div>
																</div>
																<div class="col-xs-1">
																	<span class="glyphicon glyphicon-sort" style="margin-top:9px;cursor:move;" title="Move up or down to sort parameters!"></span>
																	<span class="glyphicon glyphicon-remove fleet-delete-parameter" style="margin-top:9px;margin-left: 5px;cursor:pointer;" title="Remove this parameter!"></span>
																</div>
															</div><!-- .row //-->														
														<?php } ?>
													<?php } ?>
													
													<div id="ecalypse-rental-additional-parameters-insert-gb"></div>
													
													<div class="ecalypse-rental-insert-existing-parameter" style="margin-bottom:10px;">
														<?php if (isset($all_additional_parameters['gb'])) { ?>
															<?php foreach ($all_additional_parameters['gb'] as $k => $v) { ?>
															<?php if ($v == '') { continue;}?>
															<a href="#" class="ecalypse-rental-insert-parameter-link" data-name="<?php echo $v;?>"><?php echo $v;?></a> | 
															<?php } ?>
														<?php } ?>
													</div>
												</div>
												
												<?php if ($available_languages && !empty($available_languages)) { ?>														
													<?php foreach ($available_languages as $key => $val) { ?>
														<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div id="additional_parameters_sort_<?php echo $val['country-www'];?>" class="additional_parameters_tab" data-lng="<?php echo $val['country-www'];?>" style="display: none;">
															<div id="ecalypse-rental-additional-parameters-<?php echo $val['country-www'];?>" style="display: none;">
																<!-- Additional parameter row //-->
																<div class="row" style="position: relative;" class="sortable" data-row-i="0">
																	<div class="col-xs-3">
																		<div class="">
																			<input type="text" name="additional_parameters[<?php echo $val['country-www'];?>][0][name]" class="form-control fleet-parameter-name" placeholder="Parameter name">
																		</div>
																	</div>
																	<div class="col-xs-4">
																		<div class="form-group has-feedback">
																			<input type="text" name="additional_parameters[<?php echo $val['country-www'];?>][0][value]" class="form-control" placeholder="Parameter value">
																		</div>
																	</div>
																	<div class="col-xs-1">
																		<span class="glyphicon glyphicon-sort" style="margin-top:9px;cursor:move;" title="Move up or down to sort parameters!"></span>
																		<span class="glyphicon glyphicon-remove fleet-delete-parameter" style="margin-top:9px;margin-left: 5px;cursor:pointer;" title="Remove this parameter!"></span>
																	</div>
																</div><!-- .row //-->
															</div>
															<?php if ($edit == true && isset($detail->additional_parameters) && !empty($detail->additional_parameters)) { ?>
																<?php $additional_parameters = unserialize($detail->additional_parameters); ?>
																<?php if (!isset($additional_parameters[$val['country-www']])) { $additional_parameters[$val['country-www']] = array();} ?>
																<?php $i =0;foreach ($additional_parameters[$val['country-www']] as $pkey => $pval) { $i++; ?>
																	<!-- Additional parameter row //-->
																	<div class="row" style="position: relative;" class="sortable" data-row-i="<?php echo $i;?>">
																		<div class="col-xs-3">
																			<div class="">
																				<input type="text" name="additional_parameters[<?php echo $val['country-www'];?>][<?php echo $i;?>][name]" class="form-control fleet-parameter-name" placeholder="<?php echo EcalypseRental_Admin::fleet_placeholder_param($i, $additional_parameters);?>" value="<?php echo $pval['name'];?>">
																			</div>
																		</div>
																		<div class="col-xs-4">
																			<div class="form-group has-feedback">
																				<input type="text" name="additional_parameters[<?php echo $val['country-www'];?>][<?php echo $i;?>][value]" class="form-control" placeholder="Parameter value" value="<?php echo $pval['value'];?>">
																			</div>
																		</div>
																		<div class="col-xs-1">
																			<span class="glyphicon glyphicon-sort" style="margin-top:9px;cursor:move;" title="Move up or down to sort parameters!"></span>
																			<span class="glyphicon glyphicon-remove fleet-delete-parameter" style="margin-top:9px;margin-left: 5px;cursor:pointer;" title="Remove this parameter!"></span>
																		</div>
																	</div><!-- .row //-->														
																<?php } ?>
															<?php } ?>

															<div id="ecalypse-rental-additional-parameters-insert-<?php echo $val['country-www'];?>"></div>
															<div class="ecalypse-rental-insert-existing-parameter" style="margin-bottom:10px;">
																<?php if (isset($all_additional_parameters[$val['country-www']])) { ?>
																	<?php foreach ($all_additional_parameters[$val['country-www']] as $k => $v) { ?>
																	<?php if ($v == '') { continue;}?>
																	<a href="#" class="ecalypse-rental-insert-parameter-link" data-name="<?php echo $v;?>"><?php echo $v;?></a> | 
																	<?php } ?>
																<?php } ?>
															</div>
														</div>
													<?php } ?>
												<?php } ?>
												
												<a href="javascript:void(0);" id="ecalypse-rental-add-additional-parameter" class="btn btn-info btn-xs"><?php _e('Add New Parameter', 'ecalypse-rental');?></a>
											</div>
										</div>

										<!-- Description //-->
										<div class="form-group">
											<label for="ecalypse-rental-description" class="col-sm-3 control-label"><?php _e('Description', 'ecalypse-rental');?></label>
											<div class="col-sm-9">

												<ul class="nav nav-tabs" role="tablist">
													<li role="presentation" class="active"><a href="javascript:void(0);" class="edit_fleet_description" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
													<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
													<?php if ($available_languages && !empty($available_languages)) { ?>
														<?php foreach ($available_languages as $key => $val) { ?>
															<?php if ($val['country-www'] == 'gb') {continue;} ?>
															<li role="presentation"><a href="javascript:void(0);" class="edit_fleet_description" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
														<?php } ?>
													<?php } ?>
												</ul>

												<?php if ($edit == true) { ?>
													<?php $fleet_description = unserialize($detail->description); ?>
													<?php
													if ($fleet_description == false) {
														$fleet_description['gb'] = $detail->description;
													}
													?>
												<?php } ?>

												<textarea class="form-control fleet_description fleet_description_gb" name="description[gb]" id="ecalypse-rental-description" rows="3" placeholder="Brief description of cars in English (GB)."><?= ((isset($fleet_description['gb']) && !empty($fleet_description['gb'])) ? $fleet_description['gb'] : '') ?></textarea>
												<?php if ($available_languages && !empty($available_languages)) { ?>
													<?php foreach ($available_languages as $key => $val) { ?>
														<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<textarea class="form-control fleet_description fleet_description_<?= strtolower($val['country-www']) ?>" name="description[<?= strtolower($val['country-www']) ?>]" rows="3" placeholder="Brief description of cars in <?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)."><?= ((isset($fleet_description[strtolower($val['country-www'])]) && !empty($fleet_description[strtolower($val['country-www'])])) ? EcalypseRental::removeslashes($fleet_description[strtolower($val['country-www'])]) : '') ?></textarea>
	<?php } ?>
<?php } ?>
												<p class="help-block"><?php _e('This is shown under "show more info', 'ecalypse-rental');?>".</p>
											</div>
										</div>

										<!-- Deposit //-->
										<div class="form-group">
											<label for="ecalypse-rental-deposit" class="col-sm-3 control-label"><?php _e('Deposit', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="deposit" class="form-control" id="ecalypse-rental-deposit" placeholder="How much the deposit on the car will be." value="<?= (($edit == true) ? $detail->deposit : '') ?>">
												<p class="help-block"><?php _e('This field is only informative in car details. Leave empty to hide. Set to 0 to show 0.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- License registration number //-->
										<div class="form-group">
											<label for="ecalypse-rental-license" class="col-sm-3 control-label"><?php _e('License registration number', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="license" class="form-control" id="ecalypse-rental-license" value="<?= (($edit == true) ? $detail->license : '') ?>">
											</div>
										</div>

										<!-- VIN code //-->
										<div class="form-group">
											<label for="ecalypse-rental-vin" class="col-sm-3 control-label"><?php _e('VIN code', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="vin" class="form-control" id="ecalypse-rental-vin" value="<?= (($edit == true) ? $detail->vin : '') ?>">
											</div>
										</div>

										<!-- Internal Car ID //-->
										<div class="form-group">
											<label for="ecalypse-rental-internal-id" class="col-sm-3 control-label"><?php _e('Internal car ID', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="internal_id" class="form-control" id="ecalypse-rental-internal-id" value="<?= (($edit == true) ? $detail->internal_id : '') ?>">
											</div>
										</div>

										<!-- Class Code //-->
										<div class="form-group">
											<label for="ecalypse-rental-class-code" class="col-sm-3 control-label"><?php _e('Class code', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<input type="text" name="class_code" class="form-control" id="ecalypse-rental-class-code" value="<?= (($edit == true) ? $detail->class_code : '') ?>">
												<p class="help-block"><?php _e('If using TSDweb extension, insert your TSD car class code here; else, use for internal records.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Picture of vehicle //-->
										<div class="form-group">
											<label for="ecalypse-rental-picture" class="col-sm-3 control-label"><?php _e('Main picture of vehicle', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
<?php if ($edit == true) { ?>
													<div class="panel panel-info">
														<div class="panel-heading"><?php _e('Current picture', 'ecalypse-rental');?></div>
														<div class="panel-body">
															<p><img src="<?= $detail->picture ?>" height="80"></p>
														</div>
													</div>
													<p><strong><?php _e('Or you can upload new picture for Vehicle:', 'ecalypse-rental');?></strong></p>
<?php } ?>
												<input type="file" name="picture" id="ecalypse-rental-picture">
												<p class="help-block"><?php _e('Insert picture of the item or service, 400x400px.', 'ecalypse-rental');?></p>
											</div>
										</div>

										<!-- Additional pictures of vehicle //-->
										<div class="form-group">
											<label for="ecalypse-rental-picture" class="col-sm-3 control-label"><?php _e('Additional pictures', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<div class="panel panel-info">
													<div class="panel-heading"><?php _e('Additional pictures', 'ecalypse-rental');?></div>
													<div class="panel-body">
														<ul class="additional-pictures" id="additional-pictures-ul">

															<?php if (isset($detail->additional_pictures) && !empty($detail->additional_pictures)) { ?>
																<?php $detail->additional_pictures = unserialize($detail->additional_pictures); ?>
																<?php if (is_array($detail->additional_pictures)) { ?>
																	<?php foreach ($detail->additional_pictures as $picture) { ?>
																		<li><input type="hidden" name="additional-pictures[]" value="<?php echo $picture; ?>" class="media-input" /><img src="<?php echo $picture; ?>" /><div class="buttons"><a href="#" class="btn btn-danger btn-block delete-button">X</a></div></li>
																	<?php } ?>
	<?php } ?>
<?php } ?>
														</ul>
													</div>
												</div>
												<button class="media-button">Add new picture</button>
											</div>
										</div>
										
										<div class="form-group">
											<label for="ecalypse-rental-picture" class="col-sm-3 control-label"><?php _e('Similar cars', 'ecalypse-rental');?></label>
											<div class="col-sm-9">
												<div class="panel panel-info">
													<div class="panel-heading"><?php _e('Similar cars', 'ecalypse-rental');?></div>
														<div class="panel-body">
															<div class="" id="ecalypse_rental_similar_cars_div">
																<?php if (isset($detail)) { ?>
																	<?php $detail->similar_cars = unserialize($detail->similar_cars); ?>

																	<?php if ($detail->similar_cars && !empty($detail->similar_cars)) { ?>
																		<?php foreach ($detail->similar_cars as $key => $val) { ?>
																				<span style="margin-right:5px;margin-bottom:5px;" class="ecalypse_rental_similar_car btn btn-warning"><?php echo $fleet_by_id[$val];?> <a href="#" class="ecalypse_rental_remove_similar_car">X</a><input type="hidden" name="similar_cars[]" value="<?php echo $key;?>"></span>
																		<?php } ?>
																	<?php } else { ?>
																		<p><?php _e('You have no similar cars yet.', 'ecalypse-rental');?></p>	
																	<?php } ?>
																<?php } ?>
															</div>

														<!-- .row //-->

														<div class="row">
															<div class="col-md-6">

																<h4><?php _e('Add new similar car', 'ecalypse-rental');?></h4>

																<div>																	
																	<select id="ecalypse-rental-similar-car-select" class="form-control">
																		<option value=""><?php _e('Select car', 'ecalypse-rental');?></option>
																		<?php foreach ($fleet as $f) { ?>
																			<?php if ($edit == true && $f->id_fleet == $detail->id_fleet) { continue;}?>
																			<option value="<?php echo $f->id_fleet;?>"><?php echo $f->name.' (ID: '.$f->id_fleet.')';?></option>
																		<?php } ?>
																	</select>
																</div>
																<button id="ecalypse_rental_add_similar_car" class="btn btn-success"><?php _e('Add this car', 'ecalypse-rental');?></button>
															</div>
														</div>
													<!-- .row //-->
													</div>
												</div>
											</div>
											<!-- .panel-body //-->
										</div>

										<!-- Submit //-->
										<div class="form-group">
											<div class="col-sm-offset-3 col-sm-9">
												<?php wp_nonce_field( 'add_fleet'); ?>
<?php if ($edit == true) { ?>
													<input type="hidden" name="id_fleet" value="<?= $detail->id_fleet ?>">
													<input type="hidden" name="current_picture" value="<?= $detail->picture ?>">
													<button type="submit" class="btn btn-warning" name="add_fleet"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
												<?php } else { ?>
													<button type="submit" class="btn btn-warning" name="add_fleet"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Add', 'ecalypse-rental');?></button>
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

						<?php if (isset($fleet) && !empty($fleet)) { ?>

							<?php $distance_metric = get_option('ecalypse_rental_distance_metric'); ?>
	<?php $consumption = get_option('ecalypse_rental_consumption'); ?>
	<?php $currency = get_option('ecalypse_rental_global_currency'); ?>

							<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-fleet" /> <?php _e('Select all', 'ecalypse-rental');?></label>
							<table class="table table-striped" id="ecalypse-rental-fleet">
								<thead>
									<tr>
										<th>#</th>
										<th><?php _e('Image', 'ecalypse-rental');?></th>
										<th><?php _e('Name', 'ecalypse-rental');?></th>
										<th><?php _e('Pricing schemes', 'ecalypse-rental');?></th>
										<th><?php _e('Parameters', 'ecalypse-rental');?></th>
										<th><?php _e('Parameters', 'ecalypse-rental');?></th>
										<th><?php _e('Extras', 'ecalypse-rental');?></th>
										<th><?php _e('Action', 'ecalypse-rental');?></th>
									</tr>
								</thead>
								<tbody>
	<?php foreach ($fleet as $key => $val) { ?>
										<tr fleetId="<?php echo $val->id_fleet; ?>">
											<td>
												<input type="checkbox" class="input-control batch_processing" name="batch[]" value="<?= $val->id_fleet ?>">&nbsp;
												<abbr title="Created: <?= $val->created ?>

		<?= (!empty($val->updated) ? 'Updated: ' . $val->updated : '') ?>"><?= $val->id_fleet ?></abbr>
											</td>
											<td class="sortableTD"><img src="<?= $val->picture ?>" height="120"></td>
											<td class="sortableTD">
												<strong><?= (!empty($val->name) ? $val->name : '- Unknown -') ?></strong>
												<?php if ($val->id_branch == -1) { ?>
													<br><small><?php _e('Unassigned (unavailable for rent)', 'ecalypse-rental');?></small>
												<?php } elseif (!empty($val->branch_name)) { ?>
													<br><small><?php _e('(Loc.: ', 'ecalypse-rental');?><?= $val->branch_name ?>)</small>
												<?php } ?>
											</td>
											<td>
												<?php if (!empty($val->pricing_name)) { ?>
													<p><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>&amp;<?= (($val->pricing_type == 1) ? 'get_onetime_price' : 'get_day_ranges') ?>=<?= $val->global_pricing_scheme ?>" class="btn <?= (($val->pricing_type == 1) ? 'btn-info' : 'btn-success') ?> ecalypse_rental_show_ranges"><?= $val->pricing_name ?></a></p>
													<?php if ($val->pricing_count > 0) { ?>
														<p><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>&amp;get_fleet_price_schemes=<?= $val->id_fleet ?>" class="btn <?= (($val->pricing_type == 1) ? 'btn-info' : 'btn-success') ?> ecalypse_rental_show_ranges">+ <?= $val->pricing_count ?> <?php _e('schemes', 'ecalypse-rental');?></a></p>
													<?php } ?>
												<?php } else { ?>
													<p><em>- <?php _e('none', 'ecalypse-rental');?> -</em></p>
		<?php } ?>
											</td>
											<td class="sortableTD">
												<table class="table ecalypse-rental-fleet-parameters">
													<tr>
														<td><?php _e('Min. rent. time', 'ecalypse-rental');?></td>
														<td><?= $val->min_rental_time ?> h</td>
													</tr>
													<tr>
														<td><?php _e('Seats/Doors/Luggage', 'ecalypse-rental');?></td>
														<td><?= $val->seats ?>/<?= $val->doors ?>/<?= $val->luggage ?></td>
													</tr>
													<tr>
														<td><?php _e('Transmission', 'ecalypse-rental');?></td>
														<td>
															<?php if ($val->transmission == 1) { ?>
																<?php _e('Automatic', 'ecalypse-rental');?>
															<?php } elseif ($val->transmission == 2) { ?>
																<?php _e('Manual', 'ecalypse-rental');?>
															<?php } else { ?>
																<?php _e('Not use', 'ecalypse-rental');?>
		<?php } ?>
														</td>
													</tr>
													<tr>
														<td>AC</td>
														<td>
															<?php if ($val->ac == 1) { ?>
																<?php _e('YES', 'ecalypse-rental');?>
															<?php } elseif ($val->ac == 2) { ?>
																<?php _e('NO', 'ecalypse-rental');?>
															<?php } else { ?>
																<?php _e('Not use', 'ecalypse-rental');?>
		<?php } ?>
														</td>
													</tr>
													<tr>
														<td><?php _e('Fuel', 'ecalypse-rental');?></td>
														<td>
															<?php if ($val->fuel == 1) { ?>
																<?php _e('Petrol', 'ecalypse-rental');?>
															<?php } elseif ($val->fuel == 2) { ?>
																<?php _e('Diesel', 'ecalypse-rental');?>
															<?php } else { ?>
																<?php _e('Not use', 'ecalypse-rental');?>
		<?php } ?>
														</td>
													</tr>
												</table>
											</td>
											<td class="sortableTD">
												<table class="table table-condensed ecalypse-rental-fleet-parameters">
													<tr>
														<td><?php _e('Free distance', 'ecalypse-rental');?></td>
														<td><?= $val->free_distance ?>&nbsp;<?= (!empty($distance_metric) ? ' ' . $distance_metric : '') ?></td>
													</tr>
													<tr>
														<td><?php _e('Consumption', 'ecalypse-rental');?></td>
														<td><?= $val->consumption ?>&nbsp;<?php
													if (!empty($consumption)) {
														echo ($consumption == 'us' ? ' MPG' : ' l/100km');
													}
													?></td>
													</tr>
													<tr>
														<td><?php _e('Available vehicles', 'ecalypse-rental');?></td>
														<td><?= $val->number_vehicles ?></td>
													</tr>
													<tr>
														<td><?php _e('Deposit', 'ecalypse-rental');?></td>
														<td><?= $val->deposit ?>&nbsp;<?= (!empty($currency) ? ' ' . $currency : '') ?></td>
													</tr>
												</table>
											</td>
											<td>
													<?php if ($extras && !empty($extras) && !empty($val->extras)) { ?>
													<ul>
														<?php foreach ($extras as $kD => $vD) { ?>
														<?php if (in_array($vD->id_extras, explode(',', $val->extras))) { ?>
																<li><?= $vD->name ?></li>
				<?php } ?>
			<?php } ?>
													</ul>
		<?php } ?>
											</td>
											<td>
												<form action="" method="post" class="form" role="form">
													<div class="form-group">
														<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-fleet')); ?>&amp;edit=<?= $val->id_fleet ?>" class="btn btn-primary btn-block"><?php _e('Modify', 'ecalypse-rental');?></a>
													</div>
												</form>
												<form action="" method="post" class="form" role="form">
													<div class="form-group">
														<input type="hidden" name="id_fleet" value="<?= $val->id_fleet ?>">
														<?php wp_nonce_field( 'copy_fleet'); ?>
														<button name="copy_fleet" class="btn btn-warning btn-block"><?php _e('Copy', 'ecalypse-rental');?></button>
													</div>
												</form>
		<?php if (isset($_GET['deleted'])) { ?>
													<form action="" method="post" class="form" role="form" onsubmit="return confirm('<?= __('Do you really want to restore this Vehicle?', 'ecalypse-rental') ?>');">
														<div class="form-group">
															<input type="hidden" name="id_fleet" value="<?= $val->id_fleet ?>">
															<?php wp_nonce_field( 'restore_fleet'); ?>
															<button name="restore_fleet" class="btn btn-success btn-block"><?php _e('Restore', 'ecalypse-rental');?></button>
														</div>
													</form>
		<?php } else { ?>
													<form action="" method="post" class="form" role="form" onsubmit="return confirm('<?= __('Do you really want to delete this Vehicle?', 'ecalypse-rental') ?>');">
														<div class="form-group">
															<input type="hidden" name="id_fleet" value="<?= $val->id_fleet ?>">
															<?php wp_nonce_field( 'delete_fleet'); ?>
															<button name="delete_fleet" class="btn btn-danger btn-block"><?php _e('Delete', 'ecalypse-rental');?></button>
														</div>
													</form>
										<?php } ?>
											</td>
										</tr>

	<?php } ?>
								</tbody>
							</table>
							<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-fleet" /> <?php _e('Select all', 'ecalypse-rental');?></label>

							<h4><?php _e('Batch action on selected items', 'ecalypse-rental');?></h4>

							<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') {
										alert(<?php __('No Vehicle is selected to copy.', 'ecalypse-rental');?>);
										return false
									}
									;
									return confirm('<?= __('Do you really want to copy selected Vehicles?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<input type="hidden" name="batch_processing_values" value="">
									<?php wp_nonce_field( 'batch_copy_fleet'); ?>
									<button name="batch_copy_fleet" class="btn btn-warning"><?php _e('Copy', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Vehicles', 'ecalypse-rental');?></button>
								</div>
							</form>
							<?php if (isset($_GET['deleted'])) { ?>
								<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') {
											alert(<?php __('No Vehicle is selected to delete from database.', 'ecalypse-rental');?>);
											return false
										}
										;
										return confirm('<?= __('This action cannot be reversed, are you sure?', 'ecalypse-rental') ?>');">
									<div class="form-group">
										<input type="hidden" name="batch_processing_values" value="">
										<?php wp_nonce_field( 'batch_delete_db_fleet'); ?>
										<button name="batch_delete_db_fleet" class="btn btn-danger"><?php _e('Delete', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Vehicles from database', 'ecalypse-rental');?></button>
									</div>
								</form>
							<?php } else { ?>
								<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') {
											alert(<?php __('No Vehicle is selected to delete.', 'ecalypse-rental');?>);
											return false
										}
										;
										return confirm('<?= __('Do you really want to delete selected Vehicles?', 'ecalypse-rental') ?>');">
									<div class="form-group">
										<input type="hidden" name="batch_processing_values" value="">
										<?php wp_nonce_field( 'batch_delete_fleet'); ?>
										<button name="batch_delete_fleet" class="btn btn-danger"><?php _e('Delete', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Vehicles', 'ecalypse-rental');?></button>
									</div>
								</form>
								<?php } ?>

						<?php } else { ?>
							<div class="alert alert-info">
								<span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;
	<?= esc_html__('You do not have any Vehicles created yet, please create one clicking on "Add New Vehicle".', 'ecalypse-rental'); ?>
							</div>
<?php } ?>

					</div>
				</div>



			</div>
		</div>
	</div>

</div>
<script language="JavaScript">
	var gk_media_init = function(button_selector) {
		jQuery(button_selector).click(function(event) {
			event.preventDefault();

			// check for media manager instance
			if (wp.media.frames.gk_frame) {
				wp.media.frames.gk_frame.open();
				return;
			}
			// configuration of the media manager new instance
			wp.media.frames.gk_frame = wp.media({
				title: 'Select image',
				multiple: true,
				library: {
					type: 'image'
				},
				button: {
					text: 'Use selected image'
				}
			});

			// Function used for the image selection and media manager closing
			var gk_media_set_image = function() {
				var selection = wp.media.frames.gk_frame.state().get('selection');

				// no selection
				if (!selection) {
					return;
				}
				//console.log(selection);
				// iterate through selected elements
				selection.each(function(attachment) {
					var url = attachment.attributes.url;
					// add to additional images
					jQuery('#additional-pictures-ul').append('<li><input type="hidden" name="additional-pictures[]" value="' + url + '" class="media-input" /><img src="' + url + '" /><div class="buttons"><a href="#" class="btn btn-danger btn-block delete-button">X</a></div></li>');
				});
			};

			// closing event for media manger
			//wp.media.frames.gk_frame.on('close', gk_media_set_image);
			// image selection event
			wp.media.frames.gk_frame.on('select', gk_media_set_image);
			// showing media manager
			wp.media.frames.gk_frame.open();
		});

	};

	gk_media_init('.media-button');

	jQuery(document).ready(function($) {
	
		$(document).on('click', '.ecalypse_rental_remove_similar_car', function (e) {
			e.preventDefault();
			$(this).parent().remove();
		});
		
		$('#ecalypse_rental_add_similar_car').click(function(e){
			e.preventDefault();
			if ($('#ecalypse-rental-similar-car-select').val() == '') {
				return;
			}
			$('#ecalypse_rental_similar_cars_div p').remove();
			$('#ecalypse_rental_similar_cars_div').append('<span style="margin-right:5px;margin-bottom:5px;" class="ecalypse_rental_similar_car btn btn-warning">'+$('#ecalypse-rental-similar-car-select option:selected').text()+' <a href="#" class="ecalypse_rental_remove_similar_car">X</a><input type="hidden" name="similar_cars[]" value="'+$('#ecalypse-rental-similar-car-select').val()+'"></span>');
			
		});
	
		jQuery("#additional-pictures-ul").sortable({
			handle: 'img',
			cursor: 'move'
		});
		jQuery("#additional-pictures-ul").disableSelection();

		jQuery(document).on('mouseover', "#additional-pictures-ul li", function() {
			jQuery(this).children('.buttons').show();
		});

		jQuery(document).on('mouseout', "#additional-pictures-ul li", function() {
			jQuery(this).children('.buttons').hide();
		});

		jQuery(document).on('click', "#additional-pictures-ul li .delete-button", function(event) {
			event.preventDefault();
			jQuery(this).parent().parent().remove();
		});

		var fixHelper = function(e, ui) {
			ui.children().each(function() {
				jQuery(this).width(jQuery(this).width());
			});
			return ui;
		};

		jQuery('a.move_button').click(function() {
			return false;
		});

		jQuery("table#ecalypse-rental-fleet tbody").sortable({
			helper: fixHelper,
			cursor: 'move',
			handle: 'td.sortableTD',
			update: function(event, ui) {
				var newOrdering = jQuery(this).sortable('toArray', {attribute: 'fleetId'})
				jQuery.ajax({
					url: ajaxurl,
					global: false,
					type: "POST",
					data: ({
						action: 'ecalypse_rental_save_fleet_order',
						ordering: newOrdering,
						 _wpnonce: '<?php echo wp_create_nonce('ecalypse_rental_save_fleet_order');?>'
					}),
					dataType: "script",
					async: true
				});
			}
		}).disableSelection();
	});
</script>
