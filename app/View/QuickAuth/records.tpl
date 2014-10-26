{% extends 'Common/base.tpl' %}

{% block content %}

    {% if isAjax %}

        <div id="quickauth_content">
            {% include 'QuickAuth/list.inc.tpl' %}
        </div>

    {% else %}

        <h1 class="page_heading">QuickAuth Records</h1>

        <div id="quickauth_content">
            {% include 'QuickAuth/list.inc.tpl' %}
        </div>

    {% endif %}

{% endblock %}
