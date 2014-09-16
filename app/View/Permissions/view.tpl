{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'Admin Permissions' %}
{% set hideTitle = true %}
{% set headerImage = false %}

{% set scripts = ['admin-perms'] %}


{% block content %}

	<h1 class="page_heading">Admin Permissions</h1>

	<p>This site synchronizes with Sourcebans to provide group-based admin permissions for things like moderating comments/reviews, editing items and updating stock.</p>

	<p>Depending on your permissions, you may be able to manually synchronize the system with Sourcebans</p>

	{% if admins %}
		<div id="admin_batch_actions">
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