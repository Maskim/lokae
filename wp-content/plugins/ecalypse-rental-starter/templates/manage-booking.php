<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<form action="" method="post" class="form form-request form-vertical form-size-100">
	<fieldset>
		
		<h1><?= EcalypseRental::t('Manage your booking') ?></h1>

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