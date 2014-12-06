(function($){

    $.fn.scrollTo = function( target, options, callback ){
        if(typeof options == 'function' && arguments.length == 2){ callback = options; options = target; }
        var settings = $.extend({
            scrollTarget  : target,
            offsetTop     : 50,
            duration      : 500,
            easing        : 'swing'
        }, options);
        return this.each(function(){
            var scrollPane = $(this);
            var scrollTarget = (typeof settings.scrollTarget == "number") ? settings.scrollTarget : $(settings.scrollTarget);
            var scrollY = (typeof scrollTarget == "number") ? scrollTarget : scrollTarget.offset().top + scrollPane.scrollTop() - parseInt(settings.offsetTop);
            scrollPane.animate({scrollTop : scrollY }, parseInt(settings.duration), settings.easing, function(){
                if (typeof callback == 'function') { callback.call(this); }
            });
        });
    };

    $('#content').on('click', '.flash_remove', function () {
        $(this).closest('.flashMessage').slideUp();
    });

    $('#activity').on('click', '.recipient_expand', function(){
        $(this).closest('.activity_item').find('.activity_reward_recipients').slideToggle(1000);
    });

    var shoutbox = $('#shoutbox');
    var updateInterval = $('#shoutbox_post_cooldown').val() * 1000;
    var submitDelay = $('#shoutbox_update_interval').val() * 1000;
    var updateTimer;

    function restartUpdateTimer() {
        clearInterval(updateTimer);
        updateTimer = setInterval(updateShoutbox, updateInterval);
    }


    function scrollShoutbox(instant) {
        var shoutbox = $('#shoutbox_content');
        var height = shoutbox.prop('scrollHeight');

        shoutbox.animate({scrollTop: height}, instant ? 0 : 750);
    }


    function updateShoutbox() {

        var btn = $('#shoutbox_button');

        $.ajax($('#shoutbox_updateurl').val(), {

            beforeSend: function(){
                $('#shoutbox_loading').fadeIn();
            },

            success: function(data, textStatus){
                if (textStatus == 'success') {
                    $('#shoutbox_content').html(data);
                    scrollShoutbox();
                }
                $('#shoutbox_loading').fadeOut();
            },

            complete: function(){
                restartUpdateTimer();
            }
        });
    }


    shoutbox.on('click', '.shoutbox_delete', function(){

        if (!confirm('Are you sure you want to remove this message?')) {
            return false;
        }

        var el = $(this);
        var message = el.closest('.shoutbox_item');

        $.ajax(el.attr('href') || el.data('href'), {

            type: 'post',
            beforeSend: function(){
                $('#shoutbox_loading').fadeIn();
            },

            success: function(data, textStatus) {
                if (textStatus == 'success') {
                    message.slideUp(function(){
                        $(this).remove();
                    });
                }
                $('#shoutbox_loading').fadeOut();
            }
        });

        return false;
    });

    $('#shoutbox_button').on('click', function(){

        if ($('#shoutbox_input').val() == '') {
            return false;
        }

        var el = $(this);
        var form = el.closest('form');
        var btn = $('#shoutbox_button');

        $.ajax(form.attr('action'), {

            type: 'post',
            data: form.serialize(),

            beforeSend: function(){
                $('#shoutbox_loading').fadeIn();
            },

            success: function(data, textStatus){
                $('#shoutbox_content').html(data);
                $('#shoutbox_loading').fadeOut();
                $('#shoutbox_input').val('');
                scrollShoutbox();
            },

            complete: function(){
                restartUpdateTimer();
                btn.attr('disabled', 'disabled').addClass('disabled');
                setTimeout(function(){
                    btn.removeAttr('disabled').removeClass('disabled');
                }, submitDelay)
            }

        });

        return false;
    });


    scrollShoutbox(true);
    if (shoutbox.length != 0) {
        restartUpdateTimer();
    }

    $(window).load(function(){
        //Webkit browsers
        scrollShoutbox(true);
    });

    window.rxg = window.rxg || {};

    window.rxg.onActivityPageLoad = function() {
        var content = $('#activity');
        if ($.rateit) {
            content.find('.rateit').rateit();
        }
        rxg.scrollTo(content, 250);
    };

    window.rxg.onReviewPageLoad = function() {
        var content = $('#reviews');
        content.find('.rateit').rateit();
        rxg.scrollTo(content, 250);
    };

    window.rxg.scrollTo = function(el, duration) {
        $('html, body').animate({scrollTop: $(el).offset().top}, typeof duration == 'undefined' ? 250 : duration);
    };

    window.rxg.updateCartLink = function(remove) {

        if (remove) {
            $('#cart_link_content').remove();
            return;
        }

        $.ajax($('#cart_update_location').val(), {
            success: function(data, textStatus){
                if (textStatus == 'success') {
                    $('#cart_link_content').html(data);
                }
            }
        });
    };

})(jQuery);