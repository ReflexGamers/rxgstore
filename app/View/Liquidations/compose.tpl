{% extends 'Common/base.tpl' %}

{% set title = 'Return Items' %}
{% if composing %}
    {% set scripts = 'liquidate' %}
{% endif %}

{% block content %}

    {% if composing %}

        <p>Here you may returns items from your inventory in exchange for RXG Cash based on their current market value.</p>

        {% if userItems is empty %}
            <p>You don't have any items to return. {{ html.link('Go buy some!', {'controller': 'Items', 'action': 'index'}) }}</p>
        {% endif %}

    {% else %}

        <div class="back_link gift_backlink">
            <i class="fa fa-arrow-circle-left"></i>
            {{ html.link('Edit Details', ({
                'controller': 'Liquidations',
                'action': 'compose'
            })) }}
        </div>

    {% endif %}

    {% if userItems is not empty %}

        {{ form.create('LiquidationDetail', {
            'inputDefaults': {
                'label': false,
                'div': false,
                'required': false
            },
            'url': {
                'controller': 'Liquidations',
                'action': composing ? 'preview' : 'submit'
            },
            'id': 'LiquidateForm',
            'class': 'cf'
        }) }}

        <table class="liquidate_table">
            <thead>
            <tr>
                <th></th>
                <th class="liquidate_item_heading">Item</th>
                <th>You Have</th>
                <th>Qty</th>
                <th>Totals</th>
            </tr>
            </thead>
            <tbody>
            {% set total = 0 %}

            {% for item in sortedItems if (
                composing ?
                    (userItems[item.item_id] > 0) :
                    (details[item.item_id] > 0)
            ) %}

                {% set userQuantity = userItems[item.item_id] %}
                {% set quantity = details[item.item_id] %}

                {% set itemTotal = quantity * item.price %}
                {% set total = total + itemTotal %}

                <tr>
                    <td class="liquidate_image">
                        {{ html.image("items/#{item.short_name}.png", {
                            'url': {'controller': 'Items', 'action': 'view', 'id': item.short_name}
                        }) }}
                    </td>
                    <td class="liquidate_name">
                        {{ html.link(item.name, {'controller': 'Items', 'action': 'view', 'id': item.short_name}) }}
                    </td>
                    <td class="liquidate_available">
                        {{ userQuantity }}
                    </td>
                    <td class="liquidate_quantity">
                        {% if composing %}
                            {{ form.hidden(loop.index0 ~ '.item_id', {'value': item.item_id}) }}
                                {{ form.input(loop.index0 ~ '.quantity', {
                                    'label': false,
                                    'div': false,
                                    'class': 'liquidate_quantity_input',
                                    'data-price': item.price,
                                    'min': 0,
                                    'max': userQuantity,
                                    'value': quantity,
                                    'tabIndex': loop.index
                            }) }}
                        {% else %}
                            {{ quantity }}
                        {% endif %}
                    </td>
                    <td class="liquidate_item_total">{{ fn.currency(itemTotal, {'wrap': true}) }}</td>
                </tr>

            {% endfor %}

            <tr class="liquidate_separator">
                <td colspan="2"></td>
                <td colspan="2" class="liquidate_totals">You Receive</td>
                <td colspan="1" id="liquidate_total">{{ fn.currency(total, {'wrap': true}) }}</td>
            </tr>

            </tbody>
        </table>

        {{ form.submit(composing ? 'Preview Return' : 'Confirm Return', {
            'div': false,
            'class': 'btn-primary ' ~ ((details is empty) ? 'disabled' : ''),
            'id': 'liquidate_confirm_button',
            'disabled': (details is empty)
        }) }}

        {{ form.end() }}

    {% endif %}

    {% include 'Common/activity.inc.tpl' with {
        'title': 'Recent Returns'
    } %}

{% endblock %}
