<div class="items_howto">
    <a class="items_howto_link">How do I use items?</a>
    <div class="items_howto_content">
        <h3 class="items_howto_header">Easy Way (in chat)</h3>
        <p class="items_howto_method">
            Type <code>!useitem</code> in chat and select the item from the menu.
            <br>
            You can also use a specific item by typing <code>!useitem NAME</code>.
            <br>
            Example: <code>!useitem cookie</code> will use a {{ html.link('cookie', {'controller': 'Items', 'action': 'view', 'id': 'cookie'}) }}.
        </p>
        <h3 class="items_howto_header">Better Way (key bind)</h3>
        <p class="items_howto_method">
            First, enable the developer console in settings so that you can bind keys to custom actions. Then, enter <code>bind KEY useitem</code> into the console (and press enter) to bind that key to open the item menu.
            <br>
            Example: <code>bind f useitem</code> will bind the <code>f</code> key to open it.
            <br><br>
            Bind a specific item to a key with <code>bind KEY "useitem NAME"</code> which skips the menu and instantly uses the item.
            <br>
            Example: <code>bind f "useitem cookie"</code> will bind <code>f</code> to the {{ html.link('cookie', {'controller': 'Items', 'action': 'view', 'id': 'cookie'}) }}.
            <br><br>
            At the top of each item's listing page, you will see a value for <em>"in-game usage"</em>. That is the <code>NAME</code> you must use to bind the item directly.
        </p>
    </div>
</div>