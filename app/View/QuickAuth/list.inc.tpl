{% import 'Common/functions.tpl' as fn %}

<table class="quickauth_list">

	<tr>
		<th>Player</th>
		<th>Server</th>
		<th>Date</th>
	</tr>

	{% for entry in quickauth %}

		<tr class="quickauth_entry">

		{% set player = players[entry.user_id] %}

			<td class="quickauth_player">
				<div class="quickauth_flags">
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
					{{ html.link(server.name, {
						'controller': 'servers',
						'action': 'view',
						'name': server.short_name
					}) }}
				{% else %}
					<span class="quickauth_server_unknown">
						{{ entry.server }}
					</span>
				{% endif %}
			</td>

			{#
			<td>
				{% if access.check('QuickAuth', 'delete') %}
					<a class="quickauth_delete" href="{{ html.url({'action': 'delete', 'id': player.steamid}) }}">Remove</a>
				{% endif %}
			</td>
			#}

			<td>
				{{ fn.time(_context, entry.date) }}
			</td>


		</tr>

	{% endfor %}

</table>

</ul>
