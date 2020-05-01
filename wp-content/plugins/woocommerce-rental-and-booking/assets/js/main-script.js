jQuery(document).ready(function ($) {
  const bookNowButtonSelector = $('.redq_add_to_cart_button');
  const rfqButtonSelector = $('.redq_request_for_a_quote');

  bookNowButtonSelector.attr('disabled', 'disabled');
  rfqButtonSelector.attr('disabled', 'disabled');

  const productSettings = CALENDAR_DATA.calendar_props.settings;
  const validation_data = CALENDAR_DATA.calendar_props.settings.validations;

  const ajaxUrl = CALENDAR_DATA.ajax_url;
  let layout_two_labels = productSettings.layout_two_labels;

  if (productSettings.conditions.booking_layout === 'layout_one') {
    handleNormalLayoutTemplates(CALENDAR_DATA);
  } else {
    handleModalLayoutTemplates(CALENDAR_DATA);
  }

  function handleNormalLayoutTemplates(response) {
    RNB_TEMPLATES.pickupLocation(response.pick_up_locations);
    RNB_TEMPLATES.dropoffLocation(response.return_locations);
    RNB_TEMPLATES.resource(response.resources);
    RNB_TEMPLATES.category(response.categories);
    RNB_TEMPLATES.adults(response.adults);
    RNB_TEMPLATES.childs(response.childs);
    RNB_TEMPLATES.deposit(response.deposits);
  }

  function handleModalLayoutTemplates(response) {
    RNB_TEMPLATES.resourceModal(response.resources);
    RNB_TEMPLATES.adultModal(response.adults);
    RNB_TEMPLATES.childModal(response.childs);
    RNB_TEMPLATES.depositModal(response.deposits);
  }

  /**
   *inventorySwitching
   *
   * Fire when inventory changes
   */
  function inventorySwitching() {
    $('#booking_inventory').change(function () {
      const inventoryID = $(this).val();
      const postID = $(this).data('post-id');

      RNB_HELPER.loadingEffect('form.rnb-cart');

      $.ajax({
        type: 'post',
        dataType: 'json',
        url: ajaxUrl,
        data: {
          action: 'rnb_get_inventory_data',
          inventory_id: inventoryID,
          post_id: postID,
        },
        success: function (response) {
          RNB_HELPER.resetFields();
          RNB_HELPER.rnbHandleErrorAction();

          BOOKING_DATA = response.booking_data;
          CALENDAR_DATA = response.calendar_data;

          RNB_HELPER.handleProductUnitPrice(BOOKING_DATA);

          RNB_CALENDER_ACTION.init(CALENDAR_DATA);
          rnbCostHandle();

          const bookingLayout =
            BOOKING_DATA.rnb_data.settings.conditions.booking_layout;

          if (bookingLayout !== 'layout_two') {
            handleNormalLayoutTemplates(response);
          }

          if (bookingLayout === 'layout_two') {
            handleModalLayoutTemplates(response);
          }

          RNB_HELPER.unLoadingEffect('form.rnb-cart');
        },
      });
    });
  }

  /**
   * ModalLayout
   *
   * @type {jQuery|undefined}
   */
  function initModal() {
    const wizard = $('#rnbSmartwizard').steps({
      stepsOrientation: 'vertical',
      headerTag: 'h3',
      bodyTag: 'section',
      transitionEffect: 'fade',
      enableFinishButton: true,
      autoFocus: true,
      onFinished: function (event, currentIndex) {
        event.preventDefault();
        RNB_HELPER.rnbHandleSuccessAction();
        $('li.book-now').show();
      },
      onStepChanging: function (event, currentIndex, newIndex) {
        const pickupLocWrapper = $('.rnb-pickup-location');
        const dropoffLocWrapper = $('.rnb-dropoff-location');
        const pickupLocInput = $('input.rnb-pickup-location');
        const dropoffLocInput = $('input.rnb-dropoff-location');

        const tabName = $('li.current')
          .children('a')
          .children('span.tab-identifier')
          .data('id');

        switch (tabName) {
          case 'location':
            if (newIndex < currentIndex) {
              return true;
            }

            const pickupLoc = pickupLocWrapper.val();
            const dropoffLoc = dropoffLocWrapper.val();
            const pickupLatLng = pickupLocWrapper.attr('data-latlng');
            const dropoffLatLng = dropoffLocWrapper.attr('data-latlng');

            if (pickupLoc === '' || pickupLatLng === 'notset') {
              pickupLocInput.css('border', '1px solid red');
              return false;
            } else {
              pickupLocInput.css('border', 'none');
            }

            if (dropoffLoc === '' || dropoffLatLng === 'notset') {
              dropoffLocInput.css('border', '1px solid red');
              return false;
            } else {
              dropoffLocInput.css('border', 'none');
            }

            pickupLocInput.css('border', '1px solid #eee');
            dropoffLocInput.css('border', '1px solid #eee');

            return true;

          case 'person':
            if (newIndex < currentIndex) {
              return true;
            }

            if (validation_data.person !== 'open') {
              return true;
            }

            const isAdultSelected = $('.rnb-adult-area').find('label.checked');
            const isChildSelected = $('.rnb-child-area').find('label.checked');

            if (!isAdultSelected.length) {
              $('span.adultWarning').show();
              return false;
            } else {
              $('span.adultWarning').hide();
            }

            if (!isChildSelected.length) {
              $('span.childWarning').show();
              return false;
            } else {
              $('span.childWarning').hide();
            }

            return true;
          default:
            return true;
        }
      },

      onStepChanged: function (event, currentIndex, priorIndex) {
        const wrapperH3 = $('.title-wrapper h3');
        const wrapperP = $('.title-wrapper p');
        const wrapperBookNow = $('li.book-now');
        const nextBtnClass = $('#rnbSmartwizard .actions ul li:nth-child(2)');

        const tabName = $('li.current')
          .children('a')
          .children('span.tab-identifier')
          .data('id');

        switch (tabName) {
          case 'inventory':
            wrapperH3.html(layout_two_labels.inventory.inventory_top_heading);
            wrapperP.html(layout_two_labels.inventory.inventory_top_desc);
            nextBtnClass.removeClass('disabledNextOnModal');
            wrapperBookNow.hide();
            return true;
          case 'location':
            wrapperH3.html(layout_two_labels.location.location_top_heading);
            wrapperP.html(layout_two_labels.location.location_top_desc);
            nextBtnClass.removeClass('disabledNextOnModal');
            wrapperBookNow.hide();
            return true;
          case 'duration':
            wrapperH3.html(layout_two_labels.datetime.date_top_heading);
            wrapperP.html(layout_two_labels.datetime.date_top_desc);
            nextBtnClass
              .addClass('disabled disabledNextOnModal')
              .attr('aria-disabled', 'true');
            $('.date-error-message').hide();

            wrapperBookNow.hide();
            return true;
          case 'resource':
            wrapperH3.html(layout_two_labels.resource.resource_top_heading);
            wrapperP.html(layout_two_labels.resource.resource_top_desc);
            wrapperBookNow.hide();
            return true;

          case 'person':
            wrapperH3.html(layout_two_labels.person.person_top_heading);
            wrapperP.html(layout_two_labels.person.person_top_desc);
            wrapperBookNow.hide();
            return true;
          case 'deposit':
            wrapperH3.html(layout_two_labels.deposit.deposit_top_heading);
            wrapperP.html(layout_two_labels.deposit.deposit_top_desc);
            wrapperBookNow.hide();
            return true;
          case 'summary':
            wrapperH3.html('Summary');
            wrapperP.html('Summary Desc');
            return true;
          default:
            return true;
        }
      },
      onInit: function (event, currentIndex) {
        RNB_CALENDER_ACTION.init(CALENDAR_DATA);

        const tabName = $('li.current')
          .children('a')
          .children('span.tab-identifier')
          .data('id');

        if (tabName === 'inventory') {
          const wrapperH3 = $('.title-wrapper h3');
          const wrapperP = $('.title-wrapper p');
          wrapperH3.html(layout_two_labels.inventory.inventory_top_heading);
          wrapperP.html(layout_two_labels.inventory.inventory_top_desc);
        }

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
  }

  function rnbCostHandle() {
    $('.show_if_time').hide();
    $('.redq-quantity').hide();

    $('body').on('click', "input[name='cat_quantity[]']", function () {
      console.log('clicked on cat qty');
      const qty = $(this).val();
      const cat_id = $(this).data('cat_id');
      $(this)
        .parents('.categories-attr')
        .children('.custom-block')
        .children('input')
        .val(`${cat_id}|${qty}`);
      $('form.cart').trigger('change');
    });

    $('form.cart').on('change', function (event) {
      $('.redq_add_to_cart_button').attr('disabled', 'disabled');
      rfqButtonSelector.attr('disabled', 'disabled');

      const self = $(this);
      const dataObj = RNB_HELPER.rnbProcessFormData(self);
      const fire_ajax = RNB_HELPER.shouldFireAjax(dataObj);

      if (fire_ajax) {
        RNB_HELPER.loadingEffect('.rnb-loader');
        $.ajax({
          type: 'post',
          dataType: 'json',
          url: ajaxUrl,
          data: {
            action: 'rnb_calculate_inventory_data',
            form: dataObj,
          },
          success: function (response) {
            RNB_HELPER.rnbHandleError(response);
            RNB_HELPER.unLoadingEffect('.rnb-loader');

            if (parseInt(response.available_quantity) > 0) {
              $('input.inventory-qty').attr({
                max: response.available_quantity,
                min: 1,
              });

              if ($('.redq-quantity').length > 0) {
                $('.redq-quantity').show();
              }

              const priceBreakdown = response.price_breakdown;
              let priceBreakdownMarkupContent = '<ul>';

              const quote_total =
                priceBreakdown &&
                priceBreakdown['quote_total'] &&
                priceBreakdown['quote_total'].cost
                  ? priceBreakdown['quote_total'].cost
                  : '0';
              $('.quote_price').val(quote_total);

              for (const key in priceBreakdown) {
                if (priceBreakdown.hasOwnProperty(key)) {
                  if (key === 'quote_total') continue;
                  priceBreakdownMarkupContent += `<li class="${key}"><span class="name">${priceBreakdown[key]['text']}</span> <span class="price">${priceBreakdown[key]['cost']}</span></li>`;
                }
              }

              priceBreakdownMarkupContent += '</ul>';

              $('.booking_cost').html(priceBreakdownMarkupContent);
              $('.booking-pricing-info').fadeIn();
              //End

              $('body select.person-select').chosen('destroy');
              // $('body select.person-select').val('');

              if (response.date_multiply === 'per_hour') {
                $('body .show_if_day').children('span').hide();
                $('body .show_if_time').show();

                $('body select.person-select > option').each(function () {
                  if ($(this).data('unit') === 'per_day') {
                    $(this).hide();
                  } else {
                    $(this).show();
                  }
                });
              } else {
                $('body .show_if_time').hide();
                $('body .show_if_day').children('span').show();

                $('body select.person-select > option').each(function () {
                  if ($(this).data('unit') === 'per_hour') {
                    $(this).hide();
                  } else {
                    $(this).show();
                  }
                });
              }

              $('body select.person-select').chosen().trigger('chosen:updated');

              $('.inventory-qty').focus();
              $('.inventory-qty-next').focus();
            }
          },
        });
      } else {
        $('.redq-quantity').hide();
      }
    });
  }

  // RnB modal
  $('#showBooking').on('click', function () {
    $('#animatedModal').toggleClass('zoomIn');
    $('body').addClass('rnbOverflow');
    inventorySwitching();
  });

  /**
   * Initialize calendar
   *
   * @since 1.0.0
   * @version 5.9.0
   * @return null
   */
  RNB_CALENDER_ACTION.init(CALENDAR_DATA);
  inventorySwitching();
  rnbCostHandle();
  initModal();
});
