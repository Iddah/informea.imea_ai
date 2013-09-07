<?php $tab = get_request_int('q_tab', 2); ?>
<div class="toolbar toolbar-countries">
    <?php do_action('informea-search-toolbar-extra'); ?>
    <form action="" class="pull-right">
        <label for="tab-mode"><?php _e('View results', 'informea'); ?></label>
        <select id="tab-mode" name="view-mode">
            <option <?php echo $tab == 1 ? 'selected="selected "' : ''; ?>value="1"><?php _e('as timeline', 'informea'); ?></option>
            <option <?php echo $tab == 2 ? 'selected="selected "' : ''; ?>value="2"><?php _e('grouped by treaty', 'informea'); ?></option>
            <option <?php echo $tab == 3 ? 'selected="selected "' : ''; ?>value="3"><?php _e('global treaties', 'informea'); ?></option>
            <option <?php echo $tab == 4 ? 'selected="selected "' : ''; ?>value="4"><?php _e('decisions', 'informea'); ?></option>
            <option <?php echo $tab == 5 ? 'selected="selected "' : ''; ?>value="5"><?php _e('regional treaties', 'informea'); ?></option>
        </select>
    </form>
    <div class="clear"></div>
</div>
