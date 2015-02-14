<div class="pagination_links cf">

    <div class="pagination_loader">
        {{ html.image('misc/ajax-loader.gif', {
            'class': 'ajax-loader',
            'id': loader
        }) }}
    </div>

    <div class="pagination_nextprev">
        {{ paginator.prev('< Next', {'class': 'page_newer', 'model': pageModel}, null, {'class': 'hidden'}) }}
        {{ paginator.next('Previous >', {'class': 'page_older', 'model': pageModel}, null, {'class': 'hidden'}) }}
    </div>

    <div class="pagination_numbers">
        {{ paginator.numbers({
            'modulus': 6,
            'currentClass': 'page_current',
            'model': pageModel
        }) }}
    </div>
</div>

{{ js.writeBuffer() }}