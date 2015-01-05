{% extends 'Common/base.tpl' %}

{% set title = 'RXG Store Stats' %}
{% set scripts = ['highcharts', 'highcharts-3d', 'charts', 'stats'] %}

{% block content %}

    <p>Here are some cool stats.</p>

    {% include 'Charts/multi.inc.tpl' with {
        'id': 'spent_chart',
        'controller': 'Orders',
        'action': 'totals',
        'controls': [
            ['All Time'],
            ['Past Month', {'time': monthAgo}],
            ['Past Week', {'time': weekAgo}, true],
            ['Past Day', {'time': dayAgo}]
        ]
    } %}

{% endblock %}