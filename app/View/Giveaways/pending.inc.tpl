<div class="item_browse_gifts">

    {% for data in giveaways %}

        {% set promo = data.Giveaway %}
        {% set detail = data.GiveawayDetail %}

        <div class="item_browse_gift">

            <div class="item_browse_gift_inner">

                <div class="activity_player sender">
                    <span class="item_browse_reward_sender">Reflex Gamers</span>
                </div>

                <div class="activity_date">
                    offers you the
                </div>
                <div class="giveaway_pending_name activity_message">
                    {{ promo.name }}
                </div>

                {% include 'Items/list.inc.tpl' with {
                    'quantity': detail,
                    'maxColumns': 5
                } %}

                <div class="item_browse_gift_actions">
                    <input type="button" value="Click to Claim" class="item_browse_gift_accept btn-primary" data-href="{{ html.url({
                        'controller': 'Giveaways',
                        'action': 'claim',
                        'id': promo.giveaway_id
                    }) }}">
                    {{ html.image('misc/ajax-loader.gif', {
                        'class': 'ajax-loader item_browse_gift_loading'
                    }) }}
                </div>

            </div>

        </div>

    {% endfor %}

</div>
