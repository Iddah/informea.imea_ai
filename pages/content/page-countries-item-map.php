<?php
global $id;
wp_enqueue_script('google-maps-api', 'http://maps.google.com/maps/api/js?sensor=false');
$id = informea_countries::get_id_from_request();
$country = informea_countries::get_country_for_id($id);
$ramsar_sites = informea_countries::get_ramsar_sites($id);
$whc_sites = informea_countries::get_whc_sites($id);

function informea_country_map_view_js() {
    global $id;
    $country = informea_countries::get_country_for_id($id);
?>
<script type="text/javascript">
    var fc_map = null;
    var infoWindow = null;

    $(document).ready(function() {
        var id_country = <?php echo $country->id; ?>;
        var name = "<?php echo esc_attr($country->name); ?>";
        var latlng = null, zoom = 0, map = null;

        infoWindow = new google.maps.InfoWindow();

        if(id_country == 184) { // EU
            countryProfileShowMap(new google.maps.LatLng(50.397, 15.644), 3);
            countryProfileShowSites(id_country);
        } else if(id_country == 61) { // Georgia
            countryProfileShowMap(new google.maps.LatLng(42.180058, 43.699322), 6);
            countryProfileShowSites(id_country);
        } else { // Autodetect
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: name }, function (results) {
                if (results.length > 0) {
                    var p = results[0].geometry.location;
                    latlng = new google.maps.LatLng(p.lat(), p.lng());
                    zoom = 6;
                    countryProfileShowMap(latlng, zoom);
                    countryProfileShowSites(id_country);
                }
            });
        }
    });

    function countryProfileShowMap(latlng, zoom) {
        var myOptions = { zoom: zoom, center: latlng, mapTypeId: google.maps.MapTypeId.ROADMAP, streetViewControl: false };
        fc_map = new google.maps.Map(document.getElementById("map-canvas"), myOptions);
    }

    function countryProfileShowSites(id_country) {
        $.ajax({
            url: ajax_url + '?action=country_sites_markers',
            dataType: "json",
            data: { id: id_country },
            success: function (data) {
                $(data.whc).each(function(idx, cfg) {
                    cfg.position = new google.maps.LatLng(cfg.latitude, cfg.longitude);
                    cfg.map = fc_map;
                    var marker = new google.maps.Marker(cfg);
                    google.maps.event.addListener(marker, 'click', function () {
                        var txt = '<a href="' + cfg.url + '" target="_blank">' + cfg.title + '</a>';
                        infoWindow.setContent(txt);
                        infoWindow.open(fc_map, marker);
                    });
                });
                $(data.ramsar).each(function(idx, cfg) {
                    cfg.position = new google.maps.LatLng(cfg.latitude, cfg.longitude);
                    cfg.map = fc_map;
                    var marker = new google.maps.Marker(cfg);
                    if(cfg.url) {
                        google.maps.event.addListener(marker, 'click', function () {
                            var txt = '<a href="javascript:void(0);" onclick="countryProfileOpenRamsarSite(' + cfg.id + ');" target="_blank">' + cfg.title + '</a>';
                            txt += '<p class="text-grey"><strong>Tip:</strong> Clicking, you will leave InforMEA.<br />Press Back to return here</p>';
                            infoWindow.setContent(txt);
                            infoWindow.open(fc_map, marker);
                        });
                    }
                });
            }
        });
    }

    function countryProfileOpenRamsarSite(id) {
        alert('Cannot access Ramsar RSIS to show site details');
        return;
        var form = document.createElement('form');
        form.setAttribute('method', 'post');
        form.setAttribute('action', 'http://www.wetlands.org/reports/output.cfm');

        var site_id = document.createElement('input');
        site_id.setAttribute('type', 'hidden');
        site_id.setAttribute('name', 'site_id');
        site_id.setAttribute('value', id);
        form.appendChild(site_id);

        var button = document.createElement('input');
        button.setAttribute('type', 'hidden');
        button.setAttribute('name', 'RepAll');
        button.setAttribute('value', '1');
        form.appendChild(button);

        document.body.appendChild(form);
        form.submit();
    }

</script>
<?php
}
add_action('js_inject', 'informea_country_map_view_js');
?>
<div class="tab-content">
<div id="map-canvas"></div>
<?php if(count($whc_sites) > 0) : ?>
    <h2>WHC Sites</h2>
    <ul>
    <?php foreach($whc_sites as $site) : ?>
        <li>
            <?php if ($site->url !== NULL) { ?>
                <a target="_blank" href="<?php echo $site->url; ?>"><?php echo $site->name; ?></a>
            <?php } else { ?>
                <?php echo $site->name; ?>
            <?php } ?>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if(count($ramsar_sites) > 0) : ?>
    <h2>Ramsar Sites</h2>
    <ul>
    <?php foreach($ramsar_sites as $site) : ?>
        <li>
            <?php echo $site->name; ?>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>