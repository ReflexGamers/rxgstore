<div class="item_browse_gifts">

    {% for data in gifts %}

        {% set gift = isReward ? data.Reward : data.Gift  %}
        {% set detail = isReward ? gift.RewardDetail : data.GiftDetail %}
        {% set sender = players[gift.sender_id] %}

        <div class="item_browse_gift">

            <div class="item_browse_gift_inner">

                <div class="activity_player sender">
                    {% if isReward %}
                        <span class="item_browse_reward_sender">Reflex Gamers</span>
                    {% elseif gift.anonymous %}
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
                    sent you a {{ isReward ? 'reward' : 'gift' }} <i class="fa fa-gift {{ isReward ? 'icon_reward' : 'icon_gift' }}"></i> {{ fn.time(_context, gift.date) }}
                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': detail,
                    'maxColumns': 5
                } %}

                {% if gift.message %}
                    <div class="activity_message">
                        {% if not isReward %}
                            <i class="fa fa-quote-left icon_gift_quote"></i>
                        {% endif %}
                        {{ gift.message|e }}
                        {% if not isReward %}
                            <i class="fa fa-quote-right icon_gift_quote"></i>
                        {% endif %}
                    </div>
                {% endif %}

                <div class="item_browse_gift_actions">
                    <input type="button" value="Click to Accept" class="item_browse_gift_accept btn-primary" data-href="{{ html.url({
                        'controller': isReward ? 'Rewards' : 'Gifts',
                        'action': 'accept',
                        'id': isReward ? gift.reward_id : gift.gift_id
                    }) }}">
                    {{ html.image('misc/ajax-loader.gif', {
                        'class': 'ajax-loader item_browse_gift_loading'
                    }) }}
                </div>

            </div>

        </div>

    {% endfor %}

</div>
