{% import 'Common/functions.tpl' as fn %}

{% set player = players[entry.user_id] %}

<td class="quickauth_player">
    <div class="quickauth_flags">
        {% if entry.cached %}
            <i class="fa fa-bolt player_cached {{ entry.cached == 2 ? 'player_precached' : '' }}" title="Steam data {{ entry.cached == 2 ? 'pre' : '' }}cached"></i>
        {% endif %}
        <div class="quickauth_flag {{ entry.redeemed ? 'redeemed' : 'unredeemed' }}" title="Token {{ entry.redeemed ? 'redeemed' : 'never redeemed' }}">
            {% if entry.is_member %}
                <span class="quickauth_member" title="Authenticated as a Member">M</span>
            {% endif %}
        </div>
    </div>
    {{ fn.player(_context, player) }}
</td>

<td class="quickauth_server">
    {% set server = servers[entry.server] %}
    {% if server %}
        {#{{ html.link(server.name, {#}
        {#'controller': 'servers',#}
        {#'action': 'view',#}
        {#'name': server.short_name#}
        {#}) }}#}
        {{ server.name }}
    {% else %}
        <span class="quickauth_server_unknown">
            {{ entry.server }}
        </span>
    {% endif %}
</td>

<td class="quickauth_time">
    {{ fn.time(_context, entry.date) }}
</td>