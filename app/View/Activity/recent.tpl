{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'RECENT ACTIVITY' %}
{% set headerImage = 'pokemart.png' %}

{% block content %}

	<div id="activity">
		{% include 'Activity/recent.inc.tpl' %}
	</div>

{% endblock %}