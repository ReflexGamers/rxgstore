{% extends 'Common/base.tpl' %}

{% set title = 'Search Results' %}

{% if not isAjax %}
    {% set scripts = 'common' %}
{% endif %}

{% block content %}

    <section id="search_results">
        {% include '/SteamPlayerCache/results.inc.tpl' %}
    </section>

{% endblock %}
