<?php
$expand = get_request_variable('expand', 'str', 'icon');
?>
<div class="toolbar text-right">
    <form action="">
        <label for="view-mode"><?php _e('Change view', 'informea'); ?></label>
        <select id="view-mode" name="view-mode" onchange="onChangeViewMode(this);" disabled="disabled">
            <?php
            foreach(array('By treaty' => '', 'By terms' => 'terms') as $label => $mode) :
                $selected = ($expand == $mode) ? 'selected="selected "' : '';
                $data_url = sprintf('%s/decisions/%s', get_bloginfo('url'), $mode);
                ?>
                <option data-url="<?php echo $data_url; ?>" value="<?php echo $mode; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php
get_template_part('pages/content/page', "decisions-$expand");
