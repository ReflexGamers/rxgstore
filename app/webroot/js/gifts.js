(function($){

    function setGiftMessageChars(input) {
        $('.gift_message_chars').first()
            .html(input.val().length + ' / ' + input.attr('maxlength'));
    }

    setGiftMessageChars($('.gift_message_input').first().on('keyup', function(){
        setGiftMessageChars($(this));
    }));

})(jQuery);