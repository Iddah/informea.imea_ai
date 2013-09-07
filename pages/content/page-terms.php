<?php
$expand = get_request_variable('expand', 'str', 'hierarchical');

function informea_terms_toolbar() {
    get_template_part('pages/content/page', "terms-toolbar");
}
add_action('informea-terms-toolbar', 'informea_terms_toolbar');
get_template_part('pages/content/page', "terms-$expand");
