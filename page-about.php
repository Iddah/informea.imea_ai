<?php
/**
 * Template name: InforMEA About page
 */
if (have_posts()) : while (have_posts()) : the_post();
    global $post;
    wp_enqueue_script('jquery-custom', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.3/jquery.min.js', array(), FALSE, TRUE);
    wp_enqueue_script('jquery-ui-custom', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js', array(), FALSE, TRUE);
    wp_enqueue_script('informea-common', get_bloginfo('template_directory') . '/scripts/common.js', array(), FALSE, TRUE);
    wp_enqueue_script('imea-explorer', get_bloginfo('template_directory') . '/scripts/imea_explorer.js', array(), FALSE, TRUE);
    add_action('breadcrumbtrail', 'informea_about_breadcrumbtrail');
    $about = get_page_by_title('about');
    $subpages = get_pages(array('child_of' => $about->ID, 'sort_column' => 'menu_order', 'sort_order' => 'ASC'));
    $current_page = null;
    get_header();
?>

    <div id="page-title">
        <h1><?php echo $about->post_title; ?></h1>
        <?php echo imea_page_base_page::get_page_description($about); ?>
    </div>
    <div class="col2-left col2">
        <div>
            <?php dynamic_sidebar('about-page-left'); ?>
        </div>
        <div class="clear"></div>
    </div>
    <div class="col2-center col2 text-justify">
        <div class="tabs">
            <ul>
                <li>
                    <a<?php echo ($post->ID == $about->ID) ? ' class="active"' : ''; ?>
                        href="<?php echo get_page_link($about->ID); ?>">Introduction</a>
                </li>
                <?php
                foreach ($subpages as $sp) {
                    $css_class = 'tab';
                    if ($post->ID == $sp->ID) {
                        $css_class = 'active';
                        $current_page = $sp;
                    }
                    ?>
                    <li>
                        <a class="<?php echo $css_class; ?>"
                           href="<?php echo get_page_link($sp->ID); ?>"><?php echo $sp->post_title; ?></a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <?php
            the_content();
            echo '<div class="clear"></div>';
            edit_post_link(__('Edit', 'informea'), '<span class="edit-link">', '</span>');
        ?>
    </div>
<?php
endwhile; endif;
get_footer();

function informea_about_breadcrumbtrail() {
    global $post;
    global $about;
    $items = array();
    if($post->ID == $about->ID) {
        $items[__('About', 'informea')] = '';
    } else {
        $items[__('About', 'informea')] = get_permalink($about->ID);
        $items[$post->post_title] = '';
    }
    echo build_breadcrumbtrail($items);
}
