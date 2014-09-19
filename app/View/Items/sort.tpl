{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'Item Sorting' %}
{% set hideTitle = true %}
{% set headerImage = false %}

{% set scripts = ['jquery-ui.min', 'admin-sort'] %}


{% block content %}

	<h1 class="page_heading">Item Sorting</h1>

	{{ session.flash() }}

	<p>Click and drag the items below to change their display order.</p>

	<p>This order will be used for nearly every place lists of items are displayed. This includes the main browse page, player inventories, purchase histories, etc.</p>

	{{ form.create('Item') }}

	<div id="item_sort">

		{% for item in sortedItems %}

			<div class="item_sort_draggable">
				{{ form.hidden(loop.index0 ~ '.display_index', {'value': item.display_index, 'data-original': item.display_index, 'class': 'item_sort_index'}) }}
				{{ form.hidden(loop.index0 ~ '.item_id', {'value': item.item_id}) }}
				<div class="item_sort_image">
					{{ html.image("items/#{item.short_name}.png", {
						'url': {'controller': 'Items', 'action': 'view', 'id': item.short_name}
					}) }}
				</div>
				<div class="item_sort_name">
					{{ item.name }}
				</div>
			</div>

		{% endfor %}

	</div>

	{{ form.end({
	 	'label': 'Save Display Order',
		'class': 'btn-primary',
		'id': 'item_sort_save',
		'div': false
	}) }}

{% endblock %}