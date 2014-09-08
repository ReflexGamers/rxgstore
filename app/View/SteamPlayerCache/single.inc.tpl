{% import 'Common/functions.tpl' as fn %}

<div class="cache_time">cached {{ fn.time(_context, player.cached) }}</div>
<div class="cache_actions">
	{% if access.check('Cache', 'delete') %}
		<a class="cache_clear" href="{{ html.url({'action': 'clear', 'id': player.steamid}) }}">Remove</a> |
	{% endif %}
	{% if access.check('Cache', 'update') %}
		<a class="cache_refresh" href="{{ html.url({'action': 'refresh', 'id': player.steamid}) }}">Refresh</a>
	{% endif %}
</div>
<div class="cache_avatar">
	{{ html.image(player.avatar, {
		'url': {'controller': 'users', 'action': 'profile', 'id': player.steamid}
	}) }}
</div>
<div class="cache_player">
	{{ html.link(
		player.personaname,
		{'controller': 'users', 'action': 'profile', 'id': player.steamid}
	) }}
</div>