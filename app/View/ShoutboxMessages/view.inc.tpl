{% import 'Common/functions.tpl' as fn %}

<input type="hidden" id="shoutbox_updateurl" value="{{ html.url({'controller': 'ShoutboxMessages', 'action': 'view', 'time': theTime}) }}">
<input type="hidden" id="shoutbox_post_cooldown" value="{{ shoutPostCooldown ?: 60 }}">
<input type="hidden" id="shoutbox_update_interval" value="{{ shoutUpdateInterval ?: 60 }}">

{% for message in messages|reverse %}
    <li class="shoutbox_item {{ user.user_id == message.user_id ? 'player' : '' }}">
        <span class="shoutbox_timestamp">{{ fn.formatTime(_context, message.date) }}</span>
        {% if access.check('Chats', 'delete') %}
            <span class="shoutbox_actions">
                {{ html.link('remove', {
                    'controller': 'ShoutboxMessages',
                    'action': 'delete',
                    'id': message.shoutbox_message_id
                }, {
                    'class': 'shoutbox_delete'
                }) }}
            </span>
        {% endif %}
        <span class="shoutbox_username">
            {{ fn.profile(_context, players[message.user_id]) }}:
        </span>
        <span class="shoutbox_message">{{ message.content|e }}</span>
    </li>
{% endfor %}

{% if userCantPost %}
    <li class="shoutbox_item error">
        You recently posted a message. Please wait before posting again.
    </li>
{% endif %}