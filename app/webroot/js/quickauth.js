(function($){

    var container = $('#quickauth_totals_chart');

    // builds a chart given an element and data
    function buildChart(el, data) {

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
                text: 'QuickAuth Server Distribution'
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
    }

    Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
        return {
            radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
            stops: [
                [0, color],
                [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
            ]
        };
    });

    // get data and build charts
    $.ajax(container.data('href'), {

        type: 'post',
        beforeSend: function() {

        },
        success: function(data, textStatus) {
            buildChart(container, data.data);
        }

    });

})(jQuery);