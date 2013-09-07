<?php
wp_enqueue_script('jquery.scrollTo-1.4.3.1-min', get_bloginfo('template_directory') . '/scripts/jquery.scrollTo-1.4.3.1-min.js');
wp_enqueue_script('jquery.balloon-0.3.0', get_bloginfo('template_directory') . '/scripts/jquery.balloon-0.3.0.js');
$page_data = new informea_treaties();

// Process administrator actions
$delete_article_message = $page_data->admin_delete_article();
$delete_article_paragraph_message = $page_data->admin_delete_article_paragraph();

$treaty = informea_treaties::get_treaty_from_request();
$articles = informea_treaties::get_articles($treaty->id);
$all_paragraphs = informea_treaties::get_all_paragraphs($treaty->id);
$print_url = sprintf('%s/treaties/%s/print', get_bloginfo('url'), $treaty->odata_name);

$scoll_id_treaty_article = get_request_int('id_treaty_article');
$hidden_css = empty($scoll_id_treaty_article) ? ' hidden' : '';

?>
<div class="toolbar toolbar-treaty">
    <button id="expand-all"><i class="icon-plus-sign"></i> Expand all</button>
    <button id="collapse-all"><i class="icon-minus-sign"></i> Collapse all</button>

    <?php if (!empty($articles)) : ?>
    <select id="go-to-article" class="toolbar-right">
        <option value="0"><?php _e('-- Go to specific article --', 'informea'); ?></option>
        <?php foreach ($articles as $article) : ?>
        <option value="<?php echo $article->id; ?>"><?php echo $article->official_order . ' ' . $article->title; ?></option>
        <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <button id="print" data-target="<?php echo $print_url; ?>" class="toolbar-right"><i class="icon-print"></i> Print</button>
    <div class="clear"></div>
</div>

<div class="alert alert-info">
    <button class="close" data-dismiss="alert">×</button>
    Click on the article title to expand and see its contents
</div>

<?php if($page_data->actioned && $delete_article_message): ?>
<div class="alert alert-warning">
    <button class="close" data-dismiss="alert">×</button>
    <?php echo $delete_article_message; ?>
</div>
<?php endif; ?>

<?php if($page_data->actioned && $delete_article_paragraph_message): ?>
<div class="alert alert-warning">
    <button class="close" data-dismiss="alert">×</button>
    <?php echo $delete_article_paragraph_message; ?>
</div>
<?php endif; ?>

