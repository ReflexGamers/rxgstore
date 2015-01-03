(function($){

    var container = $('#quickauth_totals_chart');

    // builds a chart given an element and data
    function buildChart(el, data) {

        for (var i = 0; i < data.length; i++) {
            var row = data[i];
            row[1] = parseInt(row[1], 10);
        }

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