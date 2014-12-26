{% extends 'Common/base.tpl' %}

{% set player = players[user_id] %}
{% set title = player.name %}

{% if activities or reviews %}
    {% set styles = ['rateit'] %}
    {% set scripts = ['jquery.rateit.min', 'items'] %}
{% endif %}

{% block title %}
    {{ fn.memberTag(player) }}
    {{ fn.stripTag(player)|e }}
{% endblock %}

{% block content %}

    <section class="player_details">

        <div class="player_avatar_full">
            <a href="{{ player.profile }}"><img src="{{ player.avatarfull }}"></a>
            {% if player.member %}
                <div class="player_member">
                    {{ player.division ? "RXG #{player.division} Division" : 'RXG Member' }}
                </div>
            {% endif %}
            <div class="player_links">
                <a href="{{ player.profile }}">view steam profile</a>
                {% if user and user.user_id != user_id %}
                    <br>{{ html.link('send a gift', {'controller': 'Gifts', 'action': 'compose', 'id': player.steamid}) }}
                {% endif %}
            </div>
        </div>

        <p class="player_credit">CASH: {{ fn.currency(credit, {'big': true, 'wrap': true}) }} <span class="cash_spent">({{- fn.currency(totalSpent, {'big': true, 'hideIcon': true}) }} spent)</span></p>

        {% if userItems %}

            <div class="player_inventory">
                {% include 'Items/list.inc.tpl' with {
                    'quantity': userItems,
                    'maxColumns': 4
                } %}
            </div>

        {% else %}

            <p>This player currently has no items.</p>

        {% endif %}

    </section>


    {% if pastItems %}

        <section id="past_items">

            <h2 class="page_subheading">Past Items</h2>

            {% include 'Items/list.inc.tpl' with {
                'quantity': pastItems,
                'maxColumns': 7
            } %}

        </section>

    {% endif %}


    {% include 'Common/reviews.inc.tpl' with {
        'title': 'Item Reviews',
        'headerClass': 'player_reviews'
    } %}

    {% include 'Common/activity.inc.tpl' with {
        'title': 'Recent Activity'
    } %}

{% endblock %}