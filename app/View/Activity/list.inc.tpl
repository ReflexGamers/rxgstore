
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
                    'maxColumns': 7
                } %}


            {% elseif activity.Gift %}

                {% set gift = activity.Gift %}
                {% set sender = players[gift.sender_id] %}
                {% set recipient = players[gift.recipient_id] %}

                <div class="activity_player sender">
                    {% if gift.anonymous %}
                        {% if access.check('Rewards') %}
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
                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': activity.GiftDetail,
                    'maxColumns': 6
                } %}

                {% if gift.message %}
                    <div class="activity_message">
                        "{{ gift.message|e }}"
                    </div>
                {% endif %}


            {% elseif activity.Reward %}

                {% set reward = activity.Reward %}
                {% set recipients = activity.RewardRecipient %}
                {% set num_recipients = recipients|length %}

                <div class="activity_player sender">
                    <span class="item_browse_reward_sender">Reflex Gamers</span>
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
                            {{ fn.player(_context, players[recipient]) }}
                        </div>

                    {% endfor %}

                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': activity.RewardDetail,
                    'maxColumns': 6
                } %}

                {% if reward.message %}
                    <div class="activity_message">
                        "{{ reward.message|e }}"
                    </div>
                {% endif %}


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
                {% set showShipmentHandler = access.check('Stock', 'update') %}

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
                        {{ fn.player(_context, players[shipment.user_id]) }}
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
