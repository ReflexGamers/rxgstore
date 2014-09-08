{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = false %}
{% set headerImage = false %}
{% set hideTitle = true %}

{% block content %}

	<div id="activity">
		{% include 'Activity/recent.inc.tpl' %}
	</div>

{% endblock %}