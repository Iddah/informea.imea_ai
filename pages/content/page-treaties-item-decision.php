<?php
wp_enqueue_script('jquery.balloon-0.3.0', get_bloginfo('template_directory') . '/scripts/jquery.balloon-0.3.0.js');

$treaty = informea_treaties::get_treaty_from_request();
$id_decision = get_request_variable('id_decision');
$page_data = new informea_treaties();

$decision_data = new imea_decisions_page();
$decision = $decision_data->get_decision($id_decision);
$documents = $page_data->get_decision_documents($decision->id);
$paragraphs = $decision_data->get_paragraphs($id_decision);
$meeting = $decision_data->get_meeting_by_decision($decision);
?>

<h2><?php echo $decision->short_title; ?></h2>

<div class="decision">
<table class="context">
    <?php if (current_user_can('manage_options')) : ?>
        <tr>
            <th>Administer content</th>
            <td>
                <a class="btn white" href="<?php informea_decisions::edit_decision_url($treaty, $decision); ?>">Edit</a>
                <a class="btn white" href="<?php informea_decisions::tag_decision_paragraphs_url($treaty, $decision); ?>">Tag paragraphs</a>
            </td>
        </tr>
    <?php endif; ?>
    <tr>
        <th>Decision number</th>
        <td><?php echo $decision->number; ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><?php echo ucfirst($decision->status); ?></td>
    </tr>
    <tr>
        <th>Type</th>
        <td><?php echo ucfirst($decision->type); ?></td>
    </tr>
    <tr>
        <th>Published</th>
        <td><?php echo format_mysql_date($decision->published); ?></td>
    </tr>
    <?php if ($meeting) : ?>
        <tr>
            <th>Meeting</th>
            <td>
                <a href="/treaties/<?php echo $treaty->odata_name; ?>"><?php echo $treaty->short_title; ?></a>
                <a href="/treaties/<?php echo $treaty->odata_name; ?>/decisions?showall=1#decision-<?php echo $decision->id; ?>"><?php echo $meeting->title; ?></a>
            </td>
        </tr>
    <?php endif; ?>
    <?php if (!empty($decision->link)) : ?>
        <tr>
            <th>Online version</th>
            <td>
                <a href="<?php echo $decision->link; ?>" target="_blank"
                   title="<?php _e('Click to see this decision on Convention website', 'informea'); ?>">View</a>
            </td>
        </tr>
    <?php endif; ?>
    <?php if (!empty($documents)) : ?>
    <tr>
        <th>Decision text</th>
        <td>
            <ul>
            <?php foreach ($documents as $doc) : ?>
                <li>
                    <a target="_blank" href="<?php bloginfo('url') ?>/download?entity=decision_document&id=<?php echo $doc->id; ?>">
                    <img src="<?php echo $doc->icon_url; ?>" />
                    <?php echo !empty($doc->language) ? ' (' . $doc->language . ')' : '';?>
                    </a>
                </li>
            <?php endforeach; ?>
            </ul>
        </td>
    </tr>
    <?php endif; ?>
</table>
<?php if(count($paragraphs)): ?>
    <ul class="paragraphs">
    <?php foreach ($paragraphs as $paragraph) :
    ?>
        <li data-id="<?php echo $paragraph->id; ?>">
            <a name="paragraph-<?php echo $paragraph->id; ?>"></a>
            <span class="gray">[&hellip;]</span> <?php echo $paragraph->content; ?> <span class="gray">[&hellip;]</span>
        </li>
    <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
