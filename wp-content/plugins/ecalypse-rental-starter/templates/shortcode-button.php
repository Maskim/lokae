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
<span class="ecalypse-rental-custom-theme"><a href="javascript:void(0);" class="btn btn-small btn-primary btn-book ecalypse-rental-book-this-car-btn bookcar" data-branch-id="<?= $anylocation ? 0 : $vehicle->id_branch; ?>" data-car-id="<?= $vehicle->id_fleet ?>"><?= $text; ?></a></span>

<?php if (!$ecalypse_rental_fleet_loaded) { ?>
<div class="ecalypse-rental-custom-theme">
<div id="ecalypse-rental-hidden-booking-form">
	<p class="close-win">×</p>
	<h3><?= EcalypseRental::t('Book your car now') ?></h3>
	<?php $ecalypse_rental_booking_form_id = '_popup';?>
	<?php include(EcalypseRentalTheme::get_file_template_path('booking-form.php')); ?>
</div>
<div class="booking-form-overflow"></div>
</div>
<?php } ?>