{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set jquery = true %}
{% set title = 'Shopping Cart' %}
{% set scripts = ['common', 'cart'] %}

{% block content %}

	<h1 class="page_heading">Shopping Cart</h1>

	{{ session.flash() }}

	{% if cart %}

		<p>Manage the contents of your cart below. If you edit your cart and want to save your changes for later, click Update Cart before leaving this page.</p>

		<p>Want more CASH? {{ html.link('Buy it with PayPal™', {
				'controller': 'paypal',
				'action': 'addfunds'
			}) }}.</p>

		{{ form.create('OrderDetail', {
			'url': {
				'controller': 'Cart',
				'action': 'process'
			},
			'id': 'CartForm'
		}) }}

		<input type="hidden" id="shipping_cost" value="{{ shippingCost }}">
		<input type="hidden" id="shipping_free_threshold" value="{{ shippingFreeThreshold }}">

		<table class="cart">

			<tr>
				<th></th>
				<th>Item</th>
				<th>Price</th>
				<th>Qty</th>
				<th>Totals</th>
			</tr>

			{% set subTotal = 0 %}

			{% for item in items if cart[item.item_id].quantity > 0 %}

				{% set cartItem = cart[item.item_id] %}
				{% set itemTotal = cartItem.price * cartItem.quantity %}
				{% set subTotal = subTotal + itemTotal %}

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

			{% endfor %}

			{% set shipping = subTotal >= shippingFreeThreshold ? 0 : shippingCost %}
			{% set total = subTotal + shipping %}

			<tr class="cart_separator">
				<td colspan="2"></td>
				<td colspan="2" class="cart_totals">Subtotal</td>
				<td id="cart_subtotal">{{ fn.currency(subTotal, {'wrap': true}) }}</td>
			</tr>

			<tr>
				<td colspan="2"></td>
				<td colspan="2" class="cart_totals">Est. Shipping</td>
				<td id="cart_shipping">{{ fn.currency(shipping, {'wrap': true}) }}</td>
			</tr>

			<tr>
				<td colspan="2"></td>
				<td colspan="2" class="cart_totals">Grand Total</td>
				<td id="cart_total">{{ fn.currency(total, {'wrap': true}) }}</td>
			</tr>

			<tr>
				<td colspan="2"></td>
				<td colspan="2" class="cart_totals">Your CASH</td>
				<td id="user_cash">{{ fn.currency(credit, {'wrap': true}) }}</td>
			</tr>

		</table>

		{{ form.button('Proceed to Checkout', {
			'class': 'btn-primary',
			'id': 'cart_checkout_button',
			'name': 'ProcessAction',
			'value': 'checkout'
		}) }}

		{{ form.button('Empty Cart', {
			'class': 'btn-warning',
			'id': 'cart_empty_button',
			'name': 'ProcessAction',
			'value': 'empty',
			'formnovalidate': 'formnovalidate'
		}) }}

		{{ form.button('Update Cart', {
			'class': 'btn-success',
			'id': 'cart_update_button',
			'name': 'ProcessAction',
			'value': 'update'
		}) }}

		{{ form.end() }}

		<div class="clear"></div>

	{% else %}

		<p>Your cart is empty! Go pick out some items and then return here to complete your purchase.</p>

	{% endif %}

{% endblock %}
