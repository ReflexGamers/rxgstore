{% extends 'Common/base.tpl' %}

{% set title = 'View Receipt' %}
{% set backLink = {'controller': 'Users', 'action': 'profile', 'id': steam.id64(data.Order.user_id)} %}

{% block content %}

    {% set flash = flash.render() %}

    {% if flash %}

        <p>{{ flash }}</p>

    {% else %}

        {% include 'Orders/receipt.inc.tpl' with {'data': data} %}

    {% endif %}

{% endblock %}