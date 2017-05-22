{% extends 'Common/base.tpl' %}

{% set title = 'Compose ' ~ (isReward ? 'Reward' : 'Gift') %}
{% set scripts = 'gifts' %}

{% block content %}

    {% if composing %}

        {% if isReward %}
            <p>The Reward system is a way to give free items to specific players for attending events, recruiting, or anything else that leadership deems reasonable. If you want to send a reward for something that we do not have a history of rewarding, you should discuss it with other leadership first.</p>

            <p>To send a reward, simply provide a list of recipients (one per line) and then choose which items they should receive. All Steam ID formats (including profile urls) are accepted and you can even paste in a status print-out. If you want to send a reward to yourself, you need simply use the "me" keyword.</p>

            <p>Do not send random rewards to yourself or others just because you feel like it. If you want to send items to a player, it may be more appropriate to send a gift from yourself instead of from Reflex Gamers. You can search for players {{ html.link('here', { 'controller': 'SteamPlayerCache', 'action': 'search' }) }}.</p>
        {% else %}
            <p>Please choose items from your inventory below to give to the specified recipient. When the gift is sent, the items you include will be removed from your inventory. If you are in-game in a store-enabled server, it will temporarily unload your inventory and then reload it immediately after the gift is successfully sent.</p>

            <p>If you choose to send your gift anonymously, your identity will be hidden and the gift will not be counted in the statistics shown on your {{ html.link('profile', {
                    'controller': 'Users',
                    'action': 'profile',
                    'id': user.steamid
                }) }}. If you write a message with your gift, it will be displayed publicly so please keep it appropriate.</p>

            {% if access.check('Rewards', 'create') %}
                <p>Want to send a reward from Reflex Gamers instead? {{ html.link('Click here', {'controller': 'Rewards', 'action': 'compose'}) }}.</p>
            {% endif %}
        {% endif %}

    {% else %}

        <div class="back_link gift_backlink">
            <i class="fa fa-arrow-circle-left"></i>
            {{ html.link('Edit Details', (isReward ? {
                    'controller': 'Rewards',
                    'action': 'compose'
                } : {
                    'controller': 'Gifts',
                    'action': 'compose',
                    'id': player.steamid
            })) }}
        </div>

    {% endif %}

    {{ form.create((isReward) ? 'RewardDetail' : 'GiftDetail', {
        'inputDefaults': {
            'label': false,
            'div': false,
            'required': false
        },
        'url': (isReward ? {
                'controller': 'Rewards',
                'action': (composing) ? 'package' : 'send'
            } : {
                'controller': 'Gifts',
                'action': (composing) ? 'package' : 'send',
                'id': player.steamid
        }),
        'class': 'cf'
    }) }}

    {% if not composing %}

        <h2 class="gift_subheading">
            {{ isReward ? 'Reward' : 'Gift' }} value
        </h2>
        <div class="gift_value">
            {% if isReward %}
                Each: {{ fn.currency(totalValue, {'big': true, 'wrap': true}) }}
                <br>
                Total: {{ fn.currency(totalValue * recipients|length, {'big': true, 'wrap': true}) }}
            {% else %}
                {{ fn.currency(totalValue, {'big': true, 'wrap': true}) }}
            {% endif %}
        </div>

    {% endif %}

    <h2 class="gift_subheading">Recipient{{ (isReward) ? (composing ? '(s)' : 's: ' ~ recipients|length) : '' }}</h2>

    {% if isReward %}

        {% if not composing %}

            {% for recipient in recipients %}

                <div class="gift_recipient">
                    {{ fn.player(_context, players[recipient]) }}
                </div>

            {% endfor %}

            {% if failedRecipients %}

                <h2 class="gift_subheading">Failed to determine: {{ failedRecipients|length }}</h2>

                {% for recipient in failedRecipients %}

                    <div class="gift_recipient_failed">
                        {{ recipient }}
                    </div>

                {% endfor %}

            {% endif %}

        {% else %}

            {{ form.input('Reward.recipients', {
                'type': 'textarea',
                'placeholder': 'Enter a list of Steam IDs or profile URLs...',
                'class': 'gift_recipients_input',
                'value': recipientText
            }) }}

        {% endif %}

    {% else %}

        <div class="gift_recipient">
            {{ fn.player(_context, player) }}
        </div>

    {% endif %}



    <h2 class="gift_subheading">Contents</h2>

    <table class="gift_item_table {{ composing ? '' : 'gift_preview' }}">

        <tr>
            <th class="gift_item_heading">Item</th>
            {% if isReward %}
                <th>Value</th>
            {% elseif composing %}
                <th>You Have</th>
            {% endif %}
            <th class="quantity">Quantity</th>
        </tr>

        {% for item in sortedItems if
            (composing ?
                (isReward or userItems[item.item_id] > 0) :
                (isReward and item.item_id == 0 and credit or details[item.item_id] > 0))
        %}

            {% set quantity = (details) ? details[item.item_id] : '' %}

            {% if item.item_id == 0 %}
                {% set quantity = credit %}
            {% endif %}

            {% set userQuantity = userItems[item.item_id] %}

            <tr>
                <td class="gift_item_name">
                    {{ html.image("items/#{item.short_name}.png", {
                        'url': {'controller': 'Items', 'action': 'view', 'id': item.short_name},
                        'class': 'gift_item_image'
                    }) }}
                    {{ html.link(item.name, {'controller': 'Items', 'action': 'view', 'id': item.short_name}) }}
                </td>
                {% if isReward %}
                    <td class="gift_item_price">
                        {{ fn.currency(item.price) }}
                    </td>
                {% elseif composing %}
                    <td class="gift_item_available">
                        {{ userQuantity }}
                    </td>
                {% endif %}
                <td class="gift_item_input quantity">
                    {% if composing %}
                        {% if item.item_id == 0 %}
                            {{ form.input('Reward.credit', {
                                'min': 0,
                                'value': quantity
                            }) }}
                        {% else %}
                            {{ form.hidden(loop.index0 ~ '.item_id', {'value': item.item_id}) }}
                            {{ form.input(loop.index0 ~ '.quantity', {
                                'min': 0,
                                'max': userQuantity,
                                'value': quantity
                            }) }}
                        {% endif %}
                    {% else %}
                        {{ quantity }}
                    {% endif %}
                </td>
            </tr>

        {% endfor %}

    </table>

    <h2 class="gift_subheading">Message {{ isReward ? '' : '(Optional)' }}</h2>

    {{ form.input((isReward ? 'Reward' : 'Gift') ~ '.message', {
        'class': 'gift_message_input',
        'type': 'textarea',
        'maxlength': 100,
        'placeholder': composing ? ('Write a message to the recipient' ~ (isReward ? '(s)' : '') ~ '...') : '',
        'value': message,
        'disabled': composing ? '' : 'disabled'
    }) }}

    <span class="gift_message_chars"></span>

    {% if not isReward %}
        <div class="gift_anonymous">
            {{ form.input('Gift.anonymous', {
                'label': 'send anonymously',
                'checked': anonymous,
                'disabled': (composing) ? '' : 'disabled',
                'class': 'gift_anonymous_checkbox'
            }) }}
        </div>
    {% endif %}

    {% set disableSubmit = (isReward and not composing and not recipients|length) %}

    {{ form.submit((composing ? 'Package ' : 'Send ') ~ (isReward ? 'Reward' : 'Gift'), {
        'div': false,
        'class': 'btn-primary ' ~ (disableSubmit ? 'disabled' : ''),
        'id': 'gift_package_button',
        'disabled': disableSubmit
    }) }}

    {{ form.end() }}

    {% include 'Common/activity.inc.tpl' with {
        'title': 'Recent ' ~ (isReward ? 'Rewards' : 'Gifts')
    } %}

{% endblock %}