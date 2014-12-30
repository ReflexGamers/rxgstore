{% import 'Common/functions.tpl' as fn %}

<div class="cache_time">
    {% if player.precached %}
        <i class="fa fa-bolt player_cached player_precached" title="Steam data precached"></i> pre
    {%- endif -%}
    cached {{ fn.time(_context, player.cached) }}
</div>
<div class="cache_actions">
    {% if access.check('Cache', 'delete') %}
        <a class="cache_clear" href="{{ html.url({'action': 'clear', 'id': player.steamid}) }}">Remove</a> |
    {% endif %}
    {% if access.check('Cache', 'update') %}
        <a class="cache_refresh" href="{{ html.url({'action': 'refresh', 'id': player.steamid}) }}">Refresh</a>
    {% endif %}
</div>
{{ fn.player(_context, player) }}