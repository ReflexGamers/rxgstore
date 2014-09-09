{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'Player Profile' %}

{% if activities or reviews %}
	{% set styles = ['rateit'] %}
	{% set scripts = ['jquery.rateit.min', 'items', 'common'] %}
{% endif %}

{% block content %}

	{% set player = players[user_id] %}

	{% if not isAjax %}

		<div class="player_info">

			<h2 class="player_heading">
				{{ fn.memberTag(player) }}
				{{ fn.stripTag(player)|e }}
			</h2>

			<div class="player_avatar">
				<a href="{{ player.profile }}"><img src="{{ player.avatarfull }}"></a>
				{% if player.member %}
					<div class="player_member">
						RXG Member
					</div>
				{% endif %}
				<div class="player_links">
					<a href="{{ player.profile }}">view steam profile</a>
					{% if user and user.user_id != user_id %}
						<br>{{ html.link('send a gift', {'controller': 'Gifts', 'action': 'compose', 'id': player.steamid}) }}
					{% endif %}
				</div>
			</div>

			<p class="player_credit">CASH: {{ fn.currency(credit, {'big': true, 'wrap': true}) }} <span class="cash_spent">({{- fn.currency(totalSpent, {'big': true, 'hideIcon': true}) }} spent)</span></p>

			{% if userItems %}

				<div class="player_inventory">
					{% include 'Items/list.inc.tpl' with {
						'quantity': userItems,
						'maxColumns': 4
					} %}
				</div>

			{% else %}

				<p>This player currently has no items.</p>

			{% endif %}

		</div>


		{% if pastItems %}

			<h2 class="page_subheading player_past">Past Items</h2>

			{% include 'Items/list.inc.tpl' with {
				'quantity': pastItems,
				'maxColumns': 7
			} %}

		{% endif %}


		{% if reviews %}

			<h2 class="page_subheading player_reviews">Item Reviews</h2>

			<div id="reviews">
				{% include '/Reviews/list.inc.tpl' %}
			</div>

		{% endif %}


		<h2 class="page_subheading player_activity">Recent Activity</h2>

	{% endif %}


	{% if activities %}

		<div id="activity">
			{% include 'Activity/recent.inc.tpl' %}
		</div>

		{#
		<div id="recent_activity">

			{{ paginator.options({
				'update': '#recent_activity'
			}) }}

			{% for data in orders %}

				{% set trans = data.Order %}

				<div class="player_recent_activity">

					<div class="player_recent_heading">
						<div class="player_recent_info">
							{{ fn.time(_context, trans.date) }}
						</div>
						<span class="player_recent_subheading">
							Sale ID #{{ trans.order_id }}

							{% if user.user_id == trans.user_id %}

								{{ js.link(
									'View Receipt',
									{'controller': 'Orders', 'action': 'receipt', 'id': trans.order_id}, {
										'async': true,
										'update': "#receipt_#{trans.order_id}",
										'htmlAttributes': {
											'class': 'player_receipt_link',
											'id': "player_receipt_link_#{trans.order_id}"
										},
										'before': js.get("#ajax-loader_#{trans.order_id}").effect('fadeIn'),
										'complete': js.get("#ajax-loader_#{trans.order_id}").effect('fadeOut')
									}
								) }}

							{% endif %}
						</span>
					</div>

					<div class="recent_item_list">
						{% include 'Items/list.inc.tpl' with {
							'quantity': data.OrderDetail,
							'maxColumns': 7
						} %}

						{{ html.image(
							'misc/ajax-loader.gif',
							{'class': 'player_receipt_loader ajax-loader', 'id': "ajax-loader_#{trans.order_id}"}
						) }}
					</div>

					<div id="receipt_{{ trans.order_id }}" class="receipt_placeholder"></div>

				</div>

			{% endfor %}

			{% include 'Common/pagination.inc.tpl' %}

		</div>
		#}

	{% else %}

		<p>Nothing to see here!</p>

	{% endif %}

{% endblock %}