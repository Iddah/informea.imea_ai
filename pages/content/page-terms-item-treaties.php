<?php
wp_enqueue_script('meetings', get_bloginfo('template_directory') . '/scripts/search.js');

$id = get_request_variable('id', 1);
$page_data = new Thesaurus($id, array('id_ns' => 1, 'id_concept' => 1, 'feature' => 'str', 'mode' => 'str'));
$term = $page_data->term;
// Pre-cook the search - add parameters
$request = array();
$request['q_term'] = array($term->id);
$page_treaties = new imea_treaties_page(); // Treaties
$ts = $page_treaties->get_treaties();
$request['q_treaty'] = array();
foreach ($ts as $t) {
    $request['q_treaty'][] = $t->id;
}
$request['q_tab'] = 2; // Results like 3rd tab of adv search
$request['q_use_treaties'] = 1;

$search = InformeaSearch3::get_searcher($request);
echo $search->render();
