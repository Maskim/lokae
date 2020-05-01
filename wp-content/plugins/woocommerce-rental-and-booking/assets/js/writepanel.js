jQuery(document).ready(function ($) {
  var admin_data = RNB_ADMIN_DATA;

  /**
   * Add new row
   *
   * @since 2.0.0
   * @return null
   */
  $('.add_redq_row').click(function () {
    $(this).closest('table').find('tbody').append($(this).data('row'));
    $('body').trigger('row_added');
    return false;
  });

  /**
   * Cotnrol date format
   *
   * @since 2.0.0
   * @return null
   */
  var date_format;
  if (typeof admin_data.calendar_data != 'undefined') {
    if (admin_data.calendar_data.date_format.toLowerCase() === 'd/m/y') {
      date_format = 'd/m/yy';
    }

    if (admin_data.calendar_data.date_format.toLowerCase() === 'm/d/y') {
      date_format = 'm/d/yy';
    }

    if (admin_data.calendar_data.date_format.toLowerCase() === 'y/m/d') {
      date_format = 'yy/m/d';
    }
  }

  $('body').on('row_added', function () {
    $('.rnb-date-picker').datetimepicker({
      format: 'Y-m-d H:i:s',
      minDate: 0,
      step: admin_data.calendar_data
        ? admin_data.calendar_data.time_interval
        : 5,
    });
  });

  $('.rnb-date-picker').datetimepicker({
    format: 'Y-m-d H:i:s',
    minDate: 0,
    step: admin_data.calendar_data ? admin_data.calendar_data.time_interval : 5,
  });

  $('body').on('.add_redq_row', function () {
    $('.rnb-date-picker').datetimepicker({
      format: 'Y-m-d H:i:s',
      minDate: 0,
      step: admin_data.calendar_data
        ? admin_data.calendar_data.time_interval
        : 5,
    });
  });

  /**
   * Remove row
   *
   * @since 1.0.0
   * @version 2.0.0
   * @return null
   */
  $('body').on('click', 'button.remove_row', function () {
    $(this).closest('.redq-remove-rows').remove();
    return false;
  });

  $('body').on('click', 'td.remove', function () {
    $(this).closest('tr').remove();
    return false;
  });

  $('body').on('click', 'td.inventory-remove', function () {
    var ids = JSON.parse($('.redq_availability_remove_id').val());

    var newId = $(this).closest('tr').data('id');
    ids.push(newId);

    $('.redq_availability_remove_id').val(JSON.stringify(ids));

    $(this).closest('tr').remove();
    return false;
  });

  /**
   * Show or hide row
   *
   * @since 2.0.0
   * @return null
   */
  $('.redq-hide-row').hide();

  $('body').on('click', '.show-or-hide', function (e) {
    $(this)
      .closest('div.redq-show-bar')
      .next('div.redq-hide-row')
      .slideToggle();
    return false;
  });

  $('.sortable').sortable({
    cursor: 'move',
  });

  /**
   * Control pricing types
   *
   * @since 2.0.0
   * @return null
   */
  $('.daily-pricing-panel').hide();
  $('.monthly-pricing-panel').hide();

  var pricingType = $('#pricing_type').val();

  if (pricingType == 'daily_pricing') {
    $('.daily-pricing-panel').show();
    $('.general-pricing-panel').hide();
    $('.monthly-pricing-panel').hide();
    $('.redq-days-range-panel').hide();
  } else if (pricingType == 'monthly_pricing') {
    $('.daily-pricing-panel').hide();
    $('.general-pricing-panel').hide();
    $('.monthly-pricing-panel').show();
    $('.redq-days-range-panel').hide();
  } else if (pricingType == 'days_range') {
    $('.daily-pricing-panel').hide();
    $('.general-pricing-panel').hide();
    $('.monthly-pricing-panel').hide();
    $('.redq-days-range-panel').show();
  } else if (pricingType == 'flat_hours') {
    $('.daily-pricing-panel').hide();
    $('.general-pricing-panel').hide();
    $('.monthly-pricing-panel').hide();
    $('.redq-days-range-panel').hide();
  } else {
    $('.daily-pricing-panel').hide();
    $('.general-pricing-panel').show();
    $('.monthly-pricing-panel').hide();
    $('.redq-days-range-panel').hide();
  }

  $('#pricing_type').change(function () {
    var pricingType = this.value;

    if (pricingType == 'daily_pricing') {
      $('.daily-pricing-panel').show();
      $('.general-pricing-panel').hide();
      $('.monthly-pricing-panel').hide();
      $('.redq-days-range-panel').hide();
    } else if (pricingType == 'monthly_pricing') {
      $('.daily-pricing-panel').hide();
      $('.general-pricing-panel').hide();
      $('.monthly-pricing-panel').show();
      $('.redq-days-range-panel').hide();
    } else if (pricingType == 'days_range') {
      $('.daily-pricing-panel').hide();
      $('.general-pricing-panel').hide();
      $('.monthly-pricing-panel').hide();
      $('.redq-days-range-panel').show();
    } else if (pricingType == 'flat_hours') {
      $('.daily-pricing-panel').hide();
      $('.general-pricing-panel').hide();
      $('.monthly-pricing-panel').hide();
      $('.redq-days-range-panel').hide();
    } else {
      $('.daily-pricing-panel').hide();
      $('.general-pricing-panel').show();
      $('.monthly-pricing-panel').hide();
      $('.redq-days-range-panel').hide();
    }
  });

  /**
   * Control hourly pricing types
   *
   * @since 4.0.7
   * @return null
   */
  var hourlyPricingType = $('#hourly_pricing_type').val();

  if (hourlyPricingType == 'hourly_general') {
    $('.redq-hourly-general-panel').show();
    $('.redq-hourly-range-panel').hide();
  } else {
    $('.redq-hourly-general-panel').hide();
    $('.redq-hourly-range-panel').show();
  }

  $('#hourly_pricing_type').change(function () {
    var hourlyPricingType = this.value;
    if (hourlyPricingType == 'hourly_general') {
      $('.redq-hourly-general-panel').show();
      $('.redq-hourly-range-panel').hide();
    } else {
      $('.redq-hourly-general-panel').hide();
      $('.redq-hourly-range-panel').show();
    }
  });

  /**
   * Control payable terms
   *
   * @since 2.0.0
   * @return null
   */
  var showHidePanels = [
    {
      panel: 'RnB_Category',
      selector: 'select#inventory_rnb_cat_payable_or_not',
      selectorValue: { show: 'yes', hide: 'no' },
      ids: [
        'input#inventory_rnb_cat_cost_termmeta',
        'input#inventory_rnb_cat_price_applicable_term_meta',
        'select#inventory_rnb_cat_price_applicable_term_meta',
        'input#inventory_rnb_cat_hourly_cost_termmeta',
      ],
    },
    {
      panel: 'RnB_Category',
      selector: 'select#inventory_rnb_cat_price_applicable_term_meta',
      selectorValue: { show: 'per_day', hide: 'one_time' },
      ids: ['input#inventory_rnb_cat_hourly_cost_termmeta'],
    },
    {
      panel: 'Resource',
      selector: 'select#inventory_price_applicable_term_meta',
      selectorValue: { show: 'per_day', hide: 'one_time' },
      ids: ['input#inventory_hourly_cost_termmeta'],
    },
    {
      panel: 'Person',
      selector: 'select#inventory_person_payable_or_not',
      selectorValue: { show: 'yes', hide: 'no' },
      ids: [
        'input#inventory_person_cost_termmeta',
        'select#inventory_person_price_applicable_term_meta',
        'input#inventory_peroson_hourly_cost_termmeta',
      ],
    },
    {
      panel: 'Person',
      selector: 'select#inventory_person_price_applicable_term_meta',
      selectorValue: { show: 'per_day', hide: 'one_time' },
      ids: ['input#inventory_peroson_hourly_cost_termmeta'],
    },
    {
      panel: 'Resource',
      selector: 'select#inventory_sd_price_applicable_term_meta',
      selectorValue: { show: 'per_day', hide: 'one_time' },
      ids: ['input#inventory_sd_hourly_cost_termmeta'],
    },
  ];

  //For preselector
  showHidePanels.forEach((showHidePanel) => {
    var currentValue = $(showHidePanel.selector).val();
    $ids = showHidePanel.ids;

    if (currentValue === showHidePanel.selectorValue.hide) {
      $ids.forEach((id) => {
        $(id).parents('.form-field').hide();
      });
    }

    if (currentValue === showHidePanel.selectorValue.show) {
      $ids.forEach((id) => {
        $(id).parents('.form-field').show();
      });
    }
  });

  // For change event
  $('body').on('change', 'select', function (e) {
    var currentSelector = $(this).attr('id');

    showHidePanels.forEach((showHidePanel) => {
      if (`select#${currentSelector}` === showHidePanel.selector) {
        var currentSelectorValue = $(showHidePanel.selector).val();
        $ids = showHidePanel.ids;

        if (currentSelectorValue === showHidePanel.selectorValue.hide) {
          $ids.forEach((id) => {
            $(id).parents('.form-field').hide();
          });
        }

        if (currentSelectorValue === showHidePanel.selectorValue.show) {
          $ids.forEach((id) => {
            $(id).parents('.form-field').show();
          });
        }
      }
    });
  });

  /**
   * Control settings tabs
   *
   * @since 2.0.0
   * @return null
   */
  $('#rnb_setting_tabs').tabs();

  $('.inventory-resources').select2({
    placeholder: 'Select resources',
    tags: true,
  });
  $('select').on('select2:select', function (evt) {
    var element = evt.params.data.element;
    var $element = $(element);

    $element.detach();
    $(this).append($element);
    $(this).trigger('change');
  });

  /**
   * Control settings tabs
   *
   * @since 2.0.0
   * @return null
   */
  //Display options
  var displayOption = $('#rnb_settings_for_display').val();
  if (displayOption === 'global') {
    $('.rnb-display-fields').hide();
    $('#rnb_settings_for_display').next('span.description').show();
  } else {
    $('.rnb-display-fields').show();
    $('#rnb_settings_for_display').next('span.description').hide();
  }

  $('#rnb_settings_for_display').on('change', function () {
    var option = $(this).val();
    if (option === 'global') {
      $('.rnb-display-fields').hide();
      $('#rnb_settings_for_display').next('span.description').show();
    } else {
      $('.rnb-display-fields').show();
      $('#rnb_settings_for_display').next('span.description').hide();
    }
  });

  //Label options
  var labelOption = $('#rnb_settings_for_labels').val();
  if (labelOption === 'global') {
    $('.rnb-label-fields').hide();
    $('#rnb_settings_for_labels').next('span.description').show();
  } else {
    $('.rnb-label-fields').show();
    $('#rnb_settings_for_labels').next('span.description').hide();
  }

  $('#rnb_settings_for_labels').on('change', function () {
    var option = $(this).val();
    if (option === 'global') {
      $('.rnb-label-fields').hide();
      $('#rnb_settings_for_labels').next('span.description').show();
    } else {
      $('.rnb-label-fields').show();
      $('#rnb_settings_for_labels').next('span.description').hide();
    }
  });

  //Condition options
  var conditionOption = $('#rnb_settings_for_conditions').val();
  if (conditionOption === 'global') {
    $('.rnb-condition-fields').hide();
    $('#rnb_settings_for_conditions').next('span.description').show();
  } else {
    $('.rnb-condition-fields').show();
    $('#rnb_settings_for_conditions').next('span.description').hide();
  }

  $('#rnb_settings_for_conditions').on('change', function () {
    var option = $(this).val();
    if (option === 'global') {
      $('.rnb-condition-fields').hide();
      $('#rnb_settings_for_conditions').next('span.description').show();
    } else {
      $('.rnb-condition-fields').show();
      $('#rnb_settings_for_conditions').next('span.description').hide();
    }
  });

  //Validation options
  var conditionOption = $('#rnb_settings_for_validations').val();
  if (conditionOption === 'global') {
    $('.rnb-validation-fields').hide();
    $('#rnb_settings_for_validations').next('span.description').show();
  } else {
    $('.rnb-validation-fields').show();
    $('#rnb_settings_for_validations').next('span.description').hide();
  }

  $('#rnb_settings_for_validations').on('change', function () {
    var option = $(this).val();
    if (option === 'global') {
      $('.rnb-validation-fields').hide();
      $('#rnb_settings_for_validations').next('span.description').show();
    } else {
      $('.rnb-validation-fields').show();
      $('#rnb_settings_for_validations').next('span.description').hide();
    }
  });

  $('#_redq_product_inventory').select2({
    placeholder: '--Select--',
    tags: true,
  });
});
