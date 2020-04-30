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
						
						<?php if ($edit == true) { ?>
							<h3><?php _e('Edit item:', 'ecalypse-rental');?> <?= $detail->name_admin == '' ? $detail->name : $detail->name_admin ?></h3>
						<?php } else { ?>
							<?php if (isset($_GET['deleted'])) { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-extras')); ?>" class="btn btn-default" style="float:right;"><?php _e('Show normal', 'ecalypse-rental');?></a>
							<?php } else { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-extras')); ?>&amp;deleted" class="btn btn-default" style="float:right;"><?php _e('Show deleted', 'ecalypse-rental');?></a>
							<?php } ?>
							
							<a href="javascript:void(0);" class="btn btn-success" id="ecalypse-rental-extras-add-button"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;<?php _e('Add new item or service', 'ecalypse-rental');?></a>
						<?php } ?>
						
						<div id="<?= (($edit == true) ? 'ecalypse-rental-extras-edit-form' : 'ecalypse-rental-extras-add-form') ?>" class="ecalypse-rental-add-form">
							<form action="" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
								
								<div class="row">
									<div class="col-md-11">
										
										<div class="alert alert-info">
											<p><span class="glyphicon glyphicon-share-alt"></span>&nbsp;&nbsp;<?php _e('Whichever field is left blank will not be used in item or service description.', 'ecalypse-rental');?></p>
											<p><span class="glyphicon glyphicon-share-alt"></span>&nbsp;&nbsp;<?php _e('Manage your', 'ecalypse-rental');?> <a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>"><?php _e('Pricing schemes', 'ecalypse-rental');?></a> <?php _e('first.', 'ecalypse-rental');?></p>
										</div>
										
										 <!-- Name admin //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-name-admin" class="col-sm-3 control-label"><?php _e('Internal name for administration', 'ecalypse-rental');?></label>
									     <div class="col-sm-9">
									    	<input type="text" name="name_admin" class="form-control" id="ecalypse-rental-name-admin" value="<?= (($edit == true) ? $detail->name_admin : '') ?>">
									    </div>
									  </div>
									
										<div class="form-group">
											<div class="col-sm-3"></div>
											<div class="col-sm-9">
												<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
													<li role="presentation" class="active"><a href="javascript:void(0);" class="edit_extras_name_desc" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
													<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
													<?php if ($available_languages && !empty($available_languages)) { ?>														
														<?php foreach ($available_languages as $key => $val) { ?>
															<?php if ($val['country-www'] == 'gb') {continue;} ?>
															<li role="presentation"><a href="javascript:void(0);" class="edit_extras_name_desc" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
														<?php } ?>
													<?php } ?>
												</ul>
											</div>
										</div>
										
									  <!-- Name //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-name" class="col-sm-3 control-label"><?php _e('Name', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_extras_translations" data-lng="gb">
												<input type="text" name="name" class="form-control" id="ecalypse-rental-name" placeholder="GPS, Additional Driver, ..." value="<?= (($edit == true) ? $detail->name : '') ?>">
											</div>
											<?php if ($edit == true) { ?>
												<?php $name_translations = unserialize($detail->name_translations);?>
												<?php if (empty($name_translations)) { $name_translations = array(); } ?>
											<?php } ?>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_extras_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<input type="text" name="name_translations[<?php echo $val['country-www'];?>]" placeholder="Name in <?php echo $val['lang-native'];?>" class="form-control" value="<?= (($edit == true && isset($name_translations[$val['country-www']])) ? $name_translations[$val['country-www']] : '') ?>">
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									  
									  <!-- Description //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-description" class="col-sm-3 control-label"><?php _e('Description', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_extras_translations" data-lng="gb">
												<textarea class="form-control" name="description" id="ecalypse-rental-description" rows="3"><?= (($edit == true) ? EcalypseRental::removeslashes($detail->description) : '') ?></textarea>
											</div>
											<?php if ($edit == true) { ?>
												<?php $description_translations = unserialize($detail->description_translations);?>
												<?php if (empty($description_translations)) { $description_translations = array(); } ?>
											<?php } ?>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_extras_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<textarea class="form-control" name="description_translations[<?php echo $val['country-www'];?>]" placeholder="Description in <?php echo $val['lang-native'];?>" rows="3"><?= (($edit == true && isset($description_translations[$val['country-www']])) ? $description_translations[$val['country-www']] : '') ?></textarea>
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									  
										<!-- Global Pricing Scheme //-->
									  <div class="form-group">
									    <label class="col-sm-3 control-label"><?php _e('Global Pricing scheme', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
										    <select name="global_pricing_scheme" class="form-control">
										    	<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
										    	<?php if (isset($pricing) && !empty($pricing)) { ?>
											    	<?php foreach ($pricing as $key => $val) { ?>
											    		<option value="<?= $val->id_pricing ?>" <?= (($edit == true && $detail->global_pricing_scheme == $val->id_pricing) ? 'selected="selected"' : '') ?>><?= $val->name ?></option>
											    	<?php } ?>
											    <?php } ?>
									    	</select>
										    <p class="help-block"><?php _e('This pricing scheme is used when no other pricing scheme is active or usable.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
																			  
									  <!-- Price Scheme //-->
									  <div class="form-group disabled">
									    <label class="col-sm-3 control-label"><abbr title="Highest priority first!"><?php _e('Pricing scheme', 'ecalypse-rental');?></abbr></label>
									    <div class="col-sm-9">
											<h4 style="color: #000;margin-top: 0px; margin-bottom: 20px;">Full version of Ecalypse Rental Plugin allows to set your own currencies and exchange rates. <a href="https://wp.ecalypse.com/" target="_blank">Get it here</a></h4>
										    <div id="pricing_sort">
										    		
														<?php if ($edit == true && isset($detail->pricing) && !empty($detail->pricing)) { ?>
															<?php foreach ($detail->pricing as $key => $val) { ?>
																
																<!-- Price scheme row //-->
												    		<div class="row" style="position: relative;" class="sortable">
																  <div class="col-xs-5">
																	  <select name="" class="form-control" disabled="disabled">
																    	<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
																    	<?php if (isset($pricing) && !empty($pricing)) { ?>
																	    	<?php foreach ($pricing as $kD => $vD) { ?>
																	    		<option value="<?= $vD->id_pricing ?>" <?= (($val->id_pricing == $vD->id_pricing) ? 'selected="selected"' : '') ?>><?= $vD->name ?></option>
																	    	<?php } ?>
																	    <?php } ?>
															    	</select>
															    </div>
																	<div class="col-xs-3">
																		<div class="form-group has-feedback">
																			<input type="text" name=""  disabled="disabled" class="form-control" placeholder="Valid from" value="<?= (($val->valid_from != '0000-00-00') ? $val->valid_from : '') ?>">
														    			<span class="glyphicon glyphicon-calendar form-control-feedback"></span>
														    		</div>
														    	</div>
																	<div class="col-xs-3">
																		<div class="form-group has-feedback">
														    			<input type="text" name=""  disabled="disabled" class="form-control" placeholder="Valid until" value="<?= (($val->valid_to != '0000-00-00') ? $val->valid_to : '') ?>">
														    			<span class="glyphicon glyphicon-calendar form-control-feedback"></span>
														    		</div>
														    	</div>
														    	<div class="col-xs-1">
														    		<span class="glyphicon glyphicon-sort" style="margin-top:9px;cursor:move;" title="Move up or down to sort Price scheme. Highest priority first!"></span>
																  </div>														
													    	</div><!-- .row //-->
											    	
															<?php } ?>
														<?php } ?>
										    		
										    	<div id="ecalypse-rental-prices">
										    		<!-- Price scheme row //-->
										    		<div class="row" style="position: relative;" class="sortable">
														  <div class="col-xs-5">
														  	<select name=""  disabled="disabled" class="form-control">
														    	<option value="0">- <?php _e('none', 'ecalypse-rental');?> -</option>
														    	<?php if (isset($pricing) && !empty($pricing)) { ?>
															    	<?php foreach ($pricing as $key => $val) { ?>
															    		<option value="<?= $val->id_pricing ?>"><?= $val->name ?></option>
															    	<?php } ?>
															    <?php } ?>
													    	</select>
													    </div>
															<div class="col-xs-3">
																<div class="form-group has-feedback">
																	<input type="text" name=""  disabled="disabled" class="form-contro" placeholder="Valid from">
												    			<span class="glyphicon glyphicon-calendar form-control-feedback"></span>
												    		</div>
												    	</div>
															<div class="col-xs-3">
																<div class="form-group has-feedback">
												    			<input type="text" name=""  disabled="disabled" class="form-control" placeholder="Valid until">
												    			<span class="glyphicon glyphicon-calendar form-control-feedback"></span>
												    		</div>
												    	</div>
												    	<div class="col-xs-1">
												    		<span class="glyphicon glyphicon-sort" style="margin-top:9px;cursor:move;" title="Move up or down to sort Price scheme. Highest priority first!"></span>
														  </div>														
											    	</div><!-- .row //-->
												  </div>
											    
													<div id="ecalypse-rental-prices-insert"></div>
												</div>
									    	<a href="javascript:void(0);" id="" class="btn btn-info btn-xs"><?php _e('Add Pricing Scheme', 'ecalypse-rental');?></a>
									    </div>
									  </div>
									  
									  <!-- Mandatory //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-mandatory" class="col-sm-3 control-label"><?php _e('Mandatory', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												  <input type="radio" name="mandatory" value="1" <?= (($detail->mandatory == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
												  <input type="radio" name="mandatory" value="0" <?= (($detail->mandatory == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('No', 'ecalypse-rental');?>
												</label>
									    </div>
									  </div> 
									  
									  <!-- Internal ID //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-internal_id" class="col-sm-3 control-label"><?php _e('Internal item or service ID', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="internal_id" class="form-control" id="ecalypse-rental-internal_id" value="<?= (($edit == true) ? $detail->internal_id : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Additional Drivers //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-max_drivers" class="col-sm-3 control-label"><?php _e('Maximum Additional Drivers', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="max_additional_drivers" class="form-control" id="ecalypse-rental-max_drivers" value="<?= (($edit == true) ? $detail->max_additional_drivers : '') ?>">
									    	<p class="help-block">
													<?php _e('This is a special field. If you want to use this item as a function', 'ecalypse-rental');?> "<strong><?php _e('Additional Driver', 'ecalypse-rental');?></strong>" <?php _e('in the booking process, insert maximum of additional drivers available (0 = disabled function).', 'ecalypse-rental');?>
													<br><?php _e('Every driver will be charched based on price scheme individually.', 'ecalypse-rental');?>
												</p>
											</div>
									  </div>
									  
									  <!-- Picture of item or service //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-picture" class="col-sm-3 control-label"><?php _e('Picture of item or service', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<?php if ($edit == true) { ?>
									    		<div class="panel panel-info">
													  <div class="panel-heading"><?php _e('Current picture', 'ecalypse-rental');?></div>
													  <div class="panel-body">
													    <p><img src="<?= $detail->picture ?>" height="80"></p>
													  </div>
													</div>
													<p><strong><?php _e('Or you can upload new picture for Branch:', 'ecalypse-rental');?></strong></p>
									  		<?php } ?>
									    	<input type="file" name="picture" id="ecalypse-rental-picture">
									    	<p class="help-block"><?php _e('Insert picture of the item or service, 400x400px.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
									  <!-- Submit //-->
									  <div class="form-group">
									  	<div class="col-sm-offset-3 col-sm-9">
											<?php wp_nonce_field( 'add_extras'); ?>
									  		<?php if ($edit == true) { ?>
									  			<input type="hidden" name="id_extras" value="<?= $detail->id_extras ?>">
									  			<input type="hidden" name="current_picture" value="<?= $detail->picture ?>">
									  			<button type="submit" class="btn btn-warning" name="add_extras"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									  		<?php } else { ?>
									  			<button type="submit" class="btn btn-warning" name="add_extras"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Add', 'ecalypse-rental');?></button>
									  		<?php } ?>
									  	</div>
										</div>
										
									</div>
								</div>
								
							</form>
						</div>
						
						<hr>
					</div>
				</div>
				
				<div class="row">
					<div class="col-md-12">
						
						<?php if (isset($extras) && !empty($extras)) { ?>
						<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-extras" /> <?php _e('Select all', 'ecalypse-rental');?></label>
							<table class="table table-striped" id="ecalypse-rental-extras">
					      <thead>
					        <tr>
					          <th>#</th>
					          <th><?php _e('Image', 'ecalypse-rental');?></th>
					          <th><?php _e('Name', 'ecalypse-rental');?></th>
					          <th><?php _e('Pricing schemes', 'ecalypse-rental');?></th>
					          <th><?php _e('Internal ID', 'ecalypse-rental');?></th>
					          <th><?php _e('Description', 'ecalypse-rental');?></th>
					          <th><?php _e('Action', 'ecalypse-rental');?></th>
					        </tr>
					      </thead>
					      <tbody>
								<?php foreach ($extras as $key => $val) { ?>
				      		<tr>
					          <td>
					          	<input type="checkbox" class="input-control batch_processing" name="batch[]" value="<?= $val->id_extras ?>">&nbsp;
											<abbr title="Created: <?= $val->created ?>

<?= (!empty($val->updated) ? 'Updated: ' . $val->updated : '') ?>"><?= $val->id_extras ?></abbr>
										</td>
					          <td><img src="<?= $val->picture ?>" height="120"></td>
					          <td><strong><?= ($val->name_admin == '' ? (!empty($val->name) ? $val->name : '- Unknown -') : $val->name_admin) ?></strong></td>
										<td>
											<?php if (!empty($val->pricing_name)) { ?>
												<p><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>&amp;<?= (($val->pricing_type == 1) ? 'get_onetime_price' : 'get_day_ranges') ?>=<?= $val->global_pricing_scheme ?>" class="btn <?= (($val->pricing_type == 1) ? 'btn-info' : 'btn-success') ?> ecalypse_rental_show_ranges"><?= $val->pricing_name ?></a></p>
												<?php if ($val->pricing_count > 0) { ?>
													<p><a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-pricing')); ?>&amp;get_extras_price_schemes=<?= $val->id_extras ?>" class="btn <?= (($val->pricing_type == 1) ? 'btn-info' : 'btn-success') ?> ecalypse_rental_show_ranges">+ <?= $val->pricing_count ?> <?php _e('schemes', 'ecalypse-rental');?></a></p>
												<?php } ?>
											<?php } else { ?>
												<p><em>- <?php _e('none', 'ecalypse-rental');?> -</em></p>
											<?php } ?>
										</td>
										<td><?= (!empty($val->internal_id) ? $val->internal_id : '<p><em>- empty -</em></p>') ?></td>
										<td><p style="max-width:200px;"><?= (!empty($val->description) ? $val->description : '<em>- empty -</em>') ?></p></td>
										<td>
											<form action="" method="post" class="form" role="form">
												<div class="form-group">
													<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-extras')); ?>&amp;edit=<?= $val->id_extras ?>" class="btn btn-primary btn-block"><?php _e('Modify', 'ecalypse-rental');?></a>
												</div>
											</form>
											<form action="" method="post" class="form" role="form">
												<div class="form-group">
													<input type="hidden" name="id_extras" value="<?= $val->id_extras ?>">
													<?php wp_nonce_field( 'copy_extras'); ?>
													<button name="copy_extras" class="btn btn-warning btn-block"><?php _e('Copy', 'ecalypse-rental');?></button>
												</div>
											</form>
											<?php if (isset($_GET['deleted'])) { ?>
												<form action="" method="post" class="form" role="form" onsubmit="return confirm('<?= __('Do you really want to restore this Extras?', 'ecalypse-rental') ?>');">
													<div class="form-group">
														<input type="hidden" name="id_extras" value="<?= $val->id_extras ?>">
														<?php wp_nonce_field( 'restore_extras'); ?>
														<button name="restore_extras" class="btn btn-success btn-block"><?php _e('Restore', 'ecalypse-rental');?></button>
													</div>
												</form>
											<?php } else { ?>
												<form action="" method="post" class="form" role="form" onsubmit="return confirm('<?= __('Do you really want to delete this Extras?', 'ecalypse-rental') ?>');">
													<div class="form-group">
														<input type="hidden" name="id_extras" value="<?= $val->id_extras ?>">
														<?php wp_nonce_field( 'delete_extras'); ?>
														<button name="delete_extras" class="btn btn-danger btn-block"><?php _e('Delete', 'ecalypse-rental');?></button>
													</div>
												</form>
											<?php } ?>
										</td>
					        </tr>
				      	<?php } ?>
					    	</tbody>
					  	</table>
						<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-extras" /> <?php _e('Select all', 'ecalypse-rental');?></label>
					  	
					    <h4><?php _e('Batch action on selected items', 'ecalypse-rental');?></h4>
					    
					    <form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Item is selected to copy.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to copy selected Items?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<input type="hidden" name="batch_processing_values" value="">
									<?php wp_nonce_field( 'batch_copy_extras'); ?>
									<button name="batch_copy_extras" class="btn btn-warning">Copy <span class="batch_processing_count"></span><?php _e('selected Items', 'ecalypse-rental');?></button>
								</div>
							</form>
						
						<?php if (isset($_GET['deleted'])) { ?>
								<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Item is selected to delete.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to delete selected Items?', 'ecalypse-rental') ?>');">
									<div class="form-group">
										<input type="hidden" name="batch_processing_values" value="">
										<?php wp_nonce_field( 'batch_delete_db_extras'); ?>
										<button name="batch_delete_db_extras" class="btn btn-danger"><?php _e('Delete', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Extras from database', 'ecalypse-rental');?></button>
									</div>
								</form>
							<?php } else { ?>
								<form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Item is selected to delete.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to delete selected Items?', 'ecalypse-rental') ?>');">
									<div class="form-group">
										<input type="hidden" name="batch_processing_values" value="">
										<?php wp_nonce_field( 'batch_delete_extras'); ?>
										<button name="batch_delete_extras" class="btn btn-danger">Delete <span class="batch_processing_count"></span><?php _e('selected Items', 'ecalypse-rental');?></button>
									</div>
								</form>
								<?php } ?>							
						<?php } else { ?>
							<div class="alert alert-info">
								<span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;
								<?= esc_html__( 'You do not have any Extras created yet, please create one clicking on "Add New Item or Service".', 'ecalypse-rental' ); ?>
							</div>
						<?php } ?>
						
					</div>
				</div>
				
				
				
			</div>
		</div>
	</div>
	
</div>
