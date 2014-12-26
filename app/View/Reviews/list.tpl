{% extends 'Common/base.tpl' %}

{% if not isAjax %}
    {% set styles = ['rateit'] %}
    {% set scripts = ['jquery.rateit.min', 'items'] %}
{% endif %}

{% if user_id %}
    {% set player = players[user_id] %}
{% endif %}

{% if not title %}
    {% if user_id %}
        {% set title = player.name ~ '\'s Reviews' %}
    {% endif %}
{% endif %}

{% block title %}
    {% if user_id %}
        {{ fn.memberTag(player) }}
        {{ fn.stripTag(player)|e }}'s Reviews
    {% else %}
        {{ title }}
    {% endif %}
{% endblock %}

{% block content %}

    {% include 'Common/reviews.inc.tpl' %}

{% endblock %}
