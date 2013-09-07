var map, layer, country_layer;
var country_location = '';
var popup;
var current_country = '';

$(document).ready(function () {
    init_map();
});

function init_map() {
    var _mapWidth = 720;
    var _mapHeight = 490;
    if (typeof(mapWidth) != 'undefined') {
        _mapWidth = mapWidth;
    }
    if (typeof(mapHeight) != 'undefined') {
        _mapHeight = mapHeight;
    }
    var _singleTile = false;

    OpenLayers.Control.Click = OpenLayers.Class(OpenLayers.Control, {
        initialize: function () {
            OpenLayers.Control.prototype.initialize.apply(this, arguments);
            this.handler = new OpenLayers.Handler.Click(
                this, {
                    'click': this.onClick
                });
        },
        onClick: function (evt) {
            if (country_location != '') {
                location = country_location;
            }
        }
    });

    OpenLayers.Control.Hover = OpenLayers.Class(OpenLayers.Control, {
        initialize: function () {
            OpenLayers.Control.prototype.initialize.apply(this, arguments);

            this.handler = new OpenLayers.Handler.Hover(this,
                {'pause': this.onPause },
                {'delay': 50}
            );
        },
        onPause: function (evt) {
            bounds = map.calculateBounds()
            $.ajax({
                url: mapserver_url + "?map=" + mapserver_localmappath + "&SERVICE=WMS&VERSION=1.3.0&REQUEST=GetFeatureInfo&layers=countries&CRS=EPSG:4326&BBOX=" + bounds.bottom + "," + bounds.left + "," + bounds.top + "," + bounds.right + "&WIDTH=" + _mapWidth + "&HEIGHT=" + _mapHeight + "&query_layers=countries&i=" + evt.xy.x + "&j=" + evt.xy.y,
                success: function (data) {
                    handleCountryGetFeatureInfoResponse(data, evt);
                }
            });
        }
    });


    map = new OpenLayers.Map('openlayers_map', {controls: [new OpenLayers.Control.Navigation(), new OpenLayers.Control.LayerSwitcher({'ascending': false}), new OpenLayers.Control.PanZoomBar()], numZoomLevels: 5, restrictedExtent: new OpenLayers.Bounds(-180, -90, 180, 90), maxResolution: (360 / _mapWidth)});

    var layer = new OpenLayers.Layer.MapServer("OpenLayers WMS", mapserver_url + "?map=" + mapserver_localmappath + "&SERVICE=WMS&VERSION=1.3.0&REQUEST=GetMap&BBOX=-90,-180,90,180&CRS=EPSG:4326&LAYERS=countries&STYLES=&FORMAT=image/png&DPI=91&TRANSPARENT=TRUE", {layers: 'x'}, {gutter: 15, singleTile: _singleTile});
    // map.addLayer(layer);
    // layer = new OpenLayers.Layer.Google("Google Physical",{type: G_PHYSICAL_MAP, sphericalMercator: false});
    map.addLayer(layer);
    map.setCenter(new OpenLayers.LonLat(0, 30), 1);
    var clickControl = new OpenLayers.Control.Click();
    map.addControl(clickControl);
    clickControl.activate();

    var hoverControl = new OpenLayers.Control.Hover();
    map.addControl(hoverControl);
    hoverControl.activate();

    if (have_membership) {
        var hm_url = mapserver_url + "?map=" + mapserver_localmappath + "&SERVICE=WMS&VERSION=1.3.0&REQUEST=GetMap&BBOX=-90,-180,90,180&CRS=EPSG:4326&WIDTH=512&HEIGHT=256&LAYERS=countries_filter&STYLES=&FORMAT=image/png&DPI=91&TRANSPARENT=TRUE&highlight=" + membership_filter + '&map.layer[countries_filter].class[0]=COLOR+255+163+252+END';
        membership_layer = new OpenLayers.Layer.MapServer("OpenLayers Highlight", hm_url, {layers: 'y'}, {isBaseLayer: false, singleTile: _singleTile});
        map.addLayer(membership_layer);
    }

}

function handleCountryGetFeatureInfoResponse(data, evt) {
    var re = new RegExp("ISO_2DIGIT = \'(.+)\'");
    var m = re.exec(data);
    if (m != null) {
        if (current_country == m[1]) {
            return;
        }
        if (country_layer) {
            map.removeLayer(country_layer);
            country_layer = null;
        }
        current_country = m[1];
        var country_id = -1;
        var country_name = "";
        var country_icon = "";
        for (var i in countries) {
            if (countries[i][1] == current_country) {
                country_id = countries[i][1];
                country_name = countries[i][2];
                country_icon = countries[i][3];
                break;
            }
        }
        if (country_id != -1) {
            var tooltip = $("#tooltip");
            tooltip.html(
                '<div class="text-center"><img src="' + country_icon + '" />'
                + '<div>' + country_name
                + '&nbsp;&nbsp;<a href="' + (base_url + "/" + country_id) +  '">view &raquo;</a></div></div>'
            );
            var style = "display:block; top:" + (evt.xy.y - tooltip.height() - 10) + "px; left:" + (evt.xy.x - tooltip.width() - 10) + "px;";
            tooltip.attr("style", style);
            country_layer = new OpenLayers.Layer.MapServer(
                "Country WMS",
                mapserver_url + "?map="
                    + mapserver_localmappath
                    + "&SERVICE=WMS&VERSION=1.3.0&REQUEST=GetMap&BBOX=-90,-180,90,180&CRS=EPSG:4326&WIDTH=512&HEIGHT=256&LAYERS=countries_filter&STYLES=&FORMAT=image/png&DPI=91&TRANSPARENT=TRUE&highlight="
                    + m[1],
                { layers: 'country' },
                { gutter: 15, 'isBaseLayer': false, singleTile: true }
            );
            map.addLayer(country_layer);
        }
        else {
            $("#tooltip").attr("style", "display:none");
        }
    }
    else {
        if (country_layer) {
            $("#tooltip").attr("style", "display:none");
            map.removeLayer(country_layer);
            country_layer = null;
        }
    }
}

