<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<?php $currency = get_option('ecalypse_rental_global_currency'); ?>
<?php $use_detail_page = get_option('ecalypse_rental_detail_page') == 'yes' ? true : false; ?>
<?php
$anylocation = get_option('ecalypse_rental_any_location_search');
if ($anylocation && $anylocation == 'yes') {
	$anylocation = true;
} else {
	$anylocation = false;
}
$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
$call_price = get_option('ecalypse_rental_call_for_price');
if ($call_price && $call_price == 'yes') {
	$call_price = true;
} else {
	$call_price = false;
}
?>
<?php $theme_options = unserialize(get_option('ecalypse_rental_theme_options')); ?>
<div class="ecalypse-rental-custom-theme">
<div class="columns-2 break-md aside-on-left">
	<div class="column column-fixed">
		<div class="box box-clean">
			
			<div class="box-title mobile-toggle mobile-toggle-md" data-target="modify-search">
				<?= EcalypseRental::t('Modify search') ?>
			</div>
			<!-- .box-title -->

			<div data-id="modify-search" class="box-inner-small box-border-bottom md-hidden">			
				
				<form action="" method="get" class="form form-vertical form-size-100" id="ecalypse_rental_booking_form">
		
					<fieldset>

						<div class="control-group">
							<div class="control-field">
								<select name="el" id="ecalypse_rental_enter_location" class="size-90">
									<option value=""><?= EcalypseRental::t('Enter Location') ?></option>
									<?php if (isset($locations) && !empty($locations)) { ?>
										<?php $locations_no = count($locations); ?>
										<?php foreach ($locations as $key => $val) { ?>
											<option value="<?= $val->id_branch ?>" <?php if ((isset($_GET['el']) && (int) $_GET['el'] == $val->id_branch) || $locations_no == 1 || (!isset($_GET['el']) && $val->is_default == 1)) { ?>selected<?php } ?>><?= $val->name ?></option>
										<?php } ?>	
									<?php } ?>
								</select>
							</div>
						</div>						
						<?php if (!isset($theme_options['display_return_location']) || (isset($theme_options) && isset($theme_options['display_return_location']) && $theme_options['display_return_location'] == 1)) { ?>
							<div class="control-group">
								<div class="control-field">
									<label><input name="dl" id="ecalypse_rental_different_loc" type="checkbox" <?php if (isset($_GET['dl']) && $_GET['dl'] == 'on') { ?>checked<?php } ?>>&nbsp;&nbsp;<?= EcalypseRental::t('Returning to Different location') ?></label>
								</div>
							</div>
							
							<div class="control-group">
								<div class="control-field">
									<select name="rl" id="ecalypse_rental_return_location" class="size-90">
										<option value=""><?= EcalypseRental::t('Return Location') ?></option>
										<?php if (isset($locations) && !empty($locations)) { ?>
											<?php $locations_no = count($locations); ?>
											<?php foreach ($locations as $key => $val) { ?>
												<option value="<?= $val->id_branch ?>" <?php if ((isset($_GET['rl']) && (int) $_GET['rl'] == $val->id_branch) || $locations_no == 1) { ?>selected<?php } ?>><?= $val->name ?></option>
											<?php } ?>	
										<?php } ?>
									</select>
								</div>
							</div>
						<?php } else { ?>
							<input type="hidden" name="rl" value="" id="ecalypse_rental_return_location<?php echo $ecalypse_rental_booking_form_id;?>">
						<?php } ?>
						
						<?php $disable_time = get_option('ecalypse_rental_disable_time'); ?>
						<?php if ($disable_time == 'yes') {$disable_time = true;} else {$disable_time = false;} ?>
						<div class="columns<?php echo $disable_time || $time_pricing_type == 'half_day' ? ' only-date' : '-2';?> control-group">
							<div class="column column-wide" <?php if (!($disable_time || $time_pricing_type == 'half_day')) { ?>style="width:60.5%"<?php } ?>>
								<div class="control-group">
									<div class="control-field">
										<span class="control-addon">
											<input type="text" class="control-input" name="fd" readonly="readonly" id="ecalypse_rental_from_date" placeholder="<?= EcalypseRental::t('Pick-up date') ?>" <?php if (isset($_GET['fd'])) { ?>value="<?= htmlspecialchars($_GET['fd']) ?>"<?php } ?>>
											<span class="control-addon-item">
												<span class="sprite-calendar"></span>
											</span>
										</span>
									</div>
								</div>
							</div>
							<?php if ($time_pricing_type != 'half_day') { ?>
								<?php if (!$disable_time) { ?>
								<?php $default_enter_time = get_option('default_enter_time', ''); ?>
								<div class="column column-thin" style="width:39.5%">
									<div class="control-group">
										<div class="control-field">
											<span class="control-addon">
												<select name="fh" id="ecalypse_rental_from_hour" style="width: 85%; padding:2px 9px; -webkit-border-radius: 4px; border-radius: 4px; font-size: 12px; ">
													<option value=""><?= EcalypseRental::t('Time') ?></option>
													<?php for ($x = 0; $x <= 23; $x++) { ?>
														<option value="<?= str_pad($x, 2, '0', STR_PAD_LEFT) ?>:00" <?php if ((isset($_GET['fh']) && $_GET['fh'] == str_pad($x, 2, '0', STR_PAD_LEFT) . ':00') || ($default_enter_time == str_pad($x, 2, '0', STR_PAD_LEFT).':00')) { ?>selected<?php } ?>><?= EcalypseRental::ecalypse_rental_time_format(str_pad($x, 2, '0', STR_PAD_LEFT).':00',(isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24));?></option>
														<option value="<?= str_pad($x, 2, '0', STR_PAD_LEFT) ?>:30" <?php if ((isset($_GET['fh']) && $_GET['fh'] == str_pad($x, 2, '0', STR_PAD_LEFT) . ':30') || ($default_enter_time == str_pad($x, 2, '0', STR_PAD_LEFT).':30')) { ?>selected<?php } ?>><?= EcalypseRental::ecalypse_rental_time_format(str_pad($x, 2, '0', STR_PAD_LEFT).':30',(isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24));?></option>
													<?php } ?>
												</select>

												<span class="control-addon-item display-none" style="right:-8px;">
													<span class="sprite-time"></span>
												</span>
											</span>	

										</div>
									</div>
								</div>
								<?php } else { ?>
								<input type="hidden" name="fh" id="ecalypse_rental_from_hour" value="00:00">
								<?php } ?>
							<?php } ?>
						</div>
						<!-- .columns-2 -->
						
						<?php if ($time_pricing_type == 'half_day') { ?>
						<div class="columns control-group">
							<div class="control-group">
								<div class="control-field">
									<span class="control-addon">
										<select name="p" id="ecalypse_rental_period<?php echo $ecalypse_rental_booking_form_id;?>" style="width: 85%; padding:2px 9px; -webkit-border-radius: 4px; border-radius: 4px; font-size: 12px; ">
											<option value="am" <?php echo isset($_GET['p']) && $_GET['p'] == 'am' ? 'selected' : '';?>><?= EcalypseRental::t('AM') ?></option>
											<option value="pm" <?php echo isset($_GET['p']) && $_GET['p'] == 'pm' ? 'selected' : '';?>><?= EcalypseRental::t('PM') ?></option>
											<option value="day" <?php echo (isset($_GET['p']) && $_GET['p'] == 'day') || !isset($_GET['p']) ? 'selected' : '';?>><?= EcalypseRental::t('Full day') ?></option>
										</select>
									</span>	
								</div>
							</div>
						</div>
						<?php } ?>
	
						<div class="columns<?php echo $disable_time || $time_pricing_type == 'half_day' ? ' only-date' : '-2';?> control-group">
							<div class="column column-wide" <?php if (!($disable_time || $time_pricing_type == 'half_day')) { ?>style="width:60.5%"<?php } ?>>
								<div class="control-group">
									<div class="control-field">
										<span class="control-addon" <?php echo (isset($_GET['p']) && $_GET['p'] == 'day') || !isset($_GET['p']) ? '' : ' style="display:none;"';?>>
											<input type="text" class="control-input" name="td" readonly="readonly" id="ecalypse_rental_to_date" placeholder="<?= EcalypseRental::t('Return date') ?>" <?php if (isset($_GET['td'])) { ?>value="<?= htmlspecialchars($_GET['td']) ?>"<?php } ?>>
											<span class="control-addon-item">
												<span class="sprite-calendar"></span>
											</span>
										</span>
									</div>
								</div>
							</div>
							<?php if ($time_pricing_type != 'half_day') { ?>
								<?php if (!$disable_time) { ?>
								<?php $default_return_time = get_option('default_return_time', ''); ?>
								<div class="column column-thin" style="width:39.5%">
									<div class="control-group">
										<div class="control-field">
											<span class="control-addon">
												<select name="th" id="ecalypse_rental_to_hour" style="width: 85%; padding:2px 9px; -webkit-border-radius: 4px; border-radius: 4px; font-size: 12px; ">
													<option value=""><?= EcalypseRental::t('Time') ?></option>
													<?php for ($x = 0; $x <= 23; $x++) { ?>
														<option value="<?= str_pad($x, 2, '0', STR_PAD_LEFT) ?>:00" <?php if ((isset($_GET['th']) && $_GET['th'] == str_pad($x, 2, '0', STR_PAD_LEFT) . ':00') || ($default_return_time == str_pad($x, 2, '0', STR_PAD_LEFT).':00')) { ?>selected<?php } ?>><?= EcalypseRental::ecalypse_rental_time_format(str_pad($x, 2, '0', STR_PAD_LEFT).':00',(isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24));?></option>
														<option value="<?= str_pad($x, 2, '0', STR_PAD_LEFT) ?>:30" <?php if ((isset($_GET['th']) && $_GET['th'] == str_pad($x, 2, '0', STR_PAD_LEFT) . ':30') || ($default_return_time == str_pad($x, 2, '0', STR_PAD_LEFT).':30')) { ?>selected<?php } ?>><?= EcalypseRental::ecalypse_rental_time_format(str_pad($x, 2, '0', STR_PAD_LEFT).':30',(isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24));?></option>
													<?php } ?>
												</select>
												<span class="control-addon-item display-none" style="right:-8px;">
													<span class="sprite-time"></span>
												</span>
											</span>	

										</div>
									</div>
								</div>
								<?php } else { ?>
								<input type="hidden" name="th" id="ecalypse_rental_to_hour" value="00:00">
								<?php } ?>
							<?php } ?>
						</div>
	
						<div class="control-group">
							<div class="control-field">
								<input type="hidden" name="page" value="ecalypse-rental">
								<input type="hidden" name="order" value="<?php echo ((isset($_GET['order']) && $_GET['order'] == 'price') || (!isset($_GET['order']) && isset($theme_options['default_sort_by']) && $theme_options['default_sort_by'] == 'price')) ? 'price' : 'name';?>" id="ecalypse_rental_order_input">
								<input type="hidden" name="book_now" value="ok">
								<input type="hidden" name="flt" value="">
								<input type="submit" name="search" value="<?= EcalypseRental::t('SEARCH') ?>" id="ecalypse_rental_book_now" class="btn btn-primary btn-block">
							</div>
							<!-- .control-field -->
						</div>
						<!-- .control-group -->

					</fieldset>
					
					<ul id="ecalypse_rental_book_errors" style="margin:1em 2em;list-style-type:circle;color:tomato;"></ul>
				</form>
			</div>
			
			<?php if ((isset($theme_options['filter_price_range']) && $theme_options['filter_price_range'] == 1) ||
								(isset($theme_options['filter_extras']) && $theme_options['filter_extras'] == 1) ||
								(isset($theme_options['filter_fuel']) && $theme_options['filter_fuel'] == 1) ||
								(isset($theme_options['filter_passangers']) && $theme_options['filter_passangers'] == 1) ||
								(isset($theme_options['filter_vehicle_categories']) && $theme_options['filter_vehicle_categories'] == 1) ||
								(isset($theme_options['filter_vehicle_names']) && $theme_options['filter_vehicle_names'] == 1)) { ?>
			
			<div class="box-title mobile-toggle mobile-toggle-md" data-target="filter-results">
				<?= EcalypseRental::t('Filter Results') ?>
			</div>
			<!-- .box-title -->

			<div data-id="filter-results" class="box-inner-small box-border-bottom md-hidden">
				
				<?php
					$flt = array();
					$custom_params = array();
					if (isset($_GET['flt']) && !empty($_GET['flt'])) {
						foreach (explode('|', $_GET['flt']) as $kD => $vD) {
							list($key, $val) = explode(':', $vD);
							$flt[$key] = $val;
							if (substr($key, 0, 2) == 'cp') {
								$key = substr($key, 3);
								if (strpos($key, '-') !== false) {
									$key = substr($key,0,strpos($key, '-'));
									$values = explode('-', $val);
									$custom_params[(int)$key] = array('from' => $values[0], 'to' => $values[1]);
								} else {
									$values = explode(',', $val);
									if (!isset($custom_params[(int)$key])) {
										$custom_params[(int)$key] = array();
									}
									foreach ($values as $value) {
										if ((int)$value > 0) {
											$custom_params[(int)$key][] = (int)$value;
										}
									}
								}
							}
						}
					}					
				?>
				
				<form action="" method="post" class="form form-vertical">
					<fieldset>
						
						<?php if (isset($theme_options['filter_price_range']) && $theme_options['filter_price_range'] == 1) { ?>
							<div class="control-group">
								<div class="control-label">
									<label id="ecalypse_rental_filter_price_range_label"><?= EcalypseRental::t('Price range') ?></label>
								</div>
								<!-- .control-label -->
								<?php $showvat = get_option('ecalypse_rental_show_vat'); ?>
								<?php 
									if ($showvat && $showvat == 'yes') {
										
									}
								?>
								<div class="control-field inputSliderWrapper" id="ecalypse_rental_filter_price_range">
									<div class="full-width-oddo">
										<div class="half-oddo">
											<label for="from"><?= EcalypseRental::t('From') ?>: (<?= (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) ? EcalypseRentalSession::$session['ecalypse_rental_currency'] : $currency) ?>)</label>
											<input type="number" class="oddo inputSliderMin" min="0">	
										</div>

										<div class="half-oddo float-right-oddo">
											<label for="to"><?= EcalypseRental::t('To') ?>: (<?= (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) ? EcalypseRentalSession::$session['ecalypse_rental_currency'] : $currency) ?>)</label>
											<input type="number" class="oddo inputSliderMax" min="0">	
										</div>
									</div>
									<?php $priceSliderMin = isset($theme_options['filter_price_range_min']) && (int)$theme_options['filter_price_range_min'] >= 0 ? (int)$theme_options['filter_price_range_min'] : 0;?>
									<?php $priceSliderMax = isset($theme_options['filter_price_range_max']) && (int)$theme_options['filter_price_range_max'] >= 0 ? (int)$theme_options['filter_price_range_max'] : 500;?>
									<?php if (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) && EcalypseRentalSession::$session['ecalypse_rental_currency'] != $currency) {
											$av_currencies = unserialize(get_option('ecalypse_rental_available_currencies'));
											if (isset($av_currencies[EcalypseRentalSession::$session['ecalypse_rental_currency']])) {
												$rate = $av_currencies[EcalypseRentalSession::$session['ecalypse_rental_currency']];
												$priceSliderMin = floor($priceSliderMin/$rate);
												$priceSliderMax = ceil($priceSliderMax/$rate);
											}
									} ?>
									<div class="inputSlider slider" data-inputmin=".inputSliderMin" data-inputmax=".inputSliderMax" data-value="[<?= (isset($flt['spr']) ? (int) $flt['spr'] : $priceSliderMin) ?>,<?= (isset($flt['epr']) ? (int) $flt['epr'] : $priceSliderMax) ?>]" data-unit="<?= (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) ? EcalypseRentalSession::$session['ecalypse_rental_currency'] : 'USD') ?>" data-min="<?php echo $priceSliderMin;?>" data-max="<?php echo $priceSliderMax;?>" data-step="10">
										<div class="slider-init"></div>
									</div>
									<!-- .slider -->
		
								</div>
								<!-- .control-field -->
							</div>
						<?php } ?>
						
						<?php if (isset($theme_options['filter_extras']) && $theme_options['filter_extras'] == 1) { ?>
							<div class="control-group">
								<div class="control-label">
									<label id="ecalypse_rental_filter_extras_label"><?= EcalypseRental::t('Extras') ?></label>
								</div>
								<!-- .control-label -->
								<div class="control-field" id="ecalypse_rental_filter_extras">
									<label class="custom-inline"><input type="checkbox" name="nonac" value="1" <?php if (isset($flt['nac']) && $flt['nac'] == 1) { ?>checked<?php } ?>> <?= EcalypseRental::t('Non AC') ?></label>
									<label class="custom-inline"><input type="checkbox" name="ac" value="1" <?php if (isset($flt['ac']) && $flt['ac'] == 1) { ?>checked<?php } ?>> <?= EcalypseRental::t('AC') ?></label>
								</div>
								<!-- .control-field -->
							</div>
						<?php } ?>
						
						<?php if (isset($theme_options['filter_fuel']) && $theme_options['filter_fuel'] == 1) { ?>
							<div class="control-group">
								<div class="control-label">
									<label id="ecalypse_rental_filter_fuel_label"><?= EcalypseRental::t('Fuel') ?></label>
								</div>
								<!-- .control-label -->
								<div class="control-field" id="ecalypse_rental_filter_fuel">
									<label class="custom-inline"><input type="checkbox" name="petrol" value="1" <?php if (isset($flt['pl']) && $flt['pl'] == 1) { ?>checked<?php } ?>> <?= EcalypseRental::t('Petrol') ?></label>
									<label class="custom-inline"><input type="checkbox" name="diesel" value="1" <?php if (isset($flt['dl']) && $flt['dl'] == 1) { ?>checked<?php } ?>> <?= EcalypseRental::t('Diesel') ?></label>
								</div>
								<!-- .control-field -->
							</div>
						<?php } ?>
						
						<?php if (isset($theme_options['filter_passengers']) && $theme_options['filter_passengers'] == 1) { ?>
							<?php $passengersSliderMin = isset($theme_options['filter_passengers_range_min']) && (int)$theme_options['filter_passengers_range_min'] >= 0 ? (int)$theme_options['filter_passengers_range_min'] : 1;?>
							<?php $passengersSliderMax = isset($theme_options['filter_passengers_range_max']) && (int)$theme_options['filter_passengers_range_max'] >= 0 ? (int)$theme_options['filter_passengers_range_max'] : 8;?>
							<div class="control-group">
								<div class="control-label">
									<label id="ecalypse_rental_filter_passangers_label"><?= EcalypseRental::t('Number of passangers') ?></label>
								</div>

								<!-- .control-label -->
								<div class="control-field" id="ecalypse_rental_filter_passangers">								
									<div class="slider" data-value="[<?= (isset($flt['sp']) ? (int) $flt['sp'] : $passengersSliderMin) ?>,<?= (isset($flt['ep']) ? (int) $flt['ep'] : $passengersSliderMax) ?>]" data-min="<?php echo $passengersSliderMin;?>" data-max="<?php echo $passengersSliderMax;?>" data-step="1">
										<div class="slider-init"></div>
										<!-- .slider-init -->
										<input type="hidden" class="slider-input-start">
										<input type="hidden" class="slider-input-end">
									</div>
									<!-- .slider -->
		
								</div>
								<!-- .control-field -->
							</div>
						<?php } ?>
						
						<?php if (isset($theme_options['filter_vehicle_categories']) && $theme_options['filter_vehicle_categories'] == 1) { ?>
							<div class="control-group">
								<div class="control-label">
									<label id="ecalypse_rental_filter_categories_label"><?= EcalypseRental::t('Categories') ?></label>
								</div>
								
								<div class="control-field" id="ecalypse_rental_filter_categories">
									<label class="custom-block"><input type="checkbox" id="categories_select_all"> <?= EcalypseRental::t('Select All') ?></label>
									<?php $cats = (isset($flt['cats']) ? explode(',', $flt['cats']) : array()); ?>
									<?php if (isset($vehicle_cats) && !empty($vehicle_cats)) { ?>
										<?php foreach ($vehicle_cats as $key => $val) { ?>
											<label class="custom-block"><input type="checkbox" class="categories_checkall" name="categories[]" value="<?= $val->id_category ?>" <?php if (in_array($val->id_category, $cats)) { ?>checked<?php } ?>> <?= $val->name ?></label>		
										<?php } ?>
									<?php } ?>
								</div>
								
							</div>
						<?php } ?>
						
						<?php if (isset($theme_options['filter_vehicle_names']) && $theme_options['filter_vehicle_names'] == 1) { ?>
							<div class="control-group">
								<div class="control-label">
									<label id="ecalypse_rental_filter_vehicles_label"><?= EcalypseRental::t('Vehicles') ?></label>
								</div>
								<div class="control-field" id="ecalypse_rental_filter_vehicles">
									<label class="custom-block"><input type="checkbox" id="vehicles_select_all"> <?= EcalypseRental::t('Select All') ?></label>
									<?php $vh = (isset($flt['vh']) ? explode(',', $flt['vh']) : array()); ?>
									<?php if (isset($vehicle_names) && !empty($vehicle_names)) { ?>
										<?php foreach ($vehicle_names as $key => $val) { ?>
											<label class="custom-block"><input type="checkbox" class="vehicles_checkall" name="names[]" value="<?= $val->name ?>" <?php if (in_array($val->name, $vh)) { ?>checked<?php } ?>> <?= $val->name ?></label>		
										<?php } ?>
									<?php } ?>
								</div>
							</div>
						<?php } ?>
						<!-- Custom fleet parameters -->
						<?php $lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB'); ?>
						<?php $lang = strtolower(end(explode('_', $lang))); ?>
						<?php if (isset($fleet_parameters) && is_array($fleet_parameters)) { ?>
							<?php foreach ($fleet_parameters as $parameter) { 
								$parameter_names = unserialize($parameter->name);
								$parameter_values = unserialize($parameter->values);
								?>
								<div class="control-group">
									<div class="control-label">
										<label class="ecalypse_rental_filter_custom_label"><?php echo $parameter_names[$lang] ?></label>
									</div>
									<div class="control-field custom-parameter<?php echo $parameter->type == 2 ? '-values': '-range';?>">
										<?php $vh = (isset($flt['vh']) ? explode(',', $flt['vh']) : array()); ?>
										<?php if ($parameter->type == 2) { ?>
											<?php if (isset($parameter_values[$lang]) && !empty($parameter_values[$lang])) { ?>
												<?php foreach ($parameter_values[$lang] as $key => $val) { ?>
													<?php if ($val == '') { continue; }?>
													<label class="custom-block"><input data-parameter="<?php echo $parameter->id_fleet_parameter;?>" type="checkbox" class="custom_parameter_checkbox custom_parameter_input" name="custom-parameter[<?php echo $parameter->id_fleet_parameter;?>][]" value="<?= $key; ?>" <?php if (isset($custom_params[$parameter->id_fleet_parameter]) && in_array($key, $custom_params[$parameter->id_fleet_parameter])) { ?>checked="checked"<?php } ?>> <?= $val; ?></label>		
												<?php } ?>
											<?php } ?>
										<?php } else { ?>
											<div class="slider" data-value="[<?= (isset($custom_params[$parameter->id_fleet_parameter]) && isset($custom_params[$parameter->id_fleet_parameter]['from']) ? (int) $custom_params[$parameter->id_fleet_parameter]['from'] : $parameter->range_from) ?>,<?= (isset($custom_params[$parameter->id_fleet_parameter]) && isset($custom_params[$parameter->id_fleet_parameter]['to']) ? (int) $custom_params[$parameter->id_fleet_parameter]['to'] : $parameter->range_to) ?>]" data-min="<?php echo (int)$parameter->range_from;?>" data-max="<?php echo (int)$parameter->range_to;?>" data-step="1">
												<div class="slider-init"></div>
												<!-- .slider-init -->
												<input type="hidden" data-parameter="<?php echo $parameter->id_fleet_parameter;?>" name="custom-parameter[<?php echo $parameter->id_fleet_parameter;?>][range_from]" class="slider-input-start custom_parameter_input">
												<input type="hidden" data-parameter="<?php echo $parameter->id_fleet_parameter;?>" name="custom-parameter[<?php echo $parameter->id_fleet_parameter;?>][range_to]" class="slider-input-end custom_parameter_input">
											</div>
										<?php } ?>
									</div>
								</div>
							<?php } ?>
						<?php } ?>
						
						<br>
						<input type="button" value="<?= EcalypseRental::t('Modify search') ?>" class="btn btn-primary btn-block modify_search">
						
					</fieldset>
				</form>
				
				<script type="text/javascript">
					
					jQuery(document).ready(function() {
						
						jQuery('.modify_search').on('click', function() {
							jQuery('#ecalypse_rental_book_now').click();
						});
						
						jQuery('#categories_select_all').click(function() {
							var checked = !jQuery(this).data('checked');
							jQuery('.categories_checkall').prop('checked', checked);
					    jQuery(this).data('checked', checked);
						});
						
						jQuery('#vehicles_select_all').click(function() {
							var checked = !jQuery(this).data('checked');
							jQuery('.vehicles_checkall').prop('checked', checked);
					    jQuery(this).data('checked', checked);
						});
						
						jQuery('#ecalypse_rental_filter_price_range_label').click(function() {
							jQuery('#ecalypse_rental_filter_price_range').toggle('fast');
						});
						
						jQuery('#ecalypse_rental_filter_extras_label').click(function() {
							jQuery('#ecalypse_rental_filter_extras').toggle('fast');
						});
						
						jQuery('#ecalypse_rental_filter_fuel_label').click(function() {
							jQuery('#ecalypse_rental_filter_fuel').toggle('fast');
						});
						
						jQuery('#ecalypse_rental_filter_passangers_label').click(function() {
							jQuery('#ecalypse_rental_filter_passangers').toggle('fast');
						});
						
						jQuery('#ecalypse_rental_filter_categories_label').click(function() {
							jQuery('#ecalypse_rental_filter_categories').toggle('fast');
						});
						
						jQuery('#ecalypse_rental_filter_vehicles_label').click(function() {
							jQuery('#ecalypse_rental_filter_vehicles').toggle('fast');
						});
						
						jQuery('.ecalypse_rental_filter_custom_label').click(function() {
							jQuery(this).parent().next().toggle('fast');
						});
						
						jQuery('#ecalypse_rental_set_order').on('click', function() {
							var value = jQuery(this).attr('rel').toLowerCase(); 
							jQuery('#ecalypse_rental_order_input').val(value);
							jQuery('#ecalypse_rental_booking_form').submit();
						});
						
					});
					
				
				</script>
				
			</div>
			<!-- .box-inner-small -->
			<?php } ?>
			
			<?php if (isset($theme_options['phone_number']) && !empty($theme_options['phone_number'])) { ?>
				<div class="box-inner-small">
					<div class="invert-columns-2 init-md">
						<div class="column">
							<div class="box box-inner-small box-contact box-contact-small">
								<div class="h2" style="text-align:center;margin-bottom:15px;">
									<?= EcalypseRental::t('Make a reservation by phone') ?><br>
								</div>
								<div class="h2" style="font-size: 1.75em;margin:0;">
									<strong><?= $theme_options['phone_number'] ?></strong>
								</div>
								<span class="sprite-call-us-small"></span>
							</div>
						</div>
					</div>
				</div>
			<?php } ?>
			
		</div>
		<!-- .box -->
	
	</div>
	<!-- .column -->
	
	<div class="column column-fluid">
				
		<div class="bordered-content">

			<div class="bordered-content-title">
				<div class="results">
					<?= $vehicles['count'] ?> <?php if ($vehicles['count'] == 1) { ?><?= EcalypseRental::t('result') ?><?php } else { ?><?= EcalypseRental::t('results') ?><?php } ?>
				</div>
				<!-- .results -->
				
				
				<div class="sort">
					<span class="label"><?= EcalypseRental::t('Sort by') ?>:</span>
					<?php if ((isset($_GET['order']) && $_GET['order'] == 'price') || (!isset($_GET['order']) && isset($theme_options['default_sort_by']) && $theme_options['default_sort_by'] == 'price')) { ?>
						<?= EcalypseRental::t('Price') ?> <span>|</span> <a href="<?= EcalypseRental::sort_link('name') ?>" rel="name"><?= EcalypseRental::t('Name') ?></a>
					<?php } else { ?>
						<a href="<?= EcalypseRental::sort_link('price') ?>" rel="price"><?= EcalypseRental::t('Price') ?></a> <span>|</span> <?= EcalypseRental::t('Name') ?>
					<?php } ?>
				</div>
				<!-- .sort -->
				
			</div>
			<!-- .bordered-content-title -->
			
			<?php $consumption = get_option('ecalypse_rental_consumption'); ?>
			<?php if (!$consumption || empty($consumption)) { $consumption = 'eu'; } ?>
			<?php $distance_metric = get_option('ecalypse_rental_distance_metric'); ?>
			<?php $currency = get_option('ecalypse_rental_global_currency'); ?>
												
			<?php if (isset($vehicles['results']) && !empty($vehicles['results'])) { ?>
				<?php foreach ($vehicles['results'] as $key => $val) { ?>
				
					<div class="list-item list-item-car box box-white box-inner">
		
						<div class="list-item-media">
							<div class="pic-area">
								<?php $additional_pictures_count = 0; ?>
								<?php if (isset($val->additional_pictures) && !empty($val->additional_pictures)) { ?>
									<?php $val->additional_pictures = unserialize($val->additional_pictures); ?>
									<?php if (is_array($val->additional_pictures) && count($val->additional_pictures) > 0) { ?>
										<?php $additional_pictures_count = count($val->additional_pictures); ?>
									<?php } ?>
								<?php } ?>
								<p>
									<?php if ($use_detail_page) { ?>
										<a href="<?php echo EcalypseRental::get_fleet_url($val->id_fleet, $val->name);?>">
									<?php } else { ?>
										<a href="<?= $val->picture ?>" data-lightbox="fleet-<?= $val->id_fleet ?>">
									<?php } ?>
										<img src="<?= $val->picture ?>" alt="<?= $val->name ?>">
										<?php if ($additional_pictures_count > 0) { ?>
											<span class="btn btn-small btn-primary btn-book btn-absolute"><?= EcalypseRental::t('Show more pictures') ?> <strong>(<?php echo $additional_pictures_count;?>)</strong></span>
										<?php } ?>
									</a>
								</p>
								<div class="hid-imgs">
									<?php if ($additional_pictures_count > 0) { ?>
										<?php foreach ($val->additional_pictures as $adPicture) { ?>
											<a href="<?= $adPicture ?>" data-lightbox="fleet-<?= $val->id_fleet ?>"></a>
										<?php } ?>
									<?php } ?>
							</div>
							</div>
							<p class="car-name"><?php if ($use_detail_page) { ?><a href="<?php echo EcalypseRental::get_fleet_url($val->id_fleet, $val->name);?>"><?= $val->name ?></a><?php } else { ?><?= $val->name ?><?php } ?></p>
						</div>
		
						<div class="list-item-content">
							<div class="columns-2 break-lg columns-equal-height">
								<div class="column">
									
									<?php if (isset($val->ac) && (int) $val->ac > 0) { ?>
										<div class="icon-text"><span class="sprite-snowflake"></span><?= ($val->ac == 2) ? EcalypseRental::t('No A/C') : EcalypseRental::t('A/C'); ?></div>
									<?php } ?>
									<?php if (isset($val->luggage) && !empty($val->luggage)) { ?>
										<div class="icon-text"><span class="sprite-briefcase"></span><?= $val->luggage ?>&times; <?= EcalypseRental::t('Luggage Quantity') ?></div>
									<?php } ?>
									<?php if (isset($val->seats) && !empty($val->seats)) { ?>
										<div class="icon-text"><span class="sprite-person"></span><?= $val->seats ?>&times; <?= EcalypseRental::t('Persons') ?></div>
									<?php } ?>
									<?php if (isset($val->fuel) && !empty($val->fuel)) { ?>
										<div class="icon-text"><span class="sprite-fuel"></span><?= (($val->fuel == 1) ? EcalypseRental::t('Petrol') : EcalypseRental::t('Diesel')) ?></div>
									<?php } ?>
									<?php if (isset($val->consumption) && !empty($val->consumption)) { ?>
										<div class="icon-text"><span class="sprite-timeout"></span><abbr title="<?= EcalypseRental::t('Average Consumption') ?>"><?= $val->consumption ?> <?= (($consumption == 'eu') ? 'l/100km' : 'MPG') ?></abbr></div>
									<?php } ?>
									<?php if (isset($val->description) && !empty($val->description)) { ?>
										<p class="ecalypse_rental_car_details">
											
											<?php if (isset($val->transmission) && !empty($val->transmission)) { ?>
												<?= (($val->transmission == 1) ? EcalypseRental::t('Transmission: Automatic') : EcalypseRental::t('Transmission: Manual')) ?><br />
											<?php } ?>
											<?php if (isset($val->free_distance)) { ?>
												<?= EcalypseRental::t('Free distance') ?>: <?php if (isset($val->free_distance_total)) { echo $val->free_distance_total == 0 ? EcalypseRental::t('Unlimited') : $val->free_distance_total.'&nbsp;'.$distance_metric; } elseif ($val->free_distance > 0) { ?><?= (isset($val->prices) ? (int)$val->prices['diff_days'] : 1) * $val->free_distance ?>&nbsp;<?= $distance_metric ?> <?= EcalypseRental::t('per day') ?><?php } else { ?><?= EcalypseRental::t('Unlimited') ?><?php } ?><br />
											<?php } ?>
											<?php if (isset($val->deposit) && $val->deposit != '' && $val->deposit > 0) { ?>
												<?= EcalypseRental::t('Deposit') ?>: <?= $val->deposit ?>&nbsp;<?= $currency ?><br />
											<?php } ?>
											<br />
											<?php $fleet_description = unserialize($val->description); ?>
											<?php if ($fleet_description == false) { $fleet_description['gb'] = $val->description; } ?>
											<?php $lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB'); ?>
											<?php $lang = end(explode('_', $lang)); ?>
											<?= (isset($fleet_description[strtolower($lang)]) ? EcalypseRental::removeslashes($fleet_description[strtolower($lang)]) : EcalypseRental::removeslashes($fleet_description['gb'])) ?>
											<?php $additional_parameters = unserialize($val->additional_parameters); ?>
											<?php $lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB'); ?>
											<?php $lang = strtolower(end(explode('_', $lang))); ?>
											<?php if ($additional_parameters && isset($additional_parameters[$lang]) && !empty($additional_parameters[$lang])) { ?>
												<?php foreach ($additional_parameters[$lang] as $p) { ?>
												<?php if (!isset($p['name']) || trim($p['name']) == '') { continue; }?>
												<strong><?php echo $p['name'];?>:</strong> <span><?php echo $p['value'];?></span><br />
												<?php } ?>
											<?php } ?>
										</p>
									<?php } ?>
								</div>
		
								<div class="column align-right">
									<p>
										<?php if (isset($val->prices) && !empty($val->prices)) { ?>
											<?php $showvat = get_option('ecalypse_rental_show_vat'); ?>
											<?php 
												if ((float) $val->prices['vat'] > 0 && $showvat && $showvat == 'yes') {
													$val->prices['price'] = $val->prices['price_with_tax'];
													$val->prices['total_rental'] = $val->prices['total_price_with_tax'];
												}
											?>
											<?php if (!isset($_GET['fd'])) { ?>
												<span class="price-from"><?php echo EcalypseRental::t('starting from');?></span>
											<?php } ?>
											<span class="price"><?= $val->prices['cc_before'] ?><?= number_format($val->prices['price'], 2, '.', ',') ?><?= $val->prices['cc_after'] ?> <?php if(!($time_pricing_type == 'half_day' && $val->prices['pr_type'] == 0)) { if ($val->prices['pr_type'] == 2) { ?><?= EcalypseRental::t('per hour') ?><?php } else { ?><?= EcalypseRental::t('per day') ?><?php }} ?></span><br>
											<span class="additional" <?php if ($val->prices['maxprice_reached'] == true) { ?>style="color:tomato;" title="<?= EcalypseRental::t('Maximum price for this vehicle was reached.') ?>"<?php } ?>><?= EcalypseRental::t('Total Rental') ?> <?= $val->prices['cc_before'] ?><?= number_format($val->prices['total_rental'], 2, '.', ',') ?><?= $val->prices['cc_after'] ?></span>
										<?php } else { ?>
											<span class="additional<?php echo $call_price ? ' ecalypse_call_price' : '';?>"><?= $call_price ? EcalypseRental::t('Call for price') : EcalypseRental::t('Not available') ?></span>
										<?php } ?>
									</p>
									<?php if (isset($_GET['page'])) { ?>
									<?php if ($use_detail_page) { ?>
										<a href="<?php echo EcalypseRental::get_fleet_url($val->id_fleet, $val->name);?>" class="btn btn-small btn-book" style="background-color:silver;"><?= EcalypseRental::t('Show details') ?></a>
									<?php } else { ?>
										<a href="javascript:void(0);" class="btn btn-small btn-book ecalypse_rental_car_details_link" style="background-color:silver;"><?= EcalypseRental::t('Show details') ?></a>
									<?php } ?>									
										<br><br>
										<a href="<?= $_SERVER['REQUEST_URI'] ?>&amp;id_car=<?= $val->id_fleet ?>" class="btn btn-small btn-primary btn-book"><?= EcalypseRental::t('Book This Car') ?></a>
									<?php } else { ?>
										<?php if ($use_detail_page) { ?>
											<a href="<?php echo EcalypseRental::get_fleet_url($val->id_fleet, $val->name);?>" class="btn btn-small btn-book" style="background-color:silver;"><?= EcalypseRental::t('Show details') ?></a>
										<?php } else { ?>
											<a href="javascript:void(0);" class="btn btn-small btn-book ecalypse_rental_car_details_link" style="background-color:silver;"><?= EcalypseRental::t('Show details') ?></a>
										<?php } ?>	
										
										<?php if ((isset($theme_options['car_available_button']) && $theme_options['car_available_button'] == 1)) { ?> 
											<br><br>
											<input type="text" data-car-id="<?= $val->id_fleet ?>" class="edited-cal control-input btn-primary special-btn car-available-button btn btn-small btn-primary btn-book zindex" name="fd" readonly="readonly" placeholder="<?= EcalypseRental::t('Show availability') ?>" readonly="readonly" />
											<div class="overflow-cal" style="display: none;"></div>
										<?php } ?>	
										<br><br>
										<a href="javascript:void(0);" class="btn btn-small btn-primary btn-book ecalypse-rental-book-this-car-btn<?php if ((isset($theme_options['car_available_button']) && $theme_options['car_available_button'] == 1)) { ?>  bookcar<?php } ?>" data-branch-id="<?= $anylocation ? 0 : $val->id_branch;?>" data-car-id="<?= $val->id_fleet ?>"><?= EcalypseRental::t('Book This Car') ?></a>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>
				
				<?php } ?>
			<?php } ?>
		</div>
		<!-- .bordered-content -->

	</div>
	<!-- .column -->

</div>
<!-- .columns-2 -->
</div>

<div class="ecalypse-rental-custom-theme">
	<div id="ecalypse-rental-hidden-booking-form">
		<p class="close-win">×</p>
		<h3><?= EcalypseRental::t('Book your car now') ?></h3>
		<?php $ecalypse_rental_booking_form_id = '_popup';?>
		<?php include(EcalypseRentalTheme::get_file_template_path('booking-form.php')); ?>
	</div>
	<div class="booking-form-overflow"></div>
</div>
<?php include ECALYPSERENTALSTARTER__PLUGIN_DIR . '/templates/booking-javascript.php';?>