{% extends 'Common/base.tpl' %}

{% set title = 'Giveaways' %}

{% block content %}

    <p>A giveaway is a set of items that we give away for free to users for a specific period of time. For example, we could give away free pumpkins to all users during the week of Halloween.</p>

    <p>Each giveaway can be restricted to only members or be open to all users. If you are eligible to claim any current giveaway, each one will be listed on the home page like a gift but with a 'Claim' button.</p>

    <p>If items are added to a giveaway after a user claims it, the remaining items will be claimable by the user again for the duration of the giveaway.</p>

    <p>
        {% if access.check('Giveaway', 'create') %}
            {{ html.link('+ Create a new Givewaway', {
                action: 'add'
            }, {
                class: 'giveaway_add'
            }) }}
        {% endif %}
    </p>

    <table class="giveaway_table">
        <tr>
            <th>Name</th>
            <th>Start</th>
            <th>End</th>
            <th>Restriction</th>
            <th>Status</th>
        </tr>
        {% for promo in giveaway %}
            <tr>
                <td class="giveaway_row_name">
                    {% if access.check('Giveaway', 'update') %}
                        {{ html.link(promo.name, {
                            action: 'edit',
                            id: promo.giveaway_id
                        }) }}
                    {% else %}
                        {{ promo.name }}
                    {% endif %}
                </td>
                <td>
                    {{ promo.start_date ? time.format(promo.start_date, '%m-%d-%Y') : '' }}
                </td>
                <td>
                    {{ promo.end_date ? time.format(promo.end_date, '%m-%d-%Y') : 'Never' }}
                </td>
                <td>
                    {% if promo.is_member_only %}
                        <span class="member-tag">Member</span>
                    {% else %}
                        None
                    {% endif %}
                </td>
                <td>
                    {% if promo.status > 1 %}
                        <span class="giveaway_status_upcoming">Upcoming</span>
                    {% elseif promo.status < 0 %}
                        <span class="giveaway_status_expired">Expired</span>
                    {% else %}
                        <span class="giveaway_status_active">Active</span>
                    {% endif %}
                </td>
            </tr>
        {% endfor %}
    </table>

    {% include 'Common/activity.inc.tpl' with {
        'title': 'Recent Claims'
    } %}

{% endblock %}
