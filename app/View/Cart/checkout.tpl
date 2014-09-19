{% extends isAjax ? 'Common/ajax.tpl' : 'Common/layout.tpl' %}

{% set title = 'Checkout' %}

{% block content %}

	<h1 class="page_heading">Order Confirmation</h1>

	{% set flash = session.flash() %}

	{% if flash %}

		<p>{{ flash }}</p>

	{% else %}

		<div class="flashMessage">Please confirm the contents of your order below.</div>

		<div class="back_link">
			{{ html.link('< Return to Cart',
				{'controller': 'Cart', 'action': 'view'}
			) }}
		</div>

		<table class="cart">

			<tr>
				<th></th>
				<th>Item</th>
				<th>Price</th>
				<th>Qty</th>
				<th>Totals</th>
			</tr>

		{% for item in cart %}

			<tr>
				<td class="image">
					{{ html.image("items/#{items[item.item_id].short_name}.png", {'class': 'image'}) }}
				</td>
				<td class="desc">
					{{ items[item.item_id].name }}
				</td>
				<td class="price">
					{{ fn.currency(item.price) }}
				</td>
				<td class="quantity">
					{{ item.quantity }}
				</td>
				<td class="total">
					{{ fn.currency(item.price * item.quantity) }}
				</td>
			</tr>

		{% endfor %}

			<tr class="cart_separator">
				<td colspan="2"></td>
				<td colspan="2" class="cart_totals">Subtotal</td>
				<td id="cart_subtotal">{{ fn.currency(subTotal) }}</td>
			</tr>

			<tr>
				<td colspan="2"></td>
				<td colspan="2" class="cart_totals">Shipping</td>
				<td id="cart_shipping">{{ fn.currency(shipping) }}</td>
			</tr>

			<tr>
				<td colspan="2"></td>
				<td colspan="2" class="cart_totals">Grand Total</td>
				<td id="cart_total">{{ fn.currency(total) }}</td>
			</tr>

			<tr>
				<td colspan="2"></td>
				<td colspan="2" class="cart_totals">Your CASH</td>
				<td id="user_cash">{{ fn.currency(credit) }}</td>
			</tr>

		</table>

		{{ form.create('checkout', {
			'url': {
				'controller': 'Orders',
				'action': 'buy',
		}}) }}

		{{ form.end({
			'label': 'Complete Purchase',
			'div': false,
			'class': 'btn-primary',
			'id': 'cart_checkout_button'
		}) }}

	{% endif %}

{% endblock %}