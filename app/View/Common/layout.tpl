{% import 'Common/functions.tpl' as fn %}
<!DOCTYPE html>
<html>
<head>
	{{ html.charset() }}
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width">
	<title>{{ title }}</title>
	{{ html.css([
		'http://fonts.googleapis.com/css?family=Roboto:400,300,700,400italic',
		'http://fonts.googleapis.com/css?family=PT+Mono',
		'theme'
	]|merge(styles ?: [])) }}

	{% if jquery %}
		{{ html.script('jquery-1.10.2.min') }}
	{% endif %}
</head>
<body>
<div id="background">&nbsp;</div>

<div class="header">
	<table class="headert">
		<tr>
			<td class="header_left">
				{{ html.link('SHOP', {'controller': 'items', 'action': 'index'}) }}
				{{ html.link('FAQ', {'controller': 'items', 'action': 'faq'}) }}
				<span id="cart_link_content">
					{% if cartItems %}
						{% include 'Cart/link.inc.tpl' %}
					{% endif %}
				</span>
				<input type="hidden" id="cart_update_location" value="{{ html.url({'controller': 'cart', 'action': 'link'}) }}">
			</td>
			<td class="header_right">
				{% if user %}
					<span class="login_name">
						{{ html.link(user.name, {
							'controller': 'users',
							'action': 'profile',
							'id': user.steamid
						}, {
							'class': 'username_link'
						}) }}
					</span>
					<img class="user_avatar" src="{{ user.avatar }}" />
					{{ html.link('log out', {
						'controller': 'users',
						'action': 'logout'
					}, {
						'class': 'logout'
					}) }}
				{% else %}
					{{ form.create('login', {
						'url': {
							'controller': 'users',
							'action': 'login'
						},
						'id': 'steam_signin'
					}) }}
						<input class="normal" type="image" src="http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png" alt="Sign in through Steam">
						<input type="checkbox" name="rememberme" id="rememberme"><label for="rememberme">remember me</label>
					{{ form.end }}
				{% endif %}
			</td>
		</tr>
	</table>
</div>

<div id="content" class="content">
	{% block content %}{% endblock %}
</div>

<div class="foot">
	<a href="http://reflex-gamers.com">reflex gamers</a> | <a href="http://steampowered.com">Powered by Steam</a>
</div>

{{ js.writeBuffer() }}

{% if scripts %}
	{{ html.script(scripts) }}
{% endif %}

</body>
</html>