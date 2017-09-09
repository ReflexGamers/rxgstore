(function($){

    var servers = $('.item_edit_servers').find('input[type="checkbox"]');
    var childServers = $('#childServers').val().split(',');
    var parent;
    var parents = [];

    $.each(servers, function(index, element){

        var el = $(this);
        var server = el.val();

        if ($.inArray(server, childServers) != -1) {
            el.addClass('item_edit_server_child child_' + parent);
            parents[server] = parent;
        } else {
            parent = server;
        }

    });

    function evalServers() {

        var el = $(this);
        var changedServer = el.val();

        if (parents[changedServer]) {
            //child was changed
            var allChildrenChecked = [];

            //loop through children
            $.each(servers, function(){
                var el = $(this);
                var parent = parents[el.val()];
                if (parent) {
                    allChildrenChecked[parent] = el.is(':checked') && allChildrenChecked[parent] !== false;
                }
            });

            //loop through parents
            $.each(servers, function(){
                var el = $(this);
                var server = el.val();
                if (!parents[server] ) {
                    //is parent
                    el.prop('checked', allChildrenChecked[server]);
                }
            });

        } else {
            //parent was changed
            servers.filter('.child_' + changedServer).prop('checked', el.is(':checked'));
        }
    }

    $.each(servers, function(){
        var el = $(this);
        if (!parents[el.val()] && el.is(':checked')) {
            //Call only on checked parents
            evalServers.call(this);
        }
    });

    servers.on('click', evalServers);


    $('.item_edit_features').on('click', '.feature_remove', function(){
        $(this).closest('.item_edit_feature').slideUp(function(){
            featureTemplate = featureTemplate || $(this).clone();
            $(this).remove();
        });
    });

    function increaseFieldIndex(match) {
        return parseInt(match) + 1;
    }

    function incrementFeatureProps(input, props) {
        for (var i = 0; i < props.length; i++){
            input.attr(props[i], input.attr(props[i]).replace(/\d+/g, increaseFieldIndex));
        }
    }

    var featureTemplate;

    $('#item_edit_feature_add').on('click', function(){
        var lastFeature = $('.item_edit_feature').last();

        if (!lastFeature.length) {
            lastFeature = featureTemplate.clone();
        } else if (lastFeature.find('.edit_input').first().val() == '') {
            return;
        }

        var newFeature = lastFeature.clone();
        newFeature.find('.item_edit_feature_id').remove();

        if (!featureTemplate || !featureTemplate.length) {
            featureTemplate = newFeature.clone();
        }

        var newInput = newFeature.find('.edit_input');
        incrementFeatureProps(newInput, ['id', 'name']);
        newInput.val('');

        newFeature.hide();
        $(this).before(newFeature);
        newFeature.slideDown();
    });

    $('#item_preview').on('click', function(){
        $.ajax($(this).data('href'), {
            type: 'post',
            data: {
                //$(this).closest('form').serialize()
                description: $('#item_edit_description').val()
            },
            beforeSend: function() {
                $('.item_preview_loading').fadeIn();
            },
            success: function(data, textStatus) {
                $('.item_preview_divider').slideDown();
                $('#item_preview_content').html(data).slideDown();
            },
            complete: function() {
                $('.item_preview_loading').fadeOut();
                $('html, body').animate({
                    scrollTop: $('#item_preview_content').offset().top
                }, 750);
            }
        });
    });

})(jQuery);
