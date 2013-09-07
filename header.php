<?php
global $post;
require_once(dirname(__FILE__) . '/template/index.php');
$imea_options = get_option('informea_options');
// Actions for management of treaty paragraphs in edit mode
if (current_user_can('manage_options')) {
    do_action('move_up_treaty_paragraph');
    do_action('move_down_treaty_paragraph');
}
$tpl_show_explorer = $post !== NULL && $post->post_name != 'index' && $post->post_name != 'search';
$tpl_primary_menu = wp_nav_menu(array('echo' => false, 'menu' => 'primary', 'container' => '', 'theme_location' => 'primary', 'walker' => new imea_menu_walker()));
$tpl_useful_links_menu = !is_user_logged_in() ?
    '<a href="' . get_bloginfo('url') . '/wp-admin/">' . __('Login', 'informea') . '</a>'
    : '<a href="' . get_bloginfo('url') . '/wp-admin/">' . __('Administration Panel', 'informea') . '</a> | <a href="' . wp_logout_url(get_permalink()) . '">' . __('Logout', 'informea') . '</a>';
add_filter('body_class', function ($classes) {
    $classes[] = 'informea';
    return $classes;
});
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes"/>
    <link rel="alternate" type="application/rss+xml" title="InforMEA &raquo; News" href="<?php bloginfo('url'); ?>/news/rss"/>
    <link rel="alternate" type="application/rss+xml" title="InforMEA &raquo; Meetings" href="<?php bloginfo('url'); ?>/meetings/rss"/>
    <title>
        <?php
        wp_title('-', true, 'right');
        echo apply_filters('informea_page_title', '');
        bloginfo('name');
        ?>
    </title>
    <link rel="profile" href="http://gmpg.org/xfn/11"/>
    <?php tengine_head(); ?>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
    <?php wp_head(); ?>
    <script type="text/javascript">
        var images_dir = '<?php bloginfo('url'); ?>/wp-content/themes/informea/images/';
        var blog_dir = '<?php bloginfo('url'); ?>';
        var ajax_url = '<?php bloginfo('url'); ?>/wp-admin/admin-ajax.php';
    </script>
</head>
<body <?php body_class(); ?>>
<a name="top"></a>
<?php if (informea_is_staging()) : ?>
    <div id="informea-staging">
        DEMO VERSION
    </div>
<?php endif; ?>
    <div id="informea">
        <div class="skip-link screen-reader-text">
            <a href="#content"
               title="<?php _e('Skip to content', 'informea'); ?>"><?php _e('Skip to content', 'informea'); ?></a>
        </div>
        <?php tengine_header($tpl_show_explorer, $tpl_primary_menu, $tpl_useful_links_menu); ?>
        <div id="main">
            <?php do_action('breadcrumbtrail'); ?>
            <div id="container">
                <div id="content" role="main">