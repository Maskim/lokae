/*
Car Rental WP Plugin - 2015

v 3.0.0

*/
 
jQuery(document).ready(function($) {
	
	$('.ecalypse_rental_branch_add_new_tax').click(function (e) {
		e.preventDefault();
		html = $('.ecalypse_rental_tax_referer').html();
		last_id = $('.tax-row:last-child').data('id');
		last_id = last_id || 0;
		last_id++;
		html = '<tr class="tax-row" data-id="'+last_id+'">' + html.replace(/\[0\]/g, '[' + last_id + ']') + '</tr>';
		$('#ecalypse-rental-branch-taxes tbody').append(html);
	});
	
	$(document).on('click', '.ecalypse_rental_branch_delete_tax', function(e) {
		e.preventDefault();
		$(this).closest('tr').remove();
	});
	
	if ($('#ecalypse-news-div').length > 0) {
		$('#ecalypse-feed-dialog').dialog({
      autoOpen: false,
	  minWidth: 600,
      show: {
        effect: "blind",
        duration: 200
      },
	  modal: true
    });
		
		
		// load ecalypse news
		jQuery.ajax({
			url: ajaxurl,
			global: false,
			type: "POST",
			dataType: 'json',
			data: ({
				action: 'ecalypse_rental_load_ecalypse_news'
			}),
			complete: function(data){
				$('#ecalypse-news-ajax-loader').remove();
				if (!data || !data.responseJSON) {
					$('#ecalypse-news-div').append('<div class="alert alert-warning">There are no news for you. Have a nice day!</div>');
				} else {
					$.each(data.responseJSON, function (k,v) {
						$('#ecalypse-news-div').append('<div class="panel panel-info feed-box" style="border-color:#'+v['bg_color']+';" data-type="'+v['type']+'" data-id="'+v['id']+'">\
							  <div class="panel-heading" style="background-color:#'+v['bg_color']+';border-color:#'+v['bg_color']+';color:#'+v['text_color']+';"><a href="#" class="feed-read-more feed-name">'+v['name']+'</a><span class="news-date">'+v['date_from']+'</span></div>\
							  <div class="panel-body">'+v['preview']+'\
								<div class="feed-text">'+v['text']+'</div>\
								<div class="feed-buttons"><a class="btn btn-default feed-read-more" href="#"><strong>Read more</strong></a></div>\
							  </div>\
							</div>').hide().delay( 100 ).show('slow');
					});
					
				}
			},
			async: true
		}); 
	}
	
	$(document).on('click', '.feed-buttons-close', function(e) {
		e.preventDefault();
		$('#ecalypse-feed-dialog').dialog("close");
	});
	
	$(document).on('click', '.feed-buttons-delete', function(e) {
		e.preventDefault();
		var dataid = $('#ecalypse-feed-dialog').attr('data-id');
		jQuery.ajax({
			url: ajaxurl,
			global: false,
			type: "POST",
			dataType: 'json',
			data: ({
				action: 'ecalypse_rental_feed_actions',
				type: 'delete',
				id: dataid
			}),
			complete: function(){
				$('#ecalypse-feed-dialog').dialog("close");
				$('.feed-box[data-id='+dataid+']').remove();
			},
			async: true
		}); 
	});
	
	$(document).on('click', '.feed-buttons-confirm', function(e) {
		e.preventDefault();
		var dataid = $('#ecalypse-feed-dialog').attr('data-id');
		jQuery.ajax({
			url: ajaxurl,
			global: false,
			type: "POST",
			dataType: 'json',
			data: ({
				action: 'ecalypse_rental_feed_actions',
				type: 'confirm',
				id: dataid
			}),
			complete: function(){
				$('#ecalypse-feed-dialog').dialog("close");
				$('.feed-box[data-id='+dataid+']').remove();
			},
			async: true
		}); 
		$('#ecalypse-feed-dialog').dialog("close");
	});
	
	$(document).on('click', '.feed-read-more', function(e) {
		e.preventDefault();
		el = $(this).closest('.feed-box');
		$('#ecalypse-feed-dialog').attr('title', el.find('.feed-name').text());
		$('#ecalypse-feed-dialog').attr('data-id', el.attr('data-id'));
		$('#ecalypse-feed-dialog').dialog('option', 'title', el.find('.feed-name').text());
		buttons = '<div class="ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"><div class="ui-dialog-buttonset">';
		if (el.attr('data-type') == 2) {
			// confirm dialog
			buttons += '<a class="btn btn-default feed-buttons-confirm mr" href="#"><strong>Confirm reading</strong></a>';
		} else {
			buttons += '<a class="btn btn-default feed-buttons-delete mr" href="#"><strong>Delete</strong></a>';
		}
		buttons += '<a class="btn btn-default feed-buttons-close" href="#"><strong>Close</strong></a></div></div>';
		
		$('#ecalypse-feed-dialog').html( el.find('.feed-text').html() + buttons);
		$('#ecalypse-feed-dialog').dialog("open");
	});
	
	$('#ecalypse-rental-fleet-add-form').hide();
	$('#ecalypse-rental-extras-add-form').hide();
	$('#ecalypse-rental-branches-add-form').hide();
	$('#ecalypse-rental-pricing-add-form').hide();
	$('#ecalypse-rental-language-add-form').hide();
	$('#ecalypse-rental-language-primary-form').hide();
	
	$('#ecalypse-rental-fleet-parameters').dataTable({  stateSave: true, "ordering": false, "paging": false});
	$('#ecalypse-rental-fleet').dataTable({  stateSave: true, "ordering": false, "paging": false});
	$('#ecalypse-rental-extras').dataTable({ stateSave: true });
	$('#ecalypse-rental-branches').dataTable({ stateSave: true, "ordering": false, "paging": false });
	$('#ecalypse-rental-booking').dataTable({ stateSave: true, "searching": false });
	$('#ecalypse-rental-pricing').dataTable({ stateSave: true });
	if (jQuery.fn.DataTable.TableTools) { 
		TableTools.DEFAULTS.aButtons = [ "print" ];
	}
	$('#ecalypse-rental-newsletter').dataTable({
		stateSave: true,
		"dom": 'T<"clear">lfrtip'
	});
	
	$('.data_table_select_all').click(function(){
		table = $('#'+$(this).attr('data-id'));
		table.find(':checkbox').prop('checked', $(this).is(':checked'));
		$('.data_table_select_all[data-id="'+$(this).attr('data-id')+'"]').prop('checked', $(this).is(':checked'));
	});
	
	
	$("#ecalypse-rental-fleet-add-button").click(function() {
	  $("#ecalypse-rental-fleet-add-form").toggle("slow");
	});
	
	$("#ecalypse-rental-fleet-parameter-add-button").click(function() {
	  $("#ecalypse-rental-fleet-parameter-add-form").toggle("slow");
	});

	$("#ecalypse-rental-extras-add-button").click(function() {
	  $("#ecalypse-rental-extras-add-form").toggle("slow");
	});
	
	$("#ecalypse-rental-branches-add-button").click(function() {
	  $("#ecalypse-rental-branches-add-form").toggle("slow");
	});
	
	$("#ecalypse-rental-pricing-add-button").click(function() {
	  $("#ecalypse-rental-pricing-add-form").toggle("slow");
	});
	
	$("#ecalypse-rental-booking-add-button").click(function() {
	  $("#ecalypse-rental-booking-add-form").toggle("slow");
	});
	
	$("#ecalypse-rental-language-add-button").click(function() {
	  $("#ecalypse-rental-language-add-form").toggle("slow");
	});
	
	$("#ecalypse-rental-language-primary-button").click(function() {
	  $("#ecalypse-rental-language-primary-form").toggle("slow");
	});
	
	$('#ecalypse-rental-add-pricing-scheme').click(function() {
		ecalypse_rental_add_pricing();
	});
	
	$('#ecalypse-rental-add-additional-parameter').click(function() {
		ecalypse_rental_add_additional_parameter();
	});
	
	$('.ecalypse-rental-insert-parameter-link').click(function(e) {
		e.preventDefault();
		ecalypse_rental_insert_existing_parameter($(this));
	});
	
	$(document).on('click', '.fleet-delete-parameter', function() {
		ecalypse_rental_remove_additional_parameter(jQuery(this));
	});
	
	$(document).on('blur', '.fleet-parameter-name', function() {
		ecalypse_rental_blur_additional_parameter(jQuery(this));
	});
	
	$(".additional_parameters_tab").sortable({
		stop: ecalypse_rental_sort_additional_parameter
	});
	
	$('#ecalypse-rental-add-fleet-parameter-value').click(function() {
		ecalypse_rental_add_fleet_parameter_value();
	});
	
	$(document).on('click', '.fleet-delete-parameter-value', function() {
		ecalypse_rental_remove_fleet_parameter_value(jQuery(this));
	});
	
	$(document).on('blur', '.fleet-parameter-value', function() {
		ecalypse_rental_blur_fleet_parameter_value(jQuery(this));
	});
	
	$('#ecalypse-rental-fleet-parameter-form').on('submit', function() {
		if ($('.fleet_parameter_name_input.lng_gb').val() == '') {
			alert('Sorry, Name of the parameter in english should not be empty.');
			return false;
		}
	});
	
	$("#ecalypse-rental-hour-range-box-show").click(function() {
	  $("#ecalypse-rental-hour-range-box").toggle("fast");
	});
	
	
	$('#ecalypse-rental-add-av-currencies').click(function() {
		$('#ecalypse-rental-av-currencies-insert').after($("#ecalypse-rental-av-currencies").html());
	});
	
	$('#ecalypse-rental-add-vehicle-category').click(function() {
		$('#ecalypse-rental-vehicle-cats-insert').before('<tr>' + $("#ecalypse-rental-vehicle-cats").html() + '</tr>');
	});
	
	$('#ecalypse-rental-add-day-range').click(function() {
		$('#day-range-row-before').before('<tr>' + $("#day-range-row").html() + '</tr>');
	});
	
	$('#ecalypse-rental-add-hour-range').click(function() {
		$('#hour-range-row-before').before('<tr>' + $("#hour-range-row").html() + '</tr>');
	});
	
	$('#ecalypse-rental-fleet-add-form form').on('submit', function() {
		if ($('#ecalypse-rental-type').val() == '') {
			alert('Sorry, Name of the vehicle should not be empty.');
			return false;
		}
	});
	
	$('#ecalypse-rental-extras-add-form form').on('submit', function() {
		if ($('#ecalypse-rental-name').val() == '') {
			alert('Sorry, Name of the item should not be empty.');
			return false;
		}
	});
	
	$('#ecalypse-rental-branches-add-form form').on('submit', function() {
		if ($('#ecalypse-rental-name').val() == '') {
			alert('Sorry, Name of the branch should not be empty.');
			return false;
		}
	});
	
	$('#ecalypse-rental-pricing-add-form form').on('submit', function() {
		if ($('#ecalypse-rental-name').val() == '') {
			alert('Sorry, Name of the scheme should not be empty.');
			return false;
		}
	});
	
	$(document).on('keyup', '[name=days\\[from\\]\\[\\]]', function() {
		ecalypse_rental_check_ranges('days');
	});
	
	$(document).on('keyup', '[name=days\\[to\\]\\[\\]]', function() {
		ecalypse_rental_check_ranges('days');
	});
	
	$(document).on('keyup', '[name=hours\\[from\\]\\[\\]]', function() {
		ecalypse_rental_check_ranges('hours');
	});
	
	$(document).on('keyup', '[name=hours\\[to\\]\\[\\]]', function() {
		ecalypse_rental_check_ranges('hours');
	});
	
	$('[name=currency]').on('change', function() {
		ecalypse_rental_pricing_set_currency();
	});
	
	$('[name=type]').on('click', function() {
		if ($(this).val() == 1) {
			$('.type-onetime').show();
			$('.type-timerelated').hide();
		} else {
			$('.type-onetime').hide();
			$('.type-timerelated').show();
		}
		ecalypse_rental_pricing_set_currency();
	});
	
	// Init
	$('#ecalypse-rental-prices').hide();
	$('#days-range-help').hide();
	$('#hours-range-help').hide();
	
	ecalypse_rental_add_pricing();
	ecalypse_rental_add_additional_parameter();
	ecalypse_rental_pricing_set_currency();
	
	$("#pricing_sort").sortable();
	//$("#pricing_sort").disableSelection();
	//$("#additional_parameters_sort").disableSelection();
			    
	$(document).on('click', '.pricing_datepicker', function() {
		$(this).datepicker({ dateFormat: 'yy-mm-dd' }).datepicker('show');
	});
	
	$(document).on('click', '.ecalypse_rental_show_ranges', function() {
		var $dialog = $('<div>Loading...</div>')
				.load($(this).attr('href'))
				.dialog({
					autoOpen: true,
					title: 'Details',
					width: 700,
					height: 400,
					resizable: true
				});
		return false;
	});
	
	// Translations
	$('.ecalypse_rental_translations_email_customers').hide();
	$('.ecalypse_rental_translations_email_reminder_customers').hide();
	$('.ecalypse_rental_translations_email_thank_you').hide();
	$('.ecalypse_rental_translations_email_status_pending').hide();
	$('.ecalypse_rental_translations_email_status_pending_other').hide();
	$('.ecalypse_rental_translations_email_status_confirmed').hide();
	$('.ecalypse_rental_translations_terms').hide();
	$('.ecalypse_rental_translations_theme').hide();
	
	$(".ecalypse_rental_translations_email_customers_toggle").click(function() {
	  $(".ecalypse_rental_translations_email_customers").toggle("fast", function() {
			if ($(this).is(':hidden')) {
				$(".ecalypse_rental_translations_email_customers_toggle").find('span').html('▼');
			} else {
				$(".ecalypse_rental_translations_email_customers_toggle").find('span').html('▲');
			}	
		});
	});
	
	
	$(".ecalypse_rental_translations_email_reminder_customers_toggle").click(function() {
	  $(".ecalypse_rental_translations_email_reminder_customers").toggle("fast", function() {
			if ($(this).is(':hidden')) {
				$(".ecalypse_rental_translations_email_reminder_customers_toggle").find('span').html('▼');
			} else {
				$(".ecalypse_rental_translations_email_reminder_customers_toggle").find('span').html('▲');
			}	
		});
	});
	
	$(".ecalypse_rental_translations_email_status_pending_toggle").click(function() {
	  $(".ecalypse_rental_translations_email_status_pending").toggle("fast", function() {
			if ($(this).is(':hidden')) {
				$(".ecalypse_rental_translations_email_status_pending_toggle").find('span').html('▼');
			} else {
				$(".ecalypse_rental_translations_email_status_pending_toggle").find('span').html('▲');
			}	
		});
	});
	
	$(".ecalypse_rental_translations_email_thank_you_toggle").click(function() {
	  $(".ecalypse_rental_translations_email_thank_you").toggle("fast", function() {
			if ($(this).is(':hidden')) {
				$(".ecalypse_rental_translations_email_thank_you_toggle").find('span').html('▼');
			} else {
				$(".ecalypse_rental_translations_email_thank_you_toggle").find('span').html('▲');
			}	
		});
	});
	
	$(".ecalypse_rental_translations_email_status_pending_other_toggle").click(function() {
	  $(".ecalypse_rental_translations_email_status_pending_other").toggle("fast", function() {
			if ($(this).is(':hidden')) {
				$(".ecalypse_rental_translations_email_status_pending_other_toggle").find('span').html('▼');
			} else {
				$(".ecalypse_rental_translations_email_status_pending_other_toggle").find('span').html('▲');
			}	
		});
	});
	
	$(".ecalypse_rental_translations_email_status_confirmed_toggle").click(function() {
	  $(".ecalypse_rental_translations_email_status_confirmed").toggle("fast", function() {
			if ($(this).is(':hidden')) {
				$(".ecalypse_rental_translations_email_status_confirmed_toggle").find('span').html('▼');
			} else {
				$(".ecalypse_rental_translations_email_status_confirmed_toggle").find('span').html('▲');
			}	
		});
	});
	
	$(".ecalypse_rental_translations_terms_toggle").click(function() {
	  $(".ecalypse_rental_translations_terms").toggle("fast", function() {
			if ($(this).is(':hidden')) {
				$(".ecalypse_rental_translations_terms_toggle").find('span').html('▼');
			} else {
				$(".ecalypse_rental_translations_terms_toggle").find('span').html('▲');
			}	
		});
	});
	
	$(".ecalypse_rental_translations_theme_toggle").click(function() {
	  $(".ecalypse_rental_translations_theme").toggle("fast", function() {
			if ($(this).is(':hidden')) {
				$(".ecalypse_rental_translations_theme_toggle").find('span').html('▼');
			} else {
				$(".ecalypse_rental_translations_theme_toggle").find('span').html('▲');
			}	
		});
	});
	
	
	// Fleet translations
	$('.fleet_description').hide();
	$('.fleet_description_gb').show();
	
	$('.edit_fleet_description').click(function() {
		var lang = $(this).attr('data-value');
		$('.fleet_description').hide();
		$('.edit_fleet_description').parent().removeClass('active');
		$('.fleet_description_' + lang).show();
		$(this).parent().addClass('active');
	});
	
	$('.edit_fleet_parameters').click(function() {
		var lang = $(this).attr('data-value');
		$('.additional_parameters_tab').hide();
		$('.edit_fleet_parameters').parent().removeClass('active');
		$('#additional_parameters_sort_' + lang).show();
		$(this).parent().addClass('active');
	});
	
	$('.edit_fleet_parameter_value').click(function() {
		var lang = $(this).attr('data-value');
		$('.fleet_parameter_values_tab').hide();
		$('.edit_fleet_parameter_value').parent().removeClass('active');
		$('#fleet_parameter_values_sort_' + lang).show();
		$(this).parent().addClass('active');
	});
	ecalypse_rental_add_fleet_parameter_value();
	
	$('.edit_fleet_parameter_name').click(function() {
		var lang = $(this).attr('data-value');
		$('.fleet_parameter_name_input').hide();
		$('.edit_fleet_parameter_name').parent().removeClass('active');
		$('.fleet_parameter_name_input.lng_' + lang).show();
		$(this).parent().addClass('active');
	});
	
	$('.ecalypse-rental-fleet-parameter-type').change(function() {
		type = $('input.ecalypse-rental-fleet-parameter-type:checked').attr('data-type');
		$('.ecalypse_rental_fleet_parameter_type_block').hide();
		$('.ecalypse_rental_fleet_parameter_type_block.type_'+type).show();
	});
	
	$('.edit_extras_name_desc').click(function() {
		var lang = $(this).attr('data-value');
		$('.ecalypse_rental_extras_translations').hide();
		$('.edit_extras_name_desc').parent().removeClass('active');
		$('.ecalypse_rental_extras_translations[data-lng=' + lang+']').show();
		$(this).parent().addClass('active');
	});
	
	$('.categories_categories_switcher').click(function() {
		var lang = $(this).attr('data-value');
		$('.ecalypse_rental_categories_translations').hide();
		$('.categories_categories_switcher').parent().removeClass('active');
		$('.ecalypse_rental_categories_translations[data-lng=' + lang+']').show();
		$(this).parent().addClass('active');
	});
	
	$('.ecalypse_rental_branch_taxes_switcher').click(function() {
		var lang = $(this).attr('data-value');
		$('.ecalypse_rental_branch_taxes_translations').hide();
		$('.ecalypse_rental_branch_taxes_switcher').parent().removeClass('active');
		$('.ecalypse_rental_branch_taxes_translations[data-lng=' + lang+']').show();
		$(this).parent().addClass('active');
	});
	
	$('.categories_new_categories_switcher').click(function() {
		var lang = $(this).attr('data-value');
		$('.ecalypse_rental_new_categories_translations').hide();
		$('.categories_new_categories_switcher').parent().removeClass('active');
		$('.ecalypse_rental_new_categories_translations[data-lng=' + lang+']').show();
		$(this).parent().addClass('active');
	});
	
	$('.edit_seo_name_desc').click(function() {
		var lang = $(this).attr('data-value');
		$('.ecalypse_rental_seo_translations').hide();
		$('.edit_seo_name_desc').parent().removeClass('active');
		$('.ecalypse_rental_seo_translations[data-lng=' + lang+']').show();
		$(this).parent().addClass('active');
	});
	
	$('.edit_branch').click(function() {
		var lang = $(this).attr('data-value');
		$('.ecalypse_rental_branch_translations').hide();
		$('.edit_branch').parent().removeClass('active');
		$('.ecalypse_rental_branch_translations[data-lng=' + lang+']').show();
		$(this).parent().addClass('active');
	});
	
	// Disclamer translations
	$('.disclaimer').hide();
	$('.disclaimer_gb').show();
	
	$('.edit_disclaimer').click(function() {
		var lang = $(this).attr('data-value');
		$('.disclaimer').hide();
		$('.edit_disclaimer').parent().removeClass('active');
		$('.disclaimer_' + lang).show();
		$(this).parent().addClass('active');
	});
	
	
	$('.days-check-all').click(function() {
		if ($(this).is(':checked')) {
			$('.days-check').prop('checked', true);
		} else {
			$('.days-check').prop('checked', false);
		}
	});
	
	// Batch processing (fleet, extras, branches, pricing, booking)
	$(document).on('click', '.batch_processing, .data_table_select_all', function() {
		var values = new Array();
		var values_delete = new Array();
		$('.batch_processing').each(function() {
			if ($(this).is(':checked')) {
				values.push($(this).val());
				if (parseInt($(this).attr('data-usage')) == 0) {
					values_delete.push($(this).val());
				}
			}
		});
		
		$('[name=batch_processing_values]').val(values.join(','));
		$('.batch_processing_count').html(((values.length > 0) ? values.length + ' ' : ''));
		
		$('[name=batch_processing_values_delete]').val(values_delete.join(','));
		$('.batch_processing_count_delete').html(((values_delete.length > 0) ? values_delete.length + ' ' : ''));

	});
	
	
});

