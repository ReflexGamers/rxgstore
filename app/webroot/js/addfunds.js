(function($){

    $('#addfunds_more_btn').on('click', function(){

        $(this).hide();
        $('#addfunds_more').slideDown();

    });

    var currencyMult = $('#addfunds_receive').data('currency-mult');

    $('#addfunds_input').on('keyup change', function(){

        var el = $(this);
        var val = el.val();
        var min = parseFloat(el.attr('min'));

        var output = $('#addfunds_receive').find('.currency_value').first();
        var btn = $('#addfunds_more_submit');

        if (val < min) {
            output.text('Must be at least $' + min.toFixed(2));
            btn.prop('disabled', true).addClass('disabled');
            return;
        }

        btn.prop('disabled', false).removeClass('disabled');

        var amount = val * 100 * currencyMult;
        output.text(rxg.formatNum(amount));

    });

})(jQuery);