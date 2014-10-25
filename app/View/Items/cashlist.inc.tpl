{% if not maxColumns or maxColumns > 6 %}
    {% set maxColumns = 6 %}
{% endif %}

{% set stacks = (amount / currencyMult / cashStackSize)|round(0, 'ceil') %}

{% set cols = stacks > maxColumns ? (stacks / 2)|round(0, 'ceil') : stacks %}
{% set cols = cols > maxColumns ? maxColumns : cols %}

<div class="item_list_group">

    <ul class="item_list">
        {% for i in 1..stacks %}
            <li class="item_list_entry cash" data-item_id="{{ item.item_id }}">
                <div class="item_pic" title="{{ 'CASH' }}">
                    {{ html.image("items/money.png", {
                        'url': {
                            'controller': 'PaypalOrders',
                            'action': 'addfunds'
                        }
                    }) }}
                </div>
            </li>
            {% if loop.index is divisible by(cols) and stacks > loop.index %}
                </ul>
                <ul class="item_list">
            {% endif %}
        {% endfor %}
    </ul>

</div>
