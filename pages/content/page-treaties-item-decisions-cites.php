<?php
wp_enqueue_script('jquery.scrollTo-1.4.3.1-min', get_bloginfo('template_directory') . '/scripts/jquery.scrollTo-1.4.3.1-min.js');
$page_data = new informea_treaties();
$treaty = informea_treaties::get_treaty_from_request();
$showall = get_request_variable('showall', 'str');
?>
<div class="toolbar toolbar-decisions">
    <div class="pull-right">
        Scroll down to
        <button data-target="resolutions" id="scroll-resolutions"><i class="icon-hand-down"></i> Resolutions</button>
    </div>
    <div class="clear"></div>
</div>
<?php
$decisions = $page_data->get_cites_decisions();
$data = array('decisions' => $page_data->get_cites_decisions(), 'resolutions' => $page_data->get_cites_resolutions());
foreach ($data as $title => $decisions) {
?>
<a name="<?php echo $title; ?>"></a>
<h2><?php echo ucfirst($title); ?></h2>
<table class="decisions">
    <thead>
    <tr>
        <th style="width: 130px;"><?php _e('No.', 'informea'); ?></th>
        <th><?php _e('Title', 'informea'); ?></th>
        <th><?php _e('Type/Status', 'informea'); ?></th>
    </tr>
    </thead>
    <tbody>
<?php
    $icount = 0;
    foreach ($decisions as $decision) :
        $tags = $page_data->get_decision_tags($decision->id);
?>
        <tr id="decision-<?php echo $decision->id; ?>">
            <td>
                <?php echo $decision->number; ?>
            </td>
            <td>
                <h3>
                    <?php echo $page_data->page_decisions_overview_decision_link($decision, $treaty); ?>
                </h3>
                <?php if (count($tags)) : ?>
                <ul class="terms round">
                <?php
                    $last = end($tags);
                    foreach ($tags as $tag) :
                ?>
                    <li>
                        <a href="<?php bloginfo('url'); ?>/terms/<?php echo $tag->id; ?>"><?php echo $tag->term; ?></a><?php if ($last !== $tag) { echo ','; } ?>
                    </li>
                <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                    <div class="management-toolbar pull-left">
                    <?php if (current_user_can('manage_options')) : ?>
                        <a class="btn white small" title="Permanent link to this decision"
                           href="<?php informea_treaties::decision_url($treaty, $decision) ;?>">Permalink</a>
                        <a target="_blank" class="btn white small"
                           href="<?php echo admin_url(); ?>admin.php?page=informea_decisions&act=decision_edit&id_decision=<?php echo $decision->id; ?>&id_treaty=<?php echo $decision->id_treaty; ?>">Edit</a>
                        <a target="_blank" class="btn white small"
                           href="<?php echo admin_url(); ?>admin.php?page=informea_decisions&act=decision_edit_decision&id_treaty=<?php echo $decision->id_treaty; ?>&id_decision=<?php echo $decision->id; ?>">Break in paragraphs</a>
                    <?php endif; ?>
                    </div>
            </td>
            <td>
                <?php echo $decision->type; ?> (<?php $status = decode_decision_status($decision->status); echo $status; ?>)
            </td>
        </tr>
    <?php
        endforeach;
    ?>
    </tbody>
</table>
<a href="#top" class="back-top">Back to top</a>
<?php } ?>
