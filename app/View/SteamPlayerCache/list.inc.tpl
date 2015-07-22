
{% set loader = 'cache_page_loading' %}

{{ paginator.options({
    'update': '#cache_content',
    'url': pageLocation,
    'before': js.get('#' ~ loader).effect('fadeIn'),
    'complete': "rxg.scrollTo($('#cache_content'), 250)"
}) }}

{% if cache %}

    <p class="list_total">{{ paginator.counter('{:count}')|number_format }} players in cache</p>

    <ul class="player_list striped">

        <li class="list_heading">
            <div class="cache_time">Date Cached</div>
            Player
        </li>

        {% for player in cache %}

            <li class="player_list_item cache_entry">
                {% include 'SteamPlayerCache/single.inc.tpl' %}
            </li>

        {% endfor %}

    </ul>

{% else %}

    <p><strong>The cache is currently empty!</strong></p>

{% endif %}

{% include 'Common/pagination.inc.tpl' %}