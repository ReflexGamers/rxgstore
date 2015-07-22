{% extends 'Common/base.tpl' %}

{% set title = 'Steam Data Cache' %}
{% set scripts = ['highcharts', 'highcharts-3d', 'charts', 'admin-cache'] %}

{% block content %}

    <h2>Why Caching?</h2>

    <p>The Store has to query the Steam API in order to get player names and avatars, but it is slow and often times out completely, so we cache Steam data when it is fetched, and we also precache the data of players in RXG servers just in case they use <code>!store</code>. This has proven to greatly decrease load times.</p>

    <h2>How It Works</h2>

    <p>When a page on the Store is requested, all players needed for the screen are batched into a list and then looked up in the cache. If all of the desired players are present in the cache, the cached data is used. Otherwise, the Steam API is queried for all of the desired players, not just the missing ones.</p>

    <p>You would think refreshing more players than needed for the page would be bad, but it helps keep the other players fresh while having little to no impact on speed because we have found that the amount of players in a single Steam API call is nearly insignificant to performance compared to the number of calls we make.</p>

    <h3>Fresh vs Expired</h3>

    <p>Steam data for a player is considered fresh or valid for <strong>{{ cacheDuration }}</strong> hours (configurable) after being fetched from the Steam API, after which point it is considered expired. We will still use expired data if we have it to prevent slowdowns, but it is automatically pruned once a week to prevent extreme staleness.</p>

    <h3>Automatic Refreshing</h3>

    <p>There are many places in the Store that we show player information, particularly in regard to purchase history. To prevent slowdowns and maintain fresh data, we automatically refresh Steam data every <strong>4</strong> hours for all RXG members, reward recipients and players who have made a purchase in the past year.</p>

    <h3>Precaching</h3>

    <p>Automatic refreshing covers players who have already used the store, but it doesn't help with newcomers. To solve this problem, we preemptively request Steam data for all players in RXG store-enabled servers every <strong>5</strong> minutes.</p>

    {% if access.check('Debug') %}
        <p>You can configure the Steam Cache parameters in the rxgstore.php config, but the frequency of the automatic cron jobs is configured on the cron tab.</p>

        <h2>Command Line Interface</h2>

        <p>The CLI is mainly used in cron jobs but can also be used standalone. It provides the following functions:</p>

        <p><code>./cake steamCache info</code> - Prints the number of total, valid, expired and precached players.</p>

        <p><code>./cake steamCache clear</code> - Deletes all cached data.</p>

        <p><code>./cake steamCache prune</code> - Prunes all expired players.</p>

        <p><code>./cake steamCache precache_ingame</code> - Precaches all ingame players.</p>

        <p><code>./cake steamCache refresh_known</code> - Refreshes all "known" players.</p>

        <p><code>./cake steamCache refresh_expired</code> - Refreshes all expired players.</p>
    {% endif %}

    <p>{{ html.link('Click here', {'controller': 'Admin', 'action': 'viewlog', 'name': 'steam'}) }} to view the Steam cache log.</p>

    {% if cache %}
        <div id="cache_batch_actions">
            {% if access.check('Cache', 'delete') %}
                <input type="button" id="cache_clear_expired" class="btn-danger" value="Prune Expired Players" data-href="{{ html.url({'action': 'clear_expired'}) }}" />
            {% endif %}
            {{ html.image('misc/ajax-loader.gif', {
                'class': 'ajax-loader',
                'id': 'cache_loading'
            }) }}
        </div>
    {% endif %}

    <div id="cached_chart" class="chart_container" data-href="{{ html.url({
        'controller': 'SteamPlayerCache',
        'action': 'totals_cached',
        'ext': 'json'
    }) }}">
        <div class="chart_inner"></div>
    </div>

    <div id="cache_content">
        {% include 'SteamPlayerCache/list.inc.tpl' %}
    </div>

{% endblock %}