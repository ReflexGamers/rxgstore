{% extends 'Common/base.tpl' %}

{% set title = 'Secret Stats' %}
{% set scripts = ['highcharts', 'highcharts-3d', 'charts', 'admin-stats'] %}

{% block content %}

    <p>This is the secret stats page that public users are not allowed to view.</p>

    <div id="credit_chart" class="chart_container" data-href="{{ html.url({
        'controller': 'TotalCreditLog',
        'action': 'totals',
        'ext': 'json'
    }) }}">
        <div class="chart_inner"></div>
    </div>

    <div id="paypal_chart" class="chart_container" data-href-yearly="{{ html.url({
        'controller': 'PaypalOrders',
        'action': 'yearlyTotals',
        'ext': 'json'
    }) }}" data-href-monthly="{{ html.url({
        'controller': 'PaypalOrders',
        'action': 'monthlyTotals',
        'ext': 'json'
    }) }}" data-href-daily="{{ html.url({
        'controller': 'PaypalOrders',
        'action': 'dailyTotals',
        'ext': 'json'
    }) }}">
        <div class="chart_inner"></div>
    </div>

{% endblock %}