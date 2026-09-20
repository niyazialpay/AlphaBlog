<?php

return [
    'dashboard' => 'Dashboard',
    'top_pages' => 'Top Pages',
    'top_countries' => 'Top Countries',
    'top_browsers' => 'Top Browsers',
    'top_operating_systems' => 'Top Operating Systems',
    'ad_impression' => 'Ad Impressions',
    'sessions' => 'Sessions',
    'ad_clicks' => 'Ad Clicks',
    'total_visitors_and_page_views' => 'Total Visitors and Page Views',
    'total_visitors' => 'Total Visitors',
    'total_page_views' => 'Total Page Views',
    'user_types' => 'User Types',
    'page_views' => 'Page Views',
    'views' => 'Views',

    'user_type' => [
        'new' => 'New Visitors',
        'returning' => 'Returning Visitors',
        'others' => 'Others',
    ],
    'back_to_dashboard' => 'Back to Dashboard',
    'analytics' => 'Google Analytics Data',
    'filter' => 'Filter',

    'ga4_not_configured' => 'Google Analytics is not configured.',
    'ga4_not_configured_hint' => 'Make sure storage/app/analytics/service-account-credentials.json is in place and ANALYTICS_PROPERTY_ID is set in .env.',
    'gsc_not_configured' => 'Search Console is not configured.',
    'gsc_not_configured_hint' => 'Upload the credentials file and set the site URL under the Google Indexing settings.',
    'data_fetch_failed' => 'Could not load data.',
    'data_fetch_failed_hint' => 'The Google request failed. Check the system logs for details.',
];
