{% import 'Common/functions.tpl' as fn %}

<div class="addfunds_option">

	{{ form.create('PaypalOrder', {
		'inputDefaults': {
			'div': false,
			'label': false
		},
		'url': {
			'controller': 'paypalorders',
			'action': 'begin'
		}
	}) }}

	{{ form.hidden('option', {
		'value': loop.index0
	}) }}

	<span class="addfunds_amount">{{ fn.realMoney(price) }}</span> for {{ fn.currency(price * mult * currencyMult, {'wrap': true}) }}
	{% if mult > 1 %}
		(+{{ (mult - 1) * 100 }}% BONUS)
	{% endif %}

	{{ form.submit('BUY', {
		'class': 'btn-primary addfunds_btn',
		'div': false
	}) }}

	{{ form.end() }}

</div>