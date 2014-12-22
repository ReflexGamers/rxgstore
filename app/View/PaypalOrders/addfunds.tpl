{% extends 'Common/base.tpl' %}

{% set title = "Buy CASH" %}
{% set scripts = 'addfunds' %}

{% block content %}

    <h1 class="page_heading">{{ title }}</h1>

    {{ session.flash() }}

    <div class="addfunds">

        <p>Add funds to your rxg CASH through PayPal™. If you do not have a PayPal™ account, you can use a credit card to pay once directed to their site. Every dollar of real money is equivalent to a minimum of <strong>{{ fn.currency(100 * currencyMult) }} CASH</strong>. Depending on how much you give, you may receive a bonus.</p>

        <div class="addfunds_options">

            {% for price, mult in options %}

                {% include 'PaypalOrders/option.inc.tpl' %}

            {% endfor %}

        </div>

        <input type="button" id="addfunds_more_btn" class="btn-primary" value="Buy more than ${{ "%.2f"|format(minPrice) }} CASH">

        <div id="addfunds_more">

            {{ form.create('PaypalOrder', {
                'inputDefaults': {
                    'div': false,
                    'label': false
                },
                'url': {
                    'controller': 'PaypalOrders',
                    'action': 'begin'
                }
            }) }}

                Spend this much:
                ${{ form.input('amount', {
                    'value': minPrice,
                    'type': 'number',
                    'min': minPrice,
                    'id': 'addfunds_input'
                }) }}

                <br />

                Receive this much:
                <span id="addfunds_receive" data-currency-mult="{{ maxMult * currencyMult }}">{{ fn.currency(minPrice * 100 * maxMult * currencyMult, {'wrap': 'true'}) }}</span>

                {{ form.submit('BUY CASH', {
                    'class': 'btn-primary',
                    'id': 'addfunds_more_submit'
                }) }}

            {{ form.end() }}

        </div>

        <p>100% of dollar bills obtained is put back into the community; feel confident while giving us your money!</p>

        {% if activities %}

            {% include 'Common/activity.inc.tpl' with {
                'title': 'Recent CASH Purchases'
            } %}

        {% endif %}

    </div>

{% endblock %}