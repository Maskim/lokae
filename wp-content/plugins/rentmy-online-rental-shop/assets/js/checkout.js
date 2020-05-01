jQuery(function ($) {
    var elements ;
    var card ;
    var stripe;
    var rm_checkout = {
        country: 'US',
        fulfillment_type: 'instore',
        shipping_response: '',
        delivery_response: '',
        payment_gateway_name: '',
        payment_gateway_id: '',
        stripe_key: '',
        payment_gateway_type: 1,
        payment_amount: 0,
        amount_tendered: 0,
        canvasSignature: null,
        signaturePad: null,
        init: function () {
            if ($('#signature-pad').length) {
                this.init_signature_pad();
            }
            if ($('#rentmy-btn-checkout-billing').length) {
                this.init_algolia_places('');
            }
            if ($('#rentmy-btn-checkout-fulfillment').length) {
                this.init_fulfillment();
                this.init_algolia_places('sh');
            }
            if ($('#rentmy-btn-checkout-payment').length) {
                this.init_payment();
            }

            if ($('#rm-btn-delivery-cost').length) {
                this.free_shipping();
            }

            //initial triggers will go here
            jQuery('#checkout-fulfillment .fullfilment-type ul li').trigger('click');
        },
        init_signature_pad: function () {
            this.canvasSignature = document.querySelector('.signature-pad');
            if (this.canvasSignature) {
                this.signaturePad = new SignaturePad(this.canvasSignature, {
                    // 'canvasWidth': 500,
                    // 'canvasHeight': 200,
                    'penColor': 'black',
                    'backgroundColor': 'white'
                    //backgroundColor: 'rgb(255, 255, 255)' // necessary for saving image as JPEG; can be removed is only saving as PNG or SVG
                });
            }
        },
        init_fulfillment: function () {
            // instore pick is active
            if ($(".fullfilment-type li[data-id='instore']").length) {
                $(".fullfilment-type li[data-id='instore']").addClass('active');
                $(".rm-instore-container").show();
                $(".rm-delivery-container").hide();
                $("#rm-btn-delivery-cost").hide();
                $("#rm-btn-shipping-cost").hide();
                this.fulfillment_type = 'instore';
            } else if ($(".fullfilment-type li[data-id='delivery']").length) {
                $(".fullfilment-type li[data-id='delivery']").addClass('active');
                $(".rm-instore-container").hide();
                $(".rm-delivery-container").show();
                $("#rm-btn-delivery-cost").show();
                $("#rm-btn-shipping-cost").hide();
                $("#rentmy-btn-checkout-fulfillment").hide();
                this.fulfillment_type = 'delivery';

            } else if ($(".fullfilment-type li[data-id='shipping']").length) {
                $(".fullfilment-type li[data-id='shipping']").addClass('active');
                $(".rm-instore-container").hide();
                $(".rm-delivery-container").show();
                $("#rm-btn-delivery-cost").hide();
                $("#rm-btn-shipping-cost").show();
                $("#rentmy-btn-checkout-fulfillment").hide();
                this.fulfillment_type = 'shipping';
            }

        },
        init_algolia_places: function (section) {

            if (section == '') {
                var algo_city = "#rm_" + section + 'city';
                var algo_state = "#rm_" + section + "state";
                var algo_zip = "#rm_" + section + "zipcode";
            } else {
                var algo_city = "#rm_" + section + '_city';
                var algo_state = "#rm_" + section + "_state";
                var algo_zip = "#rm_" + section + "_zipcode";
            }

            if (document.querySelector(algo_city) == null) {
                return;
            }

            var placesAutocomplete = places({
                appId: 'plIF9PULAHKJ',
                apiKey: '33382f2e6281756a1ceb6302fbc6bcbe',
                container: document.querySelector(algo_city),
                type: "city",
                countries: [this.country],
                templates: {
                    value: function (suggestion) {
                        return suggestion.name;
                    }

                }
            }).configure({
                countries: [this.country]
            });
            placesAutocomplete.on("change", function resultSelected(e) {
                $(algo_zip).val(e.suggestion.postcode || "");
                $(algo_state).val(e.suggestion.administrative || "");
            });
        },
        init_payment: function () {
            if ($(".payment-type li[data-id='credit-card']").length) {
                $(".payment-type li[data-id='credit-card']").addClass('active');
                $(".rm-card-payment-container").show();
                $(".rm-others-payment-container").hide();
                this.payment_gateway_id = $(".payment-type li[data-id='credit-card'] .rm_payment_gateway_id").val();
                this.payment_gateway_name = $(".payment-type li[data-id='credit-card'] .rm_payment_gateway_name").val();
                this.stripe_key = $(".payment-type li[data-id='credit-card'] .rm_stripe_key").val();
                this.payment_gateway_type = 1;

                // for partial payments these two lines
                this.payment_amount = $("#tamount").val();
                // alert(rm_checkout.stripe_key);
                if (rm_checkout.payment_gateway_name == 'Stripe') {
                    $('#card-element').show();
                    stripe = Stripe(rm_checkout.stripe_key);
                    elements = stripe.elements();
                    card = elements.create('card');
                    card.mount('#card-element');
                } else {
                    $('#other-card-element').show();
                }
                // ends
            } else if ($(".payment-type li[data-id='others']").length) {
                $(".payment-type li[data-id='delivery']").addClass('active');
                $(".rm-card-payment-container").hide();
                $(".rm-others-payment-container").show();
                this.payment_gateway_id = $(".payment-type li[data-id='others'] .rm_payment_gateway_id").val();
                this.payment_gateway_name = $(".payment-type li[data-id='others'] .rm_payment_gateway_name").val();
                this.payment_gateway_type = 2;
            }
        },
        free_shipping: function () {
            console.log('free shipping check');
            var data = {
                'action': 'rentmy_options',
                'action_type': 'free_shipping',
            };
            $('#rm-btn-shipping-cost').hide();
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                if (response.data == true) {
                    $('#rentmy-btn-checkout-fulfillment').show();
                    $('#rm-btn-shipping-cost').hide();
                    $('#shipping_method').val(5);
                }
            });
        }
    };

    $('body')
        .on('change', '#rm_sh_country', function () {
            rm_checkout.country = $(this).val()
        })
        .on('change', '#rm_country', function () {
            rm_checkout.country = $(this).val()
        })
        .on("click", "#rentmy-btn-checkout-billing", function (e) { // on submit billing data
            e.preventDefault();
            if (rm_checkout.signaturePad) {
                let signatureToData = rm_checkout.signaturePad.toDataURL('image/jpeg', 0.5);
                $('#signature').val(signatureToData);
            }

            let formId = $(this).attr('data-step');
            let redirectUrl = $(this).attr('data-succeredirect');
            let formInfo = $('#checkout-' + formId);
            let flagError = false;

            let data = {
                'action': 'rentmy_checkout_information',
                'data': $('#checkout-' + formId).serialize(),
                'step': formId
            };

            formInfo.find('input, select, textarea').each(function () {
                if (typeof $(this).attr("required") != 'undefined') {
                    $(this).next('small.error').remove();
                    if ($(this).val() == '') {
                        $('<small class="error">This field is required.</small>').insertAfter($(this));
                        flagError = true;
                    } else {
                        $(this).next('small.error').remove();
                        // email check
                        if ($(this).attr('type') == 'email') {
                            let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                            if (regex.test($(this).val())) {
                                $(this).next('small.error').remove();
                            } else {
                                $('<small class="error">This is not a valid email.</small>').insertAfter($(this));
                                flagError = true;
                            }
                        }
                        // ends
                        //mobile check
                        if ($(this).attr('name') == 'mobile') {
                            if (isNaN(parseInt($(this).val())) == true) {
                                $('<small class="error">This is not a valid mobile number.</small>').insertAfter($(this));
                                flagError = true;
                            } else {
                                $(this).next('small.error').remove();
                            }
                        }
                        // ends

                        //checkbox or radio check
                        if ($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio') {
                            if ($(this).prop("checked", true)) {
                                $(this).next('small.error').remove();
                            } else {
                                $('<small class="error">This field is required.</small>').insertAfter($(this));
                                flagError = true;
                            }
                        }
                        // ends
                    }
                }
            });

            if (flagError) {
                return;
            }
            console.log('validation passed');

            toastr.warning('Preparing to add checkout info');
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                //showToastMessage(response, 'Information added successfully');
                window.location.replace(redirectUrl);

            }).done(function (response) {
                // console.log(response);
            });

            // $('#checkout-' + formId).validate({
            //     onkeyup: false,
            //     onfocusout: false,
            //     errorElement: 'small',
            //     submitHandler: function (form) {
            //         var data = {
            //             'action': 'rentmy_checkout_information',
            //             'data': $('#checkout-' + formId).serialize(),
            //             'step': formId
            //         };
            //
            //         toastr.warning('Preparing to add checkout info');
            //         $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
            //             //showToastMessage(response, 'Information added successfully');
            //             window.location.replace(redirectUrl);
            //
            //         }).done(function () {
            //             console.log(response);
            //         });
            //     }
            // });

        })
        .on("click", ".fullfilment-type li", function () { // onclick fulfillment tabs
            type = $(this).attr('data-id');
            $(".fullfilment-type li").removeClass('active');
            $(this).addClass('active');
            rm_checkout.fulfillment_type = type;
            if (type == 'instore') {
                $(this).addClass('active');
                $(".rm-instore-container").show();
                $(".rm-delivery-container").hide();
                $("#rm-btn-delivery-cost").hide();
                $("#rm-btn-shipping-cost").hide();
                $('#rentmy-btn-checkout-fulfillment').show();
            } else if (type == 'delivery') {
                $(".rm-instore-container").hide();
                $(".rm-delivery-container").show();
                $("#rm-btn-delivery-cost").show();
                $("#rm-btn-shipping-cost").hide();
                $("#rentmy-btn-checkout-fulfillment").hide();
            } else if (type == 'shipping') {
                $(".rm-instore-container").hide();
                $(".rm-delivery-container").show();
                $("#rm-btn-delivery-cost").hide();
                $("#rm-btn-shipping-cost").show();
                $("#rentmy-btn-checkout-fulfillment").hide();
            }

        })
        .on('click', '#rm-btn-delivery-cost', function () { // onclick get delivery cost
            var data = {
                'action': 'rentmy_options',
                'action_type': 'get_delivery_cost',
                'data': $('#checkout-fulfillment').serialize(),
            };
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                if (response.error) {
                    $('.checkout-fulfillment-message').show().text(response.error).addClass('error');
                } else {
                    $('.checkout-fulfillment-message').show().text(response.message);
                    $("#rentmy-btn-checkout-fulfillment").show();
                }

                // generate location delivery charge
                try {
                    if (response.location.length > 0) {
                        $('.checkout-fulfillment-message').html('<br>');
                        for (var key in response.location) {
                            $('.checkout-fulfillment-message').append(
                                $('<div class="custom-control custom-radio">' +
                                    '<input type="radio" id="customRadio1199123' + key + '" name="loc" class="custom-control-input" data-amount="' + response.location[key].charge + '" data-type="2" data-tax="' + response.location[key].tax + '">' +
                                    '<label class="custom-control-label" for="customRadio1199123' + key + '">' + response.location[key].name + '</label>' +
                                    '</div>')
                            );
                        }
                    }
                } catch (e) {
                    $('.checkout-fulfillment-message').show().text('No charge found for this delivery location').addClass('error');
                }
                // ends

            });

        })
        .on('click', '#rm-btn-shipping-cost', function () { // onclick get shipping methods
            $('.checkout-fulfillment-message').show().html('<div class="loader-main-area"><i class="shipping-method-loader fa fa-spin fa-spinner fa-3x"></i></div> ');
            var data = {
                'action': 'rentmy_options',
                'action_type': 'get_shipping_methods',
                'data': $('#checkout-fulfillment').serialize(),
            };
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                if (response.status == 'NOK') {
                    $('.checkout-fulfillment-message').show().html('<span class="rentmy-success-msg">' + response.result.error + '</span>');
                    return;
                }
                $('.checkout-fulfillment-message').show().html(response.html);
                // $('#rentmy-btn-checkout-fulfillment').show();
            });
        })
        .on('click', '.checkout-fulfillment-message input', function () { // select any shipping method and add shipping cost to cart
            var data = {
                'action': 'rentmy_options',
                'action_type': 'add_shipping_to_cart',
                'data': {
                    shipping_cost: $(this).attr('data-amount'),
                    shipping_method: $(this).attr('data-type'),
                    tax: $(this).attr('data-tax'),
                },

            };
            rm_checkout.shipping_response = $(this).val();
            $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                $('#rentmy-btn-checkout-fulfillment').show();
            });

        })
        .on('click', '#rentmy-btn-checkout-fulfillment', function () { // continue fulfillment
            var formId = $(this).attr('data-step');
            var redirectUrl = $(this).attr('data-succeredirect');
            $('#checkout-' + formId).validate({
                onkeyup: false,
                onfocusout: false,
                errorElement: 'small',
                submitHandler: function (form) {
                    if (rm_checkout.fulfillment_type == 'instore') {
                        var selected = $("input[type='radio'][name='rm_instore_loc']:checked");
                        var postData = {
                            id: selected.val(),
                            location: selected.attr('data-location'),
                            name: selected.attr('data-name'),
                            type: "instore",
                            shipping_method: $('#shipping_method').val(),
                        };
                    } else if (rm_checkout.fulfillment_type == 'shipping') {
                        var postData = {
                            shipping: rm_checkout.shipping_response,
                            shipping_address1: $('#rm_sh_address_line1').val(),
                            shipping_address2: $('#rm_sh_address_line2').val(),
                            shipping_city: $('#rm_sh_city').val(),
                            shipping_country: $('#rm_sh_country').val(),
                            shipping_state: $('#rm_sh_state').val(),
                            shipping_zipcode: $('#rm_sh_zipcode').val(),
                            shipping_method: $('#shipping_method').val(),
                            type: "shipping"
                        };
                    }
                    var data = {
                        'action': 'rentmy_checkout_information',
                        'data': postData,
                        'step': formId
                    };
                    $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                        window.location.replace(redirectUrl);

                    }).done(function () {

                    });
                }
            });
        })
        .on("click", ".payment-type li", function () { // onclick payments tabs
            type = $(this).attr('data-id');
            $(".payment-type li").removeClass('active');
            $(this).addClass('active');
            if (type == 'credit-card') {
                $(".rm-card-payment-container").show();
                $(".rm-others-payment-container").hide();
                rm_checkout.payment_gateway_id = $(this).children('.rm_payment_gateway_id').val();
                rm_checkout.payment_gateway_name = $(this).children(".rm_payment_gateway_name").val();
                rm_checkout.stripe_key = $(this).children(".rm_stripe_key").val();
                rm_checkout.payment_gateway_type = 1;
                if (rm_checkout.payment_gateway_name == 'Stripe') {
                    $('#card-element').show();
                    stripe= Stripe(rm_checkout.stripe_key);
                    elements = stripe.elements();
                    card = elements.create('card');
                    card.mount('#card-element');
                } else {
                    $('#other-card-element').show();
                }

            } else if (type == 'others') {
                $(".rm-card-payment-container").hide();
                $(".rm-others-payment-container").show();
                rm_checkout.payment_gateway_id = $(this).children('.rm_payment_gateway_id').val();
                rm_checkout.payment_gateway_name = $(this).children(".rm_payment_gateway_name").val();
                rm_checkout.stripe_key = $(this).children(".rm_stripe_key").val();
                this.payment_gateway_type = 2;

            }
            //  console.log(rm_checkout);

        })
        .on('click', '#rentmy-btn-checkout-payment', function (e) { // continue payment with credit card
            var redirectUrl = $(this).attr('data-succeredirect');
            $('#checkout-payment').validate({
                onkeyup: false,
                onfocusout: false,
                errorElement: 'small',
                rules: {
                    rm_cardNo: {required: true, number: true},
                    rm_cardName: {required: true},
                    rm_expireMonth: {required: true},
                    rm_expireYear: {required: true},
                    rm_cvv: {required: true, number: true},
                },
                messages: {
                    rm_cardNo: {required: 'Please enter valid card number'},

                },
                submitHandler: function (e) {
                    if (rm_checkout.payment_gateway_name == 'Stripe') { // stripe payment
                       // Stripe.setPublishableKey(rm_checkout.stripe_key);
                      //  var elements = stripe.elements();
                     //   var card = elements.create('card');
                     //   card.mount('#card-element');
                        stripe.createToken(card).then(function (result) {
                            if (result.error) {
                                // Inform the user if there was an error
                                //var errorElement = document.getElementById('card-errors');
                                //errorElement.textContent = result.error.message;
                            } else {
                                // Send the token to your server
                                token= result.token.id;
                                var data = {
                                    'action': 'rentmy_options',
                                    'action_type': 'submit_order',
                                    'data': {
                                        'card_name': $('#rm_cardName').val(),
                                        'card_no': token,
                                        'exp_month': $('#rm_expireMonth').val(),
                                        'exp_year': $('#rm_expireYear').val(),
                                        'cvv': $('#rm_cvv').val(),
                                        'payment_gateway_id': rm_checkout.payment_gateway_id,
                                        'payment_gateway_name': rm_checkout.payment_gateway_name,
                                        'payment_gateway_type': rm_checkout.payment_gateway_type,
                                        'payment_amount': parseFloat(rm_checkout.payment_amount),
                                        'amount_tendered': rm_checkout.amount_tendered,
                                    },
                                };

                                toastr.warning('Order processing...');
                                $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                                    //$('.checkout-fulfillment-message').show().html(response.html);
                                    // $('#rentmy-btn-checkout-fulfillment').show();
                                    if (response.status == 'OK') {
                                        toastr.success('Order created.');
                                        window.location.replace(redirectUrl);
                                    } else {
                                        toastr.error(response.message);
                                    }
                                });


                            }
                        });


                        // Stripe.card.createToken(
                        //     {
                        //         number: $('#rm_cardNo').val(),
                        //         cvc: $('#rm_cvv').val(),
                        //         exp_month: $('#rm_expireMonth').val(),
                        //         exp_year: $('#rm_expireYear').val()
                        //     },
                        //     (status, response) => {
                        //         if (status === 200) {
                        //             var data = {
                        //                 'action': 'rentmy_options',
                        //                 'action_type': 'submit_order',
                        //                 'data': {
                        //                     'card_name': $('#rm_cardName').val(),
                        //                     'card_no': response.id,
                        //                     'exp_month': $('#rm_expireMonth').val(),
                        //                     'exp_year': $('#rm_expireYear').val(),
                        //                     'cvv': $('#rm_cvv').val(),
                        //                     'payment_gateway_id': rm_checkout.payment_gateway_id,
                        //                     'payment_gateway_name': rm_checkout.payment_gateway_name,
                        //                     'payment_gateway_type': rm_checkout.payment_gateway_type,
                        //                     'payment_amount': parseFloat(rm_checkout.payment_amount),
                        //                     'amount_tendered': rm_checkout.amount_tendered,
                        //                 },
                        //             };
                        //
                        //             toastr.warning('Order processing...');
                        //             $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                        //                 //$('.checkout-fulfillment-message').show().html(response.html);
                        //                 // $('#rentmy-btn-checkout-fulfillment').show();
                        //                 if (response.status == 'OK') {
                        //                     toastr.success('Order created.');
                        //                     window.location.replace(redirectUrl);
                        //                 } else {
                        //                     toastr.error(response.message);
                        //                 }
                        //             });
                        //
                        //         } else {
                        //             toastr.error(response, response.error.message);
                        //         }
                        //     }
                        // );


                    } else {
                        var data = {
                            'action': 'rentmy_options',
                            'action_type': 'submit_order',
                            'data': {
                                'card_name': $('#rm_cardName').val(),
                                'card_no': $('#rm_cardNo').val(),
                                'exp_month': $('#rm_expireMonth').val(),
                                'exp_year': $('#rm_expireYear').val(),
                                'cvv': $('#rm_cvv').val(),
                                'payment_gateway_id': rm_checkout.payment_gateway_id,
                                'payment_gateway_name': rm_checkout.payment_gateway_name,
                                'payment_gateway_type': rm_checkout.payment_gateway_type
                            }
                        };

                        toastr.warning('Order processing...');
                        $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                            //$('.checkout-fulfillment-message').show().html(response.html);
                            // $('#rentmy-btn-checkout-fulfillment').show();
                            if (response.status == 'OK') {
                                toastr.success('Order created.');
                                window.location.replace(redirectUrl);
                            } else {
                                toastr.error(response.message);
                            }
                        });
                    }

                }
            });

        })
        .on('click', '#rentmy-btn-checkout-others', function (e) {
            var redirectUrl = $(this).attr('data-succeredirect');
            $('#checkout-others-payment').validate({
                onkeyup: false,
                onfocusout: false,
                errorElement: 'small',
                rules: {
                    rm_payment_note: {required: true},
                },
                submitHandler: function (e) {
                    var data = {
                        'action': 'rentmy_options',
                        'action_type': 'submit_order',
                        'data': {
                            'note': $('#rm_payment_note').length ? $('#rm_payment_note').val() : '',
                            'payment_gateway_id': rm_checkout.payment_gateway_id,
                            'payment_gateway_name': rm_checkout.payment_gateway_name,
                            'payment_gateway_type': 2,
                            'custom_values': localStorage.getItem("custom_values") ? JSON.parse(localStorage.getItem("custom_values")) : []
                        },
                    };
                    toastr.warning('Order processing...');
                    $.post(rentmy_ajax_object.ajaxurl, data, function (response) {
                        if (response.status == 'OK') {
                            localStorage.removeItem("custom_values");
                            toastr.success('Order created.');
                            window.location.replace(redirectUrl);
                        } else {
                            toastr.error(response.message);
                        }
                    });
                }
            });
        })
        .on('keyup, change', '#rentmy-custom-checkout-wrapper', function (e) {
            var custom_values = [];
            localStorage.removeItem("custom_values");
            $(this).find('input, select').each(function () {
                if ($(this).val() == '') {
                    return;
                }
                custom_values.push({
                    field_label: $(this).attr('data-field_label'),
                    field_name: $(this).attr('name'),
                    field_values: $(this).val(),
                    id: $(this).attr('data-field_id'),
                    type: $(this).attr('data-field_type')
                });
            });
            localStorage.setItem("custom_values", JSON.stringify(custom_values));
        })
        .on('change', '#rentmy-custom-checkout-wrapper input[type="file"]', function (e) {

            e.preventDefault();
            let formData = new FormData();
            formData.append('file', e.target.files[0]);
            formData.append('type', $(this).attr('name'));
            formData.append('action', 'rentmy_options');
            formData.append('action_type', 'upload_media');

            $.ajax({
                enctype: 'multipart/form-data',
                url: rentmy_ajax_object.ajaxurl,
                cache: false,
                contentType: false,
                processData: false,
                data: formData,
                type: 'post',
                success: function (response) {
                    console.log(response);
                }
            }).error(function (response) {
                console.log(response);
            });
        })
        .on('change', '.agreementCheckProceed', function (e) {

            e.preventDefault();
            if ($(this).prop('checked')) {
                $('.agreementCheckProceed').prop('checked', true);
                $('#rentmy-btn-checkout-billing').attr('disabled', false);
                $('.popup-continue-btn').attr('disabled', false);
                if ($('.signature-pad-wrapper').length > 0) {
                    $('.signature-pad-wrapper').show();
                }
            } else {
                $('.agreementCheckProceed').prop('checked', false);
                $('#rentmy-btn-checkout-billing').attr('disabled', true);
                $('.popup-continue-btn').attr('disabled', true);
                if ($('.signature-pad-wrapper').length > 0) {
                    $('.signature-pad-wrapper').hide();
                    if (rm_checkout.signaturePad) {
                        rm_checkout.signaturePad.clear();
                        $('#signature').val('');
                    }
                }
            }

        })
        .on('click', '.clear-signature', function (e) {
            e.preventDefault();
            if (rm_checkout.signaturePad) {
                rm_checkout.signaturePad.clear();
                $('#signature').val('');
            }
        })
        .on('click', 'a.rentmy-terms-and-condition', function (e) {
            $('.pop-up-content').addClass('open');
            console.log('asd');
        })
        .on('click', '.pop-up-content .close, .popup-back-btn', function (e) {
            e.preventDefault();
            $('.pop-up-content').removeClass('open');
        })
        .on('click', '.popup-continue-btn', function (e) {
            e.preventDefault();
            $('.pop-up-content').removeClass('open');
            $("#rentmy-btn-checkout-billing").trigger('click');
        })
        .on('click', '.payment-type ul li', function (e) {
            var labelText = $(this).find('.rentmy-payment-label-top').html();
            $('.rentmy-payment-label-bottom').html(labelText);
        })

    ;

    rm_checkout.init();
});
