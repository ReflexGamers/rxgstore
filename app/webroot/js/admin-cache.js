(function($){

    $('#cache_clear_expired').on('click', function(){

        if (!confirm('Are you sure you want to prune all expired players from the cache?')) {
            return false;
        }

        var el = $(this);

        $.ajax(el.attr('href') || el.data('href'), {

            type: 'post',
            beforeSend: function(){
                $('#cache_loading').fadeIn();
            },

            success: function(data, textStatus) {
                $('#cache_content').html(data);
                $('#cache_loading').fadeOut();
            }
        });

        return false;
    });


    //$('#cache_clearall').on('click', function(){
    //
    //    if (!confirm('Are you sure you clear the entire cache?')) {
    //        return false;
    //    }
    //
    //    var el = $(this);
    //
    //    $.ajax(el.attr('href') || el.data('href'), {
    //
    //        type: 'post',
    //        success: function(data, textStatus) {
    //            $('#cache_batch_actions').remove();
    //            $('#cache_content').html(data);
    //        }
    //    });
    //
    //    return false;
    //});


    $('#cache_content').on('click', '.cache_refresh', function(){

        if (!confirm('Are you sure you want to refresh this player in the cache?')) {
            return false;
        }

        var el = $(this);
        var entry = el.closest('.cache_entry');

        $.ajax(el.attr('href') || el.data('href'), {

            type: 'post',
            success: function(data, textStatus) {
                entry.html(data);
            }
        });

        return false;

    }).on('click', '.cache_clear', function(){

        if (!confirm('Are you sure you want to delete this player from the cache?')) {
            return false;
        }

        var el = $(this);
        var entry = el.closest('.cache_entry');

        $.ajax(el.attr('href') || el.data('href'), {

            type: 'post',
            success: function(data, textStatus) {
                entry.slideUp(function(){
                    $(this).remove();
                });
            }
        });

        return false;
    });


    $('#cached_chart').buildChart({
        chartFunc: 'buildPieChart',
        chartParams: {
            title: {
                text: 'Total Cached Players'
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
                            fontSize: '16px'
                        }
                    }
                }
            },
            series: [{
                name: 'Count'
            }]
        }
    });

})(jQuery);