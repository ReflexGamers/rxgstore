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

    $.ajax(paypalChart.data('href-monthly'), {
        type: 'post',
        beforeSend: function () {

        },
        success: function (data, textStatus) {

            data = data.data;

            for (var i = 0; i < data.length; i++) {
                var column = data[i];
                data[i] = {
                    name: column[0],
                    drilldown: column[0],
                    y: column[1] / 100
                };
            }

            paypalChart.highcharts({
                chart: {
                    type: 'column',
                    backgroundColor: null,
                    plotBackgroundColor: null,
                    events: {
                        drilldown: function (e) {
                            var chart = this,
                                url = paypalChart.data('href-daily');

                            url += '?offset=' + (e.point.series.points.length - e.point.x);

                            $.ajax(url, {
                                success: function (drilldownData, textStatus) {

                                    drilldownData = drilldownData.data;

                                    for (var i = 0; i < drilldownData.length; i++) {
                                        var column = drilldownData[i];
                                        drilldownData[i] = {
                                            name: column[0],
                                            y: column[1] / 100
                                        };
                                    }

                                    var pointName = e.point.name;

                                    chart.addSeriesAsDrilldown(e.point, {
                                        name: pointName,
                                        data: drilldownData
                                    });

                                    chart.setTitle({
                                        text: 'PayPal Income for ' + pointName
                                    });
                                }
                            });
                        },
                        drillup: function (e) {
                            var chart = this;
                            chart.setTitle({
                                text: 'PayPal Income Past Year'
                            });
                        }
                    }
                },
                title: {
                    text: 'PayPal Income Past Year'
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Dollars'
                    },
                    maxPadding: 0.5
                },
                xAxis: {
                    type: 'category'
                },
                tooltip: {
                    headerFormat: '<span style="font-size: 16px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">PayPal Income: </td>' +
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
                    name: 'PayPal Income By Month',
                    type: 'column',
                    data: data
                }]
            });
        }
    });

})(jQuery);