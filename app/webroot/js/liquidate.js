(function($){

    function isValidQuantityInput($el, val) {
        return val === '' || (
            $.isNumeric(val) &&
            val <= parseInt($el.attr('max'), 10) &&
            val >= parseInt($el.attr('min'), 10) &&
            val % 1 === 0
        );
    }

    /**
     * Updates liquidation totals
     */
    function updateTotals() {

        var inputs = $('.liquidate_quantity_input');

        var total = 0;
        var invalidInput = false;

        $.each(inputs, function(index, element){
            var $el = $(element);
            var val = $el.val();

            if (!isValidQuantityInput($el, val)) {
                invalidInput = true;
                $el.addClass('input_error');
            } else {
                $el.removeClass('input_error input_warning');
            }

            if (!invalidInput) {
                var itemTotal = val * $el.data('price');
                var $elItemTotal = $el.closest('tr').find('.liquidate_item_total');
                $elItemTotal.find('.currency_value').first().html(rxg.formatNum(itemTotal));
                total += itemTotal;
            }
        });

        var $submit = $('#liquidate_confirm_button');

        if (!invalidInput) {
            $('#liquidate_total').find('.currency_value').first().html(rxg.formatNum(total));
        }

        if (invalidInput || !total) {
            $submit.prop('disabled', true).addClass('disabled');
        } else {
            $submit.prop('disabled', false).removeClass('disabled');
        }
    }


    $('.liquidate_quantity_input').on('keyup input', updateTotals);

    updateTotals();

})(jQuery);
