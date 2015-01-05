(function($){

    var creditChart = $('#credit_chart');

    // get data and build charts
    $.ajax(creditChart.data('href'), {

        type: 'post',
        beforeSend: function() {

        },
        success: function(data, textStatus) {
            var allTime = data.allTime;
            rxg.buildCashTimeChart(creditChart.find('.chart_inner'), allTime.startDate, allTime.creditLog, allTime.currencyMult, 'Total CASH (All Time)');
        }

    });

})(jQuery);