function ecalypse_rental_check_ranges(name) {
	var arr = [];
		
	jQuery('[name=' + name + '\\[from\\]\\[\\]]').each(function(i) {
		arr.push(jQuery(this).val());
	});
	
	jQuery('[name=' + name + '\\[to\\]\\[\\]]').each(function(i) {
		arr.push(jQuery(this).val());
	});
	
	arr.sort(function(a,b){return a - b});
	//$('#days-range-checker').html(arr);
	
	var results = [];
	for (var i = 0; i < arr.length - 1; i++) {
	  if (arr[i + 1] == arr[i]) {
	    results.push(arr[i]);
	  }
	}
	
	if (results != '') {
		jQuery('#' + name + '-range-help').show('fast');
	} else {
		jQuery('#' + name + '-range-help').hide('fast');
	}
	
}

function ecalypse_rental_add_pricing() {
	var html = jQuery("#ecalypse-rental-prices").html();
	jQuery('#ecalypse-rental-prices-insert').before(html);
}

function ecalypse_rental_add_additional_parameter() {
	jQuery.each(jQuery('.additional_parameters_tab'), function(k, v) {
		lng = jQuery(v).attr('data-lng');
		var html = jQuery("#ecalypse-rental-additional-parameters-"+lng).html();
		last_i = 0;
		jQuery.each(jQuery('#additional_parameters_sort_'+lng+' div.row'), function(kk,vv){
			if (parseInt(jQuery(vv).attr('data-row-i')) > last_i) {
				last_i = parseInt(jQuery(vv).attr('data-row-i'));
			}
		});
		last_i++;
		
		html = html.replace('data-row-i="0"', 'data-row-i="'+last_i+'"');
		jQuery('#ecalypse-rental-additional-parameters-insert-'+lng).before(html.replace(/0/g,last_i));
	});
	
	//var html = jQuery("#ecalypse-rental-additional-parameters").html();	
	/*last_i = jQuery('#additional_parameters_sort div.row:last input:first').attr('name');
	last_i = last_i.substring(22);
	last_i = parseInt(last_i.substring(0, last_i.indexOf(']'))) + 1;*/
	
}

