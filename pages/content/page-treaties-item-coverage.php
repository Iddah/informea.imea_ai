<?php
$page_data = new informea_treaties();
$treaty = informea_treaties::get_treaty_from_request();
$parties = informea_treaties::get_parties($treaty->id);
if (count($parties)) :
?>
<div class="content">
    <div class="toolbar toolbar-parties">
        <div class="pull-right">
            Search: <input id="party-filter" type="text" name="search" placeholder="in table below ..." />
        </div>
        <div class="clear"></div>
    </div>
    <p>
        <strong>Total number of parties</strong>: <?php echo count($parties); ?>
    </p>
    <table id="parties" class="table-striped">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th class="text-left">Member country</th>
        <th>Since</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($parties as $country) { ?>
        <tr>
            <td>
                <img src="<?php echo sprintf('%s/%s', get_bloginfo('template_directory'), $country->icon_medium); ?>"
            </td>
            <td>
                <?php echo $country->name; ?>
            </td>
            <td class="center">
                <?php echo $country->year; ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<?php endif;