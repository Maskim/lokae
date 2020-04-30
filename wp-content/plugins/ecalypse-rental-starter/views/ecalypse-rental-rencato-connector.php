<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<div class="ecalypse-rental-wrapper">

	<?php include ECALYPSERENTALSTARTER__PLUGIN_DIR . 'views/header.php'; ?>

	<div class="row">
		<div class="col-md-12 ecalypse-rental-main-wrapper">
			<div class="ecalypse-rental-main-content">

				<?php include ECALYPSERENTALSTARTER__PLUGIN_DIR . 'views/flash_msg.php'; ?>
				
				<h3>Rencato is a free platform used by rentals to cooperate with resell partners and automatically manage and pay out commissions</h3>

				<!-- Automatic upgrade //-->
				<div class="panel panel-default">
					<div class="panel-heading"><h4><?php _e('Rencato connector settings', 'ecalypse-rental'); ?></h4></div>
					<div class="panel-body">

						<div class="row">
							<div class="col-md-12">
								<?php $connector_settings = unserialize(get_option('ecalypse_rental_rencato_connector_settings')); 
									  $enabled = isset($connector_settings['enabled']) ? $connector_settings['enabled'] : 0;
									  $api_key = isset($connector_settings['api_key']) ? $connector_settings['api_key'] : '';
								?>
								<div class="row">
									<div class="col-md-6">
										<form action="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-rencato-connector') ?>" method="post" role="form" class="form-horizontal">
											<div class="form-group">
												<label for="enable_api" class="col-sm-3 control-label"><?php _e('Enable Rencato connector?', 'ecalypse-rental'); ?></label>
												<div class="col-sm-9">
													<label class="radio-inline">
														<input type="radio" name="ecalypse_rental_enable_rencato_connector" value="1" <?= ($enabled == 1 ? ' checked="checked"' : '') ?>>&nbsp;<?php _e(' Yes', 'ecalypse-rental'); ?>
													</label>
													<label class="radio-inline"><input type="radio" name="ecalypse_rental_enable_rencato_connector" value="0" <?= ($enabled != 1 ? ' checked="checked"' : '') ?>>
														&nbsp;<?php _e(' No', 'ecalypse-rental'); ?>
													</label>
													<p class="help-block"><?php _e('To enable connection with Rencato, you must enable it in your Rencato settings. Login to your Rencato account and navigate to Rental settings -> Other systems connector and set Enable this API to “Yes” Then, insert Your API connector here.', 'ecalypse-rental'); ?></p>
												</div>
											</div>

											<div class="form-group">
												<label for="ecalypse_rental_rencato_connector_api_key" class="col-sm-3 control-label" style="padding-top:0;"><?php _e('Insert API key', 'ecalypse-rental'); ?></label>
												<div class="col-sm-9">
													<input type="text" name="ecalypse_rental_rencato_connector_api_key" id="ecalypse_rental_api" class="form-control" value="<?php echo $api_key;?>" placeholder="<?php _e('Your Rencato connector API key.', 'ecalypse-rental'); ?>">
													<p>Connected to Rencato under domain <strong><?php echo $_SERVER['HTTP_HOST'];?></strong>. <a href="#" id="ecalypse_rental_disconnect_rencato">Disconnect</a>.</p>
												</div>
											</div>
											
											<div class="form-group">
												<div class="col-sm-offset-3 col-sm-9">
													<button type="submit" name="save_rencato_connector_settings" class="btn btn-warning"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Save Rencato connector settings', 'ecalypse-rental'); ?></button>
												</div>
											</div>
										</form>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#ecalypse_rental_disconnect_rencato').click(function(e){
			e.preventDefault();
			$('input[name=ecalypse_rental_enable_rencato_connector][value=0]').prop("checked", true);
			$('button[name=save_rencato_connector_settings]').click();
		});
	});
</script>
