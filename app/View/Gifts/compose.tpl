{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'Composing ' ~ (isReward ? 'Reward' : 'Gift') %}

{% set scripts = ['common', 'gifts'] %}

{% block content %}

	<h1 class="page_heading">Compose {{ isReward ? 'Reward' : 'Gift' }}</h1>

	{{ session.flash() }}

	{% if data is empty %}
		{% if isReward %}
			<p>Please provide a list of recipients (one per line) and then choose which items they should receive. Multiple formats are allowed and you can even paste in a status print-out. If you want to send a reward to yourself, you need simply use the "me" keyword.</p>
		{% else %}
			<p>Please choose items from your inventory below to give to the specified recipient. When the gift is sent, the items you include will be removed from your inventory. If you are in-game in a store-enabled server, it will temporarily unload your inventory and then reload it immediately after the gift is successfully sent.</p>

			<p>If you choose to send your gift anonymously, your identity will be hidden and the gift will not be counted in the statistics shown on your {{ html.link('profile', {
					'controller': 'Users',
					'action': 'profile',
					'id': user.steamid
				}) }}. If you write a message with your gift, it will be displayed publicly so please keep it appropriate.</p>

			{% if access.check('Rewards') %}
				<p>Want to send a reward from Reflex Gamers instead? {{ html.link('Click here', {'controller': 'Rewards', 'action': 'compose'}) }}.</p>
			{% endif %}
		{% endif %}
	{% endif %}

	{{ form.create(isReward ? 'RewardDetail' : 'GiftDetail', {
		'inputDefaults': {
			'label': false,
			'div': false,
			'required': false
		},
		'url': (isReward ? {
				'controller': isReward ? 'rewards' : 'gifts',
				'action': data ? 'send' : 'package'
			} : {
				'controller': 'Gifts',
				'action': data ? 'send' : 'package',
				'id': player.steamid
		}),
		'class': 'cf'
	}) }}

	{% if data %}

		<h2 class="gift_subheading">
			{{ isReward ? 'Reward' : 'Gift' }} value
		</h2>
		<div class="gift_value">
			{% if isReward %}
				Each: {{ fn.currency(totalValue, {'big': true, 'wrap': true}) }}
				<br>
				Total: {{ fn.currency(totalValue * recipients|length, {'big': true, 'wrap': true}) }}
			{% else %}
				{{ fn.currency(totalValue, {'big': true, 'wrap': true}) }}
			{% endif %}
		</div>

	{% endif %}

	<h2 class="gift_subheading">Recipient{{ isReward ? (data ? 's: ' ~ recipients|length : '(s)') : '' }}</h2>

	{% if isReward %}

		{% if data %}

			{% for recipient in recipients %}

				<div class="gift_recipient">
					{{ fn.player(_context, players[recipient]) }}
				</div>

			{% endfor %}

		{% else %}

			{{ form.input('Reward.recipients', {
				'type': 'textarea',
				'placeholder': 'Enter a list of Steam IDs or profile URLs...',
				'class': 'gift_recipients_input'
			}) }}

		{% endif %}

	{% else %}

		<div class="gift_recipient">
			{{ fn.player(_context, player) }}
		</div>

	{% endif %}



	<h2 class="gift_subheading">Contents</h2>

	<table class="gift_item_table {{ data ? 'gift_preview' : '' }}">

		<tr>
			<th class="gift_item_heading">Item</th>
			{% if gift is empty and not isReward %}
				<th>You Have</th>
			{% endif %}
			<th class="quantity">Quantity</th>
		</tr>

		{% for item in (data ?: sortedItems) if isReward or userItems[item.item_id] > 0 or item.quantity > 0 %}

			{% set userQuantity = userItems[item.item_id] %}

			{% if data %}
				{% set quantity = item.quantity %}
				{% set item = items[item.item_id] %}
			{% endif %}

			<tr>
				<td class="gift_item_name">
					{{ html.image("items/#{item.short_name}.png", {
						'url': {'controller': 'Items', 'action': 'view', 'id': item.short_name},
						'class': 'gift_item_image'
					}) }}
					{{ html.link(item.name, {'controller': 'Items', 'action': 'view', 'id': item.short_name}) }}
				</td>
				{% if gift is empty and not isReward %}
					<td class="gift_item_available">
						{{ userQuantity }}
					</td>
				{% endif %}
				<td class="gift_item_input quantity">
					{% if data %}
						{{ quantity }}
					{% else %}
						{{ form.hidden(loop.index0 ~ '.item_id', {'value': item.item_id}) }}
						{{ form.input(loop.index0 ~ '.quantity', {
							'min': 0,
							'max': userQuantity
						}) }}
					{% endif %}
				</td>
			</tr>

		{% endfor %}

	</table>

	<h2 class="gift_subheading">Message (Optional)</h2>

	{{ form.input((isReward ? 'Reward' : 'Gift') ~ '.message', {
		'class': 'gift_message_input',
		'type': 'textarea',
		'maxlength': 100,
		'placeholder': data ? message : 'Write a message to the recipient' ~ (isReward ? '(s)' : '') ~ '...',
		'value': message,
		'disabled': data ? 'disabled' : ''
	}) }}

	<span class="gift_message_chars"></span>

	{% if not isReward %}
		<div class="gift_anonymous">
			{{ form.input('Gift.anonymous', {
				'label': 'send anonymously',
				'value': anonymous,
				'disabled': data ? 'disabled' : '',
				'class': 'gift_anonymous_checkbox'
			}) }}
		</div>
	{% endif %}

	{{ form.submit((data ? 'Send ' : 'Package ') ~ (isReward ? 'Reward' : 'Gift'), {
		'div': false,
		'class': 'btn-primary',
		'id': 'gift_package_button'
	}) }}

	{{ form.end() }}

	{% include 'Common/activity.inc.tpl' with {
		'title': 'Recent ' ~ (isReward ? 'Rewards' : 'Gifts')
	} %}

{% endblock %}