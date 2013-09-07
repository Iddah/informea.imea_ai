<?php
$page_data = new informea_treaties();
$expand = get_request_variable('expand', '', 'icon');
$css_tab_g = $page_data->is_tab_global() ? 'active' : 'tab';
$css_tab_r = !$page_data->is_tab_global() ? 'active' : 'tab';
$tabs = array('Africa', 'Asia Pacific', 'Europe', 'Latin America and the Carribean', 'North America', 'West Asia');
$tabs = array_unique(array_merge($tabs, $page_data->get_regions()));
sort($tabs);
?>
<div class="tabs">
    <ul>
        <li>
            <?php imea_anchor(array('label' => __('Global', 'informea'), 'href' => 'treaties/region/Global', 'css' => $css_tab_g)); ?>
        </li>
        <?php
        foreach ($tabs as $idx => $region) {
            $css_tab_r = $page_data->region == $region ? 'active' : 'tab';
            $disabled = $page_data->region_has_treaties($region) == '0';
        ?>
        <li>
            <?php
            if (!$disabled) {
                imea_anchor(array('label' => $region, 'href' => 'treaties/region/' . $region, 'css' => $css_tab_r));
            } else {
            ?>
                <a href="javascript:void(0);" class="disabled"><?php echo $region; ?></a>
            <?php } ?>
        </li>
        <?php } ?>
    </ul>
</div>
<div class="toolbar text-right">
    <form action="">
        <label for="view-mode"><?php _e('Change view', 'informea'); ?></label>
        <select id="view-mode" name="view-mode" onchange="onChangeViewMode(this);">
            <?php
            foreach(array('Icon' => 'icon', 'Grid' => 'grid') as $label => $mode) :
                $selected = ($expand == $mode) ? 'selected="selected "' : '';
                $data_url = sprintf('%s/treaties/region/%s/%s', get_bloginfo('url'), $page_data->region, $mode);
            ?>
            <option data-url="<?php echo $data_url; ?>" value="<?php echo $mode; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php
get_template_part('pages/content/page', "treaties-$expand");
?>
