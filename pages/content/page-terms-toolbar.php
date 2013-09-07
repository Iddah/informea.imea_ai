<?php
$expand = get_request_variable('expand', 'str', 'map');
?>
<div class="toolbar toolbar-terms">
    <?php do_action('informea-terms-toolbar-extra'); ?>
    <form action="" class="pull-right">
        <label for="view-mode"><?php _e('Change view', 'informea'); ?></label>
        <select id="view-mode" name="view-mode" onchange="onChangeViewMode(this);">
            <?php
            foreach(array('Hierarchical' => 'hierarchical', 'List' => 'list') as $label => $mode) :
                $selected = ($expand == $mode) ? 'selected="selected "' : '';
                $data_url = sprintf('%s/terms/%s', get_bloginfo('url'), $mode);
                ?>
                <option data-url="<?php echo $data_url; ?>" value="<?php echo $mode; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <div class="clear"></div>
</div>
