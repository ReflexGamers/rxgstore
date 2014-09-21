(function($){

	$('#item_sort').sortable({
		items: '> .item_sort_draggable',
		axis: 'y',
		containment: 'parent',
		cursor: 'move',
		opacity: 0.75,
		placeholder: 'item_sort_placeholder',
		update: function(event, ui){

			//var currentEl = $(ui.item);

			$.each($('.item_sort_draggable'), function(index, el){

				var el = $(el);
				var indexEl = el.find('.item_sort_index').first();
				indexEl.val(index);

				if (index == indexEl.data('original')) {
					el.removeClass('changed');
				} else {//else if (currentEl.is(el)) {
					el.addClass('changed');
				}
			});
		}
	});

})(jQuery);