{% extends 'Common/base.tpl' %}

{% set title = item.name %}
{% set styles = 'rateit' %}
{% set scripts = ['jquery.rateit.min', 'items'] %}

{% block preheader %}

    {% if access.check('Items', 'update') %}
        <div class="item_edit_link">
            <i class="fa fa-pencil"></i>
            {{ html.link('edit', {
                'action': 'edit',
                'id': item.short_name
            }) }}
        </div>
    {% endif %}

{% endblock %}

{% block content %}

    <div id="flash_container"></div>


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
                {% if not inStock %}
                    <i class="fa fa-ban"></i> Out of Stock
                {% elseif stock.quantity < stock.ideal_quantity %}
                    {{stock.quantity}} In Stock
                {% else %}
                    In Stock
                {% endif %}
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


    {% include 'Common/reviews.inc.tpl' with {
        'condition': reviews or userCanRate,
        'title': 'Customer Reviews',
        'description': 'The number next to each review is the quantity of this item the reviewer purchased. Want your review to be seen first? Buy more of this item to push it to the top!'
    } %}


    {% if topBuyers %}

        <section id="top_buyers">

            <h2 class="page_subheading">Top Buyers</h2>

            <ul class="top_player_list">

                {% for buyer, data in topBuyers %}

                    <li>
                        <span class="top_player_name">
                            {{ fn.player(_context, players[buyer]) }}
                        </span>
                        bought <span class="top_player_amount">{{ data.quantity }}</span> for <span class="top_player_cost">{{ fn.currency(data.total, {'big': true}) }}</span>
                    </li>

                {% endfor %}

            </ul>

        </section>

    {% endif %}


    {% if topHoarders %}

        <section id="top_hoarders">

            <h2 class="page_subheading">Top Hoarders</h2>

            <ul class="top_player_list">

                {% for buyer, data in topHoarders %}

                    <li>
                        <span class="top_player_name">
                            {{ fn.player(_context, players[buyer]) }}
                        </span>
                        is hoarding <span class="top_player_amount">{{ data.quantity }}</span>
                    </li>

                {% endfor %}

            </ul>

        </section>

    {% endif %}


    {% include 'Common/activity.inc.tpl' with {
        'title': 'Recent Activity'
    } %}

{% endblock %}