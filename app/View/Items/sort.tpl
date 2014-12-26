{% extends 'Common/base.tpl' %}

{% set title = 'Item Sorting' %}
{% set scripts = ['jquery-ui.min', 'admin-sort'] %}


{% block content %}

    <p>Click and drag the items below to change their display order.</p>

    <p>This order will be used for nearly every place lists of items are displayed. This includes the main browse page, player inventories, player histories, etc.</p>

    {{ form.create('Item') }}

    <div id="item_sort">

        {% for item in sortedItems %}

            <div class="item_sort_draggable">
                {{ form.hidden(loop.index0 ~ '.display_index', {'value': loop.index0, 'data-original': loop.index0, 'class': 'item_sort_index'}) }}
                {{ form.hidden(loop.index0 ~ '.item_id', {'value': item.item_id}) }}
                <div class="item_sort_movable"><i class="fa fa-arrows-v"></i></div>
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