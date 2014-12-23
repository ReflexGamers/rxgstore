
<div class="flash_outer">
    <div id="flashMessage" class="flashMessage {{ class }}"><i class="fa fa-times flash_remove"></i>&nbsp; {{ message }}
        <span class="flash_view_cart">{{
            html.link('Checkout', {
                'controller': 'Cart',
                'action': 'view'
            }) }}
        </span>
    </div>
</div>