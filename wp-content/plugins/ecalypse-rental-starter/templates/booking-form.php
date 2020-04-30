<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<?php if (!isset($ecalypse_rental_booking_form_id)) {
	$ecalypse_rental_booking_form_id = '';
}
$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
?>
<?php $theme_options = unserialize(get_option('ecalypse_rental_theme_options')); ?>
<div class="ecalypse-rental-custom-theme">
<form action="" method="get" class="form form-request form-vertical form-size-100" id="ecalypse_rental_booking_form<?php echo $ecalypse_rental_booking_form_id;?>">
											
	<fieldset>
		<?php if ($ecalypse_rental_booking_form_id == '_popup') { ?>
		<input type="hidden" name="id_car" value="" id="ecalypse_rental_booking_form_id_car" />
		<?php } ?>
		<div class="control-group">
			<div class="control-field">
				<select name="el" id="ecalypse_rental_enter_location<?php echo $ecalypse_rental_booking_form_id;?>" class="size-90">
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
				<label><input name="dl" id="ecalypse_rental_different_loc<?php echo $ecalypse_rental_booking_form_id;?>" type="checkbox" <?php if (isset($_GET['dl']) && $_GET['dl'] == 'on') { ?>checked<?php } ?>>&nbsp;&nbsp;<?= EcalypseRental::t('Returning to Different location') ?></label>
			</div>
		</div>
		
		<div class="control-group">
			<div class="control-field">
				<select name="rl" id="ecalypse_rental_return_location<?php echo $ecalypse_rental_booking_form_id;?>" class="size-90">
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
		
		<div class="control-group">
			<div class="control-field">
				<div class="columns-2 control-group">
					<div class="column">
						<?php $disable_time = get_option('ecalypse_rental_disable_time'); ?>
						<?php if ($disable_time == 'yes') {$disable_time = true;} else {$disable_time = false;} ?>
						<div class="columns<?php echo $disable_time ? ' only-date' : '-2';?>">
							<div class="column column-wider">
								<div class="control-group">
									<div class="control-field">
										<span class="control-addon">
											<input type="text" class="control-input" name="fd" readonly="readonly" id="ecalypse_rental_from_date<?php echo $ecalypse_rental_booking_form_id;?>" placeholder="<?= EcalypseRental::t('Pick-up date') ?>" <?php if (isset($_GET['fd'])) { ?>value="<?= htmlspecialchars($_GET['fd']) ?>"<?php } ?>>
											<span class="control-addon-item">
												<span class="sprite-calendar"></span>
											</span>
										</span>
									</div>
								</div>
							</div>
							<?php if ($time_pricing_type == 'half_day') { ?>
								<div class="column column-thiner">
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
							<?php } else { ?>
								<?php if (!$disable_time) { ?>
								<?php $default_enter_time = get_option('default_enter_time', ''); ?>
								<div class="column column-thiner">
									<div class="control-group">
										<div class="control-field">
											<span class="control-addon">
												<select name="fh" id="ecalypse_rental_from_hour<?php echo $ecalypse_rental_booking_form_id;?>" style="width: 85%; padding:2px 9px; -webkit-border-radius: 4px; border-radius: 4px; font-size: 12px; ">
													<option value=""><?= EcalypseRental::t('Time') ?></option>
													<?php for ($x = 0; $x <= 23; $x++) { ?>
														<option value="<?= EcalypseRental::ecalypse_rental_time_format($x.':00',24); ?>" <?php if ((isset($_GET['fh']) && $_GET['fh'] == EcalypseRental::ecalypse_rental_time_format($x.':00',24)) || ($default_enter_time == str_pad($x, 2, '0', STR_PAD_LEFT).':00')) { ?>selected<?php } ?>><?= EcalypseRental::ecalypse_rental_time_format(str_pad($x, 2, '0', STR_PAD_LEFT).':00',(isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24));?></option>
														<option value="<?= EcalypseRental::ecalypse_rental_time_format($x.':30',24); ?>" <?php if ((isset($_GET['fh']) && $_GET['fh'] == EcalypseRental::ecalypse_rental_time_format($x.':30',24)) || ($default_enter_time == str_pad($x, 2, '0', STR_PAD_LEFT).':30')) { ?>selected<?php } ?>><?= EcalypseRental::ecalypse_rental_time_format(str_pad($x, 2, '0', STR_PAD_LEFT).':30',(isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24));?></option>
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
								<input type="hidden" name="fh" id="ecalypse_rental_from_hour<?php echo $ecalypse_rental_booking_form_id;?>" value="00:00">
								<?php } ?>
							<?php } ?>
						</div>
					</div>

					<div class="column">
						<div class="columns<?php echo $disable_time ?  ' only-date' : '-2';?>">
							<div class="column column-wider">
								<div class="control-group">
									<div class="control-field">
										<span class="control-addon"<?php echo (isset($_GET['p']) && $_GET['p'] == 'day') || !isset($_GET['p']) ? '' : ' style="display:none;"';?>>
											<input type="text" class="control-input" name="td" readonly="readonly" id="ecalypse_rental_to_date<?php echo $ecalypse_rental_booking_form_id;?>" placeholder="<?= EcalypseRental::t('Return date') ?>" <?php if (isset($_GET['td'])) { ?>value="<?= htmlspecialchars($_GET['td']) ?>"<?php } ?>>
											<span class="control-addon-item">
												<span class="sprite-calendar"></span>
											</span>
										</span>
									</div>
								</div>
							</div>
							<?php if ($time_pricing_type !== 'half_day') { ?>
								<?php if (!$disable_time) { ?>
								<?php $default_return_time = get_option('default_return_time', ''); ?>
								<div class="column column-thiner">
									<div class="control-group">
										<div class="control-field">
											<span class="control-addon">
												<select name="th" id="ecalypse_rental_to_hour<?php echo $ecalypse_rental_booking_form_id;?>" style="width: 85%; padding:2px 9px; -webkit-border-radius: 4px; border-radius: 4px; font-size: 12px; ">
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
									<input type="hidden" name="th" id="ecalypse_rental_to_hour<?php echo $ecalypse_rental_booking_form_id;?>" value="00:00">
								<?php } ?>
							<?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		
		<div class="control-group">
			<div class="control-field align-right">
				<input type="hidden" name="page" value="ecalypse-rental">
				<button type="submit" name="book_now" class="btn btn-primary" id="ecalypse_rental_book_now<?php echo $ecalypse_rental_booking_form_id;?>"><?= EcalypseRental::t('BOOK NOW') ?></button>
			</div>
		</div>
		
	</fieldset>
	
	<ul id="ecalypse_rental_book_errors<?php echo $ecalypse_rental_booking_form_id;?>" style="margin:1em 2em;list-style-type:circle;color:tomato;"></ul>
</form>
</div>