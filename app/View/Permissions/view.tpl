{% extends 'Common/base.tpl' %}

{% set title = 'Admin Permissions' %}
{% set scripts = 'admin-perms' %}

{% block content %}

    <h1 class="page_heading">{{ title }}</h1>

    <p>This Store synchronizes with our Sourcebans database to provide group-based admin permissions for things like moderating comments/reviews, editing items and updating stock. The Forum database is also used to obtain division tags and to identify other Members.</p>

    <p>Synchronization should happen automatically twice daily, and depending on your permissions, you may see a button on this page to manually perform a sync.</p>

    <p>{{ html.link('Click here', {
            'controller': 'Admin',
            'action': 'viewlog',
            'name': 'permsync'
        }) }} to view the sync log.</p>

    {% if members %}
        <div class="permissions_batch_actions">
            {% if access.check('Permissions', 'update') %}
                <input type="button" id="permissions_sync" class="btn-primary" value="Synchronize Now" data-href="{{ html.url({'action': 'synchronize'}) }}" />
            {% endif %}

            {{ html.image('misc/ajax-loader.gif', {
                'class': 'ajax-loader',
                'id': 'permissions_loading'
            }) }}
        </div>
    {% endif %}


    <div id="permissions_data">
        {% include 'Permissions/list.inc.tpl' %}
    </div>

{% endblock %}