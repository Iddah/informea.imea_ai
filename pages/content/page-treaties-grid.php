<?php
$page_data = new informea_treaties();
$treaties = $page_data->get_treaties_by_region_by_theme($page_data->region);
?>
<table class="treaties table-hover">
    <thead>
    <tr>
        <th>&nbsp;</th>
        <th><?php _e('Year', 'informea'); ?></th>
        <th><?php _e('Depository', 'informea'); ?></th>
        <th><?php _e('Links', 'informea'); ?></th>
    </tr>
    </thead>
    <tbody>
<?php
    foreach ($treaties as $theme => $treaties) {
        if (empty($theme)) {
            $theme = __('Generic (no theme)', 'informea');
        }
?>
        <tr class="nohover">
            <td colspan="4">
                <h2 style="margin-top: 15px;"><?php echo $theme; ?></h2>
            </td>
        </tr>
<?php
        foreach ($treaties as $_treaty) {
            if ($_treaty->id == 9) {
                $tooltip = __('The Nagoya protocol was adopted at CBD COP 10 and will enter into force 90 days after the ratification by 50 parties. Open Nagoya Protocol', 'informea');
            } else {
                $tooltip = __('Open', 'informea') . ' ' . $_treaty->short_title;
            }
?>
        <tr>
            <td>
                <a href="<?php imea_url('treaties/' . $_treaty->odata_name); ?>"
                   title="Click to see details"><?php echo $_treaty->short_title; ?></a>
            </td>
            <td class="center">
                <?php echo (@date('Y', strtotime($_treaty->start)) > 0) ? @date('Y', strtotime($_treaty->start)) : '-'; ?>
            </td>
            <td>
                <?php echo $_treaty->depository; ?>
            </td>
            <td>
                <a href="<?php echo parse_url($_treaty->url, PHP_URL_SCHEME) . "://" . parse_url($_treaty->url, PHP_URL_HOST); ?>"
                   title="<?php _e('Visit convention website. This will open in a new window', 'informea'); ?>"
                   target="_blank"><img
                        src="<?php bloginfo('template_directory'); ?>/images/globe.png"/></a>
                <a href="<?php echo $_treaty->url; ?>"
                   title="<?php _e('Read treaty text on the Convention website. This will open in a new window', 'informea'); ?>"
                   target="_blank"><img
                        src="<?php bloginfo('template_directory'); ?>/images/small-treaty.png"/></a>
            </td>
        </tr>
<?php
        }
    }
?>
    </tbody>
</table>