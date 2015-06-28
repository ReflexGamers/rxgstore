{% import 'Common/functions.tpl' as fn %}

<div class="item_browse_cash">You have: {{ fn.currency(credit, {'big': true}) }}</div>

{% if userItems is not empty %}

    {% include 'Items/list.inc.tpl' with {
        'quantity': userItems,
        'maxColumns': 7
    } %}

{% else %}

    <p>... and no items!</p>

{% endif %}