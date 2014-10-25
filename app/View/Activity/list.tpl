{% extends 'Common/base.tpl' %}

{% if not isAjax %}
    {% if not title %}
        {% if user_id %}
            {% set title = players[user_id].name ~ '\'s Activity' %}
        {% elseif item %}
            {% set title = item.name ~ ' Activity' %}
        {% else %}
            {% set title = 'Store Activity' %}
        {% endif %}
    {% endif %}

    {% set jquery = true %}
    {% set styles = ['rateit'] %}
    {% set scripts = ['jquery.rateit.min', 'items', 'common'] %}
{% endif %}

{% block content %}

    {% if isAjax %}

        <div id="activity_content">
            {% include 'Activity/list.inc.tpl' %}
        </div>

    {% else %}

        <section id="activity">

            <h1 class="page_heading">{{ user_id ? fn.memberTag(players[user_id]) }}{{ title }}</h1>

            <div id="activity_content">
                {% include 'Activity/list.inc.tpl' %}
            </div>

        </section>

    {% endif %}

{% endblock %}
