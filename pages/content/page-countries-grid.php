<?php
function informea_countries_toolbar_grid() {
    ?>
    <button id="expand-all" disabled="disabled"><i class="icon icon-download"></i> Download table below</button>
<?php
}
add_action('informea-countries-toolbar-extra', 'informea_countries_toolbar_grid');
do_action('informea-countries-toolbar');

$data = informea_countries::index_grid();
$initial = array(5, 20, 2, 1, 8, 14); // Stockholm, Rotterdam, Basel, CBD, Cartagena, Plant Treaty
$columns = $data['column'];
$signatures = $data['signatures'];
$countries = $data['countries'];
?>
<div class="filter-treaties round">
    <button class="close" data-dismiss="alert">×</button>
    <h3>Select MEAs to show in table below</h3>
    <form action="">
    <?php
        foreach ($columns as $column) :
            $checked = in_array($column->id, $initial) ? ' checked="checked"' : '';
    ?>
            <label style="width: 250px; display: inline-block">
                <input type="checkbox" id="filter-<?php echo $column->id; ?>"<?php echo $checked; ?> value="<?php echo $column->id; ?>" />
                <?php echo $column->short_title; ?>
            </label>
        <?php endforeach; ?>
    </form>
</div>
<div class="table-scroll">
<table>
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th><?php _e('Country', 'informea') ?></th>
        <?php
            foreach ($columns as $column) {
                $visible = in_array($column->id, $initial) ? '' : ' hidden';
        ?>
            <th class="party-<?php echo $column->id . $visible; ?>"><?php echo $column->short_title; ?></th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($countries as $count => $country) : ?>
        <tr>
            <td>
                <a href="<?php echo get_permalink() . '/' . $country->id ?>">
                    <img src="<?php bloginfo('template_directory'); ?>/<?php echo $country->icon_medium; ?>" alt="" />
                </a>
            </td>
            <td>
                <a href="<?php echo get_permalink() . '/' . $country->id ?>">
                    <?php echo $country->name; ?>
                </a>
            </td>
            <?php
            foreach ($columns as $column) :
                $id_treaty = $column->id;
                $id_country = $country->id;
                $coldata = '-';
                $visible = in_array($column->id, $initial) ? '' : ' hidden';
                if (isset($signatures[$id_treaty])) {
                    $tmparr = $signatures[$id_treaty];
                    if (isset($tmparr[$id_country])) {
                        $coldata = $tmparr[$id_country];
                    }
                }
                ?>
                <td<?php echo $visible; ?> class="party-<?php echo $column->id . $visible; ?>"><?php echo $coldata; ?></td>
            <?php endforeach; ?>
            </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>