(function($){

    var container = $('#quickauth_charts');

    // get data and build charts
    $.ajax(container.data('href'), {

        type: 'post',
        beforeSend: function() {

        },
        success: function(data, textStatus) {
            rxg.buildPieChart(container.find('.chart_alltime'), data.allTime, 'QuickAuth Server Distribution (All Time)');
            rxg.buildPieChart(container.find('.chart_recent'), data.recent, 'QuickAuth Server Distribution (Past Day)');
        }

    });

})(jQuery);