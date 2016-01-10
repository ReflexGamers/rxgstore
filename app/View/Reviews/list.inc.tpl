
{% set loader = 'review_page_loading' %}
{% set pageModel = 'Rating' %}

{{ paginator.options({
    'update': '#reviews_content',
    'url': reviewPageLocation,
    'before': js.get('#' ~ loader).effect('fadeIn'),
    'complete': 'rxg.onReviewPageLoad()'
}) }}

<div class="review_list">

    {% if userCanRate and not review.content %}

        <div class="review" id="review_compose">
            {% include 'Reviews/compose.inc.tpl' %}
        </div>

    {% endif %}


    {% for review in reviews %}

        {% if displayType == 'user' %}

            {% set item = items[review.item_id] %}

        {% endif %}

        {% set player = (user.user_id == review.user_id) ? user : players[review.user_id] %}

        <div class="review">
            {% include 'Reviews/single.inc.tpl' %}
        </div>

    {% endfor %}

</div>

{% include 'Reviews/pagination.inc.tpl' %}
