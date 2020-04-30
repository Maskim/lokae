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
					<div class="col-md-11">
						
						<?php $current_lang = (isset($_GET['language']) ? $_GET['language'] : NULL); ?>
						<?php $available_languages = unserialize(get_option('ecalypse_rental_available_languages')); ?>
						
						<ul class="nav nav-pills">
							<?php if ($available_languages && !empty($available_languages)) { ?>
							<?php if (isset($available_languages['en_GB'])) {
									unset($available_languages['en_GB']);
							} ?>
								<?php foreach ($available_languages as $key => $val) { ?>
						  		<li <?php if ($current_lang == $key) { ?>class="active"<?php } ?>><a href="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-translations') ?>&amp;language=<?= $key ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</a></li>
								<?php } ?>
						  <?php } ?>
						  
						  <li <?php if ($current_lang == 'en_GB') { ?>class="active"<?php } ?>><a href="<?= EcalypseRental_Admin::get_page_url('ecalypse-rental-translations') ?>&amp;language=en_GB"><?php _e('English (GB)', 'ecalypse-rental');?></a></li>
						  <li><a href="javascript:void(0);" id="ecalypse-rental-language-add-button"><span class="glyphicon glyphicon-plus"></span>&nbsp;&nbsp;<?php _e('Add new language', 'ecalypse-rental');?></a></li>
						  <li><a href="javascript:void(0);" id="ecalypse-rental-language-primary-button"><span class="glyphicon glyphicon-star"></span>&nbsp;&nbsp;<?php _e('Set primary language', 'ecalypse-rental');?></a></li>
						
						</ul>
						
						<div id="ecalypse-rental-language-add-form" class="ecalypse-rental-add-form">
							<form role="form" action="" method="post">
								<div class="row">
									<div class="col-md-6">
									  <div class="form-group">
									    <label for="selectLanguage"><?php _e('Language', 'ecalypse-rental');?></label>
									    <select class="form-control" name="language" id="selectLanguage">
									    	<option value="0">- <?php _e('select', 'ecalypse-rental');?> -</option>
									    	<?php foreach ($languages as $key => $val) { ?>
												<?php if ($key == 'en_GB') { continue; } ?>
									    		<option value="<?= $key ?>"><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</option>
									    	<?php } ?>
									    </select>
									  </div>
										<?php wp_nonce_field( 'add_language'); ?>
									  <button type="submit" class="btn btn-warning" name="add_language"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Add new language', 'ecalypse-rental');?></button>
									</div>
								</div>
							</form>
						</div>
						
						<div id="ecalypse-rental-language-primary-form" class="ecalypse-rental-add-form">
							<form role="form" action="" method="post">
								<div class="row">
									<div class="col-md-6">
									  <div class="form-group">
									    <label for="selectLanguage"><?php _e('Language', 'ecalypse-rental');?></label>
									    <?php
									    	$primary_language = 'en_GB';
												$user_set_language = get_option('ecalypse_rental_primary_language');
												if ($user_set_language && !empty($user_set_language)) {
													$primary_language = $user_set_language;
												}
											?>
											<p><?php _e('Current primary language is:', 'ecalypse-rental');?> <strong><?= $languages[$primary_language]['lang'] ?> (<?= strtoupper($languages[$primary_language]['country-www']) ?>)</strong></p>
									    <select class="form-control" name="language" id="selectLanguage">
									    	<option value="en_GB" <?php if ($primary_language == 'en_GB') { ?>selected<?php } ?>><?php _e('English (UK)', 'ecalypse-rental');?></option>
									    	<?php if ($available_languages && !empty($available_languages)) { ?>
													<?php foreach ($available_languages as $key => $val) { ?>
											  		<option value="<?= $key ?>" <?php if ($primary_language == $key) { ?>selected<?php } ?>><?= $val['lang'] ?> (<?= strtoupper($val['country-www']) ?>)</option>
													<?php } ?>
											  <?php } ?>
									    </select>
									  </div>
										<?php wp_nonce_field( 'primary_language'); ?>
									  <button type="submit" class="btn btn-warning" name="primary_language"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Set language as primary', 'ecalypse-rental');?></button>
									</div>
								</div>
							</form>
						</div>
						
						<hr>
		
						<?php if (!empty($current_lang)) { ?>
							
							<!-- THEME //-->
							<div class="panel panel-warning">
								<div class="panel-heading"><h4><a href="javascript:void(0);" class="ecalypse_rental_translations_theme_toggle"><span>▼</span>&nbsp;&nbsp;<?php _e('Theme translations', 'ecalypse-rental');?></a></h4></div>
							  <div class="panel-body ecalypse_rental_translations_theme">
							  	
							    <form role="form" action="" method="post">
									  
								  	<?php if ($translations_theme && !empty($translations_theme)) { ?>
									  	<?php foreach ($translations_theme as $key => $val) { ?>
										  	<div class="form-group">
										  		<div class="row">
										  			<div class="col-md-6">
										  				<?= htmlspecialchars(stripslashes(str_replace('\\\\','',$key))) ?>
										  			</div>
										  			<div class="col-md-6">
														<input type="hidden" class="form-control" name="translation[key][]" value="<?= htmlspecialchars(stripslashes(str_replace('\\\\','',$key))) ?>">
										  				<input type="text" class="form-control" name="translation[val][]" value="<?= htmlspecialchars(stripslashes(str_replace('\\\\','',$val))) ?>">
										  			</div>
										  		</div>
										  	</div>
									  	<?php } ?>
								  	<?php } else { ?>
										<?php _e('If there are no strings to translate, visit Appearance-> theme settings. This will automatically activate translations in your theme. If problems persist, make sure your DB is using UTF-8 coding.', 'ecalypse-rental');?>
								  	<?php } ?>
								  	
									  <input type="hidden" name="language" value="<?= $current_lang ?>">
									  <?php wp_nonce_field( 'language_save_theme_translations'); ?>
									  <button type="submit" class="btn btn-warning" name="language_save_theme_translations"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									</form>
								</div>
							</div>
							
							<!-- E-MAIL //-->
							<div class="panel panel-warning">	
								<div class="panel-heading"><h4><a href="javascript:void(0);" class="ecalypse_rental_translations_email_customers_toggle"><span>▼</span>&nbsp;&nbsp;<?php _e('E-mail for customers', 'ecalypse-rental');?></a></h4></div>
							  <div class="panel-body ecalypse_rental_translations_email_customers">
							  	
							  	<?php $email_body = get_option('ecalypse_rental_reservation_email_' . $current_lang); ?>
								<?php $email_subject = get_option('ecalypse_rental_reservation_email_subject_' . $current_lang); ?>
							  	
							    <form role="form" action="" method="post">
									  <div class="form-group">
										  <label for="reservation_email_subject">Reservation subject</label>
										  <input class="form-control" type="text" id="reservation_email_subject" name="reservation_email_subject" value="<?php if (!empty($email_subject)) { ?>
<?= $email_subject ?>
<?php } else { ?>
<?php _e('Reservation confirmation #[ReservationNumber]', 'ecalypse-rental');?>
<?php } ?>">
									    <label for="reservation_email"><?php _e('Reservation e-mail', 'ecalypse-rental');?></label>
									    <textarea class="form-control" rows="20" id="reservation_email" name="reservation_email">
