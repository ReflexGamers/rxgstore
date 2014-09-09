
{% macro avatar(_, player) %}
	{{- _.html.image(player.avatar, {
		'url': {
			'controller': 'Users',
			'action': 'profile',
			'id': player.steamid
	}}) -}}
{% endmacro %}

{% macro profile(_, player) %}
	{{- _.html.link(player.name, {
		'controller': 'Users',
		'action': 'profile',
		'id': player.steamid
	}, {
		'class': player.member ? 'member' : ''
	}) -}}
{% endmacro %}

{% macro player(_, player) %}
	{% import _self as fn %}
	{{ fn.avatar(_, player) }}
	{{ fn.profile(_, player) }}
{% endmacro %}


{% macro currency(amount, options) %}
	{%- if not options.hideIcon -%}
		<i class="currency{{ options.big ? '-big' : '' }}"></i>
	{%- endif -%}
	{{- options.wrap ? '<span class="currency_value">' : '' -}}
	{{- amount|number_format -}}
	{{- options.wrap ? '</span>' : '' -}}
{% endmacro %}

{% macro dollars(amount) %}
	${{ amount|number_format -}}
{% endmacro %}

{% macro realMoney(amount) %}
	${{ "%.2f"|format(amount / 100) }}
{% endmacro %}


{% macro time(_, created, modified) %}

	{% import _self as fn %}

	{% set createdTime = fn.formatTime(_, created) %}

	{% set isModified = modified > created %}

	{% if isModified %}
		{% set modifiedTime = fn.formatTime(_, modified) %}
	{% endif %}

	{% set showEdited = _.time.wasWithinLast('1 day', modified) %}

	<span title="{{ isModified ? "Originally posted #{createdTime}" }}">
		{{ isModified and showEdited ? 'edited' : '' }}
		{{ isModified ? modifiedTime : createdTime }}
	</span>

{% endmacro %}


{% macro formatTime(_, time) %}
	{{- _.time.timeAgoInWords(time, {
		'accuracy':  {
			'minute': 'minute',
			'hour': 'hour',
			'day': 'day',
			'week': 'week',
			'month': 'month'
		},
		'format': 'F jS, Y'
	}) -}}
{% endmacro %}