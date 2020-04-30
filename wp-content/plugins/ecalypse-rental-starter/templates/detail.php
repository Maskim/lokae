<?php
/**
 * Car detail
 *
 * @package WordPress
 * @subpackage EcalypseRental
 * @since EcalypseRental 3.0.0
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
get_header();
?>
<?php
$anylocation = get_option('ecalypse_rental_any_location_search');
if ($anylocation && $anylocation == 'yes') {
	$anylocation = true;
} else {
	$anylocation = false;
}
$time_pricing_type = get_option('ecalypse_rental_time_pricing_type');
?>

<div class="ecalypse-rental-custom-theme">
<section class="content ecalypse-rental-detail">
	<div class="container">


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
										<input type="hidden" name="order" value="name" id="ecalypse_rental_order_input">
										<input type="hidden" name="book_now" value="ok">
										<input type="hidden" name="id_car" value="<?= isset($vehicle) && !empty($vehicle) ? $vehicle->id_fleet : (int) $_GET['id_car'] ?>">
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


				<fieldset>

					<div class="bordered-content">

						<?php if (isset($vehicle) && !empty($vehicle)) { ?>
							<h2 class="detail_name"><?= $vehicle->name ?></h2>
							<div class="list-item-media">
								<?php if (!empty($vehicle->picture)) { ?>
									<img src="<?= $vehicle->picture ?>" alt="<?= $vehicle->name ?>" class="main_image">
										<?php $additional_pictures_count = 0; ?>
										<?php if (isset($vehicle->additional_pictures) && !empty($vehicle->additional_pictures)) { ?>
											<?php $vehicle->additional_pictures = unserialize($vehicle->additional_pictures); ?>
											<?php if (is_array($vehicle->additional_pictures) && count($vehicle->additional_pictures) > 0) { ?>
												<?php $additional_pictures_count = count($vehicle->additional_pictures); ?>
											<?php } ?>
										<?php } ?>
										<?php if ($additional_pictures_count > 0) { ?>
											<a href="<?= $vehicle->picture ?>" data-lightbox="fleet-<?= $vehicle->id_fleet ?>" class="btn btn-small btn-primary btn-book btn-absolute"><?= EcalypseRental::t('Show more pictures') ?> <strong>(<?php echo $additional_pictures_count; ?>)</strong></a>
										<?php } ?>
									
									<div class="hid-imgs">
										<?php if ($additional_pictures_count > 0) { ?>
											<?php foreach ($vehicle->additional_pictures as $adPicture) { ?>
												<a href="<?= $adPicture ?>" data-lightbox="fleet-<?= $vehicle->id_fleet ?>"></a>
											<?php } ?>
										<?php } ?>
									</div>
								<?php } ?>

							</div>
							<div class="box box-white box-inner">

								<div class="columns-2 break-lg">

									<div class="column">
										<?php $distance_metric = get_option('ecalypse_rental_distance_metric'); ?>

										<div class="column">
											<div class="icon-text-list">
												<?php if (isset($vehicle->ac) && (int) $vehicle->ac > 0) { ?>
													<?php if ($vehicle->ac == 2) { ?><?= EcalypseRental::t('No A/C') ?><?php } else { ?><?= EcalypseRental::t('A/C') ?><?php } ?><br />
												<?php } ?>
												<?php if (isset($vehicle->luggage) && !empty($vehicle->luggage)) { ?>
													<?= $vehicle->luggage ?>&times; <?= EcalypseRental::t('Luggage Quantity') ?><br />
												<?php } ?>
												<?php if (isset($vehicle->seats) && !empty($vehicle->seats)) { ?>
													<?= $vehicle->seats ?>&times; <?= EcalypseRental::t('Persons') ?><br />
												<?php } ?>
												<?php if (isset($vehicle->fuel) && !empty($vehicle->fuel)) { ?>
													<?php if ($vehicle->fuel == 1) { ?><?= EcalypseRental::t('Petrol') ?><?php } else { ?><?= EcalypseRental::t('Diesel') ?><?php } ?><br />
												<?php } ?>
												<?php if (isset($vehicle->consumption) && !empty($vehicle->consumption)) { ?>
													<?php $consumption = get_option('ecalypse_rental_consumption'); ?>
													<?php
													if (!$consumption || empty($consumption)) {
														$consumption = 'eu';
													}
													?>
													<abbr title="<?= EcalypseRental::t('Average Consumption') ?>"><?= $vehicle->consumption ?> <?= (($consumption == 'eu') ? 'l/100km' : 'MPG') ?></abbr><br />
												<?php } ?>

												<?php if (isset($vehicle->transmission) && !empty($vehicle->transmission)) { ?>
													<?= (($vehicle->transmission == 1) ? EcalypseRental::t('Transmission: Automatic') : EcalypseRental::t('Transmission: Manual')) ?><br />
												<?php } ?>
												<?php if (isset($vehicle->free_distance)) { ?>
													<?= EcalypseRental::t('Free distance') ?>: <?php if (isset($vehicle->free_distance_total)) { echo $vehicle->free_distance_total == 0 ? EcalypseRental::t('Unlimited') : $vehicle->free_distance_total.'&nbsp;'.$distance_metric; } elseif ($vehicle->free_distance > 0) { ?><?= (isset($vehicle->prices) ? (int)$vehicle->prices['diff_days'] : 1) * $vehicle->free_distance ?>&nbsp;<?= $distance_metric ?> <?= EcalypseRental::t('per day') ?><?php } else { ?><?= EcalypseRental::t('Unlimited') ?><?php } ?><br />
												<?php } ?>
												<?php if (isset($vehicle->deposit) && $vehicle->deposit != '' && $vehicle->deposit > 0) { ?>
													<?php
													$global_currency = get_option('ecalypse_rental_global_currency');
													$av_currencies = unserialize(get_option('ecalypse_rental_available_currencies'));
													$rate = 1;
													if (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_currency']) && isset($av_currencies[EcalypseRentalSession::$session['ecalypse_rental_currency']])) {
														$current_currency = EcalypseRentalSession::$session['ecalypse_rental_currency'];
													} else {
														$current_currency = $global_currency;
													}
													?>
													<?php
													if ($current_currency != $global_currency && isset($av_currencies[$current_currency])) {
														$rate = $av_currencies[$current_currency];
													}
													?>
													<?= EcalypseRental::t('Deposit') ?>: <?php if ($vehicle->deposit > 0) { ?><?= EcalypseRental::get_currency_symbol('before', $current_currency); ?><?= round(($vehicle->deposit / $rate), 2) ?>&nbsp;<?= EcalypseRental::get_currency_symbol('after', $current_currency); ?><?php } else { ?>0<?php } ?><br />
												<?php } ?>

												<?php $additional_parameters = unserialize($vehicle->additional_parameters); ?>
												<?php $lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB'); ?>
												<?php $lang = strtolower(end(explode('_', $lang))); ?>
												<?php if ($additional_parameters && isset($additional_parameters[$lang]) && !empty($additional_parameters[$lang])) { ?>
													<?php foreach ($additional_parameters[$lang] as $p) { ?>
														<?php
														if (!isset($p['name']) || trim($p['name']) == '') {
															continue;
														}
														?>
														<?php if (trim($p['value']) == '') { ?>
															<?php echo $p['name']; ?><br />
														<?php } else { ?>
															<strong><?php echo $p['name']; ?>:</strong> <span><?php echo $p['value']; ?></span><br />
														<?php } ?>
													<?php } ?>
												<?php } ?>

												<?php foreach ($fleet_parameters_values as $param) { ?>
													<?php echo EcalypseRental::return_parameter_value($param->fleet_parameters_id, $param->value, '', '<br>'); ?>
												<?php } ?>

											</div>
										</div>
									</div>
									<!-- .column -->	


									<div class="column">
										<div class="box box-darken box-inner">
											<h3><?= EcalypseRental::t('Rates') ?></h3>
											<?php
											$showvat = get_option('ecalypse_rental_show_vat');
											if ($showvat && $showvat == 'yes') {
												$showvat = true;
											}
											
											if ($american_pricing) {
												if ($ranges->hour_price > 0) {
													echo '<div>';
													echo '<span class="range-days">' . EcalypseRental::t('Price per hour') . ': </span>';
													echo '<span class="range-price"><strong>' . ($showvat ? EcalypseRental::price_with_vat($ranges->hour_price) : $ranges->hour_price) . '&nbsp;' . $ranges->currency . '</strong> </span>';
													echo '</div>';
												}
												if ($ranges->day_price > 0) {
													echo '<div>';
													echo '<span class="range-days">' . EcalypseRental::t('Price per day') . ': </span>';
													echo '<span class="range-price"><strong>' . ($showvat ? EcalypseRental::price_with_vat($ranges->day_price, false, unserialize($ranges->tax_rates)) : $ranges->day_price) . '&nbsp;' . $ranges->currency . '</strong> </span>';
													echo '</div>';
												}
												if ($ranges->week_price > 0) {
													echo '<div>';
													echo '<span class="range-days">' . EcalypseRental::t('Price per week') . ': </span>';
													echo '<span class="range-price"><strong>' . ($showvat ? EcalypseRental::price_with_vat($ranges->week_price, false, unserialize($ranges->tax_rates)) : $ranges->week_price) . '&nbsp;' . $ranges->currency . '</strong> </span>';
													echo '</div>';
												}
												if ($ranges->month_price > 0) {
													echo '<div>';
													echo '<span class="range-days">' . EcalypseRental::t('Price per month') . ': </span>';
													echo '<span class="range-price"><strong>' . ($showvat ? EcalypseRental::price_with_vat($ranges->month_price, false, unserialize($ranges->tax_rates)) : $ranges->month_price) . '&nbsp;' . $ranges->currency . '</strong> </span>';
													echo '</div>';
												}
											} else if ($time_pricing_type === 'half_day' && !empty($ranges)) {
												if ($ranges->am_price > 0) {
													echo '<div>';
													echo '<span class="range-days">' . EcalypseRental::t('AM price') . ': </span>';
													echo '<span class="range-price"><strong>' . ($showvat ? EcalypseRental::price_with_vat($ranges->am_price, false, unserialize($ranges->tax_rates)) : $ranges->am_price) . '&nbsp;' . $ranges->currency . '</strong> </span>';
													echo '</div>';
												}
												if ($ranges->pm_price > 0) {
													echo '<div>';
													echo '<span class="range-days">' . EcalypseRental::t('PM price') . ': </span>';
													echo '<span class="range-price"><strong>' . ($showvat ? EcalypseRental::price_with_vat($ranges->pm_price, false, unserialize($ranges->tax_rates)) : $ranges->pm_price) . '&nbsp;' . $ranges->currency . '</strong> </span>';
													echo '</div>';
												}
												if ($ranges->full_day_price > 0) {
													echo '<div>';
													echo '<span class="range-days">' . EcalypseRental::t('Full day price') . ': </span>';
													echo '<span class="range-price"><strong>' . ($showvat ? EcalypseRental::price_with_vat($ranges->full_day_price, false, unserialize($ranges->tax_rates)) : $ranges->full_day_price) . '&nbsp;' . $ranges->currency . '</strong> </span>';
													echo '</div>';
												}
											} else if ($ranges && !empty($ranges)) {
												$set_type = 0;
												foreach ($ranges as $key => $val) {
													if ($set_type != $val->type) {

														echo '<h4 class="mt">' . (($val->type == 1) ? EcalypseRental::t('Days range') : EcalypseRental::t('Hours range')) . '</h4>';
														$set_type = $val->type;
													}

													echo '<div>';
													echo '<span class="range-days">' . $val->no_from . ' - ' . ((int)$val->no_to == 0 ? '&infin;' : $val->no_to) . ' ' . (($val->type == 1) ? EcalypseRental::t('days') : EcalypseRental::t('hours')) . ': </span>';
													echo '<span class="range-price"><strong>' . ($showvat ? EcalypseRental::price_with_vat($val->price, false, unserialize($vehicle->pricing_scheme->tax_rates)) : $val->price) . '&nbsp;' . $val->currency . '</strong> ' . (($val->type == 1) ? '/ ' . EcalypseRental::t('day') : '/ ' . EcalypseRental::t('hour')) . '</span>';
													echo '</div>';
												}
											} else {
												echo '<h4 class="mt">' . EcalypseRental::t('Day or hour ranges are not set.') . '</h4>';
											}
											?>

											<a href="javascript:void(0);" class="btn btn-small btn-primary btn-book ecalypse-rental-book-this-car-btn bookcar rates-book-now" data-branch-id="<?= $anylocation ? 0 : $vehicle->id_branch; ?>" data-car-id="<?= $vehicle->id_fleet ?>"><?= EcalypseRental::t('Book This Car') ?></a>
										</div>
									</div>
								</div>
								<hr class="separate">
								<div class="h2 additional"><?= EcalypseRental::t('Additional information') ?></div>
								<?php if (isset($vehicle->description) && !empty($vehicle->description)) { ?>
									<p>
										<?php $fleet_description = unserialize($vehicle->description); ?>
										<?php
										if ($fleet_description == false) {
											$fleet_description['gb'] = $vehicle->description;
										}
										?>
										<?php $lang = ((isset(EcalypseRentalSession::$session['ecalypse_rental_language']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_language'])) ? EcalypseRentalSession::$session['ecalypse_rental_language'] : 'en_GB'); ?>
										<?php $lang = end(explode('_', $lang)); ?>
										<?= (isset($fleet_description[strtolower($lang)]) ? EcalypseRental::removeslashes($fleet_description[strtolower($lang)]) : EcalypseRental::removeslashes($fleet_description['gb'])) ?>
									</p>
								<?php } ?>
							<?php } else { ?>
								<p><?= EcalypseRental::t('Sorry, we did not find the vehicle in the database.') ?></p>
							<?php } ?>

						</div>
						<!-- .bordered-content -->

				</fieldset>

			</div>
			<!-- .column -->

		</div>
		<!-- .columns-2 -->

	</div>
	<!-- .container -->

</section>
<!-- .content -->	

<div id="ecalypse-rental-hidden-booking-form">
	<p class="close-win">×</p>
	<h3><?= EcalypseRental::t('Book your car now') ?></h3>
	<?php $ecalypse_rental_booking_form_id = '_popup'; ?>
	<?php include(EcalypseRentalTheme::get_file_template_path('booking-form.php')); ?>
</div>
<div class="booking-form-overflow"></div>
</div>

<?php get_footer(); ?>