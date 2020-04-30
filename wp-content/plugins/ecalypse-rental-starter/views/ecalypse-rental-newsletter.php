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
				
				<div class="row">
					<div class="col-md-12">
						
						<?php if (isset($newsletter) && !empty($newsletter)) { ?>
						<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-newsletter" /> <?php _e('Select all', 'ecalypse-rental');?></label>
							<table class="table table-striped" id="ecalypse-rental-newsletter">
					      <thead>
					        <tr>
							  <th>#</th>
					          <th<?php _e('>Created', 'ecalypse-rental');?></th>
					          <th><?php _e('First name', 'ecalypse-rental');?></th>
					          <th><?php _e('Last name', 'ecalypse-rental');?></th>
					          <th><?php _e('E-mail', 'ecalypse-rental');?></th>
					        </tr>
					      </thead>
					      <tbody>
								<?php foreach ($newsletter as $key => $val) { ?>
				      		<tr>
								<td>
											<input type="checkbox" class="input-control batch_processing" name="batch[]" value="<?= $val->id_booking ?>">&nbsp;
											<abbr><?= $val->id_booking ?></abbr></td>
					          <td><?= (!empty($val->created) ? $val->created : '- Unknown -') ?></td>
					          <td><?= (!empty($val->first_name) ? $val->first_name : '- Unknown -') ?></td>
										<td><?= (!empty($val->last_name) ? $val->last_name : '- Unknown -') ?></td>
										<td><?= (!empty($val->email) ? $val->email : '- Unknown -') ?></td>
					        </tr>
				      	<?php } ?>
					    	</tbody>
					  	</table>
						<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-newsletter" /> <?php _e('Select all', 'ecalypse-rental');?></label>
						<div>
						<a class="btn btn-warning" href="<?= wp_nonce_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-newsletter').'&amp;ecalypse-rental-newsletter-export=csv', 'ecalypse-rental-newsletter-export');?>"><?php _e('Export all as CSV', 'ecalypse-rental');?></a>
						</div>
						<h4><?php _e('Batch action on selected items', 'ecalypse-rental');?></h4>
						
						<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No items is selected to remove.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to remove selected items?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<?php wp_nonce_field( 'batch_delete_newsletter'); ?>
									<input type="hidden" name="batch_processing_values" value="">
									<button name="batch_delete_newsletter" class="btn btn-danger">Remove <span class="batch_processing_count"></span><?php _e('selected Items', 'ecalypse-rental');?></button>
								</div>
							</form>
							
						<?php } else { ?>
							<div class="alert alert-info">
								<span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;
								<?= esc_html__( 'You do not have any User in Newsletter yet.', 'ecalypse-rental' ); ?>
							</div>
						<?php } ?>
						
					</div>
				</div>
				
				
				
			</div>
		</div>
	</div>
	
</div>
