{% import 'Common/functions.tpl' as fn %}

{% set loader = 'results_page_loading' %}

{{ paginator.options({
    'update': '#search_results',
    'url': pageLocation,
    'before': js.get('#' ~ loader).effect('fadeIn'),
    'complete': "rxg.scrollTo($('#search_results'), 250)"
}) }}

{% if term %}

    {% if results %}

        {% set resultCount = paginator.counter('{:count}') %}

        <p class="list_total">Found {{ paginator.counter('{:count}') }} result{{ resultCount > 1 ? 's' : '' }} for '{{ term }}'</p>

    {% else %}

        <p class="list_total">No results found for '{{ term }}'</p>

    {% endif %}

    <ul class="player_list striped">

        {% for result in results %}

            {% set player = players[result] %}

            <li class="player_list_item">
                {{ fn.player(_context, player) }}
                {% if result != user.user_id %}
                    <span class="search_result_send_gift">
                            {{ html.link('send gift', {
                                'controller': 'Gifts',
                                'action': 'compose',
                                id: player.steamid
                            }) }}
                        <i class="fa fa-gift icon_gift"></i>
                    </span>
                {% endif %}
            </li>

        {% endfor %}

    </ul>

    {% include 'SteamPlayerCache/pagination.inc.tpl' %}

{% endif %}
