{% extends 'Common/base.tpl' %}

{% set title = 'Receive a Shipment' %}

{% block content %}

    <p>Below, you may receive a shipment to add items to stock, but you probably will not need to most of the time due to automatic stocking which happens throughout the week by robots.</p>

    <p>Note that due to warehousing limitations, we are only able to store a certain amount of each item. If you attempt to stock more items than we can hold, only as many items as we can fit will be stocked.</p>

    <p>When an item has at least the "ideal" amount in stock, the item's listing page will show as simply "In Stock" and not list the exact number available.</p>

    <p>On this page: Items with less than 1/3 of maximum stock will show in <span class="item_stock_current stock_warning">orange</span>. Items with less than a 1/6 of maximum stock will show in <span class="item_stock_current stock_danger">red</span>.</p>

    <h2>How Automatic Stocking Works</h2>

    <p>The automatic stocking process examines recent sales to predict how many items will be needed in the immediate future for a given time period. It then stocks an additional <strong>{{ (config.OverStockMult - 1) * 100 }}%</strong> (configurable) just in case.</p>

    <p>The maximum stock is also adjusted to be <strong>{{ (config.MaxStockMult - 1) * 100 }}%</strong> more (configurable) than the amount stocked so that leadership can stock more items if they see fit.</p>

    <p>If an item's current stock is greater than <strong>{{ config.AntiMicroThreshold * 100 }}%</strong> (configurable) of the proposed stock, it is considered sufficiently stocked so no items will be added to prevent 'micro stocking', unless the current stock is less than the minimum which will cause the micro stocking check to be ignored.</p>

    {% if access.check('Debug') %}
        <p>You can configure the AutoStock parameters in the rxgstore.php config.</p>

        <h2>Command Line Interface</h2>

        <p>AutoStocking is done through the command line so it can be done via a cron job. The CLI provides the following functions:</p>

        <p><code>./cake stock suggested</code> - Prints how many items should be stocked based on sales only, including OverStockMult.</p>

        <p><code>./cake stock autoPreview</code> - Prints detailed view of how many items will be stocked if you perform autoStock, factoring in OverStockMult, AntiMicroThreshold and minimums. This also shows the proposed maximums.</p>

        <p><code>./cake stock autoStock</code> - Performs autoStock, submits a shipment and prints the quantity of each item that was stocked to the console. The cron job should use this.</p>

        <p>You may also pass a number at the end of each of those functions to specify how many days for which it should determine the stocking data. The default is 7 days or <code>./cake stock autoPreview 7</code>.</p>
    {% endif %}

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
                        <span class="item_stock_current {{ (item.quantity < item.maximum / 6 )? 'stock_danger' : (item.quantity < item.maximum / 3) ? 'stock_warning' : '' }}">{{ item.quantity }}</span> / {{ item.maximum }}
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