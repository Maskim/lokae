<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<div class="ecalypse-rental-custom-theme">

<div class="tabs" style="max-width:100%;<?php echo isset($params['width']) && (int)$params['width'] > 0 ? 'width:'.(int)$params['width'].'px;': '';?>">
	<ul class="tabs-navigation">
		<li class="tabs-navigation-active">
			<a href="javascript:void(0);" data-tab-target="quick-book"><?= EcalypseRental::t('QUICK BOOK') ?></a>
		</li>
		<li class="tabs-navigation-link">
			<a href="javascript:void(0);" data-tab-target="manage-booking"><?= EcalypseRental::t('MANAGE BOOKING') ?></a>
		</li>
	</ul>

	<div class="tabs-content">

		<div data-tab-id="quick-book" class="tabs-content-tab tabs-content-tab-active">

			<?php EcalypseRentalTheme::render_booking_form(); ?>

		</div>
		<!-- .tabs-content-tab -->

		<div data-tab-id="manage-booking" class="tabs-content-tab">
			<form action="" method="post" class="form form-request form-vertical form-size-100">
				<fieldset>

					<div class="control-group">
						<div class="control-label">
							<label for="ecalypse_rental_order_number"><?= EcalypseRental::t('Order Number') ?>:</label>
						</div>
						<div class="control-field">
							<input type="text" name="id_order" id="ecalypse_rental_order_number" class="control-input">
						</div>
					</div>

					<div class="control-group">
						<div class="control-label">
							<label for="ecalypse_rental_order_email"><?= EcalypseRental::t('Your E-mail') ?>:</label>
						</div>
						<div class="control-field">
							<input type="text" name="email" id="ecalypse_rental_order_email" class="control-input">
						</div>
					</div>

					<div class="control-group">
						<div class="control-field align-right">
							<input type="hidden" name="page" value="ecalypse-rental">
							<button type="submit" name="manage_booking" class="btn btn-primary"><?= EcalypseRental::t('SHOW ORDER DETAILS') ?></button>	
						</div>
					</div>

				</fieldset>
			</form>
		</div>
		<!-- .tabs-content-tab -->

	</div>
	<!-- .tabs-content -->
</div>
<!-- .tabs -->
</div>
