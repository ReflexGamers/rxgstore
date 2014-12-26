{% extends 'Common/base.tpl' %}

{% set title = 'Steam Player Cache' %}

{% if not isAjax %}
    {% set scripts = 'common' %}
{% endif %}

{% block content %}

    {% if isAjax %}

        <div id="cache_content">
            {% include 'SteamPlayerCache/list.inc.tpl' %}
        </div>

    {% else %}

        <div id="cache_content">
            {% include 'SteamPlayerCache/list.inc.tpl' %}
        </div>

    {% endif %}

{% endblock %}
