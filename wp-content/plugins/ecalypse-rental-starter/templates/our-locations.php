<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<?php $theme_options = unserialize(get_option('ecalypse_rental_theme_options')); ?>
<div class="ecalypse-rental-custom-theme">
<div class="column column-fluid our-locations">
						
				<div class="bordered-content">
					
					<div class="bordered-content-title">
						<?= EcalypseRental::t('Branch information') ?>
					</div>
					<!-- .bordered-content-title -->
			
			
			<?php if ($locations && !empty($locations)) { ?>
				<?php $countries = EcalypseRental::get_country_list(); ?>
				<?php foreach ($locations as $key => $val) { ?>
					<div class="list-item list-item-branch-dail box box-white box-inner">
		
						<div class="list-item-content" style="margin-right:0;padding-right:0;">
							<div class="columns-3">
								<div class="column column-thin" style="width:25%;">
									<div class="h4"><?= EcalypseRental::t('Address') ?>:</div>
									
									<address>
										<?php echo $val->name;?><br>
										<?php $googleMap = ''; ?>
										<?php if (!empty($val->street)) { echo $val->street . '<br>'; $googleMap .= $val->street . ', '; } ?>
										<?php if (!empty($val->city)) { echo $val->city . ', '; $googleMap .= $val->city . ', '; } ?>
										<?php if (!empty($val->zip)) { echo $val->zip . '<br>'; $googleMap .= $val->zip . ', '; } ?>
										<?php if (!empty($val->country)) { echo $countries[$val->country]; $googleMap .= $countries[$val->country] . ', '; } ?>
										<?php if (!empty($val->state)) { echo '&nbsp;' . $val->state; } ?>
									</address>
									
									<?php if (isset($val->picture) && !empty($val->picture)) { ?>
										<img src="<?= $val->picture ?>" width="150" style="margin: 5px;" />
									<?php } ?>
									
								</div>
		
								<div class="column column-wide" style="width:30%;">
									<div class="h4"><?= EcalypseRental::t('Working Hours') ?>:</div>
									
									<?php if (!empty($val->hours)) { ?>
										<?php foreach ($val->hours as $kD => $vD) { ?>
											<dl class="dl-boxed">
												<dt><?= EcalypseRental::get_day_name($vD->day, array(EcalypseRental::t('Monday'), EcalypseRental::t('Tuesday'), EcalypseRental::t('Wednesday'), EcalypseRental::t('Thursday'), EcalypseRental::t('Friday'), EcalypseRental::t('Saturday'), EcalypseRental::t('Sunday'))) ?></dt>
												<dd><?= EcalypseRental::ecalypse_rental_time_format(substr($vD->hours_from, 0, 5), (isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24)) ?> - <?= EcalypseRental::ecalypse_rental_time_format(substr($vD->hours_to, 0, 5), (isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24)) ?>
												<?php if (isset($vD->hours_from_2) && $vD->hours_from_2 != '' && $vD->hours_from_2 != '00:00:00') { ?>
													<?php if (isset($vD->hours_to_2) && $vD->hours_to_2 != '' && $vD->hours_to_2 != '00:00:00') { ?>
													 | <?= EcalypseRental::ecalypse_rental_time_format(substr($vD->hours_from_2, 0, 5), (isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24)) ?> - <?= EcalypseRental::ecalypse_rental_time_format(substr($vD->hours_to_2, 0, 5), (isset($theme_options) && isset($theme_options['time_format']) ? $theme_options['time_format'] : 24)) ?>
													<?php } ?>
												<?php } ?>
												</dd>
											</dl>
										<?php } ?>
									<?php } ?>
								</div>
								
								<?php if (isset($val->gps) && $val->gps != '') {
									$googleMap = $val->gps;
								}?>
								<?php if (isset($googleMap) && !empty($googleMap)) { ?>
									<div class="column col-mar-left" style="width:40%;margin-left:20px;">
										<div class="h4"><?= EcalypseRental::t('Map') ?>:</div>
										<iframe width="100%" height="200" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q=<?= urlencode($googleMap) ?>&key=AIzaSyB66YsdY21SxuDaC3J-9aOmK4L99bD8u7Q"></iframe>
									</div>
								<?php } ?>
								
							</div>
							
							<div class="columns-2">
								<?php if (!empty($val->phone)) { ?>
									<div class="column column-thin">
										<div class="h4"><?= EcalypseRental::t('Phone') ?>:</div>
										<p><?= $val->phone ?></p>
									</div>
								<?php } ?>
		
								<?php if (!empty($val->phone)) { ?>
									<div class="column column-wide">
										<div class="h4"><?= EcalypseRental::t('E-Mail') ?>:</div>
										<p><a href="mailto:<?= $val->email ?>"><?= $val->email ?></a></p>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
					
				<?php } ?>
			<?php } ?>
			
		</div>
	</div>
</div>