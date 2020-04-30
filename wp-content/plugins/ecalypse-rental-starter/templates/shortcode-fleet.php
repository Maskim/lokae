<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 

$anylocation = get_option('ecalypse_rental_any_location_search');
if ($anylocation && $anylocation == 'yes') {
	$anylocation = true;
} else {
	$anylocation = false;
}
?>
<div class="ecalypse-rental-custom-theme">

<div class="column-single-fleet">

	<div class="list-item-media">


		<div class="pic-area">
			<h4><?= $vehicle->name ?></h4>
			<?php $additional_pictures_count = 0; ?>
			<?php if (isset($vehicle->additional_pictures) && !empty($vehicle->additional_pictures)) { ?>
				<?php $vehicle->additional_pictures = unserialize($vehicle->additional_pictures); ?>
				<?php if (is_array($vehicle->additional_pictures) && count($vehicle->additional_pictures) > 0) { ?>
					<?php $additional_pictures_count = count($vehicle->additional_pictures); ?>
				<?php } ?>
			<?php } ?>
			<p>
				<a href="<?= $vehicle->picture ?>" data-lightbox="fleet-<?= $vehicle->id_fleet ?>">
					<img src="<?= $vehicle->picture ?>" alt="<?= $vehicle->name ?>">
					<?php if ($additional_pictures_count > 0) { ?>
						<span class="btn btn-small btn-primary btn-book btn-absolute"><?= EcalypseRental::t('Show more pictures') ?> <strong>(<?php echo $additional_pictures_count; ?>)</strong></span>
					<?php } ?>
				</a>
			</p>
			<div class="hid-imgs">
				<?php if ($additional_pictures_count > 0) { ?>
					<?php foreach ($vehicle->additional_pictures as $adPicture) { ?>
						<a href="<?= $adPicture ?>" data-lightbox="fleet-<?= $vehicle->id_fleet ?>"></a>
					<?php } ?>
				<?php } ?>
			</div>
		</div>
		<p class="car-name"><b><?= $vehicle->name ?></b></p>
		<p>
			<?php echo $vehicle_cats[$vehicle->id_category]->name; ?><br><b><?= $vehicle->prices['cc_before'] ?><?= number_format($vehicle->prices['price'], 2, '.', ',') ?><?= $vehicle->prices['cc_after'] ?></b>
		</p><a href="javascript:void(0);" class="btn btn-small btn-primary btn-book ecalypse-rental-book-this-car-btn bookcar" data-branch-id="<?= $anylocation ? 0 : $vehicle->id_branch; ?>" data-car-id="<?= $vehicle->id_fleet ?>"><?= EcalypseRental::t('Book This Car') ?></a>
	</div>

</div>
<?php if (!$ecalypse_rental_fleet_loaded) { ?>
<div id="ecalypse-rental-hidden-booking-form">
	<p class="close-win">×</p>
	<h3><?= EcalypseRental::t('Book your car now') ?></h3>
	<?php $ecalypse_rental_booking_form_id = '_popup';?>
	<?php include(EcalypseRentalTheme::get_file_template_path('booking-form.php')); ?>
</div>
<div class="booking-form-overflow"></div>
<?php } ?>
</div>