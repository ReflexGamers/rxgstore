
{% include 'Common/section.inc.tpl' with {
    'data': reviews,
    'sectionId': 'reviews',
    'contentId': 'reviews_content',
    'innerTemplate': 'Reviews/list.inc.tpl'
} %}
