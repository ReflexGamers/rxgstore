(function($){

	$('#permissions_sync').on('click', function(){

		if (!confirm('Are you sure you want to synchronize admin/member permissions with the Sourcebans and forum databases?')) {
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

})(jQuery);