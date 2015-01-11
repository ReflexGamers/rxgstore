{% extends 'Common/base.tpl' %}

{% set title = 'Steam Data Cache' %}
{% set scripts = ['common', 'admin-cache'] %}

{% block content %}

    <p>When a part of this site attempts to get Steam data about players, it calls the Steam API to get their information. We automatically cache that information for up to <strong>{{ cacheDuration }}</strong> hours per player.</p>

    <p>If a player's cached information has expired by the time their information is requested, it will be fetched again and updated in the cache for later use. Expired player information is automatically deleted when any part of the cache is updated.</p>

    <p>Depending on your permissions, this page will allow you to not only view cached player data, but to also manually refresh or delete any individual player or all players.</p>

    <p>If you clear players from the cache manually, then when their information is requested, the Steam API will be called, resulting in a longer page load time for the first attempt.</p>

    <p>This page is mostly just for cache debugging purposes. There is no reason to manually refresh a player from the cache unless you know that person's steam profile name or avatar has changed since it was last fetched.</p>

    <p>{{ html.link('Click here', {'controller': 'Admin', 'action': 'viewlog', 'name': 'steam'}) }} to view the Steam cache log.</p>

    {% if cache %}
        <div id="cache_batch_actions">
            {% if access.check('Cache', 'update') %}
                <input type="button" id="cache_refreshall" class="btn-success" value="Refresh All Players" data-href="{{ html.url({'action': 'refresh_all'}) }}" />
            {% endif %}
            {{ html.image('misc/ajax-loader.gif', {
                'class': 'ajax-loader',
                'id': 'cache_loading'
            }) }}
        </div>
    {% endif %}

    <div id="cache_content">
        {% include 'SteamPlayerCache/list.inc.tpl' %}
    </div>

{% endblock %}