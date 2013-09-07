<?php
$expand = get_request_variable('expand', 'str', 'treaties');
$id = get_request_variable('id', 1);
$page_data = new Thesaurus($id, array('id_ns' => 1, 'id_concept' => 1, 'feature' => 'str', 'mode' => 'str'));
$term = $page_data->term;
?>
<div class="alert alert-warning">
    <button class="close" data-dismiss="alert">x</button>
    <?php echo __('Please note that Treaty portions and decisions are manually tagged and some omissions may occur. To ensure comprehensive results, combine terms from the analytical index with terms used in a free-text search which can be entered in the search fields found in the Explorer.', 'informea'); ?>
</div>

<?php
$tabs = array(
    'treaties' => __('Treaties', 'informea'),
    'decisions' => __('Decisions', 'informea'),
    'ecolex' => __('Ecolex literature', 'informea'),
);
?>
<div class="tabs">
    <ul>
        <?php
        foreach($tabs as $url => $label) :
            $active = ($expand == $url) ? ' active' : '';
            $tab_url = sprintf('%s/%s/%s', get_permalink(), $id, $url);
            ?>
            <li>
                <a class="<?php echo $active; ?>" href="<?php echo $tab_url; ?>"><?php echo $label; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php get_template_part('pages/content/page', "terms-item-$expand"); ?>