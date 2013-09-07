<?php
function informea_countries_toolbar_alphabetical() {
    ?>
    <button id="expand-all"><i class="icon-plus-sign"></i> Expand all</button>
    <button id="collapse-all"><i class="icon-minus-sign"></i> Collapse all</button>
    <label>
        &nbsp;<?php _e('Search', 'informea'); ?>
        <input id="party-filter" type="text" placeholder="Type country name ..." />
    </label>
<?php
}
add_action('informea-countries-toolbar-extra', 'informea_countries_toolbar_alphabetical');
do_action('informea-countries-toolbar');

$treaties = informea_treaties::get_treaties_keyed_by_id();
$data = imea_countries_page::get_view_treaties();
?>
<div class="alert alert-info">
    <button class="close" data-dismiss="alert">×</button>
    Click on country name to see membership info
</div>
<ul class="countries">
    <?php
    foreach ($data as $id_treaty => $countries) :
        $treaty = $treaties[$id_treaty];
        echo '<li>';
        ?>
        <h2>
            <i class="icon icon-plus-sign"></i>
            <?php echo $treaty->short_title; ?>
        </h2>
        <div class="flag">
            <img src="<?php echo $treaty->logo_medium; ?>" alt="<?php _e('Convention logo', 'informea'); ?>" />
        </div>
        <div class="clear"></div>
        <div class="content hidden">
            <table class="table-hover">
                <thead>
                <tr>
                    <th>Country</th>
                    <th>Entry into force</th>
                    <th>Status</th>
                    <th>Instrument</th>
                    <th>Declarations</th>
                    <th>Notes</th>
                </tr>
                </thead>
                <?php foreach($countries as $party) : ?>
                    <tr>
                        <td><a href="<?php echo sprintf('%s/countries/%s', get_bloginfo('url'), $party->id_country); ?>"><?php echo $party->name; ?></a></td>
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

