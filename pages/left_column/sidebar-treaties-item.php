<?php
$page_data = new informea_treaties();
$treaty = informea_treaties::get_treaty_from_request();
$id = $treaty->id;

$parties = informea_treaties::get_parties($id);
$no_parties = (count($parties) > 0) ? count($parties) : intval($treaty->number_of_parties);

$entry_into_force = @date('Y', strtotime($treaty->start));

wp_enqueue_script('treaties', get_bloginfo('template_directory') . '/scripts/treaties.js');
?>
<form id="change-treaty" action="<?php echo bloginfo('url'); ?>/treaties" method="get">
    <select name="id" id="change-treaty-id" class="column-select" onchange="onChangeTreaty();">
        <option value="">-- <?php _e('Select another instrument', 'informea'); ?> --</option>
        <?php foreach (informea_treaties::get_treaties() as $row) : ?>
            <option value="<?php echo $row->odata_name; ?>">
                <?php echo $row->short_title; ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if($no_parties > 0) : ?>
<p>
    <span class="label"><?php _e('Number of parties', 'informea'); ?>:</span>
    <?php echo $no_parties; ?>
</p>
<?php endif; ?>

<?php if (!empty($treaty->theme)) : ?>
    <p>
        <span class="label"><?php _e('Theme', 'informea'); ?>:</span>
        <?php echo $treaty->theme; ?>
    </p>
<?php endif; ?>

<?php if (!empty($treaty->abstract)) : ?>
    <p>
        <span class="label"><?php _e('Abstract', 'informea'); ?>:</span>
        <?php
        $value = subwords($treaty->abstract, 30);
        echo '<span id="abstract_partial">';
        echo $value;
        if (strlen($value) < strlen($treaty->abstract)) {
            echo '<a href="#" onclick="onClickAbstractMore();">more &raquo;</a>';
        }
        echo '</span>';
        if (strlen($value) < strlen($treaty->abstract)) {
            echo '<span id="abstract_full" style="display:none;">' . $treaty->abstract . '</span>';
        }
        ?>
    </p>
<?php endif; ?>

<?php if (!empty($treaty->url)) : ?>
    <p>
        <span class="label"><?php _e('URL', 'informea'); ?>:</span>
        <a href="<?php echo $treaty->url; ?>" target="_blank"
           title="Visit convention website. This page will open in a new window">Visit convention website</a>
    </p>
<?php endif; ?>

<?php if ($entry_into_force) : ?>
    <p>
        <span class="label"><?php _e('Entry into force', 'informea'); ?>:</span>
        <?php echo $entry_into_force; ?>
    </p>
<?php endif; ?>

<?php if ($treaty->depository) : ?>
    <p>
        <span class="label"><?php _e('Depository', 'informea'); ?>:</span>
        <?php echo $treaty->depository; ?>
    </p>
<?php endif; ?>

<?php
    global $cloud_terms;
    $cloud_terms = $page_data->get_cloud_terms_for_treaty_page($id);
    dynamic_sidebar('treaties-sidebar-item');
?>

<?php if (current_user_can('manage_options')) : ?>
<div class="management round">
    <ul>
        <li>
            <a href="<?php echo admin_url(); ?>admin.php?page=informea_treaties&act=treaty_add_article&id_treaty=<?php echo $id; ?>">
                Add new article
            </a>
        </li>
        <li>
            <a href="<?php echo admin_url(); ?>admin.php?page=informea_treaties&act=treaty_edit_treaty&id=<?php echo $id; ?>">
                Edit this treaty
            </a>
        </li>
        <li>
            <a href="<?php echo admin_url(); ?>admin.php?page=informea_decisions&act=decision_order&id_treaty=<?php echo $id; ?>">
                Reorder decisions
            </a>
        </li>
    </ul>
</div>
<?php endif; ?>