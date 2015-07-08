
{% set isCreateNew = (data.Giveaway.giveaway_id is empty) %}

<div class="back_link">
    <i class="fa fa-arrow-circle-left"></i>
    {{ html.link('Giveaways',
        {'action': 'index'}
    ) }}
</div>

{{ form.create('Giveaway', {
    'inputDefaults': {
        'label': false,
        'div': false
    },
    'class': 'cf'
}) }}
{{  giveaway.giveaway_id }}
{{ form.input('giveaway_id', {
    value: data.Giveaway.giveaway_id
}) }}

<div class="edit_field">
    {{ form.label('name', 'Name:', {
        class: 'edit_label'
    }) }}
    {{ form.input('name', {
        value: data.Giveaway.name,
        class: 'edit_input'
    }) }}
</div>

<div class="edit_field">
    {{ form.label('name', 'Start Date:', {
        class: 'edit_label'
    }) }}
    {{ form.input('start_date', {
        selected: data.Giveaway.start_date,
        dateFormat: 'MDY',
        timeFormat: '',
        class: 'edit_input_date'
    }) }}
</div>

<div class="edit_field">
    {{ form.label('name', 'End Date:', {
        class: 'edit_label'
    }) }}
    {{ form.input('end_date', {
        selected: data.Giveaway.end_date,
        dateFormat: 'MDY',
        timeFormat: '',
        class: 'edit_input_date'
    }) }}
</div>

<div class="edit_field">
    {{ form.label('is_member_only', 'Member Only:', {
        class: 'edit_label'
    }) }}
    {{ form.input('is_member_only', {
        checked: data.Giveaway.is_member_only,
        class: 'edit_input'
    }) }}
</div>

<div class="giveaway_table_wrapper">

    <h2 class="gift_subheading">Contents</h2>

    <table class="gift_item_table">

        <tr>
            <th class="gift_item_heading">Item</th>
            {% if showUserQuantity %}
                <th>You Have</th>
            {% endif %}
            <th class="quantity">Quantity</th>
        </tr>

        {% for item in sortedItems if item.item_id != 0 %}

            {% set namePrefix = 'GiveawayDetail.' ~ item.item_id %}
            {% set lineItem = data.GiveawayDetail[item.item_id] %}

            <tr>
                <td class="gift_item_name">
                    {{ html.image("items/#{item.short_name}.png", {
                        url: {controller: 'Items', action: 'view', id: item.short_name},
                        class: 'gift_item_image'
                    }) }}
                    {{ html.link(item.name, {'controller': 'Items', 'action': 'view', 'id': item.short_name}) }}
                </td>
                <td class="gift_item_input quantity">
                    {{ form.hidden("#{namePrefix}.giveaway_detail_id", {
                        value: lineItem.giveaway_detail_id ?: ''
                    }) }}
                    {{ form.hidden("#{namePrefix}.item_id", {
                        value: item.item_id
                    }) }}
                    {{ form.input("#{namePrefix}.quantity", {
                        required: false,
                        value: lineItem.quantity ?: ''
                    }) }}
                </td>
            </tr>

        {% endfor %}

    </table>

</div>

<div>
    {{ form.submit(isCreateNew ? 'Create Giveaway' : 'Update Giveaway', {
        'class': 'btn-primary giveaway_save_button',
        'div': false
    }) }}
</div>

{{ form.end() }}

{% if not isCreateNew %}
    {% include 'Common/activity.inc.tpl' with {
      'title': 'Recent Claims'
    } %}
{% endif %}