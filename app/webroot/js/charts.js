(function($){

    window.rxg = window.rxg || {};

    /**
     * Builds a chart given an element, the data and a title.
     * @param el
     * @param data
     * @param title
     */
    window.rxg.buildPieChart = function(el, data, title) {

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

        el.highcharts({
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
                text: title
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
                name: 'Uses',
                data: data
            }]
        });
    };

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

})(jQuery);