<?php
$expand = get_request_variable('expand', 'str', 'map');
wp_enqueue_script('countries', get_bloginfo('template_directory') . '/scripts/countries.js');

function informea_countries_toolbar() {
    get_template_part('pages/content/page', "countries-toolbar");
}
add_action('informea-countries-toolbar', 'informea_countries_toolbar');

get_template_part('pages/content/page', "countries-$expand");
