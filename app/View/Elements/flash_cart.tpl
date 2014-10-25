
<div id="flashMessage" class="{{ class }}">{{ message }} <span class="flash_view_cart">{{
    html.link('View Cart >', {
        'controller': 'Cart',
        'action': 'view'
    }) }}</span></div>