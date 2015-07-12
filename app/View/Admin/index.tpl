{% extends 'Common/base.tpl' %}

{% set title = 'Admin Control Panel' %}

{% block content %}

    <p>This is the admin control panel where you can do special things.</p>

    <p>{{ html.link('Click here', {'controller': 'Admin', 'action': 'viewlog', 'name': 'admin'}) }} to view the admin log.</p>

    <ul class="business_list">
        <li>{{ html.link('Search for Players', {'controller': 'SteamPlayerCache', 'action': 'search'}) }}</li>
        {% if access.check('Giveaways', 'read') %}
            <li>{{ html.link('Givewaways', {'controller': 'Giveaways', 'action': 'index'}) }}</li>
        {% endif %}
        {% if access.check('Rewards', 'create') %}
            <li>{{ html.link('Send a Reward', {'controller': 'Rewards', 'action': 'compose'}) }}</li>
        {% endif %}
        {% if access.check('Shipments', 'create') %}
            <li>{{ html.link('Stock', {'controller': 'Shipments', 'action': 'edit'}) }}</li>
        {% endif %}
        {% if access.check('Items', 'update') %}
            <li>{{ html.link('Item Display Order', {'controller': 'Items', 'action': 'sort'}) }}</li>
        {% endif %}
        {% if access.check('Stats', 'read') %}
            <li>{{ html.link('Secret Stats', {'controller': 'Admin', 'action': 'stats'}) }}</li>
        {% endif %}
        {% if access.check('Permissions', 'read') %}
            <li>{{ html.link('Permissions', {'controller': 'Permissions', 'action': 'view'}) }}</li>
        {% endif %}
        {% if access.check('Cache', 'read') %}
            <li>{{ html.link('Steam Cache', {'controller': 'SteamPlayerCache', 'action': 'view'}) }}</li>
        {% endif %}
        {% if access.check('QuickAuth', 'read') %}
            <li>{{ html.link('QuickAuth', {'controller': 'QuickAuth', 'action': 'view'}) }}</li>
        {% endif %}
    </ul>

{% endblock %}