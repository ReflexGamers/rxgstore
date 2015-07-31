{% extends 'Common/base.tpl' %}

{% set title = 'Search for Players' %}

{% block content %}

    <p>Here you can find players in our database by name or Steam ID (many formats supported). This includes all players who have made purchases or recently played in store-powered servers.</p>

    <form method="get">
        <input type="text" name="term" placeholder="Search for players..." class="search_box">
        <input type="submit" class="btn-primary btn-search" value="Search">
    </form>

    <section id="search_results">
        {% include '/SteamPlayerCache/results.inc.tpl' %}
    </section>

{% endblock %}
