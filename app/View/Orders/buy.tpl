{% extends 'Common/layout.tpl' %}

{% set title = 'CHECKOUT' %}
{% set headerImage = false %}
{% set hideTitle = true %}
{% set backLink = {'controller': 'store', 'action': 'store'} %}

{% block content %}

	<h1 class="page_heading">Order Completion</h1>

	{{ session.flash() }}

	{% if order %}

		<div class="back_link">
			{{ html.link('< Continue Shopping',
				{'controller': 'Items', 'action': 'index'}
			) }}
		</div>

		{% include 'Orders/receipt.inc.tpl' with {'data': order} %}

	{% endif %}

{% endblock %}