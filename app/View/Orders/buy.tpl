{% extends 'Common/layout.tpl' %}

{% set title = 'Purchase Completed' %}
{% set backLink = {'controller': 'store', 'action': 'store'} %}

{% block content %}

    {% if order %}

        <div class="back_link">
            <i class="fa fa-arrow-circle-left"></i>
            {{ html.link('Continue Shopping',
                {'controller': 'Items', 'action': 'index'}
            ) }}
        </div>

        {% include 'Orders/receipt.inc.tpl' with {'data': order} %}

    {% endif %}

{% endblock %}