function ecalypse_rental_pricing_set_currency() {
	jQuery('.addon-currency').html(jQuery('[name=currency]').val());	
}

function ecalypse_rental_remove_additional_parameter(el) {	
	i = el.closest('.row').attr('data-row-i');
	jQuery('.additional_parameters_tab .row[data-row-i='+i+']').remove();	
}

function ecalypse_rental_blur_additional_parameter(el) {
	i = el.closest('.row').attr('data-row-i');
	if (el.val() == '') {
		return;
	}
	jQuery.each(jQuery('.additional_parameters_tab .row[data-row-i='+i+'] .fleet-parameter-name'), function(k, v) {
		if (jQuery(v).val() == '') {
			jQuery(v).attr('placeholder', el.val());
		}
	});
}

function ecalypse_rental_sort_additional_parameter(event, ui) {
	parent = jQuery(ui.item).parent();
	sorted = jQuery(this).sortable('toArray', {attribute: 'data-row-i'});
	jQuery.each(jQuery('.additional_parameters_tab[data-lng!='+parent.attr('data-lng')+']'), function(k, v){
		var lang = jQuery(this).attr('data-lng');
		after = jQuery('#ecalypse-rental-additional-parameters-'+lang);
		jQuery.each(sorted, function(kk, vv){
			if (vv == '') {
				return;
			}			
			jQuery('#additional_parameters_sort_'+lang+' .row[data-row-i='+vv+']').insertAfter(after);
			after = jQuery('#additional_parameters_sort_'+lang+' .row[data-row-i='+vv+']');
			
		});
	});
}

