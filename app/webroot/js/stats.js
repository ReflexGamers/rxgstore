(function($){

    $('#spent_chart').multiChart({
        chartFunc: 'buildPieChart',
        chartParams: {
            title: {
                text: 'Total CASH Spent by Item'
            },
            tooltip: {
                headerFormat: '<span style="font-size: 18px">{point.key}</span><br/>',
                pointFormat: '{series.name}: <b>{point.y}</b> (${point.yDollars:,.2f}) ({point.percentage:.1f}%)',
                formatter: function (a) {
                    // add dollars on the fly to save memory (will save after)
                    if (!this.point.yDollars) {
                        this.point.yDollars = this.point.y / 10000;
                    }
                    return a.defaultFormatter.call(this, a);
                },
                style: {
                    fontSize: '16px',
                    lineHeight: '24px'
                }
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                }
            },
            series: [{
                name: 'CASH'
            }]
        }
    });

    $('#bought_chart').multiChart({
        chartFunc: 'buildPieChart',
        chartParams: {
            title: {
                text: 'Total Items Bought'
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
                    dataLabels: {
                        style: {
                            fontSize: '12px'
                        }
                    }
                }
            },
            series: [{
                name: 'Bought'
            }]
        }
    });

})(jQuery);