{% extends 'Common/base.tpl' %}

{% set title = 'Receive a Shipment' %}
{% set scripts = 'common' %}

{% block content %}

    <h1 class="page_heading">{{ title }}</h1>

    {{ session.flash() }}

    <p>Below, you may receive a shipment to add items to stock.</p>

    <p><strong>Please do not order shipments more than 1-2 times per week.</strong> Shipments are public information so we want to space them out somewhat evenly. Be sure to check the recent shipments at the bottom of this page before proceeding.</p>

    <p>Note that due to warehousing limitations, we are only able to store a certain amount of each item. If you attempt to stock more items than we can hold, it will not work.</p>

    <p>When an item has at least the "ideal" amount in stock, the item's listing page will show as simply "In Stock" and not list the number of items available.</p>

    <p>On this page: Items with less than "ideal" stock will show in orange. Items with less than half of "ideal" stock will show in red.</p>

    {{ form.create('Shipment', {
        'inputDefaults': {
            'label': false,
            'div': false,
            'required': false
        },
        'class': 'cf'
    }) }}

        <table class="item_stock_table">

            <tr>
                <th></th>
                <th class="item_stock_ideal">Ideal</th>
                <th>In Stock</th>
                <th>Quantity</th>
            </tr>

            {% for item in stock %}

                <tr>
                    <td class="item_stock_name">
                        {{ html.image("items/#{item.short_name}.png", {
                            'url': {'controller': 'Items', 'action': 'view', 'id': item.short_name},
                            'class': 'item_stock_image'
                        }) }}
                        {{ html.link(item.name, {'controller': 'Items', 'action': 'view', 'id': item.short_name}) }}
                    </td>
                    <td class="item_stock_ideal">
                        {{ item.ideal_quantity }}
                    </td>
                    <td class="item_stock_quantity">
                        <span class="item_stock_current {{ (item.quantity < item.ideal_quantity / 2 )? 'stock_danger' : (item.quantity < item.ideal_quantity) ? 'stock_warning' : '' }}">{{ item.quantity }}</span> / {{ item.maximum }}
                    </td>
                    <td class="item_stock_input">
                        {{ form.hidden(loop.index0 ~ '.item_id', {'value': item.item_id}) }}
                        {{ form.input(loop.index0 ~ '.quantity', {
                            'min': 0,
                            'max': item.maximum - item.quantity
                        }) }}
                    </td>
                </tr>

            {% endfor %}

        </table>

    {{ form.end({
        'label': 'Receive Shipment',
        'div': false,
        'class': 'btn-primary',
        'id': 'item_stock_button'
    }) }}

    {% include 'Common/activity.inc.tpl' with {
        'title': 'Recent Shipments'
    } %}

{% endblock %}