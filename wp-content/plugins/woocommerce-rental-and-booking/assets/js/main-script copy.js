jQuery(document).ready(function($) {
  var offDays = new Array(),
    conditional_data = CALENDAR_DATA.calendar_props.settings.conditions,
    general_data = CALENDAR_DATA.calendar_props.settings.general,
    validation_data = CALENDAR_DATA.calendar_props.settings.validations,
    layout_two_labels = CALENDAR_DATA.calendar_props.settings.layout_two_labels,
    translatedStrings = CALENDAR_DATA.translated_strings,
    markers = CALENDAR_DATA.markers;

  function inventorySwitching() {
    $('#booking_inventory').change(function() {
      $('.rnb-cart').append(
        '<div class="rnb-spinner"><div class="fa fa-spinner fa-spin"></div></div>'
      );

      var inventoryID = $(this).val();
      var postID = $(this).data('post-id');
      $.ajax({
        type: 'post',
        dataType: 'json',
        url: AJAX_DATA.ajaxurl,
        data: {
          action: 'rnb_get_inventory_data',
          inventory_id: inventoryID,
          post_id: postID,
          nonce: AJAX_DATA.nonce,
        },
        success: function(response) {
          $('#pickup-date').val('');
          $('#dropoff-date').val('');
          $('.booking-pricing-info').hide();
          BOOKING_DATA = response.booking_data;
          CALENDAR_DATA = response.calendar_data;

          calendarInit();
          rnbCostHandle();

          locationSelect(
            response.pick_up_locations,
            'pickup_location',
            'redq-pick-up-location'
          );
          locationSelect(
            response.return_locations,
            'dropoff_location',
            'redq-drop-off-location'
          );
          displayDeposite(response.deposits);

          if (response.persons.data.adults.length != 0) {
            personSelect(
              response.persons.data.adults,
              response.persons.placeholder.adults_placeholder,
              'additional_adults_info',
              'additional-person-adult'
            );
          } else {
            $('.additional_adults_info').chosen('destroy');
            $('.additional_adults_info').remove();
            $('.additional-person-adult').hide();
          }

          if (response.persons.data.childs.length != 0) {
            personSelect(
              response.persons.data.childs,
              response.persons.placeholder.childs_placeholder,
              'additional_childs_info',
              'additional-person-child'
            );
          } else {
            $('.additional_childs_info').chosen('destroy');
            $('.additional_childs_info').remove();
            $('.additional-person-child').hide();
          }

          resourceCheckbox(response.resources, 'payable-extras');
          categoryCheckbox(response.categories, 'payable-categories');

          $('.rnb-spinner').remove();
        },
      });
    });
  }

  inventorySwitching();

  function resourceCheckbox(array, containerClass) {
    if (array.data.length == 0) {
      $('.' + containerClass + ' .attributes').remove();
      $('.' + containerClass).hide();
    } else {
      var dataHtml = '';
      $('.' + containerClass + ' .attributes').empty();
      $.each(array.data, function(index, value) {
        dataHtml += '<div class="attributes">';
        dataHtml += '<label class="custom-block">';
        dataHtml +=
          '<input type="checkbox" name="extras[]" value ="' +
          value.resource_name +
          '|' +
          value.resource_cost +
          '|' +
          value.resource_applicable +
          '|' +
          value.resource_hourly_cost +
          '" data-name="' +
          value.resource_name +
          '" data-value-in="0" data-applicable="' +
          value.resource_applicable +
          '" data-value="' +
          value.resource_cost +
          '" data-hourly-rate="' +
          value.resource_hourly_cost +
          '" data-currency-before="$" data-currency-after="" class="booking-extra" />';
        dataHtml += value.resource_name;
        dataHtml += value.extra_meta;
        dataHtml += '</label>';
        dataHtml += '</div>';
      });

      $('.' + containerClass).append(dataHtml);
      $('.' + containerClass).show();
    }
  }

  function categoryCheckbox(array, containerClass) {
    if (array.data.length == 0) {
      $('.' + containerClass + ' .attributes').remove();
      $('.' + containerClass).hide();
    } else {
      var dataHtml = '';
      $('.' + containerClass + ' .attributes.categories-attr').empty();

      $.each(array.data, function(index, value) {
        dataHtml += '<div class="attributes categories-attr">';
        dataHtml += '<label class="custom-block">';
        dataHtml +=
          '<input type="checkbox" name="categories[]" value ="' +
          value.name +
          '|' +
          value.cost +
          '|' +
          value.applicable +
          '|' +
          value.hourlycost +
          '" data-name="' +
          value.name +
          '" data-value-in="0" data-applicable="' +
          value.applicable +
          '" data-value="' +
          value.cost +
          '" data-hourly-rate="' +
          value.hourlycost +
          '" data-currency-before="$" data-currency-after="" class="carrental_extras" />';
        dataHtml += value.name;
        dataHtml += value.extra_meta;
        dataHtml += '</label>';
        dataHtml += '<div class="quantity">';
        dataHtml +=
          '<label class="screen-reader-text" for="' +
          value.quantity_input.input_id +
          '">' +
          value.quantity_input.placeholder +
          '</label>';
        dataHtml +=
          '<input type="number" id="' +
          value.quantity_input.input_id +
          '" class="input-text qty text" step="' +
          value.quantity_input.step +
          '" min="' +
          value.quantity_input.min_value +
          '" max="' +
          (0 < value.quantity_input.max_value
            ? value.quantity_input.max_value
            : '') +
          '" name="' +
          value.quantity_input.input_name +
          '" value="' +
          value.quantity_input.input_value +
          '" title="' +
          value.quantity_input.title +
          '" size="4" pattern="' +
          value.quantity_input.pattern +
          '" inputmode="' +
          value.quantity_input.inputmode +
          '" aria-labelledby="' +
          value.quantity_input.labelledby +
          '" />';
        dataHtml += '</div>';
        dataHtml += '</div>';
      });

      $('.' + containerClass).append(dataHtml);
      $('.' + containerClass).show();
    }
  }

  function displayDeposite(array) {
    if (array.data.length == 0) {
      $('.payable-security_deposites .attributes').remove();
      $('.payable-security_deposites').hide();
    } else {
      $('.payable-security_deposites .attributes').empty();
      var dataHtml = '';
      $.each(array.data, function(index, value) {
        dataHtml += '<div class="attributes">';
        dataHtml += '<label class="custom-block">';
        dataHtml += '<input type="checkbox" ';
        if (value.security_deposite_clickable == 'no') {
          dataHtml += 'checked onclick="return false"';
        }
        dataHtml +=
          ' name="security_deposites[]" value ="' +
          value.security_deposite_name +
          '|' +
          value.security_deposite_cost +
          '|' +
          value.security_deposite_applicable +
          '|' +
          value.security_deposite_hourly_cost +
          '" data-name="' +
          value.security_deposite_name +
          '" data-value-in="0" data-applicable="' +
          value.security_deposite_applicable +
          '" data-value="' +
          value.security_deposite_cost +
          '" data-hourly-rate="' +
          value.security_deposite_hourly_cost +
          '" data-currency-before="$" data-currency-after="" class="carrental_extras" />';
        dataHtml += value.security_deposite_name;
        dataHtml += value.extra_meta;
        dataHtml += '</label>';
        dataHtml += '</div>';
      });

      $('.payable-security_deposites').append(dataHtml);
      $('.payable-security_deposites').show();
    }
  }

  function locationSelect(array, selector, containerClass) {
    if (array.data.length == 0) {
      $('.' + selector).chosen('destroy');
      $('.' + selector).remove();
      $('.' + containerClass).hide();
    } else {
      $('.' + selector).chosen('destroy');
      $('.' + selector).remove();

      dataHtml =
        '<select class="redq-select-boxes ' +
        selector +
        ' rnb-select-box" name="' +
        selector +
        '">';
      dataHtml += '<option value="">' + array.placeholder + '</option>';
      $.each(array.data, function(index, value) {
        dataHtml +=
          '<option value="' +
          value.address +
          '|' +
          value.title +
          '|' +
          value.cost +
          '" data-pickup-location-cost="' +
          value.cost +
          '">' +
          value.title +
          '</option>';
      });

      dataHtml += '</select>';

      $('.' + containerClass).append(dataHtml);
      $('.' + selector).chosen();
      $('.' + containerClass).show();
    }
  }

  function personSelect(array, placeholder, selector, containerClass) {
    $('.' + selector).chosen('destroy');
    $('.' + selector).remove();

    dataHtml =
      '<select class="redq-select-boxes ' +
      selector +
      ' rnb-select-box" name="' +
      selector +
      '">';
    dataHtml += '<option value="">' + placeholder + '</option>';
    $.each(array, function(index, value) {
      if (value.person_cost_applicable == 'per_day') {
      } else {
        dataHtml +=
          '<option value="' +
          value.person_count +
          '|' +
          value.person_cost +
          '|' +
          value.person_cost_applicable +
          '|' +
          value.person_hourly_cost +
          '" data-person_cost= "' +
          value.person_cost +
          '" data-person_count="' +
          value.person_count +
          '" data-applicable="' +
          value.person_cost_applicable +
          '">' +
          value.extra_meta +
          '</option>';
      }
    });

    dataHtml += '</select>';

    $('.' + containerClass).append(dataHtml);
    $('.' + selector).chosen();
    $('.' + containerClass).show();
  }

  function calendarInit() {
    /**
     * Configuratin weekend
     *
     * @since 2.0.0
     * @return null
     */

    if (conditional_data.weekends != undefined) {
      var offDaysLength = conditional_data.weekends.length;
      for (var i = 0; i < offDaysLength; i++) {
        offDays.push(parseInt(conditional_data.weekends[i]));
      }
    }

    var domain = '';
    months = '';
    weekdays = '';
    if (
      general_data.lang_domain !== false &&
      general_data.months !== false &&
      general_data.weekdays !== false
    ) {
      (domain = general_data.lang_domain),
        (months = general_data.months.split(',')),
        (weekdays = general_data.weekdays.split(','));
    }
    $.datetimepicker.setLocale(domain);

    /**
     * Configuratin of date picker for pickupdate
     *
     * @since 1.0.0
     * @return null
     */
    $('#pickup-date').change(function(e) {
      $('#pickup-time').val('');
      $('#dropoff-time').val('');
    });

    var final = [];
    if (CALENDAR_DATA.buffer_days) {
      var allDates = CALENDAR_DATA.block_dates.concat(
        CALENDAR_DATA.buffer_days
      );
      final = allDates.filter((v, i, a) => a.indexOf(v) === i);
    }

    var dateTimeOptions = {};
    var datepickerOption = {
      timepicker: false,
      scrollMonth: false,
      dayOfWeekStart: general_data.day_of_week_start
        ? general_data.day_of_week_start
        : 0,
      format: conditional_data.date_format,
      minDate: 0,
      disabledDates: CALENDAR_DATA.block_dates,
      formatDate: conditional_data.date_format,
      onShow: function(ct) {
        $('#dropoff-date').val('');
        this.setOptions({
          minDate: 0,
          disabledDates: final,
        });
      },
      onSelectDate: function(ct, $i) {
        var allowedTimes = CALENDAR_DATA.allowed_datetime;
        if (
          allowedTimes[ct.dateFormat(conditional_data.date_format)] != undefined
        ) {
          if (
            allowedTimes[ct.dateFormat(conditional_data.date_format)].length ==
            0
          ) {
            $('#pickup-time').datetimepicker({
              datepicker: false,
              timepicker: false,
            });
            $('#dropoff-time').datetimepicker({
              datepicker: false,
              timepicker: false,
            });
          } else {
            // $('#pickup-time').datetimepicker('destroy');
            // $('#dropoff-time').datetimepicker('destroy');
            $('#pickup-time').datetimepicker({
              datepicker: false,
              format:
                conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
              formatTime:
                conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
              allowTimes:
                allowedTimes[ct.dateFormat(conditional_data.date_format)],
            });
            if ($('#dropoff-date').length == 0) {
              $('#dropoff-time').datetimepicker({
                datepicker: false,
                format:
                  conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
                formatTime:
                  conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
                allowTimes:
                  allowedTimes[ct.dateFormat(conditional_data.date_format)],
              });
            }
          }
        } else {
          $('#pickup-time').datetimepicker('destroy');
          $('#dropoff-time').datetimepicker('destroy');

          $('#pickup-time').datetimepicker({
            datepicker: false,
            format:
              conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
            formatTime:
              conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
            step: conditional_data.time_interval
              ? parseInt(conditional_data.time_interval)
              : 5,
            scrollInput: false,
            onShow: OpeningClosingTimeLogic,
            allowTimes: conditional_data.allowed_times,
          });

          $('#dropoff-time').datetimepicker({
            datepicker: false,
            format:
              conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
            formatTime:
              conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
            step: conditional_data.time_interval
              ? parseInt(conditional_data.time_interval)
              : 5,
            scrollInput: false,
            onShow: DropOffOpeningClosingTimeLogic,
            allowTimes: conditional_data.allowed_times,
          });
        }
      },
      disabledWeekDays: offDays,
      i18n: {
        domain: {
          months: months,
          dayOfWeek: weekdays,
        },
      },
      scrollInput: false,
    };

    if (window.innerWidth <= 480) {
      $('#pickup-date').datetimepicker('destroy');
      $('#pickup-date').on('click', function() {
        $('#pickup-modal-body ').show();
        $('#mobile-datepicker').datetimepicker({
          scrollMonth: false,
          inline: true,
          timepicker: false,
          minDate: 0,
          dayOfWeekStart: general_data.day_of_week_start
            ? general_data.day_of_week_start
            : 0,
          format: conditional_data.date_format,
          formatDate: conditional_data.date_format,
          disabledDates: final,
          disabledWeekDays: offDays,
          i18n: {
            domain: {
              months: months,
              dayOfWeek: weekdays,
            },
          },
          onShow: function(ct) {
            $('#dropoff-date').val('');
          },
          onSelectDate: function(ct) {
            dateTimeOptions['date'] = ct;
          },
          onSelectTime: function(ct) {
            dateTimeOptions['time'] = ct;
          },
        });
        $('#cal-close-btn').on('click', function() {
          $('#pickup-modal-body ').hide();
          $('#pickup-date').datetimepicker('destroy');
        });
        $('#cal-submit-btn').on('click', function() {
          $('#pickup-modal-body ').hide();
          $('#pickup-date').datetimepicker(
            Object.assign(datepickerOption, {
              value: dateTimeOptions['date'],
            })
          );
          $('#pickup-time').datetimepicker({
            format:
              conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
            formatTime:
              conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
            value: dateTimeOptions['time'],
          });
          $('form.cart').trigger('change');
          $('#pickup-date').datetimepicker('destroy');
        });
      });
    } else {
      $('#pickup-date').datetimepicker('destroy');
      $('#pickup-date').datetimepicker(datepickerOption);
    }

    /**
     * Configuratin of time picker for pickuptime
     *
     * @since 1.0.0
     * @return null
     */
    var weekDaysAra = ['sun', 'mon', 'thu', 'wed', 'thur', 'fri', 'sat'];
    var opening_closing = validation_data.openning_closing;
    const opening_closing_copy = clone(opening_closing);

    function rnb_handle_time_restriction(currentDateTime, calendarDate) {
      var euroFormat = conditional_data.euro_format,
        selectedDay,
        selectedDate;

      if (euroFormat === 'yes') {
        var splitDate = calendarDate.split('/'),
          finalDate = `${splitDate[1]}/${splitDate[0]}/${splitDate[2]}`;
        selectedDay = new Date(finalDate).getDay();
        selectedDate = new Date(finalDate).getDate();
      } else {
        selectedDay = new Date(calendarDate).getDay();
        selectedDate = new Date(calendarDate).getDate();
      }

      // Checking for todays min time
      var getToday = currentDateTime.getDay();
      var todayMinTime =
        conditional_data.time_format === '24-hours'
          ? currentDateTime.toLocaleString('en-US', {
              hour: 'numeric',
              minute: 'numeric',
              hour12: false,
            })
          : currentDateTime.toLocaleString('en-US', {
              hour: 'numeric',
              minute: 'numeric',
              hour12: true,
            });

      if (currentDateTime.getDate() === selectedDate) {
        opening_closing[weekDaysAra[getToday]].min = todayMinTime;
      } else {
        opening_closing = opening_closing_copy;
      }

      return [selectedDay, opening_closing];
    }

    var OpeningClosingTimeLogic = function(currentDateTime) {
      var pickupDate = $('#pickup-date').val();
      var results = rnb_handle_time_restriction(currentDateTime, pickupDate);
      var selectedDay = results[0],
        opening_closing = results[1];
      this.setOptions({
        minTime:
          conditional_data.time_format === '24-hours'
            ? opening_closing[weekDaysAra[selectedDay]].min
            : timeConvert(opening_closing[weekDaysAra[selectedDay]].min),
        maxTime:
          conditional_data.time_format === '24-hours'
            ? opening_closing[weekDaysAra[selectedDay]].max
            : timeConvert(opening_closing[weekDaysAra[selectedDay]].max),
        format: conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
        formatTime:
          conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
      });
    };
    $('#pickup-time').datetimepicker({
      datepicker: false,
      format: conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
      formatTime: conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
      step: conditional_data.time_interval
        ? parseInt(conditional_data.time_interval)
        : 5,
      scrollInput: false,
      onShow: OpeningClosingTimeLogic,
      allowTimes: conditional_data.allowed_times,
    });

    /**
     * Configuratin of time picker for dropoffdate
     *
     * @since 1.0.0
     * @return null
     */
    $('#dropoff-date').change(function(e) {
      $('#dropoff-time').val('');
    });
    // start new code
    var dropDateTimeOptions = {};
    var dropDatepickerOption = {
      timepicker: false,
      scrollMonth: false,
      dayOfWeekStart: general_data.day_of_week_start
        ? general_data.day_of_week_start
        : 0,
      format: conditional_data.date_format,
      minDate: 0,
      disabledDates: CALENDAR_DATA.block_dates,
      formatDate: conditional_data.date_format,
      formatTime: 'H:i',
      onShow: function(ct) {
        this.setOptions({
          minDate: $('#pickup-date').val() ? $('#pickup-date').val() : 0,
          disabledDates: final,
        });
      },
      disabledWeekDays: offDays,
      i18n: {
        domain: {
          months: months,
          dayOfWeek: weekdays,
        },
      },
      scrollInput: false,
    };

    if (window.innerWidth <= 480) {
      $('#dropoff-date').on('click', function() {
        var minDate = $('#pickup-date').val() ? $('#pickup-date').val() : 0;
        $('#dropoff-modal-body ').show();
        $('#drop-mobile-datepicker').datetimepicker({
          scrollMonth: false,
          inline: true,
          timepicker: false,
          minDate: minDate,
          disabledWeekDays: offDays,
          i18n: {
            domain: {
              months: months,
              dayOfWeek: weekdays,
            },
          },
          dayOfWeekStart: general_data.day_of_week_start
            ? general_data.day_of_week_start
            : 0,
          format: conditional_data.date_format,
          formatDate: conditional_data.date_format,
          disabledDates: final,
          formatTime: 'H:i',

          onSelectDate: function(ct) {
            dropDateTimeOptions['date'] = ct;
          },
          onSelectTime: function(ct) {
            dropDateTimeOptions['time'] = ct;
          },
        });
        $('#drop-cal-close-btn').on('click', function() {
          $('#dropoff-modal-body ').hide();
          $('#dropoff-date').datetimepicker('destroy');
        });
        $('#drop-cal-submit-btn').on('click', function() {
          $('#dropoff-modal-body ').hide();

          $('#dropoff-date').datetimepicker(
            Object.assign(dropDatepickerOption, {
              value: dropDateTimeOptions['date'],
            })
          );
          $('#dropoff-time').datetimepicker({
            format:
              conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
            formatTime:
              conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
            value: dropDateTimeOptions['time'],
          });
          $('form.cart').trigger('change');
          $('#dropoff-date').datetimepicker('destroy');
        });
      });
    } else {
      $('#dropoff-date').datetimepicker('destroy');
      $('#dropoff-date').datetimepicker(dropDatepickerOption);
    }
    // end new code

    /**
     * Configuratin of time picker for pickuptime
     *
     * @since 1.0.0
     * @return null
     */
    var DropOffOpeningClosingTimeLogic = function(currentDateTime) {
      var dropoffDate = $('#dropoff-date').val()
        ? $('#dropoff-date').val()
        : $('#pickup-date').val();
      var results = rnb_handle_time_restriction(currentDateTime, dropoffDate);
      var selectedDay = results[0],
        opening_closing = results[1];

      var minTime;

      if ($('#dropoff-date').length > 0) {
        if ($('#pickup-date').val() === $('#dropoff-date').val()) {
          if ($('#pickup-time').val() != '') {
            var time = $('#pickup-time').val();
            var minsToAdd = conditional_data.time_interval;

            minTime = new Date(
              new Date('1970/01/01 ' + time).getTime() + minsToAdd * 60000
            )
              .toLocaleTimeString('en-US', {
                hour: 'numeric',
                hour12:
                  conditional_data.time_format === '24-hours' ? false : true,
                minute: 'numeric',
              })
              .replace('AM', 'am')
              .replace('PM', 'pm');
          }
        }
      } else {
        if ($('#pickup-time').val() != '') {
          var time = $('#pickup-time').val();
          var minsToAdd = conditional_data.time_interval;

          minTime = new Date(
            new Date('1970/01/01 ' + time).getTime() + minsToAdd * 60000
          )
            .toLocaleTimeString('en-US', {
              hour: 'numeric',
              hour12:
                conditional_data.time_format === '24-hours' ? false : true,
              minute: 'numeric',
            })
            .replace('AM', 'am')
            .replace('PM', 'pm');
        }
      }

      this.setOptions({
        minTime:
          minTime !== undefined
            ? minTime
            : conditional_data.time_format === '24-hours'
            ? opening_closing[weekDaysAra[selectedDay]].min
            : timeConvert(opening_closing[weekDaysAra[selectedDay]].min),
        maxTime:
          conditional_data.time_format === '24-hours'
            ? opening_closing[weekDaysAra[selectedDay]].max
            : timeConvert(opening_closing[weekDaysAra[selectedDay]].max),
        format: conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
        formatTime:
          conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
      });
    };

    $('#dropoff-time').datetimepicker({
      datepicker: false,
      format: conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
      formatTime: conditional_data.time_format === '24-hours' ? 'H:i' : 'h:i a',
      step: conditional_data.time_interval
        ? parseInt(conditional_data.time_interval)
        : 5,
      scrollInput: false,
      onShow: DropOffOpeningClosingTimeLogic,
      allowTimes: conditional_data.allowed_times,
    });

    function timeConvert(time) {
      if (time === '24:00') {
        return '11:59 pm';
      }
      time = time
        .toString()
        .match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
      if (time.length > 1) {
        time = time.slice(1);
        time[5] = +time[0] < 12 ? ' am' : ' pm';
        time[0] = +time[0] % 12 || 12;
      }
      return time.join('');
    }
  }

  /**
   * Initialize calendar
   *
   * @since 1.0.0
   * @version 5.9.0
   * @return null
   */
  calendarInit();

  /**
   * Configuratin others pluins
   *
   * @since 1.0.0
   * @return null
   */
  $('.redq-select-boxes').chosen();
  $('.price-showing').hide();
  $('.rnb-pricing-plan-link').click(function(e) {
    e.preventDefault();
    $('.price-showing').slideToggle();
  });

  // RnB modal
  $('#showBooking').on('click', function() {
    $('#animatedModal').toggleClass('zoomIn');
    $('body').addClass('rnbOverflow');
    inventorySwitching();
  });

  $('.close-animatedModal i').on('click', function() {
    $('#animatedModal').removeClass('zoomIn');
    $('body').removeClass('rnbOverflow');
  });
  var wizardLength = $('#rnbSmartwizard').find('h3').length;

  var wizard = $('#rnbSmartwizard').steps({
    stepsOrientation: 'vertical',
    headerTag: 'h3',
    bodyTag: 'section',
    transitionEffect: 'fade',
    enableFinishButton: true,
    autoFocus: true,
    onFinished: function(event, currentIndex) {
      event.preventDefault();
      $('li.book-now').show();
    },
    onStepChanging: function(event, currentIndex, newIndex) {
      switch (currentIndex) {
        case 0:
          var pickupLoc = $('.rnb-pickup-location').val(),
            dropoffLoc = $('.rnb-dropoff-location').val();

          //Pickup location is required
          if (validation_data.pickup_location === 'open' && pickupLoc === '') {
            $('input.rnb-pickup-location').css('border', '1px solid red');
            return false;
          }

          //dropoff location is required
          if (validation_data.return_location === 'open' && dropoffLoc === '') {
            $('input.rnb-dropoff-location').css('border', '1px solid red');
            return false;
          }

          $('input.rnb-pickup-location').css('border', '1px solid #eee');
          $('input.rnb-dropoff-location').css('border', '1px solid #eee');

          return true;
        default:
          return true;
      }
    },
    onStepChanged: function(event, currentIndex, priorIndex) {
      switch (currentIndex) {
        case 0:
          $('.title-wrapper h3').html(
            layout_two_labels.location.location_top_heading
          );
          $('.title-wrapper p').html(
            layout_two_labels.location.location_top_desc
          );
          $('#rnbSmartwizard .actions ul li:nth-child(2)').removeClass(
            'disabledNextOnModal'
          );
          $('li.book-now').hide();
          return true;
        case 1:
          $('.title-wrapper h3').html(
            layout_two_labels.datetime.date_top_heading
          );
          $('.title-wrapper p').html(layout_two_labels.datetime.date_top_desc);
          // ****************** modal form validation start ******************
          $('#rnbSmartwizard .actions ul li:nth-child(2)')
            .addClass('disabled disabledNextOnModal')
            .attr('aria-disabled', 'true');
          $('.date-error-message').hide();

          var rnb_error_data = window.Rnb_error_check
            ? window.Rnb_error_check
            : false;
          if (rnb_error_data !== false) {
            $('#rnbSmartwizard .actions ul li:nth-child(2)')
              .removeClass('disabled disabledNextOnModal')
              .addClass('proceedOnModal')
              .attr('aria-disabled', 'false');
          } else {
            $('#rnbSmartwizard .actions ul li:nth-child(2)')
              .addClass('disabled disabledNextOnModal')
              .attr('aria-disabled', 'true');
          }
          // ****************** modal form validation end ******************
          $('li.book-now').hide();
        case 2:
          $('.title-wrapper h3').html(
            layout_two_labels.resource.resource_top_heading
          );
          $('.title-wrapper p').html(
            layout_two_labels.resource.resource_top_desc
          );
          $('li.book-now').hide();
          return true;
        case 3:
          $('.title-wrapper h3').html(
            layout_two_labels.person.person_top_heading
          );
          $('.title-wrapper p').html(layout_two_labels.person.person_top_desc);
          $('li.book-now').hide();
          return true;
        case 4:
          $('.title-wrapper h3').html(
            layout_two_labels.deposit.deposit_top_heading
          );
          $('.title-wrapper p').html(
            layout_two_labels.deposit.deposit_top_desc
          );
          $('li.book-now').hide();
          return true;
        case 5:
          $('.title-wrapper h3').html('Summary');
          $('.title-wrapper p').html('Summary Desc');
          return true;
        default:
          return true;
      }
      return false;
    },
    onInit: function(event, currentIndex) {
      calendarInit();
      return true;
    },
    labels: {
      cancel: 'Cancel',
      current: 'current step:',
      pagination: 'Pagination',
      finish: 'Finish Process',
      next: 'Next',
      previous: 'Previous',
      loading: 'Loading ...',
    },
  });

  var $input = $(
    '<li class="book-now" style="display: none;"><button type="submit" class="single_add_to_cart_button redq_add_to_cart_button btn-book-now button alt">Book Now</button></li>'
  );
  $input.appendTo($('ul[aria-label=Pagination]'));

  // Checkbox Class Toggle
  $('.rnb-control-checkbox input[type="checkbox"]').change(function() {
    $('label[for=rnb-resource-' + $(this).data('items') + ']').toggleClass(
      'checked'
    );
  });

  $('.rnb-control-checkbox.rnb-deposit-label input[type="checkbox"]').change(
    function() {
      $('label[for=rnb-deposit-' + $(this).data('items') + ']').toggleClass(
        'checked'
      );
    }
  );

  // Radio Class Toggle
  $('.rnb-control-radio.rnb-adult-label input[type="radio"]').bind(
    'click',
    function() {
      $('label.rnb-control-radio.rnb-adult-label').removeClass('checked');
      $('label[for=rnb-adult-' + $(this).data('items') + ']').addClass(
        'checked'
      );
    }
  );

  $('.rnb-control-radio.rnb-child-label input[type="radio"]').bind(
    'click',
    function() {
      $('label.rnb-control-radio.rnb-child-label').removeClass('checked');
      $('label[for=rnb-child-' + $(this).data('items') + ']').addClass(
        'checked'
      );
    }
  );

  /**
   * Configuratin for RFQ
   *
   * @since 1.0.0
   * @return null
   */
  $('.quote-submit i').hide();
  $('#quote-content-confirm').magnificPopup({
    items: {
      src: '#quote-popup',
      type: 'inline',
    },
    preloader: false,
    focus: '#quote-username',

    // When elemened is focused, some mobile browsers in some cases zoom in
    // It looks not nice, so we disable it:
    callbacks: {
      beforeOpen: function() {
        if ($(window).width() < 700) {
          this.st.focus = false;
        } else {
          this.st.focus = '#quote-username';
        }
      },
      open: function() {},
    },
  });

  $('.quote-submit').on('click', function(e) {
    e.preventDefault();
    $('.quote-submit i').show();
    var cartData = $('.cart').serializeArray();
    var modalForm = {
      quote_username: $('#quote-username').val(),
      quote_password: $('#quote-password').val(),
      quote_first_name: $('#quote-first-name').val(),
      quote_last_name: $('#quote-last-name').val(),
      quote_email: $('#quote-email').val(),
      quote_phone: $('#quote-phone').val(),
      quote_message: $('#quote-message').val(),
    };

    var product_id = $('.product_id').val(),
      quote_price = $('.quote_price').val();

    var errorMsg = '';
    var proceed = true;
    $(
      '#quote-popup input[required=true], #quote-popup textarea[required=true]'
    ).each(function() {
      if (!$.trim($(this).val())) {
        //if this field is empty
        var atrName = $(this).attr('name');

        if (atrName == 'quote-first-name') {
          errorMsg += `${translatedStrings.quote_first_name}<br>`;
        } else if (atrName == 'quote-email') {
          errorMsg += `${translatedStrings.quote_email}<br>`;
        } else if (atrName == 'quote-message') {
          errorMsg += `${translatedStrings.quote_message}<br>`;
        } else if (atrName == 'quote-last-name') {
          errorMsg += `${translatedStrings.quote_last_name}<br>`;
        } else if (atrName == 'quote-phone') {
          errorMsg += `${translatedStrings.quote_phone}<br>`;
        } else if (atrName == 'quote-username') {
          errorMsg += `${translatedStrings.quote_user_name}<br>`;
        } else if (atrName == 'quote-password') {
          errorMsg += `${translatedStrings.quote_password}<br>`;
        }
        proceed = false; //set do not proceed flag
      }
      //check invalid email
      var email_reg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
      if (
        $(this).attr('type') == 'email' &&
        !email_reg.test($.trim($(this).val()))
      ) {
        $(this)
          .parent()
          .addClass('has-error');
        proceed = false; //set do not proceed flag
        errorMsg += 'Email Must be valid & required!<br>';
      }

      if (errorMsg !== undefined && errorMsg !== '') {
        $('.quote-modal-message')
          .slideDown()
          .html(errorMsg);
        $('.quote-submit i').hide();
      }
    });
    if (proceed) {
      cartData.push({ forms: modalForm });
      var quote_params = {
        action: 'redq_request_for_a_quote',
        form_data: cartData,
        product_id: product_id,
        quote_price: quote_price,
      };

      $.ajax({
        url: REDQ_RENTAL_API.ajax_url,
        dataType: 'json',
        type: 'POST',
        data: quote_params,
        success: function(response) {
          $('.quote-modal-message').html(response.message);
          if (response.status_code === 200) {
            //$("#quote-popup").magnificPopup("close");
            $('.quote-submit i').hide();
          }
        },
      });
    }
  });

  /**
   * Configuratin for select fields validation checking
   *
   * @since 3.0.4
   * @return null
   */
  $('.pickup_location').on('change', function() {
    var val = $(this).val();
    if (val) {
      $('.pickup_location')
        .next('.select2-container')
        .css('border', '1px solid #bbb');
    }
  });

  $('.dropoff_location').on('change', function() {
    var val = $(this).val();
    if (val) {
      $('.dropoff_location')
        .next('.select2-container')
        .css('border', '1px solid #bbb');
    }
  });

  $('.additional_adults_info').on('change', function() {
    var val = $(this).val();
    if (val) {
      $('.additional_adults_info')
        .next('.select2-container')
        .css('border', '1px solid #bbb');
    }
  });

  $('#pickup-time').on('change', function() {
    var val = $(this).val();
    if (val) {
      $('#pickup-time').css('border', '1px solid #bbb');
    }
  });

  $('#dropoff-time').on('change', function() {
    var val = $(this).val();
    if (val) {
      $('#dropoff-time').css('border', '1px solid #bbb');
    }
  });

  $('.redq_add_to_cart_button').on('click', function(e) {
    var flag = false,
      validate_messages = [];

    if (validation_data.pickup_location === 'open') {
      var plocation = $('.pickup_location').val();
      if (!plocation && typeof plocation != 'undefined') {
        $('.pickup_location')
          .next('.chosen-container')
          .css('border', '1px solid red');
        validate_messages.push(translatedStrings.pickup_loc_required);
        flag = true;
      }
    }
    if (validation_data.return_location === 'open') {
      var dlocation = $('.dropoff_location').val();
      if (!dlocation && typeof dlocation != 'undefined') {
        $('.dropoff_location')
          .next('.chosen-container')
          .css('border', '1px solid red');
        validate_messages.push(translatedStrings.dropoff_loc_required);
        flag = true;
      }
    }
    if (validation_data.person === 'open') {
      var person = $('.additional_adults_info').val();
      if (!person && typeof person != 'undefined') {
        $('.additional_adults_info')
          .next('.chosen-container')
          .css('border', '1px solid red');
        validate_messages.push(translatedStrings.adult_required);
        flag = true;
      }
    }
    if (validation_data.pickup_time === 'open') {
      var pickup_time = $('#pickup-time').val();
      if (!pickup_time && typeof pickup_time != 'undefined') {
        $('#pickup-time').css('border', '1px solid red');
        validate_messages.push(translatedStrings.pickup_time_required);
        flag = true;
      }
    }
    if (validation_data.return_time === 'open') {
      var return_time = $('#dropoff-time').val();
      if (!return_time && typeof return_time != 'undefined') {
        $('#dropoff-time').css('border', '1px solid red');
        validate_messages.push(translatedStrings.dorpoff_time_required);
        flag = true;
      }
    }

    if (flag && validate_messages.length) {
      var preWrapper = '<ul class="validate-notice woocommerce-error">',
        postWrapper = '</ul>',
        notices = validate_messages.map(notice => {
          return `<li>${notice}</li>`;
        }),
        validateMarkup = `${preWrapper} ${notices.join(' ')} ${postWrapper}`;

      $('.rnb-notice').html(validateMarkup);
    }

    if (flag === true) e.preventDefault();
  });
});
