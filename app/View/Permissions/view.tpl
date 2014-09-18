{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'Admin Permissions' %}
{% set hideTitle = true %}
{% set headerImage = false %}

{% set scripts = ['admin-perms'] %}


{% block content %}

	<h1 class="page_heading">Admin Permissions</h1>

	<p>This Store synchronizes with our Sourcebans database to provide group-based admin permissions for things like moderating comments/reviews, editing items and updating stock. The Forum database is also used to obtain division tags and to identify other Members.</p>

	<p>Synchronization should happen automatically twice daily, and depending on your permissions, you may see a button on this page to manually perform a sync.</p>

	<p>{{ html.link('Click here', {
			'controller': 'permissions',
			'action': 'viewlog'
		}) }} to view the sync log.</p>

	{% if members %}
		<div class="admin_batch_actions">
			{% if access.check('Permissions', 'update') %}
				<input type="button" id="permissions_sync" class="btn-primary" value="Synchronize Now" data-href="{{ html.url({'action': 'synchronize'}) }}" />
			{% endif %}

			{{ html.image('misc/ajax-loader.gif', {
				'class': 'ajax-loader',
				'id': 'admin_loading'
			}) }}
		</div>
	{% endif %}


	<div id="admin_data">
		{% include 'Permissions/list.inc.tpl' %}
	</div>

{% endblock %}