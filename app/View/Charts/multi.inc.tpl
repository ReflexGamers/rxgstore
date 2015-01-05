
<div id="{{ id }}" class="chart_container">
    <div class="chart_controls">

        {% for control in controls %}

            {% set url = {
                'controller': controller,
                'action': action,
                'ext': 'json'
            } %}

            {% if control[1] %}
                {% set url = url|merge(control[1]) %}
            {% endif %}

            <a class="chart_control {{ control[2] ? 'active' : '' }}" href="{{ html.url(url) }}">{{ control[0] }}</a>
            {% if not loop.last %}
                |
            {% endif %}

        {% endfor %}

    </div>
    <div class="chart_inner"></div>
</div>
