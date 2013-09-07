<?php
function tengine_header($show_explorer = true, $primary_menu = '', $useful_links_menu = '') {
?>
<div id="header">
    <div id="masthead">
        <div id="branding" role="banner">
            <div id="logo">
                <a href="/"><img src="<?php header_image(); ?>" alt="" title="<?php bloginfo('name'); ?>" id="logo-image"/></a>
            </div>
        </div>
        <?php wp_nav_menu(array('menu' => 'secondary', 'container' => '', 'theme_location' => 'secondary', 'walker' => new imea_menu_walker())); ?>
        <?php echo (!empty($primary_menu)) ? $primary_menu : '<span id="informea_template_primary_menu"></span>'; ?>
        <?php
        if (!is_front_page() && $show_explorer) {
        ?>
            <div id="explorer">
                <div class="mea-button right">
                    <img src="<?php bloginfo('template_directory'); ?>/images/s.gif" alt="" title="<?php _e('Toggle MEA Explorer box', 'informea'); ?>"/>
                </div>
                <div class="clear"></div>
                <?php
                    if($show_explorer) {
                        include(dirname(__FILE__) . '/../pages/explorer/inc.explorer.php');
                    }
                ?>
            </div>
        <?php
        }
        ?>
        <div class="clear"></div>
    </div><!-- /#masthead -->
</div><!-- /#header -->
<?php
}
?>
