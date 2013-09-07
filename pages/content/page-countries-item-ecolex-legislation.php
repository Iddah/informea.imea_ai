<?php
$expand = get_request_variable('expand', 'str', 'map'); // or reports or country
$id = informea_countries::get_id_from_request();
$country = informea_countries::get_country_for_id($id);
$url = urldecode(
    get_request_value('next')
);
if (empty($url)) {
    $url = 'http://www.ecolex.org/ecolex/ledge/view/SearchResults?screen=Legislation&index=documents&sortField=searchDate&country=' . str_replace(' ', '%20', $country->name);
}
$p = new EcolexParser($url, get_bloginfo('url') . "/countries/{$country->id}/" . $expand);
?>
<div class="tab-content">
    <div class="ecolex-disclaimer">
        <img src="http://www.informea.org/wp-content/uploads/ecolex_header.png"/>
        The content of this this area is exclusively provided by
        <a target="_blank" href="<?php echo $url; ?>">Ecolex</a> - the gateway
        to environmental law,
        which is a collaboration of IUCN, FAO and UNEP
    </div>
    <?php echo $p->get_content(); ?>
</div>
