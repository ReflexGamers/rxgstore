{% extends 'Common/base.tpl' %}

{% set title = 'Editing: ' ~ data.Giveaway.name %}

{% block content %}

    {% include 'Giveaways/edit.inc.tpl' %}

{% endblock %}
