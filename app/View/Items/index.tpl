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

            <div class="browse_howto">
                <a class="browse_howto_link">How do I use items?</a>
                <div class="browse_howto_content">
                    <h3 class="browse_howto_header">Easy Way (in chat)</h3>
                    <p class="browse_howto_method">
                        Type <code>!useitem</code> in chat and select the item from the menu.
                        <br>
                        You can also use a specific item by typing <code>!useitem NAME</code>.
                        <br>
                        Example: <code>!useitem cookie</code> will use a {{ html.link('cookie', {'controller': 'Items', 'action': 'view', 'id': 'cookie'}) }}.
                    </p>
                    <h3 class="browse_howto_header">Better Way (key bind)</h3>
                    <p class="browse_howto_method">
                        First, enable the developer console in settings so that you can bind keys to custom actions. Then, enter <code>bind KEY useitem</code> into the console (and press enter) to bind that key to open the item menu.
                        <br>
                        Example: <code>bind f useitem</code> will bind the <code>f</code> key to open it.
                        <br><br>
                        Bind a specific item to a key with <code>bind KEY "useitem NAME"</code> which skips the menu and instantly uses the item.
                        <br>
                        Example: <code>bind f "useitem cookie"</code> will bind <code>f</code> to the {{ html.link('cookie', {'controller': 'Items', 'action': 'view', 'id': 'cookie'}) }}.
                        <br><br>
                        At the top of each item's listing page, you will see a value for <em>"in-game usage"</em>. That is the <code>NAME</code> you must use to bind the item directly.
                    </p>
                </div>
            </div>

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