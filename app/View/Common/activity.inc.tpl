
{% include 'Common/section.inc.tpl' with {
    'data': activities,
    'sectionId': 'activity',
    'contentId': 'activity_content',
    'innerTemplate': 'Activity/list.inc.tpl'
} %}
