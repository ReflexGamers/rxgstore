<div class="pagination_links">

	{{ paginator.options({
		'before': js.get('#pagination_loader_img').effect('fadeIn'),
		'complete': 'rxg.onActivityLoad()'
	}) }}

	<div class="pagination_loader">
		{{ html.image(
			'misc/ajax-loader.gif',
			{'class': 'ajax-loader', 'id': 'pagination_loader_img'}
		) }}
	</div>

	{{ paginator.prev('< Newer', {'class': 'page_newer'}, null, {'class': 'hidden'}) }}
	{{ paginator.next('Older >', {'class': 'page_older'}, null, {'class': 'hidden'}) }}
	<div class="clear"></div>
	<div class="pagination_bounds">
		{{ paginator.first('<< Newest', {'class': 'page_newest'}) }}
		{{ paginator.last('Oldest >>', {'class': 'page_oldest'}) }}
	</div>
	<div class="pagination_numbers">
		{{ paginator.numbers({
			'modulus': 6,
			'currentClass': 'page_current'
		}) }}
	</div>
	<div class="clear"></div>
</div>

{{ js.writeBuffer() }}