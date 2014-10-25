
{% if activities %}

    <section id="activity">

        <h2 class="page_subheading">{{ title }}</h2>

        <div id="activity_content">
            {% include 'Activity/list.inc.tpl' %}
        </div>

    </section>

{% endif %}