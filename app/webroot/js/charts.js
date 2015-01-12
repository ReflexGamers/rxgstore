(function($){

    window.rxg = window.rxg || {};


    /**
     * Builds a time chart given a container element, the data and a title.
     *
     * @param el
     * @param {string} startDate
     * @param {object} data
     * @param {number} currencyMult
     * @param {string} title
     */
    window.rxg.buildCashTimeChart = function (el, startDate, data, currencyMult, title) {

        for (var i = 0; i < data.length; i++) {
            data[i] = parseInt(data[i], 10);
        }

        el.highcharts({
            chart: {
                zoomType: 'x',
                backgroundColor: null,
                plotBackgroundColor: null
            },
            title: {
                text: title
            },
            subtitle: {
                text: document.ontouchstart === undefined ?
                    'Click and drag in the plot area to zoom in' :
                    'Pinch the chart to zoom in'
            },
            tooltip: {
                headerFormat: '<span style="font-size: 16px">{point.key}</span><br/>',
                pointFormat: '{series.name}: <b>{point.y:,.0f}</b> (${point.yDollars:,.2f})',
                formatter: function (a) {
                    // add dollars on the fly to save memory (will save after)
                    if (!this.point.yDollars) {
                        this.point.yDollars = this.point.y / currencyMult / 100;
                    }
                    return a.defaultFormatter.call(this, a);
                },
                style: {
                    fontSize: '14px',
                    lineHeight: '24px'
                }
            },
            xAxis: {
                type: 'datetime',
                minRange: 24 * 3600000 // 1 day
            },
            yAxis: {
                title: {
                    text: 'Total CASH'
                },
                maxPadding: 0.5 // push graph down for reset zoom button
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    },
                    marker: {
                        radius: 2
                    },
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
            },
            series: [{
                type: 'area',
                name: 'CASH',
                pointInterval: 4 * 3600 * 1000,    // 4 hours each point
                pointStart: Date.parse(startDate),
                data: data
            }]
        });
    };

    // radialize color for all pie charts
    Highcharts.getOptions().plotOptions.pie.colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
        return {
            radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
            stops: [
                [0, color],
                [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
            ]
        };
    });


    $.fn.extend({

        /**
         * Builds a chart given an element, the data and a title.
         * @param data
         * @param params
         */
        buildPieChart: function(data, params) {

            var el = $(this);

            for (var i = 0; i < data.length; i++) {
                var row = data[i];
                row[1] = parseInt(row[1], 10);
            }

            // slice 2nd piece (should be 2nd biggest)
            data[1] = {
                name: data[1][0],
                y: data[1][1],
                sliced: true,
                selected: true
            };

            el.highcharts($.extend(true, {
                chart: {
                    type: 'pie',
                    backgroundColor: null,
                    plotBackgroundColor: null,
                    options3d: {
                        enabled: true,
                        alpha: 45,
                        beta: 0
                    }
                },
                title: {
                    text: 'title here'
                },
                tooltip: {
                    headerFormat: '<span style="font-size: 18px">{point.key}</span><br/>',
                    pointFormat: '{series.name}: <b>{point.y}</b> ({point.percentage:.1f}%)',
                    style: {
                        fontSize: '16px',
                        lineHeight: '24px'
                    }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        depth: 35,
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.percentage:.1f}%',
                            style: {
                                fontSize: '14px'
                            }
                        }
                    }
                },
                series: [{
                    type: 'pie',
                    data: data
                }]
            }, params));
        },

        /**
         * Builds a multi-chart on the element.
         * @param params
         */
        multiChart: function (params) {

            var el = $(this),
                controls = el.find('.chart_control'),
                innerChart = el.find('.chart_inner'),
                initOnSelector = '.active',
                buildParams = {
                    innerChart: innerChart,
                    chartFunc: params.chartFunc,
                    chartParams: params.chartParams
                };

            el.find('.chart_controls').on('click', '.chart_control', function () {

                var el = $(this);

                if (el.hasClass('active')) {
                    return false;
                }

                controls.removeClass('active');
                $(this).buildInnerChart(buildParams);

                return false;
            });

            el.find(initOnSelector).buildInnerChart(buildParams);
        },

        /**
         * Builds a chart on the element
         * @param params
         * @returns {boolean}
         */
        buildInnerChart: function (params) {

            var el = $(this),
                innerChart = params.innerChart;

            el.addClass('active');

            // get data and build charts
            $.ajax(el.attr('href'), {

                type: 'post',
                beforeSend: function() {
                    innerChart.animate({opacity: 0.5});
                },
                success: function(data, textStatus) {
                    innerChart[params.chartFunc](data.data, params.chartParams);
                    innerChart.animate({opacity: 1});
                }

            });

            return true;
        },

        /**
         * Builds a chart on the element
         * @param params
         */
        buildChart: function (params) {

            var el = $(this);

            $.ajax(el.data('href'), {

                type: 'post',
                beforeSend: function() {
                },
                success: function(data, textStatus) {
                    el[params.chartFunc](data.data, params.chartParams);
                }

            });
        }
    });

})(jQuery);