{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set title = 'QuickAuth Records' %}
{% set scripts = 'common' %}

{% block content %}

	<h1 class="page_heading">{{ title }}</h1>

	<p>QuickAuth is our system for logging players in automatically if we know their Steam ID. This happens in RXG servers when players type <code>!store</code> or from other special sources such as Forums.</p>

	<p>To ensure total security, when a user needs to be logged in, the client application (e.g., the game server) first generates a token and stores it in the database. The person is then sent to a URL with the token to redeem it. For added security, each token can only be redeemed once and expires after <strong>{{ tokenExpire / 60 }}</strong> minutes.</p>

	<p>All QuickAuth attempts are recorded permanently and will be visible on this page for historical, statistical and diagnostic purposes.</p>

	<p>{{ html.link('Click here', {
			'controller': 'Admin',
			'action': 'viewlog',
			'name': 'quickauth'
		}) }} to view the QuickAuth log.</p>

	<div id="quickauth_content">
		{% include 'QuickAuth/list.inc.tpl' %}
	</div>

{% endblock %}