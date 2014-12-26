{% extends 'Common/base.tpl' %}

{% set title = 'QuickAuth Records' %}

{% block content %}

    {% if isAjax %}

        <div id="quickauth_content">
            {% include 'QuickAuth/list.inc.tpl' %}
        </div>

    {% else %}

        <div id="quickauth_content">
            {% include 'QuickAuth/list.inc.tpl' %}
        </div>

    {% endif %}

{% endblock %}
