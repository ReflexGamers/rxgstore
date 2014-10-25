{% import 'Common/functions.tpl' as fn %}

{% for item in serverItems %}

    {% set rating = ratings[item.item_id] %}

    <li class="browse_item cf">
        {{ html.image("items/#{item.short_name}.png", {
            'url': {'action': 'view', 'id': item.short_name},
            'class': 'browse_item_image'
        }) }}
        <div class="browse_item_details">
            <div class="browse_rating">
                <div class="browse_price">
                    {#{{ fn.currency(item.price) }}#}
                </div>
                <div class="rateit bigstars smaller" data-rateit-value="{{ rating.average / 2 }}"
                     title="{{ rating ? "out of #{rating.count} rating" ~ (rating.count > 1 ? 's' : '') : '' }}"
                     data-rateit-starwidth="24" data-rateit-starheight="24"
                     data-rateit-resetable="false"
                     data-rateit-readonly="true"
                     data-href="{{ html.url({'controller': 'Ratings', 'action': 'rate', 'id': item.item_id}) }}"></div>
                    <div class="browse_rating_count">
                        {% if rating %}
                            <strong>{{ rating.average/2|round(1) }}</strong>/5 ({{ "#{rating.count} rating" ~ (rating.count > 1 ? 's' : '') }})
                        {% else %}
                            not yet rated
                        {% endif %}
                    </div>
            </div>
            {{ html.link(item.name, {'action': 'view', 'id': item.short_name}, {'class': 'browse_item_link'}) }}
            {% if item.features %}
                <ul class="browse_feature_list">
                    {% for feature in item.features %}
                        <li class="browse_feature">{{ feature }}</li>
                    {% endfor %}
                </ul>
            {% endif %}
        </div>
    </li>

{% endfor %}
