(function($){

	$('.item_browse_gift_accept').on('click', function(){

		var el = $(this);
		var gift = el.closest('.item_browse_gift');
		var inventory = $('#item_browse_inventory_content');
		var loader = gift.find('.item_browse_gift_loading').first();

		var item_ids = [];

		$.each(gift.find('.item_list_entry[data-item_id]'), function(index, element){
			var el = $(this);
			item_ids.push(el.data('item_id'));
		});

		$.ajax(el.data('href'), {

			type: 'post',

			beforeSend: function(){
				loader.fadeIn();
			},

			success: function(data, textStatus){
				loader.fadeOut();
				gift.fadeTo(1000, 0, function(){

					$('html, body').animate({scrollTop: $('.item_browse_inventory').first().offset().top}, 750);

					$(this).slideUp(1000, function(){

						$(this).remove();
						inventory.html(data);

						for (var i = 0; i < item_ids.length; i++) {

							var item = inventory.find('.item_list_entry[data-item_id=' + item_ids[i] + ']').first().addClass('updating');

							(function(el){
								setTimeout(function(){
									el.removeClass('updating');
								}, 0);
							})(item);
						}
					});
				});
			}
		});

		return false;
	});


	var childServers = $('.server_select').first().data('child-servers').split(',');
	var parentServer;

	$.each($('#server_select_menu option'), function(index, element){

		var el = $(this);
		var server = el.val();

		if ($.inArray(server, childServers) == -1) {
			el.data('imagesrc', '/store2/img/servers/' + server + '.png');
			//parentServer = server;
		} else {
			el.data('imagesrc', '/store2/img/servers/' + server + '.png');
		}

	});

	var initializing = true;
	$('#server_select_menu').ddslick({

		onSelected: function(data){

			//Dumb bug where it calls onSelected on initialization
			if (initializing) return;

			var url = $('#browse_item_list').data('href');
			var val = data.selectedData.value;

			$.ajax(url + '/' + val, {

				beforeSend: function(){
					$('#server_select_loading').fadeIn();
				},

				success: function(data, textStatus){
					$('#server_select_loading').fadeOut();
					$('#browse_item_list').html(data);
				},

				complete: function(){
					$('#browse_item_list').find('.rateit').rateit();
				}
			});
		}
	});
	initializing = false;


	$.each($('.dd-option'), function(index, element){

		var el = $(this);
		var server = el.find('.dd-option-value').first().val();

		if ($.inArray(server, childServers) != -1) {
			el.addClass('server_select_child');
		}

	});

})(jQuery);