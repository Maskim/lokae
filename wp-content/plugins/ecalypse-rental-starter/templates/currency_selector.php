<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<?php if (count($currency) > 1) { ?> 
	<div class="control-field">
		<form action="" method="get">
			<select name="currency" id="ecalypse_rental_change_currency" style="padding: 1px 3px;">
				<?php foreach ($currency as $cc) { ?>
					<option value="<?= $cc ?>" <?php if (isset(EcalypseRentalSession::$session['ecalypse_rental_currency']) && EcalypseRentalSession::$session['ecalypse_rental_currency'] == $cc) { ?>selected<?php } ?>><?= $cc ?></option>
				<?php } ?>
			</select>
			<input type="hidden" name="page" value="ecalypse-rental">
			<input type="hidden" name="change_currency" value="1">
		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function () {
			jQuery('#ecalypse_rental_change_currency').on('change', function () {
				jQuery(this).parent().submit();
			});
		});
	</script>
<?php } ?>