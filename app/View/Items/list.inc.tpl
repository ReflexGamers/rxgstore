{% if not maxColumns or maxColumns > 6 %}
	{% set maxColumns = 6 %}
{% endif %}

{% set numItems = quantity|length %}
{% set cols = numItems > maxColumns ? (numItems / 2)|round(0, 'ceil') : numItems %}
{% set cols = cols > maxColumns ? maxColumns : cols %}

<div class="item_list_group">

	<ul class="item_list">
		{% for item in items if quantity[item.item_id] > 0 %}
			<li class="item_list_entry" data-item_id="{{ item.item_id }}">
				<div class="item_pic" title="{{ item.name }}">
					{{ html.image("items/#{item.short_name}.png", {
						'url': {
							'controller': 'Items',
							'action': 'view',
							'id': item.short_name
						}
					}) }}
					<div class="item_amt">
						{{ quantity[item.item_id] }}
					</div>
				</div>
			</li>
			{% if loop.index is divisible by(cols) and numItems > loop.index %}
				</ul>
				<ul class="item_list">
			{% endif %}
		{% endfor %}
	</ul>

</div>

{#
<table class="item_list">
	<tr>
		{% for item in items if quantity[item.item_id] > 0 %}
		<td class="item_list_entry" data-item_id="{{ item.item_id }}">
			<div class="item_pic" title="{{ item.name }}">
				{{ html.image("items/#{item.short_name}.png", {
					'url': {
						'controller': 'Items',
						'action': 'view',
						'id': item.short_name
					}
				}) }}
				<div class="item_amt">
					{{ quantity[item.item_id] }}
				</div>
			</div>
		</td>
		{% if loop.index is divisible by(maxColumns) and quantity|length > loop.index %}
			</tr>
			<tr>
		{% endif %}
		{% endfor %}
	</tr>
</table>
#}