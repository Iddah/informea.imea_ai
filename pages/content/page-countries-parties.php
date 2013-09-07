<?php
function informea_countries_toolbar_parties() {
?>
    <button id="expand-all"><i class="icon-plus-sign"></i> Expand all</button>
    <button id="collapse-all"><i class="icon-minus-sign"></i> Collapse all</button>
    <label>
        &nbsp;<?php _e('Search', 'informea'); ?>
        <input id="party-filter" type="text" placeholder="Type country name ..." />
    </label>
<?php
}
add_action('informea-countries-toolbar-extra', 'informea_countries_toolbar_parties');
do_action('informea-countries-toolbar');

$countries = imea_countries_page::get_countries_keyed_iso2l();
$data = imea_countries_page::get_view_parties();
?>
<div class="alert alert-info">
    <button class="close" data-dismiss="alert">×</button>
    Click on country name to see membership info
</div>
<ul class="countries">
<?php
foreach ($data as $iso2l => $parties) :
    $country = $countries[$iso2l];
    echo '<li>';
?>
    <h2>
        <i class="icon icon-plus-sign"></i>
        <?php echo $country->name; ?>
    </h2>
    <div class="flag">
        <img src="<?php echo sprintf('%s/%s', get_bloginfo('template_directory'), $country->icon_medium); ?>" />
    </div>
    <div class="clear"></div>
    <div class="content hidden">
        <p>
            <a href="<?php echo sprintf('%s/countries/%s', get_bloginfo('url'), $country->id); ?>">View <?php echo $country->name; ?> profile</a>
        </p>
        <table class="table-hover">
        <thead>
            <tr>
                <th>MEA</th>
                <th>Entry into force</th>
                <th>Status</th>
                <th>Instrument</th>
                <th>Declarations</th>
                <th>Notes</th>
            </tr>
        </thead>
        <?php foreach($parties as $party) : ?>
        <tr>
            <td><a href="<?php echo sprintf('%s/treaties/%s', get_bloginfo('url'), $party->odata_name); ?>"><?php echo $party->short_title; ?></a></td>
            <td><?php echo $party->year; ?></td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
        </tr>
    <?php endforeach; ?>
        </table>
    </div>
<?php
echo '</li>';
endforeach;
?>
</ul>

