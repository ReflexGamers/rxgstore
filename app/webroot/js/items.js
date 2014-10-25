(function($){

    function setReviewChars(input) {
        input.closest('.review').find('.review_chars')
            .html(input.val().length + ' / ' + input.attr('maxlength'));
    }

    function onRated(event, rating) {

        var el = $(this);
        var item_id = el.data('item_id');

        if (el.attr('id') == 'item_rateit') {
            //Update single review (item pages)
            //alert($('#review_rateit_' + item_id).rateit('value'))
            $('#review_rateit').rateit('value', rating);
        }

        //Save rating to db
        $.ajax(el.attr('href') || el.data('href'), {
            type: 'post',
            data: {
                'rating': rating * 2
            },
            success: function(data, textStatus) {
                $('#rating').html(data);
            },
            complete: function(){
                $('#item_rateit').rateit().on('rated', onRated);
            }
        });
    }


    $('#item_rateit').on('rated', onRated);


    var cartAddBtn = $('#cart_add');
    var cartQuantityInput = $('#cart_add_qty');

    function checkCartQuantity() {

        var val = cartQuantityInput.val();
        var max = parseInt(cartQuantityInput.attr('max'));
        var min = parseInt(cartQuantityInput.attr('min'));

        if (val != '' && (val > max || val < min || val % 1 != 0 || !$.isNumeric(val))) {
            cartQuantityInput.addClass('input_error');
            cartAddBtn.prop('disabled', true).addClass('disabled');
            return false;
        } else {
            cartQuantityInput.removeClass('input_error');
            cartAddBtn.prop('disabled', false).removeClass('disabled');
        }

        return true;
    }

    cartQuantityInput.on('keyup change', checkCartQuantity);

    $('#CartForm').submit(function(event) {

        if (!checkCartQuantity()) {
            return false;
        }

        $.ajax($(this).attr('action'), {

            type: 'post',
            data: {
                Cart: {
                    quantity: cartQuantityInput.val()
                }
            },

            beforeSend: function(){
                $('#flash_container').slideUp();
                $('#cart_add_loading').fadeIn();
            },

            success: function(data, textStatus){
                $('#flash_container').html(data).slideDown();
                $('#cart_add_loading').fadeOut();
                rxg.updateCartLink();
            }
        });

        return false;
    });

    $('#reviews').on('keyup', '.review_input', function(){
        setReviewChars($(this));
    }).on('rated', '.rateit', onRated).on('click', '.review_edit', function(){

        var el = $(this);
        var review = el.closest('.review');

        $.ajax(el.attr('href') || el.data('href'), {

            beforeSend: function(){
                review.find('.review_loading').fadeIn();
            },

            success: function(data, textStatus) {
                review.html(data);
            },

            complete: rxg.updateReviewFeatures
        });

        return false;

    }).on('click', '.review_delete', function(){

        if (!confirm('Are you sure you want to delete this review?')) {
            return false;
        }

        var el = $(this);
        var review = el.closest('.review');

        $.ajax(el.attr('href') || el.data('href'), {

            type: 'post',
            beforeSend: function(){
                review.find('.review_loading').fadeIn();
            },

            success: function(data, textStatus) {
                if (data) {
                    review.html(data);
                } else {
                    review.remove();
                }
            },

            complete: rxg.updateReviewFeatures
        });

        return false;

    }).on('click', '.review_submit', function(){

        var el = $(this);
        var review = el.closest('.review');

        $.ajax(el.closest('form').attr('action'), {

            type: 'post',
            data: el.closest('form').serialize(),

            beforeSend: function(){
                if (review.find('.review_input').first().val() == '') {
                    return false;
                }
                if (review.find('.rateit').first().rateit('value') == 0) {
                    alert('Please rate the item before submitting your review.');
                    return false;
                }
                review.find('.review_loading').first().fadeIn();
            },

            success: function(data, textStatus) {
                review.html(data);
            },

            complete: rxg.updateReviewFeatures
        });

        return false;

    }).on('click', '.review_cancel', function(){

        var el = $(this);
        var review = el.closest('.review');

        $.ajax(el.attr('href') || el.data('href'), {

            beforeSend: function(){
                review.find('.review_loading').fadeIn();
            },

            success: function(data, textStatus) {
                review.html(data);
            },

            complete: rxg.updateReviewFeatures
        });
    });


    window.rxg = window.rxg || {};

    window.rxg.updateReviewFeatures = function() {

        //Re-initialize all rating features
        $('#reviews').find('.rateit').rateit();

        //Refresh review character counts
        $.each($('.review_input'), function(index, el){
            setReviewChars($(el));
        });
    };

    rxg.updateReviewFeatures();

})(jQuery);