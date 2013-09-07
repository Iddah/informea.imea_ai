<?php
dynamic_sidebar('countries-sidebar-item');
$id = informea_countries::get_id_from_request();
$country = informea_countries::get_country_for_id($id);

$rUN = new UNDataWebsiteParser($country->id, $country->name);
$un_country_img_url = $rUN->get_map_image();
if ($un_country_img_url != null) {
    echo '<br /><img src="' . $un_country_img_url . '" width="210" height="210" />';
}
$env_data = $rUN->get_environmental_data();
if (!empty($env_data)) {
    echo sprintf('<p><i>Disclaimer: Environmental indicators provided by <a target="_blank" href="%s">UN data</a></i></p>',
        UNDataWebsiteParser::$WWW_IMG_URL);
    echo '<div id="country_un_env_data">' . $env_data . '</div>';
}

