
{% set loader = 'activity_page_loading' %}
{% if not pageModel %}
    {% set pageModel = 'Activity' %}
{% endif %}

{{ paginator.options({
    'update': '#activity_content',
    'url': activityPageLocation,
    'before': js.get('#' ~ loader).effect('fadeIn'),
    'complete': 'rxg.onActivityPageLoad()'
}) }}

<div id="activity_list">

    {% for activity in activities %}

        <div class="activity_item">


            {% if activity.Order %}

                {% set order = activity.Order %}

                <div class="activity_player">
                    {{ fn.player(_context, players[order.user_id]) }}
                </div>

                <div class="activity_date">
                    spent {{ fn.currency(order.subTotal + order.shipping, {'wrap': true, 'big': true}) }} ({{ fn.formatTime(_context, order.date) }})
                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': activity.OrderDetail,
                    'maxColumns': 6
                } %}

            {% elseif activity.Liquidation %}

                {% set liquidation = activity.Liquidation %}

                <div class="activity_player">
                    {{ fn.player(_context, players[liquidation.user_id]) }}
                </div>

                <div class="activity_date">
                    returned items for {{ fn.currency(liquidation.total, {'wrap': true, 'big': true}) }} ({{ fn.formatTime(_context, liquidation.date) }})
                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': activity.LiquidationDetail,
                    'maxColumns': 6
                } %}

            {% elseif activity.Gift %}

                {% set gift = activity.Gift %}
                {% set sender = players[gift.sender_id] %}
                {% set recipient = players[gift.recipient_id] %}

                <div class="activity_player sender">
                    {% if gift.anonymous %}
                        {% if access.check('Rewards', 'create') %}
                            <div class="activity_gift_anonymous_sender">
                                {{ fn.player(_context, sender) }}
                            </div>
                        {% else %}
                            <span class="activity_gift_anonymous">Anonymous</span>
                        {% endif %}
                    {% else %}
                        {{ fn.player(_context, sender) }}
                    {% endif %}
                </div>

                <div class="activity_date">
                    sent a gift <i class="fa fa-gift icon_gift"></i> {{ fn.formatTime(_context, gift.date) }} to
                </div>

                <div class="activity_player recipient">
                    {{ fn.player(_context, recipient) }}
                    {% if access.check('Rewards', 'create') %}
                        {% if gift.accepted %}
                            <i class="fa fa-check icon_gift_accepted" title="Accepted"></i>
                        {% else %}
                            <i class="fa fa-clock-o icon_gift_pending" title="Pending"></i>
                        {% endif %}
                    {% endif %}
                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': activity.GiftDetail,
                    'maxColumns': 6
                } %}

                {% if gift.message %}
                    <div class="activity_message">
                        <i class="fa fa-quote-left icon_gift_quote"></i> {{ gift.message|e }} <i class="fa fa-quote-right icon_gift_quote"></i>
                    </div>
                {% endif %}


            {% elseif activity.Reward %}

                {% set reward = activity.Reward %}
                {% set recipients = activity.RewardRecipient %}
                {% set num_recipients = recipients|length %}

                <div class="activity_player sender">
                    <span class="item_browse_reward_sender">Reflex Gamers</span>
                    {% if access.check('Rewards', 'create') %}
                        <div class="activity_reward_sender">
                            {% if reward.sender_id == 0 %}
                                {{ html.image('/img/misc/robots.png') }}
                                Robots
                                {{ html.image('/img/misc/robots.png') }}
                            {% else %}
                                {{ fn.player(_context, players[reward.sender_id]) }}
                            {% endif %}
                        </div>
                    {% endif %}
                </div>

                <div class="activity_date">
                    sent a reward <i class="fa fa-gift icon_reward"></i> {{ fn.formatTime(_context, reward.date) }} to
                    {% if num_recipients > 3 %}
                        <a class="recipient_expand">{{ num_recipients }} players</a>
                    {% endif %}
                </div>

                <div class="activity_reward_recipients {{ num_recipients > 3 ? 'hidden' : '' }}">

                    {% for recipient in recipients %}

                        <div class="activity_player recipient">
                            {{ fn.player(_context, players[recipient.recipient_id]) }}
                            {% if access.check('Rewards', 'create') %}
                                {% if recipient.accepted %}
                                    <i class="fa fa-check icon_gift_accepted" title="Accepted"></i>
                                {% else %}
                                    <i class="fa fa-clock-o icon_gift_pending" title="Pending"></i>
                                {% endif %}
                            {% endif %}
                        </div>

                    {% endfor %}

                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': activity.RewardDetail,
                    'maxColumns': 6
                } %}

                {% if reward.message %}
                    <div class="activity_message">
                        {{ reward.message|e }}
                    </div>
                {% endif %}


            {% elseif activity.GiveawayClaim %}

                {% set claim = activity.GiveawayClaim %}
                {% set giveaway = activity.Giveaway %}

                <div class="activity_player">
                    {{ fn.player(_context, players[claim.user_id]) }}
                </div>

                <div class="activity_date">
                    accepted a giveaway {{ fn.formatTime(_context, claim.date) }}
                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': activity.GiveawayClaimDetail,
                    'maxColumns': 6
                } %}

                <div class="activity_message">
                    {{ giveaway.name }}
                </div>


            {% elseif activity.PaypalOrder %}

                {% set order = activity.PaypalOrder %}

                <div class="activity_player">
                    {{ fn.player(_context, players[order.user_id]) }}
                </div>

                <div class="activity_date">
                    spent <strong>{{ fn.realMoney(order.amount) }}</strong> and got <strong>{{ fn.currency(order.credit, {'wrap': true, 'big': true}) }}</strong> ({{ fn.formatTime(_context, order.date) }})
                </div>

                {% include 'Items/cashlist.inc.tpl' with {
                    'amount': order.credit,
                    'maxColumns': 6
                } %}


            {% elseif activity.Review %}

                {% set review = activity.Review %}
                {% set rating = activity.Rating %}
                {% set item = items[rating.item_id] %}

                <div class="activity_player">
                    {{ fn.player(_context,  players[rating.user_id]) }}
                </div>

                <div class="activity_date">
                    wrote a review {{ fn.formatTime(_context, review.date) }} about the
                </div>

                <div class="activity_review">
                    {{ html.image("items/#{item.short_name}.png", {
                        'url': {
                            'controller': 'Items',
                            'action': 'view',
                            'id': item.short_name
                        }
                    }) }}
                    {{ html.link(item.name, {
                        'controller': 'Items',
                        'action': 'view',
                        'id': item.short_name,
                    }, {
                        'class': 'activity_review_item'
                    }) }}
                </div>

                <div class="activity_message">
                    <i class="fa fa-quote-left icon_review_quote"></i> {{ review.content|e }} <i class="fa fa-quote-right icon_review_quote"></i>
                </div>

                <div class="activity_review_rating">
                    <div class="rateit bigstars" data-rateit-value="{{ rating.rating / 2 }}"
                         data-rateit-starwidth="32" data-rateit-starheight="32"
                         data-rateit-resetable="false"
                         data-rateit-readonly="true"></div>
                </div>


            {% elseif activity.Shipment %}

                {% set shipment = activity.Shipment %}
                {% set showShipmentHandler = access.check('Shipments', 'create') %}

                <div class="activity_player">
                    <span class="shipment_arrived">Shipment Arrived</span>
                </div>

                <div class="activity_date">
                    {{ fn.formatTime(_context, shipment.date) }}
                    {% if showShipmentHandler %}
                        - handled by
                    {% endif %}
                </div>

                {% if showShipmentHandler %}
                    <div class="activity_player handler">
                        {% if shipment.user_id == 0 %}
                            <div class="activity_reward_sender">
                                {{ html.image('/img/misc/robots.png') }}
                                Robots
                                {{ html.image('/img/misc/robots.png') }}
                            </div>
                        {% else %}
                            {{ fn.player(_context, players[shipment.user_id]) }}
                        {% endif %}
                    </div>
                {% endif %}

                {% include 'Items/list.inc.tpl' with {
                    'quantity': activity.ShipmentDetail,
                    'maxColumns': 6
                } %}

            {% endif %}

        </div>

    {% endfor %}

</div>

{% include 'Activity/pagination.inc.tpl' %}
