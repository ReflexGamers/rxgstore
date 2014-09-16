{% import 'Common/functions.tpl' as fn %}

{% if syncResult %}

	<p class="admin_updated">
		Sync Complete<br>
		{{ syncResult.added|length }} added{{ syncResult.added|length > 0 ? ': ' ~ syncResult.added|join(', ') }}<br>
		{{ syncResult.updated|length }} updated{{ syncResult.updated|length > 0 ? ': ' ~ syncResult.updated|join(', ') }}<br>
		{{ syncResult.removed|length }} removed{{ syncResult.removed|length > 0 ? ': ' ~ syncResult.removed|join(', ') }}

	</p>

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

		<li class="admin_entry">
			{{ fn.player(_context, player) }}
			{% set adminName = admin.name|lower() %}
			{% set playerName = fn.stripTag(player)|lower() %}
			{{ adminName in playerName or playerName in adminName ? '' : "(#{admin.name})" }}
		</li>

	{% endfor %}

</ul>