<ul id="articles" class="articles">
<?php foreach($articles as $article) : ?>
    <li id="article_<?php echo $article->id; ?>">
        <h3 id="article_title_<?php echo $article->id; ?>" data-id="<?php echo $article->id; ?>">
            <?php echo $article->official_order; ?> <?php echo $article->title; ?>
            <div class="management-toolbar article-toolbar">
            <?php if (current_user_can('manage_options')) : ?>
                <a class="btn white small" title="Permanent link to this article"
                   href="<?php informea_treaties::article_url($treaty, $article) ;?>">Permalink</a>
                <a class="btn white small"
                   href="<?php informea_treaties::admin_article_edit_url($article); ?>">Edit</a>
                <a class="btn info small"
                   href="<?php informea_treaties::admin_article_add_paragraph_url($article); ?>">Add new paragraph</a>
                <a class="btn error small" href="javascript:void(0);"
                   onclick="if(confirm('Are you REALLY sure?')) { $('form#delete-article-<?php echo $article->id; ?>').submit(); } return false;">Delete</a>
                <form id="delete-article-<?php echo $article->id; ?>"
                      action="<?php bloginfo('url'); ?>/treaties/<?php echo $treaty->odata_name; ?>"
                      method="post">
                    <input type="hidden" name="id_article" value="<?php echo $article->id; ?>"/>
                    <input type="hidden" name="action" value="delete_article"/>
                    <?php wp_nonce_field('treaty_delete_article'); ?>
                </form>
            <?php endif; ?>
            </div>
        </h3>
        <ul class="paragraphs<?php echo $hidden_css; ?>">
        <?php
            if(!isset($all_paragraphs[$article->id])) :
        ?>
            <li><?php echo $article->content; ?></li>
        <?php
            else:
                $paragraphs = $all_paragraphs[$article->id];
                $pfirst = $paragraphs[0]; $plast = $paragraphs[count($paragraphs) - 1];
                foreach ($paragraphs as $paragraph) :
                    $para_id = "article_{$article->id}_paragraph_{$paragraph->id}";
                    $content = trim(preg_replace(array('/^<p>/ix', '/<\/p>$/ix'), '', $paragraph->content));
            ?>
                <li id="<?php echo $para_id; ?>" class="ident-<?php echo $paragraph->indent;?>" data-id="<?php echo $paragraph->id; ?>">
                    <div class="management-toolbar paragraph-toolbar">
                    <?php if (current_user_can('manage_options')) : ?>
                        <a class="btn white small" title="Permanent link to this paragraphs"
                           href="<?php informea_treaties::paragraph_url($treaty, $article, $paragraph) ;?>">Permalink</a>
                        <a class="btn white small"
                           href="<?php informea_treaties::admin_paragraph_edit_url($article, $paragraph); ?>">Edit</a>
                        <a class="btn info small"
                           href="<?php informea_treaties::admin_paragraph_insert_below_url($article, $paragraph); ?>">Insert paragraph below</a>
                        <?php if ($paragraph->id != $plast->id) : ?>
                        <a class="btn info small" href="javascript:void(0);"
                            onclick="$('input#direction-<?php echo $paragraph->id; ?>').val('down');$('form#move-paragraph-<?php echo $paragraph->id; ?>').submit();"><i class="icon-arrow-down"></i></a>
                        <?php endif; ?>
                        <?php if ($paragraph->id != $pfirst->id) : ?>
                        <a class="btn info small" href="javascript:void(0);"
                           onclick="$('input#direction-<?php echo $paragraph->id; ?>').val('up');$('form#move-paragraph-<?php echo $paragraph->id; ?>').submit();"><i class="icon-arrow-up"></i></a>
                        <?php endif; ?>
                        <a class="btn error small" href="javascript:void(0);"
                           onclick="if(confirm('Are you REALLY sure?')) { $('form#delete-paragraph-<?php echo $paragraph->id; ?>').submit(); } return false;">Delete</a>

                        <form id="move-paragraph-<?php echo $paragraph->id; ?>" method="post"
                              action="<?php bloginfo('url'); ?>/treaties/<?php echo $treaty->odata_name; ?>?id_treaty_article=<?php echo $article->id; ?>#<?php echo $para_id; ?>">
                            <input type="hidden" name="id_treaty" value="<?php echo $treaty->id; ?>" />
                            <input type="hidden" name="id_treaty_article" value="<?php echo $article->id; ?>" />
                            <input type="hidden" name="id_paragraph" value="<?php echo $paragraph->id; ?>" />
                            <input type="hidden" name="action" value="" id="direction-<?php echo $paragraph->id; ?>" />
                        </form>

                        <form id="delete-paragraph-<?php echo $paragraph->id; ?>"
                              action="<?php bloginfo('url'); ?>/treaties/<?php echo $treaty->odata_name; ?>/?id_treaty_article=<?php echo $article->id; ?>#article_<?php echo $article->id; ?>"
                              method="post">
                            <input type="hidden" name="id_paragraph" value="<?php echo $paragraph->id; ?>"/>
                            <input type="hidden" name="action" value="delete_article_paragraph"/>
                            <?php wp_nonce_field('treaty_delete_paragraph'); ?>
                        </form>
                    <?php endif; ?>
                    </div>
                    <?php echo $paragraph->official_order . ' ' . $content; ?>
                </li>
                <?php endforeach; ?>
        <?php endif; ?>
        </ul>
    </li>
<?php endforeach; ?>
</ul>