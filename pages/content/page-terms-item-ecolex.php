<?php
$id = get_request_variable('id', 1);
$page_data = new Thesaurus($id, array('id_ns' => 1, 'id_concept' => 1, 'feature' => 'str', 'mode' => 'str'));
$term = $page_data->term;
$url = urldecode(get_request_value('next'));
if (empty($url)) {
    $url = 'http://www.ecolex.org/ecolex/ledge/view/SearchResults?screen=Literature&index=literature&sortField=searchDate&keyword=' . str_replace(' ', '%20', $term->term);
}
$p = new EcolexParser($url, get_bloginfo('url') . "/terms/{$term->id}/ecolex");
?>
<div class="ecolex-disclaimer">
    <img src="http://www.informea.org/wp-content/uploads/ecolex_header.png"/>
    The content of this this area is exclusively provided by
    <a target="_blank" href="<?php echo $url; ?>">Ecolex</a> - the gateway
    to environmental law,
    which is a collaboration of IUCN, FAO and UNEP
</div>
<?php echo $p->get_content(); ?>
