<?php
$page_data = new informea_decisions();
$treaties = $page_data->get_treaties_list();
?>
<?php
    foreach ($treaties as $theme => $t) :
?>
    <h2><?php echo $theme; ?></h2>
    <ul>
<?php
    foreach ($t as $_treaty) :
        if ($_treaty->odata_name == 'aewa') { continue; }
        $url = sprintf('%s/treaties/%s/decisions', get_bloginfo('url'), $_treaty->odata_name);
?>
        <li class="treaty-icon">
            <a href="<?php echo $url; ?>">
                <img src="<?php echo $_treaty->logo_medium; ?>" alt="<?php _e('Convention logo', 'informea'); ?>"/>
            </a>
            <div class="clear"></div>
            <a href="<?php echo $url; ?>">
                <?php echo $_treaty->short_title_alternative; ?>
            </a>
            <?php informea_treaties::ui_secondary_theme($_treaty); ?>
        </li>
    <?php endforeach; ?>
    </ul>
    <div class="clear"></div>
<?php
    endforeach;
?>