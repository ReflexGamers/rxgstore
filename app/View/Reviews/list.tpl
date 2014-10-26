{% extends 'Common/base.tpl' %}

{% if not isAjax %}
    {% if user_id %}
        {% set title = players[user_id].name ~ '\'s Reviews' %}
    {% elseif item %}
        {% set title = item.name ~ ' Reviews' %}
    {% else %}
        {% set title = 'Reviews' %}
    {% endif %}

    {% set jquery = true %}
    {% set styles = ['rateit'] %}
    {% set scripts = ['jquery.rateit.min', 'items'] %}
{% endif %}

{% block content %}

    {% if isAjax %}

        <div id="reviews_content">
            {% include 'Reviews/list.inc.tpl' %}
        </div>

    {% else %}

        <section id="reviews">

            <h1 class="page_heading">{{ user_id ? fn.memberTag(players[user_id]) }}{{ title }}</h1>

            <div id="reviews_content">
                {% include 'Reviews/list.inc.tpl' %}
            </div>

        </section>

    {% endif %}

{% endblock %}
