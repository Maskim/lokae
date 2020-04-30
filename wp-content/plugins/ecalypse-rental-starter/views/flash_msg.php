<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<?php if (isset(EcalypseRentalSession::$session['ecalypse_rental_flash_msg']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_flash_msg']) && !empty(EcalypseRentalSession::$session['ecalypse_rental_flash_msg']['msg'])) { ?>

	<div class="row">
	  <div class="col-md-12">
	  	<div class="alert alert-<?= EcalypseRentalSession::$session['ecalypse_rental_flash_msg']['status'] ?> alert-dismissable">
	  		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
	  		<span class="glyphicon glyphicon-<?= ((EcalypseRentalSession::$session['ecalypse_rental_flash_msg']['status'] == 'success') ? 'ok' : 'remove') ?>"></span>&nbsp;&nbsp;
				<?= EcalypseRentalSession::$session['ecalypse_rental_flash_msg']['msg'] ?>
			</div>
		</div>
	</div>
	
	<?php unset(EcalypseRentalSession::$session['ecalypse_rental_flash_msg']); // Delete flash msg ?>
<?php } ?>