<?php
global $cloud_terms;

$terms = new Thesaurus();
$page_data = new informea_treaties();
$top_concepts = $terms->get_top_concepts();
$popular_terms = $page_data->get_popular_terms(NULL, 6);
$cloud_terms = array_merge($top_concepts, $popular_terms);

dynamic_sidebar('treaties-sidebar');