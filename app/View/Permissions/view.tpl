{% extends 'Common/base.tpl' %}

{% set title = 'Admin Permissions' %}
{% set scripts = 'admin-perms' %}

{% block content %}

    <p>This Store synchronizes with our Sourcebans database to provide group-based admin permissions for things like moderating comments/reviews, editing items and updating stock. The Forum database is also used to obtain division tags and to identify other Members.</p>

    <p>Synchronization should happen automatically twice daily, and high level admins will see a button on this page to manually perform a sync.</p>

    {% if access.check('Debug') %}
        <p>You can edit permission overrides in the permissions.php config. They are automatically applied during every sync and will override anything coming from the other databases. Pressing rebuild will dump all permissions and re-create everything from scratch, then perform a sync.</p>
        <p>You can also run those two functions from command line in the store folder with <code>./cake permissions sync</code> and <code>./cake permissions rebuild</code>.</p>
    {% endif %}

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

            {% if access.check('Debug') %}
                <input type="button" id="permissions_rebuild" class="btn-danger" value="Rebuild Now" data-href="{{ html.url({'action': 'rebuild'}) }}" />
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