{% if cache %}

	<p class="cache_total">{{ cache|length }} players in cache</p>

	<ul class="player_list striped">

		{% for player in cache %}

			<li class="player_list_item cache_entry">
				{% include 'SteamPlayerCache/single.inc.tpl' %}
			</li>

		{% endfor %}

	</ul>

{% else %}

	<p><strong>The cache is currently empty!</strong></p>

{% endif %}