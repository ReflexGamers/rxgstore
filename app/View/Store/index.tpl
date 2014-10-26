{% extends 'Common/layout.tpl' %}

{% set title = 'RXG CENTER' %}
{% set headerImage = 'pokeman_center.png' %}

{% block content %}

    <p style="text-align:center">What is your business today?</p>

    {{ html.link('Store', {'controller': 'store', 'action': 'store'}, {'class': 'business'}) }}
    {{ html.link('Recent Purchases', {'controller': 'Orders', 'action': 'recent'}, {'class': 'business'}) }}
    {{ html.link('How To Use Item', {'controller': 'store', 'action': 'use'}, {'class': 'business'}) }}
    {{ html.link('Bulletin Board', {'controller': 'store', 'action': 'bulletinboard'}, {'class': 'business'}) }}
    {{ html.link('Biggest Spenders', {'controller': 'store', 'action': 'topspenders'}, {'class': 'business'}) }}

{% endblock %}