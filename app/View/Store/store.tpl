{% extends 'Common/base.tpl' %}

{% set title = 'RXGMART' %}
{% set headerImage = 'pokemart.png' %}

{% block content %}

    {% if not isAjax %}

        {% if useritem %}

            {% include 'Items/list.inc.tpl' with {
                'quantity': useritem,
                'maxColumns': 5
            } %}

        {% else %}

            <p>You currently have no items! Buy some below.</p>

        {% endif %}

        {% if user %}

            <p style="font-size:24px">Gold: {{ fn.currency(credit) }}</p>

        {% endif %}

        <hr>

        {% set ajax_loader_fadeIn = js.get('#ajax-loader').effect('fadeIn') %}
        {% set ajax_loader_fadeOut = js.get('#ajax-loader').effect('fadeOut') %}

        {{ js.get('#server_select_menu').event('change', js.request(
            {'action': 'store'},
            {
                'method': 'post',
                'async': true,
                'update': '#store_items',
                'dataExpression': true,
                'data': js.serializeForm({'inline': true}),
                'before': ajax_loader_fadeIn,
                'complete': ajax_loader_fadeOut
            }
        )) }}

        <div class="server_select">

            {{ form.create('server', {'url': {
                'controller': 'store',
                'action': 'store'
            }}) }}

            Filter items by game server:
            {{ form.select('short_name', {'all': 'All Servers'}|merge(servers), {
                'class': 'server_select_options',
                'id': 'server_select_menu',
                'value': game,
                'empty': false
            }) }}

            {{ form.end({
                'label': 'Filter',
                'div': false,
                'id': 'btnServerFilter'
            }) }}

            {{ js.buffer(js.get('#btnServerFilter').effect('hide')) }}

            {{ html.image(
                'misc/ajax-loader.gif',
                {'class': 'ajax-loader', 'id': 'ajax-loader'}
            ) }}

        </div>


    {% endif %}

    <div id="store_items">

        {% if user %}
            {{ form.create('checkout', {'url': {
                'controller': 'Orders',
                'action': 'checkout'
            } }) }}
        {% endif %}

        <table class="buylist">
            <tr style="text-align:center">
                <td></td>
                <th style="text-align:left">item</th><th>stock</th><th>price</th><th>quantity</th>
            </tr>
            {% for item in gameItems %}
                <tr>
                    <td class="image">{{ html.image("items/#{item.short_name}.png") }}<!--onclick="showitem(' .$item['id'] .')--></td>
                    <td class="desc">{{ item.name }}</td>
                    <td class="stock">{{ stock[item.item_id] }}</td>
                    <td class="prices">{{ fn.currency(item.price) }}</td>
                    <td class="itemamount">
                        <input data-price="{{ item.price }}" class="itemamount addup" autocomplete="off" type="text" name="{{ item.item_id }}" value="" />
                    </td>
                </tr>
            {% endfor %}

            <tr><td></td><td>Subtotal</td><td></td><td class="prices" id="cart_subtotal">--</td><td></td></tr>
            <tr><td></td><td>Shipping</td><td></td><td class="prices" id="cart_shipping">FREE</td><td></td></tr>
            <tr><td></td><td>Total</td><td></td><td class="prices" id="cart_total">--</td><td></td></tr>
        </table>

        {% if user %}
            {{ html.image('misc/shoppingcart.png', {'class': 'cartimg'}) }}
            {{ form.end({
                'label': 'Checkout',
                'div': false
            }) }}
        {% else %}
            <p>Please sign in through steam to buy something.</p>
        {% endif %}

    </div>

{% endblock %}