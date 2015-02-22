
{% set isCreateNew = (data.Promotion.promotion_id is empty) %}

{{ form.create('Promotion', {
    'inputDefaults': {
        'label': false,
        'div': false
    }
}) }}
{{  promotion.promotion_id }}
{{ form.input('promotion_id', {
    value: data.Promotion.promotion_id
}) }}

<div class="edit_field">
    {{ form.label('name', 'Name:', {
        class: 'edit_label'
    }) }}
    {{ form.input('name', {
        value: data.Promotion.name,
        class: 'edit_input'
    }) }}
</div>

<div class="edit_field">
    {{ form.label('name', 'Start Date:', {
        class: 'edit_label'
    }) }}
    {{ form.input('start_date', {
        selected: data.Promotion.start_date,
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
        selected: data.Promotion.end_date,
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
        checked: data.Promotion.is_member_only,
        class: 'edit_input'
    }) }}
</div>

<div class="promotion_table_wrapper">

    <h2 class="gift_subheading">Contents</h2>

    <table class="gift_item_table">

        <tr>
            <th class="gift_item_heading">Item</th>
            {% if showUserQuantity %}
                <th>You Have</th>
            {% endif %}
            <th class="quantity">Quantity</th>
        </tr>

        {% for item in sortedItems %}

            {% set namePrefix = 'PromotionDetail.' ~ item.item_id %}
            {% set lineItem = data.PromotionDetail[item.item_id] %}

            <tr>
                <td class="gift_item_name">
                    {{ html.image("items/#{item.short_name}.png", {
                        url: {controller: 'Items', action: 'view', id: item.short_name},
                        class: 'gift_item_image'
                    }) }}
                    {{ html.link(item.name, {'controller': 'Items', 'action': 'view', 'id': item.short_name}) }}
                </td>
                <td class="gift_item_input quantity">
                    {{ form.hidden("#{namePrefix}.promotion_detail_id", {
                        value: lineItem.promotion_detail_id ?: ''
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
    {{ form.submit(isCreateNew ? 'Create Promotion' : 'Update Promotion', {
        'class': 'btn-primary promotion_save_button',
        'div': false
    }) }}
</div>

{{ form.end() }}