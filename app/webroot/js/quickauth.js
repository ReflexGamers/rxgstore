(function($){

    var container = $('#quickauth_charts'),
        controls = container.find('.chart_control'),
        innerChart = container.find('.chart_inner');

    /**
     * Renders a chart with the given data.
     *
     * @param data
     */
    function renderChart(data) {
        rxg.buildPieChart(innerChart, data, 'QuickAuth Server Distribution');
    }

    /**
     * Sends an ajax request to the URL on the provided element's href, then renders a chart with the response.
     *
     * @param el
     * @returns {boolean}
     */
    function buildChart(el) {

        if (el.hasClass('active')) {
            return false;
        }

        controls.removeClass('active');

        // get data and build charts
        $.ajax(el.attr('href'), {

            type: 'post',
            beforeSend: function() {
                el.addClass('active');
                innerChart.animate({opacity: 0.1});
            },
            success: function(data, textStatus) {
                renderChart(data.data);
                innerChart.animate({opacity: 1});
            }

        });
    }

    container.find('.chart_controls').on('click', '.chart_control', function () {
        buildChart($(this));
        return false;
    });

    buildChart(
        container.find('.chart_controls').find('.control_week')
    );

})(jQuery);