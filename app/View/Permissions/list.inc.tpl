{% import 'Common/functions.tpl' as fn %}

{% if syncResult %}

	<p class="admin_updated">All admins updated: {{ syncResult.added|length }} added, {{ syncResult.demoted|length }} removed.</p>

{% endif %}

<ul class="admin_list">

	{% for admin in admins %}

		{% set player = players[admin.user_id] %}

		{% if previousRank is empty or previousRank != admin.rank %}
			</ul>
			<div class="admin_group">
				{{ admin.rank }}
			</div>
			<ul class="admin_list">
		{% endif %}

		{% set previousRank = admin.rank %}
		{% set playerName = player.name|replace({'rxg | ': ''}) %}

		<li class="admin_entry">
			<div class="admin_avatar">
				{{ html.image(player.avatar, {
					'url': {'controller': 'users', 'action': 'profile', 'id': player.steamid}
				}) }}
			</div>
			<div class="admin_player">
				{{ html.link(
					playerName,
					{'controller': 'users', 'action': 'profile', 'id': player.steamid}
				) }} {{ admin.name|lower() in playerName|lower() or playerName|lower() in admin.name|lower() ? '' : "(#{admin.name})" }}
			</div>
		</li>

	{% endfor %}

</ul>