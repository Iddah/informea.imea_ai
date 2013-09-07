<?php
global $cloud_terms;

$terms = new Thesaurus(null);
$page_data = new informea_decisions();
$top_concepts = $terms->get_top_concepts();
$popular_terms = $page_data->get_popular_terms(NULL, 7);
$cloud_terms = array_merge($top_concepts, $popular_terms);

dynamic_sidebar('decisions-sidebar');