function ecalypse_rental_insert_existing_parameter(el) {	
	row = el.parent().parent().find('.row:last');
	if (row.attr('data-row-i') == '0') {
		return;
	}
	if (row.find('.fleet-parameter-name').val() == '') {
		row.find('.fleet-parameter-name').val(el.text());
	} else {
		row.find('.fleet-parameter-name').val(row.find('.fleet-parameter-name').val() + ' ' + el.text());
	}
	ecalypse_rental_blur_additional_parameter(row.find('.fleet-parameter-name'));
}

function ecalypse_rental_add_fleet_parameter_value() {
	jQuery.each(jQuery('.fleet_parameter_values_tab'), function(k, v) {
		lng = jQuery(v).attr('data-lng');
		var html = jQuery("#ecalypse-rental-fleet-parameter-value-"+lng).html();
		last_i = 0;
		jQuery.each(jQuery('#fleet_parameter_values_sort_'+lng+' div.row'), function(kk,vv){
			if (parseInt(jQuery(vv).attr('data-row-i')) > last_i) {
				last_i = parseInt(jQuery(vv).attr('data-row-i'));
			}
		});
		last_i++;
		
		html = html.replace('data-row-i="0"', 'data-row-i="'+last_i+'"');
		jQuery('#ecalypse-rental-fleet-parameter-values-insert-'+lng).before(html.replace(/0/g,last_i));
	});
}

function ecalypse_rental_blur_fleet_parameter_value(el) {
	i = el.closest('.row').attr('data-row-i');
	if (el.val() == '') {
		return;
	}
	jQuery.each(jQuery('.fleet_parameter_values_tab .row[data-row-i='+i+'] .fleet-parameter-value'), function(k, v) {
		if (jQuery(v).val() == '') {
			jQuery(v).attr('placeholder', el.val());
		}
	});
}

function ecalypse_rental_remove_fleet_parameter_value(el) {	
	i = el.closest('.row').attr('data-row-i');
	jQuery('.fleet_parameter_values_tab .row[data-row-i='+i+']').remove();	
}