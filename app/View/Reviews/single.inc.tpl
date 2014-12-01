{% import 'Common/functions.tpl' as fn %}

{% set player = players[review.user_id] %}

<div class="review_image">
    {% if displayType == 'user' %}

        {{ html.image("items/#{item.short_name}.png", {
            'url': {'controller': 'Items', 'action': 'view', 'id': item.short_name}
        }) }}

    {% else %}

        {{ html.image(player.avatarmedium, {
            'url': {'controller': 'Users', 'action': 'profile', 'id': player.steamid}
        }) }}

    {% endif %}

    {% if review.quantity %}
        <div class="review_quantity" title="This player purchased {{ review.quantity }} of this item">
            {{ review.quantity }}
        </div>
    {% endif %}
</div>


<div class="review_details">

    {% if review.created %}

        <div class="review_date">
            {{ fn.time(_context, review.created, review.modified) }}
        </div>

    {% endif %}


    {% if (user.user_id == review.user_id or access.check('Reviews', 'update')) and not isEditMode %}

        <div class="review_actions">
            <i class="fa fa-pencil"></i>
            <a class="review_edit" href="{{ html.url({
                'controller': 'Reviews',
                'action': 'edit',
                'type': displayType,
                'id': review.review_id
            }) }}">edit</a>

            {% if access.check('Reviews', 'update') and not isEditMode %}

                <br/>
                <i class="fa fa-trash"></i>
                <a class="review_delete" href="{{ html.url({
                    'controller': 'Reviews',
                    'action': 'delete',
                    'type': displayType,
                    'id': review.review_id
                }) }}">delete</a>

            {% endif %}
        </div>

    {% endif %}




    <div class="review_name">
        {% if displayType == 'user' %}

            {{ html.link(
                item.name,
                {'controller': 'Items', 'action': 'view', 'id': item.short_name}
            ) }}

        {% else %}

            {{ html.link(
                player.name,
                {'controller': 'Users', 'action': 'profile', 'id': player.steamid}
            ) }}

        {% endif %}
    </div>

    <div class="review_rating">
        {% set enableReviewRating = (isCreateMode or user.user_id == review.user_id) %}

        <div id="{{ enableReviewRating ? "review_rateit" }}" class="rateit bigstars smaller" data-rateit-value="{{ review.rating / 2 }}"
             data-rateit-starwidth="24" data-rateit-starheight="24"
             data-rateit-resetable="false"
             data-rateit-readonly="{{ enableReviewRating ? '' : 'true' }}"
             data-href="{{ html.url({'controller': 'Ratings', 'action': 'rate', 'id': item.item_id}) }}"></div>
    </div>

    {% if isEditMode %}

        {{ form.create('Review', {'url': {
            'controller': 'Reviews',
            'action': 'save',
            'type': displayType,
            'id': item.item_id
        }}) }}

        {% if not isCreateMode %}
            {{ form.hidden('review_id', {'value': review.review_id}) }}
        {% endif %}

        {{ form.input('content', {
            'label': false,
            'div': false,
            'class': 'review_input',
            'type': 'textarea',
            'maxlength': 200,
            'placeholder': 'Write about your experience with this item...',
            'value': review.content ?: ''
        }) }}

        <span class="review_chars"></span>

        <input type="submit" class="review_submit btn-primary" value="{{ isCreateMode ? 'Submit' : 'Save' }} Review">

        {% if not isCreateMode %}
            <input type="button" class="review_cancel btn-secondary" value="Cancel" data-href="{{ html.url({
                'controller': 'Reviews',
                'action': 'view',
                'type': displayType,
                'id': review.review_id
            }) }}">
        {% endif %}

        {{ html.image(
            'misc/ajax-loader.gif',
            {'class': 'ajax-loader review_loading'}
        ) }}

        {{ form.end }}

    {% else %}

        <div class="review_content">
            "{{ review.content|e }}"
        </div>

    {% endif %}

</div>
