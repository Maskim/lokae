<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit; 
?>
<div class="container-fluid">
	
	<?php include ECALYPSERENTALSTARTER__PLUGIN_DIR . 'views/header.php'; ?>
	
	<div class="row">
	
		<div class="col-md-12 ecalypse-rental-main-wrapper">
			<div class="ecalypse-rental-main-content">
				
				<?php include ECALYPSERENTALSTARTER__PLUGIN_DIR . 'views/flash_msg.php'; ?>
				  		
				<div class="row">
					<div class="col-md-12">
						<?php if ($edit == true) { ?>
							<h3><?php _e('Edit Branch no.', 'ecalypse-rental');?> <?= $detail->id_branch ?></h3>
						<?php } else { ?>
							<?php if (isset($_GET['deleted'])) { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-branches')); ?>" class="btn btn-default" style="float:right;"><?php _e('Show normal', 'ecalypse-rental');?></a>
							<?php } else { ?>
								<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-branches')); ?>&amp;deleted" class="btn btn-default" style="float:right;"><?php _e('Show deleted', 'ecalypse-rental');?></a>
							<?php } ?>
							
							<a href="javascript:void(0);" class="btn btn-success" id="ecalypse-rental-branches-add-button"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;<?php _e('Add new branch', 'ecalypse-rental');?></a>
						<?php } ?>
						
						<div id="<?= (($edit == true) ? 'ecalypse-rental-branches-edit-form' : 'ecalypse-rental-branches-add-form') ?>" class="ecalypse-rental-add-form">
							<form action="" method="post" role="form" class="form-horizontal" enctype="multipart/form-data">
								<div class="row">
									<div class="col-md-11">
										
										<div class="alert alert-info">
											<p><span class="glyphicon glyphicon-share-alt"></span>&nbsp;&nbsp;<?php _e('Whichever field is left blank will not be used in branch detail.', 'ecalypse-rental');?></p>
										</div>
										
										<?php if ($edit == true) { ?>
											<?php $translations = unserialize($detail->translations);?>
											<?php if (empty($translations)) { $translations = array(); } ?>
										<?php } ?>
										
										<div class="form-group">
											<div class="col-sm-3"></div>
											<div class="col-sm-9">
												<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
													<li role="presentation" class="active"><a href="javascript:void(0);" class="edit_branch" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
													<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
													<?php if ($available_languages && !empty($available_languages)) { ?>														
														<?php foreach ($available_languages as $key => $val) { ?>
															<?php if ($val['country-www'] == 'gb') {continue;} ?>
															<li role="presentation"><a href="javascript:void(0);" class="edit_branch" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
														<?php } ?>
													<?php } ?>
												</ul>
											</div>
										</div>

										<!-- Name //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-name" class="col-sm-3 control-label"><?php _e('Name', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_branch_translations" data-lng="gb">
												<input type="text" name="name" class="form-control" id="ecalypse-rental-name" value="<?= (($edit == true) ? $detail->name : '') ?>">
											</div>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_branch_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<input type="text" name="translations[name][<?php echo $val['country-www'];?>]" placeholder="Name in <?php echo $val['lang-native'];?>" class="form-control" value="<?= (($edit == true && isset($translations['name']) && isset($translations['name'][$val['country-www']])) ? $translations['name'][$val['country-www']] : '') ?>">
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									  
									  <!-- Internal ID //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-bid" class="col-sm-3 control-label"><?php _e('Internal ID', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="bid" class="form-control" id="ecalypse-rental-bid" value="<?= (($edit == true) ? $detail->bid : '') ?>">
											<p class="help-block"><?php _e('If using TSDweb extension, insert your TSD branch ID here; else, use for internal records.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
									  <!-- Country //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-country" class="col-sm-3 control-label"><?php _e('Country', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<select name="country" class="form-control" id="ecalypse-rental-country">
										    	<option value="">- <?php _e('select', 'ecalypse-rental');?> -</option>
										    	<?php $countries = EcalypseRental_Admin::get_country_list(); ?>
										    	<?php foreach ($countries as $key => $val) { ?>
										    		<option value="<?= $key ?>" <?= (($edit == true && $key == $detail->country) ? 'selected="selected"' : '') ?>><?= $val ?></option>
										    	<?php } ?>
									    	</select>
									    </div>
									  </div>
									  
									  <!-- State/Province //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-state" class="col-sm-3 control-label"><?php _e('State / Province', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_branch_translations" data-lng="gb">
												<input type="text" name="state" class="form-control" id="ecalypse-rental-state" value="<?= (($edit == true) ? $detail->state : '') ?>">
											</div>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_branch_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<input type="text" name="translations[state][<?php echo $val['country-www'];?>]" placeholder="State / Province in <?php echo $val['lang-native'];?>" class="form-control" value="<?= (($edit == true && isset($translations['state']) && isset($translations['state'][$val['country-www']])) ? $translations['state'][$val['country-www']] : '') ?>">
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									  
									  <!-- City //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-city" class="col-sm-3 control-label"><?php _e('City', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_branch_translations" data-lng="gb">
												<input type="text" name="city" class="form-control" id="ecalypse-rental-city" placeholder="Prague, London, Los Angeles, ..." value="<?= (($edit == true) ? $detail->city : '') ?>">
											</div>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_branch_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<input type="text" name="translations[city][<?php echo $val['country-www'];?>]" placeholder="City in <?php echo $val['lang-native'];?>" class="form-control" value="<?= (($edit == true && isset($translations['city']) && isset($translations['city'][$val['country-www']])) ? $translations['city'][$val['country-www']] : '') ?>">
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									  
									  <!-- ZIP //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-zip" class="col-sm-3 control-label"><?php _e('ZIP Code', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="zip" class="form-control" id="ecalypse-rental-zip" value="<?= (($edit == true) ? $detail->zip : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Street //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-street" class="col-sm-3 control-label"><?php _e('Street', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_branch_translations" data-lng="gb">
												<input type="text" name="street" class="form-control" id="ecalypse-rental-street" value="<?= (($edit == true) ? $detail->street : '') ?>">
											</div>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_branch_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<input type="text" name="translations[street][<?php echo $val['country-www'];?>]" placeholder="Street <?php echo $val['lang-native'];?>" class="form-control" value="<?= (($edit == true && isset($translations['street']) && isset($translations['street'][$val['country-www']])) ? $translations['street'][$val['country-www']] : '') ?>">
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									  
									  <!-- GPS //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-gps" class="col-sm-3 control-label">GPS</label>
									    <div class="col-sm-9">
									    	<input type="text" name="gps" class="form-control" id="ecalypse-rental-gps" value="<?= (($edit == true) ? $detail->gps : '') ?>">
											<p class="help-block"><?php _e('Insert GPS in format:', 'ecalypse-rental');?> 27.762631, -15.576905.</p>
									    </div>
									  </div>
									  
									  <!-- Contact e-mail //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-email" class="col-sm-3 control-label"><?php _e('Contact e-mail', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="email" class="form-control" id="ecalypse-rental-email" value="<?= (($edit == true) ? $detail->email : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Contact phone //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-phone" class="col-sm-3 control-label"><?php _e('Contact phone', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<input type="text" name="phone" class="form-control" id="ecalypse-rental-phone" value="<?= (($edit == true) ? $detail->phone : '') ?>">
									    </div>
									  </div>
									  
									  <!-- Description //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-description" class="col-sm-3 control-label"><?php _e('Description', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
											<div class="ecalypse_rental_branch_translations" data-lng="gb">
												<textarea class="form-control" name="description" id="ecalypse-rental-description" rows="3"><?= (($edit == true) ? $detail->description : '') ?></textarea>
											</div>
											<?php if ($available_languages && !empty($available_languages)) { ?>														
												<?php foreach ($available_languages as $key => $val) { ?>
													<?php if ($val['country-www'] == 'gb') {continue;} ?>
														<div class="ecalypse_rental_branch_translations" data-lng="<?php echo $val['country-www'];?>" style="display:none;">
															<textarea class="form-control" name="translations[description][<?php echo $val['country-www'];?>]" rows="3" placeholder="Description <?php echo $val['lang-native'];?>"><?= (($edit == true && isset($translations['description']) && isset($translations['description'][$val['country-www']])) ? $translations['description'][$val['country-www']] : '') ?></textarea>
														</div>
												<?php } ?>
											<?php } ?>
									    </div>
									  </div>
									  	
									  <!-- Business hours //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-description" class="col-sm-3 control-label"><?php _e('Business hours', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<table class="table ecalypse-rental-business-hours">
									    		<?php foreach (array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') as $key => $val) { ?>
									    			<tr>
										    			<td><?= $val ?></td>
										    			<td>&nbsp;<?php _e('from', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="hours[from][<?= $key ?>]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true) ? $detail->hours[$key+1]['hours_from'] : '') ?>"></td>
										    			<td>&nbsp;<?php _e('to', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="hours[to][<?= $key ?>]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true) ? $detail->hours[$key+1]['hours_to'] : '') ?>"></td>
															<td><strong><?php _e('AND', 'ecalypse-rental');?></strong></td>
															<td>&nbsp;<?php _e('from', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="hours[from_2][<?= $key ?>]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true) ? $detail->hours[$key+1]['hours_from_2'] : '') ?>"></td>
										    			<td>&nbsp;<?php _e('to', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="hours[to_2][<?= $key ?>]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true) ? $detail->hours[$key+1]['hours_to_2'] : '') ?>"></td>
										    		</tr>
									    		<?php } ?>
									    	</table>
											<p class="help-block"><?php _e('Use 24hr format to insert time; to change how time is displayed to clients, go to theme settings->time format and change it to 12hr or 24hr.', 'ecalypse-rental');?></p>
									    	<p class="help-block">* <?php _e('Leave blank if branch closed.', 'ecalypse-rental');?></p>
											<div class="row" style="margin:0; border-top:1px solid #ddd;border-bottom:0px solid #ddd;padding: 10px 0;">
												<label><input type="checkbox" name="specific_times" id="ecalypse-rental-specific-times-checkbox" value="1"<?= ($edit == true && $detail->specific_times == 1) ? ' checked="checked"' : '';?>> <?php _e('If you want pick up and return times to be restricted to specific times, check this box and insert values below.', 'ecalypse-rental');?></label>
											</div>
									    </div>
									  </div>
									  
									  <!-- Strick pick up times //-->
									  <div class="form-group strict-pick-up"<?= ($edit == true && $detail->specific_times == 1) ? '' : ' style="display:none;"';?>>
									    <label class="col-sm-3 control-label"><?php _e('Strick pick up times', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<table class="table ecalypse-rental-business-hours">
									    		<?php foreach (array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') as $key => $val) { ?>
									    			<tr>
										    			<td><?= $val ?></td>
										    			<td>&nbsp;<?php _e('from', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="enter_hours[<?= $key ?>][from]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true && isset($detail->enter_hours[$key])) ? $detail->enter_hours[$key]['from'] : '') ?>"></td>
										    			<td>&nbsp;<?php _e('to', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="enter_hours[<?= $key ?>][to]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true && isset($detail->enter_hours[$key])) ? $detail->enter_hours[$key]['to'] : '') ?>"></td>
															
															<td><strong>AND</strong></td>
										    			<td>&nbsp;<?php _e('from', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="enter_hours[<?= $key ?>][from_2]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true && isset($detail->enter_hours[$key])) ? $detail->enter_hours[$key]['from_2'] : '') ?>"></td>
										    			<td>&nbsp;<?php _e('to', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="enter_hours[<?= $key ?>][to_2]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true && isset($detail->enter_hours[$key])) ? $detail->enter_hours[$key]['to_2'] : '') ?>"></td>
										    		</tr>
									    		<?php } ?>
									    	</table>
											<p class="help-block"><?php _e('Use 24hr format to insert time; to change how time is displayed to clients, go to theme settings->time format and change it to 12hr or 24hr.', 'ecalypse-rental');?></p>
									    	<p class="help-block">* <?php _e('Leave blank if branch closed.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
									   <!-- Strick return times //-->
									  <div class="form-group strict-pick-up"<?= ($edit == true && $detail->specific_times == 1) ? '' : ' style="display:none;"';?>>
									    <label class="col-sm-3 control-label"><?php _e('Strick return times', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<table class="table ecalypse-rental-business-hours">
									    		<?php foreach (array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') as $key => $val) { ?>
									    			<tr>
										    			<td><?= $val ?></td>
										    			<td>&nbsp;<?php _e('from', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="return_hours[<?= $key ?>][from]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true && isset($detail->return_hours[$key])) ? $detail->return_hours[$key]['from'] : '') ?>"></td>
										    			<td>&nbsp;<?php _e('to', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="return_hours[<?= $key ?>][to]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true && isset($detail->return_hours[$key])) ? $detail->return_hours[$key]['to'] : '') ?>"></td>
															
															<td><strong><?php _e('AND', 'ecalypse-rental');?></strong></td>
										    			<td>&nbsp;<?php _e('from', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="return_hours[<?= $key ?>][from_2]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true && isset($detail->return_hours[$key])) ? $detail->return_hours[$key]['from_2'] : '') ?>"></td>
										    			<td>&nbsp;<?php _e('to', 'ecalypse-rental');?>&nbsp;</td>
															<td><input type="text" name="return_hours[<?= $key ?>][to_2]" class="form-control" size="2" placeholder="HH:MM" value="<?= (($edit == true && isset($detail->return_hours[$key])) ? $detail->return_hours[$key]['to_2'] : '') ?>"></td>
										    		</tr>
									    		<?php } ?>
									    	</table>
											<p class="help-block"><?php _e('Use 24hr format to insert time; to change how time is displayed to clients, go to theme settings->time format and change it to 12hr or 24hr.', 'ecalypse-rental');?></p>
									    	<p class="help-block">* <?php _e('Leave blank if branch closed.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									   
									   <!-- Price for booking outside business hours //-->
									   <div class="form-group disabled">
										<label for="ecalypse_rental_booking_outside_price" class="col-sm-3 control-label"><?php _e('Price for booking outside business hours', 'ecalypse-rental');?></label>
										<div class="col-sm-9">
											<h4 style="color: #000;margin-top: 0px; margin-bottom: 20px;">This feature is available in full version of Ecalypse Rental Plugin. <a href="https://wp.ecalypse.com/" target="_blank">Get it here</a></h4>
											<?php $currency = get_option('ecalypse_rental_global_currency'); ?>
											<?php if ($currency && !empty($currency) && strlen($currency) == 3) { ?>
												<div class="row">
													<div class="col-xs-3"><input disabled="disabled" type="text" name="" class="form-control" id="" value="<?= (($edit == true) ? $detail->outside_price : '') ?>"></div>
													<div class="col-xs-1"><h4><?= $currency ?></h4></div>
													</div>
													<p class="help-block"><?php _e('If customer chooses enter or return time outside business hours then this price will be added.', 'ecalypse-rental');?><br><?php _e('Please insert just a number (float possible).', 'ecalypse-rental');?></p>
											<?php } else { ?>
												<div class="alert alert-warning" role="alert">
														<?php _e('You can set-up price for booking outside business hours, but first, set-up Global Currency.', 'ecalypse-rental');?>
													</div>
											<?php } ?>
										</div>
									  </div>
									  
									  <!-- Picture of branch //-->
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
									    	<p><strong><?php _e('Or you can delete current picture for Branch:', 'ecalypse-rental');?></strong></p>
									    	<label><input type="checkbox" class="input-control" name="delete_picture" value="1">&nbsp;&nbsp;<?php _e('Delete picture', 'ecalypse-rental');?></label>
									    </div>
									  </div>
									  
									  <!-- Active //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-active" class="col-sm-3 control-label"><?php _e('List branch', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												  <input type="radio" name="active" id="ecalypse-rental-active" value="1" <?= (($edit == true && $detail->active == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
												  <input type="radio" name="active" id="ecalypse-rental-active" value="0" <?= (($edit == true && $detail->active == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('No', 'ecalypse-rental');?>
												</label>
												<p class="help-block"><?php _e('This will make the branch active or inactive on the front end.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
									  <!-- Default //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-is_default" class="col-sm-3 control-label"><?php _e('Default branch', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												  <input type="radio" name="is_default" id="ecalypse-rental-active" value="1" <?= (($edit == true && $detail->is_default == 1) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
												  <input type="radio" name="is_default" id="ecalypse-rental-active" value="0" <?= (($edit == true && $detail->is_default == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('No', 'ecalypse-rental');?>
												</label>
												<p class="help-block"><?php _e('Default branch is listed as the first at the front end.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
									  <!-- Show location //-->
									  <div class="form-group">
									    <label for="ecalypse-rental-show_location" class="col-sm-3 control-label"><?php _e('List branch on the location page', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
									    	<label class="radio-inline">
												  <input type="radio" name="show_location" id="ecalypse-rental-show_location" value="1" <?= (($edit == true && $detail->show_location == 1) || !$edit ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('Yes', 'ecalypse-rental');?>
												</label>
												<label class="radio-inline">
												  <input type="radio" name="show_location" id="ecalypse-rental-show_location" value="0" <?= (($edit == true && $detail->show_location == 0) ? 'checked="checked"' : '') ?>>&nbsp;&nbsp;<?php _e('No', 'ecalypse-rental');?>
												</label>
												<p class="help-block"><?php _e('This will make the branch active or inactive on the our location page.', 'ecalypse-rental');?></p>
									    </div>
									  </div>
									  
									  <div class="form-group">
									    <label for="ecalypse-rental-branch_specific_taxes" class="col-sm-3 control-label"><?php _e('Branch specific taxes', 'ecalypse-rental');?></label>
									    <div class="col-sm-9">
					
												<ul class="nav nav-tabs" role="tablist" style="margin-bottom:10px;">
													<li role="presentation" class="active"><a href="javascript:void(0);" class="ecalypse_rental_branch_taxes_switcher" data-value="gb"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
													<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
													<?php if ($available_languages && !empty($available_languages)) { ?>														
														<?php foreach ($available_languages as $key => $val) { ?>
															<?php if ($val['country-www'] == 'gb') {continue;} ?>
															<li role="presentation"><a href="javascript:void(0);" class="ecalypse_rental_branch_taxes_switcher" data-value="<?= strtolower($val['country-www']) ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
														<?php } ?>
													<?php } ?>
												</ul>
											
												  <table class="table" id="ecalypse-rental-branch-taxes">
												  <thead>
													<tr>
													  <th><?php _e('Name', 'ecalypse-rental');?></th>
													  <th><?php _e('Tax rate', 'ecalypse-rental');?></th>
													  <th><?php _e('Delete', 'ecalypse-rental');?></th>
													</tr>
												  </thead>
												  <tbody>
													  <tr class="disp_none ecalypse_rental_tax_referer">
														<td>
															<div class="ecalypse_rental_branch_taxes_translations" data-lng="gb">
																<input type="text" name="branch_tax[0][name]" class="form-control" value="" placeholder="Name in english">
															</div>
															<?php if ($available_languages && !empty($available_languages)) { ?>
																<?php foreach ($available_languages as $lkey => $lval) { ?>
																	<?php if ($lval['country-www'] == 'gb') {continue;} ?>
																		<div class="ecalypse_rental_branch_taxes_translations" data-lng="<?php echo $lval['country-www'];?>" style="display:none;">																		
																			<input type="text" name="branch_tax[0][<?php echo $lval['country-www'];?>]" placeholder="Name in <?php echo $lval['lang-native'];?>" class="form-control" value="">
																		</div>
																<?php } ?>
															<?php } ?>
															</td>
														<td>
															<input type="text" placeholder="Tax rate" name="branch_tax[0][tax]"> %
														</td>
														<td>
															<a href="#" class="ecalypse_rental_branch_delete_tax"><?php _e('Delete this tax', 'ecalypse-rental');?></a>
															</td>
													</tr>
													  <?php $taxes = $edit === true ? maybe_unserialize($detail->branch_tax) : array();?>
													<?php if ($taxes && !empty($taxes)) { ?>
															<?php 
															$i = 0;
															foreach ($taxes as $key => $val) { 
																$i++;
																?>
																<tr class="tax-row" data-id="<?php echo $i;?>">
																	<td>
																		<div class="ecalypse_rental_branch_taxes_translations" data-lng="gb">
																			<input type="text" name="branch_tax[<?= $i ?>][name]" class="form-control" value="<?= $val['name'] ?>">
																		</div>
																		<?php $name_translations = $val['name_translations'];?>
																		<?php if (empty($name_translations)) { $name_translations = array(); } ?>
																		<?php if ($available_languages && !empty($available_languages)) { ?>
																			<?php foreach ($available_languages as $lkey => $lval) { ?>
																				<?php if ($lval['country-www'] == 'gb') {continue;} ?>
																					<div class="ecalypse_rental_branch_taxes_translations" data-lng="<?php echo $lval['country-www'];?>" style="display:none;">																		
																						<input type="text" name="branch_tax[<?= $i; ?>][name_translations][<?php echo $lval['country-www'];?>]" placeholder="Name in <?php echo $lval['lang-native'];?>" class="form-control" value="<?= ((isset($name_translations[$lval['country-www']])) ? $name_translations[$lval['country-www']] : '') ?>">
																					</div>
																			<?php } ?>
																		<?php } ?>
																		</td>
																	<td>
																		<input type="text" placeholder="Tax rate" name="branch_tax[<?php echo $i;?>][tax]" value="<?= $val['tax'] ?>"> %
																	</td>
																	<td>
																		<a href="#" class="ecalypse_rental_branch_delete_tax"><?php _e('Delete this tax', 'ecalypse-rental');?></a>
																		</td>
																</tr>
															<?php } ?>
														<?php } ?>

												</tbody>
											</table>
													<a href="submit" class="btn btn-success ecalypse_rental_branch_add_new_tax"><?php _e('Add new tax', 'ecalypse-rental');?></a>
										  </div>
										</div>
									  
									  <!-- Submit //-->
									  <div class="form-group">
									  	<div class="col-sm-offset-3 col-sm-9">
											<?php wp_nonce_field( 'add_branch'); ?>
									  		<?php if ($edit == true) { ?>
									  			<input type="hidden" name="id_branch" value="<?= $detail->id_branch ?>">
									  			<input type="hidden" name="current_picture" value="<?= $detail->picture ?>">
									  			<button type="submit" class="btn btn-warning" name="add_branch"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									  		<?php } else { ?>
									  			<button type="submit" class="btn btn-warning" name="add_branch"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Add', 'ecalypse-rental');?></button>
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
						
						<?php if (isset($branches) && !empty($branches)) { ?>
							<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-branches" /> <?php _e('Select all', 'ecalypse-rental');?></label>
							<table class="table table-striped" id="ecalypse-rental-branches">
					      <thead>
					        <tr>
					          <th>#</th>
					          <th><?php _e('Image', 'ecalypse-rental');?></th>
					          <th><?php _e('Name', 'ecalypse-rental');?></th>
					          <th><?php _e('Address', 'ecalypse-rental');?></th>
					          <th><?php _e('Description', 'ecalypse-rental');?></th>
					          <th><?php _e('Business hours', 'ecalypse-rental');?></th>
					          <th><?php _e('Action', 'ecalypse-rental');?></th>
					        </tr>
					      </thead>
					      <tbody>
					      	<?php foreach ($branches as $key => $val) { ?>
					      		<tr orderId="<?php echo $val->id_branch;?>">
						          <td class="sortableTD">
												<input type="checkbox" class="input-control batch_processing" name="batch[]" value="<?= $val->id_branch ?>">&nbsp;
												<abbr title="Created: <?= $val->created ?>

<?= (!empty($val->updated) ? 'Updated: ' . $val->updated : '') ?>"><?= $val->id_branch ?></abbr>
											</td>
						          <td class="sortableTD"><img src="<?= $val->picture ?>" height="120"></td>
						          <td class="sortableTD"><strong><?= (!empty($val->name) ? $val->name : '- Unknown -') ?></strong><?php if ($val->active == 0) { ?><br><em class="branch-not-listed">(not listed)</em><?php } ?></td>
						          <td class="sortableTD">
						          	<?= (!empty($val->city) ? $val->city . '<br>' : '') ?>
												<?= (!empty($val->street) ? $val->street . '<br>' : '') ?>
												<?= (!empty($val->zip) ? $val->zip . '<br>' : '') ?>
												<?= (!empty($val->country) ? $countries[$val->country] . '<br>' : '') ?>
												<?= (!empty($val->state) ? $val->state : '') ?>
												<br>
												<?= (!empty($val->email) ? $val->email . '<br>' : '') ?>
												<?= (!empty($val->phone) ? $val->phone . '<br>' : '') ?>
											</td>
											<td class="sortableTD"><p style="max-width:200px;"><?= $val->description ?></p></td>
											<td>
												<table class="table">
													<?php if (isset($val->hours) && !empty($val->hours)) { ?>
														<?php foreach ($val->hours as $kD => $vD) { ?>
															<tr>
																<td><?= EcalypseRental_Admin::get_day_name($vD->day) ?></td>
																<td><?= substr($vD->hours_from, 0, 5) ?></td>
																<td><?= substr($vD->hours_to, 0, 5) ?></td>
															</tr>
														<?php } ?>
													<?php } ?>
												</table>
											</td>
						          <td>
												<form action="" method="post" class="form" role="form">
													<div class="form-group">
														<a href="<?= esc_url(EcalypseRental_Admin::get_page_url('ecalypse-rental-branches')); ?>&amp;edit=<?= $val->id_branch ?>" class="btn btn-primary btn-block">Modify</a>
													</div>
												</form>
												<form action="" method="post" class="form" role="form">
													<div class="form-group">
														<input type="hidden" name="id_branch" value="<?= $val->id_branch ?>">
														<?php wp_nonce_field( 'copy_branch'); ?>
														<button name="copy_branch" class="btn btn-warning btn-block"><?php _e('Copy', 'ecalypse-rental');?></button>
													</div>
												</form>
												<?php if (isset($_GET['deleted'])) { ?>
													<form action="" method="post" class="form" role="form" onsubmit="return confirm('<?= __('Do you really want to restore this Branch?', 'ecalypse-rental') ?>');">
														<div class="form-group">
															<input type="hidden" name="id_branch" value="<?= $val->id_branch ?>">
															<?php wp_nonce_field( 'restore_branch'); ?>
															<button name="restore_branch" class="btn btn-success btn-block"><?php _e('Restore', 'ecalypse-rental');?></button>
														</div>
													</form>
												<?php } else { ?>
													<form action="" method="post" class="form" role="form" onsubmit="return confirm('<?= __('Do you really want to delete this Branch?', 'ecalypse-rental') ?>');">
														<div class="form-group">
															<input type="hidden" name="id_branch" value="<?= $val->id_branch ?>">
															<?php wp_nonce_field( 'delete_branch'); ?>
															<button name="delete_branch" class="btn btn-danger btn-block"><?php _e('Delete', 'ecalypse-rental');?></button>
														</div>
													</form>
												<?php } ?>
											</td>
						        </tr>
						        
					      	<?php } ?>
					      </tbody>
					    </table>
						<label class="label_select_all"><input type="checkbox" name="select_all" value="1" class="data_table_select_all" data-id="ecalypse-rental-branches" /> <?php _e('Select all', 'ecalypse-rental');?></label>
					    
					    <h4><?php _e('Batch action on selected items', 'ecalypse-rental');?></h4>
					    
					    <form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Branch is selected to copy.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to copy selected Branches?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<input type="hidden" name="batch_processing_values" value="">
									<?php wp_nonce_field( 'batch_copy_branch'); ?>
									<button name="batch_copy_branch" class="btn btn-warning"><?php _e('Copy', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Branches', 'ecalypse-rental');?></button>
								</div>
							</form>
							
					    <form action="" method="post" class="form" role="form" onsubmit="if (jQuery('[name=batch_processing_values]').val() == '') { alert(<?php __('No Branch is selected to delete.', 'ecalypse-rental');?>); return false }; return confirm('<?= __('Do you really want to delete selected Branches?', 'ecalypse-rental') ?>');">
								<div class="form-group">
									<input type="hidden" name="batch_processing_values" value="">
									<?php wp_nonce_field( 'batch_delete_branch'); ?>
									<button name="batch_delete_branch" class="btn btn-danger"><?php _e('Delete', 'ecalypse-rental');?> <span class="batch_processing_count"></span><?php _e('selected Branches', 'ecalypse-rental');?></button>
								</div>
							</form>
							
						<?php } else { ?>
							<div class="alert alert-info">
								<span class="glyphicon glyphicon-info-sign"></span>&nbsp;&nbsp;
								<?= esc_html__( 'You do not have any Branches created yet, please create one clicking on "Add New Branch".', 'ecalypse-rental' ); ?>
							</div>
						<?php } ?>
						
					</div>
				</div>
				
				
				
			</div>
		</div>
	</div>
	
</div>

<script type="text/javascript">
    jQuery(function() {	
		
		var fixHelper = function(e, ui) {
			ui.children().each(function() {
				jQuery(this).width(jQuery(this).width());
			});
			return ui;
		};
		
		jQuery('a.move_button').click(function(){
			return false;
		});
		
		jQuery('#ecalypse-rental-specific-times-checkbox').change(function(){
			if (jQuery(this).is(":checked")) {
				jQuery('.strict-pick-up').show();
			} else {
				jQuery('.strict-pick-up').hide();
			}
		});

		jQuery("table#ecalypse-rental-branches tbody").sortable({
			helper: fixHelper,
			cursor: 'move',
			handle: 'td.sortableTD',
			update:  function(event, ui) {								
					var newOrdering = jQuery(this).sortable('toArray', {attribute: 'orderId'})
					jQuery.ajax({
						url: ajaxurl,
						global: false,
						type: "POST",
						data: ({
							action: 'ecalypse_rental_save_branch_order',
							ordering: newOrdering,
							_wpnonce: '<?php echo wp_create_nonce('ecalypse_rental_save_branch_order');?>'
						}),
						dataType: "script",
						async:true
					});				
				}
		}).disableSelection();		
	});
</script>