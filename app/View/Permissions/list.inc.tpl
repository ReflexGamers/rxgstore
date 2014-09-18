{% import 'Common/functions.tpl' as fn %}

{% if syncResult %}

	<p class="admin_updated">
		Sync Complete: {{ syncResult.added|length }} added, {{ syncResult.updated|length }} updated, {{ syncResult.removed|length }} removed.

	</p>

{% endif %}

{% if permSyncLog %}
	Sync log:<br>
	<textarea class="log_output">{{ permSyncLog }}</textarea>
{% endif %}

<ul class="admin_list">

	{% for member in members %}

		{% set player = players[member.user_id] %}

		{% if previousRank is empty or previousRank != member.rank %}
			</ul>
			<h2 class="page_subheading">
				{{ member.rank }}
			</h2>
			<ul class="admin_list">
		{% endif %}

		{% set previousRank = member.rank %}

		<li class="admin_entry">
			{{ fn.player(_context, player) }}
			{% set memberName = member.name|lower() %}
			{% set playerName = fn.stripTag(player)|lower() %}
			{{ memberName in playerName or playerName in memberName ? '' : "(#{member.name})" }}
		</li>

	{% endfor %}

</ul>