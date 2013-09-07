<?php
$imea_options = get_option('informea_options');
$coverage = get_request_int('coverage');
wp_enqueue_script('openlayers', get_bloginfo('template_directory') . '/scripts/OpenLayers/OpenLayers.js');
wp_enqueue_script('countries-map', get_bloginfo('template_directory') . '/scripts/countries-map.js');
do_action('informea-countries-toolbar');
?>
<div style="height: 5px;"></div>
<div id="openlayers_map" style="width: 720px; height: 490px; background: #9ad3e6;"><a id="tooltip"></a></div>
<div class="pull-right">
    Build with <a href="http://mapserver.org/" target="_blank">MapServer</a>
    and
    <a href="http://openlayers.org/" target="_blank">OpenLayers</a>
</div>
<br />
<div class="alert alert-warning">
    Data is still being consolidated, inaccuracies may occur
</div>

<script type="text/javascript">
    var mapserver_url = '<?php echo $imea_options["mapserver_url"]; ?>';
    var mapserver_localmappath = '<?php echo $imea_options["mapserver_localmappath"]; ?>';
    var countries = [
        <?php
            foreach(imea_countries_page::get_countries() as $c) {
        ?>
        [<?php echo($c->id) ?>, "<?php echo($c->code2l) ?>", "<?php echo($c->name) ?>", "<?php echo get_bloginfo('template_directory') . '/' . $c->icon_medium; ?>"],
        <?php
            }
        ?>
    ];
    var base_url = "<?php echo get_permalink()?>";
    var site_url = "<?php echo get_bloginfo('url')?>";
    var have_membership = <?php echo ($coverage > 0) ? 'true' : 'false'; ?>;
    var membership_filter = '<?php echo imea_countries_page::gis_get_membership_filter($coverage); ?>';
</script>