<?php
$page_data = new informea_treaties();
$treaties = $page_data->get_treaties_by_region_by_theme($page_data->region);

foreach ($treaties as $theme => $items) {
        if (empty($theme)) {
            $theme = __('Generic (no theme)', 'informea');
        }
?>
    <h2><?php echo $theme; ?></h2>
<?php
    foreach ($items as $idx => $_treaty) :
    if ($page_data->region == 'Europe' && $idx % 7 == 0) {
        echo '<div class="clear"></div>';
    }
?>
        <div class="treaty-icon">
            <a class="link" href="<?php imea_url('treaties/' . $_treaty->odata_name); ?>">
                <img src="<?php echo $_treaty->logo_medium; ?>"
                     alt="<?php _e('Convention logo', 'informea'); ?>"/></a>
            <div class="clear"></div>
            <a href="<?php imea_url('treaties/' . $_treaty->odata_name); ?>" title="<?php echo $tooltip; ?>">
                <?php echo $_treaty->short_title_alternative; ?>
            </a>
            <div class="clear"></div>
            <?php informea_treaties::ui_secondary_theme($_treaty); ?>
        </div>
<?php
    endforeach;
?>
    <div class="clear"></div>
<?php
}
?>
