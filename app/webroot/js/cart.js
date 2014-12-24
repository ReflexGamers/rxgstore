(function($){

    var shippingCost = parseInt($('#shipping_cost').val(), 10);
    var shippingFreeThreshold = parseInt($('#shipping_free_threshold').val(), 10);

    /**
     * Updates cart prices
     * @returns {boolean} true if cart isn't empty
     */
    function updateCart() {

        var inputs = $('.cart_quantity_input');
        var cartForm = $('#CartForm');

        if (!inputs.length && cartForm.length) {
            cartForm.remove();
            return false;
        }

        var subTotal = 0;
        var invalidInput = false;

        $.each(inputs, function(index, element){
            var el = $(element);
            var val = el.val();

            if (val > parseInt(el.attr('max'), 10) || val < parseInt(el.attr('min'), 10) || val % 1 !== 0 || (val !== "" && !$.isNumeric(val))) {
                invalidInput = true;
                el.addClass('input_error');
            } else {
                el.removeClass('input_error input_warning');
            }

            if (!invalidInput && val !== "") {
                var itemTotal = val * el.data('price');
                el.closest('tr').find('.total').find('.currency_value').first().html(rxg.formatNum(itemTotal));
                subTotal += itemTotal;
            }
        });

        var cash = $('#user_cash').find('.currency_value');
        var checkout = $('#cart_checkout_button');
        var update = $('#cart_update_button');

        if (!invalidInput) {

            var insufficientFunds = subTotal > parseInt(cash.html().replace(/,/g, ''), 10);
            var shipping = subTotal >= shippingFreeThreshold ? 0 : shippingCost;
            var total = subTotal + shipping;

            $('#cart_subtotal').find('.currency_value').first().html(rxg.formatNum(subTotal));
            $('#cart_shipping').find('.currency_value').first().html(rxg.formatNum(shipping));
            $('#cart_total').find('.currency_value').first().html(rxg.formatNum(total));

            if (insufficientFunds) {
                cash.addClass('cart_cash_insufficient');
            } else {
                cash.removeClass('cart_cash_insufficient');
            }
        }

        if (invalidInput || insufficientFunds) {
            checkout.prop('disabled', true).addClass('disabled');
            update.prop('disabled', true).addClass('disabled');
        } else {
            checkout.prop('disabled', false).removeClass('disabled');
            update.prop('disabled', false).removeClass('disabled');
        }

        return true;
    }


    $('#cart_empty_button').on('click', function(){
        return confirm('Are you sure you want to empty your cart?');
    });

    $('.cart_quantity_input').on('keyup input', updateCart);

    $('.cart_remove').on('click', function(){

        if (!confirm('Are you sure you want to remove this item from your cart?')) {
            return false;
        }

        var el = $(this);

        $.ajax(el.parent().attr('href'), {

            type: 'post',
            beforeSend: function() {

            },
            success: function(data, textStatus) {
                el.closest('tr').remove();
                rxg.updateCartLink(!updateCart());
            }

        });

        return false;
    });

    updateCart();

})(jQuery);