<?php if (!empty($email_body)) { ?>
<?= EcalypseRental::removeslashes(stripslashes(str_replace('\\\\','',$email_body))) ?>
<?php } else { ?>
<?php _e('Dear [CustomerName],

thank you for your reservation. Here are your reservation details:
[ReservationDetails]
[ReservationNumber]

You can return to your reservation summary page anytime by going to this link:
[ReservationLink]

We are also sending this information to the email address you have provided.

If you would like to change the reservation details, you can do so by calling our office at:
+123 456 789 or by email example@example.org

[ReservationLinkStart]Click here[ReservationLinkEnd] to print your reservation - takes them to reservation summary print out.

Thank you for your business!', 'ecalypse-rental');?>
<?php } ?>
									    </textarea>
									  </div>
									  <div class="form-group">
									  	<p><strong><?php _e('Available variables', 'ecalypse-rental');?></strong></p>
									  	<ul style="margin-left:20px;list-style-type:circle;">
									  		<li><strong>[CustomerName]</strong> -> John Doe, Phil Smith, ...</li>
											<li><strong>[CustomerEmail]</strong> -> JohnDoe@gmail.com</li>
											<li><strong>[Car]</strong> -> Ford GT, ...</li>
											<li><strong>[pickupdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[dropoffdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[pickup_location]</strong> -> New York, ...</li>
											<li><strong>[dropoff_location]</strong> -> <?php _e('Somewhere in the middle of the nowhere, ...', 'ecalypse-rental');?></li>
											<li><strong>[total_payment]</strong> -> $1574</li>
											<li><strong>[deposit_paid]</strong> -> $574</li>
											<li><strong>[remaining_amount]</strong> -> <?php _e('total_payment - deposit_paid = $1000', 'ecalypse-rental');?></li>
											<li><strong>[customer_comment]</strong> -> <?php _e('comment from step 3', 'ecalypse-rental');?></li>
											<li><strong>[rate]</strong> -> <?php _e('Display the actually rental rate per day', 'ecalypse-rental');?></li>
											<li><strong>[rental_days]</strong> -> <?php _e('Display how many selected rental days', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationDetails]</strong> -> <?php _e('Dates, Address, Selected Car, Price', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationNumber]</strong> -> #123456</li>
									  		<li><strong>[ReservationLink]</strong> -> http://example.org/reservation/123456</li>
									  		<li><strong>[ReservationLinkStart]</strong><?php _e('Any text', 'ecalypse-rental');?><strong>[ReservationLinkEnd]</strong></li>
											<li><strong>[extras]</strong> -> <?php _e('Navigation, Child seat', 'ecalypse-rental');?></li>
											<?php do_action( 'ecalypse_rental_view_shortcodes_for_emails' ); ?>
									  	</ul>
									  </div>
										<p>*<?php _e('You can use HTML tags to format this email.', 'ecalypse-rental');?></p>
									  <input type="hidden" name="language" value="<?= $current_lang ?>">
									  <?php wp_nonce_field( 'language_save_email'); ?>
									  <button type="submit" class="btn btn-warning" name="language_save_email"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									</form>
							  </div>
							</div>
							
							
							<!-- TERMS and CONDITIONS //-->
							<div class="panel panel-warning">	
								<div class="panel-heading"><h4><a href="javascript:void(0);" class="ecalypse_rental_translations_terms_toggle"><span>▼</span>&nbsp;&nbsp;<?php _e('Terms', 'ecalypse-rental');?> &amp; <?php _e('Conditions', 'ecalypse-rental');?></a></h4></div>
							  <div class="panel-body ecalypse_rental_translations_terms">
							  	
							  	<?php $terms_body = get_option('ecalypse_rental_terms_conditions_' . $current_lang); ?>
							  	
							    <form role="form" action="" method="post">
									  <div class="form-group">
									    <label for="terms_conditions"><?php _e('Terms', 'ecalypse-rental');?> &amp; <?php _e('Conditions', 'ecalypse-rental');?></label>
									    <textarea class="form-control" rows="20" id="terms_conditions" name="terms_conditions">
<?php if (!empty($terms_body)) { ?>
<?= EcalypseRental::removeslashes(stripslashes(str_replace('\\\\','',$terms_body))) ?>
<?php } else { ?>
<?php _e('Terms and Conditions', 'ecalypse-rental');?>

...
<?php } ?>
									    </textarea>
									  </div>
									  <p><?php _e('You can use HTML tags to format your terms and conditions here.', 'ecalypse-rental');?></p>
									  <input type="hidden" name="language" value="<?= $current_lang ?>">
									  <?php wp_nonce_field( 'language_save_terms'); ?>
									  <button type="submit" class="btn btn-warning" name="language_save_terms"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									</form>
							  </div>
							</div>
							
							<!-- AUTOMATIC REMINDER EMAIL //-->
							<div class="panel panel-warning">	
								<div class="panel-heading"><h4><a href="javascript:void(0);" class="ecalypse_rental_translations_email_reminder_customers_toggle"><span>▼</span>&nbsp;&nbsp;<?php _e('E-mail for automatic reminder', 'ecalypse-rental');?></a></h4></div>
							  <div class="panel-body ecalypse_rental_translations_email_reminder_customers">
							  	
							  	<?php $email_body = get_option('ecalypse_rental_reminder_email_' . $current_lang); ?>
								 <?php $email_subject = get_option('ecalypse_rental_reminder_subject_' . $current_lang); ?>
							  	
							    <form role="form" action="" method="post">
									  <div class="form-group">
										  <label for="reminder_subject"><?php _e('Automatic reminder subject', 'ecalypse-rental');?></label>
										  <input class="form-control" type="text" id="reminder_subject" name="reminder_subject" value="<?php if (!empty($email_subject)) { ?>
<?= $email_subject ?>
<?php } else { ?>
<?php _e('Reservation reminder', 'ecalypse-rental');?>
<?php } ?>">
										  
									    <label for="reminder_email"><?php _e('Automatic reminder e-mail', 'ecalypse-rental');?></label>
									    <textarea class="form-control" rows="20" id="reminder_email" name="reminder_email">
<?php if (!empty($email_body)) { ?>
<?= EcalypseRental::removeslashes(stripslashes(str_replace('\\\\','',$email_body))) ?>
<?php } else { ?>
<?php _e('Dear [CustomerName],

do not forget on your reservation. Here are your reservation details:
[ReservationDetails]
[ReservationNumber]

You can see your reservation summary page anytime by going to this link:
[ReservationLink]

[ReservationLinkStart]Click here[ReservationLinkEnd] to print your reservation - takes them to reservation summary print out.

Thank you for your business!', 'ecalypse-rental');?>
<?php } ?>
									    </textarea>
									  </div>
									  <div class="form-group">
									  	<p><strong><?php _e('Available variables', 'ecalypse-rental');?></strong></p>
									  	<ul style="margin-left:20px;list-style-type:circle;">
									  		<li><strong>[CustomerName]</strong> -> John Doe, Phil Smith, ...</li>
											<li><strong>[CustomerEmail]</strong> -> JohnDoe@gmail.com</li>
											<li><strong>[Car]</strong> -> Ford GT, ...</li>
											<li><strong>[pickupdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[dropoffdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[pickup_location]</strong> -> New York, ...</li>
											<li><strong>[dropoff_location]</strong> -> <?php _e('Somewhere in the middle of the nowhere', 'ecalypse-rental');?>, ...</li>
											<li><strong>[total_payment]</strong> -> $1574</li>
											<li><strong>[deposit_paid]</strong> -> $574</li>
											<li><strong>[remaining_amount]</strong> -> <?php _e('total_payment - deposit_paid = $1000', 'ecalypse-rental');?></li>
											<li><strong>[customer_comment]</strong> -> <?php _e('comment from step 3', 'ecalypse-rental');?></li>
											<li><strong>[rate]</strong> -> <?php _e('Display the actually rental rate per day', 'ecalypse-rental');?></li>
											<li><strong>[rental_days]</strong> -> <?php _e('Display how many selected rental days', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationDetails]</strong> -> <?php _e('Dates, Address, Selected Car, Price', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationNumber]</strong> -> #123456</li>
									  		<li><strong>[ReservationLink]</strong> -> http://example.org/reservation/123456</li>
									  		<li><strong>[ReservationLinkStart]</strong><?php _e('Any text', 'ecalypse-rental');?><strong>[ReservationLinkEnd]</strong></li>
											<li><strong>[extras]</strong> -> <?php _e('Navigation, Child seat', 'ecalypse-rental');?></li>
											<?php do_action( 'ecalypse_rental_view_shortcodes_for_emails' ); ?>
									  	</ul>
									  </div>
										<p>*<?php _e('You can use HTML tags to format this email.', 'ecalypse-rental');?></p>
									  <input type="hidden" name="language" value="<?= $current_lang ?>">
									  <?php wp_nonce_field( 'language_save_email_reminder'); ?>
									  <button type="submit" class="btn btn-warning" name="language_save_email_reminder"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									</form>
							  </div>
							</div>
							
							<!-- THANK YOU EMAIL //-->
							<div class="panel panel-warning">	
								<div class="panel-heading"><h4><a href="javascript:void(0);" class="ecalypse_rental_translations_email_thank_you_toggle"><span>▼</span>&nbsp;&nbsp;<?php _e('Thank you email', 'ecalypse-rental');?></a></h4></div>
							  <div class="panel-body ecalypse_rental_translations_email_thank_you">
							  	
							  	<?php $email_body = get_option('ecalypse_rental_thank_you_email_' . $current_lang); ?>
								<?php $email_subject = get_option('ecalypse_rental_thank_you_email_subject_' . $current_lang); ?>
							  	
							    <form role="form" action="" method="post">
									  <div class="form-group">
										  <label for="thank_you_email_subject"><?php _e('Thank you email subject', 'ecalypse-rental');?></label>
										  <input class="form-control" type="text" id="thank_you_email_subject" name="thank_you_email_subject" value="<?php if (!empty($email_subject)) { ?>
<?= $email_subject ?>
<?php } else { ?>
<?php _e('Thank for your reservation #[ReservationNumber]', 'ecalypse-rental');?>
<?php } ?>">
										  
									    <label for="thank_you_email"><?php _e('Thank you e-mail', 'ecalypse-rental');?></label>
									    <textarea class="form-control" rows="20" id="thank_you_email" name="thank_you_email">
<?php if (!empty($email_body)) { ?>
<?= EcalypseRental::removeslashes(stripslashes(str_replace('\\\\','',$email_body))) ?>
<?php } else { ?>
<?php _e('Hi [CustomerName],

We hope everything went well with your rental. We loved having you as a customer. Let us know again when you are looking for a good deal on a rental car.

Your rental team', 'ecalypse-rental');?>
<?php } ?>
									    </textarea>
									  </div>
									  <div class="form-group">
									  	<p><strong><?php _e('Available variables', 'ecalypse-rental');?></strong></p>
									  	<ul style="margin-left:20px;list-style-type:circle;">
									  		<li><strong>[CustomerName]</strong> -> John Doe, Phil Smith, ...</li>
											<li><strong>[CustomerEmail]</strong> -> JohnDoe@gmail.com</li>
											<li><strong>[Car]</strong> -> Ford GT, ...</li>
											<li><strong>[pickupdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[dropoffdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[pickup_location]</strong> -> New York, ...</li>
											<li><strong>[dropoff_location]</strong> -> <?php _e('Somewhere in the middle of the nowhere', 'ecalypse-rental');?>, ...</li>
											<li><strong>[total_payment]</strong> -> $1574</li>
											<li><strong>[deposit_paid]</strong> -> $574</li>
											<li><strong>[remaining_amount]</strong> -> <?php _e('total_payment - deposit_paid = $1000', 'ecalypse-rental');?></li>
											<li><strong>[customer_comment]</strong> -> <?php _e('comment from step 3', 'ecalypse-rental');?></li>
											<li><strong>[rate]</strong> -> <?php _e('Display the actually rental rate per day', 'ecalypse-rental');?></li>
											<li><strong>[rental_days]</strong> -> <?php _e('Display how many selected rental days', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationDetails]</strong> -> <?php _e('Dates, Address, Selected Car, Price', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationNumber]</strong> -> #123456</li>
									  		<li><strong>[ReservationLink]</strong> -> http://example.org/reservation/123456</li>
									  		<li><strong>[ReservationLinkStart]</strong><?php _e('Any text', 'ecalypse-rental');?><strong>[ReservationLinkEnd]</strong></li>
											<li><strong>[extras]</strong> -> <?php _e('Navigation, Child seat', 'ecalypse-rental');?></li>
											<?php do_action( 'ecalypse_rental_view_shortcodes_for_emails' ); ?>
									  	</ul>
									  </div>
										<p>*<?php _e('You can use HTML tags to format this email.', 'ecalypse-rental');?></p>
									  <input type="hidden" name="language" value="<?= $current_lang ?>">
									  <?php wp_nonce_field( 'language_save_email_thank_you'); ?>
									  <button type="submit" class="btn btn-warning" name="language_save_email_thank_you"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									</form>
							  </div>
							</div>
							
							<!-- E-MAIL FOR STATUS PENDING //-->
							<div class="panel panel-warning">	
								<div class="panel-heading"><h4><a href="javascript:void(0);" class="ecalypse_rental_translations_email_status_pending_toggle"><span>▼</span>&nbsp;&nbsp;<?php _e('E-mail for status pending payment', 'ecalypse-rental');?></a></h4></div>
							  <div class="panel-body ecalypse_rental_translations_email_status_pending">
							  	
							  	<?php $email_body = get_option('ecalypse_rental_email_status_pending_' . $current_lang); ?>
								<?php $email_subject = get_option('ecalypse_rental_email_status_pending_subject_' . $current_lang); ?>
							  	
							    <form role="form" action="" method="post">
									  <div class="form-group">
										  <label for="email_status_pending_subject"><?php _e('Status pending payment email subject', 'ecalypse-rental');?></label>
										  <input class="form-control" type="text" id="email_status_pending_subject" name="email_status_pending_subject" value="<?php if (!empty($email_subject)) { ?>
<?= $email_subject ?>
<?php } else { ?>
Reservation #[ReservationNumber] is pending
<?php } ?>">
									    <label for="email_status_pending"><?php _e('Pending payment e-mail', 'ecalypse-rental');?></label>
									    <textarea class="form-control" rows="20" id="email_status_pending" name="email_status_pending">
<?php if (!empty($email_body)) { ?>
<?= EcalypseRental::removeslashes(stripslashes(str_replace('\\\\','',$email_body))) ?>
<?php } else { ?>
<?php _e('Dear [CustomerName],

thank you for your reservation. We have received it and one of our agents will review it momentarily. At this moment, your reservation is pending payment.

One we have confirmed your reservation, we will inform you via email.

Thank you,

reservation team @websiteurl', 'ecalypse-rental');?>
<?php } ?>
									    </textarea>
									  </div>
									  <div class="form-group">
									  	<p><strong><?php _e('Available variables', 'ecalypse-rental');?></strong></p>
									  	<ul style="margin-left:20px;list-style-type:circle;">
									  		<li><strong>[CustomerName]</strong> -> John Doe, Phil Smith, ...</li>
											<li><strong>[CustomerEmail]</strong> -> JohnDoe@gmail.com</li>
											<li><strong>[Car]</strong> -> Ford GT, ...</li>
											<li><strong>[pickupdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[dropoffdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[pickup_location]</strong> -> New York, ...</li>
											<li><strong>[dropoff_location]</strong> -> <?php _e('Somewhere in the middle of the nowhere', 'ecalypse-rental');?>, ...</li>
											<li><strong>[total_payment]</strong> -> $1574</li>
											<li><strong>[deposit_paid]</strong> -> $574</li>
											<li><strong>[remaining_amount]</strong> -> <?php _e('total_payment - deposit_paid = $1000', 'ecalypse-rental');?></li>
											<li><strong>[customer_comment]</strong> -> <?php _e('comment from step 3', 'ecalypse-rental');?></li>
											<li><strong>[rate]</strong> -> <?php _e('Display the actually rental rate per day', 'ecalypse-rental');?></li>
											<li><strong>[rental_days]</strong> -> <?php _e('Display how many selected rental days', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationDetails]</strong> -> <?php _e('Dates, Address, Selected Car, Price', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationNumber]</strong> -> #123456</li>
									  		<li><strong>[ReservationLink]</strong> -> http://example.org/reservation/123456</li>
									  		<li><strong>[ReservationLinkStart]</strong><?php _e('Any text', 'ecalypse-rental');?><strong>[ReservationLinkEnd]</strong></li>
											<li><strong>[extras]</strong> -> <?php _e('Navigation, Child seat', 'ecalypse-rental');?></li>
											<?php do_action( 'ecalypse_rental_view_shortcodes_for_emails' ); ?>
									  	</ul>
									  </div>
										<p>*<?php _e('You can use HTML tags to format this email.', 'ecalypse-rental');?></p>
									  <input type="hidden" name="language" value="<?= $current_lang ?>">
									  <?php wp_nonce_field( 'language_save_email_status_pending'); ?>
									  <button type="submit" class="btn btn-warning" name="language_save_email_status_pending"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									</form>
							  </div>
							</div>
							
							<!-- E-MAIL FOR STATUS PENDING OTHER //-->
							<div class="panel panel-warning">	
								<div class="panel-heading"><h4><a href="javascript:void(0);" class="ecalypse_rental_translations_email_status_pending_other_toggle"><span>▼</span>&nbsp;&nbsp;<?php _e('E-mail for status pending other', 'ecalypse-rental');?></a></h4></div>
							  <div class="panel-body ecalypse_rental_translations_email_status_pending_other">
							  	
							  	<?php $email_body = get_option('ecalypse_rental_email_status_pending_other_' . $current_lang); ?>
								<?php $email_subject = get_option('ecalypse_rental_email_status_pending_other_subject_' . $current_lang); ?>
							  	
							    <form role="form" action="" method="post">
									  <div class="form-group">
										  <label for="email_status_pending_other_subject"><?php _e('Status pending other email subject', 'ecalypse-rental');?></label>
										  <input class="form-control" type="text" id="email_status_pending_other_subject" name="email_status_pending_other_subject" value="<?php if (!empty($email_subject)) { ?>
<?= $email_subject ?>
<?php } else { ?>
<?php _e('Reservation #[ReservationNumber] is pending', 'ecalypse-rental');?>
<?php } ?>">
									    <label for="email_status_pending_other"><?php _e('Pending other e-mail', 'ecalypse-rental');?></label>
									    <textarea class="form-control" rows="20" id="email_status_pending_other" name="email_status_pending_other">
<?php if (!empty($email_body)) { ?>
<?= EcalypseRental::removeslashes(stripslashes(str_replace('\\\\','',$email_body))) ?>
<?php } else { ?>
<?php _e('Dear [CustomerName],

thank you for your reservation. We have received it and one of our agents will review it momentarily. At this moment, your reservation is pending.

One we have confirmed your reservation, we will inform you via email.

Thank you,

reservation team @ website url', 'ecalypse-rental');?>
<?php } ?>
									    </textarea>
									  </div>
									  <div class="form-group">
									  	<p><strong><?php _e('Available variables', 'ecalypse-rental');?></strong></p>
									  	<ul style="margin-left:20px;list-style-type:circle;">
									  		<li><strong>[CustomerName]</strong> -> John Doe, Phil Smith, ...</li>
											<li><strong>[CustomerEmail]</strong> -> JohnDoe@gmail.com</li>
											<li><strong>[Car]</strong> -> Ford GT, ...</li>
											<li><strong>[pickupdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[dropoffdate]</strong> -> <?php _e('Date and time eg. 2015-05-01 13:30', 'ecalypse-rental');?></li>
											<li><strong>[pickup_location]</strong> -> New York, ...</li>
											<li><strong>[dropoff_location]</strong> -> <?php _e('Somewhere in the middle of the nowhere', 'ecalypse-rental');?>, ...</li>
											<li><strong>[total_payment]</strong> -> $1574</li>
											<li><strong>[deposit_paid]</strong> -> $574</li>
											<li><strong>[remaining_amount]</strong> -> <?php _e('total_payment - deposit_paid = $1000', 'ecalypse-rental');?></li>
											<li><strong>[customer_comment]</strong> -> <?php _e('comment from step 3', 'ecalypse-rental');?></li>
											<li><strong>[rate]</strong> -> <?php _e('Display the actually rental rate per day', 'ecalypse-rental');?></li>
											<li><strong>[rental_days]</strong> -> <?php _e('Display how many selected rental days', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationDetails]</strong> -> <?php _e('Dates, Address, Selected Car, Price', 'ecalypse-rental');?></li>
									  		<li><strong>[ReservationNumber]</strong> -> #123456</li>
									  		<li><strong>[ReservationLink]</strong> -> http://example.org/reservation/123456</li>
									  		<li><strong>[ReservationLinkStart]</strong><?php _e('Any text', 'ecalypse-rental');?><strong>[ReservationLinkEnd]</strong></li>
											<li><strong>[extras]</strong> -> <?php _e('Navigation, Child seat', 'ecalypse-rental');?></li>
											<?php do_action( 'ecalypse_rental_view_shortcodes_for_emails' ); ?>
									  	</ul>
									  </div>
										<p>*<?php _e('You can use HTML tags to format this email.', 'ecalypse-rental');?></p>
									  <input type="hidden" name="language" value="<?= $current_lang ?>">
									  <?php wp_nonce_field( 'language_save_email_status_pending_other'); ?>
									  <button type="submit" class="btn btn-warning" name="language_save_email_status_pending_other"><span class="glyphicon glyphicon-save"></span>&nbsp;&nbsp;<?php _e('Confirm', 'ecalypse-rental');?> &amp; <?php _e('Save', 'ecalypse-rental');?></button>
									</form>
							  </div>
							</div>
							
							<?php
					    	$primary_language = 'en_GB';
								$user_set_language = get_option('ecalypse_rental_primary_language');
								if ($user_set_language && !empty($user_set_language)) {
									$primary_language = $user_set_language;
								}
							?>
							<?php if ($current_lang != 'en_GB' && $primary_language != $current_lang) { ?>
								<form action="" method="post" class="form" role="form" onsubmit="return confirm('<?= __('Do you really want to disable this language?', 'ecalypse-rental') ?>');">
									<input type="hidden" name="language" value="<?= $current_lang ?>">
									<?php wp_nonce_field( 'disable_language'); ?>
									<button class="btn btn-danger" name="disable_language"><span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<?php _e('Disable this language', 'ecalypse-rental');?></button>
									<p class="help-block">
										* <?php _e('You will have to reenable this language to be able to edit it. Disabled language translations are saved in DB, so you do not lose your translations. Once you enable previously disabled language all previous translations will be back (unless you make changes to DB manually)', 'ecalypse-rental');?>
									</p>
								</form>
								
							<?php } else { ?>
								<p class="help-block">
									* <?php _e('This language cannot be disabled. It\'s set as a primary or is it default language.', 'ecalypse-rental');?>
								</p>
							<?php } ?>
								
							<?php if ($current_lang != 'en_GB' && $primary_language != $current_lang) { ?>
								<form action="" method="post" class="form" role="form" onsubmit="return confirm('<?= __('Do you really want to deactivate this language?', 'ecalypse-rental') ?>');">
									<input type="hidden" name="language" value="<?= $current_lang ?>">
									<?php wp_nonce_field( 'deactivate_language'); ?>
									<?php if ((isset($available_languages[$current_lang]['active']) && $available_languages[$current_lang]['active']) || !isset($available_languages[$current_lang]['active'])) { ?>
										<button class="btn btn-danger" name="deactivate_language"><span class="glyphicon glyphicon-remove"></span>&nbsp;&nbsp;<?php _e('Deactivate this language', 'ecalypse-rental');?></button>
									<?php } else { ?>
										<button class="btn btn-success" name="activate_language"><span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;<?php _e('Activate this language', 'ecalypse-rental');?></button>
									<?php } ?>
									<p class="help-block">
										* <?php _e('Clients will not see this language on your site. Deactivate a language to edit your translations while users don`t see them.', 'ecalypse-rental');?> 
									</p>
								</form>
								
							<?php } else { ?>
								<p class="help-block">
									* <?php _e('This language cannot be deactivated. It\'s set as a primary or is it default language.', 'ecalypse-rental');?>
								</p>
							<?php } ?>
							<form action="" method="post" class="form" role="form">
								<input type="hidden" name="language" value="<?= $current_lang ?>">
								<?php wp_nonce_field( 'export_language'); ?>
								<button class="btn btn-warning" name="export_language"><?php _e('Export this language', 'ecalypse-rental');?></button>
							</form>
								
								<form action="" method="post" class="form" role="form" enctype="multipart/form-data">
								<div class="form-group">
									<input type="file" name="input_file" />
									<input type="hidden" name="language" value="<?= $current_lang ?>">
									<?php wp_nonce_field( 'import_language'); ?>
									<button name="import_language" class="btn btn-success"><?php _e('Import language from file', 'ecalypse-rental');?></button>
									<p class="help-block">
										* <?php _e('This file will rewrite currently selected language!', 'ecalypse-rental');?>
									</p>
								</div>
							</form>
									
						<?php } else { ?>
							<p>
								<?php _e('Please, select language to edit or create new language.', 'ecalypse-rental');?>
							</p>
						<?php } ?>
						
					</div>
				</div>
				
			</div>
		</div>
	</div>
	
</div>