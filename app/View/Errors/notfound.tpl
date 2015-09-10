{% extends 'Common/base.tpl' %}

{% set title = error %}

{% block content %}

    <table class="error_not_found">
        <tr>
            <td>
                <i class="fa fa-warning error_not_found_icon"></i>
            </td>
            <td>
                Oops! We can't find the page you are looking for. {{ html.link('Click here', { controller: 'Items', action: 'index' }) }} to go back to the shop.
            </td>
        </tr>
    </table>

{% endblock %}