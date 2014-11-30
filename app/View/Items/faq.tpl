{% extends 'Common/base.tpl' %}

{% set title = 'RXG Store FAQ' %}

{% block content %}

    <div class="page_heading">Frequently Asked Questions</div>

    <div class="faq_item">
        <div class="faq_question">How do I use items?</div>
        <div class="faq_answer">The simplest way is to type <code>!useitem</code> in chat and then select the item you want from the menu. For convenience, you can bind <code>useitem</code> to a key.</div>
    </div>

    <div class="faq_item">
        <div class="faq_question">Some of my items don't show up... what's wrong?</div>
        <div class="faq_answer">Some of the items in the Store are only available in specific games or servers that we host. For instance, the {{ html.link('chicken', {'controller': 'Items', 'action': 'view', 'id': 'chicken'}) }} is not available in our TF2 servers. Look at an item's listing page to see where it can be used, or select a server on the main {{ html.link('store page', {'controller': 'Items', 'action': 'index'}) }} to narrow down the list to only items available in that server.</div>
    </div>

    <div class="faq_item">
        <div class="faq_question">How do I earn <i class="currency-big"></i>CASH?</div>
        <div class="faq_answer">CASH occasionally spawns when players are killed. In TF2, you simply have to run over it to pick it up but in CS:GO you have to press the <code>+use</code> key (default: E) while looking at it. You can also {{ html.link('buy CASH', {'controller': 'PaypalOrders', 'action': 'addfunds'}) }}.</div>
    </div>

    <div class="faq_item">
        <div class="faq_question">How can I earn free items?</div>
        <div class="faq_answer">Sometimes leadership rewards players or members with free items for attending events such as our weekly CS:GO Scrims. Participate in an eligible event to receive a reward.</div>
    </div>

    <div class="faq_item">
        <div class="faq_question">Where do I give feedback and suggest new items?</div>
        <div class="faq_answer">Submit your feedback on our forums at <a href="http://reflex-gamers.com">www.reflex-gamers.com</a>. You may also review an item on its respective listing page after purchasing it, but if you think an item is imbalanced, that feedback would be best heard on our forums.</div>
    </div>

    <div class="faq_item">
        <div class="faq_question">How do I get that cool <span class="member-tag">rxg</span> tag next to my name?</div>
        <div class="faq_answer">That is the official member tag. To become a member, register at <a href="http://reflex-gamers.com">www.reflex-gamers.com</a> and then apply in the <a href="http://reflex-gamers.com/forumdisplay.php?f=15">Member Applications</a> forum. For the Store to recognize you as a member, your Steam account must be linked on the forums.</div>
    </div>

    <div class="faq_item">
        <div class="faq_question">How do I make a key or mouse button use a certain item?</div>
        <div class="faq_answer">To bind an item to a key, enter <code>bind KEY "useitem ITEM"</code> in console; replace <code>KEY</code> with the key you want to bind and <code>ITEM</code> with the abbreviated name of the item found on this site. For example, <code>bind x "useitem cookie"</code> would bind the {{ html.link('cookie', {'controller': 'Items', 'action': 'view', 'id': 'cookie'}) }} item to the <code>x</code> key. You can also find the short name of an item in your inventory by typing <code>!items</code> in chat.</div>
    </div>

    <div class="faq_item">
        <div class="faq_question">The item I want is out of stock... when will it be available?</div>
        <div class="faq_answer">Shipments typically arrive once or twice a week. We have had some instances of Space Pirates attacking our delivery ships in the past so we cannot make any promises about shipment dates.</div>
    </div>

{% endblock %}