(function($){

    var creditChart = $('#credit_chart'),
        paypalChart = $('#paypal_chart'),
        paypalUrls = {
            year: paypalChart.data('href-yearly'),
            month: paypalChart.data('href-monthly'),
            day: paypalChart.data('href-daily')
        },
        paypalCache = {};


    function loadTotalCashData(url, callback) {
        $.ajax(url, {
            success: function(data, textStatus) {
                callback(data.allTime);
            }
        });
    }

    function initCashChart(data) {
        rxg.buildCashTimeChart(creditChart.find('.chart_inner'), data.startDate, data.creditLog, data.currencyMult, 'Total CASH (All Time)');
    }

    loadTotalCashData(creditChart.data('href'), initCashChart);


    function makePaypalUrl(periodType, params) {
        var baseUrl = paypalUrls[periodType];
        return (params) ? paypalUrls[periodType] + '?' + $.param(params) : baseUrl;
    }

    function tryCache(key, callback, doFetch) {
        if (key in paypalCache) {
            callback(paypalCache[key]);
            return;
        }

        doFetch(function(data) {
            paypalCache[key] = data;
            callback(data);
        });
    }

    function tryAjaxCache(url, callback) {
        tryCache(url, callback, function(cacheData) {
            $.ajax(url, {
                success: function(data, textStatus) {
                    cacheData(data.data);
                }
            });
        });
    }

    function yearlyPaypalDataToHighcharts(data) {
        var highchartsData = [];

        Object.keys(data).forEach(function(i) {
            var column = data[i],
                name = column[0],
                amount = column[1] / 100,
                entry = {
                    name: name,
                    y: amount
                };

            if (amount) {
                entry.drilldown = name;
            }

            highchartsData[i] = entry;
        });

        return highchartsData;
    }

    function monthlyPaypalDataToHighcharts(data, year) {
        var highchartsData = [];

        Object.keys(data).forEach(function(i) {
            var column = data[i],
                name = column[0],
                amount = column[1] / 100,
                entry = {
                    name: name,
                    y: amount,
                    year: year
                };

            if (amount) {
                entry.drilldown = name;
            }

            highchartsData[i] = entry;
        });

        return highchartsData;
    }

    function dailyPaypalDataToHighcharts(data) {
        var highchartsData = [];

        Object.keys(data).forEach(function(i) {
            var column = data[i];
            highchartsData[i] = {
                name: column[0],
                y: column[1] / 100
            };
        });

        return highchartsData;
    }

    function getSeriesTitle(key, pointName) {
        switch (key) {
            case 'year': return 'PayPal Income By Year';
            case 'month': return 'PayPal Income for ' + pointName;
            case 'day': return 'PayPal Income for ' + pointName;
            default: return '';
        }
    }

    function addMonthlyDrilldown(chart, point, data) {
        var pointName = point.name;

        chart.addSeriesAsDrilldown(point, {
            seriesKey: 'month',
            name: pointName,
            data: data
        });

        chart.setTitle({
            text: getSeriesTitle('month', pointName)
        });
    }

    function addDailyDrilldown(chart, point, data) {
        var pointName = point.name;

        chart.addSeriesAsDrilldown(point, {
            seriesKey: 'day',
            name: pointName,
            data: data
        });

        chart.setTitle({
            text: getSeriesTitle('day', pointName)
        });
    }

    function drilldownToMonthly(chart, point, callback) {
        var year = point.name,
            url = makePaypalUrl('month', {year: year});

        tryAjaxCache(url, function(data) {
            var monthlyData = monthlyPaypalDataToHighcharts(data, year);
            addMonthlyDrilldown(chart, point, monthlyData);
            callback && callback(monthlyData);
        });
    }

    function drilldownToDaily(chart, point, callback) {
        var month = point.x,
            year = point.options.year,
            url = makePaypalUrl('day', {
                year: year,
                month: month
            });

        tryAjaxCache(url, function(data) {
            var dailyData = dailyPaypalDataToHighcharts(data);
            addDailyDrilldown(chart, point, dailyData);
            callback && callback(dailyData);
        });
    }

    function initPaypalChart(data) {
        paypalChart.highcharts({
            chart: {
                type: 'column',
                backgroundColor: null,
                plotBackgroundColor: null,
                events: {
                    drilldown: function (e) {
                        var chart = this,
                            point = e.point;

                        switch (point.series.options.seriesKey) {
                            case 'year':
                                drilldownToMonthly(chart, point);
                                break;
                            case 'month':
                                drilldownToDaily(chart, point);

                            // no default
                        }
                    },
                    drillup: function (e) {
                        var chart = this,
                            seriesOptions = e.seriesOptions;

                        chart.setTitle({
                            text: getSeriesTitle(seriesOptions.seriesKey, seriesOptions.name)
                        });
                    }
                }
            },
            title: {
                text: getSeriesTitle('year')
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
                seriesKey: 'year',
                name: getSeriesTitle('year'),
                type: 'column',
                data: data
            }]
        });
    }

    function tail(data) {
        return data.slice(-1)[0];
    }

    tryAjaxCache(makePaypalUrl('year'), function(data) {
        var yearlyData = yearlyPaypalDataToHighcharts(data);
        initPaypalChart(yearlyData);

        var latestYearData = tail(yearlyData);

        // drilldown to latest year if there was income
        if (latestYearData.y) {
            var chart = paypalChart.highcharts(),
                latestYearPoint = tail(chart.series[0].points);

            latestYearPoint.doDrilldown();
        }
    });

})(jQuery);
