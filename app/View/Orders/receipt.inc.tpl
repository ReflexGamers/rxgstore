
<div class="receipt_container">
    <div class="receipt_top"></div>
    <div class="receipt">
        <div class="receipt_contents">
            <div class="receipt_contents_img">
                {{ html.image('misc/rxgmartsmall.png') }}
            </div>
            <table>
                <tr class="toprow">
                    <td class="qtycol">Qty</td>
                    <td class="itemcol">Item</td>
                    <td class="right">Price</td>
                </tr>

                {% set subTotal = 0 %}

                {% for item in data.OrderDetail %}

                    {% set itemTotal = item.price * item.quantity %}
                    {% set subTotal = subTotal + itemTotal %}

                    <tr class="itemrow {{ loop.last ? 'border' : '' }}">
                        <td class="qtycol">
                            {{ item.quantity }}
                        </td>
                        <td class="itemcol">
                            {{ item.quantity > 1 ? items[item.item_id].plural : items[item.item_id].name }}
                            {% if item.quantity > 1 %}
                                <br/>&nbsp;&nbsp;{{ fn.dollars(item.price) }}/ea
                            {% endif %}
                        </td>
                        <td class="right">
                            {{ fn.dollars(itemTotal) }}
                        </td>
                    </tr>

                {% endfor %}

                {% set total = subTotal + data.Order.shipping %}

                <tr class="itemrow">
                    <td class="itemcol" colspan="2">Subtotal:</td>
                    <td class="right">{{ fn.dollars(subTotal) }}</td>
                </tr>

                <tr class="itemrow">
                    <td class="itemcol" colspan="2">Shipping:</td>
                    <td class="right">{{ fn.dollars(data.Order.shipping) }}</td>
                </tr>

                <tr class="itemrow border">
                    <td class="itemcol" colspan="2"><b>Total:</b></td>
                    <td class="right">{{ fn.dollars(total) }}</td>
                </tr>

                <tr class="itemrow">
                    <td class="itemcol" colspan="2">CASH paid:</td>
                    <td class="right">{{ fn.dollars(total) }}</td>
                </tr>

                <tr class="itemrow">
                    <td class="itemcol" colspan="2">Change:</td>
                    <td class="right">{{ fn.dollars(0) }}</td>
                </tr>

            </table>

            <div class="receipt_footer">
                <span class="receipt_sale_info">STEAMID: {{ steamid }}</span>
                <br/><br/>
                <span class="receipt_sale_info">SALE #{{ data.Order.order_id }}</span>
                <br/><br/>
                Thank you for your patronage!
                <br/><br/>
                {{ time.nice(data.Order.date) }}
                <br/><br/>
                www.reflex-gamers.com
            </div>

        </div>
    </div>
    <div class="receipt_bottom"></div>
</div>