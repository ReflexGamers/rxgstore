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

    var paypalChart = $('#paypal_chart');

    $.ajax(paypalChart.data('href'), {
        type: 'post',
        beforeSend: function () {

        },
        success: function (data, textStatus) {

            for (var i = 0; i < data.data.length; i++) {
                var column = data.data[i];
                column[0] = Date.parse(column[0]);
                column[1] = parseInt(column[1], 10) / 100;
            }

            paypalChart.highcharts({
                chart: {
                    type: 'column',
                    backgroundColor: null,
                    plotBackgroundColor: null
                },
                title: {
                    text: 'PayPal Income This Month'
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Dollars'
                    }
                },
                xAxis: {
                    tickInterval: 1,
                    type: 'datetime',
                    dateTimeLabelFormats: {
                        day: '%e'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size: 16px">{point.key:%b %e}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                 '<td style="padding:0"><b>${point.y:,.2f}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointStart: 1,
                        pointPadding: 0,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'PayPal Income',
                    data: data.data,
                    pointInterval: 24 * 3600 * 1000
                }]
            })
        }
    });

})(jQuery);