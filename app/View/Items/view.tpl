{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = item.name %}
{% set hideTitle = true %}
{% set headerImage = false %}

{% set styles = ['rateit'] %}
{% set scripts = ['jquery.rateit.min', 'items', 'common'] %}


{% block content %}

    {% if access.check('Items', 'update') %}
        <div class="item_edit_link">
            <i class="icon-pencil"></i>
            {{ html.link('edit',
                {'action': 'edit', 'id': item.short_name}
            ) }}
        </div>
    {% endif %}

    <h1 class="page_heading">{{ item.name }}</h1>

    <div id="flash_container"></div>

    {% include '/ShoutboxMessages/shoutbox.inc.tpl' %}


    <section id="item_details">

        <div id="item_actions">

            {% set inStock = stock and stock.quantity > 0 %}

            {% if user and inStock and item.buyable %}
                {{ form.create('Cart', {
                    'url': {
                        'controller': 'Cart',
                        'action': 'add',
                        'id': item.item_id
                    },
                    'id': 'CartForm',
                    'novalidate': 'novalidate'
                }) }}
                {{ html.image(
                    'misc/ajax-loader.gif',
                    {'class': 'ajax-loader', 'id': 'cart_add_loading'}
                ) }}
                {{ form.input('quantity', {
                    'type': 'number',
                    'id': 'cart_add_qty',
                    'maxlength': 3,
                    'value': 1,
                    'label': false,
                    'div': false,
                    'min': 1,
                    'max': stock.quantity
                }) -}}
                {{ form.end({
                    'label': 'Add to cart',
                    'id': 'cart_add',
                    'data-href': html.url({
                        'controller': 'Cart',
                        'action': 'add',
                        'id': item.item_id
                    }),
                    'div': false
                }) }}
            {% endif %}

            <div id="rating">
                {% include 'Ratings/rate.inc.tpl' %}
            </div>

            <div class="item_stock {{ inStock ? '' : 'out' }}">
                {{ not inStock ? 'Out of Stock' : stock.quantity < stock.ideal_quantity ? "#{stock.quantity} In Stock" : 'In Stock' }}
            </div>

        </div>

        {{ html.image("items/#{item.short_name}.png") }}

        <p class="item_detail">
            <label class="item_detail_label">Price:</label>
            <span class="item_detail_value">{{ fn.currency(item.price, {'big': true}) }}</span>
        </p>

        <p class="item_detail">
            <label class="item_detail_label">In-game Usage:</label>
            <span class="item_detail_value">"{{ item.short_name }}"</span>
        </p>

        <p class="item_detail">
            <label class="item_detail_label">Supported servers:</label>
            <ul class="item_detail_servers">
                {% if servers %}
                    {% for server in servers %}
                        <li>{{ html.image("servers/#{server.short_name}.png") }} {{ server.name }}</li>
                    {% endfor %}
                {% else %}
                    <li>{{ html.image("servers/none.png") }} None (yet)</li>
                {% endif %}
            </ul>
        </p>

        {% if features %}
            <p class="item_detail">
                <label class="item_detail_label">Features:</label>
                <ul class="item_detail_features">
                    {% for feature in features %}
                        <li>{{ feature.description }}</li>
                    {% endfor %}
                </ul>
            </p>
        {% endif %}


        <div class="item_description">
            <label class="item_detail_label">Item Description:</label>
            {{ item.description }}
        </div>

    </section>


    {% if reviews or userCanRate %}

        <section id="reviews">

            <h2 class="page_subheading">Customer Reviews</h2>

            <div id="reviews_content">
                {% include 'Reviews/list.inc.tpl' %}
            </div>

        </section>

    {% endif %}


    {% if topBuyers %}

        <section id="top_buyers">

            <h2 class="page_subheading">Top Buyers</h2>

            <ul class="top_buyer_list">

                {% for buyer, data in topBuyers %}

                    <li>
                        <span class="top_buyer_name">
                            {{ fn.player(_context, players[buyer]) }}
                        </span>
                        bought <span class="top_buyer_amount">{{ data.quantity }}</span> for <span class="top_buyer_cost">{{ fn.currency(data.total, {'big': true}) }}</span>
                    </li>

                {% endfor %}

            </ul>

        </section>

    {% endif %}


    {% include 'Common/activity.inc.tpl' with {
        'title': 'Recent Activity'
    } %}

{% endblock %}