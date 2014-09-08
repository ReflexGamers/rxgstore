<div id="shoutbox">
	<ul id="shoutbox_content">
		{% include 'ShoutboxMessages/view.inc.tpl' %}
	</ul>
	{% if user %}
		<div class="shoutbox_controls">
			{{ html.image('misc/ajax-loader.gif', {
				'class': 'ajax-loader',
				'id': 'shoutbox_loading'
			}) }}
			{{ form.create('ShoutboxMessage', {
				'url': {
					'controller': 'shoutboxmessages',
					'action': 'add'
				}
			}) }}
			{{ form.input('content', {
				'label': false,
				'div': false,
				'id': 'shoutbox_input',
				'placeholder': 'Say something cool...'
			}) }}
			{{ form.button('Chat', {
				'class': 'btn-primary',
				'id': 'shoutbox_button'
			}) }}
			{{ form.end() }}
		</div>
	{% endif %}
</div>