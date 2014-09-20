{% extends 'Common/base.tpl' %}

{% if not isAjax %}
	{% set scripts = 'common' %}
{% endif %}

{% block content %}

	{% if isAjax %}

		<div id="cache_content">
			{% include 'SteamPlayerCache/list.inc.tpl' %}
		</div>

	{% else %}

		<h1 class="page_heading">Steam Player Cache</h1>

		<div id="cache_content">
			{% include 'SteamPlayerCache/list.inc.tpl' %}
		</div>

	{% endif %}

{% endblock %}
