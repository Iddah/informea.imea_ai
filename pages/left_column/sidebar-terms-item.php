<?php
$id = get_request_variable('id', 1);
$page_data = new Thesaurus($id, array('id_ns' => 1, 'id_concept' => 1, 'feature' => 'str', 'mode' => 'str'));
$term = $page_data->term;
?>
<ul class="sidebar">

    <?php if(!empty($term->description)) : ?>
    <li class="widget broad-terms">
        <h2>Definition</h2>
        <p class="text-justify">
            <?php echo $term->description; ?>
        </p>
    </li>
    <?php endif; ?>

    <li class="widget broad-terms">
        <h2>Term details</h2>
        <div class="content">
        <?php
            if(!empty($page_data->related['broader'])) :
                $broader = $page_data->related['broader'];
        ?>
            <h3>Broader terms</h3>
            <ul>
            <?php
                $last = end($broader);
                foreach ($broader as $broader_term) :
                    $url = sprintf('%s/terms/%s', get_bloginfo('url'), $broader_term->id);
            ?>
                <li>
                    <a href="<?php echo $url; ?>"><?php echo $broader_term->term; ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if(!empty($term->geg_tools_url)) : ?>
                <h3>Environmental Goals</h3>
                <a href="<?php echo $term->geg_tools_url; ?>">View on GEG website</a>
            <?php endif; ?>
        </div>
    </li>

    <?php
        global $cloud_terms;
        $t = array();
        if (isset($page_data->related['related'])) {
            $t = array_merge($t, $page_data->related['related']);
        }
        if (isset($page_data->related['narrower'])) {
            $t = array_merge($t, $page_data->related['narrower']);
        }
        $cloud_terms = $page_data->array_unique_terms($t);
        $cloud_terms = $page_data->compute_popularity($t);

        dynamic_sidebar('terms-sidebar-item');
    ?>

    <?php if (current_user_can('manage_options')) : ?>
        <li class="widget management">
            <h2>Manage</h2>
            <div class="content">
                <ul>
                    <li>
                        <a class="button" href="<?php echo bloginfo('url'); ?>/wp-admin/admin.php?page=thesaurus&act=voc_relationships&id_term=<?php echo $id; ?>">Edit term</a>
                    </li>
                </ul>
            </div>
        </li>
    <?php endif; ?>
</ul>
