{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'QuickAuth Records' %}
{% set hideTitle = true %}
{% set headerImage = false %}

{% set scripts = ['admin-cache'] %}


{% block content %}

	<h1 class="page_heading">QuickAuth Records</h1>

	<p>Below is a list of all QuickAuth attempts by players using !store in RXG servers or from other sources such as the website.</p>

	{% if quickauth %}

		<p class="cache_total">{{ quickauth|length }} total records</p>

		<div id="quickauth_data">

			{% include 'QuickAuth/list.inc.tpl' %}

		</div>

	{% else %}

		<p>No records yet!</p>

	{% endif %}

{% endblock %}