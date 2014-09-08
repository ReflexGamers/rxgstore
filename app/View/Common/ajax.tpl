{% import 'Common/functions.tpl' as fn %}

{% if styles %}
	{{ html.css(styles) }}
{% endif %}

{% block content %}{% endblock %}

{% if scripts %}
	{{ html.script(scripts) }}
{% endif %}