(function($){

    $('#quickauth_charts').multiChart({
        chartFunc: 'buildPieChart',
        chartParams: {
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
            series: [{
                name: 'Uses'
            }]
        }
    });

})(jQuery);