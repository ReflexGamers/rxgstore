{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'Stock Items' %}
{% set hideTitle = true %}
{% set headerImage = false %}


{% block content %}

	<h1 class="page_heading">Receive a Shipment</h1>

	{{ session.flash() }}

	<p>Below, you may receive a shipment and add items to stock.</p>

	<p>Note that due to warehousing limitations, we are only able to store a certain amount of each item. If you attempt to stock more items than we can hold, it will not work.</p>

	<p>When an item has at least the "ideal" amount in stock, the item's listing page will show as simply "In Stock" and not list the number of items available.</p>

	<p>On this page: Items with less than "ideal" stock will show in orange. Items with less than half of "ideal" stock will show in red.</p>

	{{ form.create('Stock', {
		'inputDefaults': {
			'label': false,
			'div': false,
			'required': false
	}}) }}

		<table class="item_stock_table">

			<tr>
				<th></th>
				<th class="item_stock_ideal">Ideal</th>
				<th>In Stock</th>
				<th>Quantity</th>
			</tr>

			{% for item in stock %}

				<tr>
					<td class="item_stock_name">
						{{ html.image("items/#{item.short_name}.png", {
							'url': {'controller': 'items', 'action': 'view', 'name': item.short_name},
							'class': 'item_stock_image'
						}) }}
						{{ html.link(item.name, {'controller': 'items', 'action': 'view', 'name': item.short_name}) }}
					</td>
					<td class="item_stock_ideal">
						{{ item.ideal_quantity }}
					</td>
					<td class="item_stock_quantity">
						<span class="item_stock_current {{ item.quantity < item.ideal_quantity / 2 ? 'stock_danger' : item.quantity < item.ideal_quantity ? 'stock_warning' : '' }}">{{ item.quantity }}</span> / {{ item.maximum }}
					</td>
					<td class="item_stock_input">
						{{ form.hidden(loop.index0 ~ '.item_id', {'value': item.item_id}) }}
						{{ form.input(loop.index0 ~ '.quantity', {
							'min': 0,
							'max': item.maximum - item.quantity
						}) }}
					</td>
				</tr>

			{% endfor %}

		</table>

	{{ form.end({
		'label': 'Receive Shipment',
		'div': false,
		'class': 'btn-primary',
		'id': 'item_stock_button'
	}) }}

	<div class="clear"></div>

	<h2 class="page_subheading">Recent Shipments</h2>

	{% include 'Activity/recent.inc.tpl' %}

{% endblock %}