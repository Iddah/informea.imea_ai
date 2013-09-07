<?php
/* template generator */
$part = get_request_value('part');
if (empty($part)) {
    die('No part specified');
}
if ('head' == $part) {
    require_once(dirname(__FILE__) . '/template/head.php');
    tengine_head();
}
if ('header' == $part) {
    $show_explorer = get_request_boolean('show_explorer');
    require_once(dirname(__FILE__) . '/template/header.php');
    tengine_header($show_explorer);
}
if ('footer' == $part) {
    require_once(dirname(__FILE__) . '/template/footer.php');
    tengine_footer();
}
