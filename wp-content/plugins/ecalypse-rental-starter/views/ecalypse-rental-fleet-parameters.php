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

						<?php $name = array();
							if ($edit == true) { 
								$name = unserialize($detail->name);				
						?>
							<h3><?php _e('Edit fleet parameter:', 'ecalypse-rental');?> <?= $name['gb'] ?></h3>
						<?php } else { ?>							
							<a href="javascript:void(0);" class="btn btn-success" id="ecalypse-rental-fleet-parameter-add-button"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;<?php _e('Add new parameter', 'ecalypse-rental');?></a>
							
							<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-fleet')); ?>" class="btn btn-warning"><span class="glyphicon glyphicon-road"></span>&nbsp;&nbsp;<?php _e('Manage fleet', 'ecalypse-rental');?></a>
						<?php } ?>

						<div id="<?= (($edit == true) ? 'ecalypse-rental-fleet-parameter-edit-form' : 'ecalypse-rental-fleet-parameter-add-form') ?>" class="ecalypse-rental-add-form"<?= (($edit == true) ? '' : ' style="display:none;"') ?>>
							<form action="" id="ecalypse-rental-fleet-parameter-form" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">

								<div class="row">
									<div class="col-md-11">

										<!-- Name //-->
										<div class="form-group">
											<label for="ecalypse-rental-type" class="col-sm-3 control-label"><?php _e('Name', 'ecalypse-rental');?></label>
											<div class="col-sm-9">

												<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
													<li role="presentation" class="active"><a href="javascript:void(0);" class="edit_fleet_parameter_name" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
													<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
													<?php if ($available_languages && !empty($available_languages)) { ?>
														<?php foreach ($available_languages as $key => $val) { ?>
															<?php if ($val['country-www'] == 'gb') {continue;} ?>
															<li role="presentation"><a href="javascript:void(0);" class="edit_fleet_parameter_name" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
														<?php } ?>
													<?php } ?>
												</ul>

												<input type="text" name="name[gb]" class="form-control fleet_parameter_name_input lng_gb" placeholder="Parameter name" value="<?= ((isset($name['gb']) && !empty($name['gb'])) ? $name['gb'] : '') ?>">
												<?php if ($available_languages && !empty($available_languages)) { ?>
													<?php foreach ($available_languages as $key => $val) { ?>
														<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<input style="display:none;" type="text" name="name[<?= strtolower($val['country-www']) ?>]" class="form-control fleet_parameter_name_input lng_<?= strtolower($val['country-www']) ?>" placeholder="Parameter name" value="<?= ((isset($name[$val['country-www']]) && !empty($name[$val['country-www']])) ? $name[$val['country-www']] : '') ?>">
													<?php } ?>
												<?php } ?>
											</div>
										</div>
										<?php if ($edit != true) { ?>
										 <!-- Parameter type//-->
									  <div class="form-group">
									    <label for="ecalypse-rental-type" class="col-sm-3 control-label"><?php _e('Type', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												<input type="radio" class="ecalypse-rental-fleet-parameter-type" name="type" data-type="range" value="1" <?= (($detail->type == 1 || !isset($detail->type)) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('Range', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
												  <input type="radio" class="ecalypse-rental-fleet-parameter-type" name="type" data-type="values" value="2" <?= (($detail->type == 2) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('Values', 'ecalypse-rental');?>
												</label>
									    </div>
									  </div>
										<?php } ?>
										  <div class="ecalypse_rental_fleet_parameter_type_block type_range"<?php echo ($edit == true && $detail->type == 1) || !$edit ? '' : ' style="display:none;"'; ?>>
											   <!-- Range from //-->
												<div class="form-group type-onetime">
												  <label for="ecalypse-rental-fleet-parameter-range-from" class="col-sm-3 control-label"><?php _e('Range from', 'ecalypse-rental');?></label>
												  <div class="col-sm-9">
														  <div class="input-group">
															  <input type="text" name="range_from" class="form-control" id="ecalypse-rental-fleet-parameter-range-from" value="<?= (($edit == true) ? $detail->range_from : '') ?>">
														  </div>
												  </div>
												</div>
											   
											   <!-- Range from //-->
												<div class="form-group type-onetime">
												  <label for="ecalypse-rental-fleet-parameter-range-to" class="col-sm-3 control-label"><?php _e('Range to', 'ecalypse-rental');?></label>
												  <div class="col-sm-9">
														  <div class="input-group">
															  <input type="text" name="range_to" class="form-control" id="ecalypse-rental-fleet-parameter-range-to" value="<?= (($edit == true) ? $detail->range_to : '') ?>">
														  </div>
												  </div>
												</div>
										  </div>
										  
										  <div class="ecalypse_rental_fleet_parameter_type_block type_values"<?php echo ($edit == true && $detail->type == 2) ? '' : ' style="display:none;"'; ?>>
											  
										  

										<!-- values //-->
										<div class="form-group">
											<label class="col-sm-3 control-label"><?php _e('Values', 'ecalypse-rental');?></label>

											<div class="col-sm-9">
												<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
													<li role="presentation" class="active"><a href="javascript:void(0);" class="edit_fleet_parameter_value" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
													<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
													<?php if ($available_languages && !empty($available_languages)) { ?>														
														<?php foreach ($available_languages as $key => $val) { ?>
															<?php if ($val['country-www'] == 'gb') {continue;} ?>
															<li role="presentation"><a href="javascript:void(0);" class="edit_fleet_parameter_value" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
														<?php } ?>
													<?php } ?>
												</ul>
												
												<div id="fleet_parameter_values_sort_gb" class="fleet_parameter_values_tab" data-lng="gb">
													<div id="ecalypse-rental-fleet-parameter-value-gb" style="display: none;">
														<!-- parameter value row //-->
														<div class="row" style="position: relative;" class="sortable" data-row-i="0">
															<div class="col-xs-3">
																<div class="">
																	<input type="text" name="values[gb][0]" class="form-control fleet-parameter-value" placeholder="Parameter value">
																</div>
															</div>															
															<div class="col-xs-1">
																<div class="form-group has-feedback">
																	<span class="glyphicon glyphicon-remove fleet-delete-parameter-value" style="margin-top:9px;margin-left: 5px;cursor:pointer;" title="Remove this value!"></span>
																</div>
															</div>
														</div><!-- .row //-->
													</div>
													<?php if ($edit == true && isset($detail->values) && !empty($detail->values)) { ?>
														<?php $values = unserialize($detail->values); ?>														
														<?php if (!isset($values['gb'])) { $values['gb'] = array();} ?>
														<?php foreach ($values['gb'] as $key => $val) { ?>
															<!-- Additional parameter row //-->
															<div class="row" style="position: relative;" class="sortable" data-row-i="<?php echo $key;?>">
																<div class="col-xs-3">
																	<div class="">
																		<input type="text" name="values[gb][<?php echo $key;?>]" class="form-control fleet-parameter-value" placeholder="Parameter value" value="<?php echo $val;?>">
																	</div>
																</div>
																<div class="col-xs-1">		
																	<div class="form-group has-feedback">
																		<span class="glyphicon glyphicon-remove fleet-delete-parameter-value" style="margin-top:9px;margin-left: 5px;cursor:pointer;" title="Remove this value!"></span>
																	</div>
																</div>
															</div><!-- .row //-->														
														<?php } ?>
													<?php } ?>
													
													<div id="ecalypse-rental-fleet-parameter-values-insert-gb"></div>
												</div>
												
												<?php if ($available_languages && !empty($available_languages)) { ?>														
													<?php foreach ($available_languages as $key => $val) { ?>
														<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div id="fleet_parameter_values_sort_<?php echo $val['country-www'];?>" class="fleet_parameter_values_tab" data-lng="<?php echo $val['country-www'];?>" style="display: none;">
															<div id="ecalypse-rental-fleet-parameter-value-<?php echo $val['country-www'];?>" style="display: none;">
																<!-- parameter value row //-->
																<div class="row" style="position: relative;" class="sortable" data-row-i="0">
																	<div class="col-xs-3">
																		<div class="">
																			<input type="text" name="values[<?php echo $val['country-www'];?>][0]" class="form-control fleet-parameter-value" placeholder="Parameter value">																			
																		</div>
																	</div>
																	<div class="col-xs-1">
																		<div class="form-group has-feedback">
																			<span class="glyphicon glyphicon-remove fleet-delete-parameter-value" style="margin-top:9px;margin-left: 5px;cursor:pointer;" title="Remove this value!"></span>
																		</div>
																	</div>
																</div><!-- .row //-->
															</div>
															<?php if ($edit == true && isset($detail->values) && !empty($detail->values)) { ?>
																<?php $values = unserialize($detail->values); ?>
																<?php if (!isset($values[$val['country-www']])) { $values[$val['country-www']] = array();} ?>
																<?php foreach ($values[$val['country-www']] as $pkey => $pval) { ; ?>
																	<!-- parameter value row //-->
																	<div class="row" style="position: relative;" class="sortable" data-row-i="<?php echo $pkey;?>">
																		<div class="col-xs-3">
																			<div class="">
																				<input type="text" name="values[<?php echo $val['country-www'];?>][<?php echo $pkey;?>]" class="form-control fleet-parameter-value" placeholder="Parameter value" value="<?php echo $pval;?>">
																			</div>
																		</div>
																		
																		<div class="col-xs-1">
																			<div class="form-group has-feedback">
																				<span class="glyphicon glyphicon-remove fleet-delete-parameter-value" style="margin-top:9px;margin-left: 5px;cursor:pointer;" title="Remove this value!"></span>
																			</div>
																		</div>
																	</div><!-- .row //-->														
																<?php } ?>
															<?php } ?>

															<div id="ecalypse-rental-fleet-parameter-values-insert-<?php echo $val['country-www'];?>"></div>
														</div>
													<?php } ?>
												<?php } ?>
												
												<a href="javascript:void(0);" id="ecalypse-rental-add-fleet-parameter-value" class="btn btn-info btn-xs"><?php _e('Add New Value', 'ecalypse-rental');?></a>
											</div>
										</div>
										</div>
										 
										 <!-- Active //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-active" class="col-sm-3 control-label">Active</label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												  <input type="radio" name="active" value="1" <?= (($detail->active == 1 || !$edit) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
												  <input type="radio" name="active" value="0" <?= ((isset($detail->active) && $detail->active == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('No', 'ecalypse-rental');?>
												</label>
									    </div>
									  </div>
										 
										  <!-- Use as default filter //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-filter" class="col-sm-3 control-label"><?php _e('Use as default filter', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												  <input type="radio" name="filter" value="1" <?= (($detail->filter == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
												  <input type="radio" name="filter" value="0" <?= ((isset($detail->filter) && $detail->filter == 0) || !$edit ? 'checked="checked"' : '') ?>>&nbsp;&nbsp; <?php _e('No', 'ecalypse-rental');?>
												</label>
									    </div>
									  </div>


										<!-- Submit //-->
										<div class="form-group">
											<div class="col-sm-offset-3 col-sm-9">
												<?php wp_nonce_field( 'add_fleet_parameter'); ?>
<?php if ($edit == true) { ?>
													<input type="hidden" name="id_fleet_parameter" value="<?= $detail->id_fleet_parameter ?>">
													<button type="submit" class="btn btn-warning" name="add_fleet_parameter"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
												<?php } else { ?>
													<button type="submit" class="btn btn-warning" name="add_fleet_parameter"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm ', 'ecalypse-rental');?>&amp; <?php _e('Add', 'ecalypse-rental');?></button>
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

						<?php if (isset($params) && !empty($params)) { ?>

							<table class="table table-striped" id="ecalypse-rental-fleet-parameters">
								<thead>
									<tr>
										<th>#</th>
										<th><?php _e('Name', 'ecalypse-rental');?></th>
										<th><?php _e('Type', 'ecalypse-rental');?></th>
										<th><?php _e('Action', 'ecalypse-rental');?></th>
									</tr>
								</thead>
								<tbody>
	<?php foreach ($params as $key => $val) { ?>
										<tr fleetParameterId="<?php echo $val->id_fleet_parameter; ?>">
											<td>
												<?php /*<input type="checkbox" class="input-control batch_processing" name="batch[]" value="<?= $val->id_fleet_parameter ?>">&nbsp;*/?>
												<?php echo $val->id_fleet_parameter; ?>
											</td>
											<td class="sortableTD">
												<?php $name = unserialize($val->name);?>
												<strong><?= (!empty($val->name) ? $name['gb'] : '- Unknown -') ?></strong>
											</td>
											<td>
												<?php echo $types[$val->type];?>
											</td>
											<td>
												<form action="" method="post" class="form form-inline" role="form" style="float: left;margin-right: 10px;">
													<div class="form-group">
														<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-fleet-parameters')); ?>&amp;edit=<?= $val->id_fleet_parameter ?>" class="btn btn-primary btn-xs"><?php _e('Modify', 'ecalypse-rental');?></a>
													</div>
												</form>
												<form action="" method="post" class="form form-inline" role="form" style="float: left;margin-right: 10px;">
													<div class="form-group">
														<input type="hidden" name="id_fleet_parameter" value="<?= $val->id_fleet_parameter ?>">
														<?php wp_nonce_field( 'copy_fleet_parameter'); ?>
														<button name="copy_fleet_parameter" class="btn btn-xs btn-warning">Copy</button>
													</div>
												</form>
												<form style="float: left;margin-right: 10px;" action="" method="post" class="form form-inline" role="form" onsubmit="return confirm('<?= __('Do you really want to delete this parameter?', 'ecalypse-rental') ?>');">
														<div class="form-group">
															<input type="hidden" name="id_fleet_parameter" value="<?= $val->id_fleet_parameter ?>">
															<?php wp_nonce_field( 'delete_fleet_parameter'); ?>
															<button name="delete_fleet_parameter" class="btn btn-xs btn-danger"><?php _e('Delete', 'ecalypse-rental');?></button>
														</div>
													</form>
											</td>
										</tr>

	<?php } ?>
								</tbody>
							</table>

						<?php } else { ?>
							<div class="alert alert-info">
								<span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;
	<?= esc_html__('You do not have any Parameters created yet, please create one clicking on "Add New parameter".', 'ecalypse-rental'); ?>
							</div>
<?php } ?>

					</div>
				</div>
			</div>
		</div>
	</div>

</div>
<script language="JavaScript">
	
	jQuery(document).ready(function() {
	
	});
</script>
