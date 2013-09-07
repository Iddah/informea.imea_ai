<?php
$page_size = get_request_int('fe_page_size', 50);
$meetings = informea_meetings::get_event_list($page_size);
if(empty($meetings)) {
    echo 'No meetings found';
    return;
}

$id_treaty = get_request_int('fe_treaty');
$fe_type = get_request_value('fe_type');
$year = get_request_int('fe_year');
$id_country = get_request_int('fe_country');
$page = get_request_variable('fe_page', 0, 0);
$show_past = get_request_int('fe_show_past');


$total = informea_meetings::count_event_list($page_size);
$pages = ceil($total / $page_size);

$next_url = sprintf(
    '%s?fe_treaty=%s&fe_type=%s&fe_year=%s&fe_country=%s&fe_page_size=%s&fe_show_past=%s&page=%s',
    get_permalink(), $id_treaty, $fe_type, $year, $id_country, $page_size, $show_past, ($page+1)
);

wp_enqueue_script('meetings', get_bloginfo('template_directory') . '/scripts/meetings.js');
wp_enqueue_script('infinitescroll', get_bloginfo('template_directory') . '/scripts/jquery.infinitescroll-2.0b2.js');
// Minimized version has a bug (https://github.com/paulirish/infinite-scroll/issues/217)
?>
<div id="results">
    <ul class="items">
        <?php foreach($meetings as $event): ?>
            <?php informea_meetings::event_to_html($event, $fe_type); ?>
        <?php endforeach; ?>
    </ul>
    <?php if($total > 0 && $pages > 1 && $page < $pages): ?>
    <ul class="paginator round">
        <li>
            <a class="next" href="<?php echo $next_url; ?>">Next page &raquo;</a>
        </li>
    </ul>
    <?php endif; ?>
</div>