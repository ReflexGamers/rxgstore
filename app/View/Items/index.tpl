{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'Browse Items' %}
{% set styles = ['rateit'] %}
{% set scripts = ['jquery.ddslick.min', 'jquery.rateit.min', 'browse', 'common'] %}

{% block content %}

	<h1 class="page_heading">RXG Store</h1>

	{{ session.flash() }}

	{% include '/ShoutboxMessages/shoutbox.inc.tpl' %}

	{% if user %}

		<div class="item_browse_inventory">

			<div class="item_browse_cash">You have: {{ fn.currency(credit, {'big': true}) }}</div>

			<div id="item_browse_inventory_content">

				{% if userItems is not empty %}

					{% include 'Items/list.inc.tpl' with {
						'quantity': userItems,
						'maxColumns': 7
					} %}

				{% else %}

					<p>... and no items!<br/>Go buy stuff nerd!</p>

				{% endif %}

			</div>

		</div>

		{% if gifts %}

			{% include 'Gifts/view.inc.tpl' %}

		{% endif %}

		{% if rewards %}

			{% include 'Gifts/view.inc.tpl' with {
				'gifts': rewards,
				'isReward': true
			} %}

		{% endif %}

	{% endif %}

	<div class="server_select" data-child-servers="{{ childServers }}">

		{{ form.select('short_name', {'all': 'All Game Servers'}|merge(servers), {
			'class': 'server_select_options',
			'id': 'server_select_menu',
			'value': server,
			'empty': false,
			'div': false
		}) }}

		{{ html.image(
			'misc/ajax-loader.gif',
			{'class': 'ajax-loader', 'id': 'server_select_loading'}
		) }}

	</div>

	<ul id="item_browse_list" data-href="{{ html.url({'controller': 'items', 'action': 'server'}) }}">
		{% include 'Items/server.tpl' %}
	</ul>

	<div class="clear"></div>

	<h2 class="page_subheading">Recent Activity</h2>

	<div id="activity">
		{% include 'Activity/recent.inc.tpl' %}
	</div>

{% endblock %}