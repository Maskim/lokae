<?php
/**
 * Choose car -filter
 *
 * @package WordPress
 * @subpackage EcalypseRental
 * @since EcalypseRental 3.0.3
 * @version 3.0.3
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
$lang = strtolower(end(explode('_', $lang)));

$disable_time = get_option('ecalypse_rental_disable_time');
if ($disable_time == 'yes') {$disable_time = true;} else {$disable_time = false;}
$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
if ($time_pricing_type == 'half_day') {
	$disable_time = true;
}
$showvat = get_option('ecalypse_rental_show_vat');
if ($showvat && $showvat == 'yes') {
	$showvat = true;
}
$call_price = get_option('ecalypse_rental_call_for_price');
if ($call_price && $call_price == 'yes') {
	$call_price = true;
} else {
	$call_price = false;
}
get_header(); ?>
<div class="ecalypse-rental-custom-theme">
	<section class="content">
		<div class="container">
			<ul class="steps columns-4 no-space">
				<li>
					<a href="<?= home_url(); ?>">
						<span class="steps-number">1</span> <?= EcalypseRental::t('Create request') ?>
						<span class="sprite-arrow-right"></span>
					</a>
				</li>
				<li>
					<?php $car_query = array(); ?>
					<?php foreach ($_GET as $key => $val) { ?>
						<?php if ($key != 'id_car') { ?>
							<?php $car_query[$key] = $val; ?>
						<?php } ?>
					<?php } ?>
					<a href="<?= home_url(); ?>?<?= http_build_query($car_query); ?>">
						<span class="steps-number">2</span> <?= EcalypseRental::t('Choose a car') ?>
						<span class="sprite-arrow-right"></span>
					</a>
				</li>
				<li class="active">
					<a href="javascript:void(0);">
						<span class="steps-number">3</span> <?= EcalypseRental::t('Services &amp; book') ?>
						<span class="sprite-arrow-right"></span>
					</a>
				</li>
				<li>
					<a href="javascript:void(0);">
						<span class="steps-number">4</span> <?= EcalypseRental::t('Summary') ?>
						<span class="sprite-arrow-right"></span>
					</a>
				</li>
			</ul>
		
		<div class="columns-2 break-md aside-on-left">
	<div class="column column-fixed">
		<div class="box box-clean">
			
			<div class="box-title mobile-toggle mobile-toggle-md" data-target="modify-search">
				<?= EcalypseRental::t('Modify reservation') ?>
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
											<option value="<?= $val->id_branch ?>" <?php if ((isset($_GET['el']) && (int) $_GET['el'] == $val->id_branch) || $locations_no == 1) { ?>selected<?php } ?>><?= $val->name ?></option>
										<?php } ?>	
									<?php } ?>
								</select>
							</div>
						</div>
						
						<?php if (!isset($theme_options['display_return_location']) || (isset($theme_options) && isset($theme_options['display_return_location']) && $theme_options['display_return_location'] == 1)) { ?>
						<div class="control-group">
							<div class="control-field">
								<label><input name="dl" id="ecalypse_rental_different_loc" type="checkbox" <?php if (isset($_GET['dl']) && $_GET['dl'] == 'on') { ?>checked<?php } ?>>&nbsp;&nbsp;<?= EcalypseRental::t('Returning to Different location.') ?></label>
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
								<input type="hidden" name="order" value="name" id="ecalypse_rental_order_input">
								<input type="hidden" name="book_now" value="ok">
								<input type="hidden" name="id_car" value="<?= (int) $_GET['id_car'] ?>">
								<input type="hidden" name="promo" value="<?php if (isset($_GET['promo'])) { ?><?= htmlspecialchars($_GET['promo']) ?><?php } ?>" id="ecalypse_rental_promocode">
								<input type="submit" name="search" value="<?= EcalypseRental::t('MODIFY') ?>" id="ecalypse_rental_book_now" class="btn btn-primary btn-block">
							</div>
							<!-- .control-field -->
						</div>
						<!-- .control-group -->

					</fieldset>
					
					<ul id="ecalypse_rental_book_errors" style="margin:1em 2em;list-style-type:circle;color:tomato;"></ul>
				</form>
			</div>
			
			<?php include(EcalypseRentalTheme::get_file_template_path('booking-javascript.php')); ?>
			
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
	
				<form action="" method="post" class="form form-vertical form-large" id="ecalypse_rental_confirm_reservation">
					<fieldset>
					
						<div class="bordered-content">
							
							<?php if (isset($_GET['search']) && !empty($_GET['search'])) { ?>
								<div class="bordered-content-title highlight"><?= EcalypseRental::t('Reservation was successfully updated.') ?></div>
							<?php } else { ?>
								<div class="bordered-content-title"><?= EcalypseRental::t('Complete reservation') ?></div>
							<?php if ((isset($_GET['ewayError']) && $_GET['ewayError'] == 1) || (isset($_GET['paymentError']) && $_GET['paymentError'] == 1)) { ?>
									<div class="bordered-content-title error"><?= EcalypseRental::t('Payment canceled. Please make payment to secure your reservation.') ?></div>
								<?php } ?>
							<?php } ?>
							
							<?php if (isset($vehicle) && !empty($vehicle)) { ?>
							
							<div class="box box-white box-inner">
								<div class="columns-2 break-lg">
									<div class="column column-thin list-item-car">
										<div class="list-item-media">
											<?php if (!empty($vehicle->picture)) { ?>
												<p><img src="<?= $vehicle->picture ?>" alt="<?= $vehicle->name ?>" width="200"></p>
											<?php } ?>
											<p><?= $vehicle->name ?></p>
										</div>
									</div>
									<!-- .column -->

									<div class="column column-wide">
										<div class="box box-darken box-inner">
											<div class="columns-2">
												<div class="column">
																					
													<h5><?= EcalypseRental::t('Pick Up') ?></h5>
													<p class="point-location highlight">
														<?php if (isset($locations) && !empty($locations) && isset($locations[(int) $_GET['el']])) { ?>
															<?= $locations[(int) $_GET['el']]->name ?>
														<?php } else { ?>
															&mdash;
														<?php } ?>
													</p>

													<div class="icon-text highlight">
														<span class="sprite-calendar"></span> <?= Date(EcalypseRental::date_format_php(isset($theme_options['date_format']) ? $theme_options['date_format'] : '', false), strtotime($_GET['fd'])) ?>                  
													</div>
													<?php if (!$disable_time) { ?>
													<div class="icon-text highlight">
														<span class="sprite-time"></span> <?= EcalypseRental::ecalypse_rental_time_format(Date('H:i', strtotime($_GET['fh'])),(isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24)); ?>
													</div>
													<?php } ?>
																			
													<h5><?= EcalypseRental::t('Drop Off') ?></h5>
													<p class="point-location highlight">
														<?php if (isset($locations) && !empty($locations) && isset($locations[(int) $_GET['rl']])) { ?>
															<?= $locations[(int) $_GET['rl']]->name ?>
														<?php } elseif (isset($locations) && !empty($locations) && isset($locations[(int) $_GET['el']])) { ?>
															<?= $locations[(int) $_GET['el']]->name ?>
															<?php $_GET['rl'] = (int) $_GET['el']; ?>
														<?php } else { ?>
															&mdash;
														<?php } ?>
													</p>

													<div class="icon-text highlight">
														<span class="sprite-calendar"></span> <?= Date(EcalypseRental::date_format_php(isset($theme_options['date_format']) ? $theme_options['date_format'] : '', false), strtotime($_GET['td'])) ?>            
													</div>
													<?php if (!$disable_time) { ?>
													<div class="icon-text highlight">
														<span class="sprite-time"></span> <?= EcalypseRental::ecalypse_rental_time_format(Date('H:i', strtotime($_GET['th'])),(isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24)); ?>
													</div>
													<?php } ?>
													
													<h5><?= EcalypseRental::t('Rental time') ?></h5>
													<p><?php echo EcalypseRental::time_diff($_GET['fd'].(isset($_GET['fh']) ? ' '.$_GET['fh'] : ''), $_GET['td'].(isset($_GET['th']) ? ' '.$_GET['th'] : ''));?></p>
													
													<?php if (isset($_GET['search']) && !empty($_GET['search'])) { ?>
														<script type="text/javascript">
															jQuery(document).ready(function() {
																jQuery('.highlight').effect( 'highlight', {}, 2000);
															});
														</script>
													<?php } ?>
												</div>
												<!-- .column -->	
												
												<?php $distance_metric = get_option('ecalypse_rental_distance_metric'); ?>
			
												<div class="column">
													<div class="icon-text-list">
														<?php if (isset($vehicle->ac) && (int) $vehicle->ac > 0) { ?>
															<div class="icon-text"><span class="sprite-snowflake"></span><?php if ($vehicle->ac == 2) { ?><?= EcalypseRental::t('No A/C') ?><?php } else { ?><?= EcalypseRental::t('A/C') ?><?php } ?></div>
														<?php } ?>
														<?php if (isset($vehicle->luggage) && !empty($vehicle->luggage)) { ?>
															<div class="icon-text"><span class="sprite-briefcase"></span><?= $vehicle->luggage ?>&times; <?= EcalypseRental::t('Luggage Quantity') ?></div>
														<?php } ?>
														<?php if (isset($vehicle->seats) && !empty($vehicle->seats)) { ?>
															<div class="icon-text"><span class="sprite-person"></span><?= $vehicle->seats ?>&times; <?= EcalypseRental::t('Persons') ?></div>
														<?php } ?>
														<?php if (isset($vehicle->fuel) && !empty($vehicle->fuel)) { ?>
															<div class="icon-text"><span class="sprite-fuel"></span><?php if ($vehicle->fuel == 1) { ?><?= EcalypseRental::t('Petrol') ?><?php } else { ?><?= EcalypseRental::t('Diesel') ?><?php } ?></div>
														<?php } ?>
														<?php if (isset($vehicle->consumption) && !empty($vehicle->consumption)) { ?>
															<?php $consumption = get_option('ecalypse_rental_consumption'); ?>
															<?php if (!$consumption || empty($consumption)) { $consumption = 'eu'; } ?>
															<div class="icon-text"><span class="sprite-timeout"></span><abbr title="<?= EcalypseRental::t('Average Consumption') ?>"><?= $vehicle->consumption ?> <?= (($consumption == 'eu') ? 'l/100km' : 'MPG') ?></abbr></div>
														<?php } ?>
														
														<?php if (isset($vehicle->transmission) && !empty($vehicle->transmission)) { ?>
															<div class="icon-text"><?= (($vehicle->transmission == 1) ? EcalypseRental::t('Transmission: Automatic') : EcalypseRental::t('Transmission: Manual')) ?></div>
														<?php } ?>
														<?php if (isset($vehicle->free_distance)) { ?>
															<div class="icon-text"><?= EcalypseRental::t('Free distance') ?>: <?php if (isset($vehicle->free_distance_total)) { echo $vehicle->free_distance_total == 0 ? EcalypseRental::t('Unlimited') : $vehicle->free_distance_total.'&nbsp;'.$distance_metric; } elseif ($vehicle->free_distance > 0) { ?><?= (isset($vehicle->prices) ? (int)$vehicle->prices['diff_days'] : 1) * $vehicle->free_distance ?>&nbsp;<?= $distance_metric ?> <?= EcalypseRental::t('per day') ?><?php } else { ?><?= EcalypseRental::t('Unlimited') ?><?php } ?></div>
														<?php } ?>
														<?php if (isset($vehicle->deposit) && $vehicle->deposit != '' && $vehicle->deposit > 0) { ?>
															<?php $global_currency = get_option('ecalypse_rental_global_currency');
															$av_currencies = unserialize(get_option('ecalypse_rental_available_currencies'));
															$rate = 1;
															if (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_currency']) && isset($av_currencies[EcalypseRentalSession::$session['ecalypse_rental_currency']])) {
																$current_currency = EcalypseRentalSession::$session['ecalypse_rental_currency'];
															} else {
																$current_currency = $global_currency;
															}
															?>
															<?php if ($current_currency != $global_currency && isset($av_currencies[$current_currency])) {
																$rate = $av_currencies[$current_currency];
															} ?>
															<div class="icon-text"><?= EcalypseRental::t('Deposit') ?>: <?php if ($vehicle->deposit > 0) { ?><?= EcalypseRental::get_currency_symbol('before', $current_currency); ?><?= round(($vehicle->deposit / $rate),2) ?>&nbsp;<?= EcalypseRental::get_currency_symbol('after', $current_currency); ?><?php } else { ?>0<?php } ?></div>
														<?php } ?>
														
														
														<?php if (isset($vehicle->description) && !empty($vehicle->description)) { ?>
															<br><a href="javascript:void(0);" class="ecalypse_rental_car_details_link"><?= EcalypseRental::t('Show details') ?></a>
															<p class="ecalypse_rental_car_details">
																<?php $fleet_description = unserialize($vehicle->description); ?>
																<?php if ($fleet_description == false) { $fleet_description['gb'] = $vehicle->description; } ?>
																<?php $lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB'); ?>
																<?php $lang = end(explode('_', $lang)); ?>
																<?= (isset($fleet_description[strtolower($lang)]) ? EcalypseRental::removeslashes($fleet_description[strtolower($lang)]) : EcalypseRental::removeslashes($fleet_description['gb'])) ?>
																<?php $additional_parameters = unserialize($vehicle->additional_parameters); ?>
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

													<p>
														<strong><?= EcalypseRental::t('Payable Amount') ?></strong><br>
														<?php if (isset($vehicle->prices) && !empty($vehicle->prices)) { ?>
															<span class="additional xxlarge" <?php if ($vehicle->prices['maxprice_reached'] == true) { ?>style="color:tomato;" title="<?= EcalypseRental::t('Maximum price for this vehicle was reached.') ?>"<?php } ?>><?= $vehicle->prices['cc_before'] ?><?= number_format(($showvat ? $vehicle->prices['total_price_with_tax'] : $vehicle->prices['total_rental']), 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
														<?php } else { ?>
															<span class="additional xxlarge<?php echo $call_price ? ' ecalypse_call_price' : '';?>"><?= $call_price ? EcalypseRental::t('Call for price') : EcalypseRental::t('Not available') ?></span>
														<?php } ?>
													</p>
									
												</div>
											</div>
										</div>
									</div>
								</div>
								
								<?php if (isset($vehicle->prices) && !empty($vehicle->prices)) { ?>
									<hr class="separate">

									<?php $additional_driver = array(); ?>
									<?php if ($extras && !empty($extras)) { ?>
										<div class="h2 additional"><?= EcalypseRental::t('Available Extras') ?></div>

										<div class="columns-1 break-lg extras-div">										
											<div class="column">
												<?php foreach ($extras as $key => $val) { ?>
												
													<?php if (empty($additional_driver) && $val->max_additional_drivers > 0) { ?>
														<?php $additional_driver = $val; ?>
														<?php $additional_driver_class = 'additional_driver_price'; ?>
													<?php } else { ?>
														<?php $additional_driver_class = 'ecalypse_rental_extras'; ?>
													<?php } ?>

													<label class="custom-block">
														<?php $datavalue = $taxtotal = $tax = 0; ?>
														<?php if (isset($val->prices) && !empty($val->prices)) { ?>
															<?php $datavalue = number_format($val->prices['total_rental'], 2, '.', ''); ?>
															<?php $taxtotal = number_format($val->prices['tax_total_rental'], 2, '.', ''); ?>
															<?php $tax = number_format($val->prices['vat'], 2, '.', ''); ?>
															<?php $tax_2 = number_format($val->prices['vat_2'], 2, '.', ''); ?>
															<?php $taxtotal_2 = number_format($val->prices['tax_total_rental_2'], 2, '.', ''); ?>
															<?php $tax_3 = number_format($val->prices['vat_3'], 2, '.', ''); ?>
															<?php $taxtotal_3 = number_format($val->prices['tax_total_rental_3'], 2, '.', ''); ?>

														<?php } ?>

														
														<?php if (isset($val->name_translations) && $lang != 'gb') {
															$val->name_translations = unserialize($val->name_translations);
															if (isset($val->name_translations[$lang]) && $val->name_translations[$lang] != '') { 
																$extra_name = $val->name_translations[$lang];
															} else {
																$extra_name = $val->name; 
															} 
														} else {
															$extra_name = $val->name; 
														} ?>
														<?php if (isset($val->description_translations) && $lang != 'gb') {
															$val->description_translations = unserialize($val->description_translations);
															if (isset($val->description_translations[$lang]) && $val->description_translations[$lang] != '') { 
																$extra_description = $val->description_translations[$lang];
															} else {
																$extra_description = $val->description; 
															} 
														} else {
															$extra_description = $val->description; 
														} ?>
														<input<?php echo $val->mandatory == 1 ? ' checked="checked" disabled="disabled"' : '';?> type="checkbox" name="extras[]" value="<?= $val->id_extras ?>" data-name="<?= $extra_name ?>" data-value-in="0" data-value="<?= $datavalue ?>" data-tax-value="<?= $tax ?>" data-taxtotal-value="<?= $taxtotal ?>" data-tax-2-value="<?= $tax_2 ?>" data-taxtotal-2-value="<?= $taxtotal_2 ?>" data-currency-before="<?= $val->prices['cc_before'] ?>" data-currency-after="<?= $val->prices['cc_after'] ?>" class="<?= $additional_driver_class ?>">
														<?php echo $extra_name;?>
														<?php echo $extra_description == '' ? '' : '<span class="extra-desc" data-title="'.$extra_name.'" data-desc="'.strip_tags(str_replace('"','',$extra_description)).'">?</span>';?>
														<span class="pull-right">
															<?php if (isset($val->prices) && !empty($val->prices)) { ?>
																<?php if ($val->prices['type'] == 1) { ?>
																	<?= $val->prices['cc_before'] ?><?= number_format(($showvat ? $val->prices['total_price_with_tax'] : $val->prices['total_rental']), 2, '.', ',') ?><?= $val->prices['cc_after'] ?> - <?= EcalypseRental::t('one time') ?>
																<?php } else { ?>
																	<span <?php if ($val->prices['maxprice_reached'] == true) { ?>style="color:tomato;" title="<?= EcalypseRental::t('Maximum price for this item was reached.') ?>"<?php } ?>>
																		<?= $val->prices['cc_before'] ?><?= number_format(($showvat ? $val->prices['price_with_tax'] : $val->prices['price']), 2, '.', ',') ?><?= $val->prices['cc_after'] ?> - <?php if ($val->prices['pr_type'] == 2) { ?><?= EcalypseRental::t('per hour') ?><?php } else { ?><?= EcalypseRental::t('per day') ?><?php } ?>
																	</span>
																<?php } ?>
															<?php } else { ?>
																	<span class="<?php echo $call_price ? ' ecalypse_call_price' : '';?>"><?= $call_price ? EcalypseRental::t('Call for price') : EcalypseRental::t('Not available') ?></span>
															<?php } ?>
														</span>
													</label>
												<?php } ?>
											</div>
										</div>

										<hr class="separate">

									<?php } ?>
										
									<?php 
									$multiple_rental = get_option('ecalypse_rental_multiple_rental');
									   $selected_additional_vehicles = array();
									   $overbooking = get_option('ecalypse_rental_overbooking');
								   ?>
								   <?php if ($multiple_rental && $multiple_rental == 1 && isset($vehicle->additional_vehicles)) { ?>
										<div class="h2 additional"><?= EcalypseRental::t('Other vehicles to book') ?></div>

										<div class="columns-1 break-lg">
											<div class="column">

									   <div class="additional-vehicles clearfix">
										   <?php $additional_used_total = 0; 
											   foreach ($vehicle->additional_vehicles as $val) { ?>
											   <?php if (isset($val->error) && $val->error != '') { continue; } ?>
											   <?php if ($val->id_fleet == $_GET['id_car'] && $val->rented_cars+1 >= $val->number_vehicles) { continue; } ?>
											   <?php if (isset($val->prices) && !empty($val->prices)) { ?>
													   <?php 
													   $datavalue = $taxtotal = $tax = 0;

													   $datavalue = number_format($val->prices['total_rental'], 2, '.', '');
													   $taxtotal = number_format($val->prices['tax_total_rental'], 2, '.', '');
													   $tax = number_format($val->prices['vat'], 2, '.', '');
													   $tax_2 = number_format($val->prices['vat_2'], 2, '.', '');
													   $taxtotal_2 = number_format($val->prices['tax_total_rental_2'], 2, '.', '');
													   $tax_3 = number_format($val->prices['vat_3'], 2, '.', '');
													   $taxtotal_3 = number_format($val->prices['tax_total_rental_3'], 2, '.', '');

													   if ((float) $val->prices['vat'] > 0 && $showvat) {													
														   $val->prices['price'] = $val->prices['price_with_tax'];
														   $val->prices['total_rental'] = $val->prices['total_price_with_tax'];
													   }
													   $used = 0;
													   if (isset($_GET['additional']) && in_array($val->id_fleet, $_GET['additional'])) {
														   $tmp = array_count_values($_GET['additional']);
														   for ($j=1; $j<=$tmp[$val->id_fleet];$j++) {
															   $used++;
															   $additional_used_total++;
															   $selected_additional_vehicles[] = array('id' => $val->id_fleet, 'name' => $val->name.', '.Date(EcalypseRental::date_format_php(isset($theme_options['date_format']) ? $theme_options['date_format'] : ''), strtotime($_GET['fd'])).' &ndash; '. Date(EcalypseRental::date_format_php(isset($theme_options['date_format']) ? $theme_options['date_format'] : ''), strtotime($_GET['td'])), 'price' => $datavalue, 'tax' => $tax, 'tax_total' => $taxtotal, 'tax_2' => $tax_2, 'tax_total_2' => $taxtotal_2, 'tax_3' => $tax_3, 'tax_total_3' => $taxtotal_3, 'cc_before' => $val->prices['cc_before'], 'cc_after' => $val->prices['cc_after']);
															   $vats[1] += $taxtotal;
															   $vats[2] += $taxtotal_2;
															   $vats[3] += $taxtotal_3;
															   $add_to_discount += $taxtotal + $taxtotal_2 + $taxtotal_3 + $datavalue;
														   }
													   }
											   } else {
												   continue;
											   }
											   if ($overbooking == 'yes') {
												   $val->number_vehicles = 999;
											   }
													   ?>
											   <div<?php echo $used >= ($val->number_vehicles - $val->rented_cars - ($val->id_fleet == $_GET['id_car'] ? 1 : 0)) ? ' class="na-u"' : '';?>>
												   <a href="<?php echo EcalypseRental::get_fleet_url($val->id_fleet, $val->name, array('el', 'fh', 'fd', 'td', 'th'));?>" target="_blank">
													   <img src="<?= $val->picture ?>" alt="<?= $val->name ?>">
												   </a>
												   <h3>
													   <a  target="_blank" href="<?php echo EcalypseRental::get_fleet_url($val->id_fleet, $val->name);?>"><?= $val->name ?></a>
												   </h3>

													   <span class="price"><?= $val->prices['cc_before'] ?><?= number_format($val->prices['price'], 2, '.', ',') ?><?= $val->prices['cc_after'] ?> <?php if ($val->prices['pr_type'] == 2) { ?><?= EcalypseRental::t('per hour') ?><?php } else { ?><?= EcalypseRental::t('per day') ?><?php } ?></span><br>
													   <span class="total"><?= EcalypseRental::t('Total:') ?><?= $val->prices['cc_before'] ?><?= number_format($val->prices['total_rental_clear'], 2, '.', ',') ?><?= $val->prices['cc_after'] ?></span>
													   <a href="#" data-used="<?php echo $used;?>" data-count="<?php echo $val->number_vehicles - $val->rented_cars - ($val->id_fleet == $_GET['id_car'] ? 1 : 0);?>"  data-id="<?= $val->id_fleet ?>" data-name="<?= $val->name; ?>, <?= Date(EcalypseRental::date_format_php(isset($theme_options['date_format']) ? $theme_options['date_format'] : ''), strtotime($_GET['fd'].' '.$_GET['fh'])) ?> &ndash; <?= Date(EcalypseRental::date_format_php(isset($theme_options['date_format']) ? $theme_options['date_format'] : ''), strtotime($_GET['td'].' '.$_GET['th'])) ?>" data-value-in="0" data-value="<?= $datavalue ?>" data-tax-value="<?= $tax ?>" data-taxtotal-value="<?= $taxtotal ?>" data-tax-2-value="<?= $tax_2 ?>" data-taxtotal-2-value="<?= $taxtotal_2 ?>" data-tax-3-value="<?= $tax_3 ?>" data-taxtotal-3-value="<?= $taxtotal_3 ?>" data-currency-before="<?= $val->prices['cc_before'] ?>" data-currency-after="<?= $val->prices['cc_after'] ?>" class="add_car"><?php echo EcalypseRental::t('Add this') ?></a>
											   </div>
										   <?php } ?>
										</div>
										</div>
										</div>
										<hr class="separate">
								   <?php } ?>

									<div class="h2 additional"><?= EcalypseRental::t('Summary of Charges') ?></div>

									<?php if ($american_pricing) { ?>
										<?php $american_prices = EcalypseRental_American_Pricing::service_book_summary($vehicle, $theme_options); ?>
										<?php foreach ($american_prices as $pr) { ?>
											<div class="row row-boxed">
												<?php echo $pr['name'];?>
												<?php if (isset($pr['price']) && !empty($pr['price']) && $pr['price'] > 0) { ?>
													<span class="pull-right ecalypse_rental_total_price ecalypse_rental_currency" data-value-in="1" data-value="<?= number_format($pr['price'], 2, '.', '') ?>" data-currency-before="<?= $vehicle->prices['cc_before'] ?>" data-currency-after="<?= $vehicle->prices['cc_after'] ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format($pr['price'], 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
												<?php } else { ?>
													<span class="pull-right<?php echo $call_price ? ' ecalypse_call_price' : '';?>"><?= $call_price ? EcalypseRental::t('Call for price') : EcalypseRental::t('Not available') ?></span>
												<?php } ?>
											</div>
										<?php } ?>
									<?php } else { ?>
										<div class="row row-boxed">
											<?= $vehicle->name ?>, <?= Date(EcalypseRental::date_format_php(isset($theme_options['date_format']) ? $theme_options['date_format'] : ''), strtotime($_GET['fd'].' '.$_GET['fh'])) ?> <?php if ($time_pricing_type !== 'half_day' || ($time_pricing_type == 'half_day' && $_GET['p'] == 'day')) { ?>&ndash; <?= Date(EcalypseRental::date_format_php(isset($theme_options['date_format']) ? $theme_options['date_format'] : ''), strtotime($_GET['td'].' '.$_GET['th'])) ?><?php } else { echo $_GET['p'] == 'pm' ? EcalypseRental::t('PM') : EcalypseRental::t('AM'); }?>
											<?php if (isset($vehicle->prices) && !empty($vehicle->prices)) { ?>
												<span class="pull-right ecalypse_rental_total_price ecalypse_rental_currency" data-value-in="1" data-value="<?= number_format($vehicle->prices['total_rental_clear'], 2, '.', '') ?>" data-currency-before="<?= $vehicle->prices['cc_before'] ?>" data-currency-after="<?= $vehicle->prices['cc_after'] ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format($vehicle->prices['total_rental_clear'], 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
											<?php } else { ?>
												<span class="pull-right<?php echo $call_price ? ' ecalypse_call_price' : '';?>"><?= $call_price ? EcalypseRental::t('Call for price') : EcalypseRental::t('Not available') ?></span>
											<?php } ?>
										</div>
									<?php } ?>
									
									<?php if (isset($vehicle->prices['summary']) && isset($vehicle->prices['summary']['branch_distance_price'])) { ?>
										<div class="row row-boxed">
											<abbr title="<?= EcalypseRental::t('Return location is different from pick-up location.') ?>"><?= EcalypseRental::t('Returning to Different location.') ?></abbr>
											<span class="pull-right ecalypse_rental_total_price" data-value-in="1" data-value="<?= number_format($vehicle->prices['summary']['branch_distance_price'], 2, '.', '') ?>" data-currency-before="<?= $vehicle->prices['cc_before'] ?>" data-currency-after="<?= $vehicle->prices['cc_after'] ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format($vehicle->prices['summary']['branch_distance_price'], 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php } ?>
									
									<?php if (isset($vehicle->prices['summary']) && isset($vehicle->prices['summary']['branch_pick_up_price'])) { ?>
										<div class="row row-boxed">
											<abbr title="<?= EcalypseRental::t('Fee for pick-up on specific branch.') ?>"><?= EcalypseRental::t('Fee for pick-up on specific branch.') ?></abbr>
											<span class="pull-right ecalypse_rental_total_price" data-value-in="1" data-value="<?= number_format($vehicle->prices['summary']['branch_pick_up_price'], 2, '.', '') ?>" data-currency-before="<?= $vehicle->prices['cc_before'] ?>" data-currency-after="<?= $vehicle->prices['cc_after'] ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format($vehicle->prices['summary']['branch_pick_up_price'], 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php } ?>
									
									<?php if (isset($vehicle->prices['summary']) && isset($vehicle->prices['summary']['branch_returning_price'])) { ?>
										<div class="row row-boxed">
											<abbr title="<?= EcalypseRental::t('Fee for returning on specific branch.') ?>"><?= EcalypseRental::t('Fee for returning on specific branch.') ?></abbr>
											<span class="pull-right ecalypse_rental_total_price" data-value-in="1" data-value="<?= number_format($vehicle->prices['summary']['branch_returning_price'], 2, '.', '') ?>" data-currency-before="<?= $vehicle->prices['cc_before'] ?>" data-currency-after="<?= $vehicle->prices['cc_after'] ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format($vehicle->prices['summary']['branch_returning_price'], 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php } ?>
									
									<?php if (isset($vehicle->prices['outside_booking_enter']) && $vehicle->prices['outside_booking_enter'] > 0) { ?>
										<div class="row row-boxed">
											<abbr title="<?= EcalypseRental::t('Fee for pick-up outside booking hours.') ?>"><?= EcalypseRental::t('Fee for pick-up outside booking hours.') ?></abbr>
											<span class="pull-right ecalypse_rental_total_price" data-value-in="1" data-value="<?= number_format($vehicle->prices['outside_booking_enter'], 2, '.', '') ?>" data-currency-before="<?= $vehicle->prices['cc_before'] ?>" data-currency-after="<?= $vehicle->prices['cc_after'] ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format($vehicle->prices['outside_booking_enter'], 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php } ?>
									
									<?php if (isset($vehicle->prices['outside_booking_return']) && $vehicle->prices['outside_booking_return'] > 0) { ?>
										<div class="row row-boxed">
											<abbr title="<?= EcalypseRental::t('Fee for returning outside booking hours.') ?>"><?= EcalypseRental::t('Fee for returning outside booking hours.') ?></abbr>
											<span class="pull-right ecalypse_rental_total_price" data-value-in="1" data-value="<?= number_format($vehicle->prices['outside_booking_return'], 2, '.', '') ?>" data-currency-before="<?= $vehicle->prices['cc_before'] ?>" data-currency-after="<?= $vehicle->prices['cc_after'] ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format($vehicle->prices['outside_booking_return'], 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php } ?>

									<?php //if (isset($vehicle->prices) && !empty($vehicle->prices) && $vehicle->prices['tax_total_rental'] > 0) { ?>
										<div class="row row-boxed">
											<?= $vehicle->prices['vat'] ?>% <?= EcalypseRental::t('Value Added Tax') ?>
											<span class="pull-right ecalypse_rental_tax_item ecalypse_rental_total_price vat_item" data-vat="<?= $vehicle->prices['vat'] ?>" data-value-in="1" data-value="<?= number_format($vehicle->prices['tax_total_rental'], 2, '.', '') ?>"><?= $vehicle->prices['cc_before'] ?><span><?= number_format($vehicle->prices['tax_total_rental'], 2, '.', ',') ?></span><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php //} ?>
									
									<?php //if (isset($vehicle->prices) && !empty($vehicle->prices) && $vehicle->prices['tax_total_rental_2'] > 0) { ?>
										<div class="row row-boxed">
											<?= $vehicle->prices['vat_2'] ?>% <?= EcalypseRental::t('Value Added Tax 2') ?>
											<span class="pull-right ecalypse_rental_tax_item ecalypse_rental_total_price vat_item_2" data-vat="<?= $vehicle->prices['vat_2'] ?>" data-value-in="1" data-value="<?= number_format($vehicle->prices['tax_total_rental_2'], 2, '.', '') ?>"><?= $vehicle->prices['cc_before'] ?><span><?= number_format($vehicle->prices['tax_total_rental_2'], 2, '.', ',') ?></span><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php //} ?>
									
									<?php //if (isset($vehicle->prices) && !empty($vehicle->prices) && $vehicle->prices['tax_total_rental_3'] > 0) { ?>
										<div class="row row-boxed">
											<?= $vehicle->prices['vat_3'] ?>% <?= EcalypseRental::t('Value Added Tax 3') ?>
											<span class="pull-right ecalypse_rental_tax_item ecalypse_rental_total_price vat_item_3" data-vat="<?= $vehicle->prices['vat_3'] ?>" data-value-in="1" data-value="<?= number_format($vehicle->prices['tax_total_rental_3'], 2, '.', '') ?>"><?= $vehicle->prices['cc_before'] ?><span><?= number_format($vehicle->prices['tax_total_rental_3'], 2, '.', ',') ?></span><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php //} ?>
									
									<?php
										if (isset($locations[(int) $_GET['el']]) && isset($locations[(int) $_GET['el']]->branch_tax)) {
											if (!empty($locations[(int) $_GET['el']]->branch_tax)) {
												$lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB');
												$lang = strtolower(end(explode('_', $lang)));

												foreach ($locations[(int) $_GET['el']]->branch_tax as $k => $v) {
													$tax_name = $v['name'];
													if ($lang != 'gb') {
														if (isset($v['name_translations'][$lang]) && $v['name_translations'][$lang] != '') {
															$tax_name = $v['name_translations'][$lang];
														}
													}?>
													<div class="row row-boxed">
														<abbr title="<?= $v['tax'].'% '.$tax_name; ?>"><?= $v['tax'].'% '.$tax_name; ?></abbr>
														<span class="pull-right ecalypse_rental_tax_item ecalypse_rental_total_price branch_specific_tax branch_tax_<?php echo $k;?>" data-value-in="1" data-value="<?= number_format(0, 2, '.', '') ?>" data-currency-before="<?= $vehicle->prices['cc_before'] ?>" data-currency-after="<?= $vehicle->prices['cc_after'] ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format(0, 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
													</div>
													<?php
												}
											}
										}
									?>
									
									<?php $delivery_price = EcalypseRental::get_delivery_price(); ?>
									<?php if ((int) $_GET['el'] != (int) $_GET['rl'] && $delivery_price && (float) $delivery_price > 0) { ?>
										<div class="row row-boxed">
											<abbr title="<?= EcalypseRental::t('Pick-up location is different from return location.') ?>"><?= EcalypseRental::t('Car delivery to different location') ?></abbr>
											<span class="pull-right ecalypse_rental_total_price" data-value-in="1" data-value="<?= number_format($delivery_price, 2, '.', '') ?>"><?= $vehicle->prices['cc_before'] ?><?= number_format($delivery_price, 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></span>
										</div>
									<?php } ?>

									<div class="row row-total" id="ecalypse_rental_summary_charges">

										<div class="discount">
											<span class="control-appended control-appended-nospace">
												<span class="control-appended-input">
													<span class="control-addon">
														<span class="control-addon-item">
															<span class="sprite-discount"></span>
														</span>
														<?php if (isset($_GET['promo']) && !empty($_GET['promo'])) { ?>
															<input type="hidden" name="promo" value="">
															<input type="text" class="control-input" disabled value="<?= htmlspecialchars($_GET['promo']) ?>">
														<?php } else { ?>
															<input type="text" name="promo" id="ecalypse_rental_promo_value" class="control-input" placeholder="<?= EcalypseRental::t('Discount/Promotion Code') ?>">
														<?php } ?>
													</span>
												</span>						
												<span class="control-appended-btn">
													<?php if (isset($_GET['promo']) && !empty($_GET['promo'])) { ?>
														<a href="javascript:void(0);" type="button" id="ecalypse_rental_promo_remove" class="btn btn-light"><?= EcalypseRental::t('REMOVE') ?></a>
													<?php } else { ?>
														<a href="javascript:void(0);" id="ecalypse_rental_promo_add" class="btn btn-light"><?= EcalypseRental::t('ADD') ?></a>
													<?php } ?>
												</span>
											</span>
										</div>

										<script type="text/javascript">

											jQuery('#ecalypse_rental_promo_value').bind("keyup keypress", function(e) {
											  var code = e.keyCode || e.which; 
											  if (code  == 13) {               
												e.preventDefault();
												return false;
											  }
											});

											jQuery('#ecalypse_rental_promo_add').on('click', function() {
												jQuery('#ecalypse_rental_promocode').val(jQuery('#ecalypse_rental_promo_value').val());
												jQuery('#ecalypse_rental_booking_form').submit();	
											});

											jQuery('#ecalypse_rental_promo_remove').on('click', function() {
												jQuery('#ecalypse_rental_promocode').val('');
												jQuery('#ecalypse_rental_booking_form').submit();	
											});

										</script>

										<p class="pull-right">
											<strong><?= EcalypseRental::t('Total Amount') ?> </strong><br>
											<?php if (isset($vehicle->prices) && !empty($vehicle->prices)) { ?>
												<span class="additional xxlarge ecalypse_rental_total_amount"> - </span>
											<?php } else { ?>
												<span class="additional xxlarge<?php echo $call_price ? ' ecalypse_call_price' : '';?>"><?= $call_price ? EcalypseRental::t('Call for price') : EcalypseRental::t('Not available') ?></span>
											<?php } ?>
										</p>

									</div>
									<!-- .row -->
									
									<?php
									$user = array();
									if (defined('ECALYPSERENTALSTARTER_CLIENT_AREA_VERSION') && is_plugin_active( 'ecalypse-rental-client-area/ecalypse-rental-client-area.php' )) {
										$user = EcalypseRental_Client_Area::get_current_user();
									}
									
									$inputs = get_option('ecalypse_rental_reservation_inputs');
									$inputs = unserialize($inputs);
									?>

									<hr class="separate">

									<div class="h2 additional"><?= EcalypseRental::t('Driver details') ?></div>

									<div class="form-size-100">

										<div class="columns-2 control-group">
											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="first_name" value="<?php echo isset($user['first_name']) ? $user['first_name'] : '';?>" placeholder="<?= EcalypseRental::t('Enter First Name') ?>">
												</div>
											</div>

											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="last_name" value="<?php echo isset($user['last_name']) ? $user['last_name'] : '';?>" placeholder="<?= EcalypseRental::t('Last name') ?>">
												</div>
											</div>
										</div>

										<div class="columns-2 control-group">
											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="email" value="<?php echo isset($user['email']) ? $user['email'] : '';?>" placeholder="<?= EcalypseRental::t('Email address') ?>">
												</div>
											</div>

											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="phone" value="<?php echo isset($user['phone']) ? $user['phone'] : '';?>" placeholder="<?= EcalypseRental::t('Phone number') ?>">
												</div>
											</div>
										</div>

										<div class="columns-2 control-group">
											<div class="column column-wider">
												<div class="columns-2">

													<div class="column column-wider">
														<div class="control-field">
															<input type="text" class="control-input" name="street" value="<?php echo isset($user['street']) ? $user['street'] : '';?>" placeholder="<?= EcalypseRental::t('Street') ?>">
														</div>
													</div>

													<div class="column column-thiner">
														<div class="control-field">
															<input type="text" class="control-input" name="city" value="<?php echo isset($user['city']) ? $user['city'] : '';?>" placeholder="<?= EcalypseRental::t('City') ?>">
														</div>
													</div>
												</div>

											</div>

											<div class="column column-thiner">
												<div class="columns-2">
													<div class="column column-thin">
														<div class="control-field">
															<input type="text" class="control-input" name="zip" value="<?php echo isset($user['zip']) ? $user['zip'] : '';?>" placeholder="<?= EcalypseRental::t('ZIP') ?>">
														</div>
													</div>

													<div class="column column-wide">
														<div class="control-field">
															<select name="country" style="width:133px;">
																<option value=""><?= EcalypseRental::t('Country') ?></option>
																<?php $countries = EcalypseRental::get_country_list(); ?>
															<?php foreach ($countries as $key => $val) { ?>
																<option value="<?= $key ?>"<?php echo isset($user['country']) && $user['country'] == $key ?  ' selected="selected"' : '';?>><?= $val ?></option>
															<?php } ?>
															</select>
														</div>
													</div>
												</div>
											</div>
										</div>

										<div class="columns-3 control-group">
											<?php if (!isset($inputs['company'])) { ?>
											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="company" value="<?php echo isset($user['company']) ? $user['company'] : '';?>" placeholder="<?= EcalypseRental::t('Company name') ?>">
												</div>
											</div>
											<?php } ?>

											<?php if (!isset($inputs['vat'])) { ?>
											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="vat" value="<?php echo isset($user['vat']) ? $user['vat'] : '';?>" placeholder="<?= EcalypseRental::t('VAT number') ?>">
												</div>
											</div>
											<?php } ?>

											<?php if (!isset($inputs['flight'])) { ?>
											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="flight" placeholder="<?= EcalypseRental::t('Flight number') ?>">
												</div>
											</div>
											<?php } ?>

										</div>

										<div class="columns-3 control-group">

											<?php if (!isset($inputs['license'])) { ?>
											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="license" placeholder="<?= EcalypseRental::t('License number') ?>">
												</div>
											</div>
											<?php } ?>

											<?php if (!isset($inputs['id_card'])) { ?>
											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="id_card" placeholder="<?= EcalypseRental::t('ID / Passport number') ?>">
												</div>
											</div>
											<?php } ?>
											
											<?php if (!isset($inputs['partner_code'])) { ?>
											<div class="column">
												<div class="control-field">
													<input type="text" class="control-input" name="partner_code" placeholder="<?= EcalypseRental::t('Partner code') ?>">
												</div>
											</div>
											<?php } ?>
										</div>
										<?php do_action( 'ecalypse_rental_services_book_after_driver_details'); ?>
									</div>		
									
									<hr class="separate">

									<div class="h2 additional"><?= EcalypseRental::t('Comment') ?></div>

									<div class="form-size-100">

										<div class="columns-1 control-group">
											<textarea class="control-input" name="comment" placeholder="<?= EcalypseRental::t('Enter Your comment here') ?>"></textarea>
										</div>
									</div>

									<hr class="separate">

									<?php if (!empty($additional_driver)) { ?>
										<div class="additional_driver_box">
											<div class="h2 additional"><?= EcalypseRental::t('Additional Drivers') ?></div>

											<div class="form-size-100">
												<div class="columns-2 control-group">
													<div class="column">
														<div class="control-field">
															<select name="drivers" id="additional_drivers_change">
																<option value="0"><?= EcalypseRental::t('No additional driver') ?></option>
																<?php for ($add = 1; $add <= $additional_driver->max_additional_drivers; $add++) { ?>
																	<option value="<?= $add ?>"><?= $add ?> <?= EcalypseRental::t('additional driver') ?></option>
																<?php } ?>
															</select>
														</div>
													</div>
												</div>
												<div class="columns-2 control-group">
													<div class="column">
														<br />
													</div>
												</div>
											</div>

											<?php for ($add = 1; $add <= $additional_driver->max_additional_drivers; $add++) { ?>

												<div class="form-size-100" id="additional_driver_<?= $add ?>">
													<div class="h4 additional"><?= EcalypseRental::t('Additional Driver') ?> #<?= $add ?></div>
													<div class="columns-2 control-group">
														<div class="column">
															<div class="control-field">
																<input type="text" class="control-input" name="drv[<?= $add ?>][first_name]" placeholder="<?= EcalypseRental::t('Enter First Name') ?>">
															</div>
														</div>
														<div class="column">
															<div class="control-field">
																<input type="text" class="control-input" name="drv[<?= $add ?>][last_name]" placeholder="<?= EcalypseRental::t('Last name') ?>">
															</div>
														</div>
													</div>

													<div class="columns-2 control-group">
														<div class="column">
															<div class="control-field">
																<input type="text" class="control-input" name="drv[<?= $add ?>][email]" placeholder="<?= EcalypseRental::t('Email address') ?>">
															</div>
														</div>
														<div class="column">
															<div class="control-field">
																<input type="text" class="control-input" name="drv[<?= $add ?>][phone]" placeholder="<?= EcalypseRental::t('Phone number') ?>">
															</div>
														</div>
													</div>

													<div class="columns-2 control-group">
														<div class="column column-wider">
															<div class="columns-2">
																<div class="column column-wider">
																	<div class="control-field">
																		<input type="text" class="control-input" name="drv[<?= $add ?>][street]" placeholder="<?= EcalypseRental::t('Enter Street') ?>">
																	</div>
																</div>
																<div class="column column-thiner">

																	<div class="control-field">
																		<input type="text" class="control-input" name="drv[<?= $add ?>][city]" placeholder="<?= EcalypseRental::t('City') ?>">
																	</div>
																</div>
															</div>
														</div>
														<div class="column column-thiner">
															<div class="columns-2">
																<div class="column column-thin">
																	<div class="control-field">
																		<input type="text" class="control-input" name="drv[<?= $add ?>][zip]" placeholder="<?= EcalypseRental::t('ZIP') ?>">
																	</div>
																</div>
																<div class="column column-wide">
																	<div class="control-field">
																		<select name="drv[<?= $add ?>][country]" style="width:133px;">
																			<option value=""><?= EcalypseRental::t('Country') ?></option>
																			<?php $countries = EcalypseRental::get_country_list(); ?>
																		<?php foreach ($countries as $key => $val) { ?>
																			<option value="<?= $key ?>"><?= $val ?></option>
																		<?php } ?>
																		</select>
																	</div>
																</div>
															</div>
														</div>
													</div>

													<div class="columns-3 control-group">
														<div class="column">
															<div class="control-field">
																<input type="text" class="control-input" name="drv[<?= $add ?>][license]" placeholder="<?= EcalypseRental::t('License number') ?>">
															</div>
														</div>
														<div class="column">
															<div class="control-field">
																<input type="text" class="control-input" name="drv[<?= $add ?>][id_card]" placeholder="<?= EcalypseRental::t('ID / Passport number') ?>">
																<br /><br />
															</div>
														</div>
													</div>
												</div>

											<?php } ?>

											<hr class="separate">
										</div>

									<?php } ?>

									<div class="row row-total">

										<p class="pull-right">
											<strong><?= EcalypseRental::t('Grand Total') ?></strong><br>
											<?php if (isset($vehicle->prices) && !empty($vehicle->prices)) { ?>
												<span class="additional xxlarge ecalypse_rental_total_amount"> - </span>
											<?php } else { ?>
												<span class="additional xxlarge<?php echo $call_price ? ' ecalypse_call_price' : '';?>"><?= $call_price ? EcalypseRental::t('Call for price') : EcalypseRental::t('Not available') ?></span>
											<?php } ?>
										</p>

									</div>
									<!-- .row -->

									<?php $available_payments = unserialize(get_option('ecalypse_rental_available_payments')); ?>
									<?php if ((isset($available_payments) && !empty($available_payments) && $available_payments['payment']['paypal'] == 'yes')) { ?>
										<?php if (isset($available_payments) && isset($available_payments['ecalypse-rental-paypal-security-deposit']) && isset($vehicle->prices)  && !empty($vehicle->prices) ) { ?>
											<div class="row row-total" id="ecalypse_rental_security_deposit_div">

												<p class="pull-right">
													<strong><?= EcalypseRental::t('Security deposit (due now)') ?></strong><br />
													<span><?= EcalypseRental::t('This amount is payable now to secure your booking.') ?></span><br />
													<?php if (isset($available_payments['ecalypse-rental-paypal-security-deposit-type']) && $available_payments['ecalypse-rental-paypal-security-deposit-type'] == 'amount') { ?>
														<span class="additional xxlarge ecalypse_rental_security_deposit" data-deposit="<?php echo $available_payments['ecalypse-rental-paypal-security-deposit'];?>" data-deposit-amount="<?php echo $available_payments['ecalypse-rental-paypal-security-deposit-amount'];?>" data-deposit-type="<?php echo $available_payments['ecalypse-rental-paypal-security-deposit-type'];?>" data-deposit-round="<?php echo $available_payments['ecalypse-rental-paypal-security-deposit-round'];?>"> - </span>
													<?php } else { ?>
														<span class="additional xxlarge ecalypse_rental_security_deposit" data-deposit="<?php echo $available_payments['ecalypse-rental-paypal-security-deposit'];?>" data-deposit-round="<?php echo $available_payments['ecalypse-rental-paypal-security-deposit-round'];?>"> - </span>
													<?php } ?>
												</p>

											</div>
											<!-- .row -->
										<?php } ?>
									<?php } ?>
											
									<?php $payments_others = unserialize(get_option('ecalypse_rental_available_payments_others')); ?>
									<?php if (isset($payments_others) && isset($payments_others['eway']) && isset($payments_others['eway']['security-deposit']) && (float)$payments_others['eway']['security-deposit'] > 0 && isset($vehicle->prices)  && !empty($vehicle->prices)) { ?>
											<div class="row row-total" id="ecalypse_rental_security_deposit_eway_div">
												<p class="pull-right">
													<strong><?= EcalypseRental::t('Security deposit (due now)') ?></strong><br />
													<span><?= EcalypseRental::t('This amount is payable now to secure your booking.') ?></span><br />
													<span class="additional xxlarge ecalypse_rental_security_deposit_eway" data-deposit="<?php echo (float)$payments_others['eway']['security-deposit'];?>" data-deposit-round="<?php echo $payments_others['eway']['security-deposit-round'];?>"> - </span>
												</p>
											</div>
											<!-- .row -->
									<?php } ?>
											
									<?php if (isset($payments_others) && isset($payments_others['mercadopago']) && isset($payments_others['mercadopago']['security-deposit']) && (float)$payments_others['mercadopago']['security-deposit'] > 0 && isset($vehicle->prices)  && !empty($vehicle->prices)) { ?>
											<div class="row row-total" id="ecalypse_rental_security_deposit_mercadopago_div">
												<p class="pull-right">
													<strong><?= EcalypseRental::t('Security deposit (due now)') ?></strong><br />
													<span><?= EcalypseRental::t('This amount is payable now to secure your booking.') ?></span><br />
													<span class="additional xxlarge ecalypse_rental_security_deposit_mercadopago" data-deposit="<?php echo (float)$payments_others['mercadopago']['security-deposit'];?>" data-deposit-round="<?php echo $payments_others['mercadopago']['security-deposit-round'];?>"> - </span>
												</p>
											</div>
											<!-- .row -->
									<?php } ?>
											
									<?php do_action( 'ecalypse_rental_service_book_security_deposit', $vehicle); ?>

									<hr class="separate">

									<div class="control-group control-submit">
										<div class="control-field">

											<div class="pull-left">

													<label class="custom-block">
														<input type="checkbox" name="terms" value="1">
														<a href="?page=ecalypse-rental&amp;terms=1" target="_blank" class="show_terms" title="<?= EcalypseRental::t('Show Terms and conditions in new window.') ?>"><?= EcalypseRental::t('I agree to the Terms and conditions') ?></a>
													</label>
													<label class="custom-block">
														<input type="checkbox" name="newsletter" value="1">
														<?= EcalypseRental::t('I agree to receive newsletter') ?>
													</label>

												</div>
												<!-- .before-control -->

												<div class="pull-right" class="w50p">

												<?php foreach ($_GET as $key => $val) { ?>
													<input type="hidden" name="<?= htmlspecialchars($key) ?>" value="<?= htmlspecialchars($val) ?>">
												<?php } ?>

												<?php $paypal = get_option('ecalypse_rental_paypal'); ?>
												<?php $isPayPalPayment = false; ?>
												<?php $require_payment = get_option('ecalypse_rental_require_payment'); ?>
												<?php
													$is_other_payment_available = false;
													if ($payments_others && is_array($payments_others)) {
														foreach ($payments_others as $other_payment_name => $payment_info) { 
															if (isset($payment_info['enabled']) && $payment_info['enabled'] == 'yes') {
																$is_other_payment_available = true;
															}
														}
													}
												?>
												<?php if ($is_other_payment_available || (isset($available_payments) && !empty($available_payments) &&
																	($available_payments['payment']['cash'] == 'yes' ||
																	$available_payments['payment']['cc'] == 'yes' ||
																	$available_payments['payment']['paypal'] == 'yes' || 
																	$available_payments['payment']['bank'] == 'yes'))) { ?>
													
													<select name="payment_option" class="form-control" id="ecalypse_rental_payment_options_select">
														<option value=""><?= EcalypseRental::t('Please, select payment method') ?></option>
														<?php if (isset($available_payments['payment']['cash']) && $available_payments['payment']['cash'] == 'yes') { ?>
															<option value="cash"><?= EcalypseRental::t('Pay by cash upon pick up') ?></option>
														<?php } ?>
														<?php if (isset($available_payments['payment']['cc']) && $available_payments['payment']['cc'] == 'yes') { ?>
															<option value="cc"><?= EcalypseRental::t('Pay by credit card upon pick up') ?></option>
														<?php } ?>
														<?php if (isset($available_payments['payment']['paypal']) && $available_payments['payment']['paypal'] == 'yes') { ?>
															<option data-online="1" value="paypal"><?= EcalypseRental::t('PayPal payment') ?></option>
														<?php } ?>
														
														<?php if ($payments_others && is_array($payments_others)) { ?>
															<?php foreach ($payments_others as $other_payment_name => $payment_info) { ?>
																<?php if (isset($payment_info['enabled']) && $payment_info['enabled'] == 'yes') { ?>
																	<option data-online="1" value="<?php echo $other_payment_name;?>"><?= EcalypseRental::t('Pay by credit card') ?></option>
																<?php } ?>
															<?php } ?>
														<?php  } ?>
														<?php if (isset($available_payments['payment']['bank']) && $available_payments['payment']['bank'] == 'yes') { ?>
															<option value="bank"><?= EcalypseRental::t('Bank transfer payment') ?></option>
														<?php } ?>
													</select>
													<br><br>
													
													<?php do_action( 'ecalypse_rental_service_book_include_after_payment_selector'); ?>
													
													<?php if (isset($available_payments['ecalypse_rental_online_payment_discount']) && $available_payments['ecalypse_rental_online_payment_discount'] > 0) { ?>
														<p><?= str_replace('[discount percentage]%', $available_payments['ecalypse_rental_online_payment_discount'].'%', EcalypseRental::t('Get a [discount percentage]% discount if you pay online')) ?></p>
													<?php } ?>

													<div class="payment_options_info payment_info_cash">
														<h4><?= EcalypseRental::t('Please have total rental sum ready when picking up your car.') ?></h4>
													</div>
													<div class="payment_options_info payment_info_cc">
														<h4><?= EcalypseRental::t('We support all major credit cards. Please have your credit card on you when picking up your car.') ?></h4>
														<p class="payments">
															<span class="sprite-payment-visa"></span>
															<span class="sprite-payment-mastercard"></span>
															<span class="sprite-payment-amex"></span>
															<span class="sprite-payment-discover"></span>
														</p>
													</div>
													<div class="payment_options_info payment_info_paypal"></div>
													<div class="payment_options_info payment_info_bank"></div>

													<br>

													<input type="hidden" name="payment_selected_option" value="">
													<input type="hidden" name="paypal" value="">
													<input type="hidden" name="total_rental" value="" class="ecalypse_rental_total_rental">
													<input type="hidden" name="currency_code" value="<?= $vehicle->prices['currency'] ?>" class="ecalypse_rental_currency_code">
													<?php $disclaimer = get_option('ecalypse_rental_disclaimer');?>
													<?php if ($disclaimer) { ?>
														<?php $disclaimer = unserialize($disclaimer); ?>
														<?php $lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB'); ?>
														<?php $lang = strtolower(end(explode('_', $lang))); ?>
														<?php if (isset($disclaimer[$lang]) && !empty($disclaimer[$lang])) { ?>
															<div class="disclaimer">
															<?php echo $disclaimer[$lang];?>
															</div>
														<?php } ?>
													<?php } ?>
													<input type="submit" name="confirm_reservation" value="<?= EcalypseRental::t('Confirm Reservation') ?>" class="btn btn-primary">

												<?php } else { ?>
													<?php if ($require_payment == 'yes' && !empty($paypal)) { ?>
														<?php $isPayPalPayment = true; ?>
														<input type="hidden" name="paypal" value="1">
														<input type="hidden" name="total_rental" value="" class="ecalypse_rental_total_rental">
														<input type="hidden" name="currency_code" value="<?= $vehicle->prices['currency'] ?>" class="ecalypse_rental_currency_code">
														<input type="submit" id="input_submit_reservation" name="confirm_reservation" value="<?= EcalypseRental::t('Pay via PayPal') ?>" class="btn btn-primary pull-right">
													<?php } else { ?>
														<input type="submit" id="input_submit_reservation" name="confirm_reservation" value="<?= EcalypseRental::t('Confirm Reservation') ?>" class="btn btn-primary pull-right">
													<?php } ?>
												<?php } ?>

												<ul id="ecalypse_rental_confirm_errors" style="margin:5em 2em 1em 2em;list-style-type:circle;color:tomato;"></ul>

											</div>
											<!-- .pull-right -->

										</div>
										<!-- .control-field -->
									</div>
									<!-- .control-group -->
								<?php } else { ?>
									<!-- Car is not available -->
									<hr class="separate">
									<p><?= EcalypseRental::t('This car is not available. Please <a href="/contact-us">contact us</a>.') ?></p>
								<?php } ?>
							</div>
							<!-- .box -->
							
							<?php } else { ?>
								<p><?= EcalypseRental::t('Sorry, we did not find the vehicle in the database.') ?></p>
							<?php } ?>
							
						</div>
						<!-- .bordered-content -->
						
					</fieldset>
				</form>
	
			</div>
			<!-- .column -->

		</div>
		<!-- .columns-2 -->
		
	</div>
	<!-- .container -->
	
</section>
<!-- .content -->	
</div>
<script type="text/javascript">
	
	jQuery(document).ready(function() {
		var global_cc_before = '<?= $vehicle->prices['cc_before'] ?>';
		var global_cc_after= '<?= $vehicle->prices['cc_after'] ?>';
		var days_before_rental = <?php echo (int)floor((strtotime($_GET['fd']) - strtotime(date('Y-m-d'))) / 86400);?>;
		var payment_full_days = <?php echo isset($available_payments['ecalypse_rental_online_payment_full_days']) ? (int)$available_payments['ecalypse_rental_online_payment_full_days'] : 0;?>;
		
		<?php if (isset($available_payments['ecalypse_rental_online_payment_discount']) && $available_payments['ecalypse_rental_online_payment_discount'] > 0) { ?>
			jQuery('#ecalypse_rental_payment_options_select').change(function(){
				jQuery('#ecalypse_rental_online_payment_discount').remove();
				ecalypse_rental_calculate_total_amount();
				if (jQuery(this).find('option:selected').attr('data-online') == 1) {
					discount = -1 * (total_value_float * (<?php echo $available_payments['ecalypse_rental_online_payment_discount'];?>/100));
					jQuery('#ecalypse_rental_summary_charges').before('<div id="ecalypse_rental_online_payment_discount" class="row row-boxed"><?php echo EcalypseRental::t('Online payment discount');?><span class="pull-right ecalypse_rental_total_price" data-value="'+ parseFloat(discount).toFixed(2) +'" data-value-in="1">' + global_cc_before + '' + parseFloat(discount).toFixed(2) + '' + global_cc_after + '</span></div>');
					ecalypse_rental_calculate_total_amount();
				}
			});
		<?php } ?>
		
		if (jQuery('#ecalypse_rental_security_deposit_div').length) {
			if (jQuery('#ecalypse_rental_payment_options_select').val() == 'paypal') {
				jQuery('#ecalypse_rental_security_deposit_div').show();
			} else {
				jQuery('#ecalypse_rental_security_deposit_div').hide();
			}
			if (jQuery('#ecalypse_rental_payment_options_select').length) {
				jQuery('#ecalypse_rental_payment_options_select').change(function(){
					if (jQuery(this).val() == 'paypal') {
						jQuery('#ecalypse_rental_security_deposit_div').show();
					} else {
						jQuery('#ecalypse_rental_security_deposit_div').hide();
					}
				});
			}
		}
		
		if (jQuery('#ecalypse_rental_security_deposit_eway_div').length) {
			if (jQuery('#ecalypse_rental_payment_options_select').val() == 'eway') {
				jQuery('#ecalypse_rental_security_deposit_eway_div').show();
			} else {
				jQuery('#ecalypse_rental_security_deposit_eway_div').hide();
			}
			if (jQuery('#ecalypse_rental_payment_options_select').length) {
				jQuery('#ecalypse_rental_payment_options_select').change(function(){
					if (jQuery(this).val() == 'eway') {
						jQuery('#ecalypse_rental_security_deposit_eway_div').show();
					} else {
						jQuery('#ecalypse_rental_security_deposit_eway_div').hide();
					}
				});
			}
		}
		
		if (jQuery('#ecalypse_rental_security_deposit_mercadopago_div').length) {
			if (jQuery('#ecalypse_rental_payment_options_select').val() == 'mercadopago') {
				jQuery('#ecalypse_rental_security_deposit_mercadopago_div').show();
			} else {
				jQuery('#ecalypse_rental_security_deposit_mercadopago_div').hide();
			}
			if (jQuery('#ecalypse_rental_payment_options_select').length) {
				jQuery('#ecalypse_rental_payment_options_select').change(function(){
					if (jQuery(this).val() == 'mercadopago') {
						jQuery('#ecalypse_rental_security_deposit_mercadopago_div').show();
					} else {
						jQuery('#ecalypse_rental_security_deposit_mercadopago_div').hide();
					}
				});
			}
		}
		<?php do_action( 'ecalypse_rental_service_book_js'); ?>
		
		jQuery(document).on('click', '.show_terms', function() {
			jQuery('<div>Loading...</div>')
					.load(jQuery(this).attr('href'))
					.dialog({
						autoOpen: true,
						title: '<?= EcalypseRental::t('Terms and conditions') ?>',
						width: 700,
						height: 600,
						resizable: true,
						 create: function( event, ui ) {
							// Set maxWidth
							jQuery(this).parent().css("max-width", "90%");
						  }
					});
			return false;
		});
		
		jQuery(document).on('click', '.extra-desc', function(e) {
			e.preventDefault();
			jQuery('<div>'+jQuery(this).data('desc')+'</div>').dialog({
						autoOpen: true,
						title: jQuery(this).data('title'),
						width: 600,
						height: 300,
						resizable: true,
						 create: function( event, ui ) {
							// Set maxWidth
							jQuery(this).parent().css("max-width", "90%");
						  }
					});
			return false;
		});
		
		var total_value_float;
		
		jQuery('.add_car').click(function(e){
			e.preventDefault();
			
			element = jQuery(this);
			var name = element.attr('data-name');
			var id_additional = element.data('id');
			var datavalue = element.attr('data-value');
			var taxtotal = element.attr('data-taxtotal-value');
			var taxtotal_2 = element.attr('data-taxtotal-2-value');
			var taxtotal_3 = element.attr('data-taxtotal-3-value');
			var tax_value = element.attr('data-tax-value');
			var taxname = name + ' &ndash; ' + tax_value + '% <?= EcalypseRental::t('Value Added Tax') ?>';
			var currency_before = element.attr('data-currency-before');
			var currency_after = element.attr('data-currency-after');
			
			if (jQuery('.vat_item:first').length) {
				jquery_element = '.vat_item:first';
			} else {
				jquery_element = '#ecalypse_rental_summary_charges p';
			}
			
			jQuery(jquery_element).parent().before('<div id="ecalypse_rental_additional_charge_row_' + id_additional + '" data-id="' + id_additional + '" class="row row-boxed ecalypse_rental_total_price" data-value-in="1" data-value="' + parseFloat(datavalue).toFixed(2) + '"><input type="hidden" name="additional[]" value="'+id_additional+'">' + name + '<span class="pull-right">' + currency_before + '' + parseFloat(datavalue).toFixed(2) + '' + currency_after + '<a href="#" class="remove-additional">&times;</a></span></div>');
			if (taxtotal > 0) {
				total_vat = parseFloat(jQuery('.vat_item').attr('data-value'));
				total_vat += parseFloat(taxtotal);
				jQuery('.vat_item').attr('data-value', total_vat);
				jQuery('.vat_item span').text(parseFloat(total_vat).toFixed(2));
			}
			if (taxtotal_2 > 0) {
				total_vat_2 = parseFloat(jQuery('.vat_item_2').attr('data-value'));
				total_vat_2 += parseFloat(taxtotal_2);
				jQuery('.vat_item_2').attr('data-value', total_vat_2);
				jQuery('.vat_item_2 span').text(parseFloat(total_vat_2).toFixed(2));
			}
			if (taxtotal_3 > 0) {
				total_vat_3 = parseFloat(jQuery('.vat_item_3').attr('data-value'));
				total_vat_3 += parseFloat(taxtotal_3);
				jQuery('.vat_item_3').attr('data-value', total_vat_3);
				jQuery('.vat_item_3 span').text(parseFloat(total_vat_3).toFixed(2));
			}

			element.data('used', element.data('used') + 1);
			
			if (element.data('used') >= element.data('count')) {
				element.parent().addClass('na-u');
			}
			
			ecalypse_rental_calculate_total_amount();
			
		});
		
		jQuery(document).on('click', '.remove-additional', function(e) {
			e.preventDefault();
			var additional_id = jQuery(this).parent().parent().data('id');
			element = jQuery('.add_car[data-id=' + additional_id + ']');
			var name = element.attr('data-name');
			var id_additional = element.data('id');
			var datavalue = element.attr('data-value');
			var taxtotal = element.attr('data-taxtotal-value');
			var taxtotal_2 = element.attr('data-taxtotal-2-value');
			var taxtotal_3 = element.attr('data-taxtotal-3-value');
			var tax_value = element.attr('data-tax-value');
			var taxname = name + ' &ndash; ' + tax_value + '% <?= EcalypseRental::t('Value Added Tax') ?>';
			var currency_before = element.attr('data-currency-before');
			var currency_after = element.attr('data-currency-after');
			
			jQuery(this).parent().parent().remove();
			
			if (taxtotal > 0) {
				total_vat = parseFloat(jQuery('.vat_item').attr('data-value'));
				total_vat -= parseFloat(taxtotal);
				jQuery('.vat_item').attr('data-value', total_vat);
				jQuery('.vat_item span').text(parseFloat(total_vat).toFixed(2));
			}
			if (taxtotal_2 > 0) {
				total_vat_2 = parseFloat(jQuery('.vat_item_2').attr('data-value'));
				total_vat_2 -= parseFloat(taxtotal_2);
				jQuery('.vat_item_2').attr('data-value', total_vat_2);
				jQuery('.vat_item_2 span').text(parseFloat(total_vat_2).toFixed(2));
			}
			if (taxtotal_3 > 0) {
				total_vat_3 = parseFloat(jQuery('.vat_item_3').attr('data-value'));
				total_vat_3 -= parseFloat(taxtotal_3);
				jQuery('.vat_item_3').attr('data-value', total_vat_3);
				jQuery('.vat_item_3 span').text(parseFloat(total_vat_3).toFixed(2));
			}
			
			element.data('used', parseInt(element.data('used') - 1));
			
			if (element.data('used') >= element.data('count')) {
				element.parent().addClass('na-u');
			} else {
				element.parent().removeClass('na-u');
			}
			
			ecalypse_rental_calculate_total_amount();
		});	
	
		function ecalypse_rental_calculate_total_amount() {
			
			var total_value = 0;
			var cc_before = '$';
			var cc_after = '';
			
			cc_before = jQuery('.ecalypse_rental_currency').attr('data-currency-before');
			cc_after = jQuery('.ecalypse_rental_currency').attr('data-currency-after');
			
			// recalculate branch specific taxes
			if (enter_location > 0 && typeof branches[enter_location] !== 'undefined') {
				if (typeof branches[enter_location]['branch_tax'] !== 'undefined' && Object.size(branches[enter_location]['branch_tax']) > 0) {
					var base_price = 0;
					jQuery('.ecalypse_rental_total_price:not(.ecalypse_rental_tax_item)').each(function(i) {
						if (jQuery(this).attr('data-value-in') == 1) {
							var thisValue = jQuery(this).attr('data-value').replace(/,/g, '');
							base_price += parseFloat(thisValue);
						}
					});
					jQuery.each(branches[enter_location]['branch_tax'], function(k,v){
						branch_tax = base_price * (v['tax']/100);
						jQuery('.branch_tax_'+k).attr('data-value', branch_tax);
						jQuery('.branch_tax_'+k).text(parseFloat(branch_tax).toFixed(2));
					})
				}
			}
			
			jQuery('.ecalypse_rental_total_price').each(function(i) {
				if (i == 0 && (jQuery(this).attr('data-currency-before') != '' || jQuery(this).attr('data-currency-after') != '')) {
					cc_before = jQuery(this).attr('data-currency-before');
					cc_after = jQuery(this).attr('data-currency-after');
				}
				if (jQuery(this).attr('data-value-in') == 1) {
					var thisValue = jQuery(this).attr('data-value').replace(/,/g, '');
					total_value += parseFloat(thisValue);
					if (parseFloat(jQuery(this).attr('data-taxtotal-value')) > 0 && !jQuery(this).hasClass('ecalypse_rental_extras')) {
						total_value += parseFloat(jQuery(this).attr('data-taxtotal-value'));
					}
				}
			});
			
			total_value_float = parseFloat(total_value).toFixed(2);
			total_value_string = total_value_float.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			jQuery('.ecalypse_rental_total_amount').html(cc_before + '' + total_value_string + '' + cc_after);
			
			if (jQuery('.ecalypse_rental_security_deposit').length) {
				var deposit_value = total_value_float;
				if (payment_full_days == 0 || payment_full_days < days_before_rental) {
					if (jQuery('.ecalypse_rental_security_deposit').attr('data-deposit-type') == 'amount') {
						deposit_value = jQuery('.ecalypse_rental_security_deposit').attr('data-deposit-amount');
					} else {
						deposit_value = (jQuery('.ecalypse_rental_security_deposit').attr('data-deposit')/100) * deposit_value;
						if (jQuery('.ecalypse_rental_security_deposit').attr('data-deposit-round') == 'up') {
							deposit_value = Math.ceil(deposit_value);
						} else if (jQuery('.ecalypse_rental_security_deposit').attr('data-deposit-round') == 'down') {
							deposit_value = Math.floor(deposit_value);
						} else {
							deposit_value = deposit_value.toFixed(2);
						}
					}
				}
				deposit_value = deposit_value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
				jQuery('.ecalypse_rental_security_deposit').html(cc_before + '' + deposit_value + '' + cc_after);
			}
			
			if (jQuery('.ecalypse_rental_security_deposit_eway').length) {
				var deposit_value = total_value_float;
				deposit_value = (jQuery('.ecalypse_rental_security_deposit_eway').attr('data-deposit')/100) * deposit_value;
				if (jQuery('.ecalypse_rental_security_deposit_eway').attr('data-deposit-round') == 'up') {
					deposit_value = Math.ceil(deposit_value);
				} else if (jQuery('.ecalypse_rental_security_deposit_eway').attr('data-deposit-round') == 'down') {
					deposit_value = Math.floor(deposit_value);
				} else {
					deposit_value = deposit_value.toFixed(2);
				}
				deposit_value = deposit_value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
				jQuery('.ecalypse_rental_security_deposit_eway').html(cc_before + '' + deposit_value + '' + cc_after);
			}
			
			if (jQuery('.ecalypse_rental_security_deposit_mercadopago').length) {
				var deposit_value = total_value_float;
				deposit_value = (jQuery('.ecalypse_rental_security_deposit_mercadopago').attr('data-deposit')/100) * deposit_value;
				if (jQuery('.ecalypse_rental_security_deposit_mercadopago').attr('data-deposit-round') == 'up') {
					deposit_value = Math.ceil(deposit_value);
				} else if (jQuery('.ecalypse_rental_security_deposit_mercadopago').attr('data-deposit-round') == 'down') {
					deposit_value = Math.floor(deposit_value);
				} else {
					deposit_value = deposit_value.toFixed(2);
				}
				deposit_value = deposit_value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
				jQuery('.ecalypse_rental_security_deposit_mercadopago').html(cc_before + '' + deposit_value + '' + cc_after);
			}
			
			<?php do_action( 'ecalypse_rental_service_book_js_calculate_total'); ?>
					
			jQuery('.ecalypse_rental_total_rental').val(total_value);
			
			// hide null taxes
			if (parseFloat(jQuery('.vat_item').attr('data-value')) > 0) {
				jQuery('.vat_item').parent().show();
			} else {
				jQuery('.vat_item').parent().hide();
			}
			
			if (parseFloat(jQuery('.vat_item_2').attr('data-value')) > 0) {
				jQuery('.vat_item_2').parent().show();
			} else {
				jQuery('.vat_item_2').parent().hide();
			}
			
			if (parseFloat(jQuery('.vat_item_3').attr('data-value')) > 0) {
				jQuery('.vat_item_3').parent().show();
			} else {
				jQuery('.vat_item_3').parent().hide();
			}

			
		}
		
		
		ecalypse_rental_calculate_total_amount(); // Init
		
		jQuery('.ecalypse_rental_extras').on('change', add_extra);
		
		function add_extra(event, element) {
			if (typeof(element) == 'undefined') {
				element = jQuery(this);
			}

			var name = element.attr('data-name');
			var id_extras = element.val();
			var datavalue = element.attr('data-value');
			var taxtotal = element.attr('data-taxtotal-value');
			var taxtotal_2 = element.attr('data-taxtotal-2-value');
			var taxtotal_3 = element.attr('data-taxtotal-3-value');
			var tax_value = element.attr('data-tax-value');
			var taxname = name + ' &ndash; ' + tax_value + '% <?= EcalypseRental::t('Value Added Tax') ?>';
			var currency_before = element.attr('data-currency-before');
			var currency_after = element.attr('data-currency-after');
			
			if (element.is(':checked')) {
				if (jQuery('.vat_item:first').length) {
					jquery_element = '.vat_item:first';
				} else {
					jquery_element = '#ecalypse_rental_summary_charges p';
				}
				jQuery(jquery_element).parent().before('<div id="ecalypse_rental_extras_charge_row_' + id_extras + '" class="row row-boxed ecalypse_rental_total_price" data-value-in="1" data-value="' + parseFloat(datavalue).toFixed(2) + '">' + name + '<span class="pull-right">' + currency_before + '' + parseFloat(datavalue).toFixed(2) + '' + currency_after + '</span></div>');
				if (taxtotal > 0) {
					total_vat = parseFloat(jQuery('.vat_item').attr('data-value'));
					total_vat += parseFloat(taxtotal);
					jQuery('.vat_item').attr('data-value', total_vat);
					jQuery('.vat_item span').text(parseFloat(total_vat).toFixed(2));
					//jQuery('#ecalypse_rental_summary_charges').before('<div id="ecalypse_rental_extras_charge_row_tax_' + id_extras + '" class="row row-boxed">' + taxname + '<span class="pull-right">' + currency_before + '' + parseFloat(taxtotal).toFixed(2) + '' + currency_after + '</span></div>');
				}
				if (taxtotal_2 > 0) {
					total_vat_2 = parseFloat(jQuery('.vat_item_2').attr('data-value'));
					total_vat_2 += parseFloat(taxtotal_2);
					jQuery('.vat_item_2').attr('data-value', total_vat_2);
					jQuery('.vat_item_2 span').text(parseFloat(total_vat_2).toFixed(2));
					//jQuery('#ecalypse_rental_summary_charges').before('<div id="ecalypse_rental_extras_charge_row_tax_' + id_extras + '" class="row row-boxed">' + taxname + '<span class="pull-right">' + currency_before + '' + parseFloat(taxtotal).toFixed(2) + '' + currency_after + '</span></div>');
				}
				if (taxtotal_3 > 0) {
					total_vat_3 = parseFloat(jQuery('.vat_item_3').attr('data-value'));
					total_vat_3 += parseFloat(taxtotal_3);
					jQuery('.vat_item_3').attr('data-value', total_vat_3);
					jQuery('.vat_item_3 span').text(parseFloat(total_vat_3).toFixed(2));
					//jQuery('#ecalypse_rental_summary_charges').before('<div id="ecalypse_rental_extras_charge_row_tax_' + id_extras + '" class="row row-boxed">' + taxname + '<span class="pull-right">' + currency_before + '' + parseFloat(taxtotal).toFixed(2) + '' + currency_after + '</span></div>');
				}

				element.attr('data-value-in', 1);
			} else {
				jQuery('#ecalypse_rental_extras_charge_row_' + id_extras).remove();
				if (taxtotal > 0) {
					total_vat = parseFloat(jQuery('.vat_item').attr('data-value'));
					total_vat -= parseFloat(taxtotal);
					jQuery('.vat_item').attr('data-value', total_vat);
					jQuery('.vat_item span').text(parseFloat(total_vat).toFixed(2));
				}
				if (taxtotal_2 > 0) {
					total_vat_2 = parseFloat(jQuery('.vat_item_2').attr('data-value'));
					total_vat_2 -= parseFloat(taxtotal_2);
					jQuery('.vat_item_2').attr('data-value', total_vat_2);
					jQuery('.vat_item_2 span').text(parseFloat(total_vat_2).toFixed(2));
				}
				if (taxtotal_3 > 0) {
					total_vat_3 = parseFloat(jQuery('.vat_item_3').attr('data-value'));
					total_vat_3 -= parseFloat(taxtotal_3);
					jQuery('.vat_item_3').attr('data-value', total_vat_3);
					jQuery('.vat_item_3 span').text(parseFloat(total_vat_3).toFixed(2));
				}

				//jQuery('#ecalypse_rental_extras_charge_row_tax_' + id_extras).remove();
				element.attr('data-value-in', 0);
			} 
			
			ecalypse_rental_calculate_total_amount();
			
		}
		
		jQuery.each(jQuery('.ecalypse_rental_extras:checked'), function (){
			add_extra(false, jQuery(this));
		});
		
		jQuery('.additional_driver_price').on('change', function() {
			if (jQuery(this).is(':checked')) {
				jQuery('.additional_driver_box').show();
			} else {
				jQuery('.additional_driver_box').hide();
				var id_extras = jQuery('.additional_driver_price').val();
				jQuery('#ecalypse_rental_extras_charge_row_' + id_extras).remove();
				//jQuery('#ecalypse_rental_extras_charge_row_tax_' + id_extras).remove();
				if (addition_drivers_vat > 0) {
					total_vat = parseFloat(jQuery('.vat_item').attr('data-value'));
					total_vat -= addition_drivers_vat;
					addition_drivers_vat = 0;
					jQuery('.vat_item').attr('data-value', total_vat);
					jQuery('.vat_item span').text(parseFloat(total_vat).toFixed(2));
				}
				if (addition_drivers_vat_2 > 0) {
					total_vat_2 = parseFloat(jQuery('.vat_item_2').attr('data-value'));
					total_vat_2 -= addition_drivers_vat_2;
					addition_drivers_vat_2 = 0;
					jQuery('.vat_item_2').attr('data-value', total_vat_2);
					jQuery('.vat_item_2 span').text(parseFloat(total_vat_2).toFixed(2));
				}
				if (addition_drivers_vat_3 > 0) {
					total_vat_3 = parseFloat(jQuery('.vat_item_3').attr('data-value'));
					total_vat_3 -= addition_drivers_vat_3;
					addition_drivers_vat_3 = 0;
					jQuery('.vat_item_3').attr('data-value', total_vat_3);
					jQuery('.vat_item_3 span').text(parseFloat(total_vat_3).toFixed(2));
				}

				ecalypse_rental_calculate_total_amount();
			}
			jQuery('#additional_drivers_change').val('0');
			ecalypse_rental_hide_all_additional_driver();
		});
	
		jQuery('.additional_driver_box').hide();
		
		var max_additional_drivers = '<?= (!empty($additional_driver) ? $additional_driver->max_additional_drivers : 0) ?>';
		
		function ecalypse_rental_hide_all_additional_driver() {
			for (x = 1; x <= max_additional_drivers; x++) {
				jQuery('#additional_driver_' + x).hide();
			}
		}
		
		ecalypse_rental_hide_all_additional_driver();
		var addition_drivers_vat = 0;
		var addition_drivers_vat_2 = 0;
		var addition_drivers_vat_3 = 0;

		
		jQuery('#additional_drivers_change').on('change', function() {
			ecalypse_rental_hide_all_additional_driver();
			for (x = 1; x <= jQuery(this).val(); x++) {
				jQuery('#additional_driver_' + x).show();
			}
			
			var ad_price = jQuery('.additional_driver_price');
			var name = jQuery(this).val() + 'x ' + ad_price.attr('data-name');
			var id_extras = ad_price.val();
			var datavalue = parseFloat(ad_price.attr('data-value')) * parseInt(jQuery(this).val());
			var taxtotal = parseFloat(ad_price.attr('data-taxtotal-value')) * parseInt(jQuery(this).val());
			var taxtotal_2 = parseFloat(ad_price.attr('data-taxtotal-2-value')) * parseInt(jQuery(this).val());
			var taxtotal_3 = parseFloat(ad_price.attr('data-taxtotal-3-value')) * parseInt(jQuery(this).val());
			var taxname = name + ' &ndash; ' + ad_price.attr('data-tax-value') + '% <?= EcalypseRental::t('Value Added Tax') ?>';
			var currency_before = ad_price.attr('data-currency-before');
			var currency_after = ad_price.attr('data-currency-after');
			
			jQuery('#ecalypse_rental_extras_charge_row_' + id_extras).remove();
			jQuery('#ecalypse_rental_extras_charge_row_tax_' + id_extras).remove();
			if (jQuery(this).val() > 0) {
				if (jQuery('.vat_item:first').length) {
					jquery_element = '.vat_item:first';
				} else {
					jquery_element = '#ecalypse_rental_summary_charges p';
				}
				jQuery(jquery_element).parent().before('<div id="ecalypse_rental_extras_charge_row_' + id_extras + '" class="row row-boxed ecalypse_rental_total_price" data-value-in="1" data-value="' + parseFloat(datavalue).toFixed(2) + '">' + name + '<span class="pull-right">' + currency_before + '' + parseFloat(datavalue).toFixed(2) + '' + currency_after + '</span></div>');
				if (taxtotal > 0) {
					total_vat = parseFloat(jQuery('.vat_item').attr('data-value'));
					total_vat += parseFloat(taxtotal) - addition_drivers_vat;
					addition_drivers_vat = taxtotal;
					jQuery('.vat_item').attr('data-value', total_vat);
					jQuery('.vat_item span').text(parseFloat(total_vat).toFixed(2));
					//jQuery('#ecalypse_rental_summary_charges').before('<div id="ecalypse_rental_extras_charge_row_tax_' + id_extras + '" class="row row-boxed ecalypse_rental_total_price" data-value-in="1" data-value="' + parseFloat(taxtotal).toFixed(2) + '">' + taxname + '<span class="pull-right">' + currency_before + '' + parseFloat(taxtotal).toFixed(2) + '' + currency_after + '</span></div>');
				}
				if (taxtotal_2 > 0) {
					total_vat_2 = parseFloat(jQuery('.vat_item_2').attr('data-value'));
					total_vat_2 += parseFloat(taxtotal_2) - addition_drivers_vat_2;
					addition_drivers_vat_2 = taxtotal_2;
					jQuery('.vat_item_2').attr('data-value', total_vat_2);
					jQuery('.vat_item_2 span').text(parseFloat(total_vat_2).toFixed(2));
					//jQuery('#ecalypse_rental_summary_charges').before('<div id="ecalypse_rental_extras_charge_row_tax_' + id_extras + '" class="row row-boxed ecalypse_rental_total_price" data-value-in="1" data-value="' + parseFloat(taxtotal).toFixed(2) + '">' + taxname + '<span class="pull-right">' + currency_before + '' + parseFloat(taxtotal).toFixed(2) + '' + currency_after + '</span></div>');
				}
				if (taxtotal_3 > 0) {
					total_vat_3 = parseFloat(jQuery('.vat_item_3').attr('data-value'));
					total_vat_3 += parseFloat(taxtotal_3) - addition_drivers_vat_3;
					addition_drivers_vat_3 = taxtotal_3;
					jQuery('.vat_item_3').attr('data-value', total_vat_3);
					jQuery('.vat_item_3 span').text(parseFloat(total_vat_3).toFixed(2));
					//jQuery('#ecalypse_rental_summary_charges').before('<div id="ecalypse_rental_extras_charge_row_tax_' + id_extras + '" class="row row-boxed ecalypse_rental_total_price" data-value-in="1" data-value="' + parseFloat(taxtotal).toFixed(2) + '">' + taxname + '<span class="pull-right">' + currency_before + '' + parseFloat(taxtotal).toFixed(2) + '' + currency_after + '</span></div>');
				}

			} else {
				jQuery('#ecalypse_rental_extras_charge_row_' + id_extras).remove();
				//jQuery('#ecalypse_rental_extras_charge_row_tax_' + id_extras).remove();
				if (addition_drivers_vat > 0) {
					total_vat = parseFloat(jQuery('.vat_item').attr('data-value'));
					total_vat -= addition_drivers_vat;
					addition_drivers_vat = 0;
					jQuery('.vat_item').attr('data-value', total_vat);
					jQuery('.vat_item span').text(parseFloat(total_vat).toFixed(2));
				}
				if (addition_drivers_vat_2 > 0) {
					total_vat_2 = parseFloat(jQuery('.vat_item_2').attr('data-value'));
					total_vat_2 -= addition_drivers_vat_2;
					addition_drivers_vat_2 = 0;
					jQuery('.vat_item_2').attr('data-value', total_vat_2);
					jQuery('.vat_item_2 span').text(parseFloat(total_vat_2).toFixed(2));
				}
				if (addition_drivers_vat_3 > 0) {
					total_vat_3 = parseFloat(jQuery('.vat_item_3').attr('data-value'));
					total_vat_3 -= addition_drivers_vat_3;
					addition_drivers_vat_3 = 0;
					jQuery('.vat_item_3').attr('data-value', total_vat_3);
					jQuery('.vat_item_3 span').text(parseFloat(total_vat_3).toFixed(2));
				}

			} 
			
			ecalypse_rental_calculate_total_amount();
				
		});
		
		
		// Payment options
		jQuery('.payment_options_info').hide();
		jQuery('[name=payment_option]').change(function() {
			jQuery('.payment_options_info').hide();
			jQuery('.payment_info_' + jQuery(this).val()).show();
			jQuery('[name=payment_selected_option]').val(jQuery(this).val());
			if (jQuery(this).val() == 'paypal') {
				jQuery('[name=paypal]').val('1');
			} else {
				jQuery('[name=paypal]').val('0');
			}
		});
		
		
	});
	
</script>
									
<script type="text/javascript">
	
	jQuery('#ecalypse_rental_confirm_reservation').on('submit', function(e) {
		var errors = [];
		
		if (jQuery('[name=first_name]').val() == '' ||
				jQuery('[name=last_name]').val() == '') {
			errors.push('<?= addslashes(EcalypseRental::t('Please, insert your full name.')) ?>');
		}
		
		if (jQuery('[name=phone]').val() == '') {
			errors.push('<?= addslashes(EcalypseRental::t('Please, insert your phone or mobile number to confirm reservation.')) ?>');
		}
		
		if (jQuery('[name=email]').val() == '') {
			errors.push('<?= addslashes(EcalypseRental::t('Please, insert your e-mail to confirm reservation.')) ?>');
		} else {
			r_email	= new RegExp("^([a-zA-Z0-9_.-]+@([a-zA-Z0-9_-]+\.)+[a-z]{2,4}){0,1}$");
			if (!r_email.test(jQuery('[name=email]').val())) {
				errors.push('<?= addslashes(EcalypseRental::t('Please, enter valid e-mail address.')) ?>');
			}
		}
		
		if (jQuery('[name=terms]').is(':checked') == false) {
			errors.push('<?= addslashes(EcalypseRental::t('Please, confirm you agree with Terms and conditions.')) ?>');
		}
		
		if (jQuery('[name=payment_option]').length > 0 && jQuery('[name=payment_option]').val() == '') {
			errors.push('<?= addslashes(EcalypseRental::t('Please, select payment method.')) ?>');
		}
		
		if (jQuery(this).hasClass('from-submitted')) {
			errors.push('<?= addslashes(EcalypseRental::t('Order have already been sent.')) ?>');
		}
		
		if (errors.length == 0) {
			<?php do_action( 'ecalypse_rental_service_book_js_submit_form'); ?>
			// prevent double action
			jQuery(this).addClass('from-submitted');
			//jQuery('input[name=confirm_reservation]').prop('disabled', true);
			return true;
		} else {
			jQuery('#ecalypse_rental_confirm_errors').html('<li>' + errors.join('</li><li>') + '</li>');
			return false;
		}
		
	});

</script>

<?php get_footer(); ?>