(function($){

    function ajaxButton(selector, confirmText) {

        $(selector).on('click', function(){

            if (!confirm(confirmText)) {
                return false;
            }

            var el = $(this);

            $.ajax(el.attr('href') || el.data('href'), {

                type: 'post',
                beforeSend: function(){
                    $('#permissions_loading').fadeIn();
                },

                success: function(data, textStatus) {
                    $('#permissions_data').html(data);
                    $('#permissions_loading').fadeOut();
                }
            });

            return false;
        });
    }

    ajaxButton('#permissions_sync', 'Are you sure you want to synchronize admin/member permissions with the Sourcebans and forum databases?');

    ajaxButton('#permissions_rebuild', 'Are you sure you want to completely rebuild the admin/member permission tables?');

})(jQuery);