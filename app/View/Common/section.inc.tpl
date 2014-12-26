
{% if (condition is defined) ? condition : data %}

    {% if isAjax %}

        {% include innerTemplate %}

    {% else %}

        <section id="{{ sectionId }}">

            {% if not standalone %}
                <h2 class="page_subheading {{ headerClass }}">{{ title }}</h2>
            {% endif %}

            {% if description %}
                <p class="section_description">{{ description }}</p>
            {% endif %}

            <div id="{{ contentId }}">
                {% include innerTemplate %}
            </div>

        </section>

    {% endif %}

{% endif %}
