
{% if closable is not defined %}
    {% set closable = true %}
{% endif %}

<div class="flash_outer">
    <div id="flashMessage" class="flashMessage {{ class }}">

        {% if class == 'success' %}
            <i class="fa fa-check flash_icon flash_check"></i>
        {% elseif class == 'info' %}
            <i class="fa fa-info-circle flash_icon flash_info"></i>
        {% elseif class == 'error' %}
            <i class="fa fa-warning flash_icon flash_warning"></i>
        {% endif %}

        {% if closable %}
            <i class="fa fa-times flash_remove"></i>
        {% endif %}

        {% if isCart %}
            <span class="flash_view_cart">{{
                html.link('Checkout', {
                    'controller': 'Cart',
                    'action': 'view'
                }) }}
            </span>
        {% endif %}

        {{ message }}
    </div>
</div>
