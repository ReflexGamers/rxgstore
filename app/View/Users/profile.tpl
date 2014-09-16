{% extends 'Common/base.tpl' %}

{% set jquery = true %}
{% set title = 'Player Profile' %}

{% if activities or reviews %}
	{% set styles = ['rateit'] %}
	{% set scripts = ['jquery.rateit.min', 'items', 'common'] %}
{% endif %}

{% block content %}

	{% set player = players[user_id] %}

	<h1 class="player_heading">
		{{ fn.memberTag(player) }}
		{{ fn.stripTag(player)|e }}
	</h1>

	<section class="player_details">

		<div class="player_avatar">
			<a href="{{ player.profile }}"><img src="{{ player.avatarfull }}"></a>
			{% if player.member %}
				<div class="player_member">
					{{ player.division ? "RXG #{player.division} Division" : 'RXG Member' }}
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

	</section>


	{% if pastItems %}

		<section id="lifetime">

			<h2 class="page_subheading">Past Items</h2>

			{% include 'Items/list.inc.tpl' with {
				'quantity': pastItems,
				'maxColumns': 7
			} %}

		</section>

	{% endif %}


	{% if reviews %}

		<section id="reviews">

			<h2 class="page_subheading player_reviews">Item Reviews</h2>

			<div id="reviews_content">
				{% include '/Reviews/list.inc.tpl' %}
			</div>

		</section>

	{% endif %}


	{% if activities %}

		{% include 'Common/activity.inc.tpl' with {
			'title': 'Recent Activity'
		} %}

	{% endif %}

{% endblock %}