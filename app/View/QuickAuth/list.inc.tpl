
{% set loader = 'quickauth_page_loading' %}

{{ paginator.options({
    'update': '#quickauth_content',
    'url': pageLocation,
    'before': js.get('#' ~ loader).effect('fadeIn'),
    'complete': "rxg.scrollTo($('#quickauth_content'), 250)"
}) }}

{% if quickauth %}

    <p class="list_total">{{ paginator.counter('{:count}') }} total records</p>

    <table class="player_list striped quickauth_list">

        <tr>
            <th>Player</th>
            <th>Server</th>
            <th>Date</th>
        </tr>

        {% for entry in quickauth %}

            <tr class="player_list_item quickauth_entry">
                {% include 'QuickAuth/single.inc.tpl' %}
            </tr>

        {% endfor %}

    </table>

{% else %}

    <p>No records yet!</p>

{% endif %}

{% include 'Common/pagination.inc.tpl' %}