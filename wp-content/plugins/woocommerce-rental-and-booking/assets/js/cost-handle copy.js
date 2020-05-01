function rnbCostHandle() {
  jQuery(document).ready(function($) {
    'use strict';
    $('.show_if_time').hide();
    $('.redq-quantity').hide();

    var translated_strings = BOOKING_DATA.translated_strings,
      pricing_data = BOOKING_DATA.rnb_data.pricings,
      conditional_data = BOOKING_DATA.rnb_data.settings.conditions,
      field_labels = BOOKING_DATA.rnb_data.settings.labels,
      availability_data = BOOKING_DATA.availability;

    //Prevent insert number manually in quantity input field
    // $(".redq-quantity [type='number']").keypress(function(evt) {
    //   evt.preventDefault();
    // });

    $('form.cart').on('change', function() {
      $('.redq_add_to_cart_button').attr('disabled', 'disabled');
      $('.redq_request_for_a_quote').attr('disabled', 'disabled');

      var bookingSummary = [];

      var error = new Array(),
        max_rental_days = conditional_data.max_book_days,
        min_rental_days = conditional_data.min_book_days;

      var formData = $(this).serializeArray(),
        dataObj = {};

      $(formData).each(function(i, field) {
        dataObj[field.name] = field.value;
      });

      /**
       * Configuring data
       *
       * @since 1.0.3
       * @return null
       */
      var date_format,
        selected_qty = dataObj.inventory_quantity
          ? dataObj.inventory_quantity
          : 1;

      if (conditional_data.date_format.toLowerCase() === 'd/m/y') {
        date_format = 'd/M/yyyy';
      }

      if (conditional_data.date_format.toLowerCase() === 'm/d/y') {
        date_format = 'MM/d/yyyy';
      }

      if (conditional_data.date_format.toLowerCase() === 'y/m/d') {
        date_format = 'yyyy/MM/d';
      }

      if (dataObj.dropoff_date == undefined) {
        if (
          dataObj.pickup_time == undefined ||
          dataObj.dropoff_time == undefined
        ) {
          dataObj.dropoff_date = dataObj.pickup_date;
        } else {
          dataObj.dropoff_date = dataObj.pickup_date;
        }
      }

      if (dataObj.pickup_date == undefined) {
        if (
          dataObj.pickup_time == undefined ||
          dataObj.dropoff_time == undefined
        ) {
          dataObj.pickup_date = dataObj.dropoff_date;
        } else {
          dataObj.pickup_date = dataObj.dropoff_date;
        }
      }

      if (dataObj.pickup_time == undefined) {
        if (dataObj.pickup_time == undefined && dataObj.dropoff_time) {
          dataObj.pickup_time = dataObj.dropoff_time;
        } else {
          dataObj.pickup_time = '';
        }
      }

      if (dataObj.dropoff_time == undefined) {
        if (dataObj.dropoff_time == undefined && dataObj.pickup_time) {
          dataObj.dropoff_time = dataObj.pickup_time;
        } else {
          dataObj.dropoff_time = '';
        }
      }

      // calcute days and prices
      if (
        dataObj.pickup_date != undefined &&
        dataObj.pickup_date != '' &&
        dataObj.dropoff_date != undefined &&
        dataObj.dropoff_date != ''
      ) {
        $('.booking-pricing-info').show();
        $('.single_add_to_cart_button').removeAttr('disabled', 'disabled');

        /**
         * Handling days and hours
         *
         * @since 1.0.0
         * @return null
         */
        if (conditional_data.date_format == 'd/m/Y') {
          var splitPickupDate = dataObj.pickup_date.split('/'),
            splitDropoffDate = dataObj.dropoff_date.split('/');

          if (parseInt(splitPickupDate[0]) < 13) {
            var pickupDate =
              splitPickupDate[1] +
              '/' +
              splitPickupDate[0] +
              '/' +
              splitPickupDate[2];
          } else {
            var pickupDate = Date.parse(dataObj.pickup_date).toString(
              'M/d/yyyy'
            );
          }

          if (parseInt(splitDropoffDate[0]) < 13) {
            var dropoffDate =
              splitDropoffDate[1] +
              '/' +
              splitDropoffDate[0] +
              '/' +
              splitDropoffDate[2];
          } else {
            var dropoffDate = Date.parse(dataObj.dropoff_date).toString(
              'M/d/yyyy'
            );
          }
        } else {
          var pickupDate = Date.parse(dataObj.pickup_date).toString('M/d/yyyy'),
            dropoffDate = Date.parse(dataObj.dropoff_date).toString('M/d/yyyy');
        }

        if (dataObj.pickup_time != '' && dataObj.dropoff_time == '') {
          var pickupTime = dataObj.pickup_time,
            dropoffTime = pickupTime;
        } else {
          var pickupTime = dataObj.pickup_time,
            dropoffTime = dataObj.dropoff_time;
        }

        var pickupDateTime = pickupDate + ' ' + pickupTime,
          dropoffDateTime = dropoffDate + ' ' + dropoffTime;

        bookingSummary.push(
          {
            type: 'datetime',
            name: field_labels.pickup_datetime,
            value: `${pickupDate} at ${pickupTime}`,
          },
          {
            type: 'datetime',
            name: field_labels.return_datetime,
            value: `${dropoffDate} at ${dropoffTime}`,
          }
        );

        var start = new Date(pickupDateTime),
          end = new Date(dropoffDateTime),
          diff = end.getTime() - start.getTime(),
          hours = diff / 3600000,
          days,
          total_hours = Math.ceil(hours);

        var enableSingleDayTimeBooking = conditional_data.single_day_booking;

        if (hours < 24) {
          if (enableSingleDayTimeBooking == 'open') {
            days = 1;
          } else {
            days = 0;
            $('.show_if_time').show();
            $('.additional_adults_info').trigger('chosen:updated');
            $('.additional_childs_info').trigger('chosen:updated');
            $('.show_adults_cost_if_day').hide();
            $('.show_adults_cost_if_time').show();
            $('.show_childs_cost_if_day').hide();
            $('.show_childs_cost_if_time').show();
            $('.show_if_day')
              .children('span')
              .hide();
            $('.single_add_to_cart_button').removeAttr('disabled', 'disabled');
          }
        } else {
          days = parseInt(hours / 24);
          var extra_hours = hours % 24;
          if (enableSingleDayTimeBooking == 'open') {
            if (extra_hours >= parseFloat(conditional_data.max_time_late)) {
              days = days + 1;
            }
          } else {
            if (extra_hours > parseFloat(conditional_data.max_time_late)) {
              days = days + 1;
            }
          }

          $('.show_adults_cost_if_day').show();
          $('.show_adults_cost_if_time').hide();
          $('.show_childs_cost_if_day').show();
          $('.show_childs_cost_if_time').hide();

          $('.additional_adults_info').trigger('chosen:updated');
          $('.additional_childs_info').trigger('chosen:updated');
          $('.show_if_day')
            .children('span')
            .show();
          $('.show_if_time').hide();
        }

        if (pricing_data.pricing_type === 'flat_hours') {
          $('.show_if_time').show();
          $('.show_adults_cost_if_day').hide();
          $('.show_adults_cost_if_time').show();
          $('.show_childs_cost_if_day').hide();
          $('.show_childs_cost_if_time').show();
          $('.show_if_day')
            .children('span')
            .hide();
          $('.additional_adults_info').trigger('chosen:updated');
          $('.additional_childs_info').trigger('chosen:updated');
        }

        /**
         * Find Available Quantity
         *
         * @since 5.5.6
         * @return null
         */
        // if (days > 0) {

        // var quantity = 'false';
        var ajax_preload = [];
        var fire_ajax = false;
        if ($('#pickup-date').length > 0) {
          ajax_preload.push('pickup_date');
        }
        if ($('#dropoff-date').length > 0) {
          ajax_preload.push('dropoff_date');
        }

        if ($('#pickup-time').length > 0) {
          ajax_preload.push('dropoff_time');
        }
        if ($('#dropoff-time').length > 0) {
          ajax_preload.push('dropoff_time');
        }
        $.each(ajax_preload, function(index, value) {
          if (dataObj[value] != '') {
            fire_ajax = true;
          } else {
            fire_ajax = false;
          }
        });

        if (fire_ajax) {
          $('.rnb-cart').append(
            '<div class="rnb-spinner"><div class="fa fa-spinner fa-spin"></div></div>'
          );
          $.ajax({
            type: 'post',
            dataType: 'json',
            url: AJAX_DATA.ajaxurl,
            data: {
              action: 'rnb_get_inventory_quantity',
              form: dataObj,
              nonce: AJAX_DATA.nonce,
            },
            success: function(response) {
              if (
                conditional_data.blockable !== 'no' &&
                parseInt(selected_qty) > parseInt(response.avaialable)
              ) {
                var quantity_msg =
                  selected_qty > 1
                    ? translated_strings.qty_plural_msg
                    : translated_strings.qty_singular_msg;
                error.push(`${selected_qty} ${quantity_msg}`);
              }

              if (response.avaialable > 0) {
                $('input.inventory-qty').attr({
                  max: response.avaialable,
                  min: 1,
                });
                if ($('.redq-quantity').length > 0) {
                  $('.redq-quantity').show();
                }
                $('.redq_add_to_cart_button').focus();
              }

              Rnb_error_check_func(error, translated_strings);
              $('.rnb-spinner').remove();
            },
          });
        } else {
          $('.redq-quantity').hide();
        }
        // var quantity = rnb_check_available_quantity(
        //   days,
        //   pickupDateTime,
        //   dropoffDateTime
        // );
        // $('input.inventory-qty').attr({
        //   max: quantity,
        //   min: 1,
        // });
        // } else {
        //   $('.redq-quantity').hide();
        // }

        /**
         * Handling book now button on/off
         *
         * @since 1.0.0
         * @return null
         */
        var selected_days = new Array(),
          flag = 0,
          format;

        if (conditional_data.date_format === 'Y/m/d') {
          format = 'yyyy/MM/dd';
        }

        if (conditional_data.date_format === 'm/d/Y') {
          format = 'MM/dd/yyyy';
        }

        if (conditional_data.date_format === 'd/m/Y') {
          format = 'dd/MM/yyyy';
        }

        for (var i = 0; i < parseInt(days); i++) {
          if (i == 0) {
            selected_days.push(Date.parse(pickupDate).toString(format));
          } else {
            selected_days.push(
              Date.parse(pickupDate)
                .add(i)
                .day()
                .toString(format)
            );
          }
        }

        if (days < 0) {
          error.push(translated_strings.positive_days);
        }

        if (total_hours < 0) {
          error.push(translated_strings.positive_hours);
        }

        // if (conditional_data.blockable !== 'no' && selected_qty > BOOKING_DATA.quantity) {
        //   var quantity_msg =
        //     selected_qty > 1
        //       ? translated_strings.qty_plural_msg
        //       : translated_strings.qty_singular_msg;
        //   error.push(`${selected_qty} ${quantity_msg}`);
        // }

        if (conditional_data.blockable !== 'no') {
          for (var i = 0; i < selected_days.length; i++) {
            for (var j = 0; j < BOOKING_DATA.block_dates.length; j++) {
              if (flag == 0) {
                if (selected_days[i] == BOOKING_DATA.block_dates[j]) {
                  error.push(translated_strings.unavailable_date_range);
                  flag = 1;
                } else {
                  //$('.single_add_to_cart_button').removeAttr('disabled','disabled');
                }
              }
            }
          }
        }

        if (parseInt(days) > parseInt(max_rental_days)) {
          var max_rental_day_msg =
            parseInt(max_rental_days) <= 1
              ? translated_strings.singular_max_booking_day_msg
              : translated_strings.plural_max_booking_days_msg;
          error.push(
            `${max_rental_day_msg} ${max_rental_days} ${
              translated_strings.exceed_text
            }`
          );
        }

        if (parseInt(days) < parseInt(min_rental_days)) {
          var min_rental_day_msg =
            parseInt(min_rental_days) <= 1
              ? translated_strings.singular_min_booking_day_msg
              : translated_strings.plural_min_booking_days_msg;
          error.push(`${min_rental_day_msg} ${min_rental_days}`);
        }

        /**
         * Handling resources
         *
         * @since 1.0.0
         * @return null
         */
        var extras_pricing_plan = {};
        extras_pricing_plan.extras = $("input[name='extras[]']:checked")
          .map(function() {
            var extras = {
              name: $(this).data('name'),
              cost: $(this).data('value'),
              hourly_cost: $(this).data('hourly-rate'),
              applicable: $(this).data('applicable'),
            };

            bookingSummary.push({
              type: 'resource',
              name: extras.name,
              value: `${formatCurrency(extras.cost)} - ${extras.applicable}`,
            });

            return extras;
          })
          .get();

        /**
         * Handling categories
         *
         * @since 1.0.0
         * @return null
         */
        var categories_pricing = {};

        categories_pricing.cat = $("input[name='categories[]']:checked")
          .map(function() {
            var maxQty = $(this)
              .parent()
              .next('.quantity')
              .children('input')
              .attr('max');
            var qantityVal = $(this)
              .parent()
              .next('.quantity')
              .children('input')
              .val();

            if (parseInt(qantityVal) > parseInt(maxQty)) {
              $(this)
                .parent()
                .next('.quantity')
                .children('input')
                .css('border', '1px solid red');

              error.push(`Max value ${maxQty} exceed`);
            }

            var cat = {
              name: $(this).data('name'),
              cost: $(this).data('value'),
              hourly_cost: $(this).data('hourlyrate'),
              applicable: $(this).data('applicable'),
              quantity: $(this)
                .parent()
                .next('.quantity')
                .children('input')
                .val(),
            };

            bookingSummary.push({
              type: 'category',
              name: cat.name,
              value: ` - ${formatCurrency(cat.cost)}- ${cat.applicable}`,
            });

            var newValue =
              cat.name +
              '|' +
              cat.cost +
              '|' +
              cat.applicable +
              '|' +
              cat.hourly_cost +
              '|' +
              cat.quantity;
            $(this).val(newValue);

            return cat;
          })
          .get();

        /**
         * Handling Adults
         *
         * @since 1.0.0
         * @return null
         */
        var adults_cost,
          adults_hourly_cost,
          adults_count,
          acost_applicable = '';

        if (conditional_data.booking_layout === 'layout_one') {
          var selectedAdult = $('.additional_adults_info').find(':selected');
          (adults_cost = selectedAdult.data('person_cost')),
            (adults_hourly_cost = selectedAdult.data('person_hourly_cost')),
            (adults_count = selectedAdult.data('person_count')),
            (acost_applicable = selectedAdult.data('applicable'));
          bookingSummary.push({
            type: 'person',
            name: field_labels.adults,
            value: `${adults_count} - ${formatCurrency(
              adults_cost
            )} - ${acost_applicable}`,
          });
        } else {
          var adults = $("input[name='additional_adults_info']:checked")
            .map(function() {
              var adult = {
                adults_cost: $(this).data('person_cost'),
                adults_count: $(this).data('person_count'),
                acost_applicable: $(this).data('applicable'),
              };
              return adult;
            })
            .get();

          if (adults.length > 0) {
            adults_cost = adults[0].adults_cost;
            adults_count = adults[0].adults_count;
            acost_applicable = adults[0].acost_applicable;
          }
          if (adults.length > 0) {
            bookingSummary.push({
              type: 'person',
              name: field_labels.adults,
              value: `${adults_count} - ${formatCurrency(
                adults_cost
              )} - ${acost_applicable}`,
            });
          }
        }

        /**
         * Handling Childs
         *
         * @since 3.0.9
         * @return null
         */
        var childs_cost,
          childs_hourly_cost,
          childs_count,
          ccost_applicable = '';

        if (conditional_data.booking_layout === 'layout_one') {
          var selectedChild = $('.additional_childs_info').find(':selected');
          (childs_cost = selectedChild.data('person_cost')),
            (childs_hourly_cost = selectedChild.data('person_hourly_cost')),
            (childs_count = selectedChild.data('person_count')),
            (ccost_applicable = selectedChild.data('applicable'));

          bookingSummary.push({
            type: 'person',
            name: field_labels.childs,
            value: `${childs_count} - ${formatCurrency(
              childs_count
            )} - ${ccost_applicable}`,
          });
        } else {
          var childs = $("input[name='additional_childs_info']:checked")
            .map(function() {
              var child = {
                childs_cost: $(this).data('person_cost'),
                childs_count: $(this).data('person_count'),
                ccost_applicable: $(this).data('applicable'),
              };
              return child;
            })
            .get();

          if (childs.length > 0) {
            childs_cost = childs[0].childs_cost;
            childs_count = childs[0].childs_count;
            ccost_applicable = childs[0].ccost_applicable;
          }
          if (childs.length > 0) {
            bookingSummary.push({
              type: 'person',
              name: field_labels.childs,
              value: `${childs_count} - ${formatCurrency(
                childs_count
              )} - ${ccost_applicable}`,
            });
          }
        }

        /**
         * Handling location cost
         *
         * @since 1.0.0
         * @return null
         */
        var pickup_cost = $('.pickup_location')
            .find(':selected')
            .data('pickup-location-cost'),
          dropoff_cost = $('.dropoff_location')
            .find(':selected')
            .data('dropoff-location-cost');

        var locationSummary = $('.rnb-distance').val(),
          splitLocation = locationSummary ? locationSummary.split('|') : '',
          totalKilometer = splitLocation[0] ? splitLocation[0] : 0;

        var pickupLoction = $('#rnb-origin-autocomplete').val(),
          returnLocation = $('#rnb-destination-autocomplete').val();

        bookingSummary.push(
          {
            type: 'location',
            name: field_labels.pickup_location,
            value: pickupLoction,
          },
          {
            type: 'location',
            name: field_labels.return_location,
            value: returnLocation,
          }
        );

        /**
         * Handling security_deposites
         *
         * @since 1.0.0
         * @return null
         */
        var security_deposites_pricing_plan = {};
        security_deposites_pricing_plan.security_deposites = $(
          "input[name='security_deposites[]']:checked"
        )
          .map(function() {
            var security_deposites = {
              name: $(this).data('name'),
              cost: $(this).data('value'),
              hourly_cost: $(this).data('hourly-rate'),
              applicable: $(this).data('applicable'),
            };

            bookingSummary.push({
              type: 'deposit',
              name: security_deposites.name,
              value: ` - ${formatCurrency(security_deposites.cost)}- ${
                security_deposites.applicable
              }`,
            });

            return security_deposites;
          })
          .get();

        // window.Rnb_error_check = Rnb_error_check_func(error, translated_strings);

        /**
         * Check Available Quantity
         *
         * @since 5.5.7
         * @return number
         */
        function rnb_check_available_quantity(days, pickupDate, dropoffDate) {
          var bookingDates = [],
            quantityAra = [],
            quantity = 0;

          for (var i = 0; i < days; i++) {
            if (i === 0) {
              bookingDates.push(Date.parse(pickupDate).toString('yyyy-MM-dd'));
            } else {
              bookingDates.push(
                Date.parse(pickupDate)
                  .add(i)
                  .day()
                  .toString('yyyy-MM-dd')
              );
            }
          }

          bookingDates.forEach(function(date) {
            var count_qty = BOOKING_DATA.quantity;
            for (var key in availability_data) {
              if (availability_data.hasOwnProperty(key)) {
                var booked_dates = availability_data[key]['only_block_dates'];
                if (booked_dates.includes(date)) {
                  console.log('found');
                  count_qty--;
                } else {
                  // count_qty++;
                }
              }
            }
            quantityAra.push(parseInt(count_qty));
          });

          quantity = Math.min.apply(Math, quantityAra);
          return quantity;
        }

        /**
         * Calculate price discount
         *
         * @since 1.0.0
         * @return number
         */
        function calculate_price_discount(cost, price_discount) {
          var flag = 0,
            discount_amount,
            discount_type;

          $.each(price_discount, function(index, value) {
            if (flag == 0) {
              if (
                parseInt(value.min_days) <= parseInt(days) &&
                parseInt(value.max_days) >= parseInt(days)
              ) {
                discount_type = value.discount_type;
                discount_amount = value.discount_amount;
                flag = 1;
              }
            }
          });

          if (discount_type && discount_amount) {
            $('p.discount-rate').show();
            if (discount_type === 'percentage') {
              cost = cost - (cost * discount_amount) / 100;
              $('p.discount-rate span').html(discount_amount + '%');
            } else {
              cost = cost - discount_amount;
              var currency = $('.currency-symbol').val();
              $('p.discount-rate span').html(
                accounting.formatMoney(discount_amount, currency)
              );
            }
          } else {
            $('p.discount-rate').hide();
          }
          return cost;
        }

        /**
         * Calculate resources and person cost
         *
         * @since 1.0.0
         * @return number
         */
        function calculate_third_party_cost(totalDays, cost) {
          if (pickup_cost != null && pickup_cost != undefined && pickup_cost) {
            cost = parseFloat(cost) + parseFloat(pickup_cost);
          }

          if (
            dropoff_cost != null &&
            dropoff_cost != undefined &&
            dropoff_cost
          ) {
            cost = parseFloat(cost) + parseFloat(dropoff_cost);
          }

          if (totalKilometer) {
            var perKiloCost = pricing_data.perkilo_price
              ? parseFloat(pricing_data.perkilo_price)
              : 0;
            cost = parseFloat(cost) + parseFloat(totalKilometer * perKiloCost);
          }

          if (categories_pricing.cat.length != 0) {
            $.each(categories_pricing.cat, function(index, value) {
              if (value.applicable == 'per_day') {
                value.cost = value.cost ? value.cost * value.quantity : 0;
                cost =
                  parseFloat(cost) +
                  parseInt(totalDays) * parseFloat(value.cost);
              } else {
                value.cost = value.cost ? value.cost * value.quantity : 0;
                cost = parseFloat(cost) + parseFloat(value.cost);
              }
            });
          }

          if (extras_pricing_plan.extras.length != 0) {
            $.each(extras_pricing_plan.extras, function(index, value) {
              if (value.applicable == 'per_day') {
                value.cost = value.cost ? value.cost : 0;
                cost =
                  parseFloat(cost) +
                  parseInt(totalDays) * parseFloat(value.cost);
              } else {
                value.cost = value.cost ? value.cost : 0;
                cost = parseFloat(cost) + parseFloat(value.cost);
              }
            });
          }

          if (adults_cost != null && adults_cost != undefined && adults_cost) {
            if (acost_applicable == 'per_day') {
              cost =
                parseFloat(cost) +
                parseInt(totalDays) * parseFloat(adults_cost);
            } else {
              cost = parseFloat(cost) + parseFloat(adults_cost);
            }
          }

          if (childs_cost != null && childs_cost != undefined && childs_cost) {
            if (ccost_applicable == 'per_day') {
              cost =
                parseFloat(cost) +
                parseInt(totalDays) * parseFloat(childs_cost);
            } else {
              cost = parseFloat(cost) + parseFloat(childs_cost);
            }
          }

          if (security_deposites_pricing_plan.security_deposites.length != 0) {
            $.each(security_deposites_pricing_plan.security_deposites, function(
              index,
              value
            ) {
              if (value.applicable == 'per_day') {
                value.cost = value.cost ? value.cost : 0;
                cost =
                  parseFloat(cost) +
                  parseInt(totalDays) * parseFloat(value.cost);
              } else {
                value.cost = value.cost ? value.cost : 0;
                cost = parseFloat(cost) + parseFloat(value.cost);
              }
            });
          }

          return cost;
        }

        /**
         * Calculate hourly resources and person cost
         *
         * @since 1.0.0
         * @return number
         */
        function calculate_hourly_third_party_cost(
          totalHours,
          cost,
          oneTimeItem
        ) {
          if (pickup_cost && oneTimeItem) {
            cost = parseFloat(cost) + parseFloat(pickup_cost);
          }

          if (dropoff_cost && oneTimeItem) {
            cost = parseFloat(cost) + parseFloat(dropoff_cost);
          }

          if (totalKilometer && oneTimeItem) {
            var perKiloCost = pricing_data.perkilo_price
              ? parseFloat(pricing_data.perkilo_price)
              : 0;
            cost = parseFloat(cost) + parseFloat(totalKilometer * perKiloCost);
          }

          if (categories_pricing.cat.length != 0) {
            $.each(categories_pricing.cat, function(index, value) {
              if (value.applicable == 'per_day') {
                value.hourly_cost = value.hourly_cost
                  ? value.hourly_cost * value.quantity
                  : 0;
                cost =
                  parseFloat(cost) +
                  parseInt(totalHours) * parseFloat(value.hourly_cost);
              } else {
                value.hourly_cost = value.hourly_cost
                  ? value.hourly_cost * value.quantity
                  : 0;
                cost = parseFloat(cost) + parseFloat(value.hourly_cost);
              }
            });
          }

          if (extras_pricing_plan.extras.length != 0) {
            $.each(extras_pricing_plan.extras, function(index, value) {
              if (value.applicable == 'per_day') {
                cost =
                  parseFloat(cost) +
                  parseInt(totalHours) * parseFloat(value.hourly_cost);
              } else if (oneTimeItem === true) {
                cost = parseFloat(cost) + parseFloat(value.cost);
              }
            });
          }

          if (adults_hourly_cost || adults_cost) {
            cost =
              acost_applicable == 'per_day'
                ? parseFloat(cost) +
                  parseInt(totalHours) * parseFloat(adults_hourly_cost)
                : oneTimeItem === true
                ? parseFloat(cost) + parseFloat(adults_cost)
                : parseFloat(cost);
          }

          if (childs_hourly_cost || childs_cost) {
            cost =
              ccost_applicable == 'per_day'
                ? parseFloat(cost) +
                  parseInt(totalHours) * parseFloat(childs_hourly_cost)
                : oneTimeItem === true
                ? parseFloat(cost) + parseFloat(childs_cost)
                : parseFloat(cost);
          }

          if (security_deposites_pricing_plan.security_deposites.length != 0) {
            $.each(security_deposites_pricing_plan.security_deposites, function(
              index,
              value
            ) {
              if (value.applicable == 'per_day') {
                cost =
                  parseFloat(cost) +
                  parseInt(totalHours) * parseFloat(value.hourly_cost);
              } else if (oneTimeItem === true) {
                cost = parseFloat(cost) + parseFloat(value.cost);
              }
            });
          }

          return cost;
        }

        /**
         * Calculate hourly pricing
         *
         * @since 1.0.0
         * @return number
         */
        function calculate_hourly_price(total_hours, pricing_data) {
          var cost = 0;

          if (pricing_data.hourly_pricing_type === 'hourly_general') {
            cost =
              parseInt(total_hours) * parseFloat(pricing_data.hourly_general);
          }

          if (pricing_data.hourly_pricing_type === 'hourly_range') {
            var hourly_ranges = pricing_data.hourly_range,
              flag = 0,
              max_hours_check = new Array();

            $.each(hourly_ranges, function(index, value) {
              max_hours_check.push(parseInt(value.max_hours));
            });

            if (total_hours > Math.max.apply(Math, max_hours_check)) {
              $('.single_add_to_cart_button').attr('disabled', 'disabled');
              sweetAlert(
                translated_strings.opps,
                translated_strings.max_booking_hours_exceed,
                'error'
              );
            } else {
              $('.single_add_to_cart_button').removeAttr(
                'disabled',
                'disabled'
              );
            }

            $.each(hourly_ranges, function(index, value) {
              if (flag == 0) {
                if (value.cost_applicable === 'per_hour') {
                  if (
                    parseInt(value.min_hours) <= parseInt(total_hours) &&
                    parseInt(value.max_hours) >= parseInt(total_hours)
                  ) {
                    cost = parseFloat(value.range_cost) * parseInt(total_hours);
                    flag = 1;
                  }
                } else {
                  if (
                    parseInt(value.min_hours) <= parseInt(total_hours) &&
                    parseInt(value.max_hours) >= parseInt(total_hours)
                  ) {
                    cost = parseFloat(value.range_cost);
                    flag = 1;
                  }
                }
              }
            });
          }
          return cost;
        }

        /**
         * Format Currency
         *
         * @since 5.0.9
         * @return null
         */
        function formatCurrency(price) {
          var woocommerce_config = BOOKING_DATA.woocommerce_info,
            currencyFormat;

          switch (woocommerce_config.position) {
            case 'right':
              currencyFormat = '%v%s';
              break;
            case 'right_space':
              currencyFormat = '%v %s';
              break;
            case 'left_space':
              currencyFormat = '%s %v';
              break;
            default:
              currencyFormat = '%s%v';
              break;
          }

          var currencyOptions = {
            symbol: woocommerce_config.symbol,
            decimal: woocommerce_config.decimal,
            thousand: woocommerce_config.thousand,
            precision: woocommerce_config.number,
            format: currencyFormat,
          };

          return accounting.formatMoney(price, currencyOptions);
        }

        /**
         * Calculate general pricing
         *
         * @since 1.0.0
         * @return null
         */
        if (pricing_data.pricing_type === 'general_pricing') {
          var cost = 0,
            dayCost = 0,
            hourCost = 0,
            rentalDays,
            extraHoursPayment = conditional_data.pay_extra_hours,
            generalPrice = pricing_data.general_pricing,
            priceDiscount = pricing_data.price_discount;

          if (days > 0) {
            rentalDays =
              extraHoursPayment === 'yes' && extra_hours > 0 ? days - 1 : days;

            dayCost = parseInt(rentalDays) * parseFloat(generalPrice);
            dayCost = calculate_price_discount(dayCost, priceDiscount);
            dayCost = calculate_third_party_cost(rentalDays, dayCost);

            if (extraHoursPayment === 'yes' && extra_hours > 0) {
              hourCost = calculate_hourly_price(extra_hours, pricing_data);
              hourCost = calculate_hourly_third_party_cost(
                extra_hours,
                hourCost,
                false
              );
            }
            cost = dayCost + hourCost;
          } else {
            cost = calculate_hourly_price(total_hours, pricing_data);
            cost = calculate_hourly_third_party_cost(total_hours, cost, true);
          }
        }

        /**
         * Calculate day ranges pricing
         *
         * @since 1.0.0
         * @return null
         */
        if (pricing_data.pricing_type === 'days_range') {
          var flag = 0,
            cost = 0,
            dayCost = 0,
            hourCost = 0,
            rentalDays,
            extraHoursPayment = conditional_data.pay_extra_hours,
            max_days_check = new Array(),
            days_range = pricing_data.days_range,
            priceDiscount = pricing_data.price_discount;

          if (days > 0) {
            rentalDays =
              extraHoursPayment === 'yes' && extra_hours > 0 ? days - 1 : days;

            $.each(days_range, function(index, value) {
              max_days_check.push(parseInt(value.max_days));
            });

            if (rentalDays > Math.max.apply(Math, max_days_check)) {
              $('.single_add_to_cart_button').attr('disabled', 'disabled');
              sweetAlert(
                translated_strings.opps,
                translated_strings.max_booking_days_exceed,
                'error'
              );
            } else {
              $('.single_add_to_cart_button').removeAttr(
                'disabled',
                'disabled'
              );
            }

            $.each(days_range, function(index, value) {
              if (flag == 0) {
                if (value.cost_applicable === 'per_day') {
                  if (
                    parseInt(value.min_days) <= parseInt(rentalDays) &&
                    parseInt(value.max_days) >= parseInt(rentalDays)
                  ) {
                    dayCost =
                      parseFloat(value.range_cost) * parseInt(rentalDays);
                    flag = 1;
                  }
                } else {
                  if (
                    parseInt(value.min_days) <= parseInt(rentalDays) &&
                    parseInt(value.max_days) >= parseInt(rentalDays)
                  ) {
                    dayCost = parseFloat(value.range_cost);
                    flag = 1;
                  }
                }
              }
            });

            dayCost = calculate_price_discount(dayCost, priceDiscount);
            dayCost = calculate_third_party_cost(rentalDays, dayCost);

            if (extraHoursPayment === 'yes' && extra_hours > 0) {
              hourCost = calculate_hourly_price(extra_hours, pricing_data);
              hourCost = calculate_hourly_third_party_cost(
                extra_hours,
                hourCost,
                false
              );
            }
            cost = dayCost + hourCost;
          } else {
            cost = calculate_hourly_price(total_hours, pricing_data);
            cost = calculate_hourly_third_party_cost(total_hours, cost, true);
          }
        }

        /**
         * Calculate daily pricing
         *
         * @since 1.0.0
         * @return null
         */
        if (pricing_data.pricing_type === 'daily_pricing') {
          var extraHoursPayment = conditional_data.pay_extra_hours,
            priceDiscount = pricing_data.price_discount,
            daily_pricing_plan = pricing_data.daily_pricing,
            dayCost = 0,
            hourCost = 0,
            cost = 0,
            week = [
              'sunday',
              'monday',
              'tuesday',
              'wednesday',
              'thursday',
              'friday',
              'saturday',
            ];

          if (days > 0) {
            rentalDays =
              extraHoursPayment === 'yes' && extra_hours > 0 ? days - 1 : days;

            for (var i = 0; i < parseInt(rentalDays); i++) {
              var day =
                i === 0
                  ? Date.parse(pickupDate).getDay()
                  : Date.parse(pickupDate)
                      .add(i)
                      .day()
                      .getDay();

              dayCost =
                daily_pricing_plan[week[day]] != ''
                  ? dayCost + parseFloat(daily_pricing_plan[week[day]])
                  : dayCost + 0;
            }

            dayCost = calculate_price_discount(dayCost, priceDiscount);
            dayCost = calculate_third_party_cost(rentalDays, dayCost);

            if (extraHoursPayment === 'yes' && extra_hours > 0) {
              hourCost = calculate_hourly_price(extra_hours, pricing_data);
              hourCost = calculate_hourly_third_party_cost(
                extra_hours,
                hourCost,
                false
              );
            }
            cost = dayCost + hourCost;
          } else {
            cost = calculate_hourly_price(total_hours, pricing_data);
            cost = calculate_hourly_third_party_cost(total_hours, cost, true);
          }
        }

        /**
         * Calculate monthly pricing
         *
         * @since 1.0.0
         * @return null
         */
        if (pricing_data.pricing_type === 'monthly_pricing') {
          var extraHoursPayment = conditional_data.pay_extra_hours,
            monthly_pricing_plan = pricing_data.monthly_pricing,
            price_discount = pricing_data.price_discount,
            rentalDays,
            dayCost = 0,
            hourCost = 0,
            cost = 0,
            months = [
              'january',
              'february',
              'march',
              'april',
              'may',
              'june',
              'july',
              'august',
              'september',
              'october',
              'november',
              'december',
            ];

          if (days > 0) {
            rentalDays =
              extraHoursPayment === 'yes' && extra_hours > 0 ? days - 1 : days;
            for (var i = 0; i < parseInt(rentalDays); i++) {
              var month =
                i === 0
                  ? Date.parse(pickupDate).getMonth()
                  : Date.parse(pickupDate)
                      .add(i)
                      .day()
                      .getMonth();

              dayCost =
                monthly_pricing_plan[months[month]] != ''
                  ? dayCost + parseFloat(monthly_pricing_plan[months[month]])
                  : dayCost + 0;
            }

            dayCost = calculate_price_discount(dayCost, price_discount);
            dayCost = calculate_third_party_cost(rentalDays, dayCost);

            if (extraHoursPayment === 'yes' && extra_hours > 0) {
              hourCost = calculate_hourly_price(extra_hours, pricing_data);
              hourCost = calculate_hourly_third_party_cost(
                extra_hours,
                hourCost,
                false
              );
            }
            cost = dayCost + hourCost;
          } else {
            cost = calculate_hourly_price(total_hours, pricing_data);
            cost = calculate_hourly_third_party_cost(total_hours, cost, true);
          }
        }

        /**
         * Calculate Flat Hour
         *
         * @since 1.0.0
         * @return null
         */
        if (pricing_data.pricing_type === 'flat_hours') {
          var cost = 0;
          cost = calculate_hourly_price(total_hours, pricing_data);
          cost = calculate_hourly_third_party_cost(total_hours, cost, true);
        }

        var summaryLayout = '<ul class="booking-info">';
        bookingSummary.forEach(function(item) {
          summaryLayout += `<li class="${item.type}"> <span class="name"> ${
            item.name
          } </span> <span class="value"> ${item.value} </span> </li>`;
        });

        summaryLayout += '</ul>';

        $('.booking-summay').html(summaryLayout);

        $('.quote_price').val(cost * selected_qty);
        $('h3.booking_cost span').html(formatCurrency(cost * selected_qty));

        //For new design
        $('.total-rental-price h2').html(formatCurrency(cost * selected_qty));
        //End for new design
      } else {
      }
    });
  });
}

// window.costHandle = rnbCostHandle();

rnbCostHandle();

/**
 * Report errors in sweatalart view
 *
 * @since 5.5.7
 * @return number
 */
function Rnb_error_check_func(error, translated_strings) {
  console.log(error, 'error');

  if (error.length > 0) {
    for (var i = 0; i < error.length; i++) {
      sweetAlert(translated_strings.opps, error[i], 'error');
    }
    jQuery('.date-error-message').show();
    jQuery('.redq_add_to_cart_button').attr('disabled', 'disabled');
    jQuery('.redq_request_for_a_quote').attr('disabled', 'disabled');
    jQuery('#rnbSmartwizard .actions ul li')
      .addClass('disabled disabledNextOnModal')
      .attr('aria-disabled', 'true');
  } else {
    jQuery('.date-error-message').hide();
    jQuery('.redq_add_to_cart_button').removeAttr('disabled', 'disabled');
    jQuery('.redq_request_for_a_quote').removeAttr('disabled', 'disabled');
    jQuery('#rnbSmartwizard .actions ul li')
      .removeClass('disabled disabledNextOnModal')
      .addClass('proceedOnModal')
      .attr('aria-disabled', 'false');
  }
  return error;
}
