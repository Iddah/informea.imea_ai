<?php

$featured_country = NULL;

class FeaturedCountryWidget extends WP_Widget {

    function FeaturedCountryWidget() {
        $options = array(
            'classname' => 'FeaturedCountryWidget',
            'description' => 'Rotate one of the countries, daily',
        );
        $this->WP_Widget('FeaturedCountryWidget', 'Featured Country', $options);
    }


    function form($instance) {
        $instance = wp_parse_args((array)$instance, array('title' => ''));
        $title = $instance['title'];
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"
                />
        </p>
    <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

    function widget($args, $instance) {
        global $featured_country;
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $iso = get_request_value('iso');
        $featured_country = informea_countries::get_country_by_iso($iso);
        if(empty($featured_country)) {
            $featured_country = informea_countries::get_country_for_visitor();
        }
        if ($featured_country) {
            add_action('js_inject', 'widget_featured_country_inject_js');
            wp_enqueue_script('google-maps-api', 'http://maps.google.com/maps/api/js?sensor=false');
?>
            <li class="widget featured-country noshadow">
                <?php if (!empty($title)) : ?>
                    <h2>
                        <?php echo $title; ?>:
                        <a href="<?php echo sprintf('%s/countries/%s', get_bloginfo('url'), $featured_country->id); ?>">
                            <?php echo $featured_country->name; ?>
                        </a>
                        <img src="<?php echo get_bloginfo('template_directory') . '/' . $featured_country->icon_large; ?>" />
                    </h2>
                    <label>
                        <select id="change_country">
                            <option value="">-- Change --</option>
                            <?php
                            foreach (informea_countries::get_countries() as $row):
                                ?>
                                <option value="<?php echo $row->code2l; ?>"><?php echo $row->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="clear"></div>
                <?php endif; ?>
                <div class="content featured-country">
                    <div id="tabs">
                        <ul>
                            <li><a href="#tabs-1">Map &amp; sites</a></li>
                            <?php if(informea_countries::count_treaty_membership($featured_country->id) > 0     ) : ?>
                            <li><a href="<?php echo admin_url("/admin-ajax.php?action=country_mea_membership&id={$featured_country->id}") ;?>">MEA membership</a></li>
                            <?php endif; ?>
                            <?php $c = informea_countries::count_focal_points($featured_country->id); if($c) : ?>
                            <li><a href="<?php echo admin_url("/admin-ajax.php?action=country_nfp&id={$featured_country->id}") ;?>">Focal points (<?php echo $c; ?>)</a>
                            <?php endif; ?>
                        </ul>
                        <div id="tabs-1">
                            <div id="fc-map-canvas"></div>
                        </div>
                    </div>
                </div>
            </li>
        <?php
        }
    }
}

add_action('widgets_init', create_function('', 'return register_widget("FeaturedCountryWidget");'));

function widget_featured_country_inject_js() {
    global $featured_country;
?>
    <script type="text/javascript">
        var fc_map = null;
        var infoWindow = null;
        function featuredCountryWidgetInitMap() {
            var id_country = <?php echo $featured_country->id; ?>;
            var name = "<?php echo esc_attr($featured_country->name); ?>";
            var latlng = null, zoom = 0, map = null;

            infoWindow = new google.maps.InfoWindow();

            if(id_country == 184) { // EU
                featuredCountryShowMap(new google.maps.LatLng(50.397, 15.644), 3);
                featuredCountryShowSites(id_country);
            } else if(id_country == 61) { // Georgia
                featuredCountryShowMap(new google.maps.LatLng(42.180058, 43.699322), 6);
                featuredCountryShowSites(id_country);
            } else { // Autodetect
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: name }, function (results) {
                    if (results.length > 0) {
                        var p = results[0].geometry.location;
                        latlng = new google.maps.LatLng(p.lat(), p.lng());
                        zoom = 6;
                        featuredCountryShowMap(latlng, zoom);
                        featuredCountryShowSites(id_country);
                    }
                });
            }
        }
    </script>
<?php
}