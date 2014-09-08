{% import 'Common/functions.tpl' as fn %}

{% for item in serverItems %}

	{% set rating = ratings[item.item_id] %}

	<li>
		<div class="item_browse_rating">
			<div class="item_browse_price">
				{#{{ fn.currency(item.price) }}#}
			</div>
			<div class="rateit bigstars smaller" data-rateit-value="{{ rating.average / 2 }}"
				 title="{{ rating ? "out of #{rating.count} rating" ~ (rating.count > 1 ? 's' : '') : '' }}"
				 data-rateit-starwidth="24" data-rateit-starheight="24"
				 data-rateit-resetable="false"
				 data-rateit-readonly="true"
				 data-href="{{ html.url({'controller': 'ratings', 'action': 'rate', 'id': item.item_id}) }}"></div>
				<div class="item_browse_rating_count">
					{% if rating %}
						<strong>{{ rating.average/2|round(1) }}</strong>/5 ({{ "#{rating.count} rating" ~ (rating.count > 1 ? 's' : '') }})
					{% else %}
						not yet rated
					{% endif %}
				</div>
		</div>
		{{ html.image("items/#{item.short_name}.png", {
			'url': {'action': 'view', 'name': item.short_name},
			'class': 'item_browse_image'
		}) }}
		<div class="item_browse_name">
			{{ html.link(item.name, {'action': 'view', 'name': item.short_name}, {'class': 'item_browse_link'}) }}
		</div>
	</li>

{% endfor %}
