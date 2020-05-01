jQuery(document).ready(function ($) {
  const bookNowButtonSelector = $('.redq_add_to_cart_button');
  const rfqButtonSelector = $('.redq_request_for_a_quote');

  const validation_data = CALENDAR_DATA.calendar_props.settings.validations;
  const translatedStrings = CALENDAR_DATA.translated_strings;

  const bodySelector = $('body');

  bodySelector.on('change', '.rnb-pickup-location', function () {
    if ($(this).val()) {
      $(this).css('border', '1px solid #bbb');
    }
  });

  bodySelector.on('change', '.rnb-dropoff-location', function () {
    if ($(this).val()) {
      $(this).css('border', '1px solid #bbb');
    }
  });

  bodySelector.on(
    'change',
    '.rnb-control-checkbox input[type="checkbox"]',
    function () {
      $('label[for=rnb-resource-' + $(this).data('items') + ']').toggleClass(
        'checked'
      );
    }
  );

  bodySelector.on(
    'change',
    '.rnb-control-checkbox-deposit input[type="checkbox"]',
    function () {
      $('label[for=rnb-deposit-' + $(this).data('items') + ']').toggleClass(
        'checked'
      );
    }
  );

  // Radio Class Toggle
  bodySelector.on(
    'click',
    '.rnb-control-radio.rnb-adult-label input[type="radio"]',
    function () {
      $('label.rnb-control-radio.rnb-adult-label').removeClass('checked');
      $('label[for=rnb-adult-' + $(this).data('items') + ']').addClass(
        'checked'
      );
    }
  );

  bodySelector.on(
    'click',
    '.rnb-control-radio.rnb-child-label input[type="radio"]',
    function () {
      $('label.rnb-control-radio.rnb-child-label').removeClass('checked');
      $('label[for=rnb-child-' + $(this).data('items') + ']').addClass(
        'checked'
      );
    }
  );

  bodySelector.on('change', '.rnb-adult-area', function () {
    const isAdultSelected = $(this).find('label.checked');
    if (isAdultSelected.length) {
      $('span.adultWarning').hide();
    } else {
      $('span.adultWarning').show();
    }
  });

  bodySelector.on('change', '.rnb-child-area', function () {
    const isChildSelected = $(this).find('label.checked');
    if (isChildSelected.length) {
      $('span.childWarning').hide();
    } else {
      $('span.childWarning').show();
    }
  });

  /**
   * Configuration for select fields validation checking
   *
   * @since 3.0.4
   * @return null
   */
  $('.pickup_location').on('change', function () {
    const val = $(this).val();
    if (val) {
      $('.pickup_location')
        .next('.chosen-container')
        .css('border', '1px solid #bbb');
    }
  });

  $('.dropoff_location').on('change', function () {
    const val = $(this).val();
    if (val) {
      $('.dropoff_location')
        .next('.chosen-container')
        .css('border', '1px solid #bbb');
    }
  });

  $('.additional_adults_info').on('change', function () {
    const val = $(this).val();
    if (val) {
      $('.additional_adults_info')
        .next('.chosen-container')
        .css('border', '1px solid #bbb');
    }
  });

  $('#pickup-time').on('change', function () {
    const val = $(this).val();
    if (val) {
      $('#pickup-time').css('border', '1px solid #bbb');
    }
  });

  $('#dropoff-time').on('change', function () {
    const val = $(this).val();
    if (val) {
      $('#dropoff-time').css('border', '1px solid #bbb');
    }
  });

  bookNowButtonSelector.on('click', function (e) {
    let flag = false,
      validate_messages = [];

    if (validation_data.pickup_location === 'open') {
      const plocation = $('.pickup_location').val();
      if (!plocation && typeof plocation != 'undefined') {
        $('.pickup_location')
          .next('.chosen-container')
          .css('border', '1px solid red');
        validate_messages.push(translatedStrings.pickup_loc_required);
        flag = true;
      }
    }
    if (validation_data.return_location === 'open') {
      const dlocation = $('.dropoff_location').val();
      if (!dlocation && typeof dlocation != 'undefined') {
        $('.dropoff_location')
          .next('.chosen-container')
          .css('border', '1px solid red');
        validate_messages.push(translatedStrings.dropoff_loc_required);
        flag = true;
      }
    }
    if (validation_data.person === 'open') {
      const person = $('.additional_adults_info').val();
      if (!person && typeof person != 'undefined') {
        $('.additional_adults_info')
          .next('.chosen-container')
          .css('border', '1px solid red');
        validate_messages.push(translatedStrings.adult_required);
        flag = true;
      }
    }

    const pickup_time = $('#pickup-time').val();
    if (!pickup_time && typeof pickup_time != 'undefined') {
      $('#pickup-time').css('border', '1px solid red');
      validate_messages.push(translatedStrings.pickup_time_required);
      flag = true;
    }

    const return_time = $('#dropoff-time').val();
    if (!return_time && typeof return_time != 'undefined') {
      $('#dropoff-time').css('border', '1px solid red');
      validate_messages.push(translatedStrings.dropoff_time_required);
      flag = true;
    }

    if (flag && validate_messages.length) {
      const preWrapper = '<ul class="validate-notice woocommerce-error">',
        postWrapper = '</ul>',
        notices = validate_messages.map((notice) => {
          return `<li>${notice}</li>`;
        }),
        validateMarkup = `${preWrapper} ${notices.join(' ')} ${postWrapper}`;

      $('.rnb-notice').html(validateMarkup);

      $('html, body').animate(
        {
          scrollTop: $('.rnb-notice').offset().top - 250,
        },
        'slow'
      );
    }

    if (flag === true) e.preventDefault();
  });

  /**
   * Configuration others plugins
   *
   * @since 1.0.0
   * @return null
   */

  $('.price-showing').hide();
  $('.rnb-pricing-plan-link').click(function (e) {
    e.preventDefault();
    $('.price-showing').slideToggle();
  });

  $('.close-animatedModal i').on('click', function () {
    $('#animatedModal').removeClass('zoomIn');
    $('body').removeClass('rnbOverflow');
  });

  $("input[name='cat_quantity']").change(function () {
    const self = $(this);
    const val = self.val();
    const cat_val = self
      .closest('.categories-attr')
      .find('.carrental_categories')
      .val()
      .split('|');
    cat_val[4] = val;
    self
      .closest('.categories-attr')
      .find('.carrental_categories')
      .val(cat_val.join('|'));
  });

  const $input = $(
    '<li class="book-now" style="display: none;"><button type="submit" class="single_add_to_cart_button redq_add_to_cart_button btn-book-now button alt">Book Now Modal</button></li>'
  );
  $input.appendTo($('ul[aria-label=Pagination]'));
});
