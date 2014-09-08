{% if cache %}

	<p class="cache_total">{{ cache|length }} players in cache</p>

	<ul class="cache_list">

		{% for player in cache %}

			<li class="cache_entry">
				{% include 'SteamPlayerCache/single.inc.tpl' %}
			</li>

		{% endfor %}

	</ul>

{% else %}

	<p><strong>The cache is currently empty!</strong></p>

{% endif %}