<?php

$page_data = new imea_news_page();
$topic = get_request_variable('topic');
if(empty($topic)) {
    $topic = get_request_value('topic');
}
$q = get_request_value('q');
$query_category = $page_data->get_category_by_slug($topic);
$page_size = 20;
$page = get_request_variable('h_page');
if(empty($page)) {
    $page = get_request_int('h_page');
}

$category_slug = $query_category ? $query_category->slug : NULL;
$query = $page_data->search($q, $category_slug, $page, $page_size);
$rows = $query->posts;
if(count($rows)):
    wp_enqueue_script('meetings', get_bloginfo('template_directory') . '/scripts/news.js');
    wp_enqueue_script('infinitescroll', get_bloginfo('template_directory') . '/scripts/jquery.infinitescroll-2.0b2.js');
    // Minimized version has a bug (https://github.com/paulirish/infinite-scroll/issues/217)
?>
<div id="results">
<ul class="items">
<?php foreach($rows as $row):
    $summary = $row->summary;
    $img_in_summary = strpos($summary, 'img') <= 0;

    if($img_in_summary && isset($row->image)) {
        $img_tag = sprintf('<img src="%s" />', $row->image);
    } else {
        $img_tag = sprintf('<img src="%s/images/pixel.gif" />', get_bloginfo('template_directory'));
    }
?>
    <li>
        <div class="pull-left logo">
            <?php echo $img_tag; ?>
        </div>
        <div class="news-title">
            <h3><?php echo $row->title; ?></h3>
            <p class="summary">
                <?php echo $summary; ?>
            </p>
            <ul class="info">
                <li>
                    <?php echo $row->date_formatted; ?>
                </li>
                <li>
                    <a target="_blank" href="<?php echo $row->permalink; ?>">View</a>
                </li>
                <?php foreach(imea_news_page::get_post_categories($row) as $cat): ?>
                <li>
                    <?php echo $cat->name; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="clear"></div>
    </li>
<?php endforeach; ?>
</ul>

<?php if($page < $query->max_num_pages):
    $next_url = sprintf('%s?q=%s&topic=%s&h_page=%s', get_permalink(), $q, $topic, ($page + 1));
?>
    <div class="paginator text-center">
        <a href="<?php echo $next_url; ?>">Next page &raquo;</a>
    </div>
    <?php endif; ?>

</div>
<?php
endif;
