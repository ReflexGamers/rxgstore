{% extends 'Common/base.tpl' %}

{% set title = 'What\'s New' %}

{% block content %}

    <div class="page_heading">{{ title }}</div>

    <p>The brand new RXG Store 2.0 is here! Take a look at all the new features below and try them out for yourself around the site!</p>

    <div class="whatsnew_item">
        <h2 class="whatsnew_title">Ratings & Reviews</h2>
        <div class="whatsnew_content">
            <p class="whatsnew_description">Want to tell other shoppers how much you love cookies? You can now rate and review all items you have purchased!</p>
            {{ html.image('whatsnew/review.png', {
                'class': 'whatsnew_img'
            }) }}
            <p class="whatsnew_description">After submitting a review, the number next to your picture will represent how many of that item you have purchased. Buy more to get your review seen first!</p>
        </div>
    </div>

    <div class="whatsnew_item">
        <h2 class="whatsnew_title">Send Gifts</h2>
        <div class="whatsnew_content">
            <p class="whatsnew_description">Feeling generous? Send someone a gift with items in your inventory. Are you a secret admirer? Don't worry, check the box to send it anonymously.</p>
            {{ html.image('whatsnew/gift.png', {
                'class': 'whatsnew_img'
            }) }}
        </div>
    </div>

    <div class="whatsnew_item">
        <h2 class="whatsnew_title">Earn Rewards</h2>
        <p class="whatsnew_description">Want free items? Participate in events such as our CS:GO scrims to receive rewards! Note: Actual rewards may vary per event.</p>
        {{ html.image('whatsnew/reward.png', {
            'class': 'whatsnew_img'
        }) }}
    </div>

    <div class="whatsnew_item">
        <h2 class="whatsnew_title">Profiles</h2>
        <p class="whatsnew_description">Click on any player's name to view that person's profile. Profiles show the player's current CASH, items, activity and more!</p>
    </div>

    <div class="whatsnew_item">
        <h2 class="whatsnew_title">Stats</h2>
        <div class="whatsnew_content"></div>
    </div>

{% endblock %}