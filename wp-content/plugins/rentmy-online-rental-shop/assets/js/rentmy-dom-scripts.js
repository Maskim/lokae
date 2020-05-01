(function($) {
    $(document).ready(function(){

        // load cart token in token field
        loadCartToken();
        function loadCartToken(){
            jQuery('.cart-item-data').find('#token').val( getCookie('rentmy_cart_token') );
        }

        // message popup for flash notify
        function showToastMessage(response, messageCustom) {
            if(typeof response.error != 'undefined'){
                toastr.error(response.error, 'Hey');
            }
            if(typeof response.message != 'undefined'){
                toastr.warning(response.message, 'Hey');
            }
            if(typeof response.data != 'undefined'){
                toastr.success(messageCustom, 'Hey');
            }
        }

        // add to cart button press
        jQuery('.single_add_to_cart_button').click(function(e){
            var thisBtn = jQuery(this);
            var formId = jQuery("#cart-item-data-" + thisBtn.attr('data-id'));
            var data = {
            		action: 'rentmy_add_to_cart',
                data: formId.serialize()
          	};
            thisBtn.attr("disabled", true);
            toastr.warning('Preparing to add to cart');
          	jQuery.post(rentmy_ajax_object.ajaxurl, data, function(response){
                setCookie('rentmy_cart_token', response.data.token, 1000);
                formId.find('#token').val( response.data.token );
                showToastMessage(response, 'Item added to cart successfully');
          	})
            .fail(function(response){
                toastr.error(response);
            })
            .done(function(){
                thisBtn.attr("disabled", false);
            });
        });

        // checkout save data step by step
        jQuery('.rentmy_checkout_submit').click(function(e){
            e.preventDefault();
            var thisBtn = jQuery(this);
            var formId = jQuery("#checkout-" + thisBtn.attr('data-step'));
            var data = {
            		'action': 'rentmy_checkout_information',
                'data': formId.serialize(),
                'step': thisBtn.attr('data-step')
          	};
            thisBtn.attr("disabled", true);
            toastr.warning('Preparing to add checkout info');
          	jQuery.post(rentmy_ajax_object.ajaxurl, data, function(response){
                showToastMessage(response, 'Information added successfully');
                setTimeout(function() {
                    window.location.replace( thisBtn.attr('data-succeredirect') );
                }, 1000);
          	}).done(function(){
                thisBtn.attr("disabled", false);
            });
        });

    });
}(jQuery));
