
<tr>
    <td class="image">
        {{ html.image("items/#{item.short_name}.png", {
            'class': 'image',
            'url': {'controller': 'Items', 'action': 'view', 'id': item.short_name}
        }) }}
    </td>
    <td class="desc">
        {{ item.name }}
    </td>
    <td class="price">
        {{ fn.currency(cartItem.price, {'wrap': true}) }}
    </td>
    <td class="quantity">
        {{ form.hidden(loop.index0 ~ '.item_id', {'value': item.item_id}) }}
        {{ form.input(loop.index0 ~ '.quantity', {
            'label': false,
            'div': false,
            'value': cartItem.quantity,
            'class': 'cart_quantity_input',
            'data-price': cartItem.price,
            'data-original': cartItem.quantity,
            'min': 0,
            'max': stock[item.item_id]
        }) }}
        {{ html.image('misc/remove.png', {
            'url': {'controller': 'Cart', 'action': 'remove', 'id': item.item_id},
            'class': 'cart_remove',
            'title': 'remove',
            'height': 20,
            'width': 20
        }) }}
    </td>
    <td class="total">{{ fn.currency(itemTotal, {'wrap': true}) }}</td>
</tr>
