<div class="item_browse_gifts">

	{% for data in gifts %}

		{% set gift = isReward ? data.Reward : data.Gift  %}
		{% set detail = isReward ? gift.RewardDetail : data.GiftDetail %}
		{% set sender = players[gift.sender_id] %}

		<div class="item_browse_gift">

			<div class="item_browse_gift_inner">

				<div class="activity_player sender">
					{% if isReward %}
						<span class="item_browse_reward_sender">RXG Leadership</span>
					{% elseif gift.anonymous %}
						<span class="browse_gift_anonymous">Anonymous</span>
					{% else %}
						<img src="{{ sender.avatar }}">
						{{ html.link(sender.name, {
							'controller': 'Users',
							'action': 'profile',
							'id': sender.steamid
						}) }}
					{% endif %}
				</div>

				<div class="activity_date">
					sent you a {{ isReward ? 'reward' : 'gift' }} {{ fn.time(_context, gift.date) }}
				</div>

				{% include 'Items/list.inc.tpl' with {
					'quantity': detail
				} %}

				{% if gift.message %}
					<div class="activity_message">
						"{{ gift.message|e }}"
					</div>
				{% endif %}

				<div class="item_browse_gift_actions">
					<input type="button" value="Click to Accept" class="item_browse_gift_accept btn-primary" data-href="{{ html.url({
						'controller': isReward ? 'Rewards' : 'Gifts',
						'action': 'accept',
						'id': isReward ? gift.reward_id : gift.gift_id
					}) }}">
					{{ html.image('misc/ajax-loader.gif', {
						'class': 'ajax-loader item_browse_gift_loading'
					}) }}
				</div>

			</div>

		</div>

	{% endfor %}

</div>
