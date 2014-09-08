
{% if userCanRate and not review.content %}

	<div class="review" id="review_compose">
		{% include 'Reviews/compose.inc.tpl' %}
	</div>

{% endif %}


{% for review in reviews if review.content %}

	{% if displayType == 'user' %}

		{% set item = itemsIndexed[review.item_id] %}

	{% endif %}

	{% set player = user.user_id == review.user_id ? user : players[review.user_id] %}

	<div class="review" id="{{ user.user_id == review.user_id ? 'review_compose' : '' }}">
		{% include 'Reviews/single.inc.tpl' %}
	</div>

{% endfor %}
