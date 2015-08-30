{% extends 'Common/base.tpl' %}

{% set title = 'RXG Store' %}
{% set styles = 'rateit' %}
{% set scripts = ['jquery.ddslick.min', 'jquery.rateit.min', 'browse'] %}

{% block content %}

    {{ session.flash('quickauth') }}

    {% if user %}

        <div class="item_browse_inventory">
            {% include 'Items/browse_inventory.inc.tpl' with {
                userItems: userItems
            } %}
        </div>

        {% if userItems is not empty %}
            {% include 'Items/howto.inc.tpl' %}
        {% endif %}

        <div class="browse_addfunds">
            {{ html.link('Buy more CASH', {
                'controller': 'PaypalOrders',
                'action': 'addfunds'
            }, {
                'class': 'btn-primary btn-addfunds'
            }) }}
        </div>

        {% if gifts %}

            {% include 'Gifts/view.inc.tpl' %}

        {% endif %}

        {% if rewards %}

            {% include 'Gifts/view.inc.tpl' with {
                'gifts': rewards,
                'isReward': true
            } %}

        {% endif %}

        {% if giveaways %}

            {% include 'Giveaways/pending.inc.tpl' %}

        {% endif %}

    {% endif %}

    <div class="server_select" data-child-servers="{{ childServers }}">

        {{ form.select('short_name', {'all': 'All Items'}|merge(servers), {
            'class': 'server_select_options',
            'id': 'server_select_menu',
            'value': server,
            'empty': false,
            'div': false
        }) }}

        {{ html.image(
            'misc/ajax-loader.gif',
            {'class': 'ajax-loader', 'id': 'server_select_loading'}
        ) }}

    </div>

    <p class="browse_item_description">Click on an item below to view more info about it. From there, you can add it to your cart, as well as rate or review it after purchasing it.</p>

    <ul id="browse_item_list" class="cf" data-href="{{ html.url({'controller': 'Items', 'action': 'server'}) }}">
        {% include 'Items/server.tpl' %}
    </ul>

    {% include 'Common/activity.inc.tpl' with {
        'title': 'Recent Activity'
    } %}

{% endblock %}