<?php
$id = informea_countries::get_id_from_request();
$mea_membership = informea_countries::get_treaty_membership($id);
?>
<div class="tab-content">
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
    <?php foreach($mea_membership as $party) : ?>
        <tr>
            <td class="text-center"><i class="thumbnail <?php echo $party->odata_name; ?>"></i></td>
            <td><a href="<?php echo sprintf('%s/treaties/%s', get_bloginfo('url'), $party->odata_name); ?>"><?php echo $party->short_title; ?></a></td>
            <td class="text-center"><?php echo mysql2date('Y', $party->date); ?></td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
        </tr>
    <?php endforeach; ?>
</table>
</div>