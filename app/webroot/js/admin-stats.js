(function($){

    var container = $('#credit_charts');

    // get data and build charts
    $.ajax(container.data('href'), {

        type: 'post',
        beforeSend: function() {

        },
        success: function(data, textStatus) {
            var allTime = data.allTime;
            rxg.buildCashTimeChart(container.find('.chart_inner'), allTime.startDate, allTime.creditLog, allTime.currencyMult, 'Total CASH (All Time)');
        }

    });

})(